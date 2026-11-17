<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
$username = $_SESSION["username"];
$idUtente = $_SESSION["user_id"];
$query = $pdo->prepare("SELECT profile_image FROM utenti WHERE id = ? AND username = ?");
$query->execute([$idUtente, $username]);
$row = $query->fetch();
echo json_encode($row["profile_image"] ?? null);