<?php
/**
 * Created by PhpStorm.
 * User: xu42
 * Date: 2015/12/11
 * Time: 14:40
 */

/**
 * Class Auth
 * 登陆身份验证
 */
class Auth extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 绑定账号到设备
     */
    public function binding()
    {
        $req_data = $this->input->post_get(NULL, TRUE);
        $auth_res = NULL;
        $type = NUll;

        if(isset($req_data['id']) && isset($req_data['ic']) && isset($req_data['name']) && isset($req_data['flag'])) {
            if(strlen($req_data['id']) == 10) $type = 'S';
            if(strlen($req_data['id']) == 6)  $type = 'T';
            $this->load->model('Auth_Model');
            $auth_res = $this->Auth_Model->auth($type, $req_data['id'], $req_data['ic'], $req_data['name'], $req_data['flag']);
        }else{
            $auth_res['status'] = '400';
            $auth_res['message'] = 'Authentication Failed, Parameter-error';
            $auth_res['data'] = NULL;
        }

        echo json_encode($auth_res);
    }

}
