<?php
/**
 *配置文件格式
 *```
 *function 应用名(){
 *	return [
 * 	'controller'=>['test'],
 * 	'def'=>['time'=>60,'num'=>100,'grade'=>2],//默认的控制器访问限制
 * 	'test'=>[
 *  	'action'=>'get',
 *  	//'def'=>['time'=>60,'num'=>100,'grade'=>2],//默认的方法的访问限制
 *  	'get'=>['time'=>60,'num'=>100,'grade'=>2],//具体的方法get的访问限制
 *  	'time'=>60,
 *  	'num'=>600,
 *  	'grade'=>1,
 *  	'tactic'=>['IP'],
 * 	],
 *  'time'=>60,
 *  'num'=>6000,
 *  'grade'=>0,
 *  'tactic'=>['IP','URL'], //访问的限制策略
 *  'type'=>1
 * ];
 *}
 *```
 *配置的必要参数为time，num
 *配置文件读取模式为只要最小一级找到规则，就不向上一级查找，
 *但是参数不足时，取上一级的的参数，
 *最上一级都没有找到将不做限制
 *
 *属性值
 *支持的策略；
 *```
 *tacticWay = ['IP','URL','USER']
 *IP 为客户的IP
 *URL为MD5(host+controller+action)
 *USER为用户提交的参数，在调用的时候传入,同时在配置文件中tactic指定USER，否则也是不使用的
 *
 *拿到值后，拼接后MD5操作后取前10位
 *```
 *
 *隐藏属性:$controllerName | $actionName 
 *
 *公共key，//主要项目的隔离,项目_策略 TK_IP
 *配置key，//主要是配置的不同，应用隔离_控制器隔离_方法隔离 YUNCHAO_PAPER_GET
 *私有key，//自主定义，例如可以是用户的IP,用户的UID，方法的参数ID
 *频率：time单位时间60s，num单位时间的访问量100s，
 *限制等级grade:0限制关闭，1只记录超限情况，2超限提示拒绝，默认为0
 *限制模式type:0将提交的限制策略组合为一个key检测（默认为0），1将提交的限制策略分别检测
 *
 * 
 *
 *0.初始化，写入配置信息，
 *1.生成key的方法规则
 *2.获取key的方法
 *3.判断key数量的
 *4.是否超限判断
 *5.超限后的操作
 *6.入口方法run；
 *7.清除限制;
 *
 *TEST  使用
 *
 *define('__CONTROLLER__', 'test');
define('__ACTION__', 'get');
define('HTTP_APP_ID_KEY', 'YUNCHAO');
define('TO_APP', 'tiku');
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$ret = json_decode($redis->get('BucketConfig_OK'),true);
 if (!empty($ret) && $ret['time'] != filectime('BucketConfig.php')){
    file_put_contents('BucketConfig.php', '<?php ' . PHP_EOL . 'return ' . var_export($ret['data'], true) . ";");
 }elseif(!file_exists('BucketConfig.php')){
    //生成下，
 }
// $nowtime = microtime();
// $a = new Bucket(102, 'YUNCHAO','TK', null, $redis);
// $a->checkLimit();
//     var_dump($a->result);die;
// $a->removeLimit();
// die;
// $_SERVER['HTTP_HOST'];
    $a = new Bucket(102, 'yunchao','tiku', null, $redis);
for ($i = 0; $i < 10; $i++) {
    // sleep(1);
    // echo (microtime() - $nowtime);
    $a->checkLimit();
    var_dump($a->result);
}

 



