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
    <link rel="stylesheet" href="style.css?v=1.0" />
    <!-- nouvelle version du CSS suite à ajout de la class sub-unsub-forms -->
</head>

<body>
    <?php include 'header.php'; ?>

    <div id="wrapper">

        <?php 
        // Gestion de la publication de message 

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
            }
        }

        //Gestion des abonnements et désabonnements 
        $messageApresClick = ''; // Variable pour stocker les messages d'abonnement
        
        // On vérifie si la méthode est POST et si le formulaire d'abonnement a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
            // on vérifie d'abord si l'abonnement existe déjà
            $checkFollowingSql = "SELECT * FROM followers WHERE followed_user_id = '$userId' AND following_user_id = '$userId'";
            $followingResult = $mysqli->query($checkFollowingSql);

            if ($followingResult->num_rows > 0) {
                //num_rows retourne le nombre de ligne du résultat de ma requête, si pas de ligne -> pas d'abonnement existant
                $messageApresClick = "Vous êtes déjà abonné."; 
            } else {

                // On insère le nouvel abonnement dans la BDD
                $requeteSQL = "INSERT INTO `followers`(`followed_user_id`, `following_user_id`)
                        VALUES ('$userId', '$userId')";
                $result = $mysqli->query($requeteSQL);
                if (!$result) {
                    $messageApresClick = "Erreur lors de l'abonnement : " . $mysqli->error;
                } else {
                    $messageApresClick = "Vous êtes maintenant abonné à $userPseudo ";
                }
            }
        }

        // On vérifie si la méthode est POST et si le formulaire de désabonnement a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsubscribe'])) {
            // On supprime l'abonnement de la BDD
            $requeteSQL = "DELETE FROM `followers`
                                        WHERE followed_user_id = '$userId' 
                                        AND following_user_id = '$userId'";
            $result = $mysqli->query($requeteSQL);
            if (!$result) {
                $messageApresClick = "Erreur lors du désabonnement : " . $mysqli->error;
            } else {
                $messageApresClick = "Vous vous êtes désabonné avec succès.";
            }
        }
        
        ?>
        <aside>
            <?php
            // Récupération des informations de l'utilisateur
            $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if ($lesInformations) {
                $user = $lesInformations->fetch_assoc();
            } else {
                die("Erreur lors de l'exécution de la requête : " . $mysqli->error);
            }
            ?>

            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez tous les messages de l'utilisatrice <?php echo $userPseudo ?>
                    (n° <?php echo $userId ?>)
                </p>
            </section>
            <section>
                <!-- Afficher le message d'abonnement/désabonnement si défini -->
                <?php if ($messageApresClick): ?>
                    <p><?php echo $messageApresClick; ?></p>
                <?php endif; ?>

                <!-- Formulaire pour s'abonner -->
                <div class="sub-unsub-forms">
                    <form action="wall.php" method="post">
                        <!-- Champ caché pour envoyer l'ID de l'utilisateur à suivre -->
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>" />
                        <input type="submit" name="subscribe" value="Je m'abonne">
                    </form>
                    <!-- Formulaire pour se désabonner -->
                    <form action="wall.php" method="post">
                        <!-- Champ caché pour envoyer l'ID de l'utilisateur à désabonner -->
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>" />
                        <input type="submit" name="unsubscribe" value="Je me désabonne">
                    </form>
                </div>
            </section>
        </aside>

        <main>
            <?php
            // Récupération des publications
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
            if (!$lesInformations) {
                echo ("Échec de la requete : " . $mysqli->error);
            }
            ?>

            <!-- Formulaire pour envoyer un message -->
            <form action="wall.php?user_id=<?php echo $userId; ?>" method="post">
                <dl>
                    <dd><textarea name='message' placeholder="Écris ton ptit mess ici..."></textarea></dd>
                </dl>
                <input type="submit" value="Poster">
            </form>
            <?php
            // Affichage des publications
            while ($post = $lesInformations->fetch_assoc()) {
                ?>
                <article>
                    <h3>
                        <time datetime='2020-02-01 11:12:13'><?php echo $post['created'] ?></time>
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
                <?php
            }
            ?>
        </main>
    </div>
</body>

</html>