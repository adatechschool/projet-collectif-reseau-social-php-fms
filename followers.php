<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mes abonnés </title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
            <?php include 'header.php';?>
        </header>
        <div id="wrapper"> 
        <?php
            $userId = intval($_GET['user_id']);
            ?>
            <?php      
                include 'vars.php'; 
            ?>         
            <aside>
            <?php

                $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                ?>
                <img src = "user.jpg" alt = "Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez la liste des personnes qui
                        suivent les messages de l'utilisatrice <?php echo  $user['alias']; ?>
                        n° <?php echo intval($_GET['user_id']) ?></p>

                </section>
            </aside>
            <main class='contacts'>
                <?php
                $userId = intval($_GET['user_id']);
                    $mysqli = new mysqli("localhost", "root", "", "socialnetwork");
                      $laQuestionEnSql = "
                    SELECT users.*
                    FROM followers
                    LEFT JOIN users ON users.id=followers.following_user_id
                    WHERE followers.followed_user_id='$userId'
                    GROUP BY users.id
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }
                while ( $followers = $lesInformations->fetch_assoc()) 
                {  
                ?>
                <article>
                    <img src="user.jpg" alt="blason"/>
                    <h3><?php echo $followers['alias'] ; ?></h3>
                    <p><?php echo "id: " . $followers['id'] ; ?></p>
                </article>
                <?php
                }
                ?>
            </main>
        </div>
    </body>
</html>
