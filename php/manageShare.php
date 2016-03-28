<?php
/**
 * Created by PhpStorm.
 * User: Doublefinger
 * Date: 3/28/16
 * Time: 10:39 AM
 */

include "manageEvent.php";

ini_set("session.cookie_httponly", 1);

session_start();
if (is_null(SESSION('user_name'))) {
    header("Location: login.php");
    exit;
}

$func = POST('func');
if (!is_null($func)) {
    switch ($func) {
        case 'getList':
            getList();
            break;
        case  'share':
            share();
            break;
    }
}

function share()
{
    $friend = POST('friend');
    if(is_null($friend)){
        echo json_encode(array("success" => false));
        return;
    }

    global $mysqli;
    $id = getUserId(SESSION('user_name'));
    $friendId = getUserId($friend);

    $stmt = $mysqli->prepare("INSERT INTO Shares (userAId, userBId) values (?, ?)");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('ii', $id, $friendId);
    $stmt->execute();
    $stmt->close();
//    header("Content-Type: application/json");
//    echo json_encode(array("success" => true));
    echo "success";
}

function getList()
{
    $id = getUserId(SESSION('user_name'));
    $list = array();
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT userAId FROM Shares WHERE userBId = ?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($Aid);

    while ($stmt->fetch()) {
        array_push($list, $Aid);
    }
    $stmt->close();

    $name_list = array();
    foreach($list as $arr){
        $stmt = $mysqli->prepare("SELECT username FROM Users WHERE id = ?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('i', $arr);
        $stmt->execute();
        $stmt->bind_result($name);
        $stmt->fetch();
        $stmt->close();
        array_push($name_list, $name);
    }
    header("Content-Type: application/json");
    echo json_encode($name_list);
}