<?php
require 'db.php';
session_start();
header("Content-Type: application/json");

$dati = json_decode(file_get_contents("php://input"), true);

$username = trim($dati["username"] ?? "");
$password = $dati["password"] ?? "";
$email = trim($dati["email"] ?? "");

if ($username === "" || $password === "" || $email === ""){
    echo json_encode("Errore, scrivere qualcosa nei campi");
    exit;
}
if (mb_strlen($username) > 50 || mb_strlen($email) > 100) {
    echo json_encode("Errore: username o email troppo lunghi");
    exit;
}
if (strlen($password) < 8) {
    echo json_encode("Errore: la password deve avere almeno 8 caratteri");
    exit;
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode("Email non valida");
    exit;
}

$passwordCriptata = password_hash($password, PASSWORD_DEFAULT);

try{
    $query = $pdo->prepare("INSERT INTO utenti (username, password, email) VALUES(?,?,?)");
    $query->execute([$username, $passwordCriptata, $email]);
    session_regenerate_id(true);
    $_SESSION["username"] = $username;
    $_SESSION["user_id"] = $pdo->lastInsertId();
    echo json_encode("Registrazione completata");
}   catch (PDOException $e) {
    if ($e->getCode() === "23000") {
        echo json_encode("Username o email già in uso");
    } else {
        error_log($e->getMessage());
        echo json_encode("Errore del server");
    }
}