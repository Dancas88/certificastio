<?php
/**
 * Configurazione Backend CertificAst.io
 * File di configurazione per email e database
 */

// ====== CONFIGURAZIONE EMAIL ======
// Configura questi dati con le credenziali email del tuo hosting Aruba

// Email mittente (il tuo Gmail)
define('EMAIL_FROM', 'TUOGMAIL@gmail.com');  // <-- SOSTITUISCI CON IL TUO GMAIL
define('EMAIL_FROM_NAME', 'CertificAst.io');

// Email destinatario per notifiche amministratore (opzionale)
define('EMAIL_ADMIN', 'TUOGMAIL@gmail.com');  // <-- SOSTITUISCI CON IL TUO GMAIL

// Metodo invio email
// 'smtp' = Usa SMTP configurato (consigliato per Gmail)
// 'mail' = Usa funzione mail() PHP (più semplice ma meno affidabile)
define('EMAIL_METHOD', 'smtp');

// Configurazione SMTP per Gmail
define('SMTP_HOST', 'smtp.gmail.com');     // Server SMTP Gmail
define('SMTP_PORT', 587);                   // Porta TLS
define('SMTP_SECURE', 'tls');               // TLS per Gmail
define('SMTP_USERNAME', 'TUOGMAIL@gmail.com');  // <-- SOSTITUISCI CON IL TUO GMAIL
define('SMTP_PASSWORD', 'TUA_APP_PASSWORD_16_CARATTERI');  // <-- SOSTITUISCI CON LA APP PASSWORD

// ====== CONFIGURAZIONE DATABASE (OPZIONALE) ======
// Lascia vuoto DB_HOST se non vuoi usare il database

define('DB_HOST', '');           // Lascia vuoto per disabilitare
// define('DB_HOST', 'localhost');  // Decommenta per abilitare
define('DB_NAME', 'certificastio');
define('DB_USER', 'tuousername');
define('DB_PASS', 'tuapassword');
define('DB_TABLE', 'orders');

// ====== CONFIGURAZIONE PAYPAL ======
// Client ID e Secret per verifica pagamenti lato server

define('PAYPAL_CLIENT_ID', 'AfpJksPo2im9nuNaLP1DOof55kvrqWt9JkGUMs_6LXH4-P9RsJHILQQ-bmTPYeQ89rvCQWOuiFhXwTGK');
define('PAYPAL_SECRET', '');  // Opzionale, solo se vuoi validazione server-side

// Ambiente PayPal
define('PAYPAL_MODE', 'sandbox');  // 'sandbox' per test, 'live' per produzione

// ====== CONFIGURAZIONE GENERALI ======

// Cartella per salvare certificati generati (deve avere permessi di scrittura)
define('CERTIFICATES_DIR', __DIR__ . '/certificates/');

// Abilita log degli errori
define('ENABLE_LOGGING', true);
define('LOG_FILE', __DIR__ . '/logs/app.log');

// URL base del sito
define('SITE_URL', 'https://www.tuodominio.it');

// Prezzo certificato
define('CERTIFICATE_PRICE', '1.00');
define('CERTIFICATE_CURRENCY', 'EUR');

// ====== SECURITY ======
// Chiave segreta per validazione richieste (cambiala con una stringa casuale)
define('SECRET_KEY', 'cambiaQuestaCon_UnaChiaveSegretaCasuale_123456');

// ====== FUNZIONI UTILITY ======

/**
 * Verifica se il database è configurato
 */
function isDatabaseEnabled() {
    return !empty(DB_HOST);
}

/**
 * Crea le cartelle necessarie se non esistono
 */
function createRequiredDirectories() {
    $dirs = [
        CERTIFICATES_DIR,
        dirname(LOG_FILE)
    ];

    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

/**
 * Scrive nel log
 */
function writeLog($message, $type = 'INFO') {
    if (!ENABLE_LOGGING) return;

    $logDir = dirname(LOG_FILE);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}

/**
 * Connessione al database
 */
function getDbConnection() {
    if (!isDatabaseEnabled()) {
        return null;
    }

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        writeLog("Errore connessione DB: " . $e->getMessage(), 'ERROR');
        return null;
    }
}

// Crea le cartelle necessarie all'avvio
createRequiredDirectories();

// Proteggi questo file dall'accesso diretto
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    die('Accesso negato');
}
