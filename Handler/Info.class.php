<?php
/**
 * Info.class.php
 * @author fang
 * @date 2016-01-12
 */
class Handler_Info extends \Core\Handler{
	public function run($request, $response){
        $response->end("resp: ".$request->get['usr']);
	}
}