<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct for your project

function sendReservationEmail($customerName, $customerEmail, $area, $date, $time, $partySize, $isUpdate = false) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'myclass.practice@gmail.com';        // ✅ your Gmail address
        $mail->Password   = 'wkddgtuxfmivwheh';          // ✅ your App Password (not your Gmail login)
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // From and To
        $mail->setFrom('myclass.practice@gmail.com', ' Reservation System'); // ✅ change if needed
        $mail->addAddress($customerEmail, $customerName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $isUpdate ? 'Reservation Updated' : 'Reservation Confirmed';
        $mail->Body = "
            <h3>Dear $customerName,</h3>
            <p>Your reservation has been <strong>" . ($isUpdate ? "updated" : "confirmed") . "</strong>.</p>
            <ul>
                <li><strong>Area:</strong> $area</li>
                <li><strong>Date:</strong> $date</li>
                <li><strong>Time:</strong> $time</li>
                <li><strong>Party Size:</strong> $partySize</li>
            </ul>
            <p>Thank you for choosing Reservation System!</p>
        ";

        $mail->send();
        // Optional log message
        // error_log("Email sent to $customerEmail");
    } catch (Exception $e) {
        // Optional error logging
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}
