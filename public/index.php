<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

// Configuration
$config = [];
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

// Create the app
$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

// Attach Logging
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler(__DIR__. '/../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

// Attach view renderer
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__. '/../view/');

// Routes
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Hi');

    return $response->withStatus(200);;
});

$app->get('/{proto}/{name}', function (Request $request, Response $response) {
    $hostHeader = $request->getHeader('Host');
    $host = array_shift($hostHeader);

    $name = $request->getAttribute('name');
    $proto = $request->getAttribute('proto');

    $portMap = [
        'http' => 80,
        'https' => 443,
        'ssh' => 22
    ];

    $containerPort = array_key_exists($proto, $portMap) ? $portMap[$proto] : $proto;

    try {
        $docker = new \Zyn\DockerClient\Client();
        $hostPort = $docker->findPort($name, $containerPort, 'tcp');

        if ($hostPort) {
            if (in_array($proto, ['http', 'https'])) {
                $redirectUrl = $proto . '://' . $host . ':' . $hostPort . '/';
                $response->getBody()->write('Redirecting to: <a href="' . $redirectUrl . '">' . $redirectUrl . '</a>');
                return $response->withRedirect($redirectUrl, 301);
            }
            else {
                $response->getBody()->write($hostPort);
                return $response->withStatus(200);
            }
        }
        else {
            $response->getBody()->write('Failed to find port binding.');
            return $response->withStatus(404);
        }
    }
    catch (\Http\Client\Exception $e) {
        $response->getBody()->write('Exception Occurred: ' . $e->getMessage());
        return $response->withStatus(500);
    }
});

$app->run();