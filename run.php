<?php
ini_set('display_errors',1);
error_reporting(0);
ini_set('memory_limit','1024M');

include __dir__.'/Core/autoload.php';

class App extends Core\Controller{
    protected $handlerMap = array(
        '/' => 'Handler_Index',
        '/sync' => 'Handler_Sync',
        '/info' => 'Handler_Info',
    );

	protected $controller = null;
	
	public function start($host, $port){
		$controller = $this->controller;
		$http = new \swoole_http_server($host, $port, SWOOLE_BASE);
		$http->on('request', array($this, 'onRequest'));
		$http->set([
			'worker_num' => 2,
		]);
		$http->start();
	}
}

$app = new App();
$app->start('', 9501);