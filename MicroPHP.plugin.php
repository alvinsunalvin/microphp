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
 * @since		Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
define('IN_WONIU_APP', TRUE);
define('WDS', DIRECTORY_SEPARATOR);
//------------------------system config----------------------------
$system['application_folder'] = 'app';
$system['controller_folder'] = $system['application_folder'] . WDS . 'controllers';
$system['model_folder'] = $system['application_folder'] . WDS . 'models';
$system['view_folder'] = $system['application_folder'] . WDS . 'views';
$system['library_folder'] = $system['application_folder'] . WDS . 'library';
$system['helper_folder'] = $system['application_folder'] . WDS . 'helper';
$system['error_page_404'] = 'app/error/error_404.php';
$system['error_page_50x'] = 'app/error/error_50x.php';
$system['error_page_db'] = 'app/error/error_db.php';
$system['default_controller'] = 'home.welcome';
$system['default_controller_method'] = 'index';
$system['controller_method_prefix'] = 'do';
$system['controller_file_subfix'] = '.php';
$system['model_file_subfix'] = '.model.php';
$system['view_file_subfix'] = '.view.php';
$system['library_file_subfix'] = '.class.php';
$system['helper_file_subfix'] = '.php';
$system['helper_file_autoload'] = array(); //array($item);  $item:such as html etc.
$system['library_file_autoload'] = array(); //array($item); $item:such as ImageTool or array('ImageTool'=>'image') etc.
$system['models_file_autoload']=array(array('User2'=>'c'));//array($item); $item:such as UserModel or array('UserModel'=>'user') etc.
$system['cache_dirname'] = 'cache';
$system['controller_method_ucfirst'] = TRUE;
$system['autoload_db'] = FALSE;
$system['debug'] = TRUE;
$system['default_timezone'] = 'PRC';

//-----------------------end system config--------------------------
//------------------------database config----------------------------
$woniu_db['active_group'] = 'default';

$woniu_db['default']['dbdriver'] = "mysql"; #可用的有mysql,pdo,sqlite3,配置见下面
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













/* End of file index.php */

//####################modules/WoniuRouter.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
class WoniuRouter {

    public static function loadClass() {
        global $system;
        $methodInfo = self::parseURI();
        //在解析路由之后，就注册自动加载，这样控制器可以继承类库文件夹里面的自定义父控制器,实现hook功能，达到拓展控制器的功能
        //但是plugin模式下，路由器不再使用，那么这里就不会被执行，自动加载功能会失效，所以在每个instance方法里面再尝试加载一次即可，
        //如此一来就能满足两种模式
        WoniuLoader::classAutoloadRegister();
//        var_dump($methodInfo);
        if (file_exists($methodInfo['file'])) {
            include $methodInfo['file'];
            $class = new $methodInfo['class']();
            if (method_exists($class, $methodInfo['method'])) {
                $methodInfo['parameters'] = is_array($methodInfo['parameters']) ? $methodInfo['parameters'] : array();
                if (method_exists($class, '__output')) {
                    ob_start();
                    call_user_func_array(array($class, $methodInfo['method']), $methodInfo['parameters']);
                    $buffer = ob_get_contents();
                    @ob_end_clean();
                    call_user_func_array(array($class, '__output'), array($buffer));
                } else {
                    call_user_func_array(array($class, $methodInfo['method']), $methodInfo['parameters']);
                }
            } else {
                trigger404($methodInfo['class'] . ':' . $methodInfo['method'] . ' not found.');
            }
        } else {
            if ($system['debug']) {
                trigger404('file:' . $methodInfo['file'] . ' not found.');
            } else {
                trigger404();
            }
        }
    }

    private static function parseURI() {
        global $system;
        $pathinfo = @parse_url($_SERVER['REQUEST_URI']);
        if (empty($pathinfo)) {
            if ($system['debug']) {
                trigger404('request parse error:' . $_SERVER['REQUEST_URI']);
            } else {
                trigger404();
            }
        }
        //优先以查询模式获取查询字符串，然后尝试获取pathinfo模式的查询字符串
        $pathinfo_query = !empty($pathinfo['query']) ? $pathinfo['query'] : (!empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
        $class_method = $system['default_controller'] . '.' . $system['default_controller_method'];
        //看看是否要处理查询字符串
        if (!empty($pathinfo_query)) {
            //查询字符串去除头部的/
            $pathinfo_query{0} === '/' ? $pathinfo_query = substr($pathinfo_query, 1) : null;
            $requests = explode("/", $pathinfo_query);
            //看看是否指定了类和方法名
            preg_match('/[^&]+(?:\.[^&]+)+/', $requests[0]) ? $class_method = $requests[0] : null;
            if(strstr($class_method, '&')!==false){
                $cm=  explode('&', $class_method);
                $class_method=$cm[0];
            }
        }
        //去掉查询字符串中的类方法部分，只留下参数
        $pathinfo_query = str_replace($class_method, '', $pathinfo_query);
        $pathinfo_query_parameters = explode("&", $pathinfo_query);
        $pathinfo_query_parameters_str = !empty($pathinfo_query_parameters[0]) ? $pathinfo_query_parameters[0] : '';
        //去掉参数开头的/，只留下参数
        $pathinfo_query_parameters_str && $pathinfo_query_parameters_str{0} === '/' ? $pathinfo_query_parameters_str = substr($pathinfo_query_parameters_str, 1) : '';

        //现在已经解析出了，$class_method类方法名称字符串(main.index），$pathinfo_query_parameters_str参数字符串(1/2)，进一步解析为真实路径
        $class_method = explode(".", $class_method);
        $method = end($class_method);
        $method = $system['controller_method_prefix'] . ($system['controller_method_ucfirst'] ? ucfirst($method) : $method);

        unset($class_method[count($class_method) - 1]);

        $file = $system['controller_folder'] . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $class_method) . $system['controller_file_subfix'];
        $class = $class_method[count($class_method) - 1];
        $parameters = explode("/", $pathinfo_query_parameters_str);
        //对参数进行urldecode解码一下
        foreach ($parameters as $key => $value) {
            $parameters[$key] = urldecode($value);
        }
        return array('file' => $file, 'class' => ucfirst($class), 'method' => str_replace('.', '/', $method), 'parameters' => $parameters);
    }

}

/* End of file Router.php */
//####################modules/WoniuLoader.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
class WoniuLoader {

    public $db, $input;
    private $helper_files = array();
    public $model, $lib;
    private $view_vars = array();
    private static $instance;

    public function __construct() {
        date_default_timezone_set($this->config('system', 'default_timezone'));
        $this->registerErrorHandle();
        $this->input = new WoniuInput();
        $this->model = new WoniuModelLoader();
        $this->lib = new WoniuLibLoader();
        WoniuCache::$path = $this->config('system', 'cache_dirname');
        if ($this->config('system', "autoload_db")) {
            $this->database();
        }
        stripslashes_all();
        $this->autoload();
    }

    public function registerErrorHandle() {
        if (!$this->config('system', 'debug')) {
            error_reporting(0);
            set_exception_handler('woniuException');
            register_shutdown_function('fatal_handler');
        } else {
            error_reporting(E_ALL);
        }
    }

    private function autoload() {
        $autoload_helper = $this->config('system', 'helper_file_autoload');
        $autoload_library = $this->config('system', 'library_file_autoload');
        $autoload_models = $this->config('system', 'models_file_autoload');
        foreach ($autoload_helper as $file_name) {
            $this->helper($file_name);
        }
        foreach ($autoload_library as $key => $val) {
            if (is_array($val)) {
                $key = key($val);
                $val = $val[$key];
                $this->lib($key, $val);
            } else {
                $this->lib($val);
            }
        }
        foreach ($autoload_models as $key => $val) {
            if (is_array($val)) {
                $key = key($val);
                $val = $val[$key];
                $this->model($key, $val);
            } else {
                $this->model($val);
            }
        }
    }

    public function config($config_group, $key = '') {
        global $$config_group;
        if ($key) {
            $config_group = $$config_group;
            return isset($config_group[$key]) ? $config_group[$key] : null;
        } else {
            return isset($$config_group) ? $$config_group : null;
        }
    }

    public function database($config = NULL, $is_return = false) {
        if ($is_return) {
            $db = null;
            //没有传递配置，使用默认配置
            if (!is_array($config)) {
                global $woniu_db;
                $db = WoniuDB::getInstance($woniu_db[$woniu_db['active_group']]);
            } else {
                $db = WoniuDB::getInstance($config);
            }
            return $db;
        } else {
            //没有传递配置，使用默认配置
            if (!is_array($config)) {
                if (!is_object($this->db)) {
                    global $woniu_db;
                    $this->db = WoniuDB::getInstance($woniu_db[$woniu_db['active_group']]);
                }
            } else {
                $this->db = WoniuDB::getInstance($config);
            }
        }
    }

    public function helper($file_name) {
        global $system;
        $filename = $system['helper_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['helper_file_subfix'];
        if (in_array($filename, $this->helper_files)) {
            return;
        }
        if (file_exists($filename)) {
            $this->helper_files[] = $filename;
            //包含文件，并把文件里面的变量变为全局变量
            $before_vars = array_keys(get_defined_vars());
            include $filename;
            $vars = get_defined_vars();
            $all_vars = array_keys($vars);
            foreach ($all_vars as $key) {
                if (!in_array($key, $before_vars) && isset($vars[$key])) {
                    $GLOBALS[$key] = $vars[$key];
                }
            }
        } else {
            trigger404($filename . ' not found.');
        }
    }

    public function lib($file_name, $alias_name = null) {
        global $system;
        $classname = $file_name;
        if (strstr($file_name, '/') !== false || strstr($file_name, "\\") !== false) {
            $classname = basename($file_name);
        }
        if (!$alias_name) {
            $alias_name = strtolower($classname);
        }
        $filepath = $system['library_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['library_file_subfix'];

        if (in_array($alias_name, array_keys(WoniuLibLoader::$lib_files))) {
            return WoniuLibLoader::$lib_files[$alias_name];
        } else {
            foreach (WoniuLibLoader::$lib_files as $aname => $obj) {
                if (strtolower(get_class($obj)) === strtolower($classname)) {
                    return WoniuLibLoader::$lib_files[$aname];
                }
            }
        }
        if (file_exists($filepath)) {
            include $filepath;
            if (class_exists($classname)) {
                return WoniuLibLoader::$lib_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

    public function model($file_name, $alias_name = null) {
        global $system;
        $classname = $file_name;
        if (strstr($file_name, '/') !== false || strstr($file_name, "\\") !== false) {
            $classname = basename($file_name);
        }
        if (!$alias_name) {
            $alias_name = strtolower($classname);
        }
        $filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $file_name . $system['model_file_subfix'];
        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        } else {
            foreach (WoniuModelLoader::$model_files as &$obj) {
                if (strtolower(get_class($obj)) == strtolower($classname)) {
                    return WoniuModelLoader::$model_files[$alias_name] = $obj;
                }
            }
        }$this->printTrace();
        if (file_exists($filepath)) {
            var_dump(class_exists($classname));
            include $filepath;
            var_dump(class_exists($classname));
            if (class_exists($classname)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
        
    }
    public function printTrace(){
       debug_print_backtrace();
    }

    public function view($view_name, $data = null, $return = false) {
        if (is_array($data)) {
            $this->view_vars = array_merge($this->view_vars, $data);
            extract($this->view_vars);
        }
        global $system;
        $view_path = $system['view_folder'] . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        if (file_exists($view_path)) {
            if ($return) {
                @ob_end_clean();
                ob_start();
                include $view_path;
                $html = ob_get_contents();
                @ob_end_clean();
                return $html;
            } else {
                include $view_path;
            }
        } else {
            trigger404('View:' . $view_path . ' not found');
        }
    }

    public static function classAutoloadRegister() {
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        $found = false;
        $__autoload_found = false;
        $auto_functions = spl_autoload_functions();
        if (is_array($auto_functions)) {
            foreach ($auto_functions as $func) {
                if (is_array($func) && $func[0] == 'WoniuLoader' && $func[1] == 'classAutoloader') {
                    $found = TRUE;
                    break;
                }
            }
            foreach ($auto_functions as $func) {
                if (!is_array($func) && $func == '__autoload') {
                    $__autoload_found = TRUE;
                    break;
                }
            }
        }
        if (function_exists('__autoload') && !$__autoload_found) {
            //如果存在__autoload而且没有被注册过,就显示的注册它，不然它会因为spl_autoload_register的调用而失效
            spl_autoload_register('__autoload');
        }
        if (!$found) {
            //最后注册我们的自动加载器
            spl_autoload_register(array('WoniuLoader', 'classAutoloader'));
        }
    }

    public static function classAutoloader($clazzName) {
        global $system;
        $library = $system['library_folder'] . DIRECTORY_SEPARATOR . $clazzName . $system['library_file_subfix'];
        if (file_exists($library)) {
            include($library);
        }
    }

    public static function instance() {
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        self::classAutoloadRegister();
        return empty(self::$instance) ? self::$instance = new self() : self::$instance;
    }

    public function view_path($view_name) {
        global $system;
        $view_path = $system['view_folder'] . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        return $view_path;
    }

    public function ajax_echo($code, $tip = '', $data = '', $is_exit = true) {
        $str = json_encode(array('code' => $code, 'tip' => $tip ? $tip : '', 'data' => empty($data) ? '' : $data));
        echo $str;
        if ($is_exit) {
            exit();
        }
    }

    public function xml_echo($xml, $is_exit = true) {
        header('Content-type:text/xml;charset=utf-8');
        echo $xml;
        if ($is_exit) {
            exit();
        }
    }

    public function redirect($url, $msg = null, $view = null, $time = 3) {
        if (empty($msg)) {
            header('Location:' . $url);
        } else {
            header("refresh:{$time};url={$url}"); //单位秒
            header("Content-type: text/html; charset=utf-8");
            if (empty($view)) {
                echo $msg;
            } else {
                $this->view($view, array('msg' => $msg, 'url' => $url, 'time' => $time));
            }
        }
    }

    public function message($msg, $view = null, $url = null, $time = 3) {
        if (!empty($url)) {
            header("refresh:{$time};url={$url}"); //单位秒
        }
        header("Content-type: text/html; charset=utf-8");
        if (!empty($view)) {
            $this->view($view, array('msg' => $msg, 'url' => $url, 'time' => $time));
        } else {
            echo $msg;
        }
    }

    public function setCookie($key, $value, $life = null, $path = '/', $domian = null) {
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        setcookie($key, $value, ($life ? $life + time() : null), $path, ($domian ? $domian : '.' . $this->input->server('HTTP_HOST')), ($this->input->server('SERVER_PORT') == 443 ? 1 : 0));
        $_COOKIE[$key] = $value;
    }

    /**
     * 分页函数
     * @param type $total 一共多少记录
     * @param type $page  当前是第几页
     * @param type $pagesize 每页多少
     * @param type $url    url是什么，url里面的{page}会被替换成页码
     * @return type  String
     */
    public function page($total, $page, $pagesize, $url) {
        $a_num = 10;
        $first = ' 首页 ';
        $last = ' 尾页 ';
        $pre = ' 上页 ';
        $next = ' 下页 ';
        $a_num = $a_num % 2 == 0 ? $a_num + 1 : $a_num;
        $pages = ceil($total / $pagesize);
        $curpage = intval($page) ? intval($page) : 1;
        $curpage = $curpage > $pages || $curpage <= 0 ? 1 : $curpage; #当前页超范围置为1
        $body = '';
        $prefix = '';
        $subfix = '';
        $start = $curpage - ($a_num - 1) / 2; #开始页
        $end = $curpage + ($a_num - 1) / 2;  #结束页
        $start = $start <= 0 ? 1 : $start;   #开始页超范围修正
        $end = $end > $pages ? $pages : $end; #结束页超范围修正
        if ($pages >= $a_num) {#总页数大于显示页数
            if ($curpage <= ($a_num - 1) / 2) {
                $end = $a_num;
            }//当前页在左半边补右边
            if ($end - $curpage <= ($a_num - 1) / 2) {
                $start-=5 - ($end - $curpage);
            }//当前页在右半边补左边
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $curpage) {
                $body.='<b>' . $i . '</b>';
            } else {
                $body.='<a href="' . str_replace('{page}', $i, $url) . '"> ' . $i . ' </a>';
            }
        }
        $prefix = ($curpage == 1 ? '' : '<a href="' . str_replace('{page}', 1, $url) . '">' . $first . '</a><a href="' . str_replace('{page}', $curpage - 1, $url) . '">' . $pre . '</a>');
        $subfix = ($curpage == $pages ? '' : '<a href="' . str_replace('{page}', $curpage + 1, $url) . '">' . $next . '</a><a href="' . str_replace('{page}', $pages, $url) . '">' . $last . '</a>');
        $info = " 第{$curpage}/{$pages}页 ";
        $go = '<script>function ekup(){if(event.keyCode==13){clkyup();}}function clkyup(){var num=document.getElementById(\'gsd09fhas9d\').value;if(!/^\d+$/.test(num)||num<=0||num>' . $pages . '){alert(\'请输入正确页码!\');return;};location=\'' . $url . '\'.replace(/\\{page\\}/,document.getElementById(\'gsd09fhas9d\').value);}</script><input onkeyup="ekup()" type="text" id="gsd09fhas9d" style="width:40px;vertical-align:text-baseline;padding:0 2px;font-size:10px;border:1px solid gray;"/> <span id="gsd09fhas9daa" onclick="clkyup();" style="cursor:pointer;text-decoration:underline;">转到</span>';
        $total = "共{$total}条";
        return $total . ' ' . $info . ' ' . $prefix . $body . $subfix . '&nbsp;' . $go;
    }

    /**
     * $source_data和$map的key一致，$map的value是返回数据的key
     * 根据$map的key读取$source_data中的数据，结果是以map的value为key的数数组
     * 
     * @param Array $map 字段映射数组
     */
    public function readData(Array $map, $source_data = null) {
        $data = array();
        $formdata = is_null($source_data) ? $this->input->post() : $source_data;
        foreach ($formdata as $form_key => $val) {
            if (isset($map[$form_key])) {
                $data[$map[$form_key]] = $val;
            }
        }
        return $data;
    }

    public function checkData(Array $rule, Array $data) {
        foreach ($rule as $col => $val) {
            if ($val['rule']) {
                #有规则但是没有数据，就补上空数据，然后进行验证
                if (!isset($data[$col])) {
                    $data[$col] = '';
                }
                #函数验证
                if (strpos($val['rule'], '/') === FALSE) {
                    return $this->{$val['rule']}($data[$col], $data);
                } else {
                    #正则表达式验证
                    if (!preg_match($val['rule'], $data[$col])) {
                        return $val['msg'];
                    }
                }
            }
        }
        return NULL;
    }

}

class WoniuModelLoader {

    public static $model_files = array();

    function __get($classname) {
        return isset(self::$model_files[strtolower($classname)]) ? self::$model_files[strtolower($classname)] : null;
    }

}

class WoniuLibLoader {

    public static $lib_files = array();

    function __get($classname) {
        return isset(self::$lib_files[strtolower($classname)]) ? self::$lib_files[strtolower($classname)] : null;
    }

}

/* End of file Loader.php */
//####################modules/WoniuController.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
class WoniuController extends WoniuLoader {

    private static $woniu;
    private static $instance;

    public function __construct() {
        parent::__construct();
        self::$woniu = &$this;
    }

    public static function &getInstance() {
        return self::$woniu;
    }
    public static function instance($classname_path) {
        if (empty($classname_path)) {
            return empty(self::$instance) ? self::$instance = new self() : self::$instance;
        }
        global $system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['controller_folder'] . DIRECTORY_SEPARATOR . $classname_path . $system['controller_file_subfix'];
        $alias_name = strtolower($filepath);

        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            WoniuLoader::classAutoloadRegister();
            include $filepath;
            if (class_exists($classname)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Ccontroller Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

}

/* End of file Controller.php */
//####################modules/WoniuModel.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
class WoniuModel extends WoniuLoader {

    private static $instance;

    public static function instance($classname_path) {
        if (empty($classname_path)) {
            return empty(self::$instance) ? self::$instance = new self() : self::$instance;
        }
        global $system;
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);
        $filepath = $system['model_folder'] . DIRECTORY_SEPARATOR . $classname_path . $system['model_file_subfix'];
        $alias_name = strtolower($filepath);
        if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
            return WoniuModelLoader::$model_files[$alias_name];
        }
        if (file_exists($filepath)) {
            //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
            //如此一来就能满足两种模式
            WoniuLoader::classAutoloadRegister();
            include $filepath;
            if (class_exists($classname)) {
                return WoniuModelLoader::$model_files[$alias_name] = new $classname();
            } else {
                trigger404('Model Class:' . $classname . ' not found.');
            }
        } else {
            trigger404($filepath . ' not found.');
        }
    }

}

/* End of file Model.php */
//####################modules/db-drivers/db.drivers.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
class WoniuDB {

    private static $conns = array();

    public static function getInstance($config) {
        $class = 'CI_DB_' . $config['dbdriver'] . '_driver';
        $hash = md5(sha1(var_export($config, TRUE)));
        if (!isset(self::$conns[$hash])) {
            self::$conns[$hash] = new $class($config);
        }
        if ($config['dbdriver'] == 'pdo' && strpos($config['hostname'], 'mysql') !== FALSE) {
            //pdo下面dns设置mysql字符会失效，这里hack一下
            self::$conns[$hash]->simple_query('set names ' . $config['char_set']);
        }
        return self::$conns[$hash];
    }

}

class CI_DB extends CI_DB_active_record {
    
}

/**
 * Database Driver Class
 *
 * This is the platform-independent base DB implementation class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
 */
class CI_DB_driver {

    var $username;
    var $password;
    var $hostname;
    var $database;
    var $dbdriver = 'mysql';
    var $dbprefix = '';
    var $char_set = 'utf8';
    var $dbcollat = 'utf8_general_ci';
    var $autoinit = TRUE; // Whether to automatically initialize the DB
    var $swap_pre = '';
    var $port = '';
    var $pconnect = FALSE;
    var $conn_id = FALSE;
    var $result_id = FALSE;
    var $db_debug = FALSE;
    var $benchmark = 0;
    var $query_count = 0;
    var $bind_marker = '?';
    var $save_queries = TRUE;
    var $queries = array();
    var $query_times = array();
    var $data_cache = array();
    var $trans_enabled = TRUE;
    var $trans_strict = TRUE;
    var $_trans_depth = 0;
    var $_trans_status = TRUE; // Used with transactions to determine if a rollback should occur
    var $cache_on = FALSE;
    var $cachedir = '';
    var $cache_autodel = FALSE;
    var $CACHE; // The cache class object
// Private variables
    var $_protect_identifiers = TRUE;
    var $_reserved_identifiers = array('*'); // Identifiers that should NOT be escaped
// These are use with Oracle
    var $stmt_id;
    var $curs_id;
    var $limit_used;

    /**
     * Constructor.  Accepts one parameter containing the database
     * connection settings.
     *
     * @param array
     */
    function __construct($params) {
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }

        log_message('debug', 'Database Driver Class Initialized');
    }

// --------------------------------------------------------------------

    /**
     * Initialize Database Settings
     *
     * @access        private Called by the constructor
     * @param        mixed
     * @return        void
     */
    function initialize() {
// If an existing connection resource is available
// there is no need to connect and select the database
        if (is_resource($this->conn_id) OR is_object($this->conn_id)) {
            return TRUE;
        }

// ----------------------------------------------------------------
// Connect to the database and set the connection ID
        $this->conn_id = ($this->pconnect == FALSE) ? $this->db_connect() : $this->db_pconnect();

// No connection resource?  Throw an error
        if (!$this->conn_id) {
            log_message('error', 'Unable to connect to the database');

            if ($this->db_debug) {
                $this->display_error('db_unable_to_connect');
            }
            return FALSE;
        }

// ----------------------------------------------------------------
// Select the DB... assuming a database name is specified in the config file
        if ($this->database != '') {
            if (!$this->db_select()) {
                log_message('error', 'Unable to select database: ' . $this->database);

                if ($this->db_debug) {
                    $this->display_error('db_unable_to_select', $this->database);
                }
                return FALSE;
            } else {
// We've selected the DB. Now we set the character set
                if (!$this->db_set_charset($this->char_set, $this->dbcollat)) {
                    return FALSE;
                }

                return TRUE;
            }
        }

        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access        public
     * @param        string
     * @param        string
     * @return        resource
     */
    function db_set_charset($charset, $collation) {
        if (!$this->_db_set_charset($this->char_set, $this->dbcollat)) {
            log_message('error', 'Unable to set database connection charset: ' . $this->char_set);

            if ($this->db_debug) {
                $this->display_error('db_unable_to_set_charset', $this->char_set);
            }

            return FALSE;
        }

        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * The name of the platform in use (mysql, mssql, etc...)
     *
     * @access        public
     * @return        string
     */
    function platform() {
        return $this->dbdriver;
    }

// --------------------------------------------------------------------

    /**
     * Database Version Number.  Returns a string containing the
     * version of the database being used
     *
     * @access        public
     * @return        string
     */
    function version() {
        if (FALSE === ($sql = $this->_version())) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }

// Some DBs have functions that return the version, and don't run special
// SQL queries per se. In these instances, just return the result.
        $driver_version_exceptions = array('oci8', 'sqlite', 'cubrid');

        if (in_array($this->dbdriver, $driver_version_exceptions)) {
            return $sql;
        } else {
            $query = $this->query($sql);
            return $query->row('ver');
        }
    }

// --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * Accepts an SQL string as input and returns a result object upon
     * successful execution of a "read" type query.  Returns boolean TRUE
     * upon successful execution of a "write" type query. Returns boolean
     * FALSE upon failure, and if the $db_debug variable is set to TRUE
     * will raise an error.
     *
     * @access        public
     * @param        string        An SQL query string
     * @param        array        An array of binding data
     * @return        mixed
     */
    function query($sql, $binds = FALSE, $return_object = TRUE) {
        if ($sql == '') {
            if ($this->db_debug) {
                log_message('error', 'Invalid query: ' . $sql);
                return $this->display_error('db_invalid_query');
            }
            return FALSE;
        }

// Verify table prefix and replace if necessary
        if (($this->dbprefix != '' AND $this->swap_pre != '') AND ($this->dbprefix != $this->swap_pre)) {
            $sql = preg_replace("/(\W)" . $this->swap_pre . "(\S+?)/", "\\1" . $this->dbprefix . "\\2", $sql);
        }

// Compile binds if needed
        if ($binds !== FALSE) {
            $sql = $this->compile_binds($sql, $binds);
        }

// Is query caching enabled?  If the query is a "read type"
// we will load the caching class and return the previously
// cached query if it exists
        if ($this->cache_on == TRUE AND stristr($sql, 'SELECT')) {
            if ($this->_cache_init()) {
                $this->load_rdriver();
                if (FALSE !== ($cache = $this->CACHE->read($sql))) {
                    return $cache;
                }
            }
        }

// Save the  query for debugging
        if ($this->save_queries == TRUE) {
            $this->queries[] = $sql;
        }

// Start the Query Timer
        $time_start = list($sm, $ss) = explode(' ', microtime());

// Run the Query
        if (FALSE === ($this->result_id = $this->simple_query($sql))) {
            if ($this->save_queries == TRUE) {
                $this->query_times[] = 0;
            }

// This will trigger a rollback if transactions are being used
            $this->_trans_status = FALSE;

            if ($this->db_debug) {
// grab the error number and message now, as we might run some
// additional queries before displaying the error
                $error_no = $this->_error_number();
                $error_msg = $this->_error_message();

// We call this function in order to roll-back queries
// if transactions are enabled.  If we don't call this here
// the error message will trigger an exit, causing the
// transactions to remain in limbo.
                $this->trans_complete();

// Log and display errors
                log_message('error', 'Query error: ' . $error_msg);
                return $this->display_error(
                                array(
                                    'Error Number: ' . $error_no,
                                    $error_msg,
                                    $sql
                                )
                );
            }

            return FALSE;
        }

// Stop and aggregate the query time results
        $time_end = list($em, $es) = explode(' ', microtime());
        $this->benchmark += ($em + $es) - ($sm + $ss);

        if ($this->save_queries == TRUE) {
            $this->query_times[] = ($em + $es) - ($sm + $ss);
        }

// Increment the query counter
        $this->query_count++;

// Was the query a "write" type?
// If so we'll simply return true
        if ($this->is_write_type($sql) === TRUE) {
// If caching is enabled we'll auto-cleanup any
// existing files related to this particular URI
            if ($this->cache_on == TRUE AND $this->cache_autodel == TRUE AND $this->_cache_init()) {
                $this->CACHE->delete();
            }

            return TRUE;
        }

// Return TRUE if we don't need to create a result object
// Currently only the Oracle driver uses this when stored
// procedures are used
        if ($return_object !== TRUE) {
            return TRUE;
        }

// Load and instantiate the result driver

        $driver = $this->load_rdriver();
        $RES = new $driver();
        $RES->conn_id = $this->conn_id;
        $RES->result_id = $this->result_id;

        if ($this->dbdriver == 'oci8') {
            $RES->stmt_id = $this->stmt_id;
            $RES->curs_id = NULL;
            $RES->limit_used = $this->limit_used;
            $this->stmt_id = FALSE;
        }

// oci8 vars must be set before calling this
        $RES->num_rows = $RES->num_rows();

// Is query caching enabled?  If so, we'll serialize the
// result object and save it to a cache file.
        if ($this->cache_on == TRUE AND $this->_cache_init()) {
// We'll create a new instance of the result object
// only without the platform specific driver since
// we can't use it with cached data (the query result
// resource ID won't be any good once we've cached the
// result object, so we'll have to compile the data
// and save it)
            $CR = new CI_DB_result();
            $CR->num_rows = $RES->num_rows();
            $CR->result_object = $RES->result_object();
            $CR->result_array = $RES->result_array();

// Reset these since cached objects can not utilize resource IDs.
            $CR->conn_id = NULL;
            $CR->result_id = NULL;

            $this->CACHE->write($sql, $CR);
        }

        return $RES;
    }

// --------------------------------------------------------------------

    /**
     * Load the result drivers
     *
     * @access        public
     * @return        string        the name of the result class
     */
    function load_rdriver() {
        $driver = 'CI_DB_' . $this->dbdriver . '_result';

        if (!class_exists($driver)) {
            include_once(BASEPATH . 'database/DB_result.php');
            include_once(BASEPATH . 'database/drivers/' . $this->dbdriver . '/' . $this->dbdriver . '_result.php');
        }

        return $driver;
    }

// --------------------------------------------------------------------

    /**
     * Simple Query
     * This is a simplified version of the query() function.  Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @access        public
     * @param        string        the sql query
     * @return        mixed
     */
    function simple_query($sql) {
        if (!$this->conn_id) {
            $this->initialize();
        }

        return $this->_execute($sql);
    }

// --------------------------------------------------------------------

    /**
     * Disable Transactions
     * This permits transactions to be disabled at run-time.
     *
     * @access        public
     * @return        void
     */
    function trans_off() {
        $this->trans_enabled = FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Enable/disable Transaction Strict Mode
     * When strict mode is enabled, if you are running multiple groups of
     * transactions, if one group fails all groups will be rolled back.
     * If strict mode is disabled, each group is treated autonomously, meaning
     * a failure of one group will not affect any others
     *
     * @access        public
     * @return        void
     */
    function trans_strict($mode = TRUE) {
        $this->trans_strict = is_bool($mode) ? $mode : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Start Transaction
     *
     * @access        public
     * @return        void
     */
    function trans_start($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return FALSE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            $this->_trans_depth += 1;
            return;
        }

        $this->trans_begin($test_mode);
    }

// --------------------------------------------------------------------

    /**
     * Complete Transaction
     *
     * @access        public
     * @return        bool
     */
    function trans_complete() {
        if (!$this->trans_enabled) {
            return FALSE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 1) {
            $this->_trans_depth -= 1;
            return TRUE;
        }

// The query() function will set this flag to FALSE in the event that a query failed
        if ($this->_trans_status === FALSE) {
            $this->trans_rollback();

// If we are NOT running in strict mode, we will reset
// the _trans_status flag so that subsequent groups of transactions
// will be permitted.
            if ($this->trans_strict === FALSE) {
                $this->_trans_status = TRUE;
            }

            log_message('debug', 'DB Transaction Failure');
            return FALSE;
        }

        $this->trans_commit();
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Lets you retrieve the transaction flag to determine if it has failed
     *
     * @access        public
     * @return        bool
     */
    function trans_status() {
        return $this->_trans_status;
    }

// --------------------------------------------------------------------

    /**
     * Compile Bindings
     *
     * @access        public
     * @param        string        the sql statement
     * @param        array        an array of bind data
     * @return        string
     */
    function compile_binds($sql, $binds) {
        if (strpos($sql, $this->bind_marker) === FALSE) {
            return $sql;
        }

        if (!is_array($binds)) {
            $binds = array($binds);
        }

// Get the sql segments around the bind markers
        $segments = explode($this->bind_marker, $sql);

// The count of bind should be 1 less then the count of segments
// If there are more bind arguments trim it down
        if (count($binds) >= count($segments)) {
            $binds = array_slice($binds, 0, count($segments) - 1);
        }

// Construct the binded query
        $result = $segments[0];
        $i = 0;
        foreach ($binds as $bind) {
            $result .= $this->escape($bind);
            $result .= $segments[++$i];
        }

        return $result;
    }

// --------------------------------------------------------------------

    /**
     * Determines if a query is a "write" type.
     *
     * @access        public
     * @param        string        An SQL query string
     * @return        boolean
     */
    function is_write_type($sql) {
        if (!preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql)) {
            return FALSE;
        }
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Calculate the aggregate query elapsed time
     *
     * @access        public
     * @param        integer        The number of decimal places
     * @return        integer
     */
    function elapsed_time($decimals = 6) {
        return number_format($this->benchmark, $decimals);
    }

// --------------------------------------------------------------------

    /**
     * Returns the total number of queries
     *
     * @access        public
     * @return        integer
     */
    function total_queries() {
        return $this->query_count;
    }

// --------------------------------------------------------------------

    /**
     * Returns the last query that was executed
     *
     * @access        public
     * @return        void
     */
    function last_query() {
        return end($this->queries);
    }

// --------------------------------------------------------------------

    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @access        public
     * @param        string
     * @return        mixed
     */
    function escape($str) {
        if (is_string($str)) {
            $str = "'" . $this->escape_str($str) . "'";
        } elseif (is_bool($str)) {
            $str = ($str === FALSE) ? 0 : 1;
        } elseif (is_null($str)) {
            $str = 'NULL';
        }

        return $str;
    }

// --------------------------------------------------------------------

    /**
     * Escape LIKE String
     *
     * Calls the individual driver for platform
     * specific escaping for LIKE conditions
     *
     * @access        public
     * @param        string
     * @return        mixed
     */
    function escape_like_str($str) {
        return $this->escape_str($str, TRUE);
    }

// --------------------------------------------------------------------

    /**
     * Primary
     *
     * Retrieves the primary key.  It assumes that the row in the first
     * position is the primary key
     *
     * @access        public
     * @param        string        the table name
     * @return        string
     */
    function primary($table = '') {
        $fields = $this->list_fields($table);

        if (!is_array($fields)) {
            return FALSE;
        }

        return current($fields);
    }

// --------------------------------------------------------------------

    /**
     * Returns an array of table names
     *
     * @access        public
     * @return        array
     */
    function list_tables($constrain_by_prefix = FALSE) {
// Is there a cached result?
        if (isset($this->data_cache['table_names'])) {
            return $this->data_cache['table_names'];
        }

        if (FALSE === ($sql = $this->_list_tables($constrain_by_prefix))) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }

        $retval = array();
        $query = $this->query($sql);

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                if (isset($row['TABLE_NAME'])) {
                    $retval[] = $row['TABLE_NAME'];
                } else {
                    $retval[] = array_shift($row);
                }
            }
        }

        $this->data_cache['table_names'] = $retval;
        return $this->data_cache['table_names'];
    }

// --------------------------------------------------------------------

    /**
     * Determine if a particular table exists
     * @access        public
     * @return        boolean
     */
    function table_exists($table_name) {
        return (!in_array($this->_protect_identifiers($table_name, TRUE, FALSE, FALSE), $this->list_tables())) ? FALSE : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Fetch MySQL Field Names
     *
     * @access        public
     * @param        string        the table name
     * @return        array
     */
    function list_fields($table = '') {
// Is there a cached result?
        if (isset($this->data_cache['field_names'][$table])) {
            return $this->data_cache['field_names'][$table];
        }

        if ($table == '') {
            if ($this->db_debug) {
                return $this->display_error('db_field_param_missing');
            }
            return FALSE;
        }

        if (FALSE === ($sql = $this->_list_columns($table))) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        }

        $query = $this->query($sql);

        $retval = array();
        foreach ($query->result_array() as $row) {
            if (isset($row['COLUMN_NAME'])) {
                $retval[] = $row['COLUMN_NAME'];
            } else if ($this->dbdriver == 'sqlite3') {
                $retval[] = $row['name'];
            } else {
                $retval[] = current($row);
            }
        }

        $this->data_cache['field_names'][$table] = $retval;
        return $this->data_cache['field_names'][$table];
    }

// --------------------------------------------------------------------

    /**
     * Determine if a particular field exists
     * @access        public
     * @param        string
     * @param        string
     * @return        boolean
     */
    function field_exists($field_name, $table_name) {
        return (!in_array($field_name, $this->list_fields($table_name))) ? FALSE : TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Returns an object with field data
     *
     * @access        public
     * @param        string        the table name
     * @return        object
     */
    function field_data($table = '') {
        if ($table == '') {
            if ($this->db_debug) {
                return $this->display_error('db_field_param_missing');
            }
            return FALSE;
        }

        $query = $this->query($this->_field_data($this->_protect_identifiers($table, TRUE, NULL, FALSE)));

        return $query->field_data();
    }

// --------------------------------------------------------------------

    /**
     * Generate an insert string
     *
     * @access        public
     * @param        string        the table upon which the query will be performed
     * @param        array        an associative array data of key/values
     * @return        string
     */
    function insert_string($table, $data) {
        $fields = array();
        $values = array();

        foreach ($data as $key => $val) {
            $fields[] = $this->_escape_identifiers($key);
            $values[] = $this->escape($val);
        }

        return $this->_insert($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $values);
    }

// --------------------------------------------------------------------

    /**
     * Generate an update string
     *
     * @access        public
     * @param        string        the table upon which the query will be performed
     * @param        array        an associative array data of key/values
     * @param        mixed        the "where" statement
     * @return        string
     */
    function update_string($table, $data, $where) {
        if ($where == '') {
            return false;
        }

        $fields = array();
        foreach ($data as $key => $val) {
            $fields[$this->_protect_identifiers($key)] = $this->escape($val);
        }

        if (!is_array($where)) {
            $dest = array($where);
        } else {
            $dest = array();
            foreach ($where as $key => $val) {
                $prefix = (count($dest) == 0) ? '' : ' AND ';

                if ($val !== '') {
                    if (!$this->_has_operator($key)) {
                        $key .= ' =';
                    }

                    $val = ' ' . $this->escape($val);
                }

                $dest[] = $prefix . $key . $val;
            }
        }

        return $this->_update($this->_protect_identifiers($table, TRUE, NULL, FALSE), $fields, $dest);
    }

// --------------------------------------------------------------------

    /**
     * Tests whether the string has an SQL operator
     *
     * @access        private
     * @param        string
     * @return        bool
     */
    function _has_operator($str) {
        $str = trim($str);
        if (!preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) {
            return FALSE;
        }

        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Enables a native PHP function to be run, using a platform agnostic wrapper.
     *
     * @access        public
     * @param        string        the function name
     * @param        mixed        any parameters needed by the function
     * @return        mixed
     */
    function call_function($function) {
        $driver = ($this->dbdriver == 'postgre') ? 'pg_' : $this->dbdriver . '_';

        if (FALSE === strpos($driver, $function)) {
            $function = $driver . $function;
        }

        if (!function_exists($function)) {
            if ($this->db_debug) {
                return $this->display_error('db_unsupported_function');
            }
            return FALSE;
        } else {
            $args = (func_num_args() > 1) ? array_splice(func_get_args(), 1) : null;
            if (is_null($args)) {
                return call_user_func($function);
            } else {
                return call_user_func_array($function, $args);
            }
        }
    }

// --------------------------------------------------------------------

    /**
     * Set Cache Directory Path
     *
     * @access        public
     * @param        string        the path to the cache directory
     * @return        void
     */
    function cache_set_path($path = '') {
        $this->cachedir = $path;
    }

// --------------------------------------------------------------------

    /**
     * Enable Query Caching
     *
     * @access        public
     * @return        void
     */
    function cache_on() {
        $this->cache_on = TRUE;
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Disable Query Caching
     *
     * @access        public
     * @return        void
     */
    function cache_off() {
        $this->cache_on = FALSE;
        return FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Delete the cache files associated with a particular URI
     *
     * @access        public
     * @return        void
     */
    function cache_delete($segment_one = '', $segment_two = '') {
        if (!$this->_cache_init()) {
            return FALSE;
        }
        return $this->CACHE->delete($segment_one, $segment_two);
    }

// --------------------------------------------------------------------

    /**
     * Delete All cache files
     *
     * @access        public
     * @return        void
     */
    function cache_delete_all() {
        if (!$this->_cache_init()) {
            return FALSE;
        }

        return $this->CACHE->delete_all();
    }

// --------------------------------------------------------------------

    /**
     * Initialize the Cache Class
     *
     * @access        private
     * @return        void
     */
    function _cache_init() {
        if (is_object($this->CACHE) AND class_exists('CI_DB_Cache')) {
            return TRUE;
        }

        if (!class_exists('CI_DB_Cache')) {
            if (!@include(BASEPATH . 'database/DB_cache.php')) {
                return $this->cache_off();
            }
        }

        $this->CACHE = new CI_DB_Cache($this); // pass db object to support multiple db connections and returned db objects
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access        public
     * @return        void
     */
    function close() {
        if (is_resource($this->conn_id) OR is_object($this->conn_id)) {
            $this->_close($this->conn_id);
        }
        $this->conn_id = FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Display an error message
     *
     * @access        public
     * @param        string        the error message
     * @param        string        any "swap" values
     * @param        boolean        whether to localize the message
     * @return        string        sends the application/error_db.php template
     */
    function display_error($error = '', $swap = '', $native = FALSE) {
        $msg = '';
        if (is_array($error)) {
            foreach ($error as $m) {
                $msg.=$m . "</br>";
            }
        } else {
            $msg = $error;
        }
        global $woniu_db, $system;
        if ($woniu_db[$woniu_db['active_group']]['db_debug']) {
            header('HTTP/1.1 500 Internal Server Database Error');
            if (!empty($system['error_page_db']) && file_exists($system['error_page_db'])) {
                include $system['error_page_db'];
            } else {
                echo $msg;
            }
        }
        exit;
    }

// --------------------------------------------------------------------

    /**
     * Protect Identifiers
     *
     * This function adds backticks if appropriate based on db type
     *
     * @access        private
     * @param        mixed        the item to escape
     * @return        mixed        the item with backticks
     */
    function protect_identifiers($item, $prefix_single = FALSE) {
        return $this->_protect_identifiers($item, $prefix_single);
    }

// --------------------------------------------------------------------

    /**
     * Protect Identifiers
     *
     * This function is used extensively by the Active Record class, and by
     * a couple functions in this class.
     * It takes a column or table name (optionally with an alias) and inserts
     * the table prefix onto it.  Some logic is necessary in order to deal with
     * column names that include the path.  Consider a query like this:
     *
     * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
     *
     * Or a query with aliasing:
     *
     * SELECT m.member_id, m.member_name FROM members AS m
     *
     * Since the column name can include up to four segments (host, DB, table, column)
     * or also have an alias prefix, we need to do a bit of work to figure this out and
     * insert the table prefix (if it exists) in the proper position, and escape only
     * the correct identifiers.
     *
     * @access        private
     * @param        string
     * @param        bool
     * @param        mixed
     * @param        bool
     * @return        string
     */
    function _protect_identifiers($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE) {
        if (!is_bool($protect_identifiers)) {
            $protect_identifiers = $this->_protect_identifiers;
        }

        if (is_array($item)) {
            $escaped_array = array();

            foreach ($item as $k => $v) {
                $escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v);
            }

            return $escaped_array;
        }

// Convert tabs or multiple spaces into single spaces
        $item = preg_replace('/[\t ]+/', ' ', $item);

// If the item has an alias declaration we remove it and set it aside.
// Basically we remove everything to the right of the first space
        if (strpos($item, ' ') !== FALSE) {
            $alias = strstr($item, ' ');
            $item = substr($item, 0, - strlen($alias));
        } else {
            $alias = '';
        }

// This is basically a bug fix for queries that use MAX, MIN, etc.
// If a parenthesis is found we know that we do not need to
// escape the data or add a prefix.  There's probably a more graceful
// way to deal with this, but I'm not thinking of it -- Rick
        if (strpos($item, '(') !== FALSE) {
            return $item . $alias;
        }

// Break the string apart if it contains periods, then insert the table prefix
// in the correct location, assuming the period doesn't indicate that we're dealing
// with an alias. While we're at it, we will escape the components
        if (strpos($item, '.') !== FALSE) {
            $parts = explode('.', $item);

// Does the first segment of the exploded item match
// one of the aliases previously identified?  If so,
// we have nothing more to do other than escape the item
            if (in_array($parts[0], $this->ar_aliased_tables)) {
                if ($protect_identifiers === TRUE) {
                    foreach ($parts as $key => $val) {
                        if (!in_array($val, $this->_reserved_identifiers)) {
                            $parts[$key] = $this->_escape_identifiers($val);
                        }
                    }

                    $item = implode('.', $parts);
                }
                return $item . $alias;
            }

// Is there a table prefix defined in the config file?  If not, no need to do anything
            if ($this->dbprefix != '') {
// We now add the table prefix based on some logic.
// Do we have 4 segments (hostname.database.table.column)?
// If so, we add the table prefix to the column name in the 3rd segment.
                if (isset($parts[3])) {
                    $i = 2;
                }
// Do we have 3 segments (database.table.column)?
// If so, we add the table prefix to the column name in 2nd position
                elseif (isset($parts[2])) {
                    $i = 1;
                }
// Do we have 2 segments (table.column)?
// If so, we add the table prefix to the column name in 1st segment
                else {
                    $i = 0;
                }

// This flag is set when the supplied $item does not contain a field name.
// This can happen when this function is being called from a JOIN.
                if ($field_exists == FALSE) {
                    $i++;
                }

// Verify table prefix and replace if necessary
                if ($this->swap_pre != '' && strncmp($parts[$i], $this->swap_pre, strlen($this->swap_pre)) === 0) {
                    $parts[$i] = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $parts[$i]);
                }

// We only add the table prefix if it does not already exist
                if (substr($parts[$i], 0, strlen($this->dbprefix)) != $this->dbprefix) {
                    $parts[$i] = $this->dbprefix . $parts[$i];
                }

// Put the parts back together
                $item = implode('.', $parts);
            }

            if ($protect_identifiers === TRUE) {
                $item = $this->_escape_identifiers($item);
            }

            return $item . $alias;
        }

// Is there a table prefix?  If not, no need to insert it
        if ($this->dbprefix != '') {
// Verify table prefix and replace if necessary
            if ($this->swap_pre != '' && strncmp($item, $this->swap_pre, strlen($this->swap_pre)) === 0) {
                $item = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $item);
            }

// Do we prefix an item with no segments?
            if ($prefix_single == TRUE AND substr($item, 0, strlen($this->dbprefix)) != $this->dbprefix) {
                $item = $this->dbprefix . $item;
            }
        }

        if ($protect_identifiers === TRUE AND !in_array($item, $this->_reserved_identifiers)) {
            $item = $this->_escape_identifiers($item);
        }

        return $item . $alias;
    }

// --------------------------------------------------------------------

    /**
     * Dummy method that allows Active Record class to be disabled
     *
     * This function is used extensively by every db driver.
     *
     * @return        void
     */
    protected function _reset_select() {
        
    }

}

/* End of file DB_driver.php */
/* Location: ./system/database/DB_driver.php */


// ------------------------------------------------------------------------

/**
 * Database Result Class
 *
 * This is the platform-independent result class.
 * This class will not be called directly. Rather, the adapter
 * class for the specific database will extend and instantiate it.
 *
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
 */
class CI_DB_result {

    var $conn_id = NULL;
    var $result_id = NULL;
    var $result_array = array();
    var $result_object = array();
    var $custom_result_object = array();
    var $current_row = 0;
    var $num_rows = 0;
    var $row_data = NULL;

    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @access        public
     * @param        string        can be "object" or "array"
     * @return        mixed        either a result object or array
     */
    public function result($type = 'object') {
        if ($type == 'array')
            return $this->result_array();
        else if ($type == 'object')
            return $this->result_object();
        else
            return $this->custom_result_object($type);
    }

// --------------------------------------------------------------------

    /**
     * Custom query result.
     *
     * @param class_name A string that represents the type of object you want back
     * @return array of objects
     */
    public function custom_result_object($class_name) {
        if (array_key_exists($class_name, $this->custom_result_object)) {
            return $this->custom_result_object[$class_name];
        }

        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }

// add the data to the object
        $this->_data_seek(0);
        $result_object = array();

        while ($row = $this->_fetch_object()) {
            $object = new $class_name();

            foreach ($row as $key => $value) {
                $object->$key = $value;
            }

            $result_object[] = $object;
        }

// return the array
        return $this->custom_result_object[$class_name] = $result_object;
    }

// --------------------------------------------------------------------

    /**
     * Query result.  "object" version.
     *
     * @access        public
     * @return        object
     */
    public function result_object() {
        if (count($this->result_object) > 0) {
            return $this->result_object;
        }

// In the event that query caching is on the result_id variable
// will return FALSE since there isn't a valid SQL resource so
// we'll simply return an empty array.
        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }

        $this->_data_seek(0);
        while ($row = $this->_fetch_object()) {
            $this->result_object[] = $row;
        }

        return $this->result_object;
    }

// --------------------------------------------------------------------

    /**
     * Query result.  "array" version.
     *
     * @access        public
     * @return        array
     */
    public function result_array() {
        if (count($this->result_array) > 0) {
            return $this->result_array;
        }

// In the event that query caching is on the result_id variable
// will return FALSE since there isn't a valid SQL resource so
// we'll simply return an empty array.
        if ($this->result_id === FALSE OR $this->num_rows() == 0) {
            return array();
        }

        $this->_data_seek(0);
        while ($row = $this->_fetch_assoc()) {
            $this->result_array[] = $row;
        }

        return $this->result_array;
    }

// --------------------------------------------------------------------

    /**
     * Query result.  Acts as a wrapper function for the following functions.
     *
     * @access        public
     * @param        string
     * @param        string        can be "object" or "array"
     * @return        mixed        either a result object or array
     */
    public function row($n = 0, $type = 'object') {
        if (!is_numeric($n)) {
// We cache the row data for subsequent uses
            if (!is_array($this->row_data)) {
                $this->row_data = $this->row_array(0);
            }

// array_key_exists() instead of isset() to allow for MySQL NULL values
            if (array_key_exists($n, $this->row_data)) {
                return $this->row_data[$n];
            }
// reset the $n variable if the result was not achieved
            $n = 0;
        }

        if ($type == 'object')
            return $this->row_object($n);
        else if ($type == 'array')
            return $this->row_array($n);
        else
            return $this->custom_row_object($n, $type);
    }

// --------------------------------------------------------------------

    /**
     * Assigns an item into a particular column slot
     *
     * @access        public
     * @return        object
     */
    public function set_row($key, $value = NULL) {
// We cache the row data for subsequent uses
        if (!is_array($this->row_data)) {
            $this->row_data = $this->row_array(0);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->row_data[$k] = $v;
            }

            return;
        }

        if ($key != '' AND !is_null($value)) {
            $this->row_data[$key] = $value;
        }
    }

// --------------------------------------------------------------------

    /**
     * Returns a single result row - custom object version
     *
     * @access        public
     * @return        object
     */
    public function custom_row_object($n, $type) {
        $result = $this->custom_result_object($type);

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

    /**
     * Returns a single result row - object version
     *
     * @access        public
     * @return        object
     */
    public function row_object($n = 0) {
        $result = $this->result_object();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * Returns a single result row - array version
     *
     * @access        public
     * @return        array
     */
    public function row_array($n = 0) {
        $result = $this->result_array();

        if (count($result) == 0) {
            return $result;
        }

        if ($n != $this->current_row AND isset($result[$n])) {
            $this->current_row = $n;
        }

        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "first" row
     *
     * @access        public
     * @return        object
     */
    public function first_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }
        return $result[0];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "last" row
     *
     * @access        public
     * @return        object
     */
    public function last_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }
        return $result[count($result) - 1];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "next" row
     *
     * @access        public
     * @return        object
     */
    public function next_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->current_row + 1])) {
            ++$this->current_row;
        }

        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * Returns the "previous" row
     *
     * @access        public
     * @return        object
     */
    public function previous_row($type = 'object') {
        $result = $this->result($type);

        if (count($result) == 0) {
            return $result;
        }

        if (isset($result[$this->current_row - 1])) {
            --$this->current_row;
        }
        return $result[$this->current_row];
    }

// --------------------------------------------------------------------

    /**
     * The following functions are normally overloaded by the identically named
     * methods in the platform-specific driver -- except when query caching
     * is used.  When caching is enabled we do not load the other driver.
     * These functions are primarily here to prevent undefined function errors
     * when a cached result object is in use.  They are not otherwise fully
     * operational due to the unavailability of the database resource IDs with
     * cached results.
     */
    public function num_rows() {
        return $this->num_rows;
    }

    public function num_fields() {
        return 0;
    }

    public function list_fields() {
        return array();
    }

    public function field_data() {
        return array();
    }

    public function free_result() {
        return TRUE;
    }

    protected function _data_seek() {
        return TRUE;
    }

    protected function _fetch_assoc() {
        return array();
    }

    protected function _fetch_object() {
        return array();
    }

}

// END DB_result class

/* End of file DB_result.php */
/* Location: ./system/database/DB_result.php */

// ------------------------------------------------------------------------

/**
 * Active Record Class
 *
 * This is the platform-independent base Active Record implementation class.
 *
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
 */
class CI_DB_active_record extends CI_DB_driver {

    var $ar_select = array();
    var $ar_distinct = FALSE;
    var $ar_from = array();
    var $ar_join = array();
    var $ar_where = array();
    var $ar_like = array();
    var $ar_groupby = array();
    var $ar_having = array();
    var $ar_keys = array();
    var $ar_limit = FALSE;
    var $ar_offset = FALSE;
    var $ar_order = FALSE;
    var $ar_orderby = array();
    var $ar_set = array();
    var $ar_wherein = array();
    var $ar_aliased_tables = array();
    var $ar_store_array = array();
// Active Record Caching variables
    var $ar_caching = FALSE;
    var $ar_cache_exists = array();
    var $ar_cache_select = array();
    var $ar_cache_from = array();
    var $ar_cache_join = array();
    var $ar_cache_where = array();
    var $ar_cache_like = array();
    var $ar_cache_groupby = array();
    var $ar_cache_having = array();
    var $ar_cache_orderby = array();
    var $ar_cache_set = array();
    var $ar_no_escape = array();
    var $ar_cache_no_escape = array();

// --------------------------------------------------------------------

    /**
     * Select
     *
     * Generates the SELECT portion of the query
     *
     * @param        string
     * @return        object
     */
    public function select($select = '*', $escape = NULL) {
        if (is_string($select)) {
            $select = explode(',', $select);
        }

        foreach ($select as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->ar_select[] = $val;
                $this->ar_no_escape[] = $escape;

                if ($this->ar_caching === TRUE) {
                    $this->ar_cache_select[] = $val;
                    $this->ar_cache_exists[] = 'select';
                    $this->ar_cache_no_escape[] = $escape;
                }
            }
        }
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Select Max
     *
     * Generates a SELECT MAX(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_max($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'MAX');
    }

// --------------------------------------------------------------------

    /**
     * Select Min
     *
     * Generates a SELECT MIN(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_min($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'MIN');
    }

// --------------------------------------------------------------------

    /**
     * Select Average
     *
     * Generates a SELECT AVG(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_avg($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'AVG');
    }

// --------------------------------------------------------------------

    /**
     * Select Sum
     *
     * Generates a SELECT SUM(field) portion of a query
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    public function select_sum($select = '', $alias = '') {
        return $this->_max_min_avg_sum($select, $alias, 'SUM');
    }

// --------------------------------------------------------------------

    /**
     * Processing Function for the four functions above:
     *
     *         select_max()
     *         select_min()
     *         select_avg()
     *  select_sum()
     *
     * @param        string        the field
     * @param        string        an alias
     * @return        object
     */
    protected function _max_min_avg_sum($select = '', $alias = '', $type = 'MAX') {
        if (!is_string($select) OR $select == '') {
            $this->display_error('db_invalid_query');
        }

        $type = strtoupper($type);

        if (!in_array($type, array('MAX', 'MIN', 'AVG', 'SUM'))) {
            show_error('Invalid function type: ' . $type);
        }

        if ($alias == '') {
            $alias = $this->_create_alias_from_table(trim($select));
        }

        $sql = $type . '(' . $this->_protect_identifiers(trim($select)) . ') AS ' . $alias;

        $this->ar_select[] = $sql;

        if ($this->ar_caching === TRUE) {
            $this->ar_cache_select[] = $sql;
            $this->ar_cache_exists[] = 'select';
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Determines the alias name based on the table
     *
     * @param        string
     * @return        string
     */
    protected function _create_alias_from_table($item) {
        if (strpos($item, '.') !== FALSE) {
            return end(explode('.', $item));
        }

        return $item;
    }

// --------------------------------------------------------------------

    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param        bool
     * @return        object
     */
    public function distinct($val = TRUE) {
        $this->ar_distinct = (is_bool($val)) ? $val : TRUE;
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * From
     *
     * Generates the FROM portion of the query
     *
     * @param        mixed        can be a string or array
     * @return        object
     */
    public function from($from) {
        foreach ((array) $from as $val) {
            if (strpos($val, ',') !== FALSE) {
                foreach (explode(',', $val) as $v) {
                    $v = trim($v);
                    $this->_track_aliases($v);

                    $this->ar_from[] = $this->_protect_identifiers($v, TRUE, NULL, FALSE);

                    if ($this->ar_caching === TRUE) {
                        $this->ar_cache_from[] = $this->_protect_identifiers($v, TRUE, NULL, FALSE);
                        $this->ar_cache_exists[] = 'from';
                    }
                }
            } else {
                $val = trim($val);

// Extract any aliases that might exist.  We use this information
// in the _protect_identifiers to know whether to add a table prefix
                $this->_track_aliases($val);

                $this->ar_from[] = $this->_protect_identifiers($val, TRUE, NULL, FALSE);

                if ($this->ar_caching === TRUE) {
                    $this->ar_cache_from[] = $this->_protect_identifiers($val, TRUE, NULL, FALSE);
                    $this->ar_cache_exists[] = 'from';
                }
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Join
     *
     * Generates the JOIN portion of the query
     *
     * @param        string
     * @param        string        the join condition
     * @param        string        the type of join
     * @return        object
     */
    public function join($table, $cond, $type = '') {
        if ($type != '') {
            $type = strtoupper(trim($type));

            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }

// Extract any aliases that might exist.  We use this information
// in the _protect_identifiers to know whether to add a table prefix
        $this->_track_aliases($table);

// Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $match[1] = $this->_protect_identifiers($match[1]);
            $match[3] = $this->_protect_identifiers($match[3]);

            $cond = $match[1] . $match[2] . $match[3];
        }

// Assemble the JOIN statement
        $join = $type . 'JOIN ' . $this->_protect_identifiers($table, TRUE, NULL, FALSE) . ' ON ' . $cond;

        $this->ar_join[] = $join;
        if ($this->ar_caching === TRUE) {
            $this->ar_cache_join[] = $join;
            $this->ar_cache_exists[] = 'join';
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with AND
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function where($key, $value = NULL, $escape = TRUE) {
        return $this->_where($key, $value, 'AND ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * OR Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with OR
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function or_where($key, $value = NULL, $escape = TRUE) {
        return $this->_where($key, $value, 'OR ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * Where
     *
     * Called by where() or or_where()
     *
     * @param        mixed
     * @param        mixed
     * @param        string
     * @return        object
     */
    protected function _where($key, $value = NULL, $type = 'AND ', $escape = NULL) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

// If the escape value was not set will will base it on the global setting
        if (!is_bool($escape)) {
            $escape = $this->_protect_identifiers;
        }

        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_where) == 0 AND count($this->ar_cache_where) == 0) ? '' : $type;

            if (is_null($v) && !$this->_has_operator($k)) {
// value appears not to have been set, assign the test to IS NULL
                $k .= ' IS NULL';
            }

            if (!is_null($v)) {
                if ($escape === TRUE) {
                    $k = $this->_protect_identifiers($k, FALSE, $escape);

                    $v = ' ' . $this->escape($v);
                }

                if (!$this->_has_operator($k)) {
                    $k .= ' = ';
                }
            } else {
                $k = $this->_protect_identifiers($k, FALSE, $escape);
            }

            $this->ar_where[] = $prefix . $k . $v;

            if ($this->ar_caching === TRUE) {
                $this->ar_cache_where[] = $prefix . $k . $v;
                $this->ar_cache_exists[] = 'where';
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Where_in
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * AND if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function where_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values);
    }

// --------------------------------------------------------------------

    /**
     * Where_in_or
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * OR if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function or_where_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, FALSE, 'OR ');
    }

// --------------------------------------------------------------------

    /**
     * Where_not_in
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with AND if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function where_not_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, TRUE);
    }

// --------------------------------------------------------------------

    /**
     * Where_not_in_or
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with OR if appropriate
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @return        object
     */
    public function or_where_not_in($key = NULL, $values = NULL) {
        return $this->_where_in($key, $values, TRUE, 'OR ');
    }

// --------------------------------------------------------------------

    /**
     * Where_in
     *
     * Called by where_in, where_in_or, where_not_in, where_not_in_or
     *
     * @param        string        The field to search
     * @param        array        The values searched on
     * @param        boolean        If the statement would be IN or NOT IN
     * @param        string
     * @return        object
     */
    protected function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ') {
        if ($key === NULL OR $values === NULL) {
            return;
        }

        if (!is_array($values)) {
            $values = array($values);
        }

        $not = ($not) ? ' NOT' : '';

        foreach ($values as $value) {
            $this->ar_wherein[] = $this->escape($value);
        }

        $prefix = (count($this->ar_where) == 0) ? '' : $type;

        $where_in = $prefix . $this->_protect_identifiers($key) . $not . " IN (" . implode(", ", $this->ar_wherein) . ") ";

        $this->ar_where[] = $where_in;
        if ($this->ar_caching === TRUE) {
            $this->ar_cache_where[] = $where_in;
            $this->ar_cache_exists[] = 'where';
        }

// reset the array for multiple calls
        $this->ar_wherein = array();
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with AND
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side);
    }

// --------------------------------------------------------------------

    /**
     * Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with AND
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'AND ', $side, 'NOT');
    }

// --------------------------------------------------------------------

    /**
     * OR Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with OR
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function or_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side);
    }

// --------------------------------------------------------------------

    /**
     * OR Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with OR
     *
     * @param        mixed
     * @param        mixed
     * @return        object
     */
    public function or_not_like($field, $match = '', $side = 'both') {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }

// --------------------------------------------------------------------

    /**
     * Like
     *
     * Called by like() or orlike()
     *
     * @param        mixed
     * @param        mixed
     * @param        string
     * @return        object
     */
    protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '') {
        if (!is_array($field)) {
            $field = array($field => $match);
        }

        foreach ($field as $k => $v) {
            $k = $this->_protect_identifiers($k);

            $prefix = (count($this->ar_like) == 0) ? '' : $type;

            $v = $this->escape_like_str($v);

            if ($side == 'none') {
                $like_statement = $prefix . " $k $not LIKE '{$v}'";
            } elseif ($side == 'before') {
                $like_statement = $prefix . " $k $not LIKE '%{$v}'";
            } elseif ($side == 'after') {
                $like_statement = $prefix . " $k $not LIKE '{$v}%'";
            } else {
                $like_statement = $prefix . " $k $not LIKE '%{$v}%'";
            }

// some platforms require an escape sequence definition for LIKE wildcards
            if ($this->_like_escape_str != '') {
                $like_statement = $like_statement . sprintf($this->_like_escape_str, $this->_like_escape_chr);
            }

            $this->ar_like[] = $like_statement;
            if ($this->ar_caching === TRUE) {
                $this->ar_cache_like[] = $like_statement;
                $this->ar_cache_exists[] = 'like';
            }
        }
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * GROUP BY
     *
     * @param        string
     * @return        object
     */
    public function group_by($by) {
        if (is_string($by)) {
            $by = explode(',', $by);
        }

        foreach ($by as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->ar_groupby[] = $this->_protect_identifiers($val);

                if ($this->ar_caching === TRUE) {
                    $this->ar_cache_groupby[] = $this->_protect_identifiers($val);
                    $this->ar_cache_exists[] = 'groupby';
                }
            }
        }
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the HAVING value
     *
     * Separates multiple calls with AND
     *
     * @param        string
     * @param        string
     * @return        object
     */
    public function having($key, $value = '', $escape = TRUE) {
        return $this->_having($key, $value, 'AND ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * Sets the OR HAVING value
     *
     * Separates multiple calls with OR
     *
     * @param        string
     * @param        string
     * @return        object
     */
    public function or_having($key, $value = '', $escape = TRUE) {
        return $this->_having($key, $value, 'OR ', $escape);
    }

// --------------------------------------------------------------------

    /**
     * Sets the HAVING values
     *
     * Called by having() or or_having()
     *
     * @param        string
     * @param        string
     * @return        object
     */
    protected function _having($key, $value = '', $type = 'AND ', $escape = TRUE) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            $prefix = (count($this->ar_having) == 0) ? '' : $type;

            if ($escape === TRUE) {
                $k = $this->_protect_identifiers($k);
            }

            if (!$this->_has_operator($k)) {
                $k .= ' = ';
            }

            if ($v != '') {
                $v = ' ' . $this->escape($v);
            }

            $this->ar_having[] = $prefix . $k . $v;
            if ($this->ar_caching === TRUE) {
                $this->ar_cache_having[] = $prefix . $k . $v;
                $this->ar_cache_exists[] = 'having';
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the ORDER BY value
     *
     * @param        string
     * @param        string        direction: asc or desc
     * @return        object
     */
    public function order_by($orderby, $direction = '') {
        if (strtolower($direction) == 'random') {
            $orderby = ''; // Random results want or don't need a field name
            $direction = $this->_random_keyword;
        } elseif (trim($direction) != '') {
            $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' ' . $direction : ' ASC';
        }


        if (strpos($orderby, ',') !== FALSE) {
            $temp = array();
            foreach (explode(',', $orderby) as $part) {
                $part = trim($part);
                if (!in_array($part, $this->ar_aliased_tables)) {
                    $part = $this->_protect_identifiers(trim($part));
                }

                $temp[] = $part;
            }

            $orderby = implode(', ', $temp);
        } else if ($direction != $this->_random_keyword) {
            $orderby = $this->_protect_identifiers($orderby);
        }

        $orderby_statement = $orderby . $direction;

        $this->ar_orderby[] = $orderby_statement;
        if ($this->ar_caching === TRUE) {
            $this->ar_cache_orderby[] = $orderby_statement;
            $this->ar_cache_exists[] = 'orderby';
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the LIMIT value
     *
     * @param        integer        the limit value
     * @param        integer        the offset value
     * @return        object
     */
    public function limit($value, $offset = '') {
        $this->ar_limit = (int) $value;

        if ($offset != '') {
            $this->ar_offset = (int) $offset;
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Sets the OFFSET value
     *
     * @param        integer        the offset value
     * @return        object
     */
    public function offset($offset) {
        $this->ar_offset = $offset;
        return $this;
    }

// --------------------------------------------------------------------

    /**
     * The "set" function.  Allows key/value pairs to be set for inserting or updating
     *
     * @param        mixed
     * @param        string
     * @param        boolean
     * @return        object
     */
    public function set($key, $value = '', $escape = TRUE) {
        $key = $this->_object_to_array($key);

        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            if ($escape === FALSE) {
                $this->ar_set[$this->_protect_identifiers($k)] = $v;
            } else {
                $this->ar_set[$this->_protect_identifiers($k, FALSE, TRUE)] = $this->escape($v);
            }
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param        string        the table
     * @param        string        the limit clause
     * @param        string        the offset clause
     * @return        object
     */
    public function get($table = '', $limit = null, $offset = null) {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }

        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }

        $sql = $this->_compile_select();

        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }

    /**
     * "Count All Results" query
     *
     * Generates a platform-specific query string that counts all records
     * returned by an Active Record query.
     *
     * @param        string
     * @return        string
     */
    public function count_all_results($table = '') {
        if ($table != '') {
            $this->_track_aliases($table);
            $this->from($table);
        }

        $sql = $this->_compile_select($this->_count_string . $this->_protect_identifiers('numrows'));

        $query = $this->query($sql);
        $this->_reset_select();

        if ($query->num_rows() == 0) {
            return 0;
        }

        $row = $query->row();
        return (int) $row->numrows;
    }

// --------------------------------------------------------------------

    /**
     * Get_Where
     *
     * Allows the where clause, limit and offset to be added directly
     *
     * @param        string        the where clause
     * @param        string        the limit clause
     * @param        string        the offset clause
     * @return        object
     */
    public function get_where($table = '', $where = null, $limit = null, $offset = null) {
        if ($table != '') {
            $this->from($table);
        }

        if (!is_null($where)) {
            $this->where($where);
        }

        if (!is_null($limit)) {
            $this->limit($limit, $offset);
        }

        $sql = $this->_compile_select();

        $result = $this->query($sql);
        $this->_reset_select();
        return $result;
    }

// --------------------------------------------------------------------

    /**
     * Insert_Batch
     *
     * Compiles batch insert strings and runs the queries
     *
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of insert values
     * @return        object
     */
    public function insert_batch($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set_insert_batch($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
//No valid data array.  Folds in cases where keys and values did not match up
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

// Batch this baby
        for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {

            $sql = $this->_insert_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_keys, array_slice($this->ar_set, $i, 100));

//echo $sql;

            $this->query($sql);
        }

        $this->_reset_write();


        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
     *
     * @param        mixed
     * @param        string
     * @param        boolean
     * @return        object
     */
    public function set_insert_batch($key, $value = '', $escape = TRUE) {
        $key = $this->_object_to_array_batch($key);

        if (!is_array($key)) {
            $key = array($key => $value);
        }

        $keys = array_keys(current($key));
        sort($keys);

        foreach ($key as $row) {
            if (count(array_diff($keys, array_keys($row))) > 0 OR count(array_diff(array_keys($row), $keys)) > 0) {
// batch function above returns an error on an empty array
                $this->ar_set[] = array();
                return;
            }

            ksort($row); // puts $row in the same order as our keys

            if ($escape === FALSE) {
                $this->ar_set[] = '(' . implode(',', $row) . ')';
            } else {
                $clean = array();

                foreach ($row as $value) {
                    $clean[] = $this->escape($value);
                }

                $this->ar_set[] = '(' . implode(',', $clean) . ')';
            }
        }

        foreach ($keys as $k) {
            $this->ar_keys[] = $this->_protect_identifiers($k);
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Insert
     *
     * Compiles an insert string and runs the query
     *
     * @param        string        the table to insert data into
     * @param        array        an associative array of insert values
     * @return        object
     */
    function insert($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

        $sql = $this->_insert($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_keys($this->ar_set), array_values($this->ar_set));

        $this->_reset_write();
        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Replace
     *
     * Compiles an replace into string and runs the query
     *
     * @param        string        the table to replace data into
     * @param        array        an associative array of insert values
     * @return        object
     */
    public function replace($table = '', $set = NULL) {
        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

        $sql = $this->_replace($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_keys($this->ar_set), array_values($this->ar_set));

        $this->_reset_write();
        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Update
     *
     * Compiles an update string and runs the query
     *
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of update values
     * @param        mixed        the where clause
     * @return        object
     */
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if (!is_null($set)) {
            $this->set($set);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

        if ($where != NULL) {
            $this->where($where);
        }

        if ($limit != NULL) {
            $this->limit($limit);
        }

        $sql = $this->_update($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_set, $this->ar_where, $this->ar_orderby, $this->ar_limit);

        $this->_reset_write();
        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Update_Batch
     *
     * Compiles an update string and runs the query
     *
     * @param        string        the table to retrieve the results from
     * @param        array        an associative array of update values
     * @param        string        the where key
     * @return        object
     */
    public function update_batch($table = '', $set = NULL, $index = NULL) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if (is_null($index)) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_index');
            }

            return FALSE;
        }

        if (!is_null($set)) {
            $this->set_update_batch($set, $index);
        }

        if (count($this->ar_set) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_must_use_set');
            }

            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        }

// Batch this baby
        for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100) {
            $sql = $this->_update_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_slice($this->ar_set, $i, 100), $this->_protect_identifiers($index), $this->ar_where);

            $this->query($sql);
        }

        $this->_reset_write();
    }

// --------------------------------------------------------------------

    /**
     * The "set_update_batch" function.  Allows key/value pairs to be set for batch updating
     *
     * @param        array
     * @param        string
     * @param        boolean
     * @return        object
     */
    public function set_update_batch($key, $index = '', $escape = TRUE) {
        $key = $this->_object_to_array_batch($key);

        if (!is_array($key)) {
// @todo error
        }

        foreach ($key as $k => $v) {
            $index_set = FALSE;
            $clean = array();

            foreach ($v as $k2 => $v2) {
                if ($k2 == $index) {
                    $index_set = TRUE;
                } else {
                    $not[] = $k . '-' . $v;
                }

                if ($escape === FALSE) {
                    $clean[$this->_protect_identifiers($k2)] = $v2;
                } else {
                    $clean[$this->_protect_identifiers($k2)] = $this->escape($v2);
                }
            }

            if ($index_set == FALSE) {
                return $this->display_error('db_batch_missing_index');
            }

            $this->ar_set[] = $clean;
        }

        return $this;
    }

// --------------------------------------------------------------------

    /**
     * Empty Table
     *
     * Compiles a delete string and runs "DELETE FROM table"
     *
     * @param        string        the table to empty
     * @return        object
     */
    public function empty_table($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }

        $sql = $this->_delete($table);

        $this->_reset_write();

        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Truncate
     *
     * Compiles a truncate string and runs the query
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @param        string        the table to truncate
     * @return        object
     */
    public function truncate($table = '') {
        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }

        $sql = $this->_truncate($table);

        $this->_reset_write();

        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * Delete
     *
     * Compiles a delete string and runs the query
     *
     * @param        mixed        the table(s) to delete from. String or array
     * @param        mixed        the where clause
     * @param        mixed        the limit clause
     * @param        boolean
     * @return        object
     */
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE) {
// Combine any cached components with the current statements
        $this->_merge_cache();

        if ($table == '') {
            if (!isset($this->ar_from[0])) {
                if ($this->db_debug) {
                    return $this->display_error('db_must_set_table');
                }
                return FALSE;
            }

            $table = $this->ar_from[0];
        } elseif (is_array($table)) {
            foreach ($table as $single_table) {
                $this->delete($single_table, $where, $limit, FALSE);
            }

            $this->_reset_write();
            return;
        } else {
            $table = $this->_protect_identifiers($table, TRUE, NULL, FALSE);
        }

        if ($where != '') {
            $this->where($where);
        }

        if ($limit != NULL) {
            $this->limit($limit);
        }

        if (count($this->ar_where) == 0 && count($this->ar_wherein) == 0 && count($this->ar_like) == 0) {
            if ($this->db_debug) {
                return $this->display_error('db_del_must_use_where');
            }

            return FALSE;
        }

        $sql = $this->_delete($table, $this->ar_where, $this->ar_like, $this->ar_limit);

        if ($reset_data) {
            $this->_reset_write();
        }

        return $this->query($sql);
    }

// --------------------------------------------------------------------

    /**
     * DB Prefix
     *
     * Prepends a database prefix if one exists in configuration
     *
     * @param        string        the table
     * @return        string
     */
    public function dbprefix($table = '') {
        if ($table == '') {
            $this->display_error('db_table_name_required');
        }

        return $this->dbprefix . $table;
    }

// --------------------------------------------------------------------

    /**
     * Set DB Prefix
     *
     * Set's the DB Prefix to something new without needing to reconnect
     *
     * @param        string        the prefix
     * @return        string
     */
    public function set_dbprefix($prefix = '') {
        return $this->dbprefix = $prefix;
    }

// --------------------------------------------------------------------

    /**
     * Track Aliases
     *
     * Used to track SQL statements written with aliased tables.
     *
     * @param        string        The table to inspect
     * @return        string
     */
    protected function _track_aliases($table) {
        if (is_array($table)) {
            foreach ($table as $t) {
                $this->_track_aliases($t);
            }
            return;
        }

// Does the string contain a comma?  If so, we need to separate
// the string into discreet statements
        if (strpos($table, ',') !== FALSE) {
            return $this->_track_aliases(explode(',', $table));
        }

// if a table alias is used we can recognize it by a space
        if (strpos($table, " ") !== FALSE) {
// if the alias is written with the AS keyword, remove it
            $table = preg_replace('/\s+AS\s+/i', ' ', $table);

// Grab the alias
            $table = trim(strrchr($table, " "));

// Store the alias, if it doesn't already exist
            if (!in_array($table, $this->ar_aliased_tables)) {
                $this->ar_aliased_tables[] = $table;
            }
        }
    }

// --------------------------------------------------------------------

    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     * Should not be called directly.  The get() function calls it.
     *
     * @return        string
     */
    protected function _compile_select($select_override = FALSE) {
// Combine any cached components with the current statements
        $this->_merge_cache();

// ----------------------------------------------------------------
// Write the "select" portion of the query

        if ($select_override !== FALSE) {
            $sql = $select_override;
        } else {
            $sql = (!$this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';

            if (count($this->ar_select) == 0) {
                $sql .= '*';
            } else {
// Cycle through the "select" portion of the query and prep each column name.
// The reason we protect identifiers here rather then in the select() function
// is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->ar_select as $key => $val) {
                    $no_escape = isset($this->ar_no_escape[$key]) ? $this->ar_no_escape[$key] : NULL;
                    $this->ar_select[$key] = $this->_protect_identifiers($val, FALSE, $no_escape);
                }

                $sql .= implode(', ', $this->ar_select);
            }
        }

// ----------------------------------------------------------------
// Write the "FROM" portion of the query

        if (count($this->ar_from) > 0) {
            $sql .= "\nFROM ";

            $sql .= $this->_from_tables($this->ar_from);
        }

// ----------------------------------------------------------------
// Write the "JOIN" portion of the query

        if (count($this->ar_join) > 0) {
            $sql .= "\n";

            $sql .= implode("\n", $this->ar_join);
        }

// ----------------------------------------------------------------
// Write the "WHERE" portion of the query

        if (count($this->ar_where) > 0 OR count($this->ar_like) > 0) {
            $sql .= "\nWHERE ";
        }

        $sql .= implode("\n", $this->ar_where);

// ----------------------------------------------------------------
// Write the "LIKE" portion of the query

        if (count($this->ar_like) > 0) {
            if (count($this->ar_where) > 0) {
                $sql .= "\nAND ";
            }

            $sql .= implode("\n", $this->ar_like);
        }

// ----------------------------------------------------------------
// Write the "GROUP BY" portion of the query

        if (count($this->ar_groupby) > 0) {
            $sql .= "\nGROUP BY ";

            $sql .= implode(', ', $this->ar_groupby);
        }

// ----------------------------------------------------------------
// Write the "HAVING" portion of the query

        if (count($this->ar_having) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $this->ar_having);
        }

// ----------------------------------------------------------------
// Write the "ORDER BY" portion of the query

        if (count($this->ar_orderby) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $this->ar_orderby);

            if ($this->ar_order !== FALSE) {
                $sql .= ($this->ar_order == 'desc') ? ' DESC' : ' ASC';
            }
        }

// ----------------------------------------------------------------
// Write the "LIMIT" portion of the query

        if (is_numeric($this->ar_limit)) {
            $sql .= "\n";
            $sql = $this->_limit($sql, $this->ar_limit, $this->ar_offset);
        }

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param        object
     * @return        array
     */
    public function _object_to_array($object) {
        if (!is_object($object)) {
            return $object;
        }

        $array = array();
        foreach (get_object_vars($object) as $key => $val) {
// There are some built in keys we need to ignore for this conversion
            if (!is_object($val) && !is_array($val) && $key != '_parent_name') {
                $array[$key] = $val;
            }
        }

        return $array;
    }

// --------------------------------------------------------------------

    /**
     * Object to Array
     *
     * Takes an object as input and converts the class variables to array key/vals
     *
     * @param        object
     * @return        array
     */
    public function _object_to_array_batch($object) {
        if (!is_object($object)) {
            return $object;
        }

        $array = array();
        $out = get_object_vars($object);
        $fields = array_keys($out);

        foreach ($fields as $val) {
// There are some built in keys we need to ignore for this conversion
            if ($val != '_parent_name') {

                $i = 0;
                foreach ($out[$val] as $data) {
                    $array[$i][$val] = $data;
                    $i++;
                }
            }
        }

        return $array;
    }

// --------------------------------------------------------------------

    /**
     * Start Cache
     *
     * Starts AR caching
     *
     * @return        void
     */
    public function start_cache() {
        $this->ar_caching = TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Stop Cache
     *
     * Stops AR caching
     *
     * @return        void
     */
    public function stop_cache() {
        $this->ar_caching = FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Flush Cache
     *
     * Empties the AR cache
     *
     * @access        public
     * @return        void
     */
    public function flush_cache() {
        $this->_reset_run(array(
            'ar_cache_select' => array(),
            'ar_cache_from' => array(),
            'ar_cache_join' => array(),
            'ar_cache_where' => array(),
            'ar_cache_like' => array(),
            'ar_cache_groupby' => array(),
            'ar_cache_having' => array(),
            'ar_cache_orderby' => array(),
            'ar_cache_set' => array(),
            'ar_cache_exists' => array(),
            'ar_cache_no_escape' => array()
        ));
    }

// --------------------------------------------------------------------

    /**
     * Merge Cache
     *
     * When called, this function merges any cached AR arrays with
     * locally called ones.
     *
     * @return        void
     */
    protected function _merge_cache() {
        if (count($this->ar_cache_exists) == 0) {
            return;
        }

        foreach ($this->ar_cache_exists as $val) {
            $ar_variable = 'ar_' . $val;
            $ar_cache_var = 'ar_cache_' . $val;

            if (count($this->$ar_cache_var) == 0) {
                continue;
            }

            $this->$ar_variable = array_unique(array_merge($this->$ar_cache_var, $this->$ar_variable));
        }

// If we are "protecting identifiers" we need to examine the "from"
// portion of the query to determine if there are any aliases
        if ($this->_protect_identifiers === TRUE AND count($this->ar_cache_from) > 0) {
            $this->_track_aliases($this->ar_from);
        }

        $this->ar_no_escape = $this->ar_cache_no_escape;
    }

// --------------------------------------------------------------------

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @param        array        An array of fields to reset
     * @return        void
     */
    protected function _reset_run($ar_reset_items) {
        foreach ($ar_reset_items as $item => $default_value) {
            if (!in_array($item, $this->ar_store_array)) {
                $this->$item = $default_value;
            }
        }
    }

// --------------------------------------------------------------------

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @return        void
     */
    protected function _reset_select() {
        $ar_reset_items = array(
            'ar_select' => array(),
            'ar_from' => array(),
            'ar_join' => array(),
            'ar_where' => array(),
            'ar_like' => array(),
            'ar_groupby' => array(),
            'ar_having' => array(),
            'ar_orderby' => array(),
            'ar_wherein' => array(),
            'ar_aliased_tables' => array(),
            'ar_no_escape' => array(),
            'ar_distinct' => FALSE,
            'ar_limit' => FALSE,
            'ar_offset' => FALSE,
            'ar_order' => FALSE,
        );

        $this->_reset_run($ar_reset_items);
    }

// --------------------------------------------------------------------

    /**
     * Resets the active record "write" values.
     *
     * Called by the insert() update() insert_batch() update_batch() and delete() functions
     *
     * @return        void
     */
    protected function _reset_write() {
        $ar_reset_items = array(
            'ar_set' => array(),
            'ar_from' => array(),
            'ar_where' => array(),
            'ar_like' => array(),
            'ar_orderby' => array(),
            'ar_keys' => array(),
            'ar_limit' => FALSE,
            'ar_order' => FALSE
        );

        $this->_reset_run($ar_reset_items);
    }

}

/* End of file DB_active_rec.php */
/* Location: ./system/database/DB_active_rec.php */

function log_message($level, $msg) {/* just suppress logging */
}

/* End of file db.php */

//####################modules/db-drivers/mysql.driver.php####################{


/**
 * MySQL Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package                CodeIgniter
 * @subpackage        Drivers
 * @category        Database
 * @author                ExpressionEngine Dev Team
 * @link                http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysql_driver extends CI_DB{

    var $dbdriver = 'mysql';
// The character used for escaping
    var $_escape_char = '`';
// clause and character used for LIKE escape sequences - not used in MySQL
    var $_like_escape_str = '';
    var $_like_escape_chr = '';

    /**
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     */
    var $delete_hack = TRUE;

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    var $_count_string = 'SELECT COUNT(*) AS ';
    var $_random_keyword = ' RAND()'; // database specific random keyword
// whether SET NAMES must be used to set the character set
    var $use_set_names;

    /**
     * Non-persistent database connection
     *
     * @access        private called by the base class
     * @return        resource
     */
    function db_connect() {
        if ($this->port != '') {
            $this->hostname .= ':' . $this->port;
        }
        return @mysql_connect($this->hostname, $this->username, $this->password, TRUE);
    }

// --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @access        private called by the base class
     * @return        resource
     */
    function db_pconnect() {
        if ($this->port != '') {
            $this->hostname .= ':' . $this->port;
        }

        return @mysql_pconnect($this->hostname, $this->username, $this->password);
    }

// --------------------------------------------------------------------

    /**
     * Reconnect
     *
     * Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout
     *
     * @access        public
     * @return        void
     */
    function reconnect() {
        if (mysql_ping($this->conn_id) === FALSE) {
            $this->conn_id = FALSE;
        }
    }

// --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @access        private called by the base class
     * @return        resource
     */
    function db_select() {
        return @mysql_select_db($this->database, $this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     *
     * @access        public
     * @param        string
     * @param        string
     * @return        resource
     */
    function _db_set_charset($charset, $collation) {
        if (!isset($this->use_set_names)) {
// mysql_set_charset() requires PHP >= 5.2.3 and MySQL >= 5.0.7, use SET NAMES as fallback
            $this->use_set_names = (version_compare(PHP_VERSION, '5.2.3', '>=') && version_compare(mysql_get_server_info(), '5.0.7', '>=')) ? FALSE : TRUE;
        }

        if ($this->use_set_names === TRUE) {
            return @mysql_query("SET NAMES '" . $this->escape_str($charset) . "' COLLATE '" . $this->escape_str($collation) . "'", $this->conn_id);
        } else {
            return @mysql_set_charset($charset, $this->conn_id);
        }
    }

// --------------------------------------------------------------------

    /**
     * Version number query string
     *
     * @access        public
     * @return        string
     */
    function _version() {
        return "SELECT version() AS ver";
    }

// --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access        private called by the base class
     * @param        string        an SQL query
     * @return        resource
     */
    function _execute($sql) {
        $sql = $this->_prep_query($sql);
        return @mysql_query($sql, $this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @access        private called by execute()
     * @param        string        an SQL query
     * @return        string
     */
    function _prep_query($sql) {
// "DELETE FROM TABLE" returns 0 affected rows This hack modifies
// the query so that it returns the number of affected rows
        if ($this->delete_hack === TRUE) {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
                $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
            }
        }

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Begin Transaction
     *
     * @access        public
     * @return        bool
     */
    function trans_begin($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return TRUE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

// Reset the transaction failure flag.
// If the $test_mode flag is set to TRUE transactions will be rolled back
// even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;

        $this->simple_query('SET AUTOCOMMIT=0');
        $this->simple_query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Commit Transaction
     *
     * @access        public
     * @return        bool
     */
    function trans_commit() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('COMMIT');
        $this->simple_query('SET AUTOCOMMIT=1');
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Rollback Transaction
     *
     * @access        public
     * @return        bool
     */
    function trans_rollback() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

// When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('ROLLBACK');
        $this->simple_query('SET AUTOCOMMIT=1');
        return TRUE;
    }

// --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @access        public
     * @param        string
     * @param        bool        whether or not the string will be used in a LIKE condition
     * @return        string
     */
    function escape_str($str, $like = FALSE) {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escape_str($val, $like);
            }

            return $str;
        }

        if (function_exists('mysql_real_escape_string') AND is_resource($this->conn_id)) {
            $str = mysql_real_escape_string($str, $this->conn_id);
        } elseif (function_exists('mysql_escape_string')) {
            $str = mysql_escape_string($str);
        } else {
            $str = addslashes($str);
        }

// escape LIKE condition wildcards
        if ($like === TRUE) {
            $str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
        }

        return $str;
    }

// --------------------------------------------------------------------

    /**
     * Affected Rows
     *
     * @access        public
     * @return        integer
     */
    function affected_rows() {
        return @mysql_affected_rows($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @access        public
     * @return        integer
     */
    function insert_id() {
        return @mysql_insert_id($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @access        public
     * @param        string
     * @return        string
     */
    function count_all($table = '') {
        if ($table == '') {
            return 0;
        }

        $query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

        if ($query->num_rows() == 0) {
            return 0;
        }

        $row = $query->row();
        $this->_reset_select();
        return (int) $row->numrows;
    }

// --------------------------------------------------------------------

    /**
     * List table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access        private
     * @param        boolean
     * @return        string
     */
    function _list_tables($prefix_limit = FALSE) {
        $sql = "SHOW TABLES FROM " . $this->_escape_char . $this->database . $this->_escape_char;

        if ($prefix_limit !== FALSE AND $this->dbprefix != '') {
            $sql .= " LIKE '" . $this->escape_like_str($this->dbprefix) . "%'";
        }

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access        public
     * @param        string        the table name
     * @return        string
     */
    function _list_columns($table = '') {
        return "SHOW COLUMNS FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE);
    }

// --------------------------------------------------------------------

    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @access        public
     * @param        string        the table name
     * @return        object
     */
    function _field_data($table) {
        return "DESCRIBE " . $table;
    }

// --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access        private
     * @return        string
     */
    function _error_message() {
        return mysql_error($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access        private
     * @return        integer
     */
    function _error_number() {
        return mysql_errno($this->conn_id);
    }

// --------------------------------------------------------------------

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @access        private
     * @param        string
     * @return        string
     */
    function _escape_identifiers($item) {
        if ($this->_escape_char == '') {
            return $item;
        }

        foreach ($this->_reserved_identifiers as $id) {
            if (strpos($item, '.' . $id) !== FALSE) {
                $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.', $item);

// remove duplicates if the user already included the escape
                return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
            }
        }

        if (strpos($item, '.') !== FALSE) {
            $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.' . $this->_escape_char, $item) . $this->_escape_char;
        } else {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }

// remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
    }

// --------------------------------------------------------------------

    /**
     * From Tables
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @access        public
     * @param        type
     * @return        type
     */
    function _from_tables($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return '(' . implode(', ', $tables) . ')';
    }

// --------------------------------------------------------------------

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access        public
     * @param        string        the table name
     * @param        array        the insert keys
     * @param        array        the insert values
     * @return        string
     */
    function _insert($table, $keys, $values) {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

// --------------------------------------------------------------------

    /**
     * Replace statement
     *
     * Generates a platform-specific replace string from the supplied data
     *
     * @access        public
     * @param        string        the table name
     * @param        array        the insert keys
     * @param        array        the insert values
     * @return        string
     */
    function _replace($table, $keys, $values) {
        return "REPLACE INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

// --------------------------------------------------------------------

    /**
     * Insert_batch statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access        public
     * @param        string        the table name
     * @param        array        the insert keys
     * @param        array        the insert values
     * @return        string
     */
    function _insert_batch($table, $keys, $values) {
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES " . implode(', ', $values);
    }

// --------------------------------------------------------------------

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @access        public
     * @param        string        the table name
     * @param        array        the update data
     * @param        array        the where clause
     * @param        array        the orderby clause
     * @param        array        the limit clause
     * @return        string
     */
    function _update($table, $values, $where, $orderby = array(), $limit = FALSE) {
        foreach ($values as $key => $val) {
            $valstr[] = $key . ' = ' . $val;
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        $orderby = (count($orderby) >= 1) ? ' ORDER BY ' . implode(", ", $orderby) : '';

        $sql = "UPDATE " . $table . " SET " . implode(', ', $valstr);

        $sql .= ($where != '' AND count($where) >= 1) ? " WHERE " . implode(" ", $where) : '';

        $sql .= $orderby . $limit;

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Update_Batch statement
     *
     * Generates a platform-specific batch update string from the supplied data
     *
     * @access        public
     * @param        string        the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @return	string
     */
    function _update_batch($table, $values, $index, $where = NULL) {
        $ids = array();
        $where = ($where != '' AND count($where) >= 1) ? implode(" ", $where) . ' AND ' : '';

        foreach ($values as $key => $val) {
            $ids[] = $val[$index];

            foreach (array_keys($val) as $field) {
                if ($field != $index) {
                    $final[$field][] = 'WHEN ' . $index . ' = ' . $val[$index] . ' THEN ' . $val[$field];
                }
            }
        }

        $sql = "UPDATE " . $table . " SET ";
        $cases = '';

        foreach ($final as $k => $v) {
            $cases .= $k . ' = CASE ' . "\n";
            foreach ($v as $row) {
                $cases .= $row . "\n";
            }

            $cases .= 'ELSE ' . $k . ' END, ';
        }

        $sql .= substr($cases, 0, -2);

        $sql .= ' WHERE ' . $where . $index . ' IN (' . implode(',', $ids) . ')';

        return $sql;
    }

// --------------------------------------------------------------------

    /**
     * Truncate statement
     *
     * Generates a platform-specific truncate string from the supplied data
     * If the database does not support the truncate() command
     * This function maps to "DELETE FROM table"
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _truncate($table) {
        return "TRUNCATE " . $table;
    }

// --------------------------------------------------------------------

    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the where clause
     * @param	string	the limit clause
     * @return	string
     */
    function _delete($table, $where = array(), $like = array(), $limit = FALSE) {
        $conditions = '';

        if (count($where) > 0 OR count($like) > 0) {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $this->ar_where);

            if (count($where) > 0 && count($like) > 0) {
                $conditions .= " AND ";
            }
            $conditions .= implode("\n", $like);
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;

        return "DELETE FROM " . $table . $conditions . $limit;
    }

// --------------------------------------------------------------------

    /**
     * Limit string
     *
     * Generates a platform-specific LIMIT clause
     *
     * @access	public
     * @param	string	the sql query string
     * @param	integer	the number of rows to limit the query to
     * @param	integer	the offset value
     * @return	string
     */
    function _limit($sql, $limit, $offset) {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ", ";
        }

        return $sql . "LIMIT " . $offset . $limit;
    }

// --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    function _close($conn_id) {
        @mysql_close($conn_id);
    }

}

/* End of file mysql_driver.php */
/* Location: ./system/database/drivers/mysql/mysql_driver.php */


// --------------------------------------------------------------------

/**
 * MySQL Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysql_result extends CI_DB_result {

    /**
     * Number of rows in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_rows() {
        return @mysql_num_rows($this->result_id);
    }

// --------------------------------------------------------------------

    /**
     * Number of fields in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_fields() {
        return @mysql_num_fields($this->result_id);
    }

// --------------------------------------------------------------------

    /**
     * Fetch Field Names
     *
     * Generates an array of column names
     *
     * @access	public
     * @return	array
     */
    function list_fields() {
        $field_names = array();
        while ($field = mysql_fetch_field($this->result_id)) {
            $field_names[] = $field->name;
        }

        return $field_names;
    }

// --------------------------------------------------------------------

    /**
     * Field data
     *
     * Generates an array of objects containing field meta-data
     *
     * @access	public
     * @return	array
     */
    function field_data() {
        $retval = array();
        while ($field = mysql_fetch_object($this->result_id)) {
            preg_match('/([a-zA-Z]+)(\(\d+\))?/', $field->Type, $matches);

            $type = (array_key_exists(1, $matches)) ? $matches[1] : NULL;
            $length = (array_key_exists(2, $matches)) ? preg_replace('/[^\d]/', '', $matches[2]) : NULL;

            $F = new stdClass();
            $F->name = $field->Field;
            $F->type = $type;
            $F->default = $field->Default;
            $F->max_length = $length;
            $F->primary_key = ( $field->Key == 'PRI' ? 1 : 0 );

            $retval[] = $F;
        }

        return $retval;
    }

// --------------------------------------------------------------------

    /**
     * Free the result
     *
     * @return	null
     */
    function free_result() {
        if (is_resource($this->result_id)) {
            mysql_free_result($this->result_id);
            $this->result_id = FALSE;
        }
    }

// --------------------------------------------------------------------

    /**
     * Data Seek
     *
     * Moves the internal pointer to the desired offset.  We call
     * this internally before fetching results to make sure the
     * result set starts at zero
     *
     * @access	private
     * @return	array
     */
    function _data_seek($n = 0) {
        return mysql_data_seek($this->result_id, $n);
    }

// --------------------------------------------------------------------

    /**
     * Result - associative array
     *
     * Returns the result set as an array
     *
     * @access	private
     * @return	array
     */
    function _fetch_assoc() {
        return mysql_fetch_assoc($this->result_id);
    }

// --------------------------------------------------------------------

    /**
     * Result - object
     *
     * Returns the result set as an object
     *
     * @access	private
     * @return	object
     */
    function _fetch_object() {
        return mysql_fetch_object($this->result_id);
    }

}

/* End of file mysql_result.php */
/* Location: ./system/database/drivers/mysql/mysql_result.php */
//####################modules/db-drivers/pdo.driver.php####################{


/**
 * PDO Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_pdo_driver extends CI_DB{

	var $dbdriver = 'pdo';

	// the character used to excape - not necessary for PDO
	var $_escape_char = '';
	var $_like_escape_str;
	var $_like_escape_chr;
	

	/**
	 * The syntax to count rows is slightly different across different
	 * database engines, so this string appears in each driver and is
	 * used for the count_all() and count_all_results() functions.
	 */
	var $_count_string = "SELECT COUNT(*) AS ";
	var $_random_keyword;
	
	var $options = array();

	function __construct($params)
	{
		parent::__construct($params);

		// clause and character used for LIKE escape sequences
		if (strpos($this->hostname, 'mysql') !== FALSE)
		{
			$this->_like_escape_str = '';
			$this->_like_escape_chr = '';

			//Prior to this version, the charset can't be set in the dsn
			if(is_php('5.3.6'))
			{
				$this->hostname .= ";charset={$this->char_set}";
			}

			//Set the charset with the connection options
			$this->options['PDO::MYSQL_ATTR_INIT_COMMAND'] = "SET NAMES {$this->char_set}";
		}
		elseif (strpos($this->hostname, 'odbc') !== FALSE)
		{
			$this->_like_escape_str = " {escape '%s'} ";
			$this->_like_escape_chr = '!';
		}
		else
		{
			$this->_like_escape_str = " ESCAPE '%s' ";
			$this->_like_escape_chr = '!';
		}

		empty($this->database) OR $this->hostname .= ';dbname='.$this->database;

		$this->trans_enabled = FALSE;

		$this->_random_keyword = ' RND('.time().')'; // database specific random keyword
	}

	/**
	 * Non-persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_connect()
	{
		$this->options['PDO::ATTR_ERRMODE'] = PDO::ERRMODE_SILENT;

		return new PDO($this->hostname, $this->username, $this->password, $this->options);
	}

	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_pconnect()
	{
		$this->options['PDO::ATTR_ERRMODE'] = PDO::ERRMODE_SILENT;
		$this->options['PDO::ATTR_PERSISTENT'] = TRUE;
	
		return new PDO($this->hostname, $this->username, $this->password, $this->options);
	}

	// --------------------------------------------------------------------

	/**
	 * Reconnect
	 *
	 * Keep / reestablish the db connection if no queries have been
	 * sent for a length of time exceeding the server's idle timeout
	 *
	 * @access	public
	 * @return	void
	 */
	function reconnect()
	{
		if ($this->db->db_debug)
		{
			return $this->db->display_error('db_unsuported_feature');
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Select the database
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_select()
	{
		// Not needed for PDO
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set client character set
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	resource
	 */
	function _db_set_charset($charset, $collation)
	{
		// @todo - add support if needed
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Version number query string
	 *
	 * @access	public
	 * @return	string
	 */
	function _version()
	{
		return $this->conn_id->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

	// --------------------------------------------------------------------

	/**
	 * Execute the query
	 *
	 * @access	private called by the base class
	 * @param	string	an SQL query
	 * @return	object
	 */
	function _execute($sql)
	{
		$sql = $this->_prep_query($sql);
		$result_id = $this->conn_id->prepare($sql);
		$result_id->execute();
		
		if (is_object($result_id))
		{
			if (is_numeric(stripos($sql, 'SELECT')))
			{
				$this->affect_rows = count($result_id->fetchAll());
				$result_id->execute();
			}
			else
			{
				$this->affect_rows = $result_id->rowCount();
			}
		}
		else
		{
			$this->affect_rows = 0;
		}
		
		return $result_id;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep the query
	 *
	 * If needed, each database adapter can prep the query string
	 *
	 * @access	private called by execute()
	 * @param	string	an SQL query
	 * @return	string
	 */
	function _prep_query($sql)
	{
		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	function trans_begin($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->_trans_failure = (bool) ($test_mode === TRUE);

		return $this->conn_id->beginTransaction();
	}

	// --------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	function trans_commit()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		$ret = $this->conn->commit();
		return $ret;
	}

	// --------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	function trans_rollback()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		$ret = $this->conn_id->rollBack();
		return $ret;
	}

	// --------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @access	public
	 * @param	string
	 * @param	bool	whether or not the string will be used in a LIKE condition
	 * @return	string
	 */
	function escape_str($str, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}
		
		//Escape the string
		$str = $this->conn_id->quote($str);
		
		//If there are duplicated quotes, trim them away
		if (strpos($str, "'") === 0)
		{
			$str = substr($str, 1, -1);
		}
		
		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
			$str = str_replace(	array('%', '_', $this->_like_escape_chr),
								array($this->_like_escape_chr.'%', $this->_like_escape_chr.'_', $this->_like_escape_chr.$this->_like_escape_chr),
								$str);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @access	public
	 * @return	integer
	 */
	function affected_rows()
	{
		return $this->affect_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 * 
	 * @access	public
	 * @return	integer
	 */
	function insert_id($name=NULL)
	{
		//Convenience method for postgres insertid
		if (strpos($this->hostname, 'pgsql') !== FALSE)
		{
			$v = $this->_version();

			$table	= func_num_args() > 0 ? func_get_arg(0) : NULL;

			if ($table == NULL && $v >= '8.1')
			{
				$sql='SELECT LASTVAL() as ins_id';
			}
			$query = $this->query($sql);
			$row = $query->row();
			return $row->ins_id;
		}
		else
		{
			return $this->conn_id->lastInsertId($name);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * "Count All" query
	 *
	 * Generates a platform-specific query string that counts all records in
	 * the specified database
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function count_all($table = '')
	{
		if ($table == '')
		{
			return 0;
		}

		$query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

		if ($query->num_rows() == 0)
		{
			return 0;
		}

		$row = $query->row();
		$this->_reset_select();
		return (int) $row->numrows;
	}

	// --------------------------------------------------------------------

	/**
	 * Show table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @access	private
	 * @param	boolean
	 * @return	string
	 */
	function _list_tables($prefix_limit = FALSE)
	{
		$sql = "SHOW TABLES FROM `".$this->database."`";

		if ($prefix_limit !== FALSE AND $this->dbprefix != '')
		{
			//$sql .= " LIKE '".$this->escape_like_str($this->dbprefix)."%' ".sprintf($this->_like_escape_str, $this->_like_escape_chr);
			return FALSE; // not currently supported
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Show column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	string
	 */
	function _list_columns($table = '')
	{
		return "SHOW COLUMNS FROM ".$table;
	}

	// --------------------------------------------------------------------

	/**
	 * Field data query
	 *
	 * Generates a platform-specific query so that the column data can be retrieved
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	object
	 */
	function _field_data($table)
	{
		return "SELECT TOP 1 FROM ".$table;
	}

	// --------------------------------------------------------------------

	/**
	 * The error message string
	 *
	 * @access	private
	 * @return	string
	 */
	function _error_message()
	{
		$error_array = $this->conn_id->errorInfo();
		return $error_array[2];
	}

	// --------------------------------------------------------------------

	/**
	 * The error message number
	 *
	 * @access	private
	 * @return	integer
	 */
	function _error_number()
	{
		return $this->conn_id->errorCode();
	}

	// --------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _escape_identifiers($item)
	{
		if ($this->_escape_char == '')
		{
			return $item;
		}

		foreach ($this->_reserved_identifiers as $id)
		{
			if (strpos($item, '.'.$id) !== FALSE)
			{
				$str = $this->_escape_char. str_replace('.', $this->_escape_char.'.', $item);

				// remove duplicates if the user already included the escape
				return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
			}
		}

		if (strpos($item, '.') !== FALSE)
		{
			$str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;
			
		}
		else
		{
			$str = $this->_escape_char.$item.$this->_escape_char;
		}

		// remove duplicates if the user already included the escape
		return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
	}

	// --------------------------------------------------------------------

	/**
	 * From Tables
	 *
	 * This function implicitly groups FROM tables so there is no confusion
	 * about operator precedence in harmony with SQL standards
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function _from_tables($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = array($tables);
		}

		return (count($tables) == 1) ? $tables[0] : '('.implode(', ', $tables).')';
	}

	// --------------------------------------------------------------------

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	function _insert($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
	}
	
	// --------------------------------------------------------------------

	/**
	 * Insert_batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access  public
	 * @param   string  the table name
	 * @param   array   the insert keys
	 * @param   array   the insert values
	 * @return  string
	 */
	function _insert_batch($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ".implode(', ', $values);
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @param	array	the orderby clause
	 * @param	array	the limit clause
	 * @return	string
	 */
	function _update($table, $values, $where, $orderby = array(), $limit = FALSE)
	{
		foreach ($values as $key => $val)
		{
			$valstr[] = $key." = ".$val;
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		$orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';

		$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);

		$sql .= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" ", $where) : '';

		$sql .= $orderby.$limit;

		return $sql;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @return	string
	 */
	function _update_batch($table, $values, $index, $where = NULL)
	{
		$ids = array();
		$where = ($where != '' AND count($where) >=1) ? implode(" ", $where).' AND ' : '';

		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field != $index)
				{
					$final[$field][] =  'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
				}
			}
		}

		$sql = "UPDATE ".$table." SET ";
		$cases = '';

		foreach ($final as $k => $v)
		{
			$cases .= $k.' = CASE '."\n";
			foreach ($v as $row)
			{
				$cases .= $row."\n";
			}

			$cases .= 'ELSE '.$k.' END, ';
		}

		$sql .= substr($cases, 0, -2);

		$sql .= ' WHERE '.$where.$index.' IN ('.implode(',', $ids).')';

		return $sql;
	}


	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 * If the database does not support the truncate() command
	 * This function maps to "DELETE FROM table"
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	string
	 */
	function _truncate($table)
	{
		return $this->_delete($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the where clause
	 * @param	string	the limit clause
	 * @return	string
	 */
	function _delete($table, $where = array(), $like = array(), $limit = FALSE)
	{
		$conditions = '';

		if (count($where) > 0 OR count($like) > 0)
		{
			$conditions = "\nWHERE ";
			$conditions .= implode("\n", $this->ar_where);

			if (count($where) > 0 && count($like) > 0)
			{
				$conditions .= " AND ";
			}
			$conditions .= implode("\n", $like);
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		return "DELETE FROM ".$table.$conditions.$limit;
	}

	// --------------------------------------------------------------------

	/**
	 * Limit string
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @access	public
	 * @param	string	the sql query string
	 * @param	integer	the number of rows to limit the query to
	 * @param	integer	the offset value
	 * @return	string
	 */
	function _limit($sql, $limit, $offset)
	{
		if (strpos($this->hostname, 'cubrid') !== FALSE || strpos($this->hostname, 'sqlite') !== FALSE)
		{
			if ($offset == 0)
			{
				$offset = '';
			}
			else
			{
				$offset .= ", ";
			}

			return $sql."LIMIT ".$offset.$limit;
		}
		else
		{
			$sql .= "LIMIT ".$limit;

			if ($offset > 0)
			{
				$sql .= " OFFSET ".$offset;
			}
			
			return $sql;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @access	public
	 * @param	resource
	 * @return	void
	 */
	function _close($conn_id)
	{
		$this->conn_id = null;
	}


}



/* End of file pdo_driver.php */


/**
 * PDO Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_pdo_result extends CI_DB_result {

	public $num_rows;

	/**
	 * Number of rows in the result set
	 *
	 * @return	int
	 */
	public function num_rows()
	{
		if (is_int($this->num_rows))
		{
			return $this->num_rows;
		}
		elseif (($this->num_rows = $this->result_id->rowCount()) > 0)
		{
			return $this->num_rows;
		}

		$this->num_rows = count($this->result_id->fetchAll());
		$this->result_id->execute();
		return $this->num_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Number of fields in the result set
	 *
	 * @access	public
	 * @return	integer
	 */
	function num_fields()
	{
		return $this->result_id->columnCount();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Field Names
	 *
	 * Generates an array of column names
	 *
	 * @access	public
	 * @return	array
	 */
	function list_fields()
	{
		if ($this->db->db_debug)
		{
			return $this->db->display_error('db_unsuported_feature');
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Field data
	 *
	 * Generates an array of objects containing field meta-data
	 *
	 * @access	public
	 * @return	array
	 */
	function field_data()
	{
		$data = array();
	
		try
		{
			for($i = 0; $i < $this->num_fields(); $i++)
			{
				$data[] = $this->result_id->getColumnMeta($i);
			}
			
			return $data;
		}
		catch (Exception $e)
		{
			if ($this->db->db_debug)
			{
				return $this->db->display_error('db_unsuported_feature');
			}
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Free the result
	 *
	 * @return	null
	 */
	function free_result()
	{
		if (is_object($this->result_id))
		{
			$this->result_id = FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Data Seek
	 *
	 * Moves the internal pointer to the desired offset.  We call
	 * this internally before fetching results to make sure the
	 * result set starts at zero
	 *
	 * @access	private
	 * @return	array
	 */
	function _data_seek($n = 0)
	{
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Result - associative array
	 *
	 * Returns the result set as an array
	 *
	 * @access	private
	 * @return	array
	 */
	function _fetch_assoc()
	{
		return $this->result_id->fetch(PDO::FETCH_ASSOC);
	}

	// --------------------------------------------------------------------

	/**
	 * Result - object
	 *
	 * Returns the result set as an object
	 *
	 * @access	private
	 * @return	object
	 */
	function _fetch_object()
	{	
		return $this->result_id->fetchObject();
	}

}


/* End of file pdo_result.php */
//####################modules/db-drivers/sqlite3.driver.php####################{


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
 * @since		Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
// SQLite3 PDO driver v.0.02 by Xintrea
// Tested on CodeIgniter 1.7.1
// Based on CI_DB_pdo_driver class v.0.1
// Warning! This PDO driver work with SQLite3 only!

/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright  Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 2.1.4
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * PDO Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		Dready
 * @link		http://dready.jexiste.fr/dotclear/
 */
class CI_DB_sqlite3_driver extends CI_DB {

// Added by Xi
    var $dbdriver = 'pdo';
    var $_escape_char = ''; // The character used to escape with - not needed for SQLite
    var $conn_id;
    var $_random_keyword = ' Random()'; // database specific random keyword
// clause and character used for LIKE escape sequences - not used in MySQL
    var $_like_escape_str = '';
    var $_like_escape_chr = '';

    /**
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     */
    var $delete_hack = TRUE;

    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     */
    var $_count_string = 'SELECT COUNT(*) AS ';
// whether SET NAMES must be used to set the character set
    var $use_set_names;

    /**
     * Non-persistent database connection
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_connect() {
        $conn_id = false;
        try {
            $conn_id = new PDO($this->database, $this->username, $this->password);
            log_message('debug', "PDO driver connecting " . $this->database);
        } catch (PDOException $e) {
            log_message('debug', 'merde');
            log_message('error', $e->getMessage());
            if ($this->db_debug) {
                $this->display_error($e->getMessage(), '', TRUE);
            }
        }
        log_message('debug', print_r($conn_id, true));
        if ($conn_id) {
            log_message('debug', 'PDO driver connection ok');
        }

        // Added by Xi
        $this->conn_id = $conn_id;

        return $conn_id;
    }

    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _list_columns($table = '') {
        return "PRAGMA table_info('" . $this->_protect_identifiers($table, TRUE, NULL, FALSE) . "') ";
    }

    // --------------------------------------------------------------------

    /**
     * Persistent database connection
     *
     * @access	private, called by the base class
     * @return	resource
     */
    function db_pconnect() {
        // For SQLite architecture can not enable persistent connection
        return $this->db_connect();

        /*
          $conn_id = false;
          try {
          $conn_id = new PDO ($this->database, $this->username, $this->password, array(PDO::ATTR_PERSISTENT => true) );
          } catch (PDOException $e) {
          log_message('error', $e->getMessage());
          if ($this->db_debug)
          {
          $this->display_error($e->getMessage(), '', TRUE);
          }
          }

          // Added by Xi
          $this->conn_id=$conn_id;

          return $conn_id;
         */
    }

    // --------------------------------------------------------------------

    /**
     * Select the database
     *
     * @access	private called by the base class
     * @return	resource
     */
    function db_select() {
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * @access	private, called by the base class
     * @param	string	an SQL query
     * @return	resource
     */
    function _execute($sql) {
        $sql = $this->_prep_query($sql);
        log_message('debug', 'SQL : ' . $sql);
        return @$this->conn_id->query($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @access	private called by execute()
     * @param	string	an SQL query
     * @return	string
     */
    function &_prep_query($sql) {
        return $sql;
    }

// Modify by Xi
    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @access	public
     * @param	string
     * @return	integer
     */
    function escape($str) {
        switch (gettype($str)) {
            case 'string' : $str = "'" . $this->escape_str($str) . "'";
                break;
            case 'boolean' : $str = ($str === FALSE) ? 0 : 1;
                break;
            default : $str = ($str === NULL) ? 'NULL' : $str;
                break;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Escape String
     *
     * @access	public
     * @param	string
     * @return	string
     */
    /*
      function escape_str($str)
      {
      if (get_magic_quotes_gpc())
      {
      $str = stripslashes($str);
      }
      return $this->conn_id->quote($str);
      }
     */
    // --------------------------------------------------------------------
// Added by Xi
    /**
     * Escape String         
     *         
     * @access      public         
     * @param       string         
     * @return      string         
     */
    function escape_str($str) {
        return sqlite_escape_string($str);
    }

// Added by Xi
    /**     * Escape the SQL Identifiers * 
     * This function escapes column and table names * 
     * @accessprivate 
     * @paramstring 
     * @returnstring */
    function _escape_identifiers($item) {
        if ($this->_escape_char == '') {
            return $item;
        }

        foreach ($this->_reserved_identifiers as $id) {
            if (strpos($item, '.' . $id) !== FALSE) {
                $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.', $item);

                // remove duplicates if the user already included the escape
                return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
            }
        }

        if (strpos($item, '.') !== FALSE) {
            $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.' . $this->_escape_char, $item) . $this->_escape_char;
        } else {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }

        // remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
    }

// Add by Xi
    /**
     * Begin Transaction
     *
     * @access	public
     * @return	bool		
     */
    function trans_begin($test_mode = FALSE) {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        // Reset the transaction failure flag.
        // If the $test_mode flag is set to TRUE transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;

        $this->simple_query('BEGIN TRANSACTION');
        return TRUE;
    }

    // --------------------------------------------------------------------
// Add by Xi
    /**
     * Commit Transaction
     *
     * @access	public
     * @return	bool		
     */
    function trans_commit() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('COMMIT');
        return TRUE;
    }

    // --------------------------------------------------------------------
// Add by Xi
    /**
     * Rollback Transaction
     *
     * @access	public
     * @return	bool		
     */
    function trans_rollback() {
        if (!$this->trans_enabled) {
            return TRUE;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_trans_depth > 0) {
            return TRUE;
        }

        $this->simple_query('ROLLBACK');
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access	public
     * @param	resource
     * @return	void
     */
    function destroy($conn_id) {
        $conn_id = null;
    }

    // --------------------------------------------------------------------

    /**
     * Insert ID
     *
     * @access	public
     * @return	integer
     */
    function insert_id() {
        return @$this->conn_id->lastInsertId();
    }

    // --------------------------------------------------------------------

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @access	public
     * @param	string
     * @return	string
     */
    function count_all($table = '') {
        if ($table == '')
            return '0';

        $query = $this->query("SELECT COUNT(*) AS numrows FROM `" . $table . "`");

        if ($query->num_rows() == 0)
            return '0';

        $row = $query->row();
        return $row->numrows;
    }

    // --------------------------------------------------------------------

    /**
     * The error message string
     *
     * @access	private
     * @return	string
     */
    function _error_message() {
        $infos = $this->conn_id->errorInfo();
        return $infos[2];
    }

    // --------------------------------------------------------------------

    /**
     * The error message number
     *
     * @access	private
     * @return	integer
     */
    function _error_number() {
        $infos = $this->conn_id->errorInfo();
        return $infos[1];
    }

    // --------------------------------------------------------------------

    /**
     * Version number query string
     *
     * @access	public
     * @return	string
     */
    function version() {
        return $this->conn_id->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
    }

    // --------------------------------------------------------------------

    /**
     * Escape Table Name
     *
     * This function adds backticks if the table name has a period
     * in it. Some DBs will get cranky unless periods are escaped
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function escape_table($table) {
        if (stristr($table, '.')) {
            $table = preg_replace("/\./", "`.`", $table);
        }

        return $table;
    }

    // --------------------------------------------------------------------

    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @access	public
     * @param	string	the table name
     * @return	object
     */
    function _field_data($table) {
        $sql = "SELECT * FROM " . $this->escape_table($table) . " LIMIT 1";
        $query = $this->query($sql);
        return $query->field_data();
    }

    // --------------------------------------------------------------------

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the insert keys
     * @param	array	the insert values
     * @return	string
     */
    function _insert($table, $keys, $values) {
        return "INSERT INTO " . $this->escape_table($table) . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    // --------------------------------------------------------------------

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the update data
     * @param	array	the where clause
     * @return	string
     */
    function _update($table, $values, $where) {
        foreach ($values as $key => $val) {
            $valstr[] = $key . " = " . $val;
        }

        return "UPDATE " . $this->escape_table($table) . " SET " . implode(', ', $valstr) . " WHERE " . implode(" ", $where);
    }

    // --------------------------------------------------------------------

    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @access	public
     * @param	string	the table name
     * @param	array	the where clause
     * @return	string
     */
    function _delete($table, $where) {
        return "DELETE FROM " . $this->escape_table($table) . " WHERE " . implode(" ", $where);
    }

    // --------------------------------------------------------------------

    /**
     * Show table query
     *
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access	public
     * @return	string
     */
    function _show_tables() {
        return "SELECT name from sqlite_master WHERE type='table'";
    }

    // --------------------------------------------------------------------

    /**
     * Show columnn query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access	public
     * @param	string	the table name
     * @return	string
     */
    function _show_columns($table = '') {
        // Not supported
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Limit string
     *
     * Generates a platform-specific LIMIT clause
     *
     * @access	public
     * @param	string	the sql query string
     * @param	integer	the number of rows to limit the query to
     * @param	integer	the offset value
     * @return	string
     */
    function _limit($sql, $limit, $offset) {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ", ";
        }

        return $sql . "LIMIT " . $offset . $limit;
    }

// Commented by Xi
    /**
     * COPY FROM sqlite_driver.php
     * Protect Identifiers ... contributed/requested by CodeIgniter user: quindo
     *
     * This function adds backticks if appropriate based on db type
     *
     * @access  private
     * @param   mixed   the item to escape
     * @param   boolean only affect the first word
     * @return  mixed   the item with backticks
     */
    /*
      function _protect_identifiers($item, $first_word_only = FALSE)
      {
      if (is_array($item))
      {
      $escaped_array = array();

      foreach($item as $k=>$v)
      {
      $escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v, $first_word_only);
      }

      return $escaped_array;
      }

      // This function may get "item1 item2" as a string, and so
      // we may need "item1 item2" and not "item1 item2"
      if (ctype_alnum($item) === FALSE)
      {
      if (strpos($item, '.') !== FALSE)
      {
      $aliased_tables = implode(".",$this->ar_aliased_tables).'.';
      $table_name =  substr($item, 0, strpos($item, '.')+1);
      $item = (strpos($aliased_tables, $table_name) !== FALSE) ? $item = $item : $this->dbprefix.$item;
      }

      // This function may get "field >= 1", and need it to return "field >= 1"
      $lbound = ($first_word_only === TRUE) ? '' : '|\s|\(';

      $item = preg_replace('/(^'.$lbound.')([\w\d\-\_]+?)(\s|\)|$)/iS', '$1$2$3', $item);
      }
      else
      {
      return "{$item}";
      }

      $exceptions = array('AS', '/', '-', '%', '+', '*');

      foreach ($exceptions as $exception)
      {
      if (stristr($item, " {$exception} ") !== FALSE)
      {
      $item = preg_replace('/ ('.preg_quote($exception).') /i', ' $1 ', $item);
      }
      }
      return $item;
      }
     */

    /**
     * From Tables ... contributed/requested by CodeIgniter user: quindo
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @access  public
     * @param   type
     * @return  type
     */
    function _from_tables($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return implode(', ', $tables);
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     * contributed/requested by CodeIgniter user:  jtiai
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    resource
     */
    function db_set_charset($charset, $collation) {
        // TODO - add support if needed
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access    public
     * @param    resource
     * @return    void
     */
    function _close($conn_id) {
        // Do nothing since PDO don't have close
    }

    /**
     * List table query    
     *    
     * Generates a platform-specific query string so that the table names can be fetched    
     *    
     * @access      private    
     * @param       boolean    
     * @return      string    
     */
    function _list_tables($prefix_limit = FALSE) {
        $sql = "SELECT name from sqlite_master WHERE type='table'";

        if ($prefix_limit !== FALSE AND $this->dbprefix != '') {
            $sql .= " AND 'name' LIKE '" . $this->dbprefix . "%'";
        }

        return $sql;
    }

}

/**
 * PDO Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		Dready
 * @link			http://dready.jexiste.fr/dotclear/
 */
class CI_DB_sqlite3_result extends CI_DB_result {

    var $pdo_results = '';
    var $pdo_index = 0;

    /**
     * Number of rows in the result set
     *
     * pfff... that's ugly !!!!!!!
     *
     * PHP manual for PDO tell us about nom_rows :
     * "For most databases, PDOStatement::rowCount() does not return the number of rows affected by
     * a SELECT statement. Instead, use PDO::query() to issue a SELECT COUNT(*) statement with the
     * same predicates as your intended SELECT statement, then use PDOStatement::fetchColumn() to
     * retrieve the number of rows that will be returned.
     *
     * which means
     * 1/ select count(*) as c from table where $where
     * => numrows
     * 2/ select * from table where $where
     * => treatment
     *
     * Holy cow !
     *
     * @access	public
     * @return	integer
     */
    function num_rows() {
        if (!$this->pdo_results) {
            $this->pdo_results = $this->result_id->fetchAll(PDO::FETCH_ASSOC);
        }
        return sizeof($this->pdo_results);
    }

    // --------------------------------------------------------------------

    /**
     * Number of fields in the result set
     *
     * @access	public
     * @return	integer
     */
    function num_fields() {
        if (is_array($this->pdo_results)) {
            return sizeof($this->pdo_results[$this->pdo_index]);
        } else {
            return $this->result_id->columnCount();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Field data
     *
     * Generates an array of objects containing field meta-data
     *
     * @access	public
     * @return	array
     */
    /* 	function field_data()
      {
      $retval = array();
      for ($i = 0; $i < $this->num_fields(); $i++)
      {
      $F 				= new CI_DB_field();
      $F->name 		= sqlite_field_name($this->result_id, $i);
      $F->type 		= 'varchar';
      $F->max_length	= 0;
      $F->primary_key = 0;
      $F->default		= '';

      $retval[] = $F;
      }

      return $retval;
      } */

    // --------------------------------------------------------------------

    /**
     * Result - associative array
     *
     * Returns the result set as an array
     *
     * @access	private
     * @return	array
     */
    function _fetch_assoc() {
        if (is_array($this->pdo_results)) {
            $i = $this->pdo_index;
            $this->pdo_index++;
            if (isset($this->pdo_results[$i]))
                return $this->pdo_results[$i];
            return null;
        }
        return $this->result_id->fetch(PDO::FETCH_ASSOC);
    }

    // --------------------------------------------------------------------

    /**
     * Result - object
     *
     * Returns the result set as an object
     *
     * @access	private
     * @return	object
     */
    function _fetch_object() {
        if (is_array($this->pdo_results)) {
            $i = $this->pdo_index;
            $this->pdo_index++;
            if (isset($this->pdo_results[$i])) {
                $back = '';
                foreach ($this->pdo_results[$i] as $key => $val) {
                    $back->$key = $val;
                }
                return $back;
            }
            return null;
        }
        return $this->result_id->fetch(PDO::FETCH_OBJ);
    }

}

/* End of file sqlite3.php */

//####################modules/WoniuHelper.php####################{


/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
function trigger404($msg = '<h1>Not Found</h1>') {
    global $system;
    header('HTTP/1.1 404 NotFound');
    if (!empty($system['error_page_404']) && file_exists($system['error_page_404'])) {
        include $system['error_page_404'];
    } else {
        echo $msg;
    }
    exit();
}

function trigger500($msg = '<h1>Server Error</h1>') {
    global $system;
    header('HTTP/1.1 500 Server Error');
    if (!empty($system['error_page_50x']) && file_exists(dirname(__FILE__) . '/' . $system['error_page_50x'])) {
        include dirname(__FILE__) . '/' . $system['error_page_50x'];
    } else {
        echo $msg;
    }
    exit();
}

function woniuException($exception) {
    $errno= $exception->getCode();
    $errfile = pathinfo($exception->getFile(), PATHINFO_FILENAME);
    $errline = $exception->getLine();
    $errstr = $exception->getMessage();
    @ob_clean();
    trigger500(format_error($errno, $errstr, $errfile, $errline));
}

function fatal_handler() {
    $errfile = "unknown file";
    $errstr = "shutdown";
    $errno = E_CORE_ERROR;
    $errline = 0;
    $error = error_get_last();
    if ($error !== NULL && isset($error["type"]) && ($error["type"] === E_ERROR || ($error['type'] === E_USER_ERROR))) {
        $errno = $error["type"];
        $errfile = pathinfo($error["file"], PATHINFO_FILENAME);
        $errline = $error["line"];
        $errstr = $error["message"];
        @ob_clean();
        trigger500(format_error($errno, $errstr, $errfile, $errline));
    }
}

function format_error($errno, $errstr, $errfile, $errline) {
//    $trace = print_r(debug_backtrace(false), true);
    $content = "<table><tbody>";
    $content .= "<tr valign='top'><td><b>Error</b></td><td>:" . nl2br($errstr) . "</td></tr>";
    $content .= "<tr valign='top'><td><b>Errno</b></td><td>:$errno</td></tr>";
    $content .= "<tr valign='top'><td><b>File</b></td><td>:$errfile</td></tr>";
    $content .= "<tr valign='top'><td><b>Line</b></td><td>:$errline</td></tr>";
//    $content .= "<tr valign='top'><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
    $content .= '</tbody></table>';
    return $content;
}

function stripslashes_all() {
    if (!get_magic_quotes_gpc()) {
        return;
    }
    $strip_list = array('_GET', '_POST', '_COOKIE');
    foreach ($strip_list as $val) {
        global $$val;
        $$val = stripslashes2($$val);
    }
}

#过滤魔法转义，参数可以是字符串或者数组，支持嵌套数组

function stripslashes2($var) {
    if (!get_magic_quotes_gpc()) {
        return $var;
    }
    if (is_array($var)) {
        foreach ($var as $key => $val) {
            if (is_array($val)) {
                $var[$key] = stripslashes2($val);
            } else {
                $var[$key] = stripslashes($val);
            }
        }
    } elseif (is_string($var)) {
        $var = stripslashes($var);
    }
    return $var;
}

function is_php($version = '5.0.0') {
    static $_is_php;
    $version = (string) $version;

    if (!isset($_is_php[$version])) {
        $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
    }

    return $_is_php[$version];
}

/**
 * 强制下载
 * 经过修改，支持中文名称
 * Generates headers that force a download to happen
 *
 * @access    public
 * @param    string    filename
 * @param    mixed    the data to be downloaded
 * @return    void
 */
function force_download($filename = '', $data = '') {
    if ($filename == '' OR $data == '') {
        return FALSE;
    }
    # Try to determine if the filename includes a file extension.
    # We need it in order to set the MIME type
    if (FALSE === strpos($filename, '.')) {
        return FALSE;
    }
    # Grab the file extension
    $x = explode('.', $filename);
    $extension = end($x);
    # Load the mime types
    $mimes = array('hqx' => 'application/mac-binhex40', 'cpt' => 'application/mac-compactpro', 'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'), 'bin' => 'application/macbinary', 'dms' => 'application/octet-stream', 'lha' => 'application/octet-stream', 'lzh' => 'application/octet-stream', 'exe' => array('application/octet-stream', 'application/x-msdownload'), 'class' => 'application/octet-stream', 'psd' => 'application/x-photoshop', 'so' => 'application/octet-stream', 'sea' => 'application/octet-stream', 'dll' => 'application/octet-stream', 'oda' => 'application/oda', 'pdf' => array('application/pdf', 'application/x-download'), 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript', 'smi' => 'application/smil', 'smil' => 'application/smil', 'mif' => 'application/vnd.mif', 'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'), 'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'), 'wbxml' => 'application/wbxml', 'wmlc' => 'application/wmlc', 'dcr' => 'application/x-director', 'dir' => 'application/x-director', 'dxr' => 'application/x-director', 'dvi' => 'application/x-dvi', 'gtar' => 'application/x-gtar', 'gz' => 'application/x-gzip', 'php' => 'application/x-httpd-php', 'php4' => 'application/x-httpd-php', 'php3' => 'application/x-httpd-php', 'phtml' => 'application/x-httpd-php', 'phps' => 'application/x-httpd-php-source', 'js' => 'application/x-javascript', 'swf' => 'application/x-shockwave-flash', 'sit' => 'application/x-stuffit', 'tar' => 'application/x-tar', 'tgz' => array('application/x-tar', 'application/x-gzip-compressed'), 'xhtml' => 'application/xhtml+xml', 'xht' => 'application/xhtml+xml', 'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'), 'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mpga' => 'audio/mpeg', 'mp2' => 'audio/mpeg', 'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'), 'aif' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio', 'rpm' => 'audio/x-pn-realaudio-plugin', 'ra' => 'audio/x-realaudio', 'rv' => 'video/vnd.rn-realvideo', 'wav' => 'audio/x-wav', 'bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => array('image/jpeg', 'image/pjpeg'), 'jpg' => array('image/jpeg', 'image/pjpeg'), 'jpe' => array('image/jpeg', 'image/pjpeg'), 'png' => array('image/png', 'image/x-png'), 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'css' => 'text/css', 'html' => 'text/html', 'htm' => 'text/html', 'shtml' => 'text/html', 'txt' => 'text/plain', 'text' => 'text/plain', 'log' => array('text/plain', 'text/x-log'), 'rtx' => 'text/richtext', 'rtf' => 'text/rtf', 'xml' => 'text/xml', 'xsl' => 'text/xml', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg', 'mpe' => 'video/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'movie' => 'video/x-sgi-movie', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'word' => array('application/msword', 'application/octet-stream'), 'xl' => 'application/excel', 'eml' => 'message/rfc822', 'json' => array('application/json', 'text/json'));
    # Set a default mime if we can't find it
    if (!isset($mimes[$extension])) {
        $mime = 'application/octet-stream';
    } else {
        $mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
    }
    header('Content-Type: "' . $mime . '"');
    $tmpName = $filename;
    $filename = '"' . urlencode($tmpName) . '"'; #ie中文文件名支持
    if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'firefox') != false) {
        $filename = '"' . $tmpName . '"';
    }#firefox中文文件名支持
    if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'chrome') != false) {
        $filename = urlencode($tmpName);
    }#Chrome中文文件名支持
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header("Content-Transfer-Encoding: binary");
    header('Pragma: no-cache');
    header("Content-Length: " . strlen($data));
    exit($data);
}

/* End of file Helper.php */
 
//####################modules/WoniuInput.class.php####################{


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 2.1.4
 * @createdtime       2013-07-29 16:59:11
 */
class WoniuInput {

    public static function get_post($key = null, $default = null) {
        $get = self::gpcs('_GET', $key, $default);
        return $get === null ? self::gpcs('_POST', $key, $default) : $get;
    }

    public static function get($key = null, $default = null) {
        return self::gpcs('_GET', $key, $default);
    }

    public static function post($key = null, $default = null) {
        return self::gpcs('_POST', $key, $default);
    }

    public static function cookie($key = null, $default = null) {
        return self::gpcs('_COOKIE', $key, $default);
    }

    public static function session($key = null, $default = null) {
        return self::gpcs('_SESSION', $key, $default);
    }

    public static function server($key = null, $default = null) {
        $key = strtoupper($key);
        return self::gpcs('_SERVER', $key, $default);
    }

    private static function gpcs($range, $key, $default) {
        global $$range;
        if ($key === null) {
            return $$range;
        } else {
            $range = $$range;
            return isset($range[$key]) ? $range[$key] : ( $default !== null ? $default : null);
        }
    }

}

/* Revision 618
 * ALl EXAMPLE & DOCUMENT ARE ON www.phpFastCache.com
 * IF YOU FOUND A BUG, PLEASE GO THERE: https://github.com/khoaofgod/phpfastcache/issues?state=open
 * Please feel free
 * Open new issue and I will fix it for you in 24 hours
 */

class WoniuCache extends phpFastCache {
    
}

class phpFastCache {

    // Public OPTIONS
    // Can be set by phpFastCache::$option_name = $value|array|string
    public static $storage = "auto"; // PDO | mpdo | Auto | Files | memcache | apc | wincache | xcache
    public static $files_cleanup_after = 1; // hour | auto clean up files after this
    public static $autosize = 40; // Megabytes
    public static $path = ""; // PATH/TO/CACHE/ default will be current path
    public static $securityKey = "cache.storage"; // phpFastCache::$securityKey = "newKey";
    public static $securityHtAccess = true; // auto create .htaccess
    public static $option = array();
    public static $server = array(array("localhost", 11211)); // for MemCache
    public static $useTmpCache = false; // use for get from Tmp Memory, will be faster in checking cache on LOOP.
    public static $debugging = false; // turn true for debugging
    // NOTHING TO CHANGE FROM HERE
    private static $step_debugging = 0;
    private static $Tmp = array();
    private static $supported_api = array("pdo", "mpdo", "files", "memcache", "memcached", "apc", "xcache", "wincache");
    private static $filename = "pdo.caching";
    private static $table = "objects";
    private static $autodb = "";
    private static $multiPDO = array();
    public static $sys = array();
    private static $checked = array(
        "path" => false,
        "servers" => array(),
        "config_file" => "",
    );
    private static $objects = array(
        "memcache" => "",
        "memcached" => "",
        "pdo" => "",
    );

    private static function getOS() {
        $os = array(
            "os" => PHP_OS,
            "php" => PHP_SAPI,
            "system" => php_uname(),
            "unique" => md5(php_uname() . PHP_OS . PHP_SAPI)
        );
        return $os;
    }

    public static function systemInfo() {
        // self::startDebug(self::$sys,"Check Sys",__LINE__,__FUNCTION__);

        if (count(self::$sys) == 0) {

            // self::startDebug("Start System Info");

            self::$sys['os'] = self::getOS();

            self::$sys['errors'] = array();
            self::$sys['storage'] = "";
            self::$sys['method'] = "pdo";
            self::$sys['drivers'] = array(
                "apc" => false,
                "xcache" => false,
                "memcache" => false,
                "memcached" => false,
                "wincache" => false,
                "pdo" => false,
                "mpdo" => false,
                "files" => false,
            );



            // Check apc
            if (extension_loaded('apc') && ini_get('apc.enabled')) {
                self::$sys['drivers']['apc'] = true;
                self::$sys['storage'] = "memory";
                self::$sys['method'] = "apc";
            }

            // Check xcache
            if (extension_loaded('xcache') && function_exists("xcache_get")) {
                self::$sys['drivers']['xcache'] = true;
                self::$sys['storage'] = "memory";
                self::$sys['method'] = "xcache";
            }

            if (extension_loaded('wincache') && function_exists("wincache_ucache_set")) {
                self::$sys['drivers']['wincache'] = true;
                self::$sys['storage'] = "memory";
                self::$sys['method'] = "wincache";
            }

            // Check memcache
            if (function_exists("memcache_connect")) {
                self::$sys['drivers']['memcache'] = true;

                try {
                    memcache_connect("127.0.0.1");
                    self::$sys['storage'] = "memory";
                    self::$sys['method'] = "memcache";
                } catch (Exception $e) {
                    
                }
            }


            // Check memcached
            if (class_exists("memcached")) {
                self::$sys['drivers']['memcached'] = true;

                try {
                    $memcached = new memcached();
                    $memcached->addServer("127.0.0.1", "11211");
                    self::$sys['storage'] = "memory";
                    self::$sys['method'] = "memcached";
                } catch (Exception $e) {
                    
                }
            }

            if (extension_loaded('pdo_sqlite')) {
                self::$sys['drivers']['pdo'] = true;
                self::$sys['drivers']['mpdo'] = true;
            }

            if (is_writable(self::getPath(true))) {
                self::$sys['drivers']['files'] = true;
            }

            if (self::$sys['storage'] == "") {

                if (extension_loaded('pdo_sqlite')) {
                    self::$sys['storage'] = "disk";
                    self::$sys['method'] = "pdo";
                } else {

                    self::$sys['storage'] = "disk";
                    self::$sys['method'] = "files";
                }
            }



            if (self::$sys['storage'] == "disk" && !is_writable(self::getPath())) {
                self::$sys['errors'][] = "Please Create & CHMOD 0777 or any Writeable Mode for " . self::getPath();
            }
        }

        // self::startDebug(self::$sys);
        return self::$sys;
    }

    // return Folder Cache PATH
    // PATH Edit by SecurityKey
    // Auto create, Chmod and Warning
    // Revision 618
    // PHP_SAPI =  apache2handler should go to tmp
    private static function isPHPModule() {
        if (PHP_SAPI == "apache2handler") {
            return true;
        } else {
            if (strpos(PHP_SAPI, "handler") !== false) {
                return true;
            }
        }
        return false;
    }

    // Revision 618
    // Security with .htaccess
    static function htaccessGen($path = "") {
        if (self::$securityHtAccess == true) {

            if (!file_exists($path . "/.htaccess")) {
                //   echo "write me";
                $html = "order deny, allow \r\n
deny from all \r\n
allow from 127.0.0.1";
                $f = @fopen($path . "/.htaccess", "w+");
                @fwrite($f, $html);
                @fclose($f);
            } else {
                //   echo "got me";
            }
        }
    }

    private static function getPath($skip_create = false) {

        if (self::$path == '') {
            // revision 618
            if (self::isPHPModule()) {
                $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
                self::$path = $tmp_dir;
            } else {
                self::$path = dirname(__FILE__);
            }
        }

        if ($skip_create == false && self::$checked['path'] == false) {
            if (!file_exists(self::$path . "/" . self::$securityKey . "/") || !is_writable(self::$path . "/" . self::$securityKey . "/")) {
                if (!file_exists(self::$path . "/" . self::$securityKey . "/")) {
                    @mkdir(self::$path . "/" . self::$securityKey . "/", 0777);
                }
                if (!is_writable(self::$path . "/" . self::$securityKey . "/")) {
                    @chmod(self::$path . "/" . self::$securityKey . "/", 0777);
                }
                if (!file_exists(self::$path . "/" . self::$securityKey . "/") || !is_writable(self::$path . "/" . self::$securityKey . "/")) {
                    die("Sorry, Please create " . self::$path . "/" . self::$securityKey . "/ and SET Mode 0777 or any Writable Permission!");
                }
            }

            self::$checked['path'] = true;
            // Revision 618
            self::htaccessGen(self::$path . "/" . self::$securityKey . "/");
        }



        return self::$path . "/" . self::$securityKey . "/";
    }

    // return method automatic;
    // APC will be TOP, then Memcached, Memcache, PDO and Files
    public static function autoconfig($name = "") {
        // self::startDebug($name,"Check Name",__LINE__,__FUNCTION__);

        $cache = self::cacheMethod($name);
        if ($cache != "" && $cache != self::$storage && $cache != "auto") {
            return $cache;
        }

        // self::startDebug($cache,"Check Cache",__LINE__,__FUNCTION__);

        $os = self::getOS();
        // self::startDebug(self::$storage,"User Set",__LINE__,__FUNCTION__);
        if (self::$storage == "" || self::$storage == "auto") {
            // self::startDebug(self::$storage,"User Set Auto",__LINE__,__FUNCTION__);

            if (extension_loaded('apc') && ini_get('apc.enabled') && strpos(PHP_SAPI, "CGI") === false) {

                self::$sys['drivers']['apc'] = true;
                self::$sys['storage'] = "memory";
                self::$sys['method'] = "apc";

                // self::startDebug(self::$sys,"GOT APC",__LINE__,__FUNCTION__);
            } elseif (extension_loaded('xcache')) {
                self::$sys['drivers']['xcache'] = true;
                self::$sys['storage'] = "memory";
                self::$sys['method'] = "xcache";
                // self::startDebug(self::$sys,"GOT XCACHE",__LINE__,__FUNCTION__);
            } else {
                // fix PATH for existing
                $reconfig = false;
                // self::startDebug(self::getPath()."/config.".$os['unique'].".cache.ini","CHECK CONFIG FILE",__LINE__,__FUNCTION__);


                if (file_exists(self::getPath() . "/config." . $os['unique'] . ".cache.ini")) {
                    $info = self::decode(file_get_contents(self::getPath() . "/config." . $os['unique'] . ".cache.ini"));

                    // self::startDebug($info,"CHECK INFO",__LINE__,__FUNCTION__);

                    if (!isset($info['value'])) {
                        $reconfig = true;
                    } else {
                        $info = $info['value'];
                        self::$sys = $info;
                    }
                } else {

                    $info = self::systemInfo();
                    // self::startDebug($info,"CHECK INFO BY SYSTEM INFO",__LINE__,__FUNCTION__);
                }

                if (isset($info['os']['unique'])) {

                    if ($info['os']['unique'] != $os['unique']) {
                        $reconfig = true;
                    }
                } else {
                    $reconfig = true;
                }

                if (!file_exists(self::getPath() . "/config." . $os['unique'] . ".cache.ini") || $reconfig == true) {

                    $info = self::systemInfo();
                    self::$sys = $info;
                    // self::startDebug($info,"Check Info",__LINE__,__FUNCTION__);

                    try {
                        $f = fopen(self::getPath() . "/config." . $os['unique'] . ".cache.ini", "w+");
                        fwrite($f, self::encode($info));
                        fclose($f);
                    } catch (Exception $e) {
                        die("Please chmod 0777 " . self::getPath() . "/config." . $os['unique'] . ".cache.ini");
                    }
                } else {
                    
                }
            }



            self::$storage = self::$sys['method'];
        } else {

            if (in_array(self::$storage, array("files", "pdo", "mpdo"))) {
                self::$sys['storage'] = "disk";
            } elseif (in_array(self::$storage, array("apc", "memcache", "memcached", "wincache", "xcache"))) {
                self::$sys['storage'] = "memory";
            } else {
                self::$sys['storage'] = "";
            }

            if (self::$sys['storage'] == "" || !in_array(self::$storage, self::$supported_api)) {
                die("Don't have this Cache " . self::$storage . " In your System! Please double check!");
            }

            self::$sys['method'] = strtolower(self::$storage);
        }

        if (self::$sys['method'] == "files") {
            $last_cleanup = self::files_get("last_cleanup_cache");
            if ($last_cleanup == null) {
                self::files_cleanup();
                self::files_set("last_cleanup_cache", @date("U"), 3600 * self::$files_cleanup_after);
            }
        }

        // self::startDebug(self::$sys,"Check RETURN SYS",__LINE__,__FUNCTION__);

        return self::$sys['method'];
    }

    private static function cacheMethod($name = "") {
        $cache = self::$storage;
        if (is_array($name)) {
            $key = array_keys($name);
            $key = $key[0];
            if (in_array($key, self::$supported_api)) {
                $cache = $key;
            }
        }
        return $cache;
    }

    public static function safename($name) {
        return strtolower(preg_replace("/[^a-zA-Z0-9_\s\.]+/", "", $name));
    }

    private static function encode($value, $time_in_second = "") {
        $value = serialize(array(
            "time" => @date("U"),
            "value" => $value,
            "endin" => $time_in_second
        ));
        return $value;
    }

    private static function decode($value) {
        $x = @unserialize($value);
        if ($x == false) {
            return $value;
        } else {
            return $x;
        }
    }

    /*
     * Start Public Static
     */

    public static function cleanup($option = "") {
        $api = self::autoconfig();
        self::$Tmp = array();

        switch ($api) {
            case "pdo":
                return self::pdo_cleanup($option);
                break;
            case "mpdo":
                return self::pdo_cleanup($option);
                break;
            case "files":
                return self::files_cleanup($option);
                break;
            case "memcache":
                return self::memcache_cleanup($option);
                break;
            case "memcached":
                return self::memcached_cleanup($option);
                break;
            case "wincache":
                return self::wincache_cleanup($option);
                break;
            case "apc":
                return self::apc_cleanup($option);
                break;
            case "xcache":
                return self::xcache_cleanup($option);
                break;
            default:
                return self::pdo_cleanup($option);
                break;
        }
    }

    public static function delete($name = "string|array(db->item)") {

        $api = self::autoconfig($name);
        if (self::$useTmpCache == true) {
            $tmp_name = md5(serialize($api . $name));
            if (isset(self::$Tmp[$tmp_name])) {
                unset(self::$Tmp[$tmp_name]);
            }
        }

        switch ($api) {
            case "pdo":
                return self::pdo_delete($name);
                break;
            case "mpdo":
                return self::pdo_delete($name);
                break;
            case "files":
                return self::files_delete($name);
                break;
            case "memcache":
                return self::memcache_delete($name);
                break;
            case "memcached":
                return self::memcached_delete($name);
                break;
            case "wincache":
                return self::wincache_delete($name);
                break;
            case "apc":
                return self::apc_delete($name);
                break;
            case "xcache":
                return self::xcache_delete($name);
                break;
            default:
                return self::pdo_delete($name);
                break;
        }
    }

    public static function exists($name = "string|array(db->item)") {

        $api = self::autoconfig($name);
        switch ($api) {
            case "pdo":
                return self::pdo_exist($name);
                break;
            case "mpdo":
                return self::pdo_exist($name);
                break;
            case "files":
                return self::files_exist($name);
                break;
            case "memcache":
                return self::memcache_exist($name);
                break;
            case "memcached":
                return self::memcached_exist($name);
                break;
            case "wincache":
                return self::wincache_exist($name);
                break;
            case "apc":
                return self::apc_exist($name);
                break;
            case "xcache":
                return self::xcache_exist($name);
                break;
            default:
                return self::pdo_exist($name);
                break;
        }
    }

    public static function deleteMulti($object = array()) {
        $res = array();
        foreach ($object as $driver => $name) {
            if (!is_numeric($driver)) {
                $n = $driver . "_" . $name;
                $name = array($driver => $name);
            } else {
                $n = $name;
            }
            $res[$n] = self::delete($name);
        }
        return $res;
    }

    public static function setMulti($mname = array(), $time_in_second_for_all = 600, $skip_for_all = false) {
        $res = array();

        foreach ($mname as $object) {
            //   print_r($object);

            $keys = array_keys($object);

            if ($keys[0] != "0") {
                $k = $keys[0];
                $name = isset($object[$k]) ? array($k => $object[$k]) : "";
                $n = $k . "_" . $object[$k];
                $x = 0;
            } else {
                $name = isset($object[0]) ? $object[0] : "";
                $x = 1;
                $n = $name;
            }

            $value = isset($object[$x]) ? $object[$x] : "";
            $x++;
            $time = isset($object[$x]) ? $object[$x] : $time_in_second_for_all;
            $x++;
            $skip = isset($object[$x]) ? $object[$x] : $skip_for_all;
            $x++;

            if ($name != "" && $value != "") {
                $res[$n] = self::set($name, $value, $time, $skip);
            }
            // echo "<br> ----- <br>";
        }

        return $res;
    }

    public static function set($name, $value, $time_in_second = 600, $skip_if_existing = false) {
        $api = self::autoconfig($name);
        if (self::$useTmpCache == true) {
            $tmp_name = md5(serialize($api . $name));
            self::$Tmp[$tmp_name] = $value;
        }

        switch ($api) {
            case "pdo":
                return self::pdo_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            case "mpdo":
                return self::pdo_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            case "files":
                return self::files_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            case "memcache":
                return self::memcache_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            case "memcached":
                return self::memcached_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            case "wincache":
                return self::wincache_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            case "apc":
                return self::apc_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            case "xcache":
                return self::xcache_set($name, $value, $time_in_second, $skip_if_existing);
                break;
            default:
                return self::pdo_set($name, $value, $time_in_second, $skip_if_existing);
                break;
        }
    }

    public static function decrement($name, $step = 1) {
        $api = self::autoconfig($name);
        if (self::$useTmpCache == true) {
            $tmp_name = md5(serialize($api . $name));
            if (isset(self::$Tmp[$tmp_name])) {
                self::$Tmp[$tmp_name] = (Int) self::$Tmp[$tmp_name] - $step;
            } else {
                self::$Tmp[$tmp_name] = $step;
            }
        }
        switch ($api) {
            case "pdo":
                return self::pdo_decrement($name, $step);
                break;
            case "mpdo":
                return self::pdo_decrement($name, $step);
                break;
            case "files":
                return self::files_decrement($name, $step);
                break;
            case "memcache":
                return self::memcache_decrement($name, $step);
                break;
            case "memcached":
                return self::memcached_decrement($name, $step);
                break;
            case "wincache":
                return self::wincache_decrement($name, $step);
                break;
            case "apc":
                return self::apc_decrement($name, $step);
                break;
            case "xcache":
                return self::xcache_decrement($name, $step);
                break;
            default:
                return self::pdo_decrement($name, $step);
                break;
        }
    }

    public static function get($name) {
        $api = self::autoconfig($name);
        if (self::$useTmpCache == true) {
            $tmp_name = md5(serialize($api . $name));
            if (isset(self::$Tmp[$tmp_name])) {
                return self::$Tmp[$tmp_name];
            }
        }

        // self::startDebug($api,"API",__LINE__,__FUNCTION__);
        // for files, check it if NULL and "empty" string
        switch ($api) {
            case "pdo":
                return self::pdo_get($name);
                break;
            case "mpdo":
                return self::pdo_get($name);

                break;
            case "files":
                return self::files_get($name);
                break;
            case "memcache":
                return self::memcache_get($name);
                break;
            case "memcached":
                return self::memcached_get($name);
                break;
            case "wincache":
                return self::wincache_get($name);
                break;
            case "apc":
                return self::apc_get($name);
                break;
            case "xcache":
                return self::xcache_get($name);
                break;
            default:
                return self::pdo_get($name);
                break;
        }
    }

    public static function getMulti($object = array()) {
        $res = array();
        foreach ($object as $driver => $name) {
            if (!is_numeric($driver)) {
                $n = $driver . "_" . $name;
                $name = array($driver => $name);
            } else {
                $n = $name;
            }
            $res[$n] = self::get($name);
        }
        return $res;
    }

    public static function stats() {
        $api = self::autoconfig();
        switch ($api) {
            case "pdo":
                return self::pdo_stats();
                break;
            case "mpdo":
                return self::pdo_stats();
                break;
            case "files":
                return self::files_stats();
                break;
            case "memcache":
                return self::memcache_stats();
                break;
            case "memcached":
                return self::memcached_stats();
                break;
            case "wincache":
                return self::wincache_stats();
                break;
            case "apc":
                return self::apc_stats();
                break;
            case "xcache":
                return self::xcache_stats();
                break;
            default:
                return self::pdo_stats();
                break;
        }
    }

    public static function increment($name, $step = 1) {
        $api = self::autoconfig($name);

        if (self::$useTmpCache == true) {
            $tmp_name = md5(serialize($api . $name));
            if (isset(self::$Tmp[$tmp_name])) {
                self::$Tmp[$tmp_name] = (Int) self::$Tmp[$tmp_name] + $step;
            } else {
                self::$Tmp[$tmp_name] = $step;
            }
        }

        switch ($api) {
            case "pdo":
                return self::pdo_increment($name, $step);
                break;
            case "mpdo":
                return self::pdo_increment($name, $step);
                break;
            case "files":
                return self::files_increment($name, $step);
                break;
            case "memcache":
                return self::memcache_increment($name, $step);
                break;
            case "memcached":
                return self::memcached_increment($name, $step);
                break;
            case "wincache":
                return self::wincache_increment($name, $step);
                break;
            case "apc":
                return self::apc_increment($name, $step);
                break;
            case "xcache":
                return self::xcache_increment($name, $step);
                break;
            default:
                return self::pdo_increment($name, $step);
                break;
        }
    }

    /*
     * Begin FILES Cache Static
     * Use Files & Folders to cache
     */

    private static function files_exist($name) {
        $data = self::files_get($name);
        if ($data == null) {
            return false;
        } else {
            return true;
        }
    }

    private static function files_set($name, $value, $time_in_second = 600, $skip_if_existing = false) {

        $db = self::selectDB($name);
        $name = $db['item'];
        $folder = $db['db'];

        $path = self::getPath();
        $tmp = explode("/", $folder);
        foreach ($tmp as $dir) {
            if ($dir != "" && $dir != "." && $dir != "..") {
                $path.="/" . $dir;
                if (!file_exists($path)) {
                    mkdir($path, 0777);
                }
            }
        }

        $file = $path . "/" . $name . ".c.html";

        $write = true;
        if (file_exists($file)) {
            $data = self::decode(file_get_contents($file));
            if ($skip_if_existing == true && ((Int) $data['time'] + (Int) $data['endin'] > @date("U"))) {
                $write = false;
            }
        }

        if ($write == true) {
            try {
                $f = fopen($file, "w+");
                fwrite($f, self::encode($value, $time_in_second));
                fclose($f);
            } catch (Exception $e) {
                die("Sorry, can't write cache to file :" . $file);
            }
        }

        return $value;
    }

    private static function files_get($name) {
        $db = self::selectDB($name);
        $name = $db['item'];
        $folder = $db['db'];

        $path = self::getPath();
        $tmp = explode("/", $folder);
        foreach ($tmp as $dir) {
            if ($dir != "" && $dir != "." && $dir != "..") {
                $path.="/" . $dir;
            }
        }

        $file = $path . "/" . $name . ".c.html";

        if (!file_exists($file)) {
            return null;
        }

        $data = self::decode(file_get_contents($file));

        if (!isset($data['time']) || !isset($data['endin']) || !isset($data['value'])) {
            return null;
        }

        if ($data['time'] + $data['endin'] < @date("U")) {
            // exp
            unlink($file);
            return null;
        }

        return isset($data['value']) ? $data['value'] : null;
    }

    private static function files_stats($dir = "") {
        $total = array(
            "expired" => 0,
            "size" => 0,
            "files" => 0
        );
        if ($dir == "") {
            $dir = self::getPath();
        }
        $d = opendir($dir);
        while ($file = readdir($d)) {
            if ($file != "." && $file != "..") {
                $path = $dir . "/" . $file;
                if (is_dir($path)) {
                    $in = self::files_stats($path);
                    $total['expired'] = $total['expired'] + $in['expired'];
                    $total['size'] = $total['size'] + $in['size'];
                    $total['files'] = $total['files'] + $in['files'];
                } elseif (strpos($path, ".c.html") !== false) {
                    $data = self::decode($path);
                    if (isset($data['value']) && isset($data['time']) && isset($data['endin'])) {
                        $total['files']++;
                        if ($data['time'] + $data['endin'] < @date("U")) {
                            $total['expired']++;
                        }
                        $total['size'] = $total['size'] + filesize($path);
                    }
                }
            }
        }
        if ($total['size'] > 0) {
            $total['size'] = $total['size'] / 1024 / 1024;
        }
        return $total;
    }

    private static function files_cleanup($dir = "") {
        $total = 0;
        if ($dir == "") {
            $dir = self::getPath();
        }
        $d = opendir($dir);
        while ($file = readdir($d)) {
            if ($file != "." && $file != "..") {
                $path = $dir . "/" . $file;
                if (is_dir($path)) {
                    $total = $total + self::files_cleanup($path);
                    try {
                        @unlink($path);
                    } catch (Exception $e) {
                        // nothing;
                    }
                } elseif (strpos($path, ".c.html") !== false) {
                    $data = self::decode($path);
                    if (isset($data['value']) && isset($data['time']) && isset($data['endin'])) {
                        if ((Int) $data['time'] + (Int) $data['endin'] < @date("U")) {
                            unlink($path);
                            $total++;
                        }
                    } else {
                        unlink($path);
                        $total++;
                    }
                }
            }
        }
        return $total;
    }

    private static function files_delete($name) {
        $db = self::selectDB($name);
        $name = $db['item'];
        $folder = $db['db'];

        $path = self::getPath();
        $tmp = explode("/", $folder);
        foreach ($tmp as $dir) {
            if ($dir != "" && $dir != "." && $dir != "..") {
                $path.="/" . $dir;
            }
        }

        $file = $path . "/" . $name . ".c.html";
        if (file_exists($file)) {
            try {
                unlink($file);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    private static function files_increment($name, $step = 1) {
        $db = self::selectDB($name);
        $name = $db['item'];
        $folder = $db['db'];

        $path = self::getPath();
        $tmp = explode("/", $folder);
        foreach ($tmp as $dir) {
            if ($dir != "" && $dir != "." && $dir != "..") {
                $path.="/" . $dir;
            }
        }

        $file = $path . "/" . $name . ".c.html";
        if (!file_exists($file)) {
            self::files_set($name, $step, 3600);
            return $step;
        }

        $data = self::decode(file_get_contents($file));
        if (isset($data['time']) && isset($data['value']) && isset($data['endin'])) {
            $data['value'] = $data['value'] + $step;
            self::files_set($name, $data['value'], $data['endin']);
        }
        return $data['value'];
    }

    private static function files_decrement($name, $step = 1) {
        $db = self::selectDB($name);
        $name = $db['item'];
        $folder = $db['db'];

        $path = self::getPath();
        $tmp = explode("/", $folder);
        foreach ($tmp as $dir) {
            if ($dir != "" && $dir != "." && $dir != "..") {
                $path.="/" . $dir;
            }
        }

        $file = $path . "/" . $name . ".c.html";
        if (!file_exists($file)) {
            self::files_set($name, $step, 3600);
            return $step;
        }

        $data = self::decode(file_get_contents($file));
        if (isset($data['time']) && isset($data['value']) && isset($data['endin'])) {
            $data['value'] = $data['value'] - $step;
            self::files_set($name, $data['value'], $data['endin']);
        }
        return $data['value'];
    }

    private static function getMemoryName($name) {
        $db = self::selectDB($name);
        $name = $db['item'];
        $folder = $db['db'];
        $name = $folder . "_" . $name;

        // connect memory server
        if (self::$sys['method'] == "memcache" || $db['db'] == "memcache") {
            self::memcache_addserver();
        } elseif (self::$sys['method'] == "memcached" || $db['db'] == "memcached") {
            self::memcached_addserver();
        } elseif (self::$sys['method'] == "wincache") {
            // init WinCache here
        }

        return $name;
    }

    /*
     * Begin XCache Static
     * http://xcache.lighttpd.net/wiki/XcacheApi
     */

    private static function xcache_exist($name) {
        $name = self::getMemoryName($name);
        if (xcache_isset($name)) {
            return true;
        } else {
            return false;
        }
    }

    private static function xcache_set($name, $value, $time_in_second = 600, $skip_if_existing = false) {
        $name = self::getMemoryName($name);
        if ($skip_if_existing == true) {
            if (!self::xcache_exist($name)) {
                return xcache_set($name, $value, $time_in_second);
            }
        } else {
            return xcache_set($name, $value, $time_in_second);
        }
        return false;
    }

    private static function xcache_get($name) {

        $name = self::getMemoryName($name);

        $data = xcache_get($name);

        if ($data === false || $data == "") {
            return null;
        }
        return $data;
    }

    private static function xcache_stats() {
        try {
            return xcache_list(XC_TYPE_VAR, 100);
        } catch (Exception $e) {
            return array();
        }
    }

    private static function xcache_cleanup($option = array()) {
        xcache_clear_cache(XC_TYPE_VAR);
        return true;
    }

    private static function xcache_delete($name) {
        $name = self::getMemoryName($name);
        return xcache_unset($name);
    }

    private static function xcache_increment($name, $step = 1) {
        $orgi = $name;
        $name = self::getMemoryName($name);
        $ret = xcache_inc($name, $step);
        if ($ret === false) {
            self::xcache_set($orgi, $step, 3600);
            return $step;
        } else {
            return $ret;
        }
    }

    private static function xcache_decrement($name, $step = 1) {
        $orgi = $name;
        $name = self::getMemoryName($name);
        $ret = xcache_dec($name, $step);
        if ($ret === false) {
            self::xcache_set($orgi, $step, 3600);
            return $step;
        } else {
            return $ret;
        }
    }

    /*
     * Begin APC Static
     * http://www.php.net/manual/en/ref.apc.php
     */

    private static function apc_exist($name) {
        $name = self::getMemoryName($name);
        if (apc_exists($name)) {
            return true;
        } else {
            return false;
        }
    }

    private static function apc_set($name, $value, $time_in_second = 600, $skip_if_existing = false) {
        $name = self::getMemoryName($name);
        if ($skip_if_existing == true) {
            return apc_add($name, $value, $time_in_second);
        } else {
            return apc_store($name, $value, $time_in_second);
        }
    }

    private static function apc_get($name) {

        $name = self::getMemoryName($name);

        $data = apc_fetch($name, $bo);

        if ($bo === false) {
            return null;
        }
        return $data;
    }

    private static function apc_stats() {
        try {
            return apc_cache_info("user");
        } catch (Exception $e) {
            return array();
        }
    }

    private static function apc_cleanup($option = array()) {
        return apc_clear_cache("user");
    }

    private static function apc_delete($name) {
        $name = self::getMemoryName($name);
        return apc_delete($name);
    }

    private static function apc_increment($name, $step = 1) {
        $orgi = $name;
        $name = self::getMemoryName($name);
        $ret = apc_inc($name, $step, $fail);
        if ($ret === false) {
            self::apc_set($orgi, $step, 3600);
            return $step;
        } else {
            return $ret;
        }
    }

    private static function apc_decrement($name, $step = 1) {
        $orgi = $name;
        $name = self::getMemoryName($name);
        $ret = apc_dec($name, $step, $fail);
        if ($ret === false) {
            self::apc_set($orgi, $step, 3600);
            return $step;
        } else {
            return $ret;
        }
    }

    /*
     * Begin Memcache Static
     * http://www.php.net/manual/en/class.memcache.php
     */

    private static function memcache_addserver() {
        if (!isset(self::$checked['memcache'])) {
            self::$checked['memcache'] = array();
        }

        if (self::$objects['memcache'] == "") {
            self::$objects['memcache'] = new Memcache;

            foreach (self::$server as $server) {
                $name = isset($server[0]) ? $server[0] : "";
                $port = isset($server[1]) ? $server[1] : 11211;
                if (!in_array($server, self::$checked['memcache']) && $name != "") {
                    self::$objects['memcache']->addServer($name, $port);
                    self::$checked['memcache'][] = $name;
                }
            }
        }
    }

    private static function memcache_exist($name) {
        $x = self::memcache_get($name);
        if ($x == null) {
            return false;
        } else {
            return true;
        }
    }

    private static function memcache_set($name, $value, $time_in_second = 600, $skip_if_existing = false) {
        $orgi = $name;
        $name = self::getMemoryName($name);
        if ($skip_if_existing == false) {
            return self::$objects['memcache']->set($name, $value, false, $time_in_second);
        } else {
            return self::$objects['memcache']->add($name, $value, false, $time_in_second);
        }
    }

    private static function memcache_get($name) {
        $name = self::getMemoryName($name);
        $x = self::$objects['memcache']->get($name);
        if ($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    private static function memcache_stats() {
        self::memcache_addserver();
        return self::$objects['memcache']->getStats();
    }

    private static function memcache_cleanup($option = "") {
        self::memcache_addserver();
        self::$objects['memcache']->flush();
        return true;
    }

    private static function memcache_delete($name) {
        $name = self::getMemoryName($name);
        return self::$objects['memcache']->delete($name);
    }

    private static function memcache_increment($name, $step = 1) {
        $name = self::getMemoryName($name);
        return self::$objects['memcache']->increment($name, $step);
    }

    private static function memcache_decrement($name, $step = 1) {
        $name = self::getMemoryName($name);
        return self::$objects['memcache']->decrement($name, $step);
    }

    /*
     * Begin Memcached Static
     */

    private static function memcached_addserver() {
        if (!isset(self::$checked['memcached'])) {
            self::$checked['memcached'] = array();
        }

        if (self::$objects['memcached'] == "") {
            self::$objects['memcached'] = new Memcache;

            foreach (self::$server as $server) {
                $name = isset($server[0]) ? $server[0] : "";
                $port = isset($server[1]) ? $server[1] : 11211;
                $sharing = isset($server[2]) ? $server[2] : 0;
                if (!in_array($server, self::$checked['memcached']) && $name != "") {
                    if ($sharing > 0) {
                        self::$objects['memcached']->addServer($name, $port, $sharing);
                    } else {
                        self::$objects['memcached']->addServer($name, $port);
                    }

                    self::$checked['memcached'][] = $name;
                }
            }
        }
    }

    private static function memcached_exist($name) {
        $x = self::memcached_get($name);
        if ($x == null) {
            return false;
        } else {
            return true;
        }
    }

    private static function memcached_set($name, $value, $time_in_second = 600, $skip_if_existing = false) {
        $orgi = $name;
        $name = self::getMemoryName($name);
        if ($skip_if_existing == false) {
            return self::$objects['memcached']->set($name, $value, time() + $time_in_second);
        } else {
            return self::$objects['memcached']->add($name, $value, time() + $time_in_second);
        }
    }

    private static function memcached_get($name) {
        $name = self::getMemoryName($name);
        $x = self::$objects['memcached']->get($name);
        if ($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    private static function memcached_stats() {
        self::memcached_addserver();
        return self::$objects['memcached']->getStats();
    }

    private static function memcached_cleanup($option = "") {
        self::memcached_addserver();
        self::$objects['memcached']->flush();
        return true;
    }

    private static function memcached_delete($name) {
        $name = self::getMemoryName($name);
        return self::$objects['memcached']->delete($name);
    }

    private static function memcached_increment($name, $step = 1) {
        $name = self::getMemoryName($name);
        return self::$objects['memcached']->increment($name, $step);
    }

    private static function memcached_decrement($name, $step = 1) {
        $name = self::getMemoryName($name);
        return self::$objects['memcached']->decrement($name, $step);
    }

    /*
     * Begin WinCache Static
     */

    private static function wincache_exist($name) {
        $name = self::getMemoryName($name);
        if (wincache_ucache_exists($name)) {
            return true;
        } else {
            return false;
        }
    }

    private static function wincache_set($name, $value, $time_in_second = 600, $skip_if_existing = false) {
        $orgi = $name;
        $name = self::getMemoryName($name);
        if ($skip_if_existing == false) {
            return wincache_ucache_set($name, $value, $time_in_second);
        } else {
            return wincache_ucache_add($name, $value, $time_in_second);
        }
    }

    private static function wincache_get($name) {
        $name = self::getMemoryName($name);

        $x = wincache_ucache_get($name, $suc);

        if ($suc == false) {
            return null;
        } else {
            return $x;
        }
    }

    private static function wincache_stats() {
        return wincache_scache_info();
    }

    private static function wincache_cleanup($option = "") {
        wincache_ucache_clear();
        return true;
    }

    private static function wincache_delete($name) {
        $name = self::getMemoryName($name);
        return wincache_ucache_delete($name);
    }

    private static function wincache_increment($name, $step = 1) {
        $name = self::getMemoryName($name);
        return wincache_ucache_inc($name, $step);
    }

    private static function wincache_decrement($name, $step = 1) {
        $name = self::getMemoryName($name);
        return wincache_ucache_dec($name, $step);
    }

    /*
     * Begin PDO Static
     */

    private static function pdo_exist($name) {
        $db = self::selectDB($name);
        $name = $db['item'];

        $x = self::db(array('db' => $db['db']))->prepare("SELECT COUNT(*) as `total` FROM " . self::$table . " WHERE `name`=:name");

        $x->execute(array(
            ":name" => $name,
        ));

        $row = $x->fetch(PDO::FETCH_ASSOC);
        if ($row['total'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    private static function pdo_cleanup($option = "") {
        self::db(array("skip_clean" => true))->exec("drop table if exists " . self::$table);
        self::initDatabase();
        return true;
    }

    private static function pdo_stats($full = false) {
        $res = array();
        if ($full == true) {
            $stm = self::db()->prepare("SELECT * FROM " . self::$table . "");
            $stm->execute();
            $result = $stm->fetchAll();
            $res['data'] = $result;
        }
        $stm = self::db()->prepare("SELECT COUNT(*) as `total` FROM " . self::$table . "");
        $stm->execute();
        $result = $stm->fetch();
        $res['record'] = $result['total'];
        if (self::$path != "memory") {
            $res['size'] = filesize(self::getPath() . "/" . self::$filename);
        }

        return $res;
    }

    // for PDO return DB name,
    // For Files, return Dir
    private static function selectDB($object) {
        $res = array(
            'db' => "",
            'item' => "",
        );
        if (is_array($object)) {
            $key = array_keys($object);
            $key = $key[0];
            $res['db'] = $key;
            $res['item'] = self::safename($object[$key]);
        } else {
            $res['item'] = self::safename($object);
        }

        if ($res['db'] == "" && self::$sys['method'] == "files") {
            $res['db'] = "files";
        }

        // for auto database
        if ($res['db'] == "" && self::$storage == "mpdo") {
            $create_table = false;
            if (!file_exists('sqlite:' . self::getPath() . '/phpfastcache.c')) {
                $create_table = true;
            }
            if (self::$autodb == "") {
                try {
                    self::$autodb = new PDO('sqlite:' . self::getPath() . '/phpfastcache.c');
                    self::$autodb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOexception $e) {
                    die("Please CHMOD 0777 or Writable Permission for " . self::getPath());
                }
            }

            if ($create_table == true) {
                self::$autodb->exec('CREATE TABLE IF NOT EXISTS "main"."db" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL  UNIQUE , "item" VARCHAR NOT NULL  UNIQUE , "dbname" INTEGER NOT NULL )');
            }

            $db = self::$autodb->prepare("SELECT * FROM `db` WHERE `item`=:item");
            $db->execute(array(
                ":item" => $res['item'],
            ));
            $row = $db->fetch(PDO::FETCH_ASSOC);
            if (isset($row['dbname'])) {
                // found key
                $res['db'] = $row['dbname'];
            } else {
                // not key // check filesize
                if ((Int) self::$autosize < 10) {
                    self::$autosize = 10;
                }
                // get last key
                $db = self::$autodb->prepare("SELECT * FROM `db` ORDER BY `id` DESC");
                $db->execute();
                $row = $db->fetch(PDO::FETCH_ASSOC);
                $dbname = isset($row['dbname']) ? $row['dbname'] : 1;
                $fsize = file_exists(self::getPath() . "/" . $dbname . ".cache") ? filesize(self::getPath() . "/" . $dbname . ".cache") : 0;
                if ($fsize > (1024 * 1024 * (Int) self::$autosize)) {
                    $dbname = (Int) $dbname + 1;
                }
                try {
                    $insert = self::$autodb->prepare("INSERT INTO `db` (`item`,`dbname`) VALUES(:item,:dbname)");
                    $insert->execute(array(
                        ":item" => $res['item'],
                        ":dbname" => $dbname
                    ));
                } catch (PDOexception $e) {
                    die('Database Error - Check A look at self::$autodb->prepare("INSERT INTO ');
                }

                $res['db'] = $dbname;
            }
        }

        return $res;
    }

    private static function pdo_get($name) {
        $db = self::selectDB($name);
        $name = $db['item'];
        // array('db'=>$db['db'])
        // self::startDebug($db,"",__LINE__,__FUNCTION__);

        $stm = self::db(array('db' => $db['db']))->prepare("SELECT * FROM " . self::$table . " WHERE `name`='" . $name . "'");
        $stm->execute();
        $res = $stm->fetch(PDO::FETCH_ASSOC);

        if (!isset($res['value'])) {
            return null;
        } else {
            // decode value on SQL;
            $data = self::decode($res['value']);
            // check if VALUE on string encode
            return isset($data['value']) ? $data['value'] : null;
        }
    }

    private static function pdo_decrement($name, $step = 1) {
        $db = self::selectDB($name);
        $name = $db['item'];
        // array('db'=>$db['db'])

        $int = self::get($name);
        try {
            $stm = self::db(array('db' => $db['db']))->prepare("UPDATE " . self::$table . " SET `value`=:new WHERE `name`=:name ");
            $stm->execute(array(
                ":new" => self::encode($int - $step),
                ":name" => $name,
            ));
        } catch (PDOexception $e) {
            die("Sorry! phpFastCache don't allow this type of value - Name: " . $name . " -> Decrement: " . $step);
        }
        return $int - $step;
    }

    private static function pdo_increment($name, $step = 1) {
        $db = self::selectDB($name);
        $name = $db['item'];
        // array('db'=>$db['db'])

        $int = self::get($name);
        // echo $int."xxx";
        try {
            $stm = self::db(array('db' => $db['db']))->prepare("UPDATE " . self::$table . " SET `value`=:new WHERE `name`=:name ");
            $stm->execute(array(
                ":new" => self::encode($int + $step),
                ":name" => $name,
            ));
        } catch (PDOexception $e) {
            die("Sorry! phpFastCache don't allow this type of value - Name: " . $name . " -> Increment: " . $step);
        }
        return $int + $step;
    }

    private static function pdo_delete($name) {
        $db = self::selectDB($name);
        $name = $db['item'];

        return self::db(array('db' => $db['db']))->exec("DELETE FROM " . self::$table . " WHERE `name`='" . $name . "'");
    }

    private static function pdo_set($name, $value, $time_in_second = 600, $skip_if_existing = false) {
        $db = self::selectDB($name);
        $name = $db['item'];
        // array('db'=>$db['db'])

        if ($skip_if_existing == true) {
            try {
                $insert = self::db(array('db' => $db['db']))->prepare("INSERT OR IGNORE INTO " . self::$table . " (name,value,added,endin) VALUES(:name,:value,:added,:endin)");
                try {
                    $value = self::encode($value);
                } catch (Exception $e) {
                    die("Sorry! phpFastCache don't allow this type of value - Name: " . $name);
                }

                $insert->execute(array(
                    ":name" => $name,
                    ":value" => $value,
                    ":added" => @date("U"),
                    ":endin" => (Int) $time_in_second
                ));

                return true;
            } catch (PDOexception $e) {
                return false;
            }
        } else {
            try {
                $insert = self::db(array('db' => $db['db']))->prepare("INSERT OR REPLACE INTO " . self::$table . " (name,value,added,endin) VALUES(:name,:value,:added,:endin)");
                try {
                    $value = self::encode($value);
                } catch (Exception $e) {
                    die("Sorry! phpFastCache don't allow this type of value - Name: " . $name);
                }

                $insert->execute(array(
                    ":name" => $name,
                    ":value" => $value,
                    ":added" => @date("U"),
                    ":endin" => (Int) $time_in_second
                ));

                return true;
            } catch (PDOexception $e) {
                return false;
            }
        }
    }

    private static function db($option = array()) {
        $vacuum = false;
        $dbname = isset($option['db']) ? $option['db'] : "";
        $dbname = $dbname != "" ? $dbname : self::$filename;
        if ($dbname != self::$filename) {
            $dbname = $dbname . ".cache";
        }
        // debuging
        // self::startDebug(self::$storage,"Check Storage",__LINE__,__FUNCTION__);
        $initDB = false;

        if (self::$storage == "pdo") {
            // start self PDO
            if (self::$objects['pdo'] == "") {

                //  self::$objects['pdo'] == new PDO("sqlite:".self::$path."/cachedb.sqlite");
                if (!file_exists(self::getPath() . "/" . $dbname)) {
                    $initDB = true;
                } else {
                    if (!is_writable(self::getPath() . "/" . $dbname)) {
                        @chmod(self::getPath() . "/" . $dbname, 0777);
                        if (!is_writable(self::getPath() . "/" . $dbname)) {
                            die("Please CHMOD 0777 or any Writable Permission for " . self::getPath() . "/" . $dbname);
                        }
                    }
                }



                try {
                    self::$objects['pdo'] = new PDO("sqlite:" . self::getPath() . "/" . $dbname);
                    self::$objects['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    if ($initDB == true) {
                        self::initDatabase();
                    }

                    $time = filemtime(self::getPath() . "/" . $dbname);
                    if ($time + (3600 * 48) < @date("U")) {
                        $vacuum = true;
                    }
                } catch (PDOexception $e) {
                    die("Can't connect to caching file " . self::getPath() . "/" . $dbname);
                }


                // remove old cache
                if (!isset($option['skip_clean'])) {

                    try {
                        self::$objects['pdo']->exec("DELETE FROM " . self::$table . " WHERE (`added` + `endin`) < " . @date("U"));
                    } catch (PDOexception $e) {
                        die("Please re-upload the caching file " . $dbname . " and chmod it 0777 or Writable permission!");
                    }
                }

                // auto Vaccuum() every 48 hours
                if ($vacuum == true) {
                    self::$objects['pdo']->exec('VACUUM');
                }


                return self::$objects['pdo'];
            } else {
                return self::$objects['pdo'];
            }
            // end self pdo
        } elseif (self::$storage == "mpdo") {

            // start self PDO
            if (!isset(self::$multiPDO[$dbname])) {
                //  self::$objects['pdo'] == new PDO("sqlite:".self::$path."/cachedb.sqlite");
                if (self::$path != "memory") {
                    if (!file_exists(self::getPath() . "/" . $dbname)) {
                        $initDB = true;
                    } else {
                        if (!is_writable(self::getPath() . "/" . $dbname)) {
                            @chmod(self::getPath() . "/" . $dbname, 0777);
                            if (!is_writable(self::getPath() . "/" . $dbname)) {
                                die("Please CHMOD 0777 or any Writable Permission for PATH " . self::getPath());
                            }
                        }
                    }



                    try {
                        self::$multiPDO[$dbname] = new PDO("sqlite:" . self::getPath() . "/" . $dbname);
                        self::$multiPDO[$dbname]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        if ($initDB == true) {
                            self::initDatabase(self::$multiPDO[$dbname]);
                        }

                        $time = filemtime(self::getPath() . "/" . $dbname);
                        if ($time + (3600 * 48) < @date("U")) {
                            $vacuum = true;
                        }
                    } catch (PDOexception $e) {
                        die("Can't connect to caching file " . self::getPath() . "/" . $dbname);
                    }
                }

                // remove old cache
                if (!isset($option['skip_clean'])) {
                    try {
                        self::$multiPDO[$dbname]->exec("DELETE FROM " . self::$table . " WHERE (`added` + `endin`) < " . @date("U"));
                    } catch (PDOexception $e) {
                        die("Please re-upload the caching file " . $dbname . " and chmod it 0777 or Writable permission!");
                    }
                }

                // auto Vaccuum() every 48 hours
                if ($vacuum == true) {
                    self::$multiPDO[$dbname]->exec('VACUUM');
                }


                return self::$multiPDO[$dbname];
            } else {
                return self::$multiPDO[$dbname];
            }
            // end self pdo
        }
    }

    private static function initDatabase($object = null) {
        if ($object == null) {
            self::db(array("skip_clean" => true))->exec('CREATE TABLE IF NOT EXISTS "' . self::$table . '" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL  UNIQUE , "name" VARCHAR UNIQUE NOT NULL  , "value" BLOB, "added" INTEGER NOT NULL  DEFAULT 0, "endin" INTEGER NOT NULL  DEFAULT 0)');
            self::db(array("skip_clean" => true))->exec('CREATE INDEX "lookup" ON "' . self::$table . '" ("added" ASC, "endin" ASC)');
            self::db(array("skip_clean" => true))->exec('VACUUM');
        } else {
            $object->exec('CREATE TABLE IF NOT EXISTS "' . self::$table . '" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL  UNIQUE , "name" VARCHAR UNIQUE NOT NULL  , "value" BLOB, "added" INTEGER NOT NULL  DEFAULT 0, "endin" INTEGER NOT NULL  DEFAULT 0)');
            $object->exec('CREATE INDEX "lookup" ON "' . self::$table . '" ("added" ASC, "endin" ASC)');
            $object->exec('VACUUM');
        }
    }

    // send all bugs to my email
    // you can replace it to your email
    // maximum 1 email per hour
    // you can use phpFastCache::bugs($title, $e) in any code
    public static function bugs($title, $e) {
        $code = md5("error_" . $title);
        $send = self::get($code);
        if ($send == null) {
            $to = "khoaofgod@yahoo.com";
            $subject = "Bugs: " . $title;
            $message = "Error Serialize:" . serialize($e);
            $from = "root@" . $_SERVER['HTTP_HOST'];
            $headers = "From:" . $from;
            @mail($to, $subject, $message, $headers);
            self::set($code, 1, 3600);
        }
    }

    // use for debug
    // public function, you can use phpFastCache::debug($e|array|string) any time in any code
    public static function debug($e, $exit = false) {
        echo "<pre>";
        print_r($e);
        echo "</pre>";
        if ($exit == true) {
            exit;
        }
    }

    public static function startDebug($value, $text = "", $line = __LINE__, $func = __FUNCTION__) {
        if (self::$debugging == true) {
            self::$step_debugging++;
            if (!is_array($value)) {
                echo "<br>" . self::$step_debugging . " => " . $line . " | " . $func . " | " . $text . " | " . $value;
            } else {
                echo "<br>" . self::$step_debugging . " => " . $line . " | " . $func . " | " . $text . " | ";
                print_r($value);
            }
        }
    }

}

/* End of file WoniuInput.php */