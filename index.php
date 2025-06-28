<?php

/**
 * Ponto de Entrada Único (Front Controller) da Aplicação.
 *
 * Todas as requisições são direcionadas para este arquivo, que é responsável
 * por inicializar a aplicação, configurar dependências e despachar a rota.
 */

// Ativa o buffer de saída. Essencial para permitir o envio de headers (como redirects)
// a qualquer momento, mesmo que algum output já tenha sido gerado.
ob_start();

// Configurações para exibir todos os erros durante o desenvolvimento.
// Em produção, isso deve ser desativado e os erros devem ser logados em um arquivo.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia ou resume uma sessão, necessário para manter o estado do usuário (login).
session_start();

// --- 1. Autoloader (PSR-4) ---
// Registra uma função que carrega as classes automaticamente quando são necessárias.
// Isso evita a necessidade de usar `require` ou `include` para cada classe.
spl_autoload_register(function ($class) {
    // Mapeia o namespace 'App\' para a pasta 'src/'.
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';

    // Verifica se a classe usa o prefixo do nosso namespace.
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // Não é uma classe do nosso projeto, ignora.
    }

    // Converte o namespace em um caminho de arquivo.
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Se o arquivo existir, carrega-o.
    if (file_exists($file)) {
        require $file;
    }
});

// --- Inclusão do PHPMailer ---
require __DIR__ . '/libs/PHPMailer/Exception.php';
require __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require __DIR__ . '/libs/PHPMailer/SMTP.php';


// --- 2. Carregamento de Configurações e Conexão com o Banco ---
$dbConfigFile = __DIR__ . '/config/database.php';
if (!file_exists($dbConfigFile)) {
    die("<h1>Erro de Configuração</h1><p>Arquivo de configuração do banco de dados (database.php) não encontrado.</p>");
}
$dbConfig = require $dbConfigFile;

$emailConfigFile = __DIR__ . '/config/email.php';
if (!file_exists($emailConfigFile)) {
    die("<h1>Erro de Configuração</h1><p>Arquivo de configuração de e-mail (email.php) não encontrado.</p>");
}
$emailConfig = require $emailConfigFile;

try {
    // Cria a string de conexão (DSN) e a instância do PDO.
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna resultados como arrays associativos.
    ]);
} catch (PDOException $e) {
    // Interrompe a execução se a conexão com o banco falhar.
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

// --- 3. Injeção de Dependências (Container de Injeção de Dependência manual) ---
// Camada de Infraestrutura
$usuarioRepository = new App\Infrastructure\Persistence\MySQLUsuarioRepository($pdo);
$emailService = new App\Infrastructure\Notification\PHPMailerAdapter($emailConfig);
$sistemaRPGRepository = new App\Infrastructure\Persistence\MySQLSistemaRPGRepository($pdo);
$salaRepository = new App\Infrastructure\Persistence\MySQLSalaRepository($pdo);

// Camada de Domínio (Serviços)
$cadastroService = new App\Domain\Services\CadastroService($usuarioRepository, $emailService);
$verificacaoEmailService = new App\Domain\Services\VerificacaoEmailService($usuarioRepository);
$loginService = new App\Domain\Services\LoginService($usuarioRepository);
$criarSalaService = new App\Domain\Services\CriarSalaService($salaRepository, $usuarioRepository); // <-- NOVO

// Camada de Aplicação (Controllers)
$usuarioController = new App\Application\Controllers\UsuarioController($cadastroService, $verificacaoEmailService, $loginService, $salaRepository);
$salaController = new App\Application\Controllers\SalaController($criarSalaService, $sistemaRPGRepository); // <-- NOVO


// --- 4. Definição das Rotas (ATUALIZADO) ---
$router = new App\Application\Router();

// Rotas Principais e de Sessão
$router->get('/', function() { header('Location: /login'); exit(); }); // <-- Rota raiz agora vai para o login
$router->get('/login', [$usuarioController, 'exibirFormularioLogin']);
$router->post('/login', [$usuarioController, 'processarLogin']); // <-- NOVA ROTA
$router->get('/logout', [$usuarioController, 'logout']); // <-- NOVA ROTA
$router->get('/dashboard', [$usuarioController, 'exibirDashboard']); // <-- NOVA ROTA

// Rotas de Cadastro
$router->get('/cadastro', [$usuarioController, 'exibirFormularioCadastro']);
$router->post('/cadastro', [$usuarioController, 'processarCadastro']);
$router->get('/cadastro/sucesso', [$usuarioController, 'exibirCadastroSucesso']);

// Rotas de Verificação
$router->get('/verificar', [$usuarioController, 'exibirFormularioVerificacao']);
$router->post('/verificar', [$usuarioController, 'processarVerificacao']);

// Rotas de salas
$router->get('/salas/criar', [$salaController, 'exibirFormularioCriacao']);
$router->post('/salas/criar', [$salaController, 'processarCriacao']);

// --- 5. Iniciar a Aplicação ---
$router->dispatch();

ob_end_flush();