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
    $response = $this->view->render($response, "index.phtml", []);

    return $response;
});

$app->post('/set_position', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $panPos = $data['pan'];
    $tiltPos = $data['tilt'];
    $transitionTime = $data['speed'] * 500;

    try {
        $msg = sendMessage(sprintf('X%03d Y%03d T%04d C', $panPos, $tiltPos, $transitionTime));
        $response->withStatus(200);
        $response->getBody()->write(json_encode(['msg' => $msg], JSON_NUMERIC_CHECK));
    }
    catch (\Zyn\Exception\MessageException $e) {
        $response->withStatus(500);
        $response->getBody()->write("{$e->getMessage()} ({$e->getCode()})");
    }

    return $response;
});