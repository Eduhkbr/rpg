<?php

namespace App\Domain\Entities;

/**
 * Classe SistemaRPG
 * Representa um sistema ou categoria de jogo (ex: Zumbi, Fantasia Medieval).
 */
class SistemaRPG
{
    public readonly ?int $id;
    public readonly string $nomeSistema;
    public readonly ?string $descricao;
    public readonly ?string $fichaTemplateJson;

    public function __construct(
        string $nomeSistema,
        ?string $descricao,
        ?string $fichaTemplateJson,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->nomeSistema = $nomeSistema;
        $this->descricao = $descricao;
        $this->fichaTemplateJson = $fichaTemplateJson;
    }
}