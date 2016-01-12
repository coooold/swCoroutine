<?php
namespace Core\Coroutine;

class HttpClientCoTask extends CoroutineTask{
    protected $url;
    protected $timeout;
    
    public function __construct($url, $timeout = 1){
        $this->url = $url;
    }
    
    public function run($coroutine){
        $urlInfo = parse_url ( $this->url );
		$timeout = $this->timeout;
		if(!isset($urlInfo ['port']))$urlInfo ['port'] = 80;
        
        $cli = new \swoole_http_client($urlInfo['host'], $urlInfo ['port']);
        $cli->set([
                'timeout' => $timeout,
                'keep_alive' => 0,
        ]);
        $cli->on('close', function($cli){});
        $cli->on('error', function($cli) use ($coroutine){
            $coroutine->run(false);
        });
        $cli->execute($this->url, function($cli)use($coroutine){
             $coroutine->run($cli->body);
        });
        return self::retBreak();
    }
}