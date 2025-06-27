<?php

namespace App\Infrastructure\Notification;

use App\Domain\Services\EmailServiceInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHPMailerAdapter implements EmailServiceInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function enviar(string $destinatarioEmail, string $destinatarioNome, string $assunto, string $corpoHtml): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Configurações do Servidor
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = $this->config['smtp_auth'];
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = $this->config['smtp_secure'];
            $mail->Port       = $this->config['port'];
            $mail->CharSet    = 'UTF-8';

            // Remetente e Destinatário
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($destinatarioEmail, $destinatarioNome);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpoHtml;
            $mail->AltBody = strip_tags($corpoHtml);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}