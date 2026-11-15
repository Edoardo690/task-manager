# Task Manager

Sito web per gestire le proprie attività, con registrazione e login, caricamento di file e meteo.
Frontend in HTML, CSS e JavaScript; backend in PHP con database MySQL/MariaDB.

> Il progetto è nato in Python (con la libreria Eel e SQLite) ed è stato poi convertito interamente in PHP.

## Funzionalità

- Registrazione e login con sessioni (password salvate con hash)
- Creazione, completamento ed eliminazione delle attività
- Caricamento, rinomina ed eliminazione di file, con possibilità di associarli a un'attività
- Immagine del profilo
- Cambio di username e password dalle impostazioni
- Recupero password via email (PHPMailer)
- Meteo tramite OpenWeatherMap

## Requisiti

- [XAMPP](https://www.apachefriends.org) (Apache, PHP e MySQL/MariaDB)
- Una chiave API gratuita di [OpenWeatherMap](https://openweathermap.org/api)
- Un account Gmail con una *password per le app* (serve solo per il recupero password via email)

## Installazione

1. Copia la cartella del progetto in `C:\xampp\htdocs\` (es. `C:\xampp\htdocs\taskmanager`).
2. Avvia **Apache** e **MySQL** dal pannello di controllo di XAMPP.
3. Apri phpMyAdmin (`http://localhost/phpmyadmin`), crea un database chiamato `taskmanager` e importa il file `database.sql`.
4. Nella cartella `api/` copia `config.example.php` in `config.php` e compila i valori:

   ```php
   return [
       "meteo_key" => "LA_TUA_CHIAVE_OPENWEATHERMAP",
       "mail_user" => "latua@email.com",
       "mail_pass" => "PASSWORD_PER_LE_APP_DI_GOOGLE"
   ];
   ```

5. Controlla che i dati di connessione in `api/db.php` corrispondano al tuo MySQL (in XAMPP di default: utente `root`, password vuota).
6. Apri `http://localhost/taskmanager/` nel browser.

La cartella `uploads/` viene creata automaticamente al primo caricamento di un file.

## Struttura del progetto

```
taskmanager/
├── index.html          # pagina principale
├── script.js           # logica del frontend, comunica con le API PHP
├── style.css           # stile
├── database.sql        # struttura del database
├── uploads/            # file degli utenti (non incluso nel repository)
└── api/                # backend PHP
    ├── db.php          # connessione al database
    ├── config.php      # chiavi e credenziali (non incluso nel repository)
    ├── login.php, registrazione.php, logout.php, sessione.php
    ├── aggiungi_task.php, leggi_task.php, completa_task.php, elimina_task.php
    ├── carica_file.php, leggi_file.php, rinomina_file.php, elimina_file.php, ...
    ├── impostazioni.php, recupera_password.php, meteo.php
    └── PHPMailer/      # libreria per l'invio delle email
```

## Note

I file caricati e le credenziali in `config.php` sono esclusi dal repository tramite `.gitignore`.
Il progetto è pensato per l'uso in locale e a scopo di studio.
