<?php

namespace App\Domain\Entities;

/**
 * Classe LogEntry
 * Representa uma única entrada no log de uma sala de jogo.
 * É uma estrutura de dados imutável após a sua criação.
 */
class LogEntry
{
    public readonly ?int $id;
    public readonly int $idSala;
    public readonly string $autorNome;
    public readonly string $tipoLog; // 'mestre', 'jogador', ou 'sistema'
    public readonly string $mensagem;
    public readonly string $timestamp;

    /**
     * Construtor da classe LogEntry.
     *
     * @param int $idSala O ID da sala a que esta entrada pertence.
     * @param string $autorNome O nome do autor da mensagem (ex: "Mestre", "Aragorn").
     * @param string $tipoLog O tipo de entrada para fins de estilização.
     * @param string $mensagem O conteúdo da narração ou ação.
     * @param ?int $id O ID da entrada (opcional, usado ao carregar da base de dados).
     * @param ?string $timestamp A data e hora da entrada (opcional).
     */
    public function __construct(
        int $idSala,
        string $autorNome,
        string $tipoLog,
        string $mensagem,
        ?int $id = null,
        ?string $timestamp = null
    ) {
        $this->id = $id;
        $this->idSala = $idSala;
        $this->autorNome = $autorNome;
        $this->tipoLog = $tipoLog;
        $this->mensagem = $mensagem;
        $this->timestamp = $timestamp ?? date('Y-m-d H:i:s');
    }
}