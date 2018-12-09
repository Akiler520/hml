<?php
/**
 * Goodsseckill.php
 *
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
namespace data\service\promotion;

use data\model\NsPromotionSeckillGoodsModel;
use data\model\NsPromotionSeckillGoodsViewModel;
use data\service\BaseService;
use data\model\AlbumPictureModel;
use data\model\NsPromotionSeckillModel;
use data\model\NsShopModel;
use think\Model;

/**
 * 商品显示秒杀活动
 * 
 * @author Administrator
 *        
 */
class GoodsSeckill extends BaseService
{

    /**
     * 查询商品在某一时间段是否有限时秒杀活动
     * 
     * @param unknown $goods_id            
     */
    public function getGoodsIsSeckill($goods_id, $start_time, $end_time)
    {
        $seckill_goods = new NsPromotionSeckillGoodsModel();
        $condition_1 = array(
            'start_time' => array(
                'ELT',
                $end_time
            ),
            'end_time' => array(
                'EGT',
                $end_time
            ),
            'status' => array(
                'NEQ',
                3
            ),
            'goods_id' => $goods_id
        );
        $condition_2 = array(
            'start_time' => array(
                'ELT',
                $start_time
            ),
            'end_time' => array(
                'EGT',
                $start_time
            ),
            'status' => array(
                'NEQ',
                3
            ),
            'goods_id' => $goods_id
        );
        $condition_3 = array(
            'start_time' => array(
                'EGT',
                $start_time
            ),
            'end_time' => array(
                'ELT',
                $end_time
            ),
            'status' => array(
                'NEQ',
                3
            ),
            'goods_id' => $goods_id
        );
        $count_1 = $seckill_goods->where($condition_1)->count();
        $count_2 = $seckill_goods->where($condition_2)->count();
        $count_3 = $seckill_goods->where($condition_3)->count();
        $count = $count_1 + $count_2 + $count_3;
        return $count;
    }

    /**
     * 查询限时秒杀的商品
     * 
     * @param number $page_index            
     * @param number $page_size            
     * @param array $condition
     *            注意传入数组
     * @param string $order            
     */
    public function getseckillGoodsList($page_index = 1, $page_size = 0, $condition = array(), $order = '')
    {
        $seckill_goods = new NsPromotionseckillGoodsViewModel();
        $goods_list = $seckill_goods->getViewList($page_index, $page_size, $condition, $order);
        if (! empty($goods_list['data'])) {
            $seckill = new NsPromotionseckillModel();
            $shop = new NsShopModel();
            $picture = new AlbumPictureModel();
            foreach ($goods_list['data'] as $k => $v) {
                $seckill_info = $seckill->getInfo([
                    'seckill_id' => $v['seckill_id']
                ], 'shop_id, shop_name, seckill_name');
                $goods_list['data'][$k]['shop_name'] = $seckill_info['shop_name'];
                $goods_list['data'][$k]['seckill_name'] = $seckill_info['seckill_name'];
                $shop_info = $shop->getInfo([
                    'shop_id' => $seckill_info['shop_id']
                ], 'shop_credit');
                $goods_list['data'][$k]['shop_credit'] = $shop_info['shop_credit'];
                $picture_info = $picture->get($v['picture']);
                $goods_list['data'][$k]['picture'] = $picture_info;
                if($v['point_exchange_type'] == 0 || $v['point_exchange_type'] == 2){
                    $goods_list['data'][$k]['display_price'] = '￥'.$v["promotion_price"];
                }else{
                    if($v['point_exchange_type'] == 1 && $v["promotion_price"] > 0){
                        $goods_list['data'][$k]['display_price'] = '￥'.$v["promotion_price"].'+'.$v["point_exchange"].'积分';
                    }else{
                        $goods_list['data'][$k]['display_price'] = $v["point_exchange"].'积分';
                    }
                }
            }
        }
        return $goods_list;
    }

    /**
     * 获取 一个商品的 限时秒杀信息
     * 
     * @param unknown $goods_id            
     */
    public function getseckillByGoodsid($goods_id)
    {
        $seckill_goods = new NsPromotionseckillGoodsModel();
        $seckill = $seckill_goods->getInfo([
            'goods_id' => $goods_id,
            'status' => 1
        ], 'seckill');
        if (empty($seckill)) {
            return - 1;
        } else {
            return $seckill['seckill'];
        }
    }
}