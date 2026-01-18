<?php
require '../vendor/autoload.php';

use SendGrid\Mail\Mail;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

    $emailObj = new Mail();
    $emailObj->setFrom("noreply@mypulse.com", "MyPulse Contact");
    $emailObj->setSubject("Contact Form: " . $subject);
    $emailObj->addTo("hoja.valentin@gmail.com", "MyPulse Company");
    $emailObj->addContent("text/plain", "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message");

    try {
        $response = $sendgrid->send($emailObj);
        if ($response->statusCode() == 202) {
            header('Location: contact.php?status=success');
            exit();
        } else {
            header('Location: contact.php?status=error&message=' . urlencode('Erreur lors de l\'envoi.'));
            exit();
        }
    } catch (Exception $e) {
        header('Location: contact.php?status=error&message=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: contact.php?status=invalid');
    exit();
}
?>
