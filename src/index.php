<?php

use DI\Container;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\SQLiteConnection;
use Carbon\Carbon;


require 'vendor/autoload.php';
// Create Container using PHP-DI
$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->set('pdo', function () {
    try {
        return (new SQLiteConnection())->connect();
    } catch (\PDOException $e) {
        return null;
    }
});

$twig = Twig::create('./templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->map(['GET', 'POST'], '/', function (Request $request, Response $response, $args) {

    $view = Twig::fromRequest($request);
    return $view->render($response, 'index.html', []);
})->setName('index');


# Endpoint for importing CSV files 
$app->map(['GET', 'POST'], '/import', function (Request $request, Response $response, $args) {

    $context = array(
        'code' => 0
    );

    if ($request->getMethod() === "POST") {

        $files = $request->getUploadedFiles();

        $csv_file = $files['csv_file'];
        if ($csv_file->getError() === UPLOAD_ERR_OK) {
            try {

                $csv_file->moveTo('/uploads/import.csv');
                $file = fopen('/uploads/import.csv', 'r');

                $pdo = $this->get('pdo');

                if ($pdo) {

                    $batch_size = 5000;

                    $sql = "INSERT INTO csv_import (id, first_name, last_name, initials, age, date_of_birth) VALUES";
                    $sql .= str_repeat("(?,?,?,?,?,?),", $batch_size - 1);
                    $sql .= "(?,?,?,?,?,?)";
                    $stmt = $pdo->prepare($sql);

                    $header_row = fgetcsv($file);

                    $i = 0;
                    $values = array();

                    while($row = fgetcsv($file)){
                        array_push($values, (int)$row[0], $row[1], $row[2], $row[3], (int)$row[4], $row[5]);
                        $i++;
                        if($i === $batch_size){
                            // batch complete insert into database 
                            $stmt->execute($values);
                            $values = array();
                            $i = 0;
                        }
                    }

                    

                    $context['code'] = 1;
                    $context['message'] = 'File upload successful.';

                } else {
                    $context['code'] = -1;
                    $context['message'] = 'Apologies, we are experiencing a technical problem.';
                }

                fclose($file);

            } catch (InvalidArgumentException $e) {
                $context['code'] = -1;
                $context['message'] = 'Apologies, we are experiencing a technical problem.';
            } catch (Error  $e) {
                $context['code'] = -1;
                $context['message'] = 'Apologies, we are experiencing a technical problem.';
            }
        } else {
            $context['code'] = -1;
            $context['message'] = 'File upload failed.';
        }
    }

    $view = Twig::fromRequest($request);
    return $view->render($response, 'import.html', $context);
})->setName('import');


# Endpoint for generate CSV files
$app->map(['GET', 'POST'], '/generate', function (Request $request, Response $response, $args) {

    $context = array(
        "code" => 0
    );

    if ($request->getMethod() === "POST") {

        $date_of_birth = Carbon::create(1973, 01, 01, 0, 0, 0, 'GMT');
        $payload = $request->getParsedBody();
        $number_of_records = (int)($payload['number_of_records']);

        $first_names = [
            'Kimberly', 'Autumn', 'Manuel', 'Michelle', 'Hannah',
            'Michael', 'Michael', 'Brian', 'Stephen', 'Jasmine',
            'Kimberly', 'Patricia', 'Jennifer', 'Ryan', 'David',
            'Elijah', 'Tara', 'John', 'David', 'Jeffery'
        ];

        $last_names = [
            'Mejia', 'Fuller', 'Gordon', 'Byrd', 'Carroll',
            'Moore', 'Landry', 'Rios', 'Stewart', 'Knight',
            'Chaney', 'Smith', 'Wood', 'Lee', 'Pierce',
            'Massey', 'Oconnell', 'Brooks', 'Briggs', 'Torres'
        ];

        $i = 0;

        $file_path = '/output/output.csv';
        try {
            $file = fopen($file_path, 'w');
            $line = array("Id", "Name", "Surname", "Initials", "Age", "DateOfBirth");
            fputcsv($file, $line);
            while ($i < $number_of_records) {
                for ($j = 0; $j < count($first_names); $j++) {
                    for ($k = 0; $k < count($last_names); $k++) {

                        $first_name = $first_names[$j];
                        $last_name = $last_names[$k];
                        $initials = substr($first_names[$j], 0, 1) . substr($last_names[$k], 0, 1);
                        $age = $date_of_birth->age;
                        $dob = $date_of_birth->toDateString();

                        $line = array($i + 1, $first_name, $last_name, $initials, $age, $dob);
                        # write to csv file 
                        fputcsv($file, $line);

                        $date_of_birth = $date_of_birth->addDay();
                        $i++;

                        if ($i === $number_of_records) break;
                    }
                    if ($i === $number_of_records) break;
                }
            }

            fclose($file);

            $context['code'] = 1;
            $context['message'] = "CSV generation successful.";
        } catch (Error $e) {

            # Show feedback that the operation could not be completed due to an internal server error.
            $context['code'] = -1;
            $context['message'] = "CSV generation failed.";
        }
    }

    $view = Twig::fromRequest($request);
    return $view->render($response, 'generate.html', $context);
})->setName('generate');

$app->run();
