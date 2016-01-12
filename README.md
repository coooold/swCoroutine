# swCoroutine
利用swoole进行协程调度的web框架

Generator是php5.5引入的新特性，可以中断函数代码执行，返回某个值。
在需要的时候继续执行。生成器的执行边界是_函数_。其基本操作有以下几种。

```php
function makeGen(){
    yield 1;
    yield 2;
}

$gen = makeGen();
$gen->current();    //第一次执行
$gen->send(1);      //第二次执行
$gen->send(2);      //第三次执行
echo $gen->valid(); //是否是末尾
```

利用生成器的特性，可以在php中模拟协程。详细介绍可以看鸟哥的这篇文章：
[在PHP中使用协程实现多任务调度](http://www.laruence.com/2015/05/28/3038.html)
定义协程类：

```php
class Coroutine{
    static public function spawn(Generator $gen){
        $co = new self($gen);
        $co->run();
    }
}
```

该协程类接收一个生成器，创建协程，后续将由该类完成协程的调度。
由于生成器的执行边界只有函数，如果生成器中又需要使用生成器的话，则需要
将后续生成器返回出来，交给调度器去执行。
协程调度函数如下：

```php
class Coroutine{
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
```

调度器对生成器的返回值进行判断，如果返回一个异步任务CoutineTask，那么根据
任务返回值类型，选择继续执行还是中断执行，将执行权让给其他协程。

这份代码中的异步任务仅有一个Http调用：

```php
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
```

可以看到，这段代码将协程唤醒的工作放到http_client的回调函数中，
由异步io重新唤起协程。

和鸟哥文章中协程调度方式不同的是，这里没有一个统一的调度器Scheduler，
而是将调度工作交给swoole的异步回调去完成，通过这种方式解决了调度器的循环和swoole事件
循环的融合问题。

测试方式：
```php
#php run.php
//异步4次
#ab -n1000 -c200 "http://127.0.0.1:9502/"
//同步4次
#ab -n1000 -c200 "http://127.0.0.1:9502/sync"
```

