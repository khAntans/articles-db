<?php

declare(strict_types=1);

use App\Controllers\ArticleController;

require_once '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../views');
$twig = new \Twig\Environment($loader);
session_start();

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/articles', [ArticleController::class, 'index']);
    $r->addRoute('POST', '/articles', [ArticleController::class, 'store']);
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticleController::class, 'show']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticleController::class, 'update']);
    $r->addRoute('GET', '/articles/create', [ArticleController::class, 'create']);
    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticleController::class, 'edit']);

    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticleController::class, 'delete']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found

        echo '404';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo '405';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        [$controller, $method] = $handler;

        $response = (new $controller)->{$method}(...array_values($vars));

        switch (get_class($response)) {
            case 'App\ViewResponse':
                if(isset($_SESSION)) {
                    $twig->addGlobal('session', $_SESSION);
                }
                echo $twig->render($response->getViewName().'.twig', $response->getData());
                unset($_SESSION['actionStatus']);
                break;
            case 'App\RedirectResponse':
                header('Location: ' . $response->getLocation());
                break;
            default:
                echo 'no worki! sowy :(';
        }


        break;
}

