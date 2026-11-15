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

$query = $pdo->prepare("SELECT percorso FROM file_utenti WHERE username = ?");
$query->execute([$username]);
$files = $query->fetchAll();

if (!$files){
    echo json_encode("Nessun file da eliminare");
    exit;
}

try{
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE attivita SET file_id = NULL WHERE username = ?")->execute([$username]);
    $pdo->prepare("DELETE FROM file_utenti WHERE username = ?")->execute([$username]);

    $pdo->commit();
    foreach($files as $i){
        $percorsoDisco = "../" . $i["percorso"];
        if (file_exists($percorsoDisco)){
            unlink($percorsoDisco);
        }
    }
    echo json_encode("File eliminati con successo");
}   catch (Exception $e){
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}