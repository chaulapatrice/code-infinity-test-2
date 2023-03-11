<?php 
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;


require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$twig = Twig::create('../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->map(['GET', 'POST'],'/', function(Request $request, Response $response, $args){
    
    $csv_file_submitted = false;

    if($request->getMethod() === "POST"){
        $csv_file_submitted = true;
    }
    
    $view = Twig::fromRequest($request);
    return $view->render($response, 'index.html', [
        'csv_file_submitted' => $csv_file_submitted
    ]);

})->setName('index');

$app->run();