<?php
include_once("../lib/PHPMailer/src/PHPMailer.php");
include_once("../lib/PHPMailer/src/SMTP.php");

trait Mailer
{
    public function sendOTPEmail(string $recipientEmail, string $recipientName, string $otpCode, string $otp_expire)
    {
        // ... (Keep your existing PHPMailer usage)
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp-wisdomit.alwaysdata.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'wisdomit@alwaysdata.net';
            $mail->Password   = '$ITwisdom0';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('wisdomit@alwaysdata.net', 'Wisdom Attendance System');
            $mail->addAddress($recipientEmail, $recipientName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Login Authentication Code';

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; background-color: #FAFAFA; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(6, 42, 57, 0.1);'>
                    <h2 style='color: #062A39; text-align: center; text-transform: uppercase; margin-bottom: 30px;'>Attendance System</h2>
                    <p style='color: #062A39; font-size: 16px;'>Hello $recipientName,</p>
                    <p style='color: #062A39; font-size: 16px;'>Your one-time authentication code is:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='font-size: 36px; font-weight: bold; color: #00B4D5; letter-spacing: 8px; border: 2px dashed #00B4D5; padding: 15px 20px; border-radius: 8px; display: inline-block;'>$otpCode</span>
                    </div>
                    <p style='color: #062A39; font-size: 14px;'>This code will expire at <strong>$otp_expire</strong>. Do not share this code with anyone.</p>
                </div>
            ";

            // Fallback for non-HTML mail clients
            $mail->AltBody = "Your authentication code is: $otpCode. It will expire at $otp_expire. Do not share this with anyone.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // If the email fails to send, return false so the model knows
            return false;
        }
    }
}
