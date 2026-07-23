<?php
require_once("../lib/PHPMailer/src/PHPMailer.php");
require_once("../lib/PHPMailer/src/SMTP.php");



trait MailerTrait
{
    public function SendEmail(array $users, string $subject, string $body): array
    {
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp-wisdomit.alwaysdata.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'wisdomit@alwaysdata.net';
            $mail->Password   = '$ITwisdom0';

            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;

            $mail->setFrom('wisdomit@alwaysdata.net', 'GroupW Messenger');
            $mail->addReplyTo('wisdomit@alwaysdata.net', 'GroupW Messenger');

            // THE FIX: Force pure plain text mode
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->clearBCCs();
            foreach ($users as $user) {
                if (!empty($user['email'])) {
                    $mail->addBCC($user['email']);
                }
            }

            $mail->send();
            return ["status" => "success", "message" => "Email dispatched successfully!"];
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Mailer Error: {$mail->ErrorInfo}"];
        }
    }
}
