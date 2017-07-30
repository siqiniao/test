<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('memory_limit', "2048M");


class sign{

    private $tmp = array();
    
    
    public function process() {
    
        $userCurrentPrice = $this->get_user_current_price();
        
        $data = array();
        
        $file = './sign_user_seed_info.txt';
        
        $file = new SplFileObject($file);
        while (! $file->eof()) {
            $line = $file->fgets();
            
            $lineNumber = $file->key();
            
            //@todo
//             $line = '403813490	10029	2	3	2016-05-17 20:51:34	2016-05-17 20:51:20	{"wateredDays":[17,27,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,18,19,21,22,23,25],"repouringDays":[16,20,24,26]}';
//             $line = '464085970	10029	2	3	2016-05-17 21:02:20	2016-05-17 21:02:08	{"wateredDays":[17,19,20,21],"repouringDays":[]}';
//             $line = '473908130	10029	2	3	2016-05-17 23:03:54	2016-05-17 23:03:46	{"wateredDays":[17,21,24,30,1,2,3,4,5,6,7,8,9,10,11,13,15,18,19,20,22,23,25,26,27],"repouringDays":[12,14,16]}';
            
            if($line)
            {
                $list = explode("\t", $line);
                 
                $uid  = trim($list[0]);
                $signJson = trim($list[6]);

                $signArr = json_decode($signJson, true);
                
                if(!is_array($signArr))
                {
                    $this->log("line:{$lineNumber}|{$line}");
                    continue;
                }
                
                //用户应该的奖品
                $realPrice = $this->get_user_real_price($signArr);
                
                $current_price = isset($userCurrentPrice[$uid]) ? $userCurrentPrice[$uid] : [];
                
                $lastPrice = $this->get_user_last_price($current_price, $realPrice);
                
                $this->log("--------------------[{$uid}]-------------- start");
                $this->log("realPrice:".var_export ($realPrice, true));
                $this->log("current_price:".var_export ($current_price, true));
                $this->log("lastPrice:".var_export ($lastPrice, true));
                
                $this->log("--------------------[{$uid}]-------------- end");
                
                if($lastPrice)
                {
                    $this->format_result($uid, $lastPrice);
                }
                
            }
             
        }
        
        return $data;
        
        
    
    }
    
    /**
     * 用户应该补发的奖品
     * @param unknown $param
     */
    function get_user_last_price($current_price, $real_price) {
        return array_diff_key($real_price, $current_price);
    }
    
    /**
     * 根据报名数据获取奖品
     * @param unknown $signData
     */
    public function get_user_real_price($signData) {
        
//         $priceConf = [
//             //定期
//             'dingqi' => [
//                 1 => 'c1',
//                 2 => 'c2',
//                 5 => 'c3',
//                 14 => 'c4',
//                 20 => 'c5',
//                 26 => 'c6',
//             ],
//             //累计
//             'leiji' => [
//                 3 => 'a1',
//                 8 => 'a2',
//                 11 => 'a3',
//                 17 => 'a4',
//                 23 => 'a5',
//                 28 => 'a6',
//                 31 => 'a7',
//             ],
//         ];
        
        
//         $priceConf = [
//             //定期
//             'dingqi' => [
//                 1 => 'c1',
//                 2 => 'c2',
//                 3 => 'c3',
//                 10 => 'c4',
//                 16 => 'c5',
//                 22 => 'c6',
//                 28 => 'c7',
//             ],
//             //累计
//             'leiji' => [
//                 4 => 'a1',
//                 7 => 'a2',
//                 13 => 'a3',
//                 19 => 'a4',
//                 25 => 'a5',
//                 30 => 'a6',
//             ],
//         ];
        
        //7月奖品
        $priceConf = [
            //定期
            'dingqi' => [
                1 => 'c1',
                5 => 'c2',
                14 => 'c3',
                20 => 'c4',
                26 => 'c5',
            ],
            //累计2
            'leiji' => [
                3 => 'a1',
                8 => 'a2',
                11 => 'a3',
                17 => 'a4',
                23 => 'a5',
                28 => 'a6',
                31 => 'a7',
            ],
        ];
        
        
        //@oops  一个月内，用户的奖品是不会重复的
        $priceList = [];
        
        //定期
        foreach ($priceConf['dingqi'] as $day=>$price)
        {
            if(in_array($day, $signData['wateredDays']))
            {
                $priceList[$price] = 1;
            }
        }
        
        //累计 签到+补签
        $signCount = count($signData['wateredDays']) + count($signData['repouringDays']);
        foreach ($priceConf['leiji'] as $count=>$price)
        {
            if($count<=$signCount)
            {
                $priceList[$price] = 1;
            }
        }
        
        return $priceList;
        
    }
    
    
    /**
     * 用户当前获取的奖品
     */
    public function get_user_current_price() {
        
        $data = array();
        
        $file = './sign_user_prize_log.txt';
        
        $file = new SplFileObject($file);
        while (! $file->eof()) {
           $line = $file->fgets();
            
           if($line)
           {
               $list = explode("\t", $line);
               
               $uid  = trim($list[0]);
               $price = trim($list[1]);
               
               if(isset($data[$uid][$price]))
               {
                   $data[$uid][$price] ++;
               }else{
                   $data[$uid][$price] = 1;
               }
           }
           
        }
        
        //错误数据检测（每个用户每种奖品应该只有一个）
//         foreach ($data as $uid=>$val )
//         {
//             foreach ($val as $v)
//             if($v>1)
//             {
//                 echo "{$uid} \n";
//             }
//         }
        
        return $data;
        
    }

    /**
     * 输出结果
     * @param unknown $param
     */
    public function format_result($uid, $lastPrice) {

        foreach ($lastPrice as $key=>$val)
        {
            file_put_contents($key, $uid."\n", FILE_APPEND);
        }
        
    }
    
    private function log($msg) {
        $msg = "{$msg} \n";
        file_put_contents('./signerror.txt', $msg, FILE_APPEND);
    }
    
}

$start = microtime(1);

$sign = new sign();
$sign->process();

$end = microtime(1);

var_dump($end-$start);


