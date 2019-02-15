<?php
/**
 * Created by PhpStorm.
 * User: reus
 * Date: 2018/7/3
 * Time: 20:10
 */
require_once './vendor/autoload.php';

class init
{
    public function __construct()
    {
        reload:
        SeasLog::setBasePath('./log');
        $arm = new \ARM\bigone();
        //接口调用失败重新生成实例
        if ($arm->reload){
            \BORN\response::logging('INFO','某计算相关接口返回失败 需要重新运行bigone对象') ;
            goto reload;
        }
    }
}

new init();