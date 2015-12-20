<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/15
 * Time: 21:30
 */

class qr extends CI_Controller{
    public function __construct()
    {
        parent::_construct();
    }

    public function get()
    {
        $req_data = $this->input->post_get(NULL, TRUE);
    }
}