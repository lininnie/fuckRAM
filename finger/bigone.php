<?php
namespace FINGER;
use BORN\api;
use BORN\response;
use \Firebase\JWT\JWT;

class bigone
{
    //社融一般指社会融资。社会融资，是指贷款人通过非传统银行贷款渠道筹集资金的活动。目前，除了银行贷款和政府直接投资的资金，都认为是社会融资渠道的资金。

　　//社会融资是经济实体融资的重要补充形式，它弥补了单一银行融资渠道狭窄、资金量供不应求等方面的不足，有助于提升全社会融投资水平，提高资金利用效率，拉动经济快速增长。

　　//社会融资相对于新增贷款量是更广义的货币流通量统计指标，社会融资总量除了包含金融机构新增贷款外，还进一步纳入股票、债券融资等，使货币流量统计延伸到股市和债市等渠道的融资来源，从而更加真实地反映社会经济资金的供求状况。
    
    private $KEY;
    private $SECRET;
    private $API_URL;

    public function __construct($public_key,$private_key,$base_url)
    {
        $this->KEY = $public_key;
        $this->SECRET = $private_key;
        $this->API_URL = $base_url;
    }

    public function curl($url,$data = false){
        $timestamp =  (microtime(true)*1000000000);//获取纳秒级别时间戳,这里精度到毫秒
        $payload = array(
            //type: (String) 固定为"OpenAPI"
            'type'=>'OpenAPI',
            //sub: (String) 用户的 API Key
            'sub'=>$this->KEY,
            //nonce: (Number) 一个纳秒级的unix时间戳, 19位。例如：1527665262168391000。nonce的时间应该和服务器时间差值在30s以内，超过这个时间的Token 视作过期Token。同一个 nonce 只能使用一次。
            'nonce'=>$timestamp,
//            'market_id'=>'ETH-BTC',
        );
//        print_r($this);
        $api = new api();
        $jwt = JWT::encode($payload,$this->SECRET,'HS256');
        $header = array(
            "Authorization: Bearer $jwt",
        );
        $response = $api->curl(($this->API_URL)."$url",$data,$header);
        return $response;
    }

    public function errors($response,$interface,$parameter=null,$module = ''){
        if (isset($response['errors'])||$response==false||!is_array($response)){
            response::logging('CRITICAL',$interface.' 调用异常 ',$response,$module);
            return false;
        } else {
            response::logging('INFO',$interface.' 调用成功 ',$response,$module);
            return true;
        }
    }

    //Balance of all assets
    public function accounts(){
        $response = $this->curl("viewer/accounts");
        if ($this->errors($response,'accounts')){
            return $response;
        }
    }

    // GET Get user orders in a market
    // POST Create Order
    public function orders($data=false,$arg=array()){
        $arg = http_build_query($arg);
        $response = $this->curl("viewer/orders?$arg",$data);
        if ($this->errors($response,'orders',array($data,$arg),'orders')) {
            return $response;
        }
    }

    // POST Cancle Order
    public function ordersCancel($order_id){
        $response = $this->curl("viewer/orders/$order_id/cancel",$order_id);
        if ($this->errors($response,'ordersCancel',$order_id,'orders')) {
            return $response;
        }
    }

    //POST Cancle All Orders
    public function ordersCancelAll($data=true){
        $response = $this->curl("viewer/orders/cancel_all",$data);
        if ($this->errors($response,'ordersCancelAll','orders')) {
            return $response;
        }
    }


    //Get one order
    public function ordersOne($order_id){
        $response = $this->curl("viewer/orders/$order_id");
        if ($this->errors($response,'ordersOne',$order_id)) {
            return $response;
        }
    }

    //Trades of user 交易历史记录
    public function trades($arg = array()){
        $arg = http_build_query($arg);
        $response = $this->curl("viewer/trades?$arg");
        if ($this->errors($response,'trades',$arg)) {
            return $response;
        }
    }

    //Get withdrawals of user
    public function withdrawals(){
        $response = $this->curl("viewer/withdrawals");
        if ($this->errors($response,'withdrawals')) {
            return $response;
        }
    }

    //Deposit of user
    public function deposits(){
        $response = $this->curl("viewer/deposits");
        if ($this->errors($response,'deposits')) {
            return $response;
        }
    }

    //Get Server timestamp
    public function ping(){
        $response = $this->curl("ping");
        if ($this->errors($response,'ping')) {
            return $response;
        }
    }

    //Tickers of all market
    public function tickers(){
        $response = $this->curl("tickers");
        if ($this->errors($response,'tickers')) {
            return $response;
        }
    }

    //Ticker of one market
    public function tickersOne($market_id){
        $response = $this->curl("$market_id}/ticker");
        if ($this->errors($response,'tickers_one',$market_id)) {
            return $response;
        }
    }

    //OrderBook of a market
    public function depth($market_id){
        $response = $this->curl("markets/$market_id/depth");
        if ($this->errors($response,'depth',$market_id)) {
            return $response;
        }
    }

    //Trades of a market
    //Only returns 50 latest trades
    public function tradesMarket($market_id,$last){
        $response = $this->curl("markets/$market_id/trades?last=$last");
        if ($this->errors($response,'tradesMarket',array($market_id,$last))) {
            return $response;
        }
    }

    //All Markets
    public function markets(){
        $response = $this->curl("markets");
        if ($this->errors($response,'markets')) {
            return $response;
        }
    }
}
