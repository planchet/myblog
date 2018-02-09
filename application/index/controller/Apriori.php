<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/2/6
 * Time: 9:55
 */
namespace app\index\controller;
use think\Controller;           //引用官方封装的控制类

class Apriori extends Controller
{

    private $confidence;    //置信度

    public function index(){

        echo "hello word";

        $transaction['item1']="i1,i2,i5";
        $transaction['item2']="i2,i4";
        $transaction['item3']="i2,i3";
        $transaction['item4']="i1,i2,i4";
        $transaction['item5']="i1,i3";
        $transaction['item6']="i2,i3";
        $transaction['item7']="i1,i3";
        $transaction['item8']="i1,i2,i3,i5";
        $transaction['item9']="i1,i2,i3";
        $c1=$this->gen_c1($transaction);
        $l1=$this->gen_l1($c1,1);

//生成c1

        for($k=1;;$k++)
        {
            ${'c'.($k+1)}=$this->gen_ck(${'l'.$k},($k+1),$transaction,2);
            ${'l'.($k+1)}=${'c'.($k+1)};
            if(empty(${'c'.($k+1)}))
            {
                exit;
            }
            else
            {
                print_r(${'c'.($k+1)});
                echo "<br>";

            }
            //${'l'.($k+1)}=gen_lk(${'l'.$k},($k+1));
        }
        //print_r($c1);
        /*echo "<br>";
        print_r($c2);
        echo "<br>";
        print_r($c3);
        echo "<br>";
        print_r($c4);
        echo "<br>";
        print_r($c5);
        echo "<br>";*/

        $this->fetch();
    }

    public function gen_ck($l,$k,$t,$sup=2)
    {
        $array_row=0;
        for($i=0;$i<count($l);$i++)
        {
            for($j=$i+1;$j<count($l);$j++)
            {
                //echo $l[$i]['item']."=>".$l[$j]['item'];
                //echo "<br>";
                $temp_array=array();
                $match=true;
                if($k>2)
                {
                    $split_array_i=split(',',$l[$i]['item']);//将ck的一项中的item中的数据分解到一个数组中
                    //print_r($split_array_i);
                    //echo " i <br>";

                    $split_array_j=split(',',$l[$j]['item']);
                    //print_r($split_array_j);
                    //echo " j <br>";

                    for($i3=0;$i3<(count($split_array_i)-1);$i3++)
                    {
                        if($split_array_i[$i3]!=$split_array_j[$i3])
                        {
                            $match=false;
                        }
                    }
                }
                if($match)
                {
                    $split_array=split(',',$l[$i]['item']);//将ck的一项中的item中的数据分解到一个数组中
                    for($i1=0;$i1<count($split_array);$i1++)
                    {
                        array_push($temp_array,$split_array[$i1]);
                    }
                    $split_array=split(',',$l[$j]['item']);
                    for($i1=0;$i1<count($split_array);$i1++)
                    {
                        array_push($temp_array,$split_array[$i1]);
                    }

                    //array_push($temp_array,$l[$i]['item']);
                    //array_push($temp_array,$l[$j]['item']);
                    $temp_array=array_unique($temp_array);

                    sort($temp_array);

                    if($k==2)
                    {
                        //print_r($temp_array);
                        //echo "<br>";
                    }
                    //${'c'.$k}[$array_row]['item']=$temp_array[0].",".$temp_array[1];
                    $temp_string='';
                    for($i4=0;$i4<count($temp_array);$i4++)
                    {
                        $temp_string=$temp_string.$temp_array[$i4].',';
                    }
                    //echo $temp_string;
                    //echo "<br>";

                    $temp_string=substr($temp_string,0,strlen($temp_string)-1);//去尾部逗号，在本模块中只能用这种方法去掉末尾逗号
                    //${'c'.$k}[$array_row]['item']=$temp_string;
                    //${'c'.$k}[$array_row]['support']=1;
                    $c[$array_row]['item']=$temp_string;
                    $c[$array_row]['support']=1;
                    $array_row++;
                }
                else
                {
                    //echo "not match";
                }
            }
        }
        //print_r(${'c'.$k});
        //支持度统计
        for($i=0;$i<count($c);$i++)
        {
            $temp_support=0;

            for($i1=1;$i1<=count($t);$i1++)
            {
                $temp_array=split(',',$c[$i]['item']);
                $temp_support1=true;
                for($i2=0;$i2<count($temp_array);$i2++)
                {
                    //echo $t['item'.$i1]."=>".$temp_array[$i2];
                    if(strpos($t['item'.$i1],$temp_array[$i2])===false)
                    {
                        $temp_support1=false;
                    }

                }
                if($temp_support1)
                {
                    $temp_support++;
                }

            }
            //echo $temp_support;
            //echo "<br>";
            $c[$i]['support']=$temp_support;
        }
        //删除支持小于最小置信度的项目
        $temp_array=array();
        $array_row=0;
        for($i=0;$i<count($c);$i++)
        {
            if($c[$i]['support']>=$sup)
            {
                $temp_array[$array_row]['item']=$c[$i]['item'];
                $temp_array[$array_row]['support']=$c[$i]['support'];
                $array_row++;
            }
        }
        $c=$temp_array;
        return $c;
    }

    public function gen_lk($c)
    {

    }

    public function gen_c1($t)
    {
        $array_row=0;
        for($i=1;$i<=count($t);$i++)
        {
            $temp_array=array();
            $temp_array=split(',',$t['item'.$i]);
            foreach ($temp_array as $temp_value)
            {
                if($this->in_array_two_dimension(&$c,$temp_value))//值已存在于ck数组中，增加计数
                {
                    //$c[$array_row]['support']++;
                }
                else//新值入践
                {
                    $c[$array_row]['item']=$temp_value;
                    $c[$array_row]['support']=1;
                    $array_row++;
                }
            }
        }
        return $c;
    }

    public function gen_l1($c1,$sup=2)
    {
        $array_row=0;
        $temp_array=array();
        for($i=0;$i<count($c1);$i++)
        {
            if($c1[$i]['support']>$sup)
            {
                $temp_array[$array_row]['item']=$c1[$i]['item'];
                $temp_array[$array_row]['support']=$c1[$i]['support'];
                $array_row++;
            }
        }
        return $temp_array;
    }

    public function in_array_two_dimension($array,$value)
    {
        for($i=0;$i<count($array);$i++)
        {
            if($array[$i]['item']==$value)
            {
                $array[$i]['support']++;
                return true;
            }
        }
        return false;
    }

}










?>