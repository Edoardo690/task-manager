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

try{
    $query = $pdo->prepare("SELECT id, nome_file, percorso, data_caricamento FROM file_utenti WHERE username = ? ORDER BY data_caricamento DESC");
    $query->execute([$username]);
    $file = $query->fetchAll();
    echo json_encode($file);
}   catch(PDOException $e){
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode("Errore del server");
}
