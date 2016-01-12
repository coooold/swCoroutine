<?php
namespace Core\Coroutine;

class Coroutine{
    protected $coroutine;
    protected $beforeFirstYield = true;
    protected $sendValue = null;
    
    protected $gen;
    protected $genStack = array();
    
    static public function spawn($cb){
        if(!$cb instanceof \Generator)return;

        $instance = new self($cb);
        $instance->run();
        return $instance;
    }
    
    protected function __construct(\Generator $co){
        $this->coroutine = $co;
        $this->gen = $co;
    }
    
    public function run($value = null){
        $this->sendValue = $value;
        while(true){
            $retval = null;
            if($this->beforeFirstYield){
                $this->beforeFirstYield = false;
                $retval = $this->gen->current();
            }else{
                $retval = $this->gen->send($this->sendValue);
            }
            if($retval instanceof \Generator){
                $this->beforeFirstYield = true;
                array_push($this->genStack, $this->gen);
                $this->gen = $retval;
            }elseif($retval instanceof CoroutineTask){
                $ret = $retval->run($this);
                if($ret instanceof CoroutineTaskReturnValue){
                    $this->sendValue = $ret->value;
                    if($ret->state == CoroutineTaskReturnValue::RET_BREAK){
                        break;
                    }
                }
            }else{
                //如果不是以上情况，那么说明需要返回上一层generator
                $this->sendValue = $retval;
                $this->beforeFirstYield = false;
                $this->gen = array_pop($this->genStack);
                if(!$this->gen)break;
            }
        }
    }
}