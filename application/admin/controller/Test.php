<?php

namespace app\admin\controller;

use data\service\Address;
use data\service\Album;
use data\service\Express as Express;
use data\service\Goods as GoodsService;
use data\service\GoodsBrand as GoodsBrand;
use data\service\GoodsCategory as GoodsCategory;
use data\service\GoodsGroup as GoodsGroup;
use data\service\Supplier;

use think\Config;
use data\service\VirtualGoods;
use data\service\Config as ConfigService;
use data\service\Member;


use data\service\Test as TestService;

class Test extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }


    public function test()
    {

        $test = new TestService();

        $user = $test ->getusers();
        
        print_r($user);





    }
}