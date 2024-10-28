<?php
require('db.php');

function registerUser($username, $email, $password) {
    global $con;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $con->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    return $stmt->execute();
}

function loginUser($username, $password) {
    global $con;
    $stmt = $con->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user; // Return user info on success
        }
    }
    return false; // Return false on failure
}

function getUserRole($userId) {
    global $con;
    $stmt = $con->prepare("SELECT role FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['role'] ?? null;
}

function updateUserProfile($userId, $email, $password = null) {
    global $con;
    $updateQuery = "UPDATE users SET email=?";

    if ($password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery .= ", password=?";
        $stmt = $con->prepare($updateQuery . " WHERE id=?");
        $stmt->bind_param("ssi", $email, $hashedPassword, $userId);
    } else {
        $stmt = $con->prepare($updateQuery . " WHERE id=?");
        $stmt->bind_param("si", $email, $userId);
    }

    return $stmt->execute();
}
?>