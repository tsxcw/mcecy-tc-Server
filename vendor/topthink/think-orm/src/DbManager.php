<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use think\db\BaseQuery;
use think\db\ConnectionInterface;
use think\db\Query;
use think\db\Raw;
/* @method Query where(mixed $field, string $op = null, mixed $condition = null) static 查询条件
 * @method Query whereTime(string $field, string $op, mixed $range = null) static 查询日期和时间
 * @method Query whereBetweenTime(string $field, mixed $startTime, mixed $endTime) static 查询日期或者时间范围
 * @method Query whereBetweenTimeField(string $startField, string $endField) static 查询当前时间在两个时间字段范围
 * @method Query whereYear(string $field, string $year = 'this year') static 查询某年
 * @method Query whereMonth(string $field, string $month = 'this month') static 查询某月
 * @method Query whereDay(string $field, string $day = 'today') static 查询某日
 * @method Query whereRaw(string $where, array $bind = []) static 表达式查询
 * @method Query whereExp(string $field, string $condition, array $bind = []) static 字段表达式查询
 * @method Query when(mixed $condition, mixed $query, mixed $otherwise = null) static 条件查询
 * @method Query join(mixed $join, mixed $condition = null, string $type = 'INNER') static JOIN查询
 * @method Query view(mixed $join, mixed $field = null, mixed $on = null, string $type = 'INNER') static 视图查询
 * @method Query with(mixed $with) static 关联预载入
 * @method Query count(string $field) static Count统计查询
 * @method Query min(string $field) static Min统计查询
 * @method Query max(string $field) static Max统计查询
 * @method Query sum(string $field) static SUM统计查询
 * @method Query avg(string $field) static Avg统计查询
 * @method Query field(mixed $field, boolean $except = false) static 指定查询字段
 * @method Query fieldRaw(string $field, array $bind = []) static 指定查询字段
 * @method Query union(mixed $union, boolean $all = false) static UNION查询
 * @method Query limit(mixed $offset, integer $length = null) static 查询LIMIT
 * @method Query order(mixed $field, string $order = null) static 查询ORDER
 * @method Query orderRaw(string $field, array $bind = []) static 查询ORDER
 * @method Query cache(mixed $key = null, integer $expire = null) static 设置查询缓存
 * @method mixed value(string $field) static 获取某个字段的值
 * @method array column(string $field, string $key = '') static 获取某个列的值
 * @method Model find(mixed $data = null) static 查询单个记录 不存在返回Null
 * @method Model findOrEmpty(mixed $data = null) static 查询单个记录 不存在返回空模型
 * @method \think\model\Collection select(mixed $data = null) static 查询多个记录
 * @method Model withAttr(array $name, \Closure $closure) 动态定义获取器
 * /
/**
 * Class DbManager
 * @package think
 * @mixin BaseQuery
 * @mixin Query
 */
class DbManager
{
    /**
     * 数据库连接实例
     * @var array
     */
    protected $instance = [];

    /**
     * 数据库配置
     * @var array
     */
    protected $config = [];

    /**
     * Event对象或者数组
     * @var array|object
     */
    protected $event;

    /**
     * SQL监听
     * @var array
     */
    protected $listen = [];

    /**
     * SQL日志
     * @var array
     */
    protected $dbLog = [];

    /**
     * 查询次数
     * @var int
     */
    protected $queryTimes = 0;

    /**
     * 查询缓存对象
     * @var CacheInterface
     */
    protected $cache;

    /**
     * 查询日志对象
     * @var LoggerInterface
     */
    protected $log;

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        $this->modelMaker();
    }

    /**
     * 注入模型对象
     * @access public
     * @return void
     */
    protected function modelMaker()
    {
        Model::setDb($this);

        if (is_object($this->event)) {
            Model::setEvent($this->event);
        }

        Model::maker(function (Model $model) {
            $isAutoWriteTimestamp = $model->getAutoWriteTimestamp();

            if (is_null($isAutoWriteTimestamp)) {
                // 自动写入时间戳
                $model->isAutoWriteTimestamp($this->getConfig('auto_timestamp', true));
            }

            $dateFormat = $model->getDateFormat();

            if (is_null($dateFormat)) {
                // 设置时间戳格式
                $model->setDateFormat($this->getConfig('datetime_format', 'Y-m-d H:i:s'));
            }
        });
    }

    /**
     * 监听SQL
     * @access protected
     * @return void
     */
    public function triggerSql(): void
    {}

    /**
     * 初始化配置参数
     * @access public
     * @param array $config 连接配置
     * @return void
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    /**
     * 设置缓存对象
     * @access public
     * @param CacheInterface $cache 缓存对象
     * @return void
     */
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * 设置日志对象
     * @access public
     * @param LoggerInterface $log 日志对象
     * @return void
     */
    public function setLog(LoggerInterface $log): void
    {
        $this->log = $log;
    }

    /**
     * 记录SQL日志
     * @access protected
     * @param string $log  SQL日志信息
     * @param string $type 日志类型
     * @return void
     */
    public function log(string $log, string $type = 'sql')
    {
        if ($this->log) {
            $this->log->log($type, $log);
        } else {
            $this->dbLog[$type][] = $log;
        }
    }

    /**
     * 获得查询日志（没有设置日志对象使用）
     * @access public
     * @param bool $clear 是否清空
     * @return array
     */
    public function getDbLog(bool $clear = false): array
    {
        $logs = $this->dbLog;
        if ($clear) {
            $this->dbLog = [];
        }

        return $logs;
    }

    /**
     * 获取配置参数
     * @access public
     * @param string $name    配置参数
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = '', $default = null)
    {
        if ('' === $name) {
            return $this->config;
        }

        return $this->config[$name] ?? $default;
    }

    /**
     * 创建/切换数据库连接查询
     * @access public
     * @param string|null $name  连接配置标识
     * @param bool        $force 强制重新连接
     * @return ConnectionInterface
     */
    public function connect(string $name = null, bool $force = false)
    {
        return $this->instance($name, $force);
    }

    /**
     * 创建数据库连接实例
     * @access protected
     * @param string|null $name  连接标识
     * @param bool        $force 强制重新连接
     * @return ConnectionInterface
     */
    protected function instance(string $name = null, bool $force = false): ConnectionInterface
    {
        if (empty($name)) {
            $name = $this->getConfig('default', 'mysql');
        }

        if ($force || !isset($this->instance[$name])) {
            $this->instance[$name] = $this->createConnection($name);
        }

        return $this->instance[$name];
    }

    /**
     * 获取连接配置
     * @param string $name
     * @return array
     */
    protected function getConnectionConfig(string $name): array
    {
        $connections = $this->getConfig('connections');
        if (!isset($connections[$name])) {
            throw new InvalidArgumentException('Undefined db config:' . $name);
        }

        return $connections[$name];
    }

    /**
     * 创建连接
     * @param $name
     * @return ConnectionInterface
     */
    protected function createConnection(string $name): ConnectionInterface
    {
        $config = $this->getConnectionConfig($name);

        $type = !empty($config['type']) ? $config['type'] : 'mysql';

        if (false !== strpos($type, '\\')) {
            $class = $type;
        } else {
            $class = '\\think\\db\\connector\\' . ucfirst($type);
        }

        /** @var ConnectionInterface $connection */
        $connection = new $class($config);
        $connection->setDb($this);

        if ($this->cache) {
            $connection->setCache($this->cache);
        }

        return $connection;
    }

    /**
     * 使用表达式设置数据
     * @access public
     * @param string $value 表达式
     * @return Raw
     */
    public function raw(string $value): Raw
    {
        return new Raw($value);
    }

    /**
     * 更新查询次数
     * @access public
     * @return void
     */
    public function updateQueryTimes(): void
    {
        $this->queryTimes++;
    }

    /**
     * 重置查询次数
     * @access public
     * @return void
     */
    public function clearQueryTimes(): void
    {
        $this->queryTimes = 0;
    }

    /**
     * 获得查询次数
     * @access public
     * @return integer
     */
    public function getQueryTimes(): int
    {
        return $this->queryTimes;
    }

    /**
     * 监听SQL执行
     * @access public
     * @param callable $callback 回调方法
     * @return void
     */
    public function listen(callable $callback): void
    {
        $this->listen[] = $callback;
    }

    /**
     * 获取监听SQL执行
     * @access public
     * @return array
     */
    public function getListen(): array
    {
        return $this->listen;
    }

    /**
     * 注册回调方法
     * @access public
     * @param string   $event    事件名
     * @param callable $callback 回调方法
     * @return void
     */
    public function event(string $event, callable $callback): void
    {
        $this->event[$event][] = $callback;
    }

    /**
     * 触发事件
     * @access public
     * @param string $event  事件名
     * @param mixed  $params 传入参数
     * @return mixed
     */
    public function trigger(string $event, $params = null)
    {
        if (isset($this->event[$event])) {
            foreach ($this->event[$event] as $callback) {
                call_user_func_array($callback, [$this]);
            }
        }
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->connect(), $method], $args);
    }
}
