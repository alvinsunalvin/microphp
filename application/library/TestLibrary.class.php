<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TestLibrary
 *
 * @author Administrator
 */
class TestLibrary {
    public static $txt='snail';
    public function testController(){
        var_dump(WoniuController::getInstance());
    }
}
 