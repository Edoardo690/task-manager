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

$dati = json_decode(file_get_contents("php://input"), true);

$id = (int)($dati["id"] ?? 0);

try{
    $query = $pdo->prepare("DELETE FROM attivita WHERE id = ? AND username = ?");
    $query->execute([$id, $username]);
    if ($query->rowCount() === 0) {
        echo json_encode("Errore: task non trovata");
        exit;
    }
    echo json_encode("Attività eliminata");
}   catch (Exception $e){
    error_log($e->getMessage());
    echo json_encode("Errore del server");
}
