<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'myclass.practice@gmail.com'; // ✅ Your Gmail address
    $mail->Password = 'wkddgtuxfmivwheh';     // ✅ App password from Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('myclass.practice@gmail.com', 'Reservation System');
    $mail->addAddress('safi.hep@gmail.com', 'Test User'); // ✅ Change to your email

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Reservation System';
    $mail->Body    = 'This is a test email using PHPMailer on your system. 🎉';

    $mail->send();
    echo '✅ Email sent successfully!';
} catch (Exception $e) {
    echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
