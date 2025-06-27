<?php

namespace App\Application;

/**
 * Classe Router
 *
 * Um roteador simples para mapear URLs para métodos de controllers.
 */
class Router
{
    private array $routes = [];

    /**
     * Adiciona uma rota GET ao roteador.
     *
     * @param string $path O caminho da URL (ex: '/usuarios/cadastro').
     * @param array $handler Um array com o objeto do controller e o nome do método.
     */
    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Adiciona uma rota POST ao roteador.
     *
     * @param string $path O caminho da URL.
     * @param array $handler Um array com o objeto do controller e o nome do método.
     */
    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Método privado para adicionar a rota à lista interna.
     */
    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    /**
     * Despacha a requisição, encontrando e executando o handler correspondente.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler) {
            [$controller, $methodName] = $handler;
            // Chama o método do controller.
            $controller->$methodName();
        } else {
            // Rota não encontrada.
            http_response_code(404);
            echo "<h1>404 - Página Não Encontrada</h1>";
        }
    }
}