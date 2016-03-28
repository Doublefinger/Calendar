<?php
include 'util.php';
require 'database.php';

$register = POST('register');
$username = $mysqli->real_escape_string(trim(POST('username')));
$password = $mysqli->real_escape_string(POST('password'));

if ($register == 1) {
    $location = "Location: register.php";
} else {
    $location = "Location: login.php";
}

if (is_null($username)) {
    header($location . "?fail=1");
    exit;
}

if (is_null($password) || strlen($password) < 6) {
    header($location . "?fail=3");
    exit;
}
session_start();
if ($register == 1) {
//    if(validateUserName($username)){
//        header($location . "?fail=2");
//        exit;
//    }
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM Users WHERE username=?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($cnt);
    $stmt->fetch();
    if ($cnt > 0) {
        header($location . "?fail=4");
        exit;
    }
    $stmt->close();

    $pwd_hash = crypt($password);
    //check if username already exists
    $stmt = $mysqli->prepare("INSERT INTO Users (username, password) values (?, ?)");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('ss', $username, $pwd_hash);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user_name'] = $username;
    $_SESSION['token'] = substr(md5(rand()), 0, 10);
} else {
    $stmt = $mysqli->prepare("SELECT COUNT(*), id, password FROM Users WHERE username=?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    // Bind the parameter
    $stmt->bind_param('s', $username);
    $stmt->execute();

    // Bind the results
    $stmt->bind_result($cnt, $user_id, $pwd_hash);
    $stmt->fetch();
    $stmt->close();
    // Compare the submitted password to the actual password hash
    if ($cnt == 1 && crypt($password, $pwd_hash) == $pwd_hash) {
        // Login succeeded!
        $_SESSION['user_name'] = $username;
        $_SESSION['token'] = substr(md5(rand()), 0, 10);
    } else {
        // Login failed; redirect back to the login screen
        header($location . "?fail=4");
        exit;
    }

}

header("Location: ../index.html");
exit;
