<?php
namespace Model;

class MyModel extends \Core\Model{
    public function getInfo($usr){
        $body = (yield $this->httpGet("http://127.0.0.1:9501/info?usr={$usr}"));
        $body = serialize($body);
        yield $body;
    }
}