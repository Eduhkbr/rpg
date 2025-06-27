<?php

// public/index.php

session_start();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . './src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// --- 2. Carregamento de Configuração e Conexão com o Banco ---

$dbConfigFile = __DIR__ . './config/database.php';

if (!file_exists($dbConfigFile)) {
    die("<h1>Erro de Configuração</h1><p>Arquivo de configuração do banco de dados não encontrado. Por favor, copie <code>config/database.php.example</code> para <code>config/database.php</code> e preencha suas credenciais.</p>");
}

$dbConfig = require $dbConfigFile;

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

// --- 3. Injeção de Dependências (Montando o Quebra-Cabeça) ---
$usuarioRepository = new App\Infrastructure\Persistence\MySQLUsuarioRepository($pdo);
$cadastroService = new App\Domain\Services\CadastroService($usuarioRepository);
$usuarioController = new App\Application\Controllers\UsuarioController($cadastroService);

// --- 4. Definição das Rotas ---
$router = new App\Application\Router();
$router->get('/cadastro', [$usuarioController, 'exibirFormularioCadastro']);
$router->post('/cadastro', [$usuarioController, 'processarCadastro']);
$router->get('/cadastro/sucesso', [$usuarioController, 'exibirCadastroSucesso']);

// --- 5. Iniciar a Aplicação ---
$router->dispatch();