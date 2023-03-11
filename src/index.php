<?php 
use DI\Container;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\SQLiteConnection;

require 'vendor/autoload.php';
// Create Container using PHP-DI
$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->set('pdo', function(){
    try{
        return (new SQLiteConnection())->connect();
    } catch (\PDOException $e){
        return null;
    }
});

$twig = Twig::create('./templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->map(['GET', 'POST'],'/', function(Request $request, Response $response, $args){
    
    $view = Twig::fromRequest($request);
    return $view->render($response, 'index.html', []);

})->setName('index');

$app->map(['GET', 'POST'],'/import', function(Request $request, Response $response, $args){
    
    $view = Twig::fromRequest($request);
    return $view->render($response, 'import.html', []);

})->setName('import');

$app->map(['GET', 'POST'],'/generate', function(Request $request, Response $response, $args){
    
    $view = Twig::fromRequest($request);
    return $view->render($response, 'generate.html', []);

})->setName('export');

$app->run();