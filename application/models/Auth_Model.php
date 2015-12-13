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
     * 用户身份验证
     * @param $type     登陆账号的类型 T for Teacher, S for Student
     * @param $id       登陆账号的ID  tea_id | stu_id
     * @param $ic       登陆账号的身份证号
     * @param $name     登陆账号的姓名
     * @param $flag     登陆账号的设备唯一标识
     * @return array    认证结果
     */
    public function auth($type, $id, $ic, $name, $flag)
    {
        switch($type)
        {
            case 'T':
                $this->auth_tea($id, $ic, $name, $flag);
                break;
            case 'S':
                $this->auth_stu($id, $ic, $name, $flag);
                break;
            default:
                $this->content['status'] = '401';
                $this->content['message'] = 'Authentication Failed, Unknow Identity-type';
                $this->content['data'] = NULL;
                break;
        }
        return $this->content;
    }

    /**
     * 教师账号的登陆验证
     * @param $id
     * @param $ic
     * @param $name
     * @param $flag
     */
    private function auth_tea($id, $ic, $name, $flag)
    {
        $sql = 'SELECT tea_ic, tea_name FROM teacher WHERE tea_id = ?';
        $query = $this->db->query($sql, array($id));
        $res = $query->row_array();

        if(empty($res)) {   // 未知的tea_id 即非法ID
            $this->content['status'] = '402';
            $this->content['message'] = 'Authentication Failed, UnKnow Teacher-job-number';
            $this->content['data'] = NULL;
            return;
        }

        if($ic == $res['tea_ic'] && $name == $res['tea_name']) {  // 验证通过
            $this->content['status'] = '200';
            $this->content['message'] = 'OK';
            $this->content['data'] = NULL;
            $this->insert_client_msg($flag, 'T', $id);
        }else{  // tea_id正确 但身份证号或姓名错误
            $this->content['status'] = '403';
            $this->content['message'] = 'Authentication Failed, Incorrect ID-card or Full-name';
            $this->content['data'] = NULL;
        }
    }

    /**
     * 学生账号的认证
     * @param $id
     * @param $ic
     * @param $name
     * @param $flag
     */
    private function auth_stu($id, $ic, $name, $flag)
    {
        $table_name = 'stu_' . substr($id, 0, 2);
        $sql = "SELECT stu_ic, stu_name FROM $table_name WHERE stu_id = ?";
        $query = $this->db->query($sql, array($id));
        $res = $query->row_array();

        if(empty($res)) {
            $this->content['status'] = '402';
            $this->content['message'] = 'Authentication Failed, UnKnow Student-ID';
            $this->content['data'] = NULL;
            return;
        }

        if($ic == $res['stu_ic'] && $name == $res['stu_name']) {
            $this->content['status'] = '200';
            $this->content['message'] = 'OK';
            $this->content['data'] = NULL;
            $this->insert_client_msg($flag, 'S', $id);
        }else{
            $this->content['status'] = '403';
            $this->content['message'] = 'Authentication Failed, Incorrect ID-card or Full-name';
            $this->content['data'] = NULL;
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
}
