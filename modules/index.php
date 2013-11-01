<?php

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @since		Version 1.0
 * @createdtime       {createdtime}
 */
define('IN_WONIU_APP', TRUE);
define('WDS', DIRECTORY_SEPARATOR);
//------------------------system config----------------------------
$system['application_folder'] = '../app';
$system['controller_folder'] = $system['application_folder'] . WDS . 'controllers';
$system['model_folder'] = $system['application_folder'] . WDS . 'models';
$system['view_folder'] = $system['application_folder'] . WDS . 'views';
$system['library_folder'] = $system['application_folder'] . WDS . 'library';
$system['helper_folder'] = $system['application_folder'] . WDS . 'helper';
$system['error_page_404'] = '../app/error/error_404.php';
$system['error_page_50x'] = '../app/error/error_50x.php';
$system['error_page_db'] = '../app/error/error_db.php';
$system['default_controller'] = 'welcome';
$system['default_controller_method'] = 'index';
$system['controller_method_prefix'] = 'do';
$system['controller_file_subfix'] = '.php';
$system['model_file_subfix'] = '.model.php';
$system['view_file_subfix'] = '.view.php';
$system['library_file_subfix'] = '.class.php';
$system['helper_file_subfix'] = '.php';
$system['helper_file_autoload'] = array(); //array($item);  $item:such as html etc.
$system['library_file_autoload'] = array(); //array($item); $item:such as ImageTool or array('ImageTool'=>'image') etc.
$system['models_file_autoload'] = array(); //array($item); $item:such as UserModel or array('UserModel'=>'user') etc.
$system['controller_method_ucfirst'] = TRUE;
$system['autoload_db'] = FALSE;
$system['debug'] = TRUE;
$system['default_timezone'] = 'PRC';
$system['route'] = array(
    "/^welcome\\/?(.*)$/u" => 'welcome.ajax/$1'
);
/**
 * ========================缓存配置========================
 */
$system['cache_drivers'] = array();
$system['cache_config'] = array(
    /*
     * 默认存储方式
     * 可用的方式有：auto,apc,sqlite,files,memcached,redis,wincache,xcache,memcache
     * auto自动模式寻找的顺序是 : apc,sqlite,files,memcached,redis,wincache,xcache,memcache
     */
    "storage" => "example",
    /*
     * 默认缓存文件存储的路径
     * 使用绝对全路径，比如： /home/username/cache
     * 留空，系统自己选择
     */
    "path" => "", // 缓存文件存储默认路径
    "securityKey" => "", // 缓存安全key，建议留空，系统会自动处理 PATH/securityKey

    /*
     * 第二驱动
     * 比如：当你现在在代码中使用的是memcached, apc等等，然后你的代码转移到了一个新的服务器而且不支持memcached 或 apc
     * 这时候怎么办呢？设置第二驱动即可，当你设置的驱动不支持的时候，系统就使用第二驱动。
     * $key是你设置的驱动，当设置的“storage”=$key不可用时，就使用$key对应的$value驱动
     */
    "fallback" => array(
        "memcache" => "files",
        "memcached" => "files",
        "redis" => "files",
        "wincache" => "files",
        "xcache" => "files",
        "apc" => "files",
        "sqlite" => "files",
    ),
    /*
     * .htaccess 保护
     * true会自动在缓存文件夹里面建立.htaccess文件防止web非法访问
     */
    "htaccess" => false,
    /*
     * Memcache服务器地址;
     */
    "server" => array(
        array("192.168.199.25", 11211, 1),
    //  array("new.host.ip",11211,1),
    ),
    /*
     * Redis服务器地址;
     */
    "redis" => array(
        'type'=>'tcp',//sock,tcp;连接类型，tcp：使用host port连接，sock：本地sock文件连接
        'prefix'=>$_SERVER['HTTP_HOST'],//key的前缀，便于管理查看，在set和get的时候会自动加上和去除前缀，无前缀请保持null
        'sock'=>'',//sock的完整路径
        'host' => '192.168.199.25',
        'port' => 6379,
        'password' => NULL,//密码，如果没有,保持null
        'timeout'=>0,//0意味着没有超时限制，单位秒
        'retry'=>100,//连接失败后的重试时间间隔，单位毫秒
        'db' => 0, // 数据库序号，默认0, 参考 http://redis.io/commands/select
    ),
);
/**
 * =======================SESSION管理配置=======================
 */
$system['session_handle'] = array(
    'handle' => 'redis', //支持的管理类型：mongodb,mysql,memcache,redis
    'common' => array(
        'autostart' => true,//是否自动session_start()
        'cookie_path' => '/',
        'cookie_domain' => '.' . $_SERVER['HTTP_HOST'],
        'session_name' => 'PHPSESSID',
        'lifetime' => 3600, // session lifetime in seconds
    ),
    'mongodb' => array(
        'host' => '192.168.199.25',
        'port' => 27017,
        'user' => 'root',
        'password' => 'local',
        'database' => 'local', // name of MongoDB database
        'collection' => 'session', // name of MongoDB collection
        // persistent related vars
        'persistent' => false, // persistent connection to DB?
        'persistentId' => 'MongoSession', // name of persistent connection
        // whether we're supporting replicaSet
        'replicaSet' => false,
    ),
    /**
     * mysql表结构
     *   CREATE TABLE `session_handler_table` (
      `id` varchar(255) NOT NULL,
      `data` mediumtext NOT NULL,
      `timestamp` int(255) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `id` (`id`,`timestamp`),
      KEY `timestamp` (`timestamp`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     */
    'mysql' => array(
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => 'admin',
        'database' => 'test',
        'table' => 'session_handler_table',
    ),
    /**
     * memcache采用的是session.save_handler管理机制
     * 需要php安装memcache拓展支持
     */
    'memcache' => "tcp://192.168.199.25:11211",
    /**
     * redis采用的是session.save_handler管理机制
     * 需要php安装redis拓展支持,你可以在https://github.com/nicolasff/phpredis 找到该拓展。
     */
    'redis' => "tcp://192.168.199.25:6379",
);
//-----------------------end system config--------------------------
//------------------------database config----------------------------
$woniu_db['active_group'] = 'default';

$woniu_db['default']['dbdriver'] = "mysql"; #可用的有mysql,mysqli,pdo,sqlite3,配置见下面
$woniu_db['default']['hostname'] = 'localhost';
$woniu_db['default']['port'] = '3306';
$woniu_db['default']['username'] = 'root';
$woniu_db['default']['password'] = 'admin';
$woniu_db['default']['database'] = 'test';
$woniu_db['default']['dbprefix'] = '';
$woniu_db['default']['pconnect'] = TRUE;
$woniu_db['default']['db_debug'] = TRUE;
$woniu_db['default']['char_set'] = 'utf8';
$woniu_db['default']['dbcollat'] = 'utf8_general_ci';
$woniu_db['default']['swap_pre'] = '';
$woniu_db['default']['autoinit'] = TRUE;
$woniu_db['default']['stricton'] = FALSE;


/*
 * PDO database config demo
 * 1.pdo sqlite3
  $woniu_db['default']['dbdriver'] = "sqlite3";
  $woniu_db['default']['database'] = 'sqlite:d:/wwwroot/sdb.db';
  $woniu_db['default']['dbprefix'] = '';
  $woniu_db['default']['db_debug'] = TRUE;
  $woniu_db['default']['char_set'] = 'utf8';
  $woniu_db['default']['dbcollat'] = 'utf8_general_ci';
  $woniu_db['default']['swap_pre'] = '';
  $woniu_db['default']['autoinit'] = TRUE;
  $woniu_db['default']['stricton'] = FALSE;
 * 2.pdo mysql:
  $woniu_db['default']['dbdriver'] = "pdo";
  $woniu_db['default']['hostname'] = 'mysql:host=localhost;port=3306';
  $woniu_db['default']['username'] = 'root';
  $woniu_db['default']['password'] = 'admin';
  $woniu_db['default']['database'] = 'test';
  $woniu_db['default']['dbprefix'] = '';
  $woniu_db['default']['char_set'] = 'utf8';
  $woniu_db['default']['dbcollat'] = 'utf8_general_ci';
  $woniu_db['default']['swap_pre'] = '';
  $woniu_db['default']['autoinit'] = TRUE;
  $woniu_db['default']['stricton'] = FALSE;
 */
//-------------------------end database config--------------------------

include('WoniuInput.class.php');
include('WoniuHelper.php');
include('db-drivers/db.drivers.php');
include('db-drivers/mysql.driver.php');
include('db-drivers/pdo.driver.php');
include('db-drivers/sqlite3.driver.php');
include('cache-drivers/phpfastcache.php');
include('cache-drivers/driver.php');
include('cache-drivers/drivers/apc.php');
include('cache-drivers/drivers/files.php');
include('cache-drivers/drivers/memcache.php');
include('cache-drivers/drivers/memcached.php');
include('cache-drivers/drivers/sqlite.php');
include('cache-drivers/drivers/wincache.php');
include('cache-drivers/drivers/xcache.php');
include('cache-drivers/drivers/redis.php');
include('session_drivers/WoniuSessionHandle.php');
include('session_drivers/MysqlSessionHandle.php');
include('session_drivers/MongodbSessionHandle.php');
include('session_drivers/MemcacheSessionHandle.php');
include('session_drivers/RedisSessionHandle.php');
include('WoniuRouter.php');
include('WoniuLoader.php');
include('WoniuController.php');
include('WoniuModel.php');
WoniuRouter::loadClass();

/* End of file index.php */