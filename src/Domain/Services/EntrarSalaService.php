<?php

namespace App\Domain\Services;

use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Exceptions\SalaNaoEncontradaException;
use App\Domain\Exceptions\SalaCheiaException;
use App\Domain\Exceptions\UtilizadorJaParticipaException;
use App\Domain\Exceptions\LimiteDeSalasAtingidoException;
use Exception;

/**
 * Classe EntrarSalaService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para um utilizador entrar numa sala existente.
 */
class EntrarSalaService
{
    private SalaRepositoryInterface $salaRepository;
    private const LIMITE_PARTICIPANTES = 5;
    private const LIMITE_SALAS_POR_UTILIZADOR = 2;

    public function __construct(SalaRepositoryInterface $salaRepository)
    {
        $this->salaRepository = $salaRepository;
    }

    /**
     * Executa o processo de um utilizador entrar numa sala.
     *
     * @param int $idUsuario O ID do utilizador que quer entrar.
     * @param string $codigoConvite O código da sala.
     * @return void
     * @throws SalaNaoEncontradaException
     * @throws SalaCheiaException
     * @throws UtilizadorJaParticipaException
     * @throws LimiteDeSalasAtingidoException
     * @throws Exception
     */
    public function executar(int $idUsuario, string $codigoConvite): void
    {
        // 1. Valida o código da sala.
        $sala = $this->salaRepository->buscarPorCodigoConvite($codigoConvite);
        if ($sala === null) {
            throw new SalaNaoEncontradaException("Nenhuma sala encontrada com este código de convite.");
        }

        // 2. Verifica se o utilizador já está na sala.
        $salasDoUsuario = $this->salaRepository->buscarPorUsuarioId($idUsuario);
        foreach ($salasDoUsuario as $salaExistente) {
            if ($salaExistente->id === $sala->id) {
                throw new UtilizadorJaParticipaException("Você já participa nesta sala.");
            }
        }

        // 3. Verifica se o utilizador atingiu o seu limite pessoal de salas.
        if (count($salasDoUsuario) >= self::LIMITE_SALAS_POR_UTILIZADOR) {
            throw new LimiteDeSalasAtingidoException("Você atingiu o limite de " . self::LIMITE_SALAS_POR_UTILIZADOR . " salas como jogador.");
        }

        // 4. Verifica se a sala está cheia.
        $totalParticipantes = $this->salaRepository->contarParticipantes($sala->id);
        if ($totalParticipantes >= self::LIMITE_PARTICIPANTES) {
            throw new SalaCheiaException("Esta sala já atingiu o limite de " . self::LIMITE_PARTICIPANTES . " participantes.");
        }

        // 5. Se todas as regras passaram, adiciona o participante.
        $sucesso = $this->salaRepository->adicionarParticipante($sala->id, $idUsuario);
        if (!$sucesso) {
            throw new Exception("Ocorreu um erro ao tentar entrar na sala.");
        }
    }
}