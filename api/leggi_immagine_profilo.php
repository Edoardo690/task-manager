<?php
session_start();
require 'db.php';
header("Content-Type: application/json");

if (!isset($_SESSION["username"], $_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode("Errore: non sei loggato");
    exit;
}
$username = $_SESSION["username"];
$idUtente = $_SESSION["user_id"];
$query = $pdo->prepare("SELECT profile_image FROM utenti WHERE id = ? AND username = ?");
$query->execute([$idUtente, $username]);
$row = $query->fetch();
echo json_encode($row["profile_image"] ?? null);