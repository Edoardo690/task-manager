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


if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK){
    echo json_encode("Errore: file non ricevuto");
    exit;
}
$LIMITE_FILE   = 18 * 1024 * 1024;
$LIMITE_TOTALE = 400 * 1024 * 1024;
$LIMITE_NUMERO = 100;
$dimensione = $_FILES["file"]["size"];


$nomeFile = basename($_FILES["file"]["name"]);

$estensione = strtolower(pathinfo($nomeFile, PATHINFO_EXTENSION));
$vietate = ["php", "phtml", "phar", "php3", "php4", "php5", "php7", "html", "htm", "js", "exe", "bat","htaccess","pht", "phps", "shtml"];
if (in_array($estensione, $vietate)){
    echo json_encode("Errore: tipo di file non consentito");
    exit;
}
if ($_FILES["file"]["size"] >= $LIMITE_FILE) {
    echo json_encode("Errore: file troppo grande (massimo 18 MB)");
    exit;
}

$query = $pdo->prepare("SELECT COUNT(*) AS numero, COALESCE(SUM(dimensione), 0) AS totale FROM file_utenti WHERE username = ?");
$query->execute([$username]);
$stato = $query->fetch();

if($stato["numero"]>= $LIMITE_NUMERO){
    echo json_encode(["messaggio" => "Errore: superato il numero massimo di file", "stop" => true]);
    exit;
}
if($stato["totale"]+ $dimensione > $LIMITE_TOTALE){
    echo json_encode(["messaggio" => "Errore: spazio esaurito", "stop" => true]);
    exit;
}


$cartella = "../uploads/" . $idUtente . "/files/";
if (!is_dir($cartella)){
    mkdir($cartella, 0777, true);
}

$destinazione = $cartella . $nomeFile;
if (file_exists($destinazione)){
    echo json_encode("Errore: file già esistente");
    exit;
}
if(!move_uploaded_file($_FILES["file"]["tmp_name"], $destinazione)){
    echo json_encode("Errore: impossibile salvare il file!");
    exit;
}
$percorsoDb = "uploads/" . $idUtente . "/files/" . $nomeFile;
$data = date("Y-m-d H:i:s");





try{
    $query = $pdo->prepare("INSERT INTO file_utenti (username, nome_file, percorso, data_caricamento, dimensione) VALUES (?, ?, ?, ?, ?)");
    $query->execute([$username, $nomeFile, $percorsoDb, $data, $dimensione]);
    echo json_encode("File caricato con successo!");
}   catch (PDOException $e){
    if (file_exists($destinazione)) { unlink($destinazione); }
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}