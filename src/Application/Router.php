<?php

namespace App\Application;

/**
 * Classe Router
 *
 * Um roteador flexível que lida com a execução em subdiretórios e aceita
 * tanto arrays de controller/método quanto Closures (funções anônimas) como handlers.
 */
class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct()
    {
        // Detecta automaticamente se a aplicação está rodando em um subdiretório.
        $this->basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($this->basePath === '/' || $this->basePath === '\\') {
            $this->basePath = '';
        }
    }

    /**
     * Adiciona uma rota GET ao roteador.
     * A mudança chave é usar 'callable' em vez de 'array' para aceitar mais tipos de handlers.
     *
     * @param string $path O caminho da URL.
     * @param callable $handler O manipulador da rota (array de controller ou Closure).
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Adiciona uma rota POST ao roteador.
     *
     * @param string $path O caminho da URL.
     * @param callable $handler O manipulador da rota.
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $path = '/' . trim($path, '/');
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<\1>[0-9]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            // Verifica se a URL atual corresponde ao padrão da rota.
            if (preg_match($pattern, $path, $matches)) {
                // Extrai os parâmetros da URL (ex: o 'id').
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (is_array($handler)) {
                    [$controller, $methodName] = $handler;
                    // Chama o método do controller, passando os parâmetros da URL.
                    $controller->$methodName(...$params);
                } else {
                    // Chama a Closure, passando os parâmetros.
                    $handler(...$params);
                }
                return;
            }
        }

        // Se nenhum loop encontrou uma rota correspondente.
        http_response_code(404);
        require __DIR__ . '/Views/errors/404.phtml';
    }
}