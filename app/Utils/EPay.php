<?php
namespace App\Utils;

use App\Services\View;
use App\Services\Auth;
use App\Models\Node;
use App\Models\TrafficLog;
use App\Models\InviteCode;
use App\Models\CheckInLog;
use App\Models\Ann;
use App\Models\Speedtest;
use App\Models\Shop;
use App\Models\Coupon;
use App\Models\Bought;
use App\Models\Ticket;
use App\Services\Config;
use App\Utils\Hash;
use App\Utils\Tools;
use App\Utils\Radius;
use App\Utils\Wecenter;
use App\Models\RadiusBan;
use App\Models\DetectLog;
use App\Models\DetectRule;
use voku\helper\AntiXSS;
use App\Models\User;
use App\Models\Code;
use App\Models\Ip;
use App\Models\Paylist;
use App\Models\LoginIp;
use App\Models\BlockIp;
use App\Models\UnblockIp;
use App\Models\Payback;
use App\Models\Relay;
use App\Utils\QQWry;
use App\Utils\GA;
use App\Utils\Geetest;
use App\Utils\Telegram;
use App\Utils\TelegramSessionManager;
use App\Utils\Pay;
use App\Utils\URL;
use App\Services\Mail;



require_once("lib/epay_submit.class.php");

class EPay
{
    public function smarty()
    {
        $this->smarty = View::getSmarty();
        return $this->smarty;
    }

    public function view()
    {
        return $this->smarty();
    }

    public static function render()
    {
        return View::getSmarty()->fetch("user/epay.tpl");
    }


    public function handle($request, $response, $args)
    {
        require_once("epay.config.php");
        $price = $request->getParam('price');
        $user = Auth::getUser();
        $pl = new Paylist();
        $pl->userid = $user->id;
        $pl->total = $price;
        $pl->domain = $_SERVER['HTTP_HOST'];
        $pl->save();


        /**************************请求参数**************************/
        $notify_url = "https://jettss.xyz/epay/epay_notify";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
    
        //页面跳转同步通知页面路径
        $return_url = "https://".$pl->domain."/user/code";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
    
        //商户订单号
        $out_trade_no = $pl->id;
        //支付方式
        $type = 'alipay';
        //商品名称
        $name = 'xxxxxxxxxxxxx';
        //付款金额
        $money = $price;
        //站点名称
        $sitename = '彩虹易支付测试站点';

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "pid" => trim($alipay_config['partner']),
            "type" => $type,
            "notify_url" => $notify_url,
            "return_url" => $return_url,
            "out_trade_no" => $out_trade_no,
            "name" => $name,
            "money" => $money,
            "sitename" => $sitename
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter);
        return json_encode([
            'errcode' => 0,
            'code' => $html_text,
        ]);
    }

    public function handle_callback($request, $response, $args)
    {
        require_once("epay.config.php");
        require_once("lib/epay_notify.class.php");
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {//验证成功
            $out_trade_no = $_GET['out_trade_no'];
            //彩虹易支付交易号
            $trade_no = $_GET['trade_no'];
            //交易状态
            $trade_status = $_GET['trade_status'];
            //支付方式
            $type = $_GET['type'];
            if ($trade_status == 'TRADE_SUCCESS') {
                $p = Paylist::find($out_trade_no);
                if ($p->status == 1) {
                    return json_encode(['errcode' => 0]);
                }
                $p->status = 1;
                $p->tradeno = $transid;
                $p->datetime = date("Y-m-d H:i:s");
                $p->save();
                $user = User::find($p->userid);
                $user->money += $p->total;
                $user->save();
                $codeq = new Code();
                $codeq->code = "支付宝充值";
                $codeq->isused = 1;
                $codeq->type = -1;
                $codeq->number = $p->total;
                $codeq->usedatetime = date("Y-m-d H:i:s");
                $codeq->userid = $user->id;
                $codeq->save();
                return json_encode(['errcode' => 0]);
            }
        }
        return '';
    }
}