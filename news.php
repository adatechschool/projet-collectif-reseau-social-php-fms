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
    <title>ReSoC - Actualités</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <?php include 'header.php'; 
    $user = fetchUserById($mysqli, $userId);
    $lesInformations = fetchPosts($mysqli, $userId);
    ?>

    <div id="wrapper">
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les derniers messages de
                    tous les utilisatrices du site.</p>
            </section>
        </aside>
        <main>
            <?php 
                include 'view_posts.php';
            ?>
        </main>
    </div>
</body>

</html>