<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'internationalstatecollegeph@gmail.com';
    $mail->Password = 'bxsx evfu okmv myjr'; // Use your actual app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Add SMTP options for development environment
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $mail->setFrom('internationalstatecollegeph@gmail.com', 'ISCP Admin');
    $mail->addAddress('jituriaga@addu.edu.ph', 'Jensi Vea');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Greetings from ISCP!';
    $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #0066cc;'>Welcome to ISCP!</h2>
                    <p>Dear Jensi Vea,</p>
                    <p>Your enrollment has been approved. Here are your login credentials:</p>
                    
                    <div style='background-color: #ffffff; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0066cc;'>
                        <p><strong>Student ID:</strong> 123456 </p>
                        <p><strong>Password:</strong> muengengka </p>
                    </div>
                    
                    <p style='color: #dc3545;'><strong>Important:</strong> For security reasons, please change your password after your first login.</p>
                    
                    <p>Best regards,<br>ISCP Administration</p>
                </div>
            </body>
            </html>
        ";

    if ($mail->send()) {
        echo 'Email sent successfully!';
    } else {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}