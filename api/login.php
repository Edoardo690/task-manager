<?php
session_start();
require 'db.php';

header("Content-Type: application/json");

$dati = json_decode(file_get_contents("php://input"), true);
$username = trim($dati["username"] ?? "");
$password = $dati["password"] ?? "";
if ($username === ""|| $password === ""){
    echo json_encode("Compila tutti i campi");
    exit;
}

$query = $pdo->prepare("SELECT password, id FROM utenti WHERE username = ?");
$query->execute([$username]);
$riga = $query->fetch();


if ($riga === false || !password_verify($password, $riga["password"])) {
    echo json_encode("Password o username errati");
    exit;
}
else{
    session_regenerate_id(true);
    $_SESSION["username"] = $username;
    $_SESSION["user_id"] = $riga["id"];
    $_SESSION["username"] = $username;
    $_SESSION["user_id"] = $riga["id"];
    echo json_encode("Login in corso....");
}


