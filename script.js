const iconaOcchioAperto = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
const iconaOcchioChiuso = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
let utenteCorrente = "";
let currentFileId = null;
let currentOldFileName = null;
let currentTaskID = null;

function escapeHtml(testo) {
    return String(testo ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}
async function chiamaApi(url, dati = null) {
    const opzioni = dati === null
        ? {}
        : {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(dati)
        };
    const risposta = await fetch(url, opzioni);
    
    if (risposta.status === 401) {
        mostraSezione("sezione-login");
        return "Sessione scaduta, accedi di nuovo";
    }
    const testo = await risposta.text();
    try {
        return JSON.parse(testo);
    } catch (e) {
        console.error("Risposta non valida da " + url + ":", testo);
        return "Errore del server";
    }
    
}
async function chiamaApiFile(url, formData) {
    const risposta = await fetch(url, {method: "POST", body: formData});
    if (risposta.status === 401) {
        mostraSezione("sezione-login");
        return "Sessione scaduta, accedi di nuovo";
    }
    const testo = await risposta.text();
    try {
        return JSON.parse(testo);
    } catch (e) {
        console.error("Risposta non valida da " + url + ":", testo);
        return "Errore del server";
    }
}
function mostraSezione(idSezioneDaMostrare) {
    const sezioni = ["sezione-login", "sezione-register", "dashboard","sezione-benvenuto","sezione-password-dimenticata", "sezione-impostazioni","sezione-file"];
    
    sezioni.forEach(function(id) {
        let elemento = document.getElementById(id);
        if (elemento) {
            elemento.classList.add("nascondi");
            let allInput = document.querySelectorAll("input");
            if (allInput.length > 0){
                allInput.forEach(function(input){
                    input.value = "";
            });
            }
    
            
        
        let allMex = elemento.querySelector("#messaggio-login, #messaggio-register, #messaggio-dashboard, #mex");
            if (allMex){
                allMex.innerText = "";
            }
        }
    });
    let sezioneAttiva = document.getElementById(idSezioneDaMostrare);
    if (sezioneAttiva) {
        sezioneAttiva.classList.remove("nascondi");
    }
    
}


async function accedi() {
    let usernameScritto = document.getElementById("login-user").value;
    let passwordScritta = document.getElementById("login-password").value;
    
    let risposta = await chiamaApi("api/login.php", {username: usernameScritto, password: passwordScritta});
    let mex = document.getElementById("messaggio-login");
    mex.innerText = risposta;

    if (risposta.includes("Login")){
        utenteCorrente = usernameScritto
        mex.style.color = "green";
        setTimeout(function(){
            mostraSezione("dashboard");
            caricaTask();
            caricaMeteoAutomatico();
            displayProfileImage();
            displayAllFiles();
        }, 1500);
        
    }
    else {
        mex.style.color = "red";
    }
}

async function mostraRecuperoPassword() {
    let email = document.getElementById("forgot-email").value;
    let mex = document.getElementById("messaggio-password-dimenticata");
    mex.innerText = "Invio in corso...";
    let risposta = await chiamaApi("api/recupera_password.php", {email: email});
    mex.innerText = risposta;
}


async function register() {
    let utente = document.getElementById("register-user").value;
    let password = document.getElementById("register-password").value;
    let email = document.getElementById("register-email").value;

    let risposta = await chiamaApi("api/registrazione.php", {username: utente, password: password, email: email});
    let mex = document.getElementById("messaggio-register");
    mex.innerText = risposta;
    if (risposta.includes("completata")){
        mex.style.color = "green";
        document.getElementById("register-user").value = "";
        document.getElementById("register-password").value = "";
        setTimeout(function(){
            mostraSezione("dashboard");
            caricaTask();
            caricaMeteoAutomatico();
        }, 1500);
    }
    else {
        mex.style.color = "red";
    }
}
async function addTask() {
    let newTask = document.getElementById("input-new-task").value;
    let mex = document.getElementById("messaggio-dashboard");
    let risposta = await chiamaApi("api/aggiungi_task.php", {testo: newTask});
    document.getElementById("input-new-task").value = "";
    mex.innerText = risposta;
    caricaTask()
}

async function caricaTask() {
    let taskList = await chiamaApi("api/leggi_task.php");
    if (!Array.isArray(taskList)) return;
    let contenitore = document.getElementById("contenitore-task");
    if (!contenitore) return;
    contenitore.innerHTML = "";

    for (let i = 0; i < taskList.length; i++){
        let currentTask = taskList[i];
        let idTask    = currentTask.id;
        let testoTask = escapeHtml(currentTask.testo);
        let completata = currentTask.completata;
        let dataTask  = escapeHtml(currentTask.data);
        let fileId    = currentTask.file_id;
        let nomeFile  = escapeHtml(currentTask.nome_file);
        let filePath  = currentTask.percorso;
        let encodedPath = filePath ? encodeURI(filePath) : "";
        
        let rigaDiv = document.createElement("div");
        rigaDiv.className = `task-item ${completata ? 'task-completata' : ''}`;

        // Se c'è un file associato, creiamo il badge visivo con il nome del file
        let fileBadgeHTML = "";
        if (fileId && nomeFile && filePath) {
            fileBadgeHTML = `
                <span class="task-file-badge" title="Allegato: ${nomeFile}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    ${nomeFile}
                </span>

                <a href="${encodedPath}" download="${nomeFile}" title="Scarica file" style="display: block; width: 100%; height: 100%;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </a>
            `;
        }

        rigaDiv.innerHTML = `
            <button class="btn-icona-elimina" onclick="eliminaTask(${idTask})" title="Elimina attività">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </button>
            <input type="checkbox" class="task-checkbox" ${completata ? 'checked' : ''} onchange="changeComplited(${idTask}, ${completata})">
            <span class="task-testo">${testoTask}</span>
            <span class="task-data">(${dataTask})</span>
            
            ${fileBadgeHTML}
            
            

            <button class="btn-collega-file" onclick="apriModalSelezionaFile(${idTask})" title="Collega file">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                </svg>
            </button>
        `;
    
        contenitore.appendChild(rigaDiv);
    }
}
async function aggiornaProfilo(){
    let newUsername = document.getElementById("nuovo-username").value;
    let oldPassword = document.getElementById("vecchia-password").value;
    let newPassword = document.getElementById("nuova-password").value;
    let mex = document.getElementById("messaggio-impostazioni");
    let risposta = await chiamaApi("api/impostazioni.php", {
    newUsername: newUsername,
    oldPassword: oldPassword,
    newPassword: newPassword
    });
    
    if (risposta.includes("successo")){
        if (newUsername && newUsername.trim() !== "") {
            utenteCorrente = newUsername;
        }
    }
    if (mex) {
        mex.innerText = risposta;
        mex.style.color = risposta.includes("successo") ? "green" : "red";
    }
}

async function changeComplited(id, completata) {
    let nuovoStato = completata === 0 ? 1 : 0;
    await chiamaApi("api/completa_task.php", {id: id, completata: nuovoStato});
    caricaTask()
}
async function eliminaTask(id) {
    await chiamaApi("api/elimina_task.php", {id: id});
    caricaTask();
}




document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('input[type="password"]').forEach(input => {
        let wrapper = document.createElement("div");
        wrapper.className = "input-password-container";
        
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        let eye = document.createElement("span");
        eye.className = "tasto-occhio";
        eye.innerHTML = iconaOcchioAperto; // Inserisce l'icona SVG iniziale
        
        eye.onclick = function() {
            if (input.type === "password") {
                input.type = "text";
                eye.innerHTML = iconaOcchioChiuso; // Cambia con l'icona sbarrata
            } else {
                input.type = "password";
                eye.innerHTML = iconaOcchioAperto; // Torna all'occhio normale
            }
        };
        
        wrapper.appendChild(eye);
    });
});

function toggleDarkMode() {
    document.body.classList.toggle("dark-mode");
    
    let isDark = document.body.classList.contains("dark-mode");
    localStorage.setItem("darkMode", isDark);

    let checkboxTema = document.getElementById("checkbox-tema");
    if (checkboxTema){
        localStorage.setItem("darkmode", checkboxTema.checked);
    }
}

function caricaMeteoAutomatico() {
    let badgeMeteo = document.getElementById("widget-meteo");
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (position) => {
            let lat = position.coords.latitude;
            let lon = position.coords.longitude;

            let risposta = await chiamaApi("api/meteo.php", {lat: lat, lon: lon});

            
            if (badgeMeteo) {
                badgeMeteo.innerText = risposta;
            }
        }, (error) => {
            
            if (badgeMeteo) {
                badgeMeteo.innerText = "Posizione non consentita";
            }
        });
    } else {
        if (badgeMeteo) {
            badgeMeteo.innerText = "Geolocalizzazione non supportata";
        }
    }
}

window.onclick = function(event) {
    if (!event.target.closest('.avatar-container')) {
        let menu = document.getElementById("menu-tendina-profilo");
        if (menu && !menu.classList.contains('nascondi')) {
            menu.classList.add('nascondi');
        }
    }
}
window.onload = async function() {
   
    let savedDarkMode = localStorage.getItem("darkMode");

    if (savedDarkMode === "true") {
        document.body.classList.add("dark-mode");
    } else {
        document.body.classList.remove("dark-mode");
    }

    let sessione = await chiamaApi("api/sessione.php");
    if (sessione.loggato) {
        utenteCorrente = sessione.username;
        mostraSezione("dashboard");
        caricaTask();
        caricaMeteoAutomatico();
        displayProfileImage();
        displayAllFiles();
    }
};


async function logout() {
    await chiamaApi("api/logout.php");
    utenteCorrente = "";
    document.getElementById("contenitore-task").innerHTML = "";
    document.getElementById("contenitore-file").innerHTML = "";
    mostraSezione("sezione-login");
}
async function addFile(){
    const input = document.getElementById("input-file");
    const files = input.files;

    const progressContainer = document.getElementById("progress-container");
    const progressBar = document.getElementById("progress-bar");
    const progressText = document.getElementById("progress-text");
    
    
    let fileCompletati = 0;
    const totaleFiles = files.length;

    mex = document.getElementById("messaggio-file");
    if (totaleFiles === 0){
        mex.innerText = "Seleziona un file!";
        return
    }

    progressContainer.style.display = "flex";
    progressBar.style.width = "0%";
    
    for (const [i, file] of Array.from(files).entries()){
        const nomeFile = file.name;
        progressText.innerText = `Caricamento file ${i + 1} di ${totaleFiles}: ${nomeFile}`;       
        const dati = new FormData();
        dati.append("file", file);
        const risposta = await chiamaApiFile("api/carica_file.php", dati);
        const testo = typeof risposta === "object" ? risposta.messaggio : risposta;
        mex.innerText = testo;

        if (typeof risposta === "object" && risposta.stop) {
            break;
        }
        fileCompletati++;
        progressBar.style.width = (fileCompletati / totaleFiles * 100) + "%";
    }

    input.value = "";
    
    setTimeout(async () => {
        await displayAllFiles();
        progressContainer.style.display = "none";
    }, 150);
    
}

async function toggleMenuProfilo(p) {
    let menu = document.getElementById("menu-tendina-profilo");
    menu.classList.toggle("nascondi");
}

async function addProfileImage() {
    const fileInput = document.getElementById("input-carica-avatar");
    mex = document.getElementById("messaggio-impostazioni");
    if (fileInput.files.length === 0){
        mex.innerText = "Seleziona un file!";
        return
    }

    const dati = new FormData();
    dati.append("immagine", fileInput.files[0]);

    const risposta = await chiamaApiFile("api/carica_immagine_profilo.php", dati);
    mex.innerText = risposta;
    fileInput.value = "";
    displayProfileImage();

}

async function displayProfileImage() {
    let imgElement = document.getElementById("avatar-immagine");
    let fallbackElement = document.getElementById("avatar-fallback")
    let percorso = await chiamaApi("api/leggi_immagine_profilo.php");

    
    
    if (typeof percorso === "string" && percorso.startsWith("uploads/")){
        imgElement.src = percorso;
        imgElement.style.display = "block"; //fa vedere l'immagine
        if (fallbackElement) fallbackElement.style.display = "none";    //nasconde l'omino
    } 
    else {
        imgElement.style.display = "none";
        if (fallbackElement) fallbackElement.style.display = "block";
    }
}

async function displayAllFiles() {
    let listaFile = await chiamaApi("api/leggi_file.php");
    if (!Array.isArray(listaFile)) return;
    let contenitore = document.getElementById("contenitore-file");

    if (!contenitore) return;
    contenitore.innerHTML = "";
    
    for (let i = 0; i < listaFile.length; i++){
        let currentFile = listaFile[i];
        let idFile   = currentFile.id;
        let fileName = escapeHtml(currentFile.nome_file);
        let data     = escapeHtml(currentFile.data_caricamento);
        let encodedPath = encodeURI(currentFile.percorso);
        
        let rigaDiv = document.createElement("div");
        rigaDiv.className = "file-card";
        rigaDiv.id = `file-card-${idFile}`;
        
        let previewContent = "";
        const fileExtension = fileName.split('.').pop().toUpperCase();

        if (isImage(fileName)) {
            previewContent = `<img src="${encodedPath}" alt="${fileName}" />`;
        } else {
            previewContent = `
                <div class="file-icon-fallback">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#007acc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span class="file-tipo-badge">${fileExtension}</span>
                </div>
            `;
        }

        rigaDiv.innerHTML = `
            <div class="file-preview-container">
                <a href="${encodedPath}" download="${fileName}" title="Scarica file" style="display: block; width: 100%; height: 100%;">
                    ${previewContent}
                </a>
            </div>
            <div class="file-info-container">
                <div class="file-info">
                    <span class="file-nome" title="${fileName}">${fileName}</span>
                    <span class="file-data">${data}</span>
                </div>
                <div class="file-actions">
                    <!-- 1. Icona Download (Verde) -->
                    <a href="${encodedPath}" download="${fileName}" class="btn-azione-file" title="Scarica file">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    </a>
                    
                    <!-- 2. Icona Rinomina / Matitina (Arancione/Giallo) -->
                    <button class="btn-rinomina-file" onclick="abilitaRinomina(${idFile})" title="Rinomina file">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                    </button>

                    <!-- 3. Icona Elimina (Rosso) sicuro -->
                    <button class="btn-elimina-file" data-id="${idFile}" onclick="deleteFile(${idFile})" title="Elimina file">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </div>
            </div>
        `;
    
        contenitore.appendChild(rigaDiv);
    }
}




async function deleteFile(id) {
    mex = document.getElementById("messaggio-file");
    risposta = await chiamaApi("api/elimina_file.php", {id: id});
    mex.innerText = risposta;
    displayAllFiles()
}

function isImage(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext);
}
async function abilitaRinomina(idFile){
    let renameMenu = document.getElementById("modal-rinomina");
    renameMenu.classList.remove("nascondi");
    let oldName = document.getElementById("modal-vecchio-nome");
    oldName.innerText = fileName;

    currentFileId = idFile;
    currentOldFileName = fileName; 
}
async function backDashboard() {
    let renameMenu = document.getElementById("modal-rinomina");
    let inputNewName = document.getElementById("modal-input-nuovo-nome");
    renameMenu.classList.add("nascondi");
    inputNewName.value = "";
}
async function renameFile() {
    let inputNewNameFIle = document.getElementById("modal-input-nuovo-nome");
    let newFileName = inputNewNameFIle.value;
    let risposta = await chiamaApi("api/rinomina_file.php", {id: currentFileId, newName: newFileName});
    backDashboard();
    let mex = document.getElementById("messaggio-file");
    mex.innerText = risposta;
    displayAllFiles();
}
async function apriModalSelezionaFile(idTask){
    currentTaskID = idTask
    let selectMenu = document.getElementById("modal-seleziona-file");
    selectMenu.classList.remove("nascondi");

    let listaFile = await chiamaApi("api/leggi_file.php");
    if (!Array.isArray(listaFile)) return;
    let grigliaFile = document.getElementById("griglia-file-selezionabili");
    grigliaFile.innerHTML = "";
    
    for (let i = 0; i < listaFile.length; i++) {
        let currentFile = listaFile[i];
        let idFile   = currentFile.id;
        let fileName = escapeHtml(currentFile.nome_file);
        let data     = escapeHtml(currentFile.data_caricamento);
        let encodedPath = encodeURI(currentFile.percorso);
        
        let fileDiv = document.createElement("div");
        fileDiv.className = "file-picker-item";
        
        fileDiv.onclick = () => assegnaFileATask(currentTaskID, idFile);
        
        // Gestione dell'anteprima (immagine o icona standard)
        let previewContent = "";
        if (isImage(fileName)) {
            previewContent = `<img src="${encodedPath}" alt="${fileName}" />`;
        } else {
            previewContent = `
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#007acc" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            `;
        }

        fileDiv.innerHTML = `
            <div class="file-picker-preview">${previewContent}</div>
            <span class="file-picker-nome" title="${fileName}">${fileName}</span>
        `;
        
        grigliaFile.appendChild(fileDiv);
    }
}
async function chiudiModalFile() {
    let selectMenu = document.getElementById("modal-seleziona-file");
    selectMenu.classList.add("nascondi");
    
}
async function assegnaFileATask(idTask, idFile) {
    let risposta = await chiamaApi("api/associa_file_task.php", {idTask: idTask, idFile: idFile});
    let mex = document.getElementById("messaggio-dashboard");
    mex.innerText = risposta;
    chiudiModalFile()
    caricaTask()
}

async function deleteAllFile() {
    let mex = document.getElementById("messaggio-file");
    if (!confirm("Eliminare tutti i file?")) return;
    let risposta = await chiamaApi("api/elimina_tutti_file.php", {});
    mex.innerText = risposta;
    displayAllFiles();
}