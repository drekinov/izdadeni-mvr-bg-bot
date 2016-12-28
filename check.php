<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

$httpClient = new \GuzzleHttp\Client();

$options = [];
$options['form_params'] = [];
$options['form_params']['TypeDoc'] = getenv('MVR_TYPEDOC'); // СУМПС
$options['form_params']['Number'] = getenv('MVR_EGN'); // СУМПС
$options['form_params']['%%Surrogate_TypeDoc'] = 1; // СУМПС
$options['form_params']['__Click'] = 'C2257C590038004D.a1a096ff9bbd6b84c22572a3005638f0/$Body/0.1336'; // СУМПС
$options['verify'] = false;

$response = $httpClient->post('https://izdadeni.mvr.bg/nld2/nWeb2.nsf/fVerification?OpenForm&Seq=3', $options);
$htmlResponse = (string) $response->getBody()->getContents();

\Symfony\Component\VarDumper\VarDumper::dump($htmlResponse);

$vv = [];
preg_match_all('/<TD class=\"system-message(.*?)\">(.*?)<\/TD>/s', $htmlResponse, $vv, PREG_SET_ORDER);
$systemMessage = $vv[0][2];

// <span class="system-message-error">След 29.03.2010 г. лицето с ЕГН [XXXXXXXXXX] няма издаден документ от избрания вид или същият вече е получен.</span>

if (stripos($systemMessage, 'няма издаден документ') !== true) {
    \Symfony\Component\VarDumper\VarDumper::dump($systemMessage);
} else {
    $mailer = new \PHPMailer\PHPMailer\PHPMailer();
    $mailer->CharSet = 'utf-8';
    $mailer->isSMTP();
    $mailer->isHTML(true);
    $mailer->Host = getenv('SMTP_HOST');
    $mailer->SMTPAuth = true;
    $mailer->Username = getenv('SMTP_USER');
    $mailer->Password = getenv('SMTP_PASSWORD');
    $mailer->SMTPSecure = getenv('SMTP_SECURITY');
    $mailer->Port = getenv('SMTP_PORT');
    $mailer->From = getenv('MAIL_FROM');
    $mailer->FromName = 'Izdadeni MVR BOT 2';
    $mailer->addAddress(getenv('MAIL_TO'));

    $mailer->Subject = '[BOT] izdadeni.mvr.bg';
    $mailer->Body = $systemMessage;

    \Symfony\Component\VarDumper\VarDumper::dump($systemMessage);

    if (!$mailer->send()) {
        \Symfony\Component\VarDumper\VarDumper::dump('Message could not be sent.');
        \Symfony\Component\VarDumper\VarDumper::dump('Mailer Error: ' . $mailer->ErrorInfo);
    } else {
        \Symfony\Component\VarDumper\VarDumper::dump('Mail sent.');
    }
}
