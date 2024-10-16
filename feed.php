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
        $user = fetchUserById($mysqli, $userId);
        $lesInformations = fetchPostsFollowed($mysqli, $userId);
        ?>
            <?php 
            include 'view_posts.php';
            ?>
        </main>
    </div>
</body>

</html>