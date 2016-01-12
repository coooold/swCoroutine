<?php
namespace Core;

class Model{
    public function httpGet($url){
        return new \Core\Coroutine\HttpClientCoTask($url);
    }
}