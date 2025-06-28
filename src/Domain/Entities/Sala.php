<?php

namespace App\Domain\Entities;

/**
 * Classe Sala
 * Representa uma sala de jogo na plataforma.
 */
class Sala
{
    public readonly ?int $id;
    public readonly int $idMestre;
    public readonly int $idSistema;
    public readonly string $nomeSala;
    public readonly string $codigoConvite;
    public readonly bool $ativa;
    public readonly string $dataCriacao;

    public function __construct(
        int $idMestre,
        int $idSistema,
        string $nomeSala,
        string $codigoConvite,
        ?int $id = null,
        bool $ativa = true,
        ?string $dataCriacao = null
    ) {
        $this->id = $id;
        $this->idMestre = $idMestre;
        $this->idSistema = $idSistema;
        $this->nomeSala = $nomeSala;
        $this->codigoConvite = $codigoConvite;
        $this->ativa = $ativa;
        $this->dataCriacao = $dataCriacao ?? date('Y-m-d H:i:s');
    }
}