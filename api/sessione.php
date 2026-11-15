<?php
session_start();

header("Content-Type: application/json");

if (isset($_SESSION["username"], $_SESSION["user_id"])) {
    echo json_encode(["loggato" => true, "username" => $_SESSION["username"]]);
    exit;
}
else{
    echo json_encode(["loggato" => false]);
}