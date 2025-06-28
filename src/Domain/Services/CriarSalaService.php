<?php

namespace App\Domain\Services;

use App\Domain\Entities\Sala;
use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Repositories\UsuarioRepositoryInterface;
use Exception;

/**
 * Classe CriarSalaService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para a criação de uma nova sala de jogo.
 */
class CriarSalaService
{
    private SalaRepositoryInterface $salaRepository;
    private UsuarioRepositoryInterface $usuarioRepository;

    public function __construct(
        SalaRepositoryInterface $salaRepository,
        UsuarioRepositoryInterface $usuarioRepository
    ) {
        $this->salaRepository = $salaRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Executa o processo de criação de sala.
     *
     * @param int $idMestre O ID do utilizador que está a criar a sala.
     * @param int $idSistema O ID do sistema de RPG escolhido.
     * @param string $nomeSala O nome para a nova sala.
     * @return Sala Retorna o objeto Sala recém-criado.
     * @throws Exception Se o mestre não for encontrado ou se houver uma falha ao salvar.
     */
    public function executar(int $idMestre, int $idSistema, string $nomeSala): Sala
    {
        // 1. Regra de Negócio: Verificar se o utilizador mestre existe.
        $mestre = $this->usuarioRepository->buscarPorId($idMestre);
        if ($mestre === null) {
            throw new Exception("Utilizador mestre inválido.");
        }

        // 2. Lógica de Negócio: Gerar um código de convite único.
        // (Numa aplicação real, seria necessário verificar a unicidade no banco de dados).
        $codigoConvite = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);

        // 3. Criação da Entidade: Instanciar um novo objeto Sala.
        $novaSala = new Sala(
            $idMestre,
            $idSistema,
            $nomeSala,
            $codigoConvite
        );

        // 4. Persistência: Usar o repositório para salvar a nova sala.
        $salaSalva = $this->salaRepository->salvar($novaSala);
        if ($salaSalva === null) {
            throw new Exception("Não foi possível criar a sala.");
        }

        // 5. Retorno: Devolve o objeto Sala completo.
        return $salaSalva;
    }
}