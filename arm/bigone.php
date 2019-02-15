<?php
/**
 * Created by PhpStorm.
 * User: reus
 * Date: 2018/7/16
 * Time: 15:33
 */
namespace ARM;

use BORN\response;

class bigone
{
//只要一个接口调用出错立即返回重新执行
    public $reload = false;
//是否真实下单
    private $fake=true;
    private $cost=0;
    private $parameter;
    private $api;
    private $prices;
    private $btc;
    private $usdt;
    private $trans_btc;
    private $trans_usdt;
    private $trans_type;
    private $vol;
    private $trans_market_return;
    private $bidPrice;
    private $askPrice;
    private $bid1;
    private $ask1;

    public function __construct()
    {
        $confing = new \BORN\config();
        $parameter =  $confing->loadConfig('bigone','file');
        if($parameter){
            $this->parameter = $parameter;
            $this->parametercheck($parameter);
            if (false===$this->newChives($parameter)){
                $this->reload = true;
                return;
            }
        } else{
            response::logging('EMERGENCY','bigone配置文件读取失败',$parameter);
        }
    }

    public function newChives($parameter){
//        print_r($parameter);
        $trans_type = $parameter['trans_type'];
        $this->match();
        $api = new \FINGER\bigone($parameter['secret_key']['public_key'],$parameter['secret_key']['private_key'],$parameter['base_url']);
        $this->api = $api;
        $this->trans_type = $trans_type;
//        $trading = false;
        $this->updateTrades();//init vol
//        print_r($vol);
        //原代码是生成一组相同元素*15的数组
        $prices = end($this->trans_market_return['data']['edges'])['node']['price'];//最新成交价
        $tmp = array();
        $prices = array_pad($tmp,15,$prices);
        $list = $this->updateOrderBook($prices);

        // 更新仓位（多线程）
        $p = 0.5;//default 0.5

        $ts1 = 0;
        $ts0 = 0;
        $this->mainLoop($ts1,$ts0,$p);

//        $this->cancelOrderAuto();

    }

    public function mainLoop($ts1,$ts0,$p){
        for ($numTick=0;;$numTick++){
            $t =  (int)(microtime(true)*1000);//毫秒
            while ($t-$ts0<$this->parameter['interval']){//配置文件间隔
                usleep(5000);
            }
//            $trading = false;
            $ts1 = $ts0;
            $ts0 = (int)(microtime(true)*1000);//毫秒
            //仓位平衡策略
            $p = $this->return50($p,$this->trans_market_return,$this->prices);
//            $p+=$p;
            print_r("\n");
            print_r($p."参数");
            print_r("\n");
            $vol = $this->updateTrades();
            $this->updateOrderBook();
            $prices = $this->prices;

            response::logging('NOTICE','原始数据',array(
                'price'=>end($prices),
                'p'=>$p,
                'btc'=>$this->btc,
                'usdt'=>$this->usdt,
                'vol'=>$vol,
            ),'orders');

            $burstPrice = end($prices)*$this->parameter['pct'];
            $bull = false;
            $bear = false;
            $tradeAmount = 0;
            // 趋势策略，价格出现方向上的突破时开始交易
            $slice1 = (array_slice($prices,-6,5));
            $slice2 = (array_slice($prices,-6,4));
            $last2 = array_slice($prices,-2,1);
            $last2 = $last2[0];
            if ($numTick>2&&(
                    end($prices)-max($slice1)>+$burstPrice||
                    end($prices)-max($slice2)>+$burstPrice&&end($prices)>$last2
                )){
                echo 'bull';
                response::logging('NOTICE','开始买入',array(),'orders');
                $bull = true;
                $tradeAmount = $this->usdt/$this->bidPrice*0.99;
            }
            if ($numTick>2&&(
                    end($prices)-min($slice1)<-$burstPrice||
                    end($prices)-min($slice2)<-$burstPrice&&end($prices)<$last2
                )){
                echo 'bear';
                response::logging('NOTICE','开始卖出',array(),'orders');
                $bear = true;
                $tradeAmount = $this->btc;
            }
            // 下单力度计算
            //  1. 小成交量的趋势成功率比较低，减小力度
            //  2. 过度频繁交易有害，减小力度
            //  3. 短时价格波动过大，减小力度
            //  4. 盘口价差过大，减少力度
//            print_r($tradeAmount);
//            print_r("\n");
//            print_r($vol.'vol'."\n");
//            print_r("\n");
//            print_r($this->parameter['vol']);
//            print_r("\n");
            print_r($tradeAmount.'加力度之前的下单量'."\n");
            print_r("\n");

            if ($vol<$this->parameter['vol']) $tradeAmount *= $vol/$this->parameter['vol'];
            if ($numTick<5) $tradeAmount *= 0.80;
            if ($numTick<10) $tradeAmount *= 0.80;
            if ($bull&&end($prices)<max($prices)) $tradeAmount *= 0.90;
            if ($bear&&end($prices)>min($prices)) $tradeAmount *= 0.90;
            if (abs(end($prices)-$last2)>$burstPrice*2) $tradeAmount*=0.90;
            if (abs(end($prices)-$last2)>$burstPrice*3) $tradeAmount*=0.90;
            if (abs(end($prices)-$last2)>$burstPrice*4) $tradeAmount*=0.90;
            if ($this->ask1-$this->bid1>$burstPrice*2) $tradeAmount*=0.90;
            if ($this->ask1-$this->bid1>$burstPrice*3) $tradeAmount*=0.90;
            if ($this->ask1-$this->bid1>$burstPrice*4) $tradeAmount*=0.90;

            print_r("\n");
            print_r($tradeAmount.'外环加力度下单量'."\n");
            print_r("\n");

//            print_r($bidPrice);
//            print_r("\n");
//            print_r($askPrice);
//            print_r("\n");
            if ($tradeAmount>=0.1){//下单量小于0.1就不操作了
//                if (true){//下单量小于0.1就不操作了//fake
                $tradePrice = $bull?$this->bidPrice:$this->askPrice;
                $trading = true;
                while ($tradeAmount>=0.1){//提单
                    $orderInfo = $bull?$this->orders($this->bidPrice,$tradeAmount,'BID'):$this->orders($this->askPrice,$tradeAmount,'ASK');
                    $orderId = $orderInfo['data']['id'];
                    //等待200ms后取消挂单,(也有可能已经成交)
                    usleep(200000);
                    $this->api->ordersCancel($orderId);
//                    $this->api->ordersCancel(109006100);//fake
//            print_r($tradeAmount);
//            print_r("\n");
                    //获取订单状态(上面已取消订单)
//            $order = null;
//                        var_dump($orderId);
//                        exit();
                    $order = $this->api->ordersOne($orderId);
                    if (!$order){
                        exit('cao');
                    }
//            if
//            print_r($order);
//            exit();
                    if ('0E-16'==($order['data']['filled_amount'])){//什么傻逼玩意 没成交是个0E-16
                        $order['data']['filled_amount'] = 0;//赋值为0
                    }
                    response::logging('NOTICE','已成交订单',array(
                        'tradePrice'=>$bull?$this->bidPrice:$this->askPrice,
                        'tradeAmount'=>$tradeAmount,
                        'dealAmount'=>$order['data']['filled_amount'],
                    ),'orders');
                    print_r($tradeAmount.'内环下单量'."\n");
                    $tradeAmount -= $order['data']['filled_amount'];
                    $tradeAmount -= 0.01;
                    $tradeAmount *= 0.98;// 每轮循环都少量削减力度
                    print_r($tradeAmount.'内环加力度下单量'."\n");
//                $pending_order = ($this->api->orders(false,array("market_id"=>"ETH-USDT",)));
//                print_r($pending_order);
//                print_r($order);
                    if ($order['data']['filled_amount']==0){//0成交
                        $this->updateOrderBook();//更新盘口，更新后的价格高于提单价格也需要削减力度

                        while ($bull&&$this->bidPrice-$tradePrice>+0.1){
                            $tradeAmount*=0.99;
                            $tradePrice+=0.1;
                            print_r($tradeAmount.'内环buff加力度下单量'."\n");
                        }
                        while ($bear&&$this->askPrice-$tradePrice<-0.1){
                            $tradeAmount*=0.99;
                            $tradePrice-=0.1;
                            print_r($tradeAmount.'内环bear加力度下单量'."\n");
                        }
                    }
                }
                $numTick=0;
            }
        }
    }

    //BTC USDT 50%仓位平衡策略
    public function return50($p,$trans_market_return,$prices){
        // 这里有一个仓位平衡的辅助策略
        //  仓位平衡策略是在仓位偏离50%时，通过不断提交小单来使仓位回归50%的策略，
        //  这个辅助策略可以有效减少趋势策略中趋势反转+大滑点带来的大幅回撤
        $last_trade_price = (array_slice($trans_market_return['data']['edges'],-1,1));//最新交易价格
        $last_trade_price = ($last_trade_price[0]['node']['price']);
        $t =  (int)(microtime(true)*1000);//毫秒
        $price_step = $this->parameter['price_step'];
        $coin_amount = $this->parameter['coin_amount'];
//        print_r($this->orders(10,1,'BID'));
//        $return_orders = $this->api->ordersCancel(105256639);
//        print_r($return_orders);
        if ($p<0.48){//btc仓位过低
//            $usdt -= 30.0;
            //USDT价格，BTC数量，方向
            $orders = $this->batchOrders(
                array(
                    array($last_trade_price,$coin_amount,'BID'),//buy
                    array($last_trade_price+$price_step,$coin_amount,'BID'),
                    array($last_trade_price+$price_step*2,$coin_amount,'BID')
                )
            );
        } elseif ($p>0.52){//btc仓位过高
//            $orders = true;
//            $btc -=0.0030;
            $orders = $this->batchOrders(
                array(
                    array($last_trade_price,$coin_amount,'ASK'),//sell
                    array($last_trade_price-$price_step,$coin_amount,'ASK'),
                    array($last_trade_price-$price_step*2,$coin_amount,'ASK')
                )
            );
        }else{
            $orders = null;
        }

        $userInfo = $this->api->accounts();
        if (!$userInfo){
            exit('cao');
        }
//        $this->userInfo = $userInfo;
        foreach ($userInfo['data'] as $balance){
            if ($balance['asset_id']==$this->trans_btc){
//                print_r($balance);
                $btc = $balance['balance'];
                $this->btc = $btc;
            }
            if ($balance['asset_id']==$this->trans_usdt){
//                print_r($balance);
                $usdt = $balance['balance'];
                $this->usdt = $usdt;
            }
        }
        $p = $btc * end($prices) / ($btc * end($prices) + $usdt);//btc持仓占总资产（btc+usdt）的比例
//        print_r("\n");
//        print_r($p);
//        print_r("\n");
//        var_dump($orders);
//        print_r($t);
//        print_r($this->api->ordersCancel(104225046));
        if ($orders!=null){
            //等待400毫秒后取消订单
            usleep(400000);
            foreach ($orders as $id){
//               response::logging('INFO','开始取消订单',$id) ;
                $return_orders = $this->api->ordersCancel($id);
//                response::logging('INFO','取消订单完成',$return_orders,'orders');
            }
        }
        //执行太快休眠5毫秒
        while ((int)(microtime(true)*1000)-$t<500){
            usleep(5000);
        }
        return $p;
    }

    public function match(){
        $trans_type = $this->parameter['trans_type'];
        $p = '/(\w+).(\w+)/';
        preg_match($p, $trans_type,$return);
        $this->trans_btc = $return[1];
        $this->trans_usdt = $return[2];
    }

    // 定时扫描、取消失效的旧订单
    //  策略执行中难免会有不能成交、取消失败遗留下来的旧订单，
    //  定时取消掉这些订单防止占用资金
    public function cancelOrderAuto(){
        $pending_order = $this->api->orders(false,array("market_id"=>"ETH-USDT","state"=>"PENDING"));
        if (!$pending_order){
            exit('cao');
        }
        if (empty($pending_order['data']['edges'])){//无数据直接返回
            return true;
        }
        $inserted_at=array();
        foreach($pending_order['data']['edges'] as $node){
            $inserted_at[] = array(
                'inserted_at'=>strtotime($node['node']['inserted_at']),
                'id'=>$node['node']['id'],
            );
        }
        //和服务器时间比较
        $timestamp = $this->api->ping();
        if (!$timestamp){
            exit('cao');
        }
        $timestamp = substr($timestamp['timestamp'],0,10);//截取到秒
        foreach ($inserted_at as $pending){
            if ($pending['inserted_at']-$timestamp<10){// orders before 10s
                $return_orders = $this->api->ordersCancel($pending['id']);
                response::logging('INFO','取消超时订单完成',$return_orders);
            }
        }
    }

    //存储 计算运行的伪数据
    public function fakeOrder($trade_price,$btc_amount,$side){
        //持仓成本
        if ('ask'==$side){

        } elseif ('bid'==$side){

        }
        //持仓量 USDT BTC

        $this->cost += $trade_price*$btc_amount;
    }
    //批量下单
    public function batchOrders($array){
        $id=array();
        foreach ($array as $value){
            $trade_price = $value[0];
            $btc_amount = $value[1];
            $side = $value[2];
            $return_orders = $this->orders($trade_price,$btc_amount,$side);
            if (!$return_orders){
                //do nothing
            } else {
                $id[] = $return_orders['data']['id'];
            }
        }
//        print_r($id);
        return $id;
    }

    //进行交易
    public function orders($trade_price,$btc_amount,$side){
        //模拟交易
        if ($this->fake){
            response::logging('INFO','开始根据真实数据进行模拟交易,假定挂单立即成交',array($trade_price,$btc_amount,$side),'orders') ;
//            $this->fakeOrder($trade_price,$btc_amount,$side);
            return array(
                'data'=> array(
                    'id'=>109006100,
                    'state'=>'CANCELED',
                    'filled_amount'=>'0E-16',
                    'amount'=>1.0000000000000000,
                )
            );
        } else{
//            response::logging('INFO','开始挂单',array($this->parameter['trans_type'],$trade_price,$btc_amount,$side)) ;
            print_r(
                $return_orders = $this->api->orders(
                    array(
                        'market_id'=>$this->parameter['trans_type'],
                        'price'=>$trade_price,
                        'amount'=>$btc_amount,
                        'side'=>$side
                    )
                )
            );
            response::logging('INFO','挂单结束',array($return_orders)) ;
            //下单返回失败不用管了
//            if (!$return_orders){
//                return false;
//            }
//            print_r($return_orders);
            return $return_orders;
        }
    }

    // 更新历史交易数据，用于计算成交量
    public function updateTrades(){
        $trans_market_return = $this->api->tradesMarket($this->trans_type,2);
        if (!$trans_market_return){
            exit('cao');
        }
        $this->trans_market_return = $trans_market_return;
        //取最近2比成交记录
        $edges = $trans_market_return['data']['edges'];
        $vol0 = array_slice($edges,-2,1)[0]['node']['amount'];//次新
        $vol1 = array_slice($edges,-1,1)[0]['node']['amount'];//最新
        // 本次tick交易量 = 上次tick交易量*0.7 + 本次tick期间实际发生的交易量*0.3，用于平滑和减少噪音
        $vol = 0.7*$vol0+0.3*$vol1;
        $this->vol = $vol;
        return $vol;
    }

    // 更新盘口数据，用于计算价格
    public function updateOrderBook($prices=null){
        if (null==$prices){//自举
            $prices = $this->prices;
        }
        $depth_return = $this->api->depth($this->trans_type);//获取订单深度;
        if (!$depth_return){
            exit('cao');
        }

        $bid1 = $depth_return['data']['bids'][0]['price'];//买1
        $ask1 = $depth_return['data']['asks'][0]['price'];//卖1

        $bid2 = $depth_return['data']['bids'][1]['price'];//买2
        $ask2 = $depth_return['data']['asks'][1]['price'];//卖2
        $bid3 = $depth_return['data']['bids'][2]['price'];//买3
        $ask3 = $depth_return['data']['asks'][2]['price'];//卖3

        // 计算提单价格
        $bidPrice = $bid1*0.618+$ask1*0.382+0.01;
        $askPrice = $bid1*0.382+$ask1*0.618-0.01;
//        print_r($bid1."\n");
//        print_r($ask1."\n") ;
        print_r($bidPrice."\n");
        print_r($askPrice."\n");
        // 更新时间价格序列
        //  本次tick价格 = (买1+卖1)*0.35 + (买2+卖2) * 0.10 + (买3+卖3)*0.05
        //原先代码是在price数组末尾添加本次tick价格,每次循环会去除一个首位元素添加一个末尾元素
        $prices = array_slice($prices,1);//割除数组0元素
//        print_r($prices);
        $prices[] = ($bid1+$ask1)/2*0.7+($bid2+$ask2)/2*0.2+($bid3+$ask3)/2*0.1;
        $this->bidPrice = $bidPrice;
        $this->askPrice = $askPrice;
        $this->prices = $prices;
        $this->bid1 = $bid1;
        $this->ask1 = $ask1;
//        print_r($prices);
        $list =  array(
            'bidPrice'=>$bidPrice,
            'askPrice'=>$askPrice,
            'prices'=>$prices,
            'bid'=>$bid1,
            'ask'=>$ask1,
        );
        return $list;
    }

    //配置参数检测
    public function parametercheck($parameter){
//        print_r($parameter);
        if (is_null($parameter['secret_key']['public_key'])){
            response::logging('EMERGENCY','bigone public_key 未设置');
            exit();
        }
        if (is_null($parameter['secret_key']['private_key'])){
            response::logging('EMERGENCY','bigone private_key 未设置');
            exit();
        }
        if (is_null($parameter['base_url'])){
            response::logging('EMERGENCY','bigone base_url 未设置');
            exit();
        }
        if (is_null($parameter['trans_type'])){
            response::logging('EMERGENCY','bigone trans_type 未设置');
            exit();
        }
    }

}