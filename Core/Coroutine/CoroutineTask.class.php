<?php
namespace Core\Coroutine;

class CoroutineTaskReturnValue{
    const RET_BREAK = 100;
    const RET_CONTINUE = 200;
    public function __construct($value, $state){
        $this->value = $value;
        $this->state = $state;
    }
}

class CoroutineTask{
    static public function retBreak($value = NULL){
        return new CoroutineTaskReturnValue($value, CoroutineTaskReturnValue::RET_BREAK);
    }
    
    static public function retContinue($value = NULL){
        return new CoroutineTaskReturnValue($value, CoroutineTaskReturnValue::RET_CONTINUE);
    }
    
    public function run($coroutine){
        self::retBreak();
    }
}