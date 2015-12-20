<?php
/**
 * Created by PhpStorm.
 * User: xu42
 * Date: 2015/12/10
 * Time: 20:08
 */

/**
 * Class Auth_Model
 * 教师&学生登陆验证
 */
class Auth_Model extends CI_Model{

    /**
     * @var $content 返回值 array for success, null for failed
     */
    public $content = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * 用户身份验证 绑定
     * @param $type     登陆账号的类型 T for Teacher, S for Student
     * @param $id       登陆账号的ID  tea_id | stu_id
     * @param $ic       登陆账号的身份证号
     * @param $name     登陆账号的姓名
     * @param $flag     登陆账号的设备唯一标识
     * @return array    认证结果
     */
    public function auth($type, $id, $ic, $name, $flag)
    {
        $is_exist = $this->check_client_msg($id);
        if($is_exist) {
            $this->content['status'] = '10004';
            $this->content['message'] = 'The ID is already bound';
            $this->content['data'] = NULL;
            return $this->content;
        }

        switch($type)
        {
            case 'T':
                $auth_res = $this->auth_tea($id, $ic, $name);
                if($auth_res) $this->insert_client_msg($flag, 'T', $id);
                break;
            case 'S':
                $auth_res = $this->auth_stu($id, $ic, $name);
                if($auth_res) $this->insert_client_msg($flag, 'S', $id);
                break;
            default:
                $this->content['status'] = '10001';
                $this->content['message'] = 'Authentication Failed, Unknow Identity-type';
                $this->content['data'] = NULL;
                break;
        }
        $this->db->close();
        return $this->content;
    }

    /**
     * 用户解绑设备
     * @param $type
     * @param $id
     * @param $ic
     * @param $name
     * @return 返回值
     */
    public function remove($type, $id, $ic, $name)
    {
        $is_exist = $this->check_client_msg($id);
        if(!$is_exist) {
            $this->content['status'] = '10005';
            $this->content['message'] = 'The ID is not bound';
            $this->content['data'] = NULL;
            return $this->content;
        }

        switch($type)
        {
            case 'T':
                $auth_res = $this->auth_tea($id, $ic, $name);
                if($auth_res) $this->update_client_msg_status($id);
                break;
            case 'S':
                $auth_res = $this->auth_stu($id, $ic, $name);
                if($auth_res) $this->update_client_msg_status($id);
                break;
            default:
                $this->content['status'] = '10001';
                $this->content['message'] = 'Authentication Failed, Unknow Identity-type';
                $this->content['data'] = NULL;
                break;
        }
        $this->db->close();
        return $this->content;
    }

    /**
     * 教师账号的登陆验证
     * @param $id
     * @param $ic
     * @param $name
     */
    private function auth_tea($id, $ic, $name)
    {
        $sql = 'SELECT tea_ic, tea_name FROM teacher WHERE tea_id = ?';
        $query = $this->db->query($sql, array($id));
        if(!$query) {
            $this->content['status'] = '10010';
            $this->content['message'] = 'Query Error';
            $this->content['data'] = NULL;
            return FALSE;
        }

        $res = $query->row_array();

        if(empty($res)) {   // 未知的tea_id 即非法ID
            $this->content['status'] = '10002';
            $this->content['message'] = 'Authentication Failed, UnKnow Teacher-job-number';
            $this->content['data'] = NULL;
            return FALSE;
        }

        if($ic == $res['tea_ic'] && $name == $res['tea_name']) {  // 验证通过
            $this->content['status'] = '200';
            $this->content['message'] = 'OK';
            $this->content['data'] = NULL;
            return TRUE;
        }else{  // tea_id正确 但身份证号或姓名错误
            $this->content['status'] = '10003';
            $this->content['message'] = 'Authentication Failed, Incorrect ID-card or Full-name';
            $this->content['data'] = NULL;
            return FALSE;
        }
    }

    /**
     * 学生账号的认证
     * @param $id
     * @param $ic
     * @param $name
     */
    private function auth_stu($id, $ic, $name)
    {
        $table_name = 'stu_' . substr($id, 0, 2);
        $sql = "SELECT stu_ic, stu_name FROM $table_name WHERE stu_id = ?";
        $query = $this->db->query($sql, array($id));
        if(!$query) {
            $this->content['status'] = '10010';
            $this->content['message'] = 'Query Error';
            $this->content['data'] = NULL;
            return FALSE;
        }

        $res = $query->row_array();

        if(empty($res)) {
            $this->content['status'] = '10002';
            $this->content['message'] = 'Authentication Failed, UnKnow Student-ID';
            $this->content['data'] = NULL;
            return FALSE;
        }

        if($ic == $res['stu_ic'] && $name == $res['stu_name']) {
            $this->content['status'] = '200';
            $this->content['message'] = 'OK';
            $this->content['data'] = NULL;
            return TRUE;
        }else{
            $this->content['status'] = '10003';
            $this->content['message'] = 'Authentication Failed, Incorrect ID-card or Full-name';
            $this->content['data'] = NULL;
            return FALSE;
        }
    }

    /**
     * 保存用户的设备标识
     * @param $flag         用户的设备唯一标识
     * @param $type         用户账号的类型 T for Teacher, S for Student
     * @param $user_id      用户的ID tea_id | stu_id
     */
    private function insert_client_msg($flag, $type, $user_id)
    {
        $insert_data = array('flag'=>$flag, 'type'=>$type, 'user_id'=>$user_id);
        $this->db->insert('client_msg', $insert_data);
    }


    /**
     * 检查学号/工号是否已经绑定
     * @param $user_id
     * @return bool TRUE for Yes,FALSE for No
     */
    private function check_client_msg($user_id)
    {
        $sql = "SELECT count(*) AS num FROM client_msg WHERE user_id = ? AND status = '1'";
        $query = $this->db->query($sql, array($user_id));
        $check_res = $query->row_array();

        if($check_res['num'] == 0) {
            return FALSE;
        }else{
            return TRUE;
        }
    }

    /**
     * 更新client_msg绑定状态 即解绑设备
     * @param $id
     * @return mixed
     */
    private function update_client_msg_status($id)
    {
        $sql = "UPDATE client_msg SET status = '0' WHERE user_id = ?";
        $query = $this->db->query($sql, array($id));
        return $query;
    }
}
