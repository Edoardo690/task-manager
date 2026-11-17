<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
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