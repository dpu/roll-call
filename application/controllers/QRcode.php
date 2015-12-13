<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/11
 * Time: 20:38
 */

/**
 * Class QRcode
 * 请求/解析二维码
 */
class QRcode extends CI_Controller{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 教师端请求二维码
     */
    public function get_QRcode()
    {
        $req_data = $this->input->post_get(NULL, TRUE);

        if(isset($req_data['client_flag']) && isset($req_data['tea_id']) && isset($req_data['room_id']) && isset($req_data['secret_key'])) {
            $this->load->model('QRcode_Model');
            $get_QRcode_res = $this->QRcode_Model->generate($req_data['client_flag'], $req_data['tea_id'], $req_data['room_id'], $req_data['secret_key']);
        }else{
            $this->content['status'] = '404';
            $this->content['message'] = 'Authentication Failed, Can not get QRcode, Please check your request parameter';
            $this->content['data'] = NULL;
        }

        echo json_encode($get_QRcode_res);
    }

    /**
     * 学生端解析二维码
     */
    public function post_QRcode()
    {
        $req_data = $this->input->post_get(NULL, TRUE);
        if(isset($req_data['qr_msg_id']) && isset($req_data['tea_id']) && isset($req_data['room_id']) && isset($req_data['stu_id']) && isset($req_data['client_flag']) && isset($req_data['req_time'])) {
            $this->load->model('QRcode_Model');
            $post_QRcode_res = $this->QRcode_Model->resolve($req_data['qr_msg_id'], $req_data['tea_id'], $req_data['room_id'], $req_data['stu_id'], $req_data['client_flag'], $req_data['req_time']);
        }else{
            $this->content['status'] = '405';
            $this->content['message'] = 'Authentication Failed, Can not resolve QRcode, Please check your request parameter';
            $this->content['data'] = NULL;
        }

        echo json_encode($post_QRcode_res);
    }

}