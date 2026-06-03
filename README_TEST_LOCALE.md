# 🧪 Guida Test Locale - CertificAst.io

Questa guida ti aiuterà a testare il sistema sul tuo computer prima di caricarlo su Aruba.

---

## 📋 Cosa ti serve

- **XAMPP** o **WAMP** (server locale PHP)
- Due email tue per testare invio/ricezione
- Account PayPal Sandbox (già configurato)

---

## 🚀 STEP 1: Installa Server Locale

### **Opzione A: XAMPP (Consigliato)**

1. Scarica XAMPP da: https://www.apachefriends.org/
2. Installa XAMPP (lascia le opzioni di default)
3. Avvia **XAMPP Control Panel**
4. Clicca **Start** su:
   - Apache (server web)
   - MySQL (database, opzionale)

### **Opzione B: WAMP**

1. Scarica WAMP da: https://www.wampserver.com/
2. Installa e avvia WampServer

---

## 📂 STEP 2: Copia i File

1. Trova la cartella del server locale:
   - **XAMPP:** `C:\xampp\htdocs\`
   - **WAMP:** `C:\wamp64\www\`

2. Crea una cartella `certificastio`:
   ```
   C:\xampp\htdocs\certificastio\
   ```

3. Copia questi file nella cartella:
   ```
   certificastio.html
   config.php
   send-certificate.php
   generate-certificate.php
   ```

4. Crea cartelle vuote:
   ```
   certificates/
   logs/
   ```

---

## ⚙️ STEP 3: Configura Email per Test

Apri `config.php` e modifica per test con **Gmail** (più facile):

```php
// ====== CONFIGURAZIONE EMAIL ======

// Email mittente (usa il tuo Gmail)
define('EMAIL_FROM', 'tua.email@gmail.com');        // ← TUA EMAIL
define('EMAIL_FROM_NAME', 'CertificAst.io Test');

// Email admin
define('EMAIL_ADMIN', 'altra.tua.email@gmail.com'); // ← ALTRA TUA EMAIL

// Usa funzione mail() per test locale (più semplice)
define('EMAIL_METHOD', 'mail');  // Cambia da 'smtp' a 'mail'

// ====== CONFIGURAZIONE DATABASE ======
// DISABILITA il database per ora
define('DB_HOST', '');  // Lascia vuoto

// ====== CONFIGURAZIONE GENERALI ======
define('SITE_URL', 'http://localhost/certificastio');

// Prezzo test
define('CERTIFICATE_PRICE', '1.00');
```

---

## 📧 STEP 4: Configura PHP per Invio Email Locale

### **Metodo 1: Test senza invio reale (consigliato per iniziare)**

1. Apri `send-certificate.php`
2. Trova la funzione `sendEmailPHP`
3. Aggiungi questa riga all'inizio della funzione per simulare l'invio:

```php
function sendEmailPHP($toEmail, $toName, $certificateHtml) {
    // SOLO PER TEST - Simula invio email senza inviare davvero
    writeLog("EMAIL SIMULATA per: $toEmail - Nome: $toName", 'TEST');
    return true;  // Simula successo

    // Il resto del codice originale...
```

### **Metodo 2: Invio reale con Gmail (opzionale)**

Se vuoi testare l'invio reale:

1. Scarica **PHPMailer** da: https://github.com/PHPMailer/PHPMailer/releases
2. Estrai la cartella `PHPMailer` in `certificastio/`
3. Configura Gmail in `config.php`:

```php
define('EMAIL_METHOD', 'smtp');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tua.email@gmail.com');
define('SMTP_PASSWORD', 'password_app_gmail');  // Vedi sotto
```

**Come ottenere Password App Gmail:**
1. Vai su: https://myaccount.google.com/security
2. Abilita **Verifica in 2 passaggi**
3. Vai su **Password per le app**
4. Genera una password per "Mail"
5. Copia la password generata in `SMTP_PASSWORD`

---

## 🧪 STEP 5: Testa il Sistema

### **Test 1: Verifica server locale**

1. Apri il browser
2. Vai su: `http://localhost/certificastio/certificastio.html`
3. Dovresti vedere la pagina CertificAst.io

### **Test 2: Anteprima certificato**

1. Clicca su **"ANTEPRIMA CERTIFICATO GRATUITA"** (anche senza compilare il form)
2. Dovrebbe aprirsi una finestra con il certificato
3. ✅ Se vedi il certificato: funziona!

### **Test 3: Pagamento Sandbox**

1. Compila il form con:
   - Nome: `Mario Rossi`
   - Email: `tua.email@gmail.com` (la tua vera email)
   - Data nascita: (opzionale)
   - Specializzazione: `Test Pagamento`

2. Clicca sul pulsante **PayPal**

3. Nel popup PayPal:
   - Accedi con l'account **Personal Sandbox** (quello del compratore)
   - Completa il pagamento test

4. Dopo il pagamento:
   - Il certificato dovrebbe scaricarsi automaticamente
   - Dovrebbe aprirsi una finestra con il certificato
   - Nella console browser (F12) dovresti vedere: `✅ Email inviata con successo!`

### **Test 4: Verifica log**

1. Apri il file: `certificastio/logs/app.log`
2. Dovresti vedere le registrazioni tipo:
   ```
   [2025-01-09 15:30:45] [INFO] Richiesta ricevuta: Array (...)
   [2025-01-09 15:30:45] [SUCCESS] Email inviata con successo a: tua.email@gmail.com
   ```

### **Test 5: Verifica email (se attivato invio reale)**

1. Controlla la tua casella email
2. Dovresti ricevere un'email con:
   - Oggetto: "Il tuo Diploma di Astiosità Ufficiale - CertificAst.io"
   - Allegato: `Diploma_Ufficiale_Mario_Rossi.html`

---

## 🐛 Risoluzione Problemi Test Locale

### **Errore: "Impossibile connettersi a localhost"**

- Verifica che Apache sia avviato in XAMPP
- Controlla che la porta 80 non sia occupata
- Prova: `http://localhost:80/certificastio/certificastio.html`

### **Errore: "send-certificate.php not found"**

1. Verifica che i file siano nella cartella giusta
2. URL corretto: `http://localhost/certificastio/send-certificate.php`
3. Nella console browser (F12), controlla l'URL della richiesta

### **Email non arriva**

- Controlla il file `logs/app.log` per errori
- Verifica che `EMAIL_METHOD` sia su `'mail'` per test semplice
- Se usi Gmail SMTP, verifica Password App

### **Errore console: "CORS policy"**

- Normale su localhost, ignoralo
- Il backend PHP ha già gli header CORS configurati

### **PayPal non si apre**

- Verifica che il Client ID Sandbox sia corretto
- Controlla console browser per errori JavaScript
- Verifica connessione internet

---

## 📊 Come Leggere i Log

Il file `logs/app.log` ti mostra tutto quello che succede:

```log
[2025-01-09 15:30:45] [INFO] Richiesta ricevuta: Array ( [name] => Mario Rossi [email] => test@gmail.com ... )
[2025-01-09 15:30:45] [SUCCESS] Email inviata con successo a: test@gmail.com
```

Tipi di log:
- `[INFO]` - Informazioni generali
- `[SUCCESS]` - Operazione riuscita
- `[WARNING]` - Attenzione ma non blocca
- `[ERROR]` - Errore che ha bloccato l'operazione

---

## ✅ Checklist Test Locale

Prima di caricare su Aruba, verifica:

- [ ] Server locale (XAMPP/WAMP) avviato
- [ ] File copiati nella cartella giusta
- [ ] Pagina `http://localhost/certificastio/certificastio.html` carica
- [ ] Anteprima certificato funziona
- [ ] Pagamento Sandbox PayPal completato
- [ ] Certificato scaricato automaticamente
- [ ] Finestra certificato si apre
- [ ] Log mostrano successo: `logs/app.log`
- [ ] Email ricevuta (se attivato invio reale)
- [ ] Nessun errore nella console browser (F12)

---

## 🎯 Prossimi Passi

Una volta che tutto funziona in locale:

1. ✅ **Test completati** → Sei pronto per Aruba
2. 📝 **Documentati le credenziali:**
   - Client ID PayPal Live
   - Password SMTP Aruba
   - Credenziali database (se necessario)
3. 🚀 **Carica su Aruba** seguendo la guida installazione
4. 💳 **Passa da Sandbox a Live** PayPal
5. 🎉 **Vai in produzione!**

---

## 🔧 Modifiche per Produzione

Quando carichi su Aruba, ricorda di cambiare:

### **In `config.php`:**
```php
// Email → credenziali Aruba
define('EMAIL_FROM', 'noreply@tuodominio.it');
define('SMTP_HOST', 'smtps.aruba.it');
define('SMTP_USERNAME', 'noreply@tuodominio.it');
define('SMTP_PASSWORD', 'password_aruba');

// URL → dominio reale
define('SITE_URL', 'https://www.tuodominio.it');

// PayPal → modalità Live
define('PAYPAL_MODE', 'live');

// Prezzo → quello reale
define('CERTIFICATE_PRICE', '5.00');
```

### **In `certificastio.html`:**
```html
<!-- Client ID PayPal Live -->
<script src="https://www.paypal.com/sdk/js?client-id=IL_TUO_CLIENT_ID_LIVE&currency=EUR&disable-funding=mybank"></script>
```

---

## 💡 Suggerimenti

1. **Testa SEMPRE in locale prima** di caricare modifiche
2. **Usa il log** per debug: `writeLog("Messaggio", 'INFO');`
3. **Fai backup** dei file prima di modificare
4. **Testa con diversi browser** (Chrome, Firefox, Edge)
5. **Prova su mobile** per verificare responsive

---

Buon test! 🚀

Se hai problemi, controlla sempre:
1. Console browser (F12)
2. File `logs/app.log`
3. Che Apache sia avviato
