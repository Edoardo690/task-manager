<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
$username = $_SESSION["username"];

$dati = json_decode(file_get_contents("php://input"), true);

$id = (int)($dati["id"] ?? 0);
$completata = (int)($dati["completata"] ?? 0);

$query = $pdo->prepare("UPDATE attivita SET completata = ? WHERE id = ? AND username = ?");
$query->execute([$completata, $id, $username]);
echo json_encode("Attività aggiornata");
