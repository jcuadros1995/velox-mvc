<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private SettingRepository $settingRepository;

    public function __construct()
    {
        $this->settingRepository =
            new SettingRepository();
    }

    private function createMailer(): PHPMailer
    {
        require_once
            __DIR__ .
            '/../../config/PHPMailer/src/Exception.php';

        require_once
            __DIR__ .
            '/../../config/PHPMailer/src/PHPMailer.php';

        require_once
            __DIR__ .
            '/../../config/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        $enabled =
            $this->getBooleanSetting(
                'enabled',
                true
            );

        if (!$enabled) {
            throw new RuntimeException(
                'El servicio de correo está deshabilitado.'
            );
        }

        $host =
            $this->getSetting(
                'host',
                'smtp.gmail.com'
            );

        $port =
            (int) $this->getSetting(
                'port',
                587
            );

        $encryption =
            $this->getSetting(
                'encryption',
                'tls'
            );

        $username =
            $this->getSetting(
                'username',
                ''
            );

        $password =
            $this->getSetting(
                'password',
                ''
            );

        $fromName =
            $this->getSetting(
                'from_name',
                'Burger Town'
            );

        $fromEmail =
            $this->getSetting(
                'from_email',
                $username
            );


        $mail->isSMTP();

        $mail->Host =
            $host;

        $mail->SMTPAuth =
            true;

        $mail->Username =
            $username;

        $mail->Password =
            $password;

        $mail->Port =
            $port;

        if ($encryption === 'ssl') {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;

        } else {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->CharSet =
            'UTF-8';

        $mail->setFrom(
            $fromEmail,
            $fromName
        );

        $mail->isHTML(true);

        return $mail;
    }

    public function send(
        string $to,
        string $subject,
        string $html,
        ?string $plainText = null
    ): void {

        $mail =
            $this->createMailer();

        $mail->addAddress(
            $to
        );

        $mail->Subject =
            $subject;

        $mail->Body =
            $html;

        $mail->AltBody =
            $plainText
            ?? strip_tags($html);

        $mail->send();
    }

    /**
     * Envía el enlace seguro de recuperación de contraseña.
     */
    public function sendPasswordReset(
        array $user,
        string $resetUrl
    ): void {

        $email = trim(
            (string) ($user['email'] ?? '')
        );

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'El usuario no tiene un correo electrónico válido.'
            );
        }

        $firstName = trim(
            (string) ($user['first_name'] ?? '')
        );

        if ($firstName === '') {
            $firstName = 'Usuario';
        }

        $siteName = class_exists('SiteConfig')
            ? SiteConfig::siteName()
            : 'Burger Town';

        /*
        |--------------------------------------------------------------------------
        | Sanitización
        |--------------------------------------------------------------------------
        */

        $safeName = htmlspecialchars(
            $firstName,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeSiteName = htmlspecialchars(
            $siteName,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeUrl = htmlspecialchars(
            $resetUrl,
            ENT_QUOTES,
            'UTF-8'
        );

        $currentYear = date('Y');

        /*
        |--------------------------------------------------------------------------
        | HTML EMAIL
        |--------------------------------------------------------------------------
        */

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Restablecer contraseña</title>
</head>

<body
    style="
        margin:0;
        padding:0;
        background:#f4f5f7;
        font-family:Arial,Helvetica,sans-serif;
        color:#18181b;
    "
>

<!-- Preheader -->
<div
    style="
        display:none;
        max-height:0;
        overflow:hidden;
        opacity:0;
        color:transparent;
        visibility:hidden;
    "
>
    Solicitud para restablecer la contraseña de tu cuenta.
</div>


<!-- MAIN WRAPPER -->
<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        width:100%;
        background:#f4f5f7;
        margin:0;
        padding:0;
    "
>

    <tr>

        <td
            align="center"
            style="
                padding:40px 16px;
            "
        >

            <!-- EMAIL CONTAINER -->
            <table
                width="620"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    width:100%;
                    max-width:620px;
                    background:#ffffff;
                    border-radius:20px;
                    overflow:hidden;
                    box-shadow:
                        0 20px 60px rgba(15,23,42,.10);
                "
            >


                <!-- =====================================================
                     HEADER
                ====================================================== -->

                <tr>

                    <td
                        style="
                            padding:0;
                            background:#18181b;
                        "
                    >

                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                        >

                            <tr>

                                <td
                                    style="
                                        padding:30px 38px;
                                    "
                                >

                                    <!-- BRAND -->
                                    <table
                                        cellpadding="0"
                                        cellspacing="0"
                                        border="0"
                                    >

                                        <tr>

                                            <!-- LOGO ICON -->
                                            <td
                                                width="44"
                                                height="44"
                                                align="center"
                                                valign="middle"
                                                style="
                                                    width:44px;
                                                    height:44px;
                                                    background:#6d28d9;
                                                    border-radius:12px;
                                                    color:#ffffff;
                                                    font-size:20px;
                                                    font-weight:800;
                                                "
                                            >
                                                V
                                            </td>

                                            <td
                                                style="
                                                    padding-left:13px;
                                                "
                                            >

                                                <div
                                                    style="
                                                        color:#ffffff;
                                                        font-size:18px;
                                                        line-height:24px;
                                                        font-weight:800;
                                                    "
                                                >
                                                    {$safeSiteName}
                                                </div>

                                                <div
                                                    style="
                                                        margin-top:2px;
                                                        color:#a1a1aa;
                                                        font-size:11px;
                                                        line-height:16px;
                                                    "
                                                >
                                                    Plataforma Administrativa
                                                </div>

                                            </td>

                                        </tr>

                                    </table>

                                </td>

                            </tr>

                        </table>

                    </td>

                </tr>


                <!-- =====================================================
                     CONTENT
                ====================================================== -->

                <tr>

                    <td
                        style="
                            padding:42px 42px 35px;
                        "
                    >

                        <!-- SECURITY BADGE -->

                        <table
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-bottom:22px;
                            "
                        >

                            <tr>

                                <td
                                    style="
                                        padding:7px 11px;
                                        background:#f5f3ff;
                                        border:1px solid #e9d5ff;
                                        border-radius:8px;
                                        color:#6d28d9;
                                        font-size:10px;
                                        font-weight:700;
                                        letter-spacing:.4px;
                                        text-transform:uppercase;
                                    "
                                >

                                    <span
                                        style="
                                            display:inline-block;
                                            width:7px;
                                            height:7px;
                                            margin-right:6px;
                                            background:#8b5cf6;
                                            border-radius:50%;
                                        "
                                    ></span>

                                    Seguridad de cuenta

                                </td>

                            </tr>

                        </table>


                        <!-- GREETING -->

                        <div
                            style="
                                margin-bottom:8px;
                                color:#71717a;
                                font-size:13px;
                                line-height:20px;
                            "
                        >
                            Hola {$safeName},
                        </div>


                        <!-- TITLE -->

                        <h1
                            style="
                                margin:0;
                                color:#18181b;
                                font-size:29px;
                                line-height:37px;
                                font-weight:800;
                                letter-spacing:-.7px;
                            "
                        >
                            Recupera tu contraseña
                        </h1>


                        <!-- DESCRIPTION -->

                        <p
                            style="
                                margin:14px 0 0;
                                color:#71717a;
                                font-size:14px;
                                line-height:24px;
                            "
                        >
                            Recibimos una solicitud para restablecer
                            la contraseña de tu cuenta.
                            Si realizaste esta solicitud, utiliza el
                            siguiente botón para crear una nueva
                            contraseña de forma segura.
                        </p>


                        <!-- =================================================
                             CTA
                        ================================================== -->

                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin:30px 0;
                            "
                        >

                            <tr>

                                <td align="center">

                                    <a
                                        href="{$safeUrl}"
                                        target="_blank"
                                        style="
                                            display:inline-block;
                                            padding:15px 28px;
                                            background:#6d28d9;
                                            color:#ffffff;
                                            text-decoration:none;
                                            border-radius:11px;
                                            font-size:13px;
                                            line-height:20px;
                                            font-weight:700;
                                            box-shadow:
                                                0 8px 20px
                                                rgba(109,40,217,.20);
                                        "
                                    >
                                        Restablecer contraseña
                                    </a>

                                </td>

                            </tr>

                        </table>


                        <!-- =================================================
                             EXPIRATION BOX
                        ================================================== -->

                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-bottom:24px;
                            "
                        >

                            <tr>

                                <td
                                    style="
                                        padding:16px 18px;
                                        background:#fafafa;
                                        border:1px solid #e4e4e7;
                                        border-radius:12px;
                                    "
                                >

                                    <table
                                        width="100%"
                                        cellpadding="0"
                                        cellspacing="0"
                                        border="0"
                                    >

                                        <tr>

                                            <td
                                                width="34"
                                                valign="top"
                                            >

                                                <div
                                                    style="
                                                        width:28px;
                                                        height:28px;
                                                        background:#f4f4f5;
                                                        border-radius:8px;
                                                        text-align:center;
                                                        line-height:28px;
                                                        color:#6d28d9;
                                                        font-size:13px;
                                                    "
                                                >
                                                    ⏱
                                                </div>

                                            </td>

                                            <td
                                                style="
                                                    padding-left:10px;
                                                "
                                            >

                                                <div
                                                    style="
                                                        color:#3f3f46;
                                                        font-size:12px;
                                                        line-height:18px;
                                                        font-weight:700;
                                                    "
                                                >
                                                    Enlace temporal
                                                </div>

                                                <div
                                                    style="
                                                        margin-top:2px;
                                                        color:#71717a;
                                                        font-size:11px;
                                                        line-height:17px;
                                                    "
                                                >
                                                    Este enlace es válido durante
                                                    <strong>
                                                        30 minutos
                                                    </strong>
                                                    y solo puede utilizarse
                                                    una vez.
                                                </div>

                                            </td>

                                        </tr>

                                    </table>

                                </td>

                            </tr>

                        </table>


                        <!-- =================================================
                             SECURITY NOTICE
                        ================================================== -->

                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                        >

                            <tr>

                                <td
                                    style="
                                        padding:17px 18px;
                                        background:#f9fafb;
                                        border-radius:11px;
                                        border:1px solid #f0f0f2;
                                    "
                                >

                                    <div
                                        style="
                                            color:#52525b;
                                            font-size:11px;
                                            line-height:18px;
                                            font-weight:700;
                                        "
                                    >
                                        ¿No solicitaste este cambio?
                                    </div>

                                    <div
                                        style="
                                            margin-top:4px;
                                            color:#a1a1aa;
                                            font-size:10px;
                                            line-height:17px;
                                        "
                                    >
                                        Puedes ignorar este mensaje.
                                        Tu contraseña actual permanecerá
                                        sin cambios.
                                    </div>

                                </td>

                            </tr>

                        </table>


                        <!-- =================================================
                             FALLBACK URL
                        ================================================== -->

                        <div
                            style="
                                margin-top:25px;
                                padding-top:20px;
                                border-top:1px solid #f0f0f2;
                            "
                        >

                            <div
                                style="
                                    margin-bottom:7px;
                                    color:#a1a1aa;
                                    font-size:9px;
                                    line-height:14px;
                                "
                            >
                                Si el botón no funciona, copia y pega
                                el siguiente enlace en tu navegador:
                            </div>

                            <div
                                style="
                                    color:#6d28d9;
                                    font-size:9px;
                                    line-height:15px;
                                    word-break:break-all;
                                "
                            >
                                {$safeUrl}
                            </div>

                        </div>

                    </td>

                </tr>


                <!-- =====================================================
                     FOOTER
                ====================================================== -->

                <tr>

                    <td
                        align="center"
                        style="
                            padding:22px 35px;
                            background:#fafafa;
                            border-top:1px solid #f0f0f2;
                        "
                    >

                        <div
                            style="
                                color:#71717a;
                                font-size:10px;
                                line-height:16px;
                                font-weight:600;
                            "
                        >
                            {$safeSiteName}
                        </div>

                        <div
                            style="
                                margin-top:3px;
                                color:#a1a1aa;
                                font-size:9px;
                                line-height:15px;
                            "
                        >
                            Este es un mensaje automático.
                            Por favor no respondas a este correo.
                        </div>

                        <div
                            style="
                                margin-top:9px;
                                color:#d4d4d8;
                                font-size:9px;
                            "
                        >
                            © {$currentYear} {$safeSiteName}
                        </div>

                    </td>

                </tr>


            </table>

        </td>

    </tr>

</table>

</body>
</html>
HTML;

        /*
        |--------------------------------------------------------------------------
        | Plain text version
        |--------------------------------------------------------------------------
        */

        $plainText =
            "Hola {$firstName},\n\n" .
            "Recibimos una solicitud para restablecer " .
            "la contraseña de tu cuenta.\n\n" .
            "Utiliza el siguiente enlace para establecer " .
            "una nueva contraseña:\n" .
            $resetUrl . "\n\n" .
            "Este enlace es válido durante 30 minutos " .
            "y solo puede utilizarse una vez.\n\n" .
            "Si no solicitaste este cambio, puedes ignorar " .
            "este mensaje. Tu contraseña actual no será modificada.\n\n" .
            "{$siteName}\n" .
            "Este es un mensaje automático.";

        /*
        |--------------------------------------------------------------------------
        | Enviar
        |--------------------------------------------------------------------------
        */

        $this->send(
            $email,
            'Recuperación de contraseña · ' . $siteName,
            $html,
            $plainText
        );
    }




    public function sendNewsletterWelcome(string $email): void
    {
        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN DE MARCA
        |--------------------------------------------------------------------------
        */

        $escape = static function ($value): string {
            return htmlspecialchars(
                (string) ($value ?? ''),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
        };

        $siteName = SiteConfig::siteName() ?: 'Burger Town';
        $description = SiteConfig::siteDescription();

        $safeSiteName = $escape($siteName);
        $safeDescription = $escape($description);

        $primary = SiteConfig::primaryColor();
        $secondary = SiteConfig::secondaryColor();
        $font = SiteConfig::font();

        $primary = preg_match(
            '/^#[0-9a-fA-F]{6}$/',
            $primary
        ) ? $primary : '#000000';

        $secondary = preg_match(
            '/^#[0-9a-fA-F]{6}$/',
            $secondary
        ) ? $secondary : '#C6111A';

        $font = preg_replace(
            '/[^a-zA-Z0-9,\s\'"-]/',
            '',
            (string) $font
        );

        if (trim($font) === '') {
            $font = 'Arial, Helvetica, sans-serif';
        }

        $baseUrl = defined('APP_URL')
            ? rtrim(APP_URL, '/')
            : '';

        $homeUrl = $baseUrl !== ''
            ? $baseUrl . '/'
            : '#';

        $safeHomeUrl = $escape($homeUrl);
        $year = date('Y');

        /*
        |--------------------------------------------------------------------------
        | FONDO CORPORATIVO BURGER TOWN
        |--------------------------------------------------------------------------
        */

        $brandBackground = '
        background-color:#090909;
        background-image:
            radial-gradient(
                circle at 80% 10%,
                rgba(215, 25, 32, .24),
                transparent 18rem
            ),
            linear-gradient(
                160deg,
                #090909 0%,
                #111111 65%,
                #1b0a0b 100%
            );
    ';

        /*
        |--------------------------------------------------------------------------
        | PLANTILLA HTML
        |--------------------------------------------------------------------------
        */

        $html = '
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta name="color-scheme" content="light">
    <title>Bienvenido a ' . $safeSiteName . '</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f3f4f6;
    font-family:' . $font . ';
    color:#18181b;
    -webkit-text-size-adjust:100%;
">

    <!-- Preencabezado para la bandeja de entrada -->
    <div style="
        display:none;
        max-height:0;
        overflow:hidden;
        opacity:0;
        color:transparent;
        font-size:1px;
        line-height:1px;
    ">
        Gracias por suscribirte. Descubre las novedades y
        promociones exclusivas de ' . $safeSiteName . '.
    </div>

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="
            width:100%;
            background-color:#f3f4f6;
        "
    >
        <tr>
            <td align="center" style="padding:35px 14px;">

                <!-- Contenedor principal -->
                <table
                    role="presentation"
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        width:100%;
                        max-width:600px;
                        background-color:#ffffff;
                        border:1px solid #e5e7eb;
                        border-collapse:separate;
                        border-spacing:0;
                    "
                >

                    <!-- Franja superior -->
                    <tr>
                        <td
                            height="5"
                            style="
                                height:5px;
                                background-color:' . $secondary . ';
                                font-size:0;
                                line-height:0;
                            "
                        >&nbsp;</td>
                    </tr>

                    <!-- HEADER -->
                    <tr>
                        <td
                            align="center"
                            style="
                                padding:38px 30px 36px;
                                ' . $brandBackground . '
                                color:#ffffff;
                            "
                        >

                            <div style="
                                color:#ff454d;
                                font-size:12px;
                                line-height:18px;
                                font-weight:bold;
                                letter-spacing:3px;
                                text-transform:uppercase;
                            ">
                                ' . $safeSiteName . '
                            </div>

                            <h1 style="
                                margin:22px 0 14px;
                                color:#ffffff;
                                font-size:32px;
                                line-height:1.25;
                                font-weight:800;
                                letter-spacing:-0.7px;
                            ">
                                ¡Bienvenido a la comunidad!
                            </h1>

                            <p style="
                                margin:0;
                                color:#d4d4d8;
                                font-size:15px;
                                line-height:1.8;
                            ">
                                El próximo antojo puede venir
                                con una sorpresa.
                            </p>

                            <div style="
                                width:54px;
                                height:3px;
                                margin:24px auto 0;
                                background-color:' . $secondary . ';
                                font-size:0;
                                line-height:0;
                            ">&nbsp;</div>

                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td style="
                            padding:34px 30px 14px;
                            background-color:#ffffff;
                        ">

                            <h2 style="
                                margin:0 0 16px;
                                color:' . $primary . ';
                                font-size:23px;
                                line-height:1.4;
                                font-weight:800;
                                letter-spacing:-0.3px;
                            ">
                                Esto se va a poner delicioso.
                            </h2>

                            <p style="
                                margin:0 0 18px;
                                color:#52525b;
                                font-size:15px;
                                line-height:1.9;
                            ">
                                Ya haces parte de ' . $safeSiteName . '.
                                Prepárate para conocer nuestras novedades,
                                descubrir promociones especiales y
                                enterarte de descuentos exclusivos
                                para nuestra comunidad.
                            </p>

                        </td>
                    </tr>

                    <!-- BENEFICIOS -->
                    <tr>
                        <td style="
                            padding:8px 30px 28px;
                            background-color:#ffffff;
                        ">

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width:100%;
                                    background-color:#fff5f5;
                                    border:1px solid #ffe0e0;
                                    border-radius:10px;
                                "
                            >
                                <tr>
                                    <td style="padding:23px 22px;">

                                        <div style="
                                            margin-bottom:16px;
                                            color:' . $secondary . ';
                                            font-size:11px;
                                            line-height:1.5;
                                            font-weight:bold;
                                            letter-spacing:1.5px;
                                        ">
                                            LO QUE VIENE PARA TI
                                        </div>

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                        >

                                            <tr>
                                                <td
                                                    width="30"
                                                    valign="top"
                                                    style="
                                                        padding:0 0 13px;
                                                        color:' . $secondary . ';
                                                        font-size:17px;
                                                        line-height:1.5;
                                                    "
                                                >
                                                    &#10003;
                                                </td>

                                                <td style="
                                                    padding:0 0 13px;
                                                    color:#3f3f46;
                                                    font-size:14px;
                                                    line-height:1.6;
                                                ">
                                                    <strong>
                                                        Novedades del menú
                                                    </strong>
                                                    <br>
                                                    Descubre lo nuevo
                                                    de nuestra cocina.
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    width="30"
                                                    valign="top"
                                                    style="
                                                        padding:0 0 13px;
                                                        color:' . $secondary . ';
                                                        font-size:17px;
                                                        line-height:1.5;
                                                    "
                                                >
                                                    &#10003;
                                                </td>

                                                <td style="
                                                    padding:0 0 13px;
                                                    color:#3f3f46;
                                                    font-size:14px;
                                                    line-height:1.6;
                                                ">
                                                    <strong>
                                                        Promociones especiales
                                                    </strong>
                                                    <br>
                                                    Entérate de nuestras
                                                    próximas promociones.
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    width="30"
                                                    valign="top"
                                                    style="
                                                        color:' . $secondary . ';
                                                        font-size:17px;
                                                        line-height:1.5;
                                                    "
                                                >
                                                    &#10003;
                                                </td>

                                                <td style="
                                                    color:#3f3f46;
                                                    font-size:14px;
                                                    line-height:1.6;
                                                ">
                                                    <strong>
                                                        Descuentos exclusivos
                                                    </strong>
                                                    <br>
                                                    Recibe novedades
                                                    especiales del boletín.
                                                </td>
                                            </tr>

                                        </table>

                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- LLAMADA A LA ACCIÓN -->
                    <tr>
                        <td align="center" style="
                            padding:0 30px 34px;
                            background-color:#ffffff;
                        ">

                            <p style="
                                margin:0 0 20px;
                                color:#52525b;
                                font-size:14px;
                                line-height:1.8;
                            ">
                                ¿Listo para descubrir tu próximo
                                favorito?
                            </p>

                            <table
                                role="presentation"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >
                                <tr>
                                    <td
                                        align="center"
                                        bgcolor="' . $secondary . '"
                                        style="
                                            border-radius:999px;
                                            background-color:' . $secondary . ';
                                        "
                                    >
                                        <a
                                            href="' . $safeHomeUrl . '"
                                            target="_blank"
                                            style="
                                                display:inline-block;
                                                padding:16px 28px;
                                                border:1px solid ' . $secondary . ';
                                                border-radius:999px;
                                                color:#ffffff;
                                                font-size:12px;
                                                line-height:1.4;
                                                font-weight:bold;
                                                letter-spacing:0.5px;
                                                text-decoration:none;
                                            "
                                        >
                                            DESCUBRIR BURGER TOWN
                                            &nbsp;&#8594;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="
                                margin:22px 0 0;
                                color:#71717a;
                                font-size:12px;
                                line-height:1.8;
                            ">
                                Queremos que cada visita tenga
                                algo especial.
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td
                            align="center"
                            style="
                                padding:27px 25px;
                                ' . $brandBackground . '
                                border-top:1px solid #292929;
                                text-align:center;
                            "
                        >

                            <div style="
                                color:#ffffff;
                                font-size:15px;
                                line-height:1.6;
                                font-weight:bold;
                                letter-spacing:0.5px;
                            ">
                                ' . $safeSiteName . '
                            </div>

                            <p style="
                                margin:9px 0 0;
                                color:#d4d4d8;
                                font-size:12px;
                                line-height:1.8;
                            ">
                                Gracias por ser parte de nuestra comunidad.
                            </p>

                            <div style="
                                margin-top:16px;
                                padding-top:15px;
                                border-top:1px solid #292929;
                                color:#a1a1aa;
                                font-size:11px;
                                line-height:1.8;
                            ">
                                &copy; ' . $year . ' ' . $safeSiteName . '.
                                Todos los derechos reservados.
                            </div>

                        </td>
                    </tr>

                    <!-- Franja inferior -->
                    <tr>
                        <td
                            height="4"
                            style="
                                height:4px;
                                background-color:' . $secondary . ';
                                font-size:0;
                                line-height:0;
                            "
                        >&nbsp;</td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>';

        /*
        |--------------------------------------------------------------------------
        | VERSIÓN DE TEXTO PLANO
        |--------------------------------------------------------------------------
        */

        $plainText =
            "¡Bienvenido a la comunidad de {$siteName}!\n\n" .
            "Esto se va a poner delicioso.\n\n" .
            "Ya haces parte de {$siteName}. Recibirás novedades " .
            "del menú, promociones especiales y descuentos exclusivos.\n\n" .
            "Descubre Burger Town: {$homeUrl}\n\n" .
            "Gracias por ser parte de nuestra comunidad.\n" .
            "© {$year} {$siteName}.";

        /*
        |--------------------------------------------------------------------------
        | ENVÍO SMTP
        |--------------------------------------------------------------------------
        */

        $this->send(
            $email,
            '¡Bienvenido a la comunidad de ' . $siteName . '!',
            $html,
            $plainText
        );
    }



    public function testConnection(
        string $recipient
    ): void {

        $mail =
            $this->createMailer();

        $mail->addAddress(
            $recipient
        );

        $mail->Subject =
            'Prueba de correo — Burger Town';

        $mail->Body =
            $this->renderTestEmail();

        $mail->AltBody =
            'La configuración SMTP de Burger Town funciona correctamente.';

        $mail->send();
    }

    private function getSetting(
        string $key,
               $default = ''
    ) {
        $setting =
            $this->settingRepository->get(
                'mail',
                $key
            );

        if (!$setting) {
            return $default;
        }

        return $setting['setting_value']
            ?? $default;
    }

    private function getBooleanSetting(
        string $key,
        bool $default
    ): bool {

        $value =
            $this->getSetting(
                $key,
                $default ? '1' : '0'
            );

        return in_array(
            strtolower(
                trim((string) $value)
            ),
            ['1', 'true', 'yes', 'on'],
            true
        );
    }





    private function renderTestEmail(): string
    {
        $escape = static function ($value): string {
            return htmlspecialchars(
                (string) ($value ?? ''),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
        };

        /*
        |--------------------------------------------------------------------------
        | IDENTIDAD VISUAL
        |--------------------------------------------------------------------------
        */

        $siteName = $escape(
            SiteConfig::siteName() ?: 'Burger Town'
        );

        $description = $escape(
            SiteConfig::siteDescription()
        );

        $primaryColor = SiteConfig::primaryColor();
        $secondaryColor = SiteConfig::secondaryColor();
        $font = SiteConfig::font();

        // Validar colores configurados en el administrador.
        $primaryColor = preg_match(
            '/^#[0-9a-fA-F]{6}$/',
            $primaryColor
        ) ? $primaryColor : '#000000';

        $secondaryColor = preg_match(
            '/^#[0-9a-fA-F]{6}$/',
            $secondaryColor
        ) ? $secondaryColor : '#C6111A';

        // Sanitizar tipografía.
        $font = preg_replace(
            '/[^a-zA-Z0-9,\s\'"-]/',
            '',
            (string) $font
        );

        if (trim($font) === '') {
            $font = 'Arial, Helvetica, sans-serif';
        }

        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        $logoFile = SiteConfig::siteLogo();

        $baseUrl = defined('APP_URL')
            ? rtrim(APP_URL, '/')
            : '';

        $logoUrl = '';

        if (
            is_string($logoFile)
            && trim($logoFile) !== ''
            && $baseUrl !== ''
        ) {
            $logoUrl = $baseUrl
                . '/assets/img/settings/'
                . rawurlencode(basename($logoFile));
        }

        /*
        |--------------------------------------------------------------------------
        | FONDO CORPORATIVO
        |--------------------------------------------------------------------------
        */

        $brandBackground = '
        background-color:#090909;
        background-image:
            radial-gradient(
                circle at 80% 10%,
                rgba(215, 25, 32, .24),
                transparent 18rem
            ),
            linear-gradient(
                160deg,
                #090909 0%,
                #111111 65%,
                #1b0a0b 100%
            );
    ';

        /*
        |--------------------------------------------------------------------------
        | HTML DEL LOGO
        |--------------------------------------------------------------------------
        */

        $logoHtml = '';

        if ($logoUrl !== '') {
            $logoHtml = '
            <tr>
                <td
                    align="center"
                    style="
                        padding:28px 30px 12px;
                        ' . $brandBackground . '
                    "
                >
                    <img
                        src="' . $escape($logoUrl) . '"
                        alt="' . $siteName . '"
                        width="150"
                        style="
                            display:block;
                            width:150px;
                            max-width:100%;
                            height:auto;
                            border:0;
                            outline:none;
                            text-decoration:none;
                        "
                    >
                </td>
            </tr>';
        }

        /*
        |--------------------------------------------------------------------------
        | FECHA
        |--------------------------------------------------------------------------
        */

        $year = date('Y');

        /*
        |--------------------------------------------------------------------------
        | PLANTILLA HTML
        |--------------------------------------------------------------------------
        */

        return '
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta name="color-scheme" content="light">
    <title>Prueba de correo - ' . $siteName . '</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f3f4f6;
    font-family:' . $font . ';
    color:#18181b;
    -webkit-text-size-adjust:100%;
">

    <!-- Preencabezado -->
    <div style="
        display:none;
        font-size:1px;
        color:#f3f4f6;
        line-height:1px;
        max-height:0;
        max-width:0;
        opacity:0;
        overflow:hidden;
    ">
        Verificación de la configuración de correo de ' . $siteName . '.
    </div>

    <!-- Fondo exterior -->
    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="
            width:100%;
            background-color:#f3f4f6;
        "
    >
        <tr>
            <td align="center" style="padding:38px 14px;">

                <!-- Contenedor principal -->
                <table
                    role="presentation"
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        width:100%;
                        max-width:600px;
                        background-color:#ffffff;
                        border:1px solid #e5e7eb;
                        border-radius:12px;
                        border-collapse:separate;
                        border-spacing:0;
                        overflow:hidden;
                    "
                >

                    <!-- Franja superior -->
                    <tr>
                        <td
                            height="6"
                            style="
                                height:6px;
                                background-color:' . $secondaryColor . ';
                                font-size:0;
                                line-height:0;
                            "
                        >&nbsp;</td>
                    </tr>

                    <!-- Logo con fondo corporativo -->
                    ' . $logoHtml . '

                    <!-- Header corporativo -->
                    <tr>
                        <td
                            align="center"
                            style="
                                padding:20px 32px 30px;
                                ' . $brandBackground . '
                                color:#ffffff;
                            "
                        >

                            <div style="
                                font-size:12px;
                                line-height:18px;
                                font-weight:bold;
                                letter-spacing:2px;
                                text-transform:uppercase;
                                color:#ff555d;
                            ">
                                SISTEMA DE NOTIFICACIONES
                            </div>

                            <h1 style="
                                margin:14px 0 10px;
                                font-size:28px;
                                line-height:1.3;
                                font-weight:800;
                                letter-spacing:-0.5px;
                                color:#ffffff;
                            ">
                                Correo de prueba
                            </h1>

                            <p style="
                                margin:0;
                                font-size:14px;
                                line-height:1.8;
                                color:#d4d4d8;
                            ">
                                ' . $description . '
                            </p>

                        </td>
                    </tr>

                    <!-- Separador -->
                    <tr>
                        <td style="
                            padding:0 32px;
                            background-color:#ffffff;
                            font-size:0;
                            line-height:0;
                        ">
                            <div style="
                                border-top:1px solid #e5e7eb;
                            ">&nbsp;</div>
                        </td>
                    </tr>

                    <!-- Contenido principal -->
                    <tr>
                        <td style="
                            padding:30px 32px 12px;
                            background-color:#ffffff;
                        ">

                            <!-- Estado del envío -->
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width:100%;
                                    background-color:#f0fdf4;
                                    border:1px solid #bbf7d0;
                                    border-radius:8px;
                                "
                            >
                                <tr>
                                    <td
                                        width="48"
                                        valign="top"
                                        style="padding:18px 0 18px 18px;"
                                    >
                                        <div style="
                                            width:32px;
                                            height:32px;
                                            line-height:32px;
                                            text-align:center;
                                            border-radius:50%;
                                            background-color:#dcfce7;
                                            color:#166534;
                                            font-size:20px;
                                            font-weight:bold;
                                        ">
                                            &#10003;
                                        </div>
                                    </td>

                                    <td
                                        valign="middle"
                                        style="padding:18px;"
                                    >
                                        <div style="
                                            font-size:16px;
                                            line-height:1.5;
                                            font-weight:bold;
                                            color:#166534;
                                        ">
                                            Envío realizado correctamente
                                        </div>

                                        <div style="
                                            margin-top:5px;
                                            font-size:13px;
                                            line-height:1.7;
                                            color:#166534;
                                        ">
                                            El servidor ha enviado este
                                            mensaje de prueba.
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Resumen de validaciones -->
                    <tr>
                        <td style="
                            padding:18px 32px 30px;
                            background-color:#ffffff;
                        ">

                            <h2 style="
                                margin:0 0 18px;
                                font-size:17px;
                                line-height:1.5;
                                color:#18181b;
                            ">
                                Resumen de la prueba
                            </h2>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >

                                <tr>
                                    <td
                                        width="28"
                                        valign="top"
                                        style="
                                            padding:10px 0;
                                            color:#16a34a;
                                            font-size:17px;
                                        "
                                    >
                                        &#10003;
                                    </td>

                                    <td style="
                                        padding:10px 0;
                                        border-bottom:1px solid #f0f0f0;
                                        font-size:14px;
                                        line-height:1.6;
                                        color:#3f3f46;
                                    ">
                                        Conexión con el servidor SMTP
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        width="28"
                                        valign="top"
                                        style="
                                            padding:10px 0;
                                            color:#16a34a;
                                            font-size:17px;
                                        "
                                    >
                                        &#10003;
                                    </td>

                                    <td style="
                                        padding:10px 0;
                                        border-bottom:1px solid #f0f0f0;
                                        font-size:14px;
                                        line-height:1.6;
                                        color:#3f3f46;
                                    ">
                                        Autenticación del remitente
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        width="28"
                                        valign="top"
                                        style="
                                            padding:10px 0;
                                            color:#16a34a;
                                            font-size:17px;
                                        "
                                    >
                                        &#10003;
                                    </td>

                                    <td style="
                                        padding:10px 0;
                                        font-size:14px;
                                        line-height:1.6;
                                        color:#3f3f46;
                                    ">
                                        Entrega del mensaje de prueba
                                    </td>
                                </tr>

                            </table>

                            <p style="
                                margin:24px 0 0;
                                font-size:13px;
                                line-height:1.8;
                                color:#71717a;
                            ">
                                Si estás recibiendo este mensaje, el
                                sistema puede entregar correos a esta
                                dirección. Esta prueba no garantiza por
                                sí sola la entrega en todas las bandejas
                                de entrada.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer con fondo corporativo -->
                    <tr>
                        <td
                            align="center"
                            style="
                                padding:26px 30px;
                                ' . $brandBackground . '
                                border-top:1px solid #292929;
                                text-align:center;
                            "
                        >

                            <div style="
                                font-size:15px;
                                line-height:1.7;
                                font-weight:bold;
                                color:#ffffff;
                            ">
                                ' . $siteName . '
                            </div>

                            <div style="
                                margin-top:8px;
                                font-size:12px;
                                line-height:1.8;
                                color:#d4d4d8;
                            ">
                                Mensaje automático de verificación.
                            </div>

                            <div style="
                                margin-top:5px;
                                font-size:11px;
                                line-height:1.8;
                                color:#a1a1aa;
                            ">
                                &copy; ' . $year . ' ' . $siteName . '.
                                Todos los derechos reservados.
                            </div>

                        </td>
                    </tr>

                    <!-- Franja inferior -->
                    <tr>
                        <td
                            height="4"
                            style="
                                height:4px;
                                background-color:' . $secondaryColor . ';
                                font-size:0;
                                line-height:0;
                            "
                        >&nbsp;</td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>';
    }





    /**
     * Envía confirmación de reserva al cliente.
     */
    public function sendReservationCreatedCustomer(
        array $reservation
    ): void {

        $customerEmail =
            trim((string) (
                $reservation['customer_email'] ?? ''
            ));

        if (
            $customerEmail === '' ||
            !filter_var(
                $customerEmail,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            return;
        }

        $customerName =
            trim((string) (
                $reservation['customer_name'] ??
                'Cliente'
            ));

        $subject =
            'Reserva confirmada · ' .
            ($reservation['reservation_code'] ?? 'Burger Town');

        $html =
            $this->renderReservationEmail(
                'created-customer',
                $reservation,
                $customerName
            );

        $plainText =
            $this->buildReservationPlainText(
                $reservation,
                'Tu reserva ha sido confirmada correctamente.'
            );

        $this->send(
            $customerEmail,
            $subject,
            $html,
            $plainText
        );
    }


    /**
     * Envía nueva reserva al administrador.
     */
    public function sendReservationCreatedAdmin(
        array $reservation,
        array $recipient
    ): void {

        $email =
            trim((string) (
                $recipient['email'] ?? ''
            ));

        if (
            $email === '' ||
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            return;
        }

        $recipientName =
            trim(
                (string) (
                    ($recipient['first_name'] ?? '') .
                    ' ' .
                    ($recipient['last_name'] ?? '')
                )
            );

        if ($recipientName === '') {
            $recipientName = 'Administrador';
        }

        $subject =
            'Nueva reserva · ' .
            ($reservation['reservation_code'] ?? 'Burger Town');

        $html =
            $this->renderReservationEmail(
                'created-admin',
                $reservation,
                $recipientName
            );

        $plainText =
            $this->buildReservationPlainText(
                $reservation,
                'Se ha registrado una nueva reserva.'
            );

        $this->send(
            $email,
            $subject,
            $html,
            $plainText
        );
    }


    /**
     * Envía cancelación al cliente.
     */
    public function sendReservationCancelledCustomer(
        array $reservation
    ): void {

        $customerEmail =
            trim((string) (
                $reservation['customer_email'] ?? ''
            ));

        if (
            $customerEmail === '' ||
            !filter_var(
                $customerEmail,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            return;
        }

        $customerName =
            trim((string) (
                $reservation['customer_name'] ??
                'Cliente'
            ));

        $subject =
            'Reserva cancelada · ' .
            ($reservation['reservation_code'] ?? 'Burger Town');

        $html =
            $this->renderReservationEmail(
                'cancelled-customer',
                $reservation,
                $customerName
            );

        $plainText =
            $this->buildReservationPlainText(
                $reservation,
                'Tu reserva ha sido cancelada.'
            );

        $this->send(
            $customerEmail,
            $subject,
            $html,
            $plainText
        );
    }


    /**
     * Envía cancelación al administrador.
     */
    public function sendReservationCancelledAdmin(
        array $reservation,
        array $recipient
    ): void {

        $email =
            trim((string) (
                $recipient['email'] ?? ''
            ));

        if (
            $email === '' ||
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            return;
        }

        $recipientName =
            trim(
                (string) (
                    ($recipient['first_name'] ?? '') .
                    ' ' .
                    ($recipient['last_name'] ?? '')
                )
            );

        if ($recipientName === '') {
            $recipientName = 'Administrador';
        }

        $subject =
            'Reserva cancelada · ' .
            ($reservation['reservation_code'] ?? 'Burger Town');

        $html =
            $this->renderReservationEmail(
                'cancelled-admin',
                $reservation,
                $recipientName
            );

        $plainText =
            $this->buildReservationPlainText(
                $reservation,
                'Una reserva ha sido cancelada.'
            );

        $this->send(
            $email,
            $subject,
            $html,
            $plainText
        );
    }

    /**
     * Renderiza los correos de reservas.
     */
    private function renderReservationEmail(
        string $type,
        array $reservation,
        string $recipientName
    ): string {

        $reservationCode =
            htmlspecialchars(
                (string) (
                    $reservation['reservation_code'] ?? ''
                ),
                ENT_QUOTES,
                'UTF-8'
            );

        $customerName =
            htmlspecialchars(
                (string) (
                    $reservation['customer_name'] ??
                    'Cliente'
                ),
                ENT_QUOTES,
                'UTF-8'
            );

        $branchName =
            htmlspecialchars(
                (string) (
                    $reservation['branch_name'] ??
                    'Sede Burger Town'
                ),
                ENT_QUOTES,
                'UTF-8'
            );

        $branchPhone =
            htmlspecialchars(
                (string) (
                    $reservation['branch_phone'] ?? ''
                ),
                ENT_QUOTES,
                'UTF-8'
            );

        $date =
            htmlspecialchars(
                $this->formatReservationDate(
                    (string) (
                        $reservation['reservation_date'] ?? ''
                    )
                ),
                ENT_QUOTES,
                'UTF-8'
            );

        $startTime =
            htmlspecialchars(
                substr(
                    (string) (
                        $reservation['start_time'] ?? ''
                    ),
                    0,
                    5
                ),
                ENT_QUOTES,
                'UTF-8'
            );

        $endTime =
            htmlspecialchars(
                substr(
                    (string) (
                        $reservation['end_time'] ?? ''
                    ),
                    0,
                    5
                ),
                ENT_QUOTES,
                'UTF-8'
            );

        $people =
            (int) (
                $reservation['people_count'] ?? 0
            );

        $recipientName =
            htmlspecialchars(
                $recipientName,
                ENT_QUOTES,
                'UTF-8'
            );



        $isCancelled = str_contains($type, 'cancelled');
        $isAdmin = str_contains($type, 'admin');

        $isCustomerConfirmation = $type === 'created-customer';

        $isAdminNotification = $type === 'created-admin';

        $isBrandedReservationEmail = in_array(
            $type,
            ['created-customer', 'created-admin'],
            true
        );

        $primaryColor = SiteConfig::primaryColor();
        $secondaryColor = SiteConfig::secondaryColor();
        $font = SiteConfig::font();

        $primaryColor = preg_match(
            '/^#[0-9a-fA-F]{6}$/',
            $primaryColor
        ) ? $primaryColor : '#000000';

        $secondaryColor = preg_match(
            '/^#[0-9a-fA-F]{6}$/',
            $secondaryColor
        ) ? $secondaryColor : '#C6111A';

        $font = preg_replace(
            '/[^a-zA-Z0-9,\s\'"-]/',
            '',
            (string) $font
        );

        if (trim($font) === '') {
            $font = 'Arial, Helvetica, sans-serif';
        }

        $brandBackground = '
    background-color:#090909;
    background-image:
        radial-gradient(
            circle at 80% 10%,
            rgba(215, 25, 32, .24),
            transparent 18rem
        ),
        linear-gradient(
            160deg,
            #090909 0%,
            #111111 65%,
            #1b0a0b 100%
        );
';

        if ($isCancelled) {
            $accent = '#C6111A';

            $title = $isAdmin
                ? 'Reserva cancelada'
                : 'Tu reserva ha sido cancelada';

            $intro = $isAdmin
                ? 'Se ha cancelado una reserva registrada en tu sede.'
                : 'Te confirmamos que tu reserva ha sido cancelada correctamente.';

        } else {
            $accent = (
                $isCustomerConfirmation ||
                $isAdminNotification
            )
                ? $secondaryColor
                : '#14b8a6';

            $title = $isAdmin
                ? 'Nueva reserva recibida'
                : '¡Tu reserva está confirmada!';

            $intro = $isAdmin
                ? 'Se ha registrado una nueva reserva en tu sede.'
                : 'Gracias por reservar con nosotros. Estos son los detalles de tu reserva.';
        }



        $actionUrl =
            defined('APP_URL')
                ? APP_URL . '/consultar-reserva'
                : '';


        $logoUrl =
            defined('APP_URL')
                ? APP_URL . '/assets/img/settings/site-logo-be31713516a5e456.png'
                : '';



        $actionUrl = $isAdminNotification
            ? rtrim(APP_URL, '/') . '/admin/reservations'
            : $actionUrl;

        $actionButton = $actionUrl !== ''
            ? '
        <a
            href="' . htmlspecialchars(
                $actionUrl,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ) . '"
            target="_blank"
            style="
                display:inline-block;
                padding:14px 24px;
                background:' . $accent . ';
                color:#ffffff;
                text-decoration:none;
                border-radius:10px;
                font-size:14px;
                font-weight:700;
                line-height:1.5;
            "
        >
            Consultar reserva &#8594;
        </a>
    '
            : '';



        return '
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f3f4f6;
    font-family:' . $font . ';
    color:#18181b;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="background:#eef2f6;"
>
<tr>
<td align="center" style="padding:35px 15px;">

<table
    width="620"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        width:100%;
        max-width:620px;
        background:#ffffff;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 12px 35px rgba(15,23,42,.10);
    "
>

    
<!-- HEADER -->
<tr>
    <td style="
        padding:30px 34px;
        ' . ($isBrandedReservationEmail
                ? $brandBackground
                : 'background:#0f172a;') . '
    ">

        <div style="
            font-size:12px;
            font-weight:800;
            color:#ff454d;
            letter-spacing:3px;
            text-transform:uppercase;
        ">
            BURGER TOWN
        </div>

        <div style="
            margin-top:18px;
            font-size:27px;
            line-height:1.3;
            font-weight:800;
            color:#ffffff;
            letter-spacing:-0.5px;
        ">
            ' . $title . '
        </div>

        <div style="
            margin-top:10px;
            color:#d4d4d8;
            font-size:13px;
            line-height:1.7;
        ">
            Restaurante &amp; Reservas
        </div>

        <div style="
            height:3px;
            width:54px;
            margin-top:22px;
            background-color:' . $accent . ';
            font-size:0;
            line-height:0;
        ">&nbsp;</div>

    </td>
</tr>



    <!-- CONTENT -->

    <tr>
        <td style="padding:36px 34px;">

            <div style="
                color:#64748b;
                font-size:13px;
                margin-bottom:8px;
            ">
                Hola ' . $recipientName . ',
            </div>

            <h1 style="
                margin:0;
                color:#0f172a;
                font-size:25px;
                line-height:1.3;
            ">
                ' . $title . '
            </h1>

            <p style="
                margin:14px 0 28px;
                color:#64748b;
                font-size:14px;
                line-height:1.7;
            ">
                ' . $intro . '
            </p>


            <!-- RESERVATION CODE -->

            <div style="
                padding:20px;
                background:#f8fafc;
                border:1px solid #e2e8f0;
                border-radius:14px;
                text-align:center;
            ">

                <div style="
                    color:#94a3b8;
                    font-size:10px;
                    font-weight:800;
                    letter-spacing:1.4px;
                ">
                    CÓDIGO DE RESERVA
                </div>

                <div style="
                    margin-top:8px;
                    color:' . $accent . ';
                    font-size:25px;
                    font-weight:800;
                    letter-spacing:2px;
                ">
                    ' . $reservationCode . '
                </div>

            </div>


            <!-- DETAILS -->

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="margin-top:22px;"
            >

                <tr>

                    <td style="
                        width:50%;
                        padding:12px 0;
                        border-bottom:1px solid #eef2f7;
                    ">

                        <div style="
                            color:#94a3b8;
                            font-size:10px;
                            font-weight:700;
                            text-transform:uppercase;
                        ">
                            Sede
                        </div>

                        <div style="
                            margin-top:5px;
                            color:#334155;
                            font-size:13px;
                            font-weight:700;
                        ">
                            ' . $branchName . '
                        </div>

                    </td>


                    <td style="
                        width:50%;
                        padding:12px 0;
                        border-bottom:1px solid #eef2f7;
                    ">

                        <div style="
                            color:#94a3b8;
                            font-size:10px;
                            font-weight:700;
                            text-transform:uppercase;
                        ">
                            Personas
                        </div>

                        <div style="
                            margin-top:5px;
                            color:#334155;
                            font-size:13px;
                            font-weight:700;
                        ">
                            ' . $people . '
                        </div>

                    </td>

                </tr>


                <tr>

                    <td style="
                        padding:12px 0;
                        border-bottom:1px solid #eef2f7;
                    ">

                        <div style="
                            color:#94a3b8;
                            font-size:10px;
                            font-weight:700;
                            text-transform:uppercase;
                        ">
                            Fecha
                        </div>

                        <div style="
                            margin-top:5px;
                            color:#334155;
                            font-size:13px;
                            font-weight:700;
                        ">
                            ' . $date . '
                        </div>

                    </td>


                    <td style="
                        padding:12px 0;
                        border-bottom:1px solid #eef2f7;
                    ">

                        <div style="
                            color:#94a3b8;
                            font-size:10px;
                            font-weight:700;
                            text-transform:uppercase;
                        ">
                            Horario
                        </div>

                        <div style="
                            margin-top:5px;
                            color:#334155;
                            font-size:13px;
                            font-weight:700;
                        ">
                            ' . $startTime . ' — ' . $endTime . '
                        </div>

                    </td>

                </tr>

            </table>


            <!-- CUSTOMER -->

            <div style="
                margin-top:22px;
                padding:15px 17px;
                background:#f8fafc;
                border-radius:11px;
            ">

                <div style="
                    color:#94a3b8;
                    font-size:10px;
                    font-weight:800;
                    text-transform:uppercase;
                ">
                    Cliente
                </div>

                <div style="
                    margin-top:5px;
                    color:#334155;
                    font-size:13px;
                    font-weight:700;
                ">
                    ' . $customerName . '
                </div>

            </div>


            <!-- ACTION -->

            <div style="
                margin-top:30px;
                text-align:center;
            ">
                ' . $actionButton . '
            </div>


            ' . (
            $branchPhone !== ''
                ? '
                    <div style="
                        margin-top:25px;
                        text-align:center;
                        color:#94a3b8;
                        font-size:11px;
                    ">
                        ¿Necesitas ayuda?
                        Contacta a la sede: ' .
                $branchPhone .
                '</div>
                    '
                : ''
            ) . '

        </td>
    </tr>


    
    <!-- FOOTER -->
    <tr>
        <td style="
            padding:25px 30px;
            ' . ($isBrandedReservationEmail
                ? $brandBackground
                : 'background:#f8fafc;') . '
            border-top:1px solid #292929;
            text-align:center;
        ">
    
            <div style="
                color:' . ($isCustomerConfirmation ? '#ffffff' : '#64748b') . ';
                font-size:13px;
                line-height:1.7;
                font-weight:700;
            ">
                Burger Town
            </div>
    
            <div style="
                margin-top:7px;
                color:' . ($isCustomerConfirmation ? '#d4d4d8' : '#94a3b8') . ';
                font-size:11px;
                line-height:1.8;
            ">
                Este correo fue enviado automáticamente por Burger Town.
                <br>
                Por favor, no respondas directamente a este mensaje.
            </div>
    
        </td>
    </tr>


</table>

</td>
</tr>
</table>

</body>
</html>';
    }


    /**
     * Texto plano alternativo.
     */
    private function buildReservationPlainText(
        array $reservation,
        string $message
    ): string {

        return
            $message . PHP_EOL .
            PHP_EOL .
            'Código: ' .
            ($reservation['reservation_code'] ?? '') .
            PHP_EOL .
            'Sede: ' .
            ($reservation['branch_name'] ?? '') .
            PHP_EOL .
            'Fecha: ' .
            ($reservation['reservation_date'] ?? '') .
            PHP_EOL .
            'Hora: ' .
            substr(
                (string) ($reservation['start_time'] ?? ''),
                0,
                5
            ) .
            ' - ' .
            substr(
                (string) ($reservation['end_time'] ?? ''),
                0,
                5
            ) .
            PHP_EOL .
            'Personas: ' .
            ($reservation['people_count'] ?? 0) .
            PHP_EOL;
    }


    /**
     * Formatea la fecha de la reserva.
     */
    private function formatReservationDate(
        string $date
    ): string {

        if ($date === '') {
            return '';
        }

        $timestamp =
            strtotime($date);

        if ($timestamp === false) {
            return $date;
        }

        return date(
            'd/m/Y',
            $timestamp
        );
    }

    public function sendContactMessage(string $to, array $contact): void
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return;
        $name=htmlspecialchars((string)$contact['name'],ENT_QUOTES,'UTF-8');
        $email=htmlspecialchars((string)$contact['email'],ENT_QUOTES,'UTF-8');
        $phone=htmlspecialchars((string)($contact['phone']??''),ENT_QUOTES,'UTF-8');
        $subject=htmlspecialchars((string)($contact['subject']??'Consulta Burger Town'),ENT_QUOTES,'UTF-8');
        $message=nl2br(htmlspecialchars((string)$contact['message'],ENT_QUOTES,'UTF-8'));
        $html=$this->burgerTownEmailShell('Nuevo mensaje de contacto',"<p>Recibiste una nueva solicitud desde el sitio web de <strong>Burger Town</strong>.</p><div style=\"background:#f8fafc;border-radius:14px;padding:20px;margin:20px 0\"><p><strong>Nombre:</strong> {$name}</p><p><strong>Correo:</strong> {$email}</p><p><strong>Teléfono:</strong> {$phone}</p><p><strong>Asunto:</strong> {$subject}</p><p><strong>Mensaje:</strong><br>{$message}</p></div>", 'Atención al cliente');
        $this->send($to,'Nuevo mensaje de contacto · Burger Town',$html,"Nuevo mensaje de contacto de {$contact['name']} ({$contact['email']}).\n\n{$contact['message']}");
    }

    public function sendContactConfirmation(string $to,string $name,array $contact): void
    {
        if (!filter_var($to,FILTER_VALIDATE_EMAIL)) return;
        $safe=htmlspecialchars($name,ENT_QUOTES,'UTF-8');
        $html=$this->burgerTownEmailShell('Recibimos tu mensaje',"<p>Hola <strong>{$safe}</strong>,</p><p>Gracias por escribir a Burger Town. Recibimos tu solicitud y nuestro equipo la revisará para responderte lo antes posible.</p><div style=\"margin:22px 0;padding:18px;border-radius:14px;background:#fff8db;border-left:4px solid #f6d400\"><strong>Tu solicitud fue registrada correctamente.</strong></div><p>Si necesitas reservar una mesa, puedes hacerlo directamente desde nuestro sitio web.</p>",'Atención Burger Town');
        $this->send($to,'Recibimos tu mensaje · Burger Town',$html,"Hola {$name}. Recibimos tu mensaje y te responderemos pronto.");
    }

    public function sendEventRequestAdmin(array $event,array $recipient): void
    {
        $to=trim((string)($recipient['email']??'')); if(!filter_var($to,FILTER_VALIDATE_EMAIL)) return;
        $recipientName=trim((string)(($recipient['first_name']??'').' '.($recipient['last_name']??''))) ?: 'Administrador';
        $html=$this->renderEventEmail($event,$recipientName,false);
        $this->send($to,'Nuevo evento solicitado · Burger Town · '.($event['branch_name']??''),$html,$this->eventPlain($event,'Nueva solicitud de evento.'));
    }

    public function sendEventConfirmation(array $event): void
    {
        $to=trim((string)($event['email']??'')); if(!filter_var($to,FILTER_VALIDATE_EMAIL)) return;
        $name=trim((string)($event['name']??'Cliente'));
        $html=$this->renderEventEmail($event,$name,true);
        $this->send($to,'Solicitud de evento recibida · Burger Town',$html,$this->eventPlain($event,'Recibimos tu solicitud de evento.'));
    }

    private function eventPlain(array $event,string $intro): string
    {
        return $intro."\n\nCliente: ".($event['name']??'')."\nSede: ".($event['branch_name']??'')."\nTipo: ".($event['event_type']??'')."\nFecha: ".($event['event_date']??'')."\nHora: ".substr((string)($event['event_time']??''),0,5)."\nInvitados: ".($event['guests']??0)."\nCorreo: ".($event['email']??'')."\nTeléfono: ".($event['phone']??'')."\nMensaje: ".($event['message']??'');
    }


    private function renderEventEmail(
        array $event,
        string $recipientName,
        bool $customer
    ): string {
        $safe = static function ($value): string {
            return htmlspecialchars(
                (string) $value,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
        };

        $name = $safe($event['name'] ?? '');
        $branch = $safe($event['branch_name'] ?? '');
        $type = $safe($event['event_type'] ?? '');
        $date = $safe($event['event_date'] ?? '');
        $time = $safe(
            substr((string) ($event['event_time'] ?? ''), 0, 5)
        );
        $guests = (int) ($event['guests'] ?? 0);
        $phone = $safe($event['phone'] ?? '');
        $email = $safe($event['email'] ?? '');
        $message = nl2br($safe($event['message'] ?? ''));
        $greeting = $safe($recipientName);

        $title = $customer
            ? 'Recibimos tu solicitud de evento'
            : 'Nuevo evento solicitado';

        $intro = $customer
            ? 'Gracias por confiar en Burger Town. La sede revisará la disponibilidad y se pondrá en contacto contigo para confirmar los detalles.'
            : 'Se ha recibido una nueva solicitud para organizar un evento. Revisa la información y contacta al cliente para continuar con la gestión.';

        $label = $customer
            ? 'Solicitud recibida'
            : 'Nueva solicitud de evento';

        $details = '
        <table role="presentation" width="100%" cellpadding="0"
            cellspacing="0" style="border-collapse:separate;border-spacing:0 9px">
            <tr>
                <td style="padding:13px 15px;background:#f7f7f7;border-radius:10px">
                    <div style="font-size:11px;color:#777;font-weight:700;text-transform:uppercase;letter-spacing:1px">Sede</div>
                    <div style="font-size:15px;color:#171717;font-weight:700;margin-top:4px">' . $branch . '</div>
                </td>
            </tr>
            <tr>
                <td style="padding:13px 15px;background:#fff8e0;border-left:4px solid #f6d400;border-radius:8px">
                    <div style="font-size:11px;color:#806700;font-weight:700;text-transform:uppercase;letter-spacing:1px">Tipo de evento</div>
                    <div style="font-size:16px;color:#171717;font-weight:800;margin-top:4px">' . $type . '</div>
                </td>
            </tr>
            <tr>
                <td style="padding:13px 15px;background:#f7f7f7;border-radius:10px">
                    <div style="font-size:11px;color:#777;font-weight:700;text-transform:uppercase;letter-spacing:1px">Fecha y hora</div>
                    <div style="font-size:15px;color:#171717;font-weight:700;margin-top:4px">' . $date . ' · ' . $time . '</div>
                </td>
            </tr>
            <tr>
                <td style="padding:13px 15px;background:#f7f7f7;border-radius:10px">
                    <div style="font-size:11px;color:#777;font-weight:700;text-transform:uppercase;letter-spacing:1px">Número de invitados</div>
                    <div style="font-size:22px;color:#171717;font-weight:800;margin-top:4px">' . $guests . '</div>
                </td>
            </tr>
        </table>

        <h3 style="font-size:15px;color:#171717;margin:26px 0 12px">Datos del cliente</h3>
        <div style="padding:17px;background:#fafafa;border:1px solid #eee;border-radius:12px">
            <p style="margin:0 0 10px"><strong>Nombre:</strong> ' . $name . '</p>
            <p style="margin:0 0 10px"><strong>Correo:</strong> ' . $email . '</p>
            <p style="margin:0"><strong>Teléfono:</strong> ' . $phone . '</p>
        </div>

        <h3 style="font-size:15px;color:#171717;margin:26px 0 12px">Detalles adicionales</h3>
        <div style="padding:17px;background:#fafafa;border:1px solid #eee;border-radius:12px;color:#444;overflow-wrap:anywhere">'
            . ($message !== '' ? $message : 'No se proporcionaron detalles adicionales.') .
            '</div>
    ';

        $body = '
        <p style="margin:0 0 14px">Hola <strong>' . $greeting . '</strong>,</p>
        <p style="margin:0 0 22px">' . $intro . '</p>
        <div style="display:inline-block;background:#fff0f0;color:#b91c1c;border-radius:7px;padding:8px 12px;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase">'
            . $label .
            '</div>
        ' . $details . '
        <div style="margin-top:25px;padding:16px;border-radius:10px;background:#111;color:#fff;font-size:13px;line-height:1.7">
            ' . (
            $customer
                ? 'Gracias por elegir Burger Town. Te contactaremos para dar seguimiento a tu solicitud.'
                : 'Por favor, revisa los datos y comunícate con el cliente para gestionar su solicitud.'
            ) . '
        </div>
    ';

        $site = htmlspecialchars(
            SiteConfig::siteName() ?: 'Burger Town',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        return '<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $safe($title) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f1f1;font-family:Arial,Helvetica,sans-serif;color:#171717">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f1f1">
        <tr>
            <td align="center" style="padding:28px 12px">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="width:100%;max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden">
                    <tr>
                        <td style="background-color:#090909;background-image:radial-gradient(circle at 80% 10%,rgba(215,25,32,.24),transparent 18rem),linear-gradient(160deg,#090909 0%,#111111 65%,#1b0a0b 100%);padding:30px 28px">
                            <div style="color:#f6d400;font-size:12px;font-weight:800;letter-spacing:3px">BURGER TOWN</div>
                            <div style="height:3px;width:45px;background:#d71920;margin:15px 0"></div>
                            <h1 style="margin:0;color:#ffffff;font-size:25px;line-height:1.3;font-weight:800">' . $safe($title) . '</h1>
                            <p style="margin:10px 0 0;color:#d0d0d0;font-size:13px">Eventos · ' . $branch . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;font-size:14px;line-height:1.75;color:#444">
                            ' . $body . '
                            <div style="margin-top:30px;padding-top:18px;border-top:1px solid #eeeeee;color:#888;font-size:11px;line-height:1.7">
                                Este correo fue enviado automáticamente por ' . $site . '.<br>
                                &copy; ' . date('Y') . ' Burger Town · Bogotá, Colombia
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    private function burgerTownEmailShell(string $title,string $body,string $eyebrow): string
    {
        $site=htmlspecialchars(SiteConfig::siteName() ?: 'Burger Town',ENT_QUOTES,'UTF-8');
        return '<!doctype html><html lang="es"><body style="margin:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;color:#171717"><table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4"><tr><td align="center" style="padding:35px 14px"><table width="620" cellpadding="0" cellspacing="0" style="max-width:620px;width:100%;background:#fff;border-radius:22px;overflow:hidden"><tr><td style="background:#090909;padding:30px 36px"><div style="color:#f6d400;font-weight:800;font-size:12px;letter-spacing:2px">BURGER TOWN</div><div style="color:#fff;font-size:25px;font-weight:800;margin-top:8px">'.$title.'</div></td></tr><tr><td style="padding:35px 36px"><div style="display:inline-block;background:#fff5c7;color:#9a7600;border-radius:999px;padding:7px 11px;font-size:10px;font-weight:800;letter-spacing:1px;text-transform:uppercase">'.$eyebrow.'</div><div style="margin-top:22px;font-size:15px;line-height:1.8;color:#444">'.$body.'</div><div style="margin-top:28px;border-top:1px solid #eee;padding-top:18px;color:#999;font-size:11px;line-height:1.6">Este correo fue enviado automáticamente por Burger Town.<br>© '.date('Y').' Burger Town · Bogotá, Colombia</div></td></tr></table></td></tr></table></body></html>';
    }

}
