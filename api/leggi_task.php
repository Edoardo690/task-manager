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

$query = $pdo->prepare("SELECT attivita.id, attivita.testo, attivita.completata, attivita.data,
       file_utenti.id AS file_id, file_utenti.nome_file, file_utenti.percorso
       FROM attivita
       LEFT JOIN task_file ON attivita.id = task_file.task_id
       LEFT JOIN file_utenti ON task_file.file_id = file_utenti.id
       WHERE attivita.username = ?
       ORDER BY attivita.id");

$query->execute([$username]);
$righe = $query->fetchAll(PDO::FETCH_ASSOC);
$task_raggruppate = [];
foreach($righe as $i) {
    $id = $i['id'];
    if (!isset($task_raggruppate[$id])){
        $task_raggruppate[$id] = [
            'id' => $i['id'],
            'testo' => $i['testo'],
            'completata'=> $i['completata'],
            'data' => $i['data'],
            'files' => []
        ];  
    }

    if ($i['file_id'] !== null){
        $task_raggruppate[$id]['files'][]= [
            'file_id' => $i['file_id'],
            'nome_file' => $i['nome_file'],
            'percorso' => $i['percorso']
        ];
    }
}


echo json_encode(array_values($task_raggruppate));

