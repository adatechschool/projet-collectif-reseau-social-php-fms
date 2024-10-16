<?php
include 'session.php';
include 'connect.php';
include 'likes.php';
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Flux</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <?php include 'header.php'; ?>

    <div id="wrapper">
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation test</h3>
                <p>Sur cette page vous trouverez tous les message des utilisatrices
                    auxquel est abonnée l'utilisatrice <?php echo $userPseudo; ?>
                    (n° <?php echo $userId ?>)
                </p>
            </section>
        </aside>
        <main>
            <?php
            $laQuestionEnSql = "
                    SELECT 
                    posts.id, 
                    posts.content,
                    posts.user_id,
                    posts.created,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo ("Échec de la requete : " . $mysqli->error);
            }
            while ($post = $lesInformations->fetch_assoc()) {
                ?>
                <article>
                    <h3>
                        <time datetime='<?php echo $post['created'] ?>'>
                            <?php 
                                $date = new DateTime($post['created']); 
                                $formatter = new IntlDateFormatter(
                                'fr_FR', 
                                IntlDateFormatter::LONG,  //date
                                IntlDateFormatter::SHORT //heure
                            );
                            echo $formatter->format($date);
                        ?>
                        </time>
                    </h3>
                    <address>par
                        <a href="wall.php?user_id=<?php echo $post['user_id'] ?>"> <?php echo $post['author_name'] ?></a>
                    </address>
                    <div>
                        <p><?php echo $post['content']; ?></p>
                    </div>
                    <footer>
                        <small>♥ <?php echo $post['like_number']; ?></small>
                        <form action="feed.php" method="post" style="display:inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>" />
                            <!-- <?php echo "<pre>" . print_r($post, 1) . "</pre>"; ?> -->
                            <button type="submit" name="action" value="like">👍 J'aime</button>
                            <button type="submit" name="action" value="dislike">👎 Je n'aime plus</button>
                        </form>
                        <a href="">#<?php echo $post['taglist']; ?></a>,
                    </footer>
                </article>
                <?php
            }
            ?>
        </main>
    </div>
</body>

</html>