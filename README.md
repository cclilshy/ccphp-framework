# 文档

## 安装

### Linux

```bash
git clone https://github.com/cclilshy/ccphp-framework.git && cd ccphp-framework && ./master run
```

### Windows -不建议

```bash
git clone https://github.com/cclilshy/ccphp-framework.git && cd ccphp-framework && php master run
```

## 简介

> 原生PHP-MVC框架

* 常驻/非常驻内存区分
* 前后端分离
* 模块化开发
* 终端/HTTP端分离,可单独使用
* 多进程全局通信/管控

## 文档(未完善)

### 目录结构

- application 	    #应用根目录
	- http		    #网站
	- console 		#终端
	- route 		#路由
- cache 			#临时文件目录
- extend 			#外部插件目录
- model 			#模型目录
- resource 			#资源目录
    - config 		#配置文件
    - logs 			#日志文件
- vendor 			#框架核心

### HTTP入口路径

> application/http/public

### 加载器

> 所有扩展和服务必须从加载器加载(兼容内存常驻,服务,日志等)
>
> APP类保留 `init`,`load` 方法,
>
> 初次加载时会执行init方法,该方法内的数据内存常驻
>
> load方法在每次访问时执行, 重置初始化参数

```php
// 加载\core\App类下 `init` 方法, 并返回 `init` 的返回
\core\Master::rouse('App');

// 加载多个组件, 并返回第一个组件的返回
\core\Master::rouse('App,Database,Cache');
```

### 路由

> 所有路由规则写在 application/route/ 中

```php
<?php
use core\Route;
// @ params
// - string 路径
// - string/callback 控制器@方法名 / 函数体
// - string 附加参数名(与参数1的冒号取值对应)
// 支持 'get', 'post', 'put', 'patch', 'delete', 'options', 'any', 'console'

Route::get('hello/:name','/http/controller/Hello@index','name');
Route::get('hello/:name',function($name){ echo "$name"; },'name');

//Class Hello
public function index($name){
    echo "hello,$name";
}
```

### HTTP应用

```php
# application/http/
# controller #控制器目录
# template #模板文件目录
# public #根目录
\core\Input::get('id'); //获取GET请求中的 id 项目
\core\Input::post('content'); //获取POST中的 content 项
```

### 终端应用

#### 路由规则

```php
<?php
use core\Route;
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

### 模板

```php
\core\View::define('arr',array());
\core\View::define('name','cclilshy');
\core\View::define('descrite',$describe);
return \core\View::template();
```

```html
<!-- 原生语句 -->
@php include __DIR__ . FS . 'header.html'; @endphp

<!-- 判断输出 -->
@if(\model\Member::isLogin())

<!-- 变量输出 -->
<p>name : {{$name}}</p>

<!-- 函数输出 -->
<p>{{ substr($describe,0,100) }}</p>

<!-- 循环输出,支持for/while/foreach -->
@foreach($arr as $key => $value)
<p>{{$key}} : {{$value}}</p>
@endforeach
@endif

<!-- 兼容Vue的写法 -->
这段文本不会被解析: @{{ message }}

<!-- 模板包含 从TEMPLATE_PATH 往下的 index/common 模板文件-->
@embed index/common @endembed
```

### 数据库

> 考虑暂时使用第三方类库

```php
\core\DB::table('user')->where('id',1)->first();
```

### 缓存

```php
//支持Redis所有命令,如
\core\Cache::set('count',8);
\core\Cache::get('count'); 
```

### 后台应用(仅linux)

```php
$func = function($thread){
    //Multiprocess 支持多应用技术,共享数据池异步存取,详情查看 Multiprocess 类
    while(rand(1,100)<50){
        sleep(1);
    }
};
//进程数
$count = 10;
//后台运行
$debug = false;

\core\Multiprocess::create($func,10)->run($debug);
```

## 日志

```php
\core\Log::record($msg); //记录一段日志
```

## 管道

```php
$pipe = \core\Pipe::register('name');
//非堵塞锁
$wait = false;           // 是否堵塞
$pipe->lock($wait);      // 多个进程之间,对同一个名称管道的调用,只有一个进程能上锁成功
$pipe->unlock();

# 文件读写, 非线程安全, 可以配合锁使用
//尾部追加数据
$pipe->write(posix_getpid());
//读取管道数据
$pipe->read();
```

## 配置

```php
<?php
use core\Config;

//获取 database.php 配置项下的type项
Config::get('database.type');

//获取 database.php 配置项下mysql下的host
Config::get('database.mysql.host');

//设置一个配置项,当前请求的生命周期内有效
Config::set('cid',1);
```

## 服务

```php
// 创建一个服务,返回false或服务类,不提供$name则按照文件命名, 一个服务仅允许创建一次,除非释放
$server = \core\Server::create(string $name = ''); 

// 加载现有服务信息,不提供$name则按照文件命名, 一个服务允许多个入口加载查看信息,重载
$server = \core\Server::create(string $name = ''); 

// 释放该服务信息
$server->release(); 

//设定特定数据,$name为空则返回设定的数据
$server->info($name); 
```

## 进程管理

### 进程控制

>以下控制方法依赖树服务

```php
// 启动树服务
\core\Process\Tree::launch();
// 连接树
\core\Process\Process::init();

// 返回一个PID,创建失败则返回 -1
$pid = \core\Process\Process::fork(function(){
    echo 'hhh';
});

// 销毁一进程
\core\Process\Process::kill($pid);

// 销毁一棵树下的所有进程
\core\Process\Process::killAll($pid);

// 发送信号, 如果用户hook了信号的处理, 请在确定进程结束时主动释放
\core\Process\Process::signal($pid,$signo);

// 如果用户hook了信号的处理, 请在确定进程结束时主动释放, 否则无需手动控制
\core\Process\Process::notice($pid,'exit');
```

### 进程间通讯

```php
// 创建一个监视者,并把当前类实例反射到 IPC->object 中
$ipc = \core\Process\IPC::create(function($ipc,$info){
    echo $info['name'] . PHP_EOL;
},new self);

// 获取IPC名称
$name = $ipc->name;

// 在任何地方连接IPC
$ipc = \core\Process\IPC::link($name);

// 调用监听者
$ipc->call(['name'=>'xiaoming']);
```
