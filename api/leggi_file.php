<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
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
