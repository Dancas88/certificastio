<?php
/**
 * Script per invio certificato via email
 * Chiamato dopo il pagamento PayPal
 */

// Abilita CORS per chiamate da JavaScript
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Gestisci preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Carica configurazione
require_once __DIR__ . '/config.php';

// Solo richieste POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non permesso']);
    exit;
}

// Leggi i dati JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

writeLog("Richiesta ricevuta: " . print_r($data, true), 'INFO');

// Validazione dati
$required = ['name', 'email', 'paypalOrderId'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Campo $field mancante"]);
        writeLog("Campo $field mancante", 'ERROR');
        exit;
    }
}

// Sanitizza dati
$name = htmlspecialchars(trim($data['name']));
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$birthDate = !empty($data['birthDate']) ? htmlspecialchars(trim($data['birthDate'])) : '';
$motivation = !empty($data['motivation']) ? htmlspecialchars(trim($data['motivation'])) : '';
$paypalOrderId = htmlspecialchars(trim($data['paypalOrderId']));
$paypalPayerId = !empty($data['paypalPayerId']) ? htmlspecialchars(trim($data['paypalPayerId'])) : '';

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email non valida']);
    writeLog("Email non valida: " . $data['email'], 'ERROR');
    exit;
}

// Genera il certificato
require_once __DIR__ . '/generate-certificate.php';
$certificateHtml = generateCertificateHTML($name, $email, $birthDate, $motivation, false);

// Salva nel database (se abilitato)
if (isDatabaseEnabled()) {
    saveOrder($name, $email, $birthDate, $motivation, $paypalOrderId, $paypalPayerId);
}

// Invia email
$emailSent = sendCertificateEmail($email, $name, $certificateHtml);

if ($emailSent) {
    writeLog("Email inviata con successo a: $email", 'SUCCESS');
    echo json_encode([
        'success' => true,
        'message' => 'Certificato inviato via email con successo!'
    ]);
} else {
    writeLog("Errore invio email a: $email", 'ERROR');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore durante invio email'
    ]);
}

/**
 * Invia il certificato via email
 */
function sendCertificateEmail($toEmail, $toName, $certificateHtml) {
    try {
        if (EMAIL_METHOD === 'smtp') {
            return sendEmailSMTP($toEmail, $toName, $certificateHtml);
        } else {
            return sendEmailPHP($toEmail, $toName, $certificateHtml);
        }
    } catch (Exception $e) {
        writeLog("Errore invio email: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Invio email con funzione mail() PHP
 */
function sendEmailPHP($toEmail, $toName, $certificateHtml) {
    $subject = "Il tuo Diploma di Astiosità Ufficiale - CertificAst.io";

    // Headers
    $headers = "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Corpo email
    $emailBody = getEmailTemplate($toName, $certificateHtml);

    return mail($toEmail, $subject, $emailBody, $headers);
}

/**
 * Invio email con SMTP (consigliato per Aruba)
 */
function sendEmailSMTP($toEmail, $toName, $certificateHtml) {
    // Usa PHPMailer se disponibile, altrimenti fallback a mail()
    if (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';
        require_once __DIR__ . '/PHPMailer/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configurazione SMTP
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            // Mittente e destinatario
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);

            // Contenuto
            $mail->isHTML(true);
            $mail->Subject = "Il tuo Diploma di Astiosità Ufficiale - CertificAst.io";
            $mail->Body = getEmailTemplate($toName, $certificateHtml);

            // Allegato HTML del certificato
            $mail->addStringAttachment($certificateHtml, "Diploma_Ufficiale_$toName.html", 'base64', 'text/html');

            $mail->send();
            return true;
        } catch (Exception $e) {
            writeLog("Errore PHPMailer: " . $e->getMessage(), 'ERROR');
            // Fallback a mail() PHP
            return sendEmailPHP($toEmail, $toName, $certificateHtml);
        }
    } else {
        // PHPMailer non disponibile, usa mail() PHP
        writeLog("PHPMailer non trovato, uso mail() PHP", 'WARNING');
        return sendEmailPHP($toEmail, $toName, $certificateHtml);
    }
}

/**
 * Template email HTML
 */
function getEmailTemplate($name, $certificateHtml) {
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
        .button { display: inline-block; background: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 CertificAst.io</h1>
            <p>Il tuo Diploma di Astiosità è pronto!</p>
        </div>

        <div class="content">
            <h2>Ciao $name!</h2>

            <p>Grazie per aver acquistato il tuo <strong>Diploma di Astiosità Certificato</strong>!</p>

            <p>Il tuo certificato ufficiale è allegato a questa email in formato HTML.</p>

            <h3>📄 Come usare il certificato:</h3>
            <ol>
                <li>Apri l'allegato HTML con un browser</li>
                <li>Clicca sul pulsante "Stampa" per stamparlo</li>
                <li>Clicca su "Scarica" per salvarlo sul tuo computer</li>
            </ol>

            <p><strong>Suggerimento:</strong> Per un risultato ottimale, stampa il certificato su carta di alta qualità!</p>

            <p style="margin-top: 30px;">Se hai domande o problemi, contattaci a: <a href="mailto:" . EMAIL_FROM . ">" . EMAIL_FROM . "</a></p>
        </div>

        <div class="footer">
            <p>&copy; 2025 CertificAst.io - Tutti i diritti riservati</p>
            <p>Hai ricevuto questa email perché hai acquistato un diploma su CertificAst.io</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Salva ordine nel database
 */
function saveOrder($name, $email, $birthDate, $motivation, $paypalOrderId, $paypalPayerId) {
    try {
        $pdo = getDbConnection();
        if (!$pdo) {
            writeLog("Database non configurato, skip salvataggio", 'WARNING');
            return false;
        }

        // Crea tabella se non esiste
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS " . DB_TABLE . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                birth_date VARCHAR(50),
                motivation TEXT,
                paypal_order_id VARCHAR(100) NOT NULL,
                paypal_payer_id VARCHAR(100),
                price DECIMAL(10,2) NOT NULL,
                currency VARCHAR(10) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_paypal (paypal_order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Inserisci ordine
        $stmt = $pdo->prepare("
            INSERT INTO " . DB_TABLE . "
            (name, email, birth_date, motivation, paypal_order_id, paypal_payer_id, price, currency)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $email,
            $birthDate,
            $motivation,
            $paypalOrderId,
            $paypalPayerId,
            CERTIFICATE_PRICE,
            CERTIFICATE_CURRENCY
        ]);

        writeLog("Ordine salvato nel database - ID: " . $pdo->lastInsertId(), 'SUCCESS');
        return true;

    } catch (PDOException $e) {
        writeLog("Errore salvataggio DB: " . $e->getMessage(), 'ERROR');
        return false;
    }
}
