<?php
namespace XP\TB;

class Bucket
{
    private $_redis = false;
    private $_redis_host = '127.0.0.1';
    private $_redis_port = 6379;
    private $_now = 0;
    private $_controller;
    private $_action;
    private $_app;
    private $_tactic;
    private $_time;
    private $_num;
    private $_doNum;
    private $_falseDoNum = 0;
    private $_nextBucket;
    private $_userData;
    private $_userKey;
    private $_configKey;
    private $_publicKey = '';
    private $_def = [];
    private $_grade = 1;
    private $_type = 0;
    public $result = true;
    private $_message = '[ %s ] the api %s can visit %d time(s) in %d s ,but now you visit %d times';
    private $_needKeys = ['time', 'num', 'tactic', 'grade', 'type', 'def'];

    /**
     * 初始化，提交必要的参数$userData，$from
     * @method __construct
     * @param  string     $userData   用户的数据，在使用策略USER时使用
     * @param  string      $from       APP的编号，类似模块
     * @param  string      $to       请求的应用名
     * @param  array      $def       默认的情况
     * @param  source      $redis      可以通过外部实例化的redis传进来使用
     * @param  string     $controller 控制器
     * @param  string      $action     方法
     */
    public function __construct($userData = null, $from = HTTP_APP_ID_KEY, $to = TO_APP, $def = [], $redis = null, $controller = CONTROLLER_NAME, $action = ACTION_NAME)
    {
        if (!$this->_redis) {
            $redis = new \Redis();
            $redis->connect($this->_redis_host, $this->_redis_port);
            $ret = json_decode($redis->get('BucketConfig_OK'), true);
            if (!empty($ret)) {
                if (!file_exists(__DIR__ . '/BucketConfig.php') || $ret['time'] < filectime('BucketConfig.php') + 24 * 3600) {

                    file_put_contents(__DIR__ . '/BucketConfig.php', '<?php ' . PHP_EOL . 'return ' . var_export($ret['data'], true) . ";");
                    $ret['time'] = time();
                    $key = 'BucketConfig_OK';
                    $ret = json_encode($ret);
                    $redis->set($key, $ret);
                }
            } else {
                    //生成文件；
                }
            $this->_redis = $redis;
        }
        if (is_array($userData)) {
            $this->_userData = implode(',', $userData);
        } else {
            $this->_userData = $userData;
        }
        $allConfig = require_once __DIR__ . '/BucketConfig.php';
        $this->_app = strtolower($from);
        $this->_publicKey = strtolower($to);
        $this->_def = !empty($def) ? $def : $allConfig[$this->_publicKey]['def'];
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
        $this->_nextBucket = $allConfig[$this->_publicKey][$this->_app];

    }

    /**
     * 配置初始化
     * @method _handlerConfig
     * @param  array         $config  配置的数组
     * @return void   无返回值，直接给类的属性赋值
     */
    private function _handlerConfig($config)
    {
        $this->_nextBucket = [];
        $this->_tactic = [];

        foreach ($this->_needKeys as $value) {
            $p_k = '_' . $value;
            $this->$p_k = isset($config[$value]) ? $config[$value] : (isset($this->_def[$value]) ? $this->_def[$value] : $this->$p_k);

        }
        if (!empty($this->_tactic) && is_string($this->_tactic)) {
            $this->_tactic = explode(',', $this->_tactic);
        }
        isset($config['controller']) && in_array($this->_controller, $config['controller']) && array_key_exists($this->_controller, $config) && $this->_nextBucket = $config[$this->_controller];
        isset($config['action']) && in_array($this->_action, $config['action']) && array_key_exists($this->_action, $config) && $this->_nextBucket = $config[$this->_action];
        switch ($this->_now) {
            case 0:
                $this->_configKey = $this->_app;
                break;
            case 1:
                $this->_configKey = $this->_app . '_' . $this->_controller;
                break;
            case 2:
                $this->_configKey = $this->_app . '_' . $this->_controller . '_' . $this->_action;
                break;
            default:
                $this->_configKey = $this->_app;
                break;
        }
        $this->_now ++;
    }

    /**
     * 生成key，同时返回这个key
     * @method _getTheKey
     * @return string     按照规则生成的key
     */
    private function _getTheKey()
    {
        if (empty($this->_num)) {
            return false;
        }
        $this->_publicKey .= '_' . implode('', $this->_tactic);
        if (in_array('IP', $this->_tactic)) {
            $this->_userKey .= '_' . $this->_getIP();
        }
        if (in_array('URL', $this->_tactic)) {
            $this->_userKey .= '_' . $this->_controller . '_' . $this->_action;
        }
        if (in_array('USER', $this->_tactic)) {
            $this->_userKey .= '_' . $this->_userData;
        }
        $this->_userKey = substr(md5($this->_userKey), 0, 10);

        return $this->_publicKey . '_' . $this->_configKey . '_' . $this->_userKey;
    }

    /**
     * 获取到用户的ip 来自TP框架
     * @method _getIP
     * @return string 获取的IP
     */
    private function _getIP($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }

                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * 检测是否生成记录，没有就写记录同时设置时间，有就仅仅更新时间
     * @method _makeRecord
     * @param  string      $key redis的key
     * @param  integer     $num 开始的值
     * @return void  无返回只是在redis中保存
     */
    private function _makeRecord($key, $num = 1, $uptime = true)
    {
        
        if ($this->_redis->exists($key)) {
            if ($uptime) {
                $this->_redis->expire($key, $this->_time);
            }
        } else {
            $this->_redis->setex($key, $this->_time, $num);
            if ($uptime) {
                $this->_redis->setex($key . '_1', $this->_time, 1);
            }
        }

    }

    /**
     * 验证通过的记录处理
     * @method _changeTrueRecord
     * @param  string            $key  主要的key
     * @return void  无返回只是在redis中保存
     */
    private function _changeTrueRecord($key)
    {

        $this->_redis->incr($key);
        $end = $this->_redis->get($key);
        $this->_redis->setex($key . '_' . $end, $this->_time, 1);
    }

    /**
     * 验证未通过的记录处理
     * 会改变属性_falseDoNum的值
     * @method _changeTrueRecord
     * @param  string            $key  主要的key
     * @return void  无返回只是在redis中保存
     */
    private function _changeFalseRecord($key)
    {
        $key .= '_falseDoNum';
        $this->_makeRecord($key, ($this->_doNum - 1), false);
        $this->_redis->incr($key);
        $this->_falseDoNum = $this->_redis->get($key);
    }

    /**
     * 检测等级
     * 0 return true; 1 写日志return false；2抛异常
     * @method _checkGrade
     * @return bool     根据错误等级来选择写日志还是抛异常
     */
    private function _checkGrade()
    {
        switch (intval($this->_grade)) {
            case 0:
                return true;
                break;
            case 1:
                //写日志
                file_put_contents(date('Y-m-d') . '.log', sprintf($this->_message, date('H:i:s'), $this->_configKey, $this->_num, $this->_time, $this->_falseDoNum) . PHP_EOL, FILE_APPEND);
                return false;
                break;
            case 2:
                //抛异常，拒绝访问
                throw new \Exception(sprintf($this->_message, date('H:i:s'), $this->_configKey, $this->_num, $this->_time, $this->_falseDoNum), 0x11111111);
                return false;
            default:
                file_put_contents(date('Y-m-d') . '.log', sprintf($this->_message, date('H:i:s'), $this->_configKey, $this->_num, $this->_time, $this->_falseDoNum) . PHP_EOL, FILE_APPEND);
                return false;
                break;
        }
    }

    /**
     * 检查数量，正常返回的是true，失败根据等级判定
     * @method _checkNum
     * @param  string    $key 主要的key
     * @return bool   正常返回的是true，失败根据等级判定
     */
    private function _checkNum($key)
    {
        $this->_makeRecord($key);
        $max = $this->_findMaxValue($key);
        $min = $this->_findMinValue($key);
        $this->_doNum = $max - $min + 1;
        if ($this->_doNum > $this->_num) {
            $this->_changeFalseRecord($key);
            return $this->_checkGrade();
        } else {
            $this->_changeTrueRecord($key);
            return true;
        }
    }

    /**
     * 当前记录主要key的值
     * @method _findMaxValue
     * @param  string        $key 主要的key
     * @return int   redis中key的值
     */
    private function _findMaxValue($key)
    {
        return $this->_redis->get($key);
    }

    /**
     * 找到还存在的最小key
     * @method _findMinValue
     * @param  string        $key 主要的key
     * @return int   主要的key的相关最小值
     */
    private function _findMinValue($key)
    {
        $max = $this->_redis->get($key);
        $tempMin = 1;
        do {
            if ($max == $tempMin) {
                break;
            }
            if ($max == ($tempMin + 1)) {
                break;
            }
            $mid = intval(($max + $tempMin) / 2);

            if ($this->_redis->exists($key . '_' . $mid)) {
                $max = $mid;
            } else {
                $tempMin = $mid;
            }
        } while (true);
        return $tempMin;
    }

    /**
     * 检查是否超过限制
     * @method checkLimit
     * @return [type]     [description]
     */
    public function checkLimit()
    {
        do {
            $this->_handlerConfig($this->_nextBucket);
            $key = $this->_getTheKey();
            if ($key && !$this->_checkNum($key)) {
                $this->result = false;
                break;
            }
            if ($this->_now > 5) {
                //防止死循环的保护
                break;
            }
        } while (is_array($this->_nextBucket) && !empty($this->_nextBucket));
    }

    /**
     * 取消限制
     * @method removeLimit
     * @return [type]     [description]
     */
    public function removeLimit()
    {
        do {
            $this->_handlerConfig($this->_nextBucket);
            $key = $this->_getTheKey();
            if ($key) {
                $this->_redis->del($key);
                $this->_redis->del($key . '_falseDoNum');
                $this->result = true;
            }
            if ($this->_now > 5) {
                //防止死循环的保护
                break;
            }

        } while (is_array($this->_nextBucket) && !empty($this->_nextBucket));
    }

}
