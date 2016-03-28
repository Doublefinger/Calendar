<?php
/**
 * Created by PhpStorm.
 * User: Doublefinger
 * Date: 3/27/16
 * Time: 11:50 PM
 */
include "util.php";
require "database.php";

ini_set("session.cookie_httponly", 1);

session_start();
if (is_null(SESSION('user_name'))) {
    header("Location: login.php");
    exit;
}

$func = POST('func');
if (!is_null($func)) {
    switch ($func) {
        case 'create':
            create();
            break;
        case 'edit':
            edit();
            break;
        case 'delete':
            delete();
            break;
        case 'display':
            display();
            break;
    }
}

function create()
{
    if(SESSION('token') !== POST('token')){
        die("Request forgery detected");
    }
    $start = POST('start');
    $end = POST('end');
    $description = POST('description');
    if (is_null($start) || is_null($description)) {
        echo json_encode(array("success" => false));
        return;
    }
    $start = date("Y-m-d H:i:s", strtotime($start));
    if(!empty($end)) {
        $end = date("Y-m-d H:i:s", strtotime($end));
    }

    global $mysqli;

    //get userId by username
    $id = getUserId();

    //insert event into table

    $stmt = $mysqli->prepare("INSERT INTO Events (ownerId, start, end, description) values (?, ?, ?, ?)");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('isss', $id, $start, $end, $description);
    $stmt->execute();
    $stmt->close();
//    header("Content-Type: application/json");
//    echo json_encode(array("success" => true));
    echo true;
}

function edit()
{
    if(SESSION('token') !== POST('token')){
        die("Request forgery detected");
    }
    $id = POST('id');
    $start = POST('start');
    $end = POST('end');
    $description = POST('description');
    if (is_null($id) || is_null($start) || is_null($description)) {
        echo json_encode(array("success" => false));
        return;
    }
    $start = date("Y-m-d H:i:s", strtotime($start));
    if(!empty($end)) {
        $end = date("Y-m-d H:i:s", strtotime($end));
    }

    global $mysqli;

    //update event
    $stmt = $mysqli->prepare("UPDATE Events SET start = ?, end = ?, description = ? where id = ?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('sssi', $start, $end, $description, $id);
    $stmt->execute();
    $stmt->close();
    echo true;
}

function delete()
{
    if(SESSION('token') !== POST('token')){
        die("Request forgery detected");
    }
    $id = POST('id');
    if (is_null($id)) {
        echo json_encode(array("success" => false));
        return;
    }

    global $mysqli;

    $stmt = $mysqli->prepare("DELETE FROM Events where id = ?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    echo true;
}

function display()
{
    $month = POST('month');
    $date = POST('date');
    $year = POST('year');
    if (is_null($month) || is_null($date) || is_null($year)) {
        echo "fail";
        return;
    }

    $from = date(DATE_ATOM, mktime(0, 0, 0, $month+1, $date, $year));
    $to = date("Y-m-d H:i:s", strtotime($from. '+35 day'));

    $id = getUserId();

    global $mysqli;

    //get userId by username
    $events = array();
    $stmt = $mysqli->prepare("SELECT id, start, end, description FROM Events WHERE ownerId=? and start >= ? and start <= ?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }

    $stmt->bind_param('iss', $id, $from, $to);
    $stmt->execute();
    $stmt->bind_result($eventId, $start, $end, $description);
    $description =  htmlentities($description);

    while ($stmt->fetch()) {
        $event = array("id" => $eventId, "start" => $start, "end" => $end, "description" => $description);
        array_push($events, $event);
    }
    $stmt->close();
    header("Content-Type: application/json");
    echo json_encode($events);
}

function getUserId(){
    global $mysqli;

    //get userId by username
    $username = SESSION('user_name');
    $stmt = $mysqli->prepare("SELECT id FROM Users WHERE username=?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();
    return $id;
}