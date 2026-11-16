<?php
require 'db.php';
header("Content-Type: application/json");

$query = $pdo->prepare("DELETE FROM tentativi WHERE data_tentativo < (NOW() - INTERVAL 1 DAY)");
$query->execute();
echo json_encode("sucesso");