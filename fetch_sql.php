<?php
function fetchPosts($mysqli, $userId) {

    $laQuestionEnSql = "
        SELECT posts.id, posts.content, posts.created, users.id, users.alias as author_name, 
               COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
        FROM posts
        JOIN users ON users.id=posts.user_id
        LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
        LEFT JOIN tags ON posts_tags.tag_id = tags.id 
        LEFT JOIN likes ON likes.post_id = posts.id 
        WHERE posts.wall_user_id = '$userId' OR posts.user_id = '$userId' 
        GROUP BY posts.id
        ORDER BY posts.created DESC
    ";

    // Exécution de la requête
    $result = $mysqli->query($laQuestionEnSql);
    if (!$result) {
        die("Erreur lors de la récupération des publications : " . $mysqli->error);
    }

    return $result;
}

function fetchUserById($mysqli, $userId) {
    $laQuestionEnSql = "SELECT * FROM users WHERE id = '$userId'";
    
    // Exécution de la requête
    $result = $mysqli->query($laQuestionEnSql);
    if (!$result) {
        die("Erreur lors de la récupération des informations de l'utilisateur : " . $mysqli->error);
    }

    return $result->fetch_assoc(); // Retourne les informations de l'utilisateur
}


function fetchPostsFollowed($mysqli, $userId) {
    $laQuestionEnSql = "
        SELECT 
            posts.id, 
            wall_user_id,
            posts.content,
            posts.user_id,
            posts.created,
            users.alias as author_name,  
            COUNT(likes.id) as like_number,  
            GROUP_CONCAT(DISTINCT tags.label) AS taglist 
        FROM followers 
        JOIN users ON users.id = followers.followed_user_id
        JOIN posts ON posts.user_id = users.id
        LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
        LEFT JOIN tags ON posts_tags.tag_id = tags.id 
        LEFT JOIN likes ON likes.post_id = posts.id 
        WHERE followers.following_user_id = '$userId' 
        GROUP BY posts.id
        ORDER BY posts.created DESC  
    ";

    // Exécution de la requête
    $result = $mysqli->query($laQuestionEnSql);
    if (!$result) {
        die("Erreur lors de la récupération des publications des utilisateurs suivis : " . $mysqli->error);
    }

    return $result;
}

?>
