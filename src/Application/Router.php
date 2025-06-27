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
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        if (strpos($uri, $this->basePath) === 0) {
            $path = substr($uri, strlen($this->basePath));
        } else {
            $path = $uri;
        }

        $path = '/' . trim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler) {
            // A mudança chave é verificar o tipo de handler antes de chamar.
            if (is_array($handler)) {
                // Formato: [$controller, 'methodName']
                [$controller, $methodName] = $handler;
                $controller->$methodName();
            } else {
                // É uma Closure, então a chamamos diretamente.
                $handler();
            }
        } else {
            http_response_code(404);
            echo "<h1>404 - Página Não Encontrada</h1>";
            echo "<p>O roteador não encontrou uma rota para o método <strong>{$method}</strong> e caminho <strong>'{$path}'</strong>.</p>";
        }
    }
}