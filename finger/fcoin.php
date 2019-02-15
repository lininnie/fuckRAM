<?php
/**
 * Created by PhpStorm.
 * User: reus
 * Date: 2018/7/3
 * Time: 20:37
 */

class fcoin
{
    //我这半年都在做什么
    //为何要逃避现实
//api.key
//
//d88808097e9f4814a247c5f09e8f933b
//Copy
//
//api.secret
//
//05b5f2576d544020adb1005f1eb59839

    private $ACCESS = 'd88808097e9f4814a247c5f09e8f933b';
    private $SECRET = '05b5f2576d544020adb1005f1eb59839';
//    private $API_URL = 'https://api.huobi.com/apiv3';
//    private $_kline = array();

    public function salt($url){
        $part1 = (base64_encode($url));
        //对得到的结果使用秘钥进行 HMAC-SHA1 签名
        //再对二进制结果进行 Base64 编码，得到：
        $signature = base64_encode(hash_hmac("sha1", $part1, $this->SECRET, true));
        return $signature;
    }

    public function __construct()
    {
        $api = new api($this->ACCESS,$this->SECRET);
//        $x_retur = $o_api->getCommURL('https://api.fcoin.com/v2/public/server-time');
//        $t1 = microtime(true);
//        $x_retur = $o_api->httpCommURL('https://api.fcoin.com/v2/public/currencies');
//        print_r($x_retur);
        $url = 'https://api.fcoin.com/v2/accounts/balance';
//        $salt = 'GET https://api.fcoin.com/v2/accounts/balance';
        $signature = $this->salt('GET '.$url);
        print_r($signature);
        $xr = $api->balance($url,$signature);
        print_r($xr);
//        $o_api->ticker();
// ... 执行代码 ...
//        $t2 = microtime(true);
//        echo '耗时'.round($t2-$t1,3).'秒<br>';
//        echo 'Now memory_get_usage: ' . memory_get_usage() . '<br />';
//        print_r($o_api);
    }
}