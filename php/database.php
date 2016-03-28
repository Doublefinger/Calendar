<?php
/**
 * Created by PhpStorm.
 * User: Doublefinger
 * Date: 2/21/16
 * Time: 1:45 PM
 */

$mysqli = new mysqli('localhost', 'visitor', 'cse330', 'mod5_group');

if($mysqli->connect_errno) {
    printf("Connection Failed: %s\n", $mysqli->connect_error);
    exit;
}