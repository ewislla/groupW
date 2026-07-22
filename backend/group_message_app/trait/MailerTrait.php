<?php
// Ensure these paths correctly point to where you installed PHPMailer inside your 'lib' folder
require_once("../lib/PHPMailer/src/PHPMailer.php");
require_once("../lib/PHPMailer/src/SMTP.php");


trait MailerTrait
{
    public function SendEmail(array $users, string $subject, string $htmlBody): array
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp-wisdomit.alwaysdata.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'wisdomit@alwaysdata.net';
            $mail->Password   = '$ITwisdom0';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;

            $mail->setFrom('wisdomit@alwaysdata.net', 'GroupW Messenger');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            // Load all other users into the BCC line
            foreach ($users as $user) {
                if (!empty($user['email'])) {
                    $mail->addBCC($user['email']);
                }
            }

            // Send the email exactly ONCE to the entire BCC list
            $mail->send();

            return ["status" => "success", "message" => "Bulk email sent successfully!"];
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Mailer Error: {$mail->ErrorInfo}"];
        }
    }
}
?>