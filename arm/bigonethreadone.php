<?php
/**
 * Created by PhpStorm.
 * User: reus
 * Date: 2018/7/20
 * Time: 21:58
 */

namespace ARM;

//因为php的限制将多线程拆分为独立文件以待移植
class bigonethreadone
{

    // 更新仓位
    public function threadone(){



    }

//def userInfo
//def btc
//def cny
//def p = 0.5
//threadExecutor.execute {
//while (true) {
//if (trading) {
//sleep 5
//continue
//}
//def t = System.currentTimeMillis()
//                ignoreException {
//    // 这里有一个仓位平衡的辅助策略
//    //  仓位平衡策略是在仓位偏离50%时，通过不断提交小单来使仓位回归50%的策略，
//    //  这个辅助策略可以有效减少趋势策略中趋势反转+大滑点带来的大幅回撤
//    def orders = (
//    p < 0.48 ? {
//                            cny -= 300.0
//                            trader2.batchTrade("btc_cny", Type.BUY, [
//                                new OrderData(orderBook.bids[0].limitPrice + 0.00, 0.010G, Type.BUY),
//                                new OrderData(orderBook.bids[0].limitPrice + 0.01, 0.010G, Type.BUY),
//                                new OrderData(orderBook.bids[0].limitPrice + 0.02, 0.010G, Type.BUY),
//                            ] as OrderData[])
//                        }() :
//                        p > 0.52 ? {
//        btc -= 0.030
//                            trader2.batchTrade("btc_cny", Type.SELL, [
//                                new OrderData(orderBook.asks[0].limitPrice - 0.00, 0.010G, Type.SELL),
//                                new OrderData(orderBook.asks[0].limitPrice - 0.01, 0.010G, Type.SELL),
//                                new OrderData(orderBook.asks[0].limitPrice - 0.02, 0.010G, Type.SELL),
//                            ] as OrderData[])
//                        }() :
//                        null)
//                    userInfo = account.userInfo
//                    btc = userInfo.info.funds.free.btc
//                    cny = userInfo.info.funds.free.cny
//                    p = btc * prices[-1] / (btc * prices[-1] + cny)
//
//                    if (orders != null) {
//                        sleep 400
//                        trader2.cancelOrder("btc_cny", orders.orderInfo.collect {it.orderId} as long[])
//                    }
//                }
//                while (System.currentTimeMillis() - t < 500) {
//                    sleep 5
//                }
//            }
//        }

}