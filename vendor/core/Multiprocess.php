<?php
/**
 * @Author: cclilshy
 * @Date:   2022-12-12 15:59:30
 * @Last Modified by:   cclilshy
 * @Last Modified time: 2022-12-14 17:41:03
 */

namespace core;

use core\Database\Redis;
use Exception;

// this is a multi process class that is used to create multi processes and can use redis in multi process

class Multiprocess extends Process
{
    //线程核心
    protected static $handle;

    //线程数
    protected static $multiprocess;

    //线程自身REdis
    protected static $redis;

    //自身PID
    protected static $pid;

    //PIPECLASS
    protected static $pipe;

    //自身名称
    protected static string $name;

    //互斥锁文件
    protected static $lockFile;

    //前台运行
    protected static bool $debug = true;

    //访问方法
    protected static $method;

    /**
     * 创建线程记录函数
     * @return Multiprocess
     */
    public function __construct()
    {
        parent::__construct();
        self::$redis->set(self::$name . ':count', 0);
        self::$redis->set(self::$name . ':lock', 0);
        return $this;
    }

    /**
     * 用户调用接口,直接返回本对象
     * @param callable $handle 线程的核心函数
     * @param int $multiprocess 线程数
     * @return Multiprocess 返回一个实例后的自身对象
     */
    public static function create(callable $handle, int $multiprocess): Multiprocess
    {
        self::$handle = $handle;
        self::$multiprocess = $multiprocess;
        return self::register();
    }

    /**
     * 基本配置与属性初始话
     * @return Multiprocess 返回一个实例后的自身对象
     */
    protected static function register(): Multiprocess
    {
        self::iniSet();
        self::$pid = posix_getpid();
        self::$name = str_replace('/', '_', self::getName());
        self::$lockFile = fopen(sys_get_temp_dir() . '/' . self::$name, 'w');
        self::$pipe = Pipe::register(self::$name);
        self::$method = php_sapi_name();
        self::$redis = self::getRedisConnect();
        //self::clean();
        return new self();
    }

    /**
     * php.ini基础设置
     * @return void
     */
    protected static function iniSet(): void
    {
        ini_set('max_execution_time', 0);
        ini_set('default_socket_timeout', -1);
    }

    /**
     * 获取当前执行的文件名
     * @return string 文件名
     */
    protected static function getName(): string
    {
        $debugBacktrace = debug_backtrace();
        return $debugBacktrace[count($debugBacktrace) - 1]['file'];
    }

    /**
     * 创建一个新的Redis连接
     * @return Redis
     */
    protected static function getRedisConnect(): Redis
    {
        return Redis::pconnect(Config::std('cache.redis'));
    }

    /**
     * 信号处理方法
     * @return void
     */
    protected static function signalHandler(): void
    {
        self::stop();
        die();
    }

    /**
     * 无规则杀死所有子进程并释放资源,防止延迟记录
     * @return void
     */
    protected static function stop(): void
    {
        foreach (self::getPids() as $pid) {
            posix_kill($pid, SIGKILL);
            self::removePid($pid);
        }
        self::release();
    }

    /**
     * 获取所有任务PID列表
     * @return array PID列表
     */
    protected static function getPids(): array
    {
        $redis = self::getRedisConnect();
        $pids = $redis->lrange(self::$name . ':pids', 0, -1);
        return is_array($pids) ? $pids : [];
    }

    /**
     * 移除一个PID
     * @param int $pid 要移除的pid
     * @return bool 成功与否
     */
    protected static function removePid(int $pid): bool
    {
        return self::$redis->lrem(self::$name . ':pids', $pid, 0);
    }

    /**
     * 释放资源 redis连接以及Pipe连接
     * @return void;
     */
    protected static function release(): void
    {
        self::clean();
        self::$redis->close();
    }

    /**
     * 清空Reids内容
     * @return void
     */
    protected static function clean(): void
    {
        self::$redis->del(self::$name . ':list');
        self::$redis->del(self::$name . ':count');
        self::$redis->del(self::$name . ':lock');
        self::$redis->del(self::$name . ':pids');
    }

    /**
     * 重启
     * @return void
     */
    protected static function restart(): void
    {
        self::stop();
        self::start();
    }

    /**
     * 开始执行任务
     * @return void
     */
    protected static function start(): void
    {
        if (self::$multiprocess <= 0 || self::status()) die();
        $pid = (self::$debug === true) ? 0 : pcntl_fork();
        if ($pid > 0) return;
        self::makeMultiprocessLock();
        self::makeMemoryLock();
        for ($i = 0; $i < self::$multiprocess; $i++) {
            $pid = pcntl_fork();
            if (0 === $pid) {
                //为每一个任务创建一个新的Redis连接
                self::$redis = self::getRedisConnect();
                self::pushPid(posix_getpid());
                self::awaitMemoryLock();
                $f = self::$handle;
                try {
                    $f(new self());
                } catch (Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                    self::$redis->close();
                    die();
                }
                self::$redis->close();
                return;
            }
        }

        while (self::getPids() < self::$multiprocess) ;
        self::unMemoryLock();
        self::registerSignal();
        self::runGuard();
    }

    /**
     * 通过flock获取当前运行状态
     * @return bool 运行状态
     */
    protected static function status(): bool
    {
        return !flock(self::$lockFile, LOCK_SH | LOCK_NB);
    }

    /**
     * 创建一个文件锁,用于互斥其他要运行的进程
     * @return bool 创建成功
     */
    protected static function makeMultiprocessLock(): bool
    {
        return flock(self::$lockFile, LOCK_EX);
    }

    /**
     * 加一个内存锁,堵塞
     * @return bool 加锁成功
     */
    protected static function makeMemoryLock(): bool
    {
        while (self::$redis->incr(self::$name . ':lock') !== 1) {
            self::$redis->decr(self::$name . ':lock');
        }
        return true;
    }

    /**
     * 插入一条新的PID
     * @param int $pid PID
     * @return bool 成功与否
     */
    protected static function pushPid(int $pid): bool
    {
        return self::$redis->lpush(self::$name . ':pids', $pid);
    }

    /**
     * 等待内存解锁,非线程安全,堵塞
     * @return bool 等待结束
     */
    protected static function awaitMemoryLock(): bool
    {
        while ((int)self::$redis->get(self::$name . ':lock') === 1) {
        }
        return true;
    }

    /**
     * 将内存解锁,非堵塞
     * @return bool 解锁成功
     */
    protected static function unMemoryLock(): bool
    {
        return self::$redis->decr(self::$name . ':lock');
    }

    /**
     * 注册信号处理
     * @return void
     */
    protected static function registerSignal(): void
    {

        declare(ticks=1);
        pcntl_signal(SIGTERM, [__CLASS__, 'signalHandler']);
        pcntl_signal(SIGINT, [__CLASS__, 'signalHandler']);
    }

    /**
     * 线程守护,当所有线程结束后,释放资源并解锁FLock
     * @return void
     */
    protected static function runGuard(): void
    {

        do {
            $pids = self::getPids();
            $pid = array_pop($pids);
            if ($pid && (pcntl_waitpid($pid, $status, WNOHANG) !== 0)) {
                self::removePid($pid);
            } else {
                sleep(1);
            }
        } while ($pid);
        self::release();
        self::unMultiprocessLock();
    }

    /**
     * 释放一个文件锁,用于互斥其他要运行的进程
     * @return bool 释放成功
     */
    protected static function unMultiprocessLock(): bool
    {
        return flock(self::$lockFile, LOCK_UN) && fclose(self::$lockFile);
    }

    /**
     * 通过命令行方式启动
     * @return void
     */
    protected static function commandLaunch(): void
    {
        global $argv;

        self::$debug = !(isset($argv[2]) && $argv[2] === '-d');
        $action = count($argv) < 2 ? 'start' : $argv[1];
        if ($action === 'start') {
            if (self::status()) {
                echo 'task is running!' . PHP_EOL;
            } else {
                echo 'task start success!' . PHP_EOL;
                self::start();
            }
        } elseif ($action === 'stop') {
            self::stop();
            echo 'task stop success!' . PHP_EOL;
        } elseif ($action === 'status') {
            echo self::status() ? 'task is running!' . PHP_EOL : 'task is stop!' . PHP_EOL;
        } elseif ($action === 'restart') {
            self::stop();
            if (self::status()) {
                echo 'task is running!' . PHP_EOL;
            } else {
                echo 'task start success!' . PHP_EOL;
                self::start();
            }
        }
    }

    /**
     * 获取属性
     * @param string $name 属性名称
     * @return array|null 属性内容
     */
    public function __get(string $name): ?array
    {
        switch ($name) {
            case 'pids':
                $pids = self::$redis->lrange(self::$name . ':pids', 0, -1);
                return is_array($pids) ? $pids : [];

            case 'redis':
                return self::$redis;
        }
        return null;
    }

    /**
     * 计数器
     * @return int 返回计数后的结果
     */
    public function counter(): int
    {
        return self::$redis->incrby(self::$name . ':count', 1);
    }

    /**
     * 设置任务列表
     * @param array $data 任务列表
     * @return Multiprocess 返回自身
     */
    public function tasks(array $data): Multiprocess
    {
        foreach ($data as $item) {
            $this->tpush($item);
        }
        return $this;
    }

    /**
     * 向任务列表插入数据
     * @param string $content 要插入的数据
     * @return int 当前区间数据总量
     */
    public function tpush(string $content): int
    {
        return self::$redis->lpush(self::$name . ':list', $content);
    }

    /**
     * 取列表区间
     * @param int $start 区间开始
     * @param int $end 区间末尾
     * @return array 区间数据
     */
    public function trange(int $start, int $end): array
    {
        return self::$redis->lrange(self::$name . ':list', $start, $end);
    }

    /**
     * 获取并删除一条区间数据
     * @return ?string 无数据则返回null
     */
    public function tpop(): ?string
    {
        return self::$redis->lpop(self::$name . ':list');
    }

    /**
     * @param bool $debug 是否等待运行结束
     * @return void
     */
    public function run(bool $debug = true): void
    {
        self::$debug = $debug;
        php_sapi_name() === 'cli' ? self::commandLaunch() : self::start();
    }
}
