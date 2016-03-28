<?php
/**
 * Created by PhpStorm.
 * User: Doublefinger
 * Date: 3/28/16
 * Time: 2:53 AM
 */
include ("util.php");
ini_set("session.cookie_httponly", 1);
session_start();

echo json_encode(array("token" => SESSION('token')));