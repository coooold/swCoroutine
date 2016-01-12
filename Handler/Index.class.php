<?php
/**
 * Index.class.php
 * @author fang
 * @date 2016-01-12
 */
class Handler_Index extends \Core\Handler{
	public function run($request, $response){
        $model = new \Model\MyModel();
        $a = (yield $model->getInfo('a'));
        $a .= (yield $model->getInfo('b'));
        $a .= (yield $model->getInfo('c'));
        $a .= (yield $model->getInfo('d'));
        $response->end($a);
	}
}