<?php
/**
 * Created by PhpStorm.
 * User: reus
 * Date: 2018/7/3
 * Time: 20:40
 */
namespace BORN;
class api
{
    private $ACCESS_KEY;
    private $SECRET_KEY;
    private $API_URL;
    private $_kline = array();

    /**
     ********************************* 交易api
     */

    /**
     * 获取个人资产信息
     * @return mixed
     */
    public function getAccountInfo(){
        $tParams = $extra = array();
        $tParams['method'] = 'get_account_info';
        // 不参与签名样例
        // $extra['test'] = 'test';
        $tResult = self::send2api($tParams, $extra);
        return $tResult;
    }

    /**
     *获取所有正在进行的委托
     * @return mixed
     */
    public function getOrders($coin_type){
        $t_params = $extra = array();
        $t_params['method'] = 'get_orders';
        $t_params['coin_type'] = $coin_type;//1 比特币 2 莱特币
        $t_result = self::send2api($t_params, $extra);
        return $t_result;
    }

    /**
     * 获取委托详情
     * @param $coin_type
     * @return mixed
     */
    public function orderInfo($coin_type,$order_id){
        $t_params = $extra = array();
        $t_params['method'] = 'order_info';
        $t_params['coin_type'] = $coin_type;//1 比特币 2 莱特币
        $t_params['id'] = $order_id;
        $t_result = self::send2api($t_params, $extra);
        return $t_result;
    }

    /**
     *买入
     * @param $coin_type 1 比特币 2 莱特币
     * @param $price 买入价格
     * @param $amount 买入数量
     * @return mixed
     */
    public function buy($coin_type,$price,$amount){
        $t_params = $extra = array();
        $t_params['method'] = 'buy';
        $t_params['coin_type'] = $coin_type;
        $t_params['price'] = $price;
        $t_params['amount'] = $amount;
        $t_result = self::send2api($t_params, $extra);
        return $t_result;
    }

    /**
     * @param $coin_type 1 比特币 2 莱特币
     * @param $price 卖出价格
     * @param $amount 卖出数量
     * @return mixed
     */
    public function sell($coin_type,$price,$amount){
        $t_params = $extra = array();
        $t_params['method'] = 'sell';
        $t_params['coin_type'] = $coin_type;//1 比特币 2 莱特币
        $t_params['price'] = $price;//价格
        $t_params['amount'] = $amount;//数量
//        $t_params['trade_password'] = $trade_password;//资金密码
//        $t_params['trade_id'] = $trade_id;//自定义订单号（15位（包括）数字）
        $t_result = self::send2api($t_params, $extra);
        return $t_result;
    }

    //买入(市价单)
    public function buyMarket(){

    }

    //卖出(市价单)
    public function sellMarket(){

    }

    //取消委托单
    public function cancelOrder(){

    }

    //修改订单
    public function modifyOrder(){

    }

    //查询个人最新10条成交订单
    public function getNewDealOrders(){

    }

    //根据trade_id查询oder_id get_order_id_by_trade_id
    public function oderIdgetOrderIdByTradeId(){

    }

    //提币BTC/LTC
    public function withdrawCoin(){

    }

    //取消提币BTC/LTC
    public function cancelWithdrawCoin(){

    }

    //查询提币BTC/LTC
    public function getWithdrawCoinResult(){

    }


    /**
     * ***************行情api
     */

    /**实时行情数据接口
     * @param $coin_type
     * @return mixed
     */
    public function ticker($coin_type) {

        $url = 'https://api.huobi.com/staticmarket/ticker_'.$coin_type.'_json.js';
        $t_result = self::httpRequest($url,false);
        return $t_result;
    }

    //你是程序员 你有自己的优势 为何不去用
    public function curl($url,$data=false,$header = array("Content-type: application/x-www-form-urlencoded")){
        $x_result = self::httpRequest($url,$data,$header);
        return $x_result;
    }

//    public function curlData($url,$pData,$header = array("Content-type: application/x-www-form-urlencoded")){
//        print_r($url);
//        $x_result = self::httpRequest($url,$pData,$header);
//        return $x_result;
//    }

    public function balance($url,$signature){
        return $x_result = self::httpRequest($url,$signature);
    }

    private function s(){

    }

    /**深度数据接口
     * @param $coin_type
     * @param $custom 指定深度数据条数（1-150条）
     * @return mixed
     */
    public function depth($coin_type,$custom) {

//        if ($custom) {
        $url = 'https://api.huobi.com/staticmarket/depth_'.$coin_type.'_'.$custom.'.js';
//        } else {
//            $url = 'http://api.huobi.com/staticmarket/depth_'.$coin_type.'_json.js';
//        }

        $t_result = self::httpRequest($url,false);
        return $t_result;
    }

    /**
     * @param $coin_type
     * @param $period
    001	1分钟线
    005	5分钟
    015	15分钟
    030	30分钟
    060	60分钟
    100	日线
    200	周线
    300	月线
    400	年线
     * @return mixed
     */
    public function kline($coin_type,$period,$length) {

        $url = 'https://api.huobi.com/staticmarket/'.$coin_type.'_kline_'.$period.'_json.js?length='.$length;
        if (isset($this->_kline[$coin_type][$period][$length])) {
            return  $this->_kline[$coin_type][$period][$length];
        } else {
            $t_result = self::httpRequest($url,false);
            $this->_kline[$coin_type][$period][$length] = $t_result;
            return $t_result;
        }
    }

    /**买卖盘实时成交数据
     * @param $coin_type
     * @return mixed
     */
    public function detail($coin_type) {

        $url = 'https://api.huobi.com/staticmarket/detail_'.$coin_type.'_json.js';
        $t_result = self::httpRequest($url,false);
        return $t_result;
    }

    /**********************/


    public function __construct($access_key=null,$secret_key=null)
    {
        $this->ACCESS_KEY = $access_key;
        $this->SECRET_KEY = $secret_key;
    }

    private function httpRequest($pUrl, $pData,$header){
        $tCh = curl_init();
        if($pData){
            is_array($pData) && $pData = http_build_query($pData);
            curl_setopt($tCh, CURLOPT_POST, true);
            curl_setopt($tCh, CURLOPT_POSTFIELDS, $pData);
        }
        curl_setopt($tCh, CURLOPT_HTTPHEADER, $header);
        curl_setopt($tCh, CURLOPT_URL, $pUrl);
        curl_setopt($tCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tCh, CURLOPT_SSL_VERIFYPEER, false);
        $tResult = curl_exec($tCh);
        curl_close($tCh);
        $tmp = json_decode($tResult, 1);
        if($tmp) {
            $tResult = $tmp;
        }
        return $tResult;
    }

    public function createSign($pParams = array()){
        $pParams['secret_key'] = $this->SECRET_KEY;
        ksort($pParams);
        $tPreSign = http_build_query($pParams);
        $tSign = md5($tPreSign);
        return strtolower($tSign);
    }

    public function send2api($pParams, $extra = array()) {
        $pParams['access_key'] = $this->ACCESS_KEY;
        $pParams['created'] = time();
        $pParams['sign'] = self::createSign($pParams);
        if($extra) {
            $pParams = array_merge($pParams, $extra);
        }
        $tResult = self::httpRequest($this->API_URL, $pParams);
        return $tResult;
    }



//try {
//var_dump(getAccountInfo());
//} catch (Exception $e) {
//    var_dump($e);
//}


}