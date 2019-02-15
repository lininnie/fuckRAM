<?php
/**
 * Created by PhpStorm.
 * User: reus
 * Date: 2018/7/3
 * Time: 20:14
 */
namespace BORN;

class config
{

    public function loadConfig($cmd,$type){
        switch ($type){
            case 'file':return $this->file($cmd);break;
            case 'database':return $this->database($cmd);break;
            default: die('config load type error!');
        }
    }

    private function file($yaml_file_name){
        $yaml_file_name = "./yaml/".$yaml_file_name.'.yaml';
        if (file_exists($yaml_file_name)){
            if(function_exists('yaml_parse_file')){
                $yaml_list = yaml_parse_file($yaml_file_name);//获取acl_list.yaml文件内容
            }else{
               $yaml_list = spyc_load_file($yaml_file_name);//低效率读取配置文件
            }
            return $yaml_list;
        } else {
            return false;
        }
    }

    private function database($cmd){
        //do nothing
    }
}