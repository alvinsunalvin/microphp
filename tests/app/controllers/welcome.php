<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Welcome extends WoniuController {

    public function __construct() {
        parent::__construct();
        $this->view_vars['vars']='aaaaa';
//        $this->helper('html.helper');
    }

    public function doForm() {
        $validator = new FormValidator();
        $data = array('user' => '111', 'pass' => 'bbbb');
        $rules = array('user' =>array('rule'=>"range(1,1000)"));
        var_dump($validator->check($rules, $data),$validator->error);
    }
    static function check($val){
        var_dump($val);
        return false;
    }
    public function doIndex($name = '') {
//        $this->redirect("http://www.163.com","测试",'message',5);
//        $this->message("提示信息",'message',"http://www.163.com",5);
//        var_dump($this->input->server('http_host',2222));
//        echo $this->page(100, $this->input->get('p'), 10, '?home.welcome.index&p={page}');
        $this->helper('config');
        //session_start();
//        $_SESSION['user']=array('aaa','bbb');
//        var_dump($_SESSION);
//        session_destroy();
//        var_dump($_SESSION);
        $this->cache->set('aaakey',array('555555fasdfasdf','dafasd'),60);
//        $this->cache->clean();
//        $this->database();
//        var_dump($this->db->get('xxx'));
        echo $a;
//        throw new Exception('l am a exception.');
        echo $a->bcc();
        $this->view("welcome", array('msg' => $name, 'ver' => $this->config('myconfig', 'app')));
    }

    public function a__output($html) {
        echo '__output' . $html;
    }

    public function doAjax($arg=null) {
        
        $this->ajax_echo(200, 'tip',$arg);
    }

}
