<?php

declare(strict_types=1);

use App\Controllers\ArticleController;
use App\Repositories\ArticleRepository;
use App\Repositories\MysqlArticleRepository;
use DI\ContainerBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use function DI\create;

require_once '../vendor/autoload.php';

$loader = new FilesystemLoader('../views');
$twig = new Environment($loader);
session_start();

$containerBuilder = new ContainerBuilder;

$containerBuilder->addDefinitions([
    ArticleRepository::class => create(MysqlArticleRepository::class),
]);

$container = $containerBuilder->build();

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/articles', [ArticleController::class, 'index']);
    $r->addRoute('POST', '/articles', [ArticleController::class, 'store']);
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticleController::class, 'show']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticleController::class, 'update']);
    $r->addRoute('GET', '/articles/create', [ArticleController::class, 'create']);
    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticleController::class, 'edit']);
    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticleController::class, 'delete']);
});


$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:

        echo '404 Not Found';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];

        echo '405 Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller = $routeInfo[1];
        $parameters = $routeInfo[2];

        $response = $container->call($controller, $parameters);

        switch (get_class($response)) {
            case 'App\ViewResponse':
                if (isset($_SESSION)) {
                    $twig->addGlobal('session', $_SESSION);
                }
                echo $twig->render($response->getViewName() . '.twig', $response->getData());
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

