<?php

namespace App\Domain\Services;

use App\Domain\Repositories\UsuarioRepositoryInterface;
use App\Domain\Exceptions\CodigoInvalidoException;
use Exception;

/**
 * Classe VerificacaoEmailService
 *
 * Orquestra a lógica de negócio para verificar o e-mail de um utilizador
 * através de um código de verificação.
 */
class VerificacaoEmailService
{
    private UsuarioRepositoryInterface $usuarioRepository;

    /**
     * O construtor recebe a implementação do repositório como dependência.
     *
     * @param UsuarioRepositoryInterface $usuarioRepository
     */
    public function __construct(UsuarioRepositoryInterface $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Executa o processo de verificação de e-mail.
     *
     * @param string $codigo O código de 6 dígitos submetido pelo utilizador.
     * @return void
     * @throws CodigoInvalidoException Se o código não for encontrado ou for inválido.
     * @throws Exception Se ocorrer um erro inesperado ao salvar.
     */
    public function executar(string $codigo): void
    {
        // 1. Validação de Formato (opcional, mas bom para evitar queries desnecessárias)
        if (empty($codigo) || !is_numeric($codigo)) {
            throw new CodigoInvalidoException("O formato do código é inválido.");
        }

        // 2. Regra de Negócio: Buscar o utilizador pelo código.
        $usuario = $this->usuarioRepository->buscarPorCodigoVerificacao($codigo);

        // 3. Se nenhum utilizador for encontrado, o código é inválido.
        if ($usuario === null) {
            throw new CodigoInvalidoException("Código de verificação inválido ou expirado.");
        }

        // 4. Lógica da Entidade: Marcar o e-mail como verificado.
        $usuario->marcarEmailComoVerificado();

        // 5. Persistência: Salvar o estado atualizado do utilizador no banco.
        $sucesso = $this->usuarioRepository->salvar($usuario);

        if (!$sucesso) {
            // Lança uma exceção genérica se houver uma falha na camada de persistência.
            throw new Exception("Não foi possível atualizar o status do utilizador.");
        }
    }
}