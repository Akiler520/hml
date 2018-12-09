<?php
use data\model\TestModel;

use data\service\BaseService as BaseService;


class Test extends BaseService
{



    public function getusers(){
        $test = new TestModel();

        $user_info = $test->getInfo([
           ''
        ], 'user_id,user_name,password,user_money,parent_id,openid,headimgurl,uname');

        return $user_info;
    }
}