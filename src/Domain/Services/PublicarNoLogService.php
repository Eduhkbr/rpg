<?php

namespace App\Domain\Services;

use App\Domain\Entities\LogEntry;
use App\Domain\Repositories\LogRepositoryInterface;
use Exception;

/**
 * Classe PublicarNoLogService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para adicionar uma nova entrada ao log de uma sala.
 */
class PublicarNoLogService
{
    private LogRepositoryInterface $logRepository;

    public function __construct(LogRepositoryInterface $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Executa o processo de publicação de uma nova mensagem no log.
     *
     * @param int $idSala O ID da sala onde a mensagem será publicada.
     * @param string $autorNome O nome a ser exibido como autor (ex: "Mestre", "Aragorn").
     * @param string $tipoLog O tipo de log para estilização ('mestre', 'jogador', 'sistema').
     * @param string $mensagem O conteúdo da mensagem.
     * @return void
     * @throws Exception Se a mensagem estiver vazia ou se houver uma falha ao salvar.
     */
    public function executar(int $idSala, string $autorNome, string $tipoLog, string $mensagem): void
    {
        // 1. Validação: A mensagem não pode estar vazia.
        if (empty(trim($mensagem))) {
            throw new Exception("A mensagem não pode estar vazia.");
        }

        // 2. Criação da Entidade: Instancia um novo objeto LogEntry.
        $novaEntrada = new LogEntry(
            $idSala,
            $autorNome,
            $tipoLog,
            $mensagem
        );

        // 3. Persistência: Usa o repositório para salvar a nova entrada.
        $sucesso = $this->logRepository->salvar($novaEntrada);

        if (!$sucesso) {
            throw new Exception("Não foi possível publicar a sua mensagem no log.");
        }
    }
}