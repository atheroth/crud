<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use FastRoute\RouteCollector;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Инициализация Twig
$loader = new FilesystemLoader(__DIR__ . '/views');
$twig = new Environment($loader);

// Определение текущего метода и URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Удаляем query параметры
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Инициализация маршрутов
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    // Роуты для интерфейса
    $r->addRoute('GET', '/', 'HomeController@index');
    $r->addRoute('GET', '/user/{id:\d+}', 'UserController@show');
    $r->addRoute('GET', '/user/add', 'UserController@showAddForm');
    $r->addRoute('POST', '/user/create', 'UserController@create');
    $r->addRoute('GET', '/user/edit/{id:\d+}', 'UserController@showEditForm');
    $r->addRoute('POST', '/user/{id:\d+}', 'UserController@update');
    $r->addRoute('POST', '/user/delete/{id:\d+}', 'UserController@delete');

    // Роуты для API 
    $r->addRoute('POST', '/api/create', 'ApiUserController@create');   // Создание пользователя
    $r->addRoute('GET', '/api/get', 'ApiUserController@getAll');       // Получение всех пользователей
    $r->addRoute('GET', '/api/get/{id:\d+}', 'ApiUserController@get'); // Получение пользователя по ID
    $r->addRoute('PATCH', '/api/update/{id:\d+}', 'ApiUserController@update'); // Обновление пользователя
    $r->addRoute('DELETE', '/api/delete/{id:\d+}', 'ApiUserController@delete'); // Удаление пользователя по ID $r->addRoute('DELETE', '/api/delete/{id:\d+}', 'ApiUserController@delete'); // Удаление пользователя по ID
});

// Обработка маршрутов
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "Route not found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "Method not allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        list($controller, $method) = explode('@', $handler);
        $controller = "App\\Controllers\\$controller";
        if (class_exists($controller) && method_exists($controller, $method)) {
            $controllerInstance = new $controller();
            call_user_func_array([$controllerInstance, $method], $vars);
        } else {
            http_response_code(500);
            echo "Internal server error";
        }
        break;
}
