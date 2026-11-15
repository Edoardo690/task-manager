<?php

require 'db.php';
header("Content-Type: application/json");
use PHPMailer\PHPMailer\PHPMailer;


require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$config = require 'config.php';
$dati = json_decode(file_get_contents("php://input"), true);

$email = trim($dati["email"] ?? "");

if ($email === ""){
    echo json_encode("Errore: compilare il campo email");
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode("Errore: email non valida, riprova");
    exit;
}
$query = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
$query->execute([$email]);
$row = $query->fetch();

if (!$row){
    echo json_encode("Se l'email è registrata riceverai un messaggio");
    exit;
}
function passwordCasuale($lunghezza = 16){
    $caratteri = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $password = "";
    for ($i = 0; $i < $lunghezza; $i++){
        $password .=$caratteri[random_int(0, strlen($caratteri) - 1)];
    }
    return $password;
}
$newPassword = passwordCasuale(16);
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

try{
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE utenti SET password = ? WHERE email = ?")->execute([$passwordHash, $email]);
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = $config["mail_user"];
    $mail->Password   = $config["mail_pass"];
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;
    $mail->CharSet    = "UTF-8";

    $mail->setFrom($config["mail_user"], "Task Manager");
    $mail->addAddress($email);
    $mail->Subject = "Password dimenticata";
    $mail->Body    = "Password sostitutiva: " . $newPassword;
    $mail->send();
    $pdo->commit();
    echo json_encode("Se l'email è registrata riceverai un messaggio");
}   catch (\Exception $e){
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log($e->getMessage());
    echo json_encode("Se l'email è registrata riceverai un messaggio");
    exit;
}


