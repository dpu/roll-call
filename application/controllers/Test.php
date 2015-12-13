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
        $t = 'dGVhX2lkPTA4MDAwMStyb29tX2lkPUE0MDMzK3RpbWU9MTQ0OTgzODc4OQ==';
        $key = md5(md5($t));
        echo 'length:' . strlen($key);
        echo "</br>";
        echo $key;
        echo "</br>";

        $base = base64_encode($t);
        echo $base;
        echo "</br>";
        echo base64_decode($t);

        echo "</br>";
        echo time();
        echo "</br>";
        echo date('Y', time());
    }
}