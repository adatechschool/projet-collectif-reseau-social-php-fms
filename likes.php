<?php
include 'connect.php';
 // Gestion des Likes 
        // Si la méthode est POST et que le champ 'message' existe
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
            $postId = $_POST['like'];
            
            // Vérifie si l'utilisateur a déjà liké le post
            $checkLikeSQL = "SELECT * FROM likes WHERE user_id = '$userId' AND post_id = '$postId'";
            $likeResult = $mysqli->query($checkLikeSQL);

            if ($likeResult->num_rows > 0) {
                // Si un like existe déjà, ne pas ajouter de nouveau like
                $messageApresClick = "Vous avez déjà liké ce post.";
            } else {
                // Ajouter un nouveau like
                $requeteSQL = "INSERT INTO likes (user_id, post_id) VALUES ('$userId', '$postId')";
                $result = $mysqli->query($requeteSQL);
                if (!$result) {
                    $messageApresClick = "Erreur lors de l'ajout du like : " . $mysqli->error;
                } else {
                    $messageApresClick = "Vous avez liké ce post.";

                }
            }
            header("Location: ". $_SERVER['REQUEST_URI']);
          exit();
        }