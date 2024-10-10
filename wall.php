<?php 
include 'session.php';    
include 'connect.php';  
?>


<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mur</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body> 
       <?php include 'header.php';?>
      
       <?php 

        // Si la méthode est POST et que le champ 'message' existe
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $message = $_POST['message'];
            
            if (!empty($message)) {
                // Prépare la requête SLQ pour mettre le message dans la BDD
                //les valeurs en '?' ne sont définies qu'après avec le bind_param
                $requeteSQL = "INSERT INTO posts (content, user_id, created) VALUES (?, ?, NOW())";
                // Prépare la requete SQL avec la connexion à la BDD ($mysqli)
                $appelSQL = $mysqli->prepare($requeteSQL);
                // Lie les valeurs du message (chaine de caractères) et de l'utilisateur à la requete
                // le 'si' veut dire 's' pour le premier parametre donc string et 'i' pour le deuxieme parametre donc integer
                $appelSQL->bind_param('si', $message, $userId);
                // Execute la requete
                $appelSQL->execute();
                header("Location: wall.php");
                exit();
        }}
        ?>

        <div id="wrapper">
            <aside>
                <?php              
                $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ($lesInformations) {
                    $user = $lesInformations->fetch_assoc();
                } else {
                    die("Erreur lors de l'exécution de la requête : " . $mysqli->error);
                }
                $user = $lesInformations->fetch_assoc();
                
                ?>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $userPseudo ?>
                        (n° <?php echo $userId ?>)
                    </p>
                </section>
            </aside>
            <main>
                <?php
                $laQuestionEnSql = "
                    SELECT posts.content, posts.created, users.alias as author_name, 
                    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }
                ?>

                <!-- Formulaire pour envoyer un message -->
                <form action="wall.php?user_id=<?php echo $userId; ?>" method="post">
                    <dl>
                        <dd><textarea name='message'></textarea></dd>
                    </dl>
                    <input type="submit" value="Poster">
                </form>

                <?php
                while ($post = $lesInformations->fetch_assoc())
                {
                ?>   
                    <article>
                        <h3>
                            <time datetime='2020-02-01 11:12:13' ><?php echo $post['created'] ?></time>
                        </h3>
                        <address>par <?php echo $post['author_name'] ?></address>
                        <div>
                            <p><?php echo $post['content'] ?></p>
                        </div>                                            
                        <footer>
                            <small>♥ <?php echo $post['like_number'] ?></small>
                            <a href="">#<?php echo $post['taglist'] ?></a>,
                        </footer>
                    </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
