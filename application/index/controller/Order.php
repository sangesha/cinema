<?php
namespace app\index\controller;

use think\Db;

class Order extends Common
{

    public function index()
    {
        $num = input("get.id");
        $data = Db::name("order")->alias("o")->where("ordernum",$num)->where("isdel",0)->join("cinema_platter p","p.id=o.pid","left")->find();
        if(!$data){
            $this->jsback("","无此订单号或此订单已被删");
            exit;
        }
        if($data['status']==1){
            $this->jsback("","此订单号已付款");
            exit;
        }
        
        $hallData = Db::name("moviehall")->where("id",$data['hallid'])->find();
        $movieData = Db::name("movie")->where("id",$data["movieid"])->find();
        $user_money = Db::name("user")->where("id",$data["userid"])->value("money");
        $group = Db::name("user")->alias("u")->join("group g","u.groupid=g.id","left")->where("u.id",$data['userid'])->field("g.preferential")->find();

        
        $data["seat"] = json_decode($data["seat"],true);
        foreach($data['seat'] as &$val)
        {
            $val = str_replace("_","排",$val)."座";
        }
        unset($val);
        $interval = $data['time']+450-time()-1;
        if($interval>=450){
            $this->jsback("","无此订单号或此订单已被删");
            exit;
        }
        //  dump($data);
        // dump($hallData);
        if($group["preferential"]==0){
            $group["preferential"] = 100;
        }
        $this->assign("preferential",$group["preferential"]);
        $this->assign("user_money",$user_money);
        $this->assign("data",$data);
        $this->assign("movieData",$movieData);
        $this->assign("hallData",$hallData);
        $this->assign("interval",$interval);
        return view();
    }

    public function delOrder(){
        $order = input("post.order");
        $result = Db::name("order")->where("ordernum",$order)->where("status","0")->update(["isdel"=>1]);
        if($result){
            return ["code"=>1,"msg"=>"success","data"=>[]];
        }else{
            return ["code"=>0,"msg"=>"error","data"=>[]];
        }
    }
    public function offOrder(){
        $order = input("post.order");
        $result = Db::name("order")->where("ordernum",$order)->update(["isshow"=>0]);
        if($result){
            return ["code"=>1,"msg"=>"success","data"=>[]];
        }else{
            return ["code"=>0,"msg"=>"error","data"=>[]];
        }
    }
}
