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
    $response->withStatus(200);
    $response->getBody()->write('Hi');

    return $response;
});

$app->get('/ssh/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');

    try {
        $port = findPort($name, 22, 'tcp');

        if ($port) {
            $response->withStatus(200);
            $response->getBody()->write($port);
        }
        else {
            $response->withStatus(404);
            $response->getBody()->write('Failed to find port binding.');
        }
    }
    catch (\Http\Client\Exception $e) {
        $response->withStatus(404);
        $response->getBody()->write('Container not found: ' . $e->getMessage());
    }

    return $response;
});

try {
    $app->run();
}
catch (\Exception $e) {
    die('Something went terribly wrong.');
}

/**
 * @param string $containerName
 * @param string $number
 * @param string $protocol
 * @return int|null the port number or null if one was not found
 * @throws \Http\Client\Exception
 */
function findPort ($containerName, $number, $protocol = 'tcp') {
    $url = '/containers/' . $containerName . '/json';

    $docker = new \Zyn\DockerClient\Client();
    $dockerRes = $docker->get($url);
    $bindings = $dockerRes->getPathValue('NetworkSettings.Ports.' . $number . '/' . $protocol);

    $port = null;

    foreach ($bindings as $binding) {
        $port = $binding['HostPort'];
    }

    return $port;
}