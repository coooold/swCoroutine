<?php
/**
 * Sync.class.php
 * @author fang
 * @date 2016-01-12
 */
class Handler_Sync extends \Core\Handler{
	public function run($request, $response){
        $a = file_get_contents("http://127.0.0.1:9501/info?usr=a");
        $a .= file_get_contents("http://127.0.0.1:9501/info?usr=b");
        $a .= file_get_contents("http://127.0.0.1:9501/info?usr=c");
        $a .= file_get_contents("http://127.0.0.1:9501/info?usr=d");
        $response->end($a);
	}
}