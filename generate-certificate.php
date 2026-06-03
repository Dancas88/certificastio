<?php
/**
 * Generatore di certificati HTML
 * Crea il contenuto HTML del diploma
 */

// Proteggi dall'accesso diretto
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    die('Accesso negato');
}

/**
 * Genera il contenuto HTML del certificato
 *
 * @param string $name Nome completo
 * @param string $email Email destinatario
 * @param string $birthDate Data di nascita (opzionale)
 * @param string $motivation Specializzazione (opzionale)
 * @param bool $isPreview Se true, genera anteprima con watermark
 * @return string HTML del certificato
 */
function generateCertificateHTML($name, $email, $birthDate = '', $motivation = '', $isPreview = false) {
    // Sicurezza: escape dei dati
    $name = htmlspecialchars($name);
    $email = htmlspecialchars($email);
    $birthDate = htmlspecialchars($birthDate);
    $motivation = htmlspecialchars($motivation);

    // Stile watermark
    $watermarkStyle = $isPreview ? '
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 80px;
        color: rgba(255, 0, 0, 0.08);
        font-weight: bold;
        z-index: 10;
        pointer-events: none;
    ' : 'display: none;';

    // Titolo e colore bordo
    $certificateTitle = $isPreview ? 'ANTEPRIMA NON UFFICIALE' : 'ATTESTATO DI COMPLETAMENTO';
    $borderColor = $isPreview ? '#FF9800' : '#4CAF50';

    // Banner preview
    $previewBanner = $isPreview ? '<div class="preview-banner">👀 ANTEPRIMA NON UFFICIALE - Compila il form per il certificato ufficiale!</div>' : '';
    $previewBannerStyle = $isPreview ? '
        .preview-banner {
            background: #FF9800;
            color: white;
            text-align: center;
            padding: 12px;
            font-weight: bold;
            font-size: 14px;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 20;
        }
    ' : '';

    // Formatta data di nascita
    $birthDateFormatted = '';
    if ($birthDate && $birthDate !== '[Data di Nascita]') {
        $date = new DateTime($birthDate);
        $birthDateFormatted = '<br><span style="font-size: 0.6em; font-weight: 400; color: #666;">nato il ' . $date->format('d/m/Y') . '</span>';
    }

    // Formatta motivazione
    $motivationSection = '';
    if ($motivation && trim($motivation) !== '' && $motivation !== '[La Tua Motivazione]') {
        $motivationSection = '
            con specializzazione in<br>
            <span class="qualification">' . strtoupper($motivation) . '</span><br>
        ';
    }

    // ID certificato
    $certificateId = $isPreview ? 'PREVIEW' : 'AST' . time();

    // Data e ora corrente
    $currentDate = date('d/m/Y');
    $currentTime = date('H:i');
    $currentYear = date('Y');

    // Background SVG (decorativo)
    $backgroundSvg = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTMwNyIgaGVpZ2h0PSI5MjQiIHZpZXdCb3g9IjAgMCAxMzA3IDkyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTAgMEgxMzA3VjkyNEgwVjBaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMCAwTDY1MyAwTDEzMDcgMjMxVjBIMTMwN1YwWiIgZmlsbD0iI0ZGQTc4QyIvPgo8cGF0aCBkPSJNMCAwTDY1MyAwTDEzMDcgMjMxVjBIMTMwN1YwWiIgZmlsbD0iI0ZGOTk4QiIvPgo8cGF0aCBkPSJNMCAwSDEzMDdWMjMxTDY1MyAwTDAgMFoiIGZpbGw9IiNGRkE3OEMiLz4KPHBhdGggZD0iTTAgOTI0TDY1MyA5MjRMMTMwNyA2OTNWOTI0SDEzMDdWOTI0WiIgZmlsbD0iI0ZGQTc4QyIvPgo8cGF0aCBkPSJNMCA5MjRINjUzTDEzMDcgNjkzVjkyNEgwVjkyNFoiIGZpbGw9IiNGRkJCOUUiLz4KPHBhdGggZD0iTTAgOTI0TDAgNjkzTDY1MyA5MjRIMFoiIGZpbGw9IiNGRjk5OEIiLz4KPHBhdGggZD0iTTEzMDcgOTI0TDEzMDcgNjkzTDY1MyA5MjRIMTMwN1oiIGZpbGw9IiNGRkJCOUUiLz4KPC9zdmc+';

    // Genera HTML
    $html = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diploma di Astiosità - $name</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@400;600&display=swap');

        body {
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Open Sans', sans-serif;
        }

        .certificate-container {
            width: 1000px;
            height: 700px;
            margin: 0 auto;
            background: white;
            border: 4px solid $borderColor;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .certificate-bg {
            background-image: url('$backgroundSvg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            width: 100%;
            height: 100%;
            position: relative;
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            grid-template-rows: auto 1fr auto;
            padding: 40px;
            box-sizing: border-box;
        }

        .watermark {
            $watermarkStyle
        }

        $previewBannerStyle

        .header-section {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 20px;
        }

        .certificate-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 1rem;
            color: #666;
            font-weight: 400;
            letter-spacing: 1px;
        }

        .left-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .authority {
            text-align: center;
        }

        .authority-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }

        .authority-name {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .seal {
            width: 70px;
            height: 70px;
            background: #FFD700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #333;
            border: 3px solid #FFA500;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin: 0 auto;
        }

        .center-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 20px;
        }

        .recipient-name {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 20px 0;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            min-width: 300px;
            display: inline-block;
        }

        .course-info {
            font-size: 1rem;
            color: #444;
            line-height: 1.6;
            margin: 20px 0;
            max-width: 450px;
        }

        .course-name {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .achievement {
            font-weight: 600;
            color: #e74c3c;
        }

        .qualification {
            font-weight: 700;
            color: #27ae60;
            font-size: 1.1rem;
            display: block;
            margin-top: 10px;
        }

        .right-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .details-title {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .details-content {
            font-size: 0.8rem;
            color: #444;
            line-height: 1.5;
        }

        .footer-section {
            grid-column: 1 / -1;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .certificate-container {
                border: 2px solid #333;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        $previewBanner

        <div class="certificate-bg">
            <div class="watermark">ANTEPRIMA</div>

            <div class="header-section">
                <div class="certificate-title">$certificateTitle</div>
                <div class="subtitle">SI CERTIFICA CHE</div>
            </div>

            <div class="left-section">
                <div class="authority">
                    <div class="authority-label">Rettore Protempore</div>
                    <div class="authority-name">Prof. Astio Maximus</div>
                </div>
                <div class="seal">🏛️</div>
            </div>

            <div class="center-section">
                <div class="recipient-name">
                    $name
                    $birthDateFormatted
                </div>

                <div class="course-info">
                    ha completato il corso di studi in<br>
                    <span class="course-name">Astio Management</span><br>
                    $motivationSection
                    ottenendo con <span class="achievement">lode</span> e <span class="achievement">merito</span><br>
                    la qualifica di<br>
                    <span class="qualification">MAESTRO DELL'ASTIOSITÀ CERTIFICATO</span>
                </div>
            </div>

            <div class="right-section">
                <div class="details-title">Responsabile Formazione</div>
                <div class="details-content">
                    <strong>Accademia Ast.io</strong><br><br>
                    Data: $currentDate<br>
                    ID: $certificateId<br>
                    Ore: $currentTime
                </div>
            </div>

            <div class="footer-section">
                "L'astio è un'arte, e tu ne sei diventato maestro" - Accademia Ast.io ©$currentYear
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    return $html;
}
