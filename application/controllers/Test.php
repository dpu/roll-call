<?php
/**
 * Created by PhpStorm.
 * User: xu42
 * Date: 2015/12/11
 * Time: 18:47
 */
class Test extends CI_Controller{
    public function aa()
    {
        $t = 'mima';
        echo md5(md5($t));
//        echo time();
    }
}