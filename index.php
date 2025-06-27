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

// Camada de Domínio (Serviços)
$cadastroService = new App\Domain\Services\CadastroService($usuarioRepository, $emailService);

// Camada de Aplicação (Controllers)
$usuarioController = new App\Application\Controllers\UsuarioController($cadastroService);


// --- 4. Definição das Rotas ---
// Cria uma instância do roteador e define as rotas da aplicação.
$router = new App\Application\Router();

// Rota para a página inicial, redirecionando para o cadastro.
$router->get('/', function() {
    header('Location: /cadastro');
    exit();
});


$router->get('/login', [$usuarioController, 'exibirFormularioLogin']);

// Rota para exibir o formulário de cadastro.
$router->get('/cadastro', [$usuarioController, 'exibirFormularioCadastro']);

// Rota para processar os dados do formulário de cadastro.
$router->post('/cadastro', [$usuarioController, 'processarCadastro']);

// Rota para a página de sucesso após o cadastro.
$router->get('/cadastro/sucesso', [$usuarioController, 'exibirCadastroSucesso']);

// Rotas de Verificação
$router->get('/verificar', [$usuarioController, 'exibirFormularioVerificacao']);
$router->post('/verificar', [$usuarioController, 'processarVerificacao']);

// --- 5. Iniciar a Aplicação ---
// O roteador irá analisar a URL da requisição e chamar o método correto do controller.
$router->dispatch();

// Envia todo o conteúdo do buffer de saída para o navegador e desliga o buffer.
ob_end_flush();