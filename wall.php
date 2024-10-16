<?php
include 'session.php';
include 'connect.php';
include 'likes.php';
include 'fetch_sql.php';
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
        // Arriver sur la page d'un autre utilisateur
        // Vérifie si un user_id est présent dans l'URL
        if (isset($_GET['user_id'])) {
            $userId = $_GET['user_id']; // ID de l'utilisateur dans l'URL
            // } else {
            //     $userId = $_SESSION['connected_id']; // Si pas d'ID dans l'URL, on utilise l'ID de l'utilisateur connecté
        }

        $userId = $mysqli->real_escape_string($userId); // Sécurisation de l'ID
        
        $sql = "SELECT * FROM users WHERE id = '$userId'";
        $result = $mysqli->query($sql);


        // Gestion de la publication de message 
        
        // Si la méthode est POST et que le champ 'message' existe 
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $message = $_POST['message'];

            if (!empty($message)) {
                $authorId = $_SESSION['connected_id']; // Ton propre ID
                echo "<pre>" . "ID DE LA PERSONNE CONNECTEE = " . $authorId . "</pre>";

                var_dump($authorId);
                $wallUserId = $userId; // L'ID de l'utilisateur sur lequel tu es
        
                // Prépare la requête SLQ pour mettre le message dans la BDD
                //les valeurs en '?' ne sont définies qu'après avec le bind_param
                $requeteSQL = "INSERT INTO posts (content, user_id, wall_user_id, created) VALUES (?, ?, ?, NOW())";
                // Prépare la requete SQL avec la connexion à la BDD ($mysqli)
                $appelSQL = $mysqli->prepare($requeteSQL);
                // Lie les valeurs du message (chaine de caractères) et de l'utilisateur à la requete
                // le 'si' veut dire 's' pour le premier parametre donc string et 'i' pour le deuxieme parametre donc integer
                $appelSQL->bind_param('sii', $message, $authorId, $wallUserId);
                // Execute la requete
                $appelSQL->execute();
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        }

        //Gestion des abonnements et désabonnements 
        $messageApresClick = ''; // Variable pour stocker les messages d'abonnement
        
        // On vérifie si la méthode est POST et si le formulaire d'abonnement a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
            $connectedUserId = $_SESSION['connected_id']; // Utilisateur connecté
            // on vérifie d'abord si l'abonnement existe déjà
            $checkFollowingSql = "SELECT * FROM followers WHERE followed_user_id = '$userId' AND following_user_id = '$connectedUserId'";
            $followingResult = $mysqli->query($checkFollowingSql);

            if ($followingResult->num_rows > 0) {
                //num_rows retourne le nombre de ligne du résultat de ma requête, si pas de ligne -> pas d'abonnement existant
                $messageApresClick = "Vous êtes déjà abonné.";
            } else {

                // On insère le nouvel abonnement dans la BDD
                $requeteSQL = "INSERT INTO `followers`(`followed_user_id`, `following_user_id`)
                        VALUES ('$userId', '$connectedUserId')";
                $result = $mysqli->query($requeteSQL);
                if (!$result) {
                    $messageApresClick = "Erreur lors de l'abonnement : " . $mysqli->error;
                } else {
                    $messageApresClick = "Vous êtes maintenant abonné";
                }
            }
        }

        // On vérifie si la méthode est POST et si le formulaire de désabonnement a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsubscribe'])) {
            $connectedUserId = $_SESSION['connected_id']; // Utilisateur connecté
            // On supprime l'abonnement de la BDD
            $requeteSQL = "DELETE FROM `followers`
                                        WHERE followed_user_id = '$userId' 
                                        AND following_user_id = '$connectedUserId'";
            $result = $mysqli->query($requeteSQL);
            if (!$result) {
                $messageApresClick = "Erreur lors du désabonnement : " . $mysqli->error;
            } else {
                $messageApresClick = "Vous vous êtes désabonné avec succès.";
            }
        }

        ?>
        <?php 
        $user = fetchUserById($mysqli, $userId);
        $lesInformations = fetchPosts($mysqli, $userId);
        ?>
        <aside>
            <!-- <h2><?php echo "<pre>" . "userId lié à la page actuellement : " . $userId . "</pre>"; ?></h2> -->
            <!-- <h2><?php echo "<pre>" . "Confirmation de l'id du user connecté : " . $authorId . "</pre>"; ?></h2> -->
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez tous les messages de l'utilisatrice <?php echo ($user['alias']) ?>
                    (n° <?php echo $user['id'] ?>)
                </p>
            </section>
            <section>
                <!-- Afficher le message d'abonnement/désabonnement si défini -->
                <?php if ($messageApresClick): ?>
                    <p><?php echo $messageApresClick; ?></p>
                <?php endif; ?>

                <!-- Formulaire pour s'abonner -->
                <div class="sub-unsub-forms">
                    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                        <!-- Champ caché pour envoyer l'ID de l'utilisateur à suivre -->
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>" />
                        <input type="submit" name="subscribe" value="Je m'abonne">
                    </form>
                    <!-- Formulaire pour se désabonner -->
                    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                        <!-- Champ caché pour envoyer l'ID de l'utilisateur à désabonner -->
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>" />
                        <input type="submit" name="unsubscribe" value="Je me désabonne">
                    </form>
                </div>
            </section>
        </aside>

        <main>

            <!-- Formulaire pour envoyer un message -->
            <form action="wall.php?user_id=<?php echo $userId; ?>" method="post">
                <dl>
                    <dd><textarea name='message' placeholder="Écris ton ptit mess ici..."></textarea></dd>
                </dl>
                <input type="submit" value="Poster">
            </form>
            <?php 
            include 'view_posts.php';
            ?>
        </main>
    </div>
</body>

</html>