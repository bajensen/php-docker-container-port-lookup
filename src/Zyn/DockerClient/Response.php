<?php
namespace Zyn\DockerClient;

class Response {
    protected $jsonArray;

    public function __construct ($jsonArray) {
        $this->jsonArray = $jsonArray;
    }

    public function getContent () {
        return $this->jsonArray;
    }

    public function getPathValue ($path) {
        $loc = &$this->jsonArray;

        foreach (explode('.', $path) as $step) {
            $loc = &$loc[$step];
        }

        return $loc;
    }
}