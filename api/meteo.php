<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
$config = require 'config.php';

$dati = json_decode(file_get_contents("php://input"), true);

$lat = (float)($dati["lat"] ?? 0);
$lon = (float)($dati["lon"] ?? 0);

$url = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=" . $config["meteo_key"] . "&units=metric&lang=it";

@$risposta = file_get_contents($url);
if(!$risposta){
    echo json_encode("Impossibile trovare il meteo");
    exit;
}

$meteo = json_decode($risposta, true);

if (!isset($meteo["name"])){
   echo json_encode("Errore: impossibile aprire il meteo");
   exit;
}

    
$testo = $meteo["name"] . " - " . $meteo["weather"][0]["description"] . ", " . (int)$meteo["main"]["temp"] . "°C";
echo json_encode($testo);
