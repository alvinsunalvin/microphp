<?php
require_once 'pluginfortest.php';
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
/*
 * Copyright 2013 pm.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * MicroPHP
 * Description of test
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		MicroPHP
 * @author		狂奔的蜗牛
 * @email		672308444@163.com
 * @copyright	        Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link		http://git.oschina.net/snail/microphp
 * @createdtime         2013-11-21 10:42:02
 */

/**
 * Description of test_view
 *
 * @author pm
 */
class Test_view extends WebTestCase{
    public function testView(){
        $loader=WoniuLoader::instance();
        echo $loader->page(100,$loader->input->get('p',1),5,'?p={page}',null,10);
        $this->get(getReqURL('view.view'));
        $this->assertEqual($this->getBrowser()->getContent(), 'inc');
    }
    public function testViewData(){
        $this->get(getReqURL('view.data'));
        $this->assertEqual($this->getBrowser()->getContent(), 'include');
    }
    public function testReturn(){
        $this->get(getReqURL('view.return'));
        $this->assertEqual($this->getBrowser()->getContent(), 'test_include');
    }
    
}