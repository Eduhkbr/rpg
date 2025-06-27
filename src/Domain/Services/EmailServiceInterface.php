<?php

namespace App\Domain\Services;

/**
 * Interface EmailServiceInterface
 *
 * Define o contrato para um serviço que envia e-mails.
 * O domínio depende desta abstração, não de uma implementação concreta.
 */
interface EmailServiceInterface
{
    /**
     * Envia um e-mail.
     *
     * @param string $destinatarioEmail O e-mail do destinatário.
     * @param string $destinatarioNome O nome do destinatário.
     * @param string $assunto O assunto do e-mail.
     * @param string $corpoHtml O conteúdo do e-mail em formato HTML.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function enviar(string $destinatarioEmail, string $destinatarioNome, string $assunto, string $corpoHtml): bool;
}