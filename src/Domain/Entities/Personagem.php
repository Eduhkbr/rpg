<?php

namespace App\Domain\Entities;

/**
 * Classe Personagem
 * Representa a entidade Personagem no domínio da aplicação.
 * Contém as propriedades e os métodos de negócio relacionados a uma ficha de personagem.
 */
class Personagem
{
    public readonly ?int $id;
    public readonly int $idUsuario;
    public readonly int $idSistema;
    private string $nomePersonagem;
    private string $fichaJson; // A ficha completa será armazenada como uma string JSON.

    /**
     * Construtor da classe Personagem.
     *
     * @param int $idUsuario O ID do utilizador dono do personagem.
     * @param int $idSistema O ID do sistema de RPG ao qual o personagem pertence.
     * @param string $nomePersonagem O nome do personagem.
     * @param string $fichaJson A ficha completa do personagem, em formato de string JSON.
     * @param ?int $id O ID do personagem (opcional, usado ao carregar da base de dados).
     */
    public function __construct(
        int $idUsuario,
        int $idSistema,
        string $nomePersonagem,
        string $fichaJson,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->idUsuario = $idUsuario;
        $this->idSistema = $idSistema;
        $this->nomePersonagem = $nomePersonagem;
        $this->fichaJson = $fichaJson;
    }

    // --- Getters ---
    // Métodos públicos para aceder às propriedades da classe.

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getIdSistema(): int
    {
        return $this->idSistema;
    }

    public function getNomePersonagem(): string
    {
        return $this->nomePersonagem;
    }

    public function getFichaJson(): string
    {
        return $this->fichaJson;
    }

    /**
     * Obtém a ficha do personagem como um array associativo.
     *
     * @return array
     */
    public function getFichaComoArray(): array
    {
        return json_decode($this->fichaJson, true) ?? [];
    }

    // --- Métodos de Negócio ---

    /**
     * Atualiza a ficha do personagem.
     *
     * @param array $novosDadosFicha Um array com os novos dados da ficha.
     */
    public function atualizarFicha(array $novosDadosFicha): void
    {
        $this->fichaJson = json_encode($novosDadosFicha);
    }

    /**
     * Atualiza o nome do personagem.
     *
     * @param string $novoNome
     */
    public function atualizarNome(string $novoNome): void
    {
        $this->nomePersonagem = $novoNome;
    }
}