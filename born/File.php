<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2016/10/19
 * Time: 16:03
 */
class File
{
    public static function loadYaml($s_yaml_file_name) {
        if (file_exists($s_yaml_file_name)){
            $a_yaml_list = yaml_parse_file($s_yaml_file_name);//获取acl_list.yaml文件内容
            return $a_yaml_list;
        } else {
            return false;
        }
    }

    public static function saveYaml($s_yaml_file_name,$a_data) {
        $return = file_put_contents($s_yaml_file_name,yaml_emit($a_data,YAML_UTF8_ENCODING),FILE_APPEND);
        if (false == $return){
            return false;
        } else {
            return true;
        }
    }

    public static function saveFile($s_file_name,$a_data) {
        $return = file_put_contents($s_file_name,$a_data);
        if (false == $return){
            return false;
        } else {
            return true;
        }
    }

    public static function loadFile($s_file_name) {
        if (file_exists($s_file_name)){
            $a_list = file_get_contents($s_file_name);
            return $a_list;
        } else {
            return false;
        }
    }

    public static function serializeFile($s_file_name,$a_data) {
        $return = file_put_contents($s_file_name,serialize($a_data),FILE_APPEND);
        if (false == $return){
            return false;
        } else {
            return true;
        }
    }


}
