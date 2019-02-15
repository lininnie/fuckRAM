<?php
/**
 * Created by PhpStorm.
 * User: reus
 * Date: 2018/7/17
 * Time: 2:18
 */

namespace BORN;


class response
{
//    public function jsonres($array){
//        echo json_encode($array);
//    }
//常量列表
//SeasLog 共将日志分成8个级别
//
//SEASLOG_DEBUG
//"DEBUG" - debug信息、细粒度信息事件
//SEASLOG_INFO
//"INFO" - 重要事件、强调应用程序的运行过程
//SEASLOG_NOTICE
//"NOTICE" - 一般重要性事件、执行过程中较INFO级别更为重要的信息
//SEASLOG_WARNING
//"WARNING" - 出现了非错误性的异常信息、潜在异常信息、需要关注并且需要修复
//SEASLOG_ERROR
//"ERROR" - 运行时出现的错误、不必要立即进行修复、不影响整个逻辑的运行、需要记录并做检测
//SEASLOG_CRITICAL
//"CRITICAL" - 紧急情况、需要立刻进行修复、程序组件不可用
//SEASLOG_ALERT
//"ALERT" - 必须立即采取行动的紧急事件、需要立即通知相关人员紧急修复
//SEASLOG_EMERGENCY
//"EMERGENCY" - 系统不可用

    public static function logging($level, $message,$content = array(), $module = ''){
        $content = array('{msg}'=>json_encode($content));
        \SeasLog::log($level,$message.'{msg}',$content,$module);
    }
}