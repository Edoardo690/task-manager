<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
$username = $_SESSION["username"];
$idUtente = $_SESSION["user_id"];
if (!isset($_FILES["immagine"]) || $_FILES["immagine"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode("Errore: file non ricevuto");
    exit;
}
if (getimagesize($_FILES["immagine"]["tmp_name"]) === false) {
    echo json_encode("Errore: il file non è un'immagine");
    exit;
}

$estensione = strtolower(pathinfo($_FILES["immagine"]["name"], PATHINFO_EXTENSION));
if (!in_array($estensione, ["jpg", "jpeg", "png", "gif", "webp"])) {
    echo json_encode("Errore: formato non consentito");
    exit;
}

$cartella = "../"."uploads/". $idUtente."/profile_image/";
if (!is_dir($cartella)){
    mkdir($cartella, 0777, true);
}


$pfpName = "pfp_". time() . "." . $estensione;
$percorsoDB = "uploads/". $idUtente."/profile_image/".$pfpName;
$percorsoDisco = "../" . $percorsoDB;   
$query = $pdo->prepare("SELECT profile_image FROM utenti WHERE id = ? AND username = ?");
$query->execute([$idUtente, $username]);
$row = $query->fetch();

try{
    if(!move_uploaded_file($_FILES["immagine"]["tmp_name"], $percorsoDisco)){
    echo json_encode("Errore: impossibile salvare il file!");
    exit;
    }
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE utenti SET profile_image = ? WHERE id = ? AND username = ?")->execute([$percorsoDB, $idUtente, $username]);
    $pdo->commit();
    if ($row && !empty($row["profile_image"]) && file_exists("../" . $row["profile_image"])) {unlink("../".$row["profile_image"]);}
    echo json_encode("Immagine profilo caricata con successo");
}   catch(Exception $e){
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    if (file_exists($percorsoDisco)){
        unlink($percorsoDisco);
    }
    
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}