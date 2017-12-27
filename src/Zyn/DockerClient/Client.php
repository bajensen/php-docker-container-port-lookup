<?php
namespace Zyn\DockerClient;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\DecoderPlugin;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\PluginClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Client\Socket\Client as SocketHttpClient;

class Client {
    /** @var HttpMethodsClient */
    protected $methodClient;

    public function __construct () {
        $messageFactory = new GuzzleMessageFactory();
        $lengthPlugin = new ContentLengthPlugin();
        $decodingPlugin = new DecoderPlugin();
        $errorPlugin = new ErrorPlugin();

        $socketClient = new SocketHttpClient($messageFactory, ['remote_socket' => 'unix:///var/run/docker.sock']);

        $httpClient = new PluginClient($socketClient, [
            $errorPlugin,
            $lengthPlugin,
            $decodingPlugin
        ]);

        $this->methodClient = new HttpMethodsClient($httpClient, $messageFactory);
    }

    /**
     * @param $url
     * @return Response
     * @throws \Http\Client\Exception
     */
    public function get ($url) {
        $response = $this->methodClient->get($url, ['Host' => 'localhost']);
        $body = $response->getBody();
        $contents = $body->getContents();
        return new Response(json_decode($contents, true));
    }
}