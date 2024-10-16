<?php

$lesInformations = fetchPosts($mysqli, $userId);

if ($lesInformations->num_rows > 0) {
// Affichage des posts
while ($post = $lesInformations->fetch_assoc()) {
    ?>
    <article>
        <h3>
            <time datetime='<?php echo $post['created']; ?>'>
                <?php 
                    $date = new DateTime($post['created']); 
                    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::SHORT);
                    echo $formatter->format($date);
                ?>
            </time>
        </h3>

        <?php
        if ($post['author_name'] === $user['alias']) {
        ?>
        <address>de 
            <a href="wall.php?user_id=<?php echo $_SESSION['connected_id'];?>">
                <?php echo $post['author_name'];?></a> 
        </address>
        <?php }else{ ?>
            <address>de 
            <a href="wall.php?user_id=<?php echo $_SESSION['connected_id'];?>">
                <?php echo $post['author_name'];?></a>
            à 
            <a href="wall.php?user_id=<?php echo $userId; ?>">
                <?php echo $user['alias']; ?>
            </a>
        </address>
        <?php } ?>
        <div>
            <p><?php echo $post['content']; ?></p>
        </div>
        <footer>
            <small>♥ <?php echo $post['like_number']; ?></small>
            <form action="wall.php" method="post" style="display:inline;">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>" />
                <button type="submit" name="action" value="like">👍 J'aime</button>
                <button type="submit" name="action" value="dislike">👎 Je n'aime plus</button>
            </form>
            <a href="">#<?php echo $post['taglist']; ?></a>
        </footer>
    </article>
    <?php
}

} else {
    // Message si aucun post n'est trouvé
    echo "<p>Aucun post trouvé.</p>";
}
?>