<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
$username = $_SESSION["username"];

$dati = json_decode(file_get_contents("php://input"), true);

$oldPassword = $dati["oldPassword"] ?? "";
$newPassword = $dati["newPassword"] ?? "";
$newUsername = trim($dati["newUsername"] ?? "");
if ($oldPassword === ""){
    echo json_encode("Errore: inserisci la tua vecchia password");
    exit;
}
$query = $pdo->prepare("SELECT password FROM utenti WHERE username = ?");
$query->execute([$username]);
$password = $query->fetch();
if ($password === false) {
    http_response_code(401);
    echo json_encode("Errore: utente non trovato");
    exit;
}

if(!(password_verify($oldPassword, $password["password"]))){
    echo json_encode("Password errata");
    exit;
}

if ($newUsername === "" and $newPassword === ""){
    echo json_encode("Errore, compilare almeno un campo");
    exit;
}

try{
    $pdo->beginTransaction();
    if ($newUsername !== "" && $newUsername !== $username){
        $query = $pdo->prepare("SELECT id FROM utenti WHERE username = ?");
        $query->execute([$newUsername]);
        if ($query->fetch()){
            $pdo->rollBack();
            echo json_encode("Errore username già esistente");
            exit;
        }
        else{
            $pdo-> prepare("UPDATE utenti SET username = ? WHERE username = ?")->execute([$newUsername, $username]);
            $pdo-> prepare("UPDATE attivita SET username = ? WHERE username = ?")->execute([$newUsername, $username]);
            $pdo-> prepare("UPDATE file_utenti SET username = ? WHERE username = ?")->execute([$newUsername, $username]);

            $username = $newUsername;     
        }   
    }
    if (!($newPassword === "")){
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo-> prepare("UPDATE utenti SET password = ? WHERE username = ?")->execute([$hash, $username]);
    }
    $pdo->commit();
    $_SESSION["username"] = $username;
}   catch (PDOException $e){
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}
    
echo json_encode("Modifiche salvate con successo");
