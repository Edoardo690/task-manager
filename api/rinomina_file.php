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
$newName = trim($dati["newName"] ?? "");
$newName = str_replace(["/", "\\", ".."], "", $newName);
if ($newName === ""){
    echo json_encode("Errore, nome non valido"); 
    exit;
}

$query = $pdo->prepare("SELECT nome_file, percorso FROM file_utenti WHERE id=? AND username = ?");
$query->execute([$id, $username]);
$riga = $query->fetch();
if (!$riga){
    echo json_encode("Errore, file non trovato");
    exit;
}
$estensione = pathinfo($riga["nome_file"], PATHINFO_EXTENSION);
$newNameFile = $estensione !== "" ? $newName . "." . $estensione : $newName;

$cartella = dirname($riga["percorso"]). "/";
$vecchioPercorsoDisco = "../" . $riga["percorso"];
$nuovoPercorsoDB = $cartella.$newNameFile;
$nuovoPercorsoCartella = "../". $nuovoPercorsoDB;
if (!file_exists($vecchioPercorsoDisco)) {
    echo json_encode("Errore: il file non esiste sul disco");
    exit;
}
if ($newNameFile === $riga["nome_file"]) {
    echo json_encode("Il nome è già questo");
    exit;
}
if(file_exists($nuovoPercorsoCartella)){
    echo json_encode("Errore: esiste già un file con questo nome");
    exit;
}

try{
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE file_utenti SET nome_file = ?, percorso = ? WHERE id = ? AND username = ?")->execute([$newNameFile,$nuovoPercorsoDB, $id, $username]);

    if (!@rename($vecchioPercorsoDisco, $nuovoPercorsoCartella)){
        throw new Exception("Impossibile rinominare il file"); 
    }
    $pdo->commit();
    echo json_encode("File rinominato con sucesso");
}   catch(Exception $e){
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}
