<?php

/**
 * Description of index
 *
 * @author Administrator
 */
class Home extends WoniuController {

    public function doIndex($name = '') {
        $this->view("welcome", array('msg' => $name, 'ver' => $this->config('myconfig', 'app')));
    }

    public function doHmvc() {
        return 'okay';
    }

}
