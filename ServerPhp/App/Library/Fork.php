<?php

namespace Wechat\App\Library;

/*
* 开额外进程进行耗时操作。
* via: https://www.jianshu.com/p/3f8a43b22dd8
*/
class Fork{

    static $instance;

    /**
     * @return static
     */
    public static function getInstance(){
        if (null == Fork::$instance)
            Fork::$instance = new Fork();
        return Fork::$instance;
    }

    public function run($rb){
        if (! function_exists('pcntl_fork')) die('PCNTL functions not available on this PHP installation');

        $pid = pcntl_fork();
        if($pid > 0){
            pcntl_wait($status);
        }elseif($pid == 0){
            $cid = pcntl_fork();
            if($cid > 0){
                exit();
            }elseif($cid == 0){
                $rb(); // 实际在孙子进程运行逻辑代码，避免僵尸进程
            }else{
                exit();
            }
        }else
        {
           exit();
        }

    }
}

