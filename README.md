# 文档

## 安装

> 环境要求`PHP 8.2+`
> 正在.......
> 数据库连接池虽然保证了数据库的稳定性但代价就是堵塞用户请求,正在完成队列调度中
> Fast还没写,但暂时还没必要

### Linux

```bash
git clone https://github.com/cclilshy/ccphp-framework.git
```

```bash
./master server start # 启动服务(包括数据库连接池和进程管理服务)
```

### Windows(不支持)

## 简介

> 原生PHP-MVC框架

* 常驻/非常驻内存区分
* 数据库连接池
* 跨进程访问函数
* 全局进程调度
* 前后端分离
* 模块化开发
* 终端/HTTP端分离,可单独使用
* 多进程全局通信/管控

## 文档

### 目录结构

* application 应用根目录
    * http 网站
    * console 终端
    * route 路由
* cache 临时文件目录
* extend 外部插件目录
* model 模型目录
* resource 资源目录
    * config 配置文件
    * logs 日志文件
* vendor 框架核心

## 框架核心类空间

```php
namespace core;
```

### HTTP入口文件路径

> application/http/public

### 加载器 Master

> 所有扩展和服务必须从加载器加载(兼容内存常驻)
> APP类保留 `initialization`,~~`reload`~~ 方法
> 初次加载时会执行init方法,该方法内的数据内存常驻(如数据库配置,日志流文件,路由等)
> load方法在每次访问时会在用户自己的内存块执行, 重置初始化参数

```php
// 加载\core\App类下 `init` 方法, 并返回 `init` 的返回
\core\Master::rouse('App');

// 加载多个组件, 并返回第一个组件的 `init` 返回
\core\Master::rouse('App,Database,Cache');
```

## 管道

### 管道安全

```php
$pipe = \core\File\Pipe::create('name'); // 创建管道空间
$pipe = \core\File\Pipe::link('name'); // 连接管道
$spipe = $pipe->clone(); // 克隆一个管道(不共用流和指针,因此锁互斥,可以给子进程使用)
$wait = false;           // 是否堵塞
$pipe->lock($wait);      // 多个进程之间,对同一个名称管道的调用,只有一个进程能上锁成功
$pipe->unlock();        // 解锁
$pipe->close();         // 关闭管道
$pipe->release();       // 请确保该管道空间没人使用了
```

### 数据处理

```php
// 取区间文本
$pipe->section(0,0); //(开始,结束),第二个参数为空则自动追加到流末尾

// 读管道信息
$pipe->read();

// 尾部追加数据
$pipe->insert('hello');

// 指定位置开始覆写数据,如指定位置为0则清空文本
$pipe->write('test',1);

$pipe->eof; // 指针末尾,-1为空文本
$pipe->point; // 指针位置
```

## 配置

```php
use \core\Config;

//获取 database.php 配置项下的type项
Config::get('database.type');

//获取 database.php 配置项下mysql下的host
Config::get('database.mysql.host');

//设置一个配置项,当前请求的生命周期内有效
Config::set('cid',1);
```

## 服务

```php
// 创建一个服务,返回false或服务类,不提供$name则按照文件命名, 一个服务仅允许创建一次,除非主动释放
$server = \core\Server::create(string $name = ''); 

// 加载现有服务信息,不提供$name则按照文件命名, 一个服务允许多个入口加载查看信息,重载
$server = \core\Server::create(string $name = ''); 

// 释放该服务信息(不意味着释放了服务,只是移除储存信息)
$server->release(); 

//设定特定数据,$name为空则返回设定的数据
$server->info($name); 
```

## 进程管理

### 进程控制

> 以下控制方法依赖树服务

```php
use \core\Process\Tree;
use \core\Process\Process;

// 启动树服务,只需要启动一次
Tree::launch();
Tree::stop(); //关闭树服务

// 连接树
Process::init();

// 返回一个PID,创建失败则返回 -1
Process::fork(function(){
    echo posix_getpid() . PHP_EOL;
});

// 在任何地方和进程里销毁一进程
Process::kill($pid);

// 销毁一棵树下的所有进程
Process::killAll($pid); //提供父ID或组名称

// 发送信号, 如果用户hook了信号的处理请在确定进程结束时主动树服务
Process::signal($pid,$signo); 

// 非堵塞模式,call回收,守护者在子程序运行结束后会自动回收
Process::guard();

// 如果子进程hook了信号的处理, 请在确定进程结束时主动通知树服务, 否则无需手动控制
self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
```

### 进程间通讯

```php
use \core\Process\IPC;
// 创建一个Observer
// 支持更多的创建方法
// 参数2: 把当前类(或自定义数据)实例反射到的 space 属性中,数据生命周期为该IPC的生命周期而非匿名周期
// 参数3: 自定义IPC名称

$ipc = IPC::create(
    // call调用者参数多追加一个参数(可选),可以接ipc对象自身,以访问对象和space
    function($info,$c,$d,$e,IPC $ipc){
        echo $info['name'] . PHP_EOL;
    }
);

// 获取IPC名称
$name = $ipc->name;

// 在任何地方和进程里连接IPC
$link = IPC::link($name);

// 调用监听者
$link->call($a,$b,$c,$d,$e);
```

## 路由

> 所有路由规则写在 application/route/ 中

```php
// 支持HTTP方法 'get', 'post', 'put', 'patch', 'delete', 'options', 'any', 'console'
/**
 * @ params
 * @ string 路径
 * @ string/callback 控制器@方法名 / 函数体
 * @ string 附加参数名(与参数1的冒号取值对应)
 */
use core\Route\Route;
```

## 终端应用

### 路由规则

```php
Route::console('handle','console/Handle');
```

#### 对应命令

> php master handle

#### 对应类

```php
// class Handle
class Handle
{   
    //应用说明(help解释)
    public static function register()
    {
        return 'You can use ccphp happily';
    }

    //入口
    public function main($argv,$console)
    {
        $console::printn("hello,world");
    }
}
```

## 数据库

> 还没写,暂时使用`Illuminate`数据库引擎(同Laravel数据库引擎)替代, [文档](https://github.com/illuminate/database)

```php
use core\Database\DatabasePool;use core\Database\DB;use core\Database\Pool;

DB::name('user')->where('id',1)->first(); // 查询
DB::getConnect($config); // 获取一个新连接,config留空则使用默认配置

// 数据库连接池,目前只能队列请求,
// 错位调度开发中(后续考虑在http入口自动连接池子而不是直接连接数据库)
Pool::launch(); //启动连接池服务
Pool::stop(); //关闭连接池
// 例子
$con = Pool::link();

// 该方法和直接调用的效果一致,但返回的是反序化之后的内容,而不是查询事件的主体也就是说不可操作对象或数组
// 支持LevelORM所有语法
$con->table('user')->where('id',1)->first()->go();
```

## 缓存

> 还没开始写,暂时用Redis替代,依赖PHPRedis

```php
//支持Redis所有命令,如:
\core\Cache\Cache::set('count',1);
\core\Cache\Cache::get('count'); 
```

## 日志

```php
\core\Log::record($msg); //记录一段日志
```

## HTTP应用
>以下内容看看就行,因为还没写完，不太稳定
```php
# application/http/
# controller #控制器目录
# template #模板文件目录
# public #根目录
$request->get['id'];
$request->post['keywords'];
```

### 模板

```php
\core\Http\View::define('arr',array());
\core\Http\View::define('name','cclilshy');
\core\Http\View::define('descrite',$describe);
return \core\Http\View::template();
```

```html
<!-- 原生语句 -->
@php include __DIR__ . FS . 'header.html'; @endphp

<!-- 判断输出 -->
@if(\model\Member::isLogin())

<!-- 变量输出 -->
<p>name : {{$name}}</p>

<!-- 函数输出 -->
<p>{{ substr($describe,0,100); }}</p>

<!-- 循环输出,支持for/while/foreach -->
@foreach($arr as $key => $value)
<p>{{$key}} : {{$value}}</p>
@endforeach

<!--  判断尾  -->
@endif

<!-- 兼容Vue的写法 -->
这段文本不会被解析: @{{ message }}

<!-- 模板包含以`TEMPLATE_PATH为`根向下索引的模板文件-->
@embed("index/common") @endembed
```