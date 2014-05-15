<?php
require_once 'pluginfortest.php';
//require_once('simpletest/web_tester.php');
require_once('simpletest/autorun.php');
/*
 * Copyright 2013 snail.
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
 * @createdtime         2013-11-22 10:05:38
 */
/**
 * Description of empty
 *
 * @author pm
 */
class Test_autoload extends UnitTestCase {
    public function testautoload(){
        global $system;
        $system['helper_file_autoload'] = array('function');
        $system['library_file_autoload'] = array('LibAutoload',array('LibAutoload'=>'la'));
        $system['models_file_autoload'] = array('ModelAutoload',array('ModelAutoload' => 'ma'));
        WoniuRouter::setConfig($system);
        $this->assertFalse(function_exists('testFunction'));
        $this->assertFalse(class_exists('LibAutoload',FALSE));
        $this->assertFalse(class_exists('ModelAutoload',FALSE));
        $woniu=WoniuLoader::instance();
        $this->assertEqual(testFunction('hello'), 'hello');
        $this->assertIsA($woniu->lib->LibAutoload, 'LibAutoload');
        $this->assertIsA($woniu->lib->la, 'LibAutoload');
        $this->assertReference($woniu->lib->LibAutoload, $woniu->lib->la);
        $this->assertIsA($woniu->model->ModelAutoload, 'ModelAutoload');
        $this->assertIsA($woniu->model->ma, 'ModelAutoload');
        $this->assertReference($woniu->model->ModelAutoload, $woniu->model->ma);
        $system['helper_file_autoload'] = array('function_again');
        $system['library_file_autoload'] = array('LibAutoload_again',array('LibAutoload_again'=>'laa'));
        $system['models_file_autoload'] = array('ModelAutoload_again',array('ModelAutoload_again' => 'maa'));
        WoniuRouter::setConfig($system);
        $this->assertFalse(function_exists('testFunctionAgain'));
        $this->assertFalse(class_exists('LibAutoload_again',FALSE));
        $this->assertFalse(class_exists('ModelAutoload_again',FALSE));
        $woniu=WoniuLoader::instance();
        $this->assertIsA($woniu->model->ModelAutoload2, 'ModelAutoload2');
        $woniu->model('ModelAutoload2','mod2');
        $this->assertReference($woniu->model->ModelAutoload2, $woniu->model->mod2);
        $this->assertIsA($woniu->lib->LibAutoload2, 'LibAutoload2');
        $woniu->lib('LibAutoload2','lib2');
        $this->assertReference($woniu->lib->LibAutoload2, $woniu->lib->lib2);
        
        $this->assertEqual(testFunctionAgain('hello'), 'hello');
        $this->assertIsA($woniu->lib->LibAutoload_again, 'LibAutoload_again');
        $this->assertIsA($woniu->lib->laa, 'LibAutoload_again');
        $this->assertReference($woniu->lib->LibAutoload_again, $woniu->lib->laa);
        $this->assertIsA($woniu->model->ModelAutoload_again, 'ModelAutoload_again');
        $this->assertIsA($woniu->model->maa, 'ModelAutoload_again');
        $this->assertReference($woniu->model->ModelAutoload_again, $woniu->model->maa);
    }
    public function tearDown() {
        global $default;
        WoniuRouter::setConfig($default);
    }
}