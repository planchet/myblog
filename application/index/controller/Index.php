<?php
namespace app\index\controller;

use think\Session;              //引用官方封装的Session类
use think\Controller;           //引用官方封装的控制类
use think\Cache;                //引用官方封装的缓存类


class Index extends Controller
{
    //显示首页
    public function index()
    {
        //          模块名/控制器名/方法名
        $index = url('index/index/login');

        //打开当前项目中 application/模块名\view\控制器名\方法名.html（如果在fetch中传值，则打开值.html）
        return $this->fetch();
    }
    //登录界面
    public function login()
    {
        //          模块名/控制器名/方法名
        $doAction = url('index/index/doAction');

        $this->assign('doAction',$doAction);//向页面传值

        return $this->fetch();//打开页面
    }
    //处理登录表单
    public function doAction()
    {
        $model = model('user');

        //判断表单是否有传值过来  没有就赋值为空
        $user = input('post.user','');
        $upas = input('post.upas','');

        $res =$model->where(['uuser'=>$user,'upas'=>md5($upas)])->find();    //带着查询条件向数据库查询

        if(!empty($res))
        {
            //结果不为空  则session缓存更新
            Session::set('nowID',$res->uid);
            Session::set('nowlogin',$res->uuser);
            //跳转页面并友好提示
            $this ->success('登录成功','index/index/center',3);
        }
        else
        {
            //跳转页面并友好提示
            $this ->error('登录失败,账号或密码输入错误','index/index/login',3);
        }


    }

    public function center()
    {
        if(!(Session::has('nowlogin')==null))
        {
            //存在  则进入页面
            $nowID=Session::get('nowID');
            $nowLogin=Session::get('nowlogin');

            $model = model('question');//引用表数据类


            if(!(input('?get.keyword')==null))
            {
                //文件类型缓存
                /*$resArr = Cache::get(input('get.keyword').'cachenew'.input('get.page'));//从缓存中读取数据
                if(!$resArr)//如果缓存不存在
                {
                    //echo '进入数据库查询';
                    $resArr =$model->where(['uid'=>$nowID])
                        ->alias('q')        //给表起别名
                        ->join('subject s','s.qid = q.qid') //联表查询
                        ->join('option o','o.sid = s.sid')  //联表查询
                        ->where('qtitle','like','%'.input('get.keyword').'%')   //通配符模糊查询
                        ->paginate(5,false,['query' => ['keyword'=>input('get.keyword')]]);
                        //分页方法(页面显示几条，是否简化，配置文件[query(页面跳转所携带的参数)])
                    Cache::set(input('get.keyword').'cachenew'.input('get.page'),$resArr,3600);//将$resArr存入缓存
                }*/

                //redis缓存
                $redis=new \Redis();//无需引用类  在之前加一个反斜杠即可

                    //键值对
                    /*$redis->set('test','1');
                    echo $redis->get('test');*/

                    //哈希结构
                    $redis->connect('127.0.0.1',6379);
                    $resArr =unserialize($redis->hGet('resArr',input('get.keyword').'cachenew'.input('get.page')));
                    if(!$resArr)
                    {
                        $resArr =$model->where(['uid'=>$nowID])
                            ->alias('q')        //给表起别名
                            ->join('subject s','s.qid = q.qid') //联表查询
                            ->join('option o','o.sid = s.sid')  //联表查询
                            ->where('qtitle','like','%'.input('get.keyword').'%')   //通配符模糊查询
                            ->paginate(5,false,['query' => ['keyword'=>input('get.keyword')]]);
                        //分页方法(页面显示几条，是否简化，配置文件[query(页面跳转所携带的参数)])
                        $redis->hSet('resArr',input('get.keyword').'cachenew'.input('get.page'),serialize($resArr));
                    }

                    //队列
                    /*$redis->lPush('key', 'value');
                    echo $redis->lSize('key');*/
                    //集合

                //memcache缓存

                //mongodb缓存

            }else{

                $resArr =$model->where(['uid'=>$nowID])
                    ->alias('q')
                    ->join('subject s','s.qid = q.qid')
                    ->join('option o','o.sid = s.sid')
                    ->paginate(5);
            }

            $this->assign('nowLogin',$nowLogin);//向页面传值
            $this->assign('resArr',$resArr);//向页面传值


            $exitUrl = url('index/index/exitUrl');//生成退出按钮的url
            $this->assign('exitUrl',$exitUrl);//向页面传值


            return $this->fetch();//打开页面
        }else{
            //不存在则退出页面
            $this ->error('未登录用户，请重新登录','index/index/login',3);
        }

    }

    //增加数据方法
    public function addCenterData()
    {
        for($i=0;$i<200;$i++)
        {
            $data=['qtitle'=>'第'.$i.'篇问卷','uid'=>1];//配置信息

            model('question')->insert($data);//向t_question表插入一条记录

            $data=['stitle'=>'第'.$i.'篇问卷的题目','stype'=>1, 'qid'=>model('question')->getLastInsID()];//获取新生成记录的ID，加入配置中

            model('subject')->insert($data);//向t_subject表插入一条记录

            $data=['otitle'=>'第'.$i.'篇问卷的选项','sid'=>model('subject')->getLastInsID()];//获取新生成记录的ID，加入配置中

            model('option')->insert($data);//向t_option表插入一条记录
        }

        $this ->success('数据插入成功','index/index/center',3);
    }

    //退出，并注销账户
    public function exitUrl()
    {
        //清除关键session
        Session::delete('nowlogin');
        Session::delete('nowID');
        //跳转页面并友好提示
        $this ->success('清除session成功，已退出','index/index/login',3);
    }

    public function teeest(){

    }
}
