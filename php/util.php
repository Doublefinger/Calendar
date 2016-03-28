<?php
/**
 * Utility Functions
 */

function POST($param)
{
    $param = htmlentities($param);
    return isset($_POST[$param]) ? $_POST[$param] : null;
}

function GET($param)
{
    $param = htmlentities($param);
    return isset($_GET[$param]) ? $_GET[$param] : null;
}

function SESSION($param)
{
    $param = htmlentities($param);
    return isset($_SESSION[$param]) ? $_SESSION[$param] : null;
}

function validateUserName($username)
{
    if (!preg_match('/^[\w_\-]+$/', $username)) {
        return false;
    }
    return true;
}

function validateFileName($filename)
{
    if (!preg_match('/^[\w_\.\-]+$/', $filename)) {
        return false;
    }
    return true;
}

function validateExtension($ext)
{
    $allowed = array('gif', 'png', 'jpg');
    if (!in_array($ext, $allowed)) {
        return false;
    }
    return true;
}