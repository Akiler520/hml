<?php
/**
 * tuangou.php
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.niushop.com.cn
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */
namespace app\admin\controller;

use data\model\NsGoodsSkuModel;
use data\service\Yifen as YifenSVS;
use data\service\Order\OrderStatus;
use data\service\Express;
use think\Cache;
use think\Exception;

/**
 * 团购
 */
class Yifen extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 拼团订单列表
     */
    public function orderList()
    {
        // 获取物流公司
        $express = new Express();
        $expressList = $express->expressCompanyQuery();
        $this->assign('expressList', $expressList);
         
        $action = Cache::get("orderAction");
        if(empty($action)){
            $action = array(
                "orderAction" => $this->fetch($this->style . "Order/orderAction"),
                "orderPrintAction" => $this->fetch($this->style . "Order/orderPrintAction"),
                "orderRefundAction" => $this->fetch($this->style . "Order/orderRefundAction")
            );
            Cache::set("orderAction", $action);
        }
        
        if (request()->isAjax()) {
            $condition = array();
            $page_index = request()->post('page_index', 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
            $end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
            $user_name = request()->post('user_name', '');
            $order_no = request()->post('order_no', '');
            $order_status = request()->post('order_status', '');
            $receiver_mobile = request()->post('receiver_mobile', '');
            $payment_type = request()->post('payment_type', 1);
            $shipping_type = request()->post('shipping_type', 0); //配送类型
            // 拼团id
//            $tuangou_group_id = request()->post('tuangou_group_id', 0);
            $condition['order_type'] = 5; // 订单类型
            $condition['is_deleted'] = 0; // 未删除订单
                                          
            // 拼团id加入条件
//            if ($tuangou_group_id > 0) {
//                $condition['tuangou_group_id'] = $tuangou_group_id; // 未删除订单
//            }

            if ($start_date != 0 && $end_date != 0) {
                $condition["create_time"] = [
                    [
                        ">",
                        $start_date
                    ],
                    [
                        "<",
                        $end_date
                    ]
                ];
            } elseif ($start_date != 0 && $end_date == 0) {
                $condition["create_time"] = [
                    [
                        ">",
                        $start_date
                    ]
                ];
            } elseif ($start_date == 0 && $end_date != 0) {
                $condition["create_time"] = [
                    [
                        "<",
                        $end_date
                    ]
                ];
            }
            if ($order_status != '') {
                // $order_status 1 待发货
                if ($order_status == 1) {
                    // 订单状态为待发货实际为已经支付未完成还未发货的订单
                    $condition['shipping_status'] = 0; // 0 待发货
                    $condition['pay_status'] = 2; // 2 已支付
                    $condition['order_status'] = array(
                        'neq',
                        4
                    ); // 4 已完成
                    $condition['order_status'] = array(
                        'neq',
                        5
                    ); // 5 关闭订单
                } else
                    $condition['order_status'] = $order_status;
            }
            if (! empty($payment_type)) {
                $condition['payment_type'] = $payment_type;
            }
            if (! empty($user_name)) {
                $condition['receiver_name'] = $user_name;
            }
            if (! empty($order_no)) {
                $condition['order_no'] = $order_no;
            }
            if (! empty($receiver_mobile)) {
                $condition['receiver_mobile'] = $receiver_mobile;
            }
            if($shipping_type != 0){
                $condition['shipping_type'] = $shipping_type;
            }
            $condition['shop_id'] = $this->instance_id;
            $order_service = new YifenSVS();

            $list = $order_service->getOrderList($page_index, $page_size, $condition, 'create_time desc');
            $list['action'] = $action;
            return $list;
        } else {
            $tuangou_group_id = request()->get("tuangou_group_id", 0);
            $this->assign("tuangou_group_id", $tuangou_group_id);
            $status = request()->get('status', '');
            $this->assign("status", $status);
            $all_status = OrderStatus::getOrderPintuanStatus();
            $child_menu_list = array();
            $child_menu_list[] = array(
                'url' => "yifen/orderList",
                'menu_name' => '全部',
                "active" => $status == '' ? 1 : 0
            );
            foreach ($all_status as $k => $v) {
                // 针对发货与提货状态名称进行特殊修改
                /*
                 * if($v['status_id'] == 1)
                 * {
                 * $status_name = '待发货/待提货';
                 * }elseif($v['status_id'] == 3){
                 * $status_name = '已收货/已提货';
                 * }else{
                 * $status_name = $v['status_name'];
                 * }
                 */
                $child_menu_list[] = array(
                    'url' => "yifen/orderList?status=" . $v['status_id'],
                    'menu_name' => $v['status_name'],
                    "active" => $status == $v['status_id'] ? 1 : 0
                );
            }
            $this->assign('child_menu_list', $child_menu_list);
            
            return view($this->style . "Yifen/orderList");
        }
    }

}