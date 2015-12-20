<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/11
 * Time: 16:26
 */

/**
 * Class QRcode_Model
 * 二维码的生成和解析 （不是真正的二维码图片）
 * 教师客户端请求二维码数据
 * 学生客户端发送二维码数据
 */
class QRcode_Model extends CI_Model{

    /**
     * @var $content    返回值 array for success, null for failed
     */
    public $content = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * 生成二维码数据
     * @param $client_flag      设备唯一标识
     * @param $tea_id           教师工号
     * @param $room_id          授课教室
     * @param $secret_key       密钥
     * @return array            请求二维码的结果
     */
    public function generate($client_flag, $tea_id, $room_id, $secret_key)
    {
        $generate_res = $this->generate_check($client_flag, $tea_id, $room_id, $secret_key);
        $this->db->close();
        return $generate_res;
    }

    /**
     * 解析二维码数据
     * @param $qr_msg_id
     * @param $tea_id
     * @param $room_id
     * @param $stu_id
     * @param $client_flag
     * @param $req_time
     * @return null
     */
    public function resolve($qr_msg_id, $tea_id, $room_id, $stu_id, $client_flag, $req_time)
    {
        $resolve_res = $this->resolve_check($qr_msg_id, $tea_id, $room_id, $stu_id, $client_flag, $req_time);
        $this->db->close();
        return $resolve_res;
    }

    /**
     * 验证二维码请求的合法性
     * @param $client_flag
     * @param $tea_id
     * @param $room_id
     * @param $secret_key
     * @return bool             验证请求二维码的参数
     */
    private function generate_check($client_flag, $tea_id, $room_id, $secret_key)
    {
        $client_msg_sql = "SELECT flag, status FROM client_msg WHERE user_id = ? AND type = ? AND status = ?";
        $client_msg_query = $this->db->query($client_msg_sql, array($tea_id, 'T', '1'));
        $client_msg_res = $client_msg_query->row_array();

        if($client_msg_res['flag'] != $client_flag) {
            $check_res['status'] = '10011';
            $check_res['message'] = 'Can not get QRcode, Wrong client_flag';
            $check_res['data'] = NULL;
            return $check_res;
        }
        if(md5(md5($client_msg_res['flag'])) != $secret_key) {
            $check_res['status'] = '10012';
            $check_res['message'] = 'Can not get QRcode, Wrong secret_key';
            $check_res['data'] = NULL;
            return $check_res;
        }

        $qr_content = 'tea_id=' . $tea_id . '+room_id=' . $room_id . '+time=' . time() ;
        $qr_msg_id = $this->insert_qr_msg($tea_id, $room_id, base64_encode($qr_content));

        $check_res['status'] = '200';
        $check_res['message'] = 'OK';
        $check_res['data'] = array('qr_msg_id'=>$qr_msg_id, 'tea_id'=>$tea_id, 'room_id'=>$room_id);

        return $check_res;
    }

    /**
     * 验证学生端POST数据的合法性
     * @param $qr_msg_id        二维码的ID
     * @param $tea_id           教师工号
     * @param $room_id          教室编号
     * @param $stu_id           学生学号
     * @param $client_flag      学生客户端标识
     * @param $req_time         请求时间
     * @return mixd             验证结果
     */
    private function resolve_check($qr_msg_id, $tea_id, $room_id, $stu_id, $client_flag, $req_time)
    {
        $qr_msg_sql = "SELECT tea_id, room_id, setup_time FROM qr_msg WHERE id = ? AND active = '1'";
        $qr_msg_query = $this->db->query($qr_msg_sql, array($qr_msg_id));
        $qr_msg_res = $qr_msg_query->row_array();

        $client_msg_sql = "SELECT flag FROM client_msg WHERE user_id = ?";
        $client_msg_query = $this->db->query($client_msg_sql, array($stu_id));
        $client_msg_res = $client_msg_query->row_array();

        if($qr_msg_res['tea_id'] != $tea_id) {
            $check_res['status'] = '10006';
            $check_res['message'] = 'Error teachers';
            $check_res['data'] = NULL;
            return $check_res;
        }
        if($qr_msg_res['room_id'] != $room_id) {
            $check_res['status'] = '10007';
            $check_res['message'] = 'Error room';
            $check_res['data'] = NULL;
            return $check_res;
        }
        if((strtotime($req_time)-strtotime($qr_msg_res['setup_time'])) >= 60*5) {
            $check_res['status'] = '10008';
            $check_res['message'] = 'This QRcode was invalid';
            $check_res['data'] = NULL;
            return $check_res;
        }
        if($client_msg_res['flag'] != $client_flag) {
            $check_res['status'] = '10009';
            $check_res['message'] = 'Error client_flag';
            $check_res['data'] = NULL;
            return $check_res;
        }

        $check_res['status'] = '200';
        $check_res['message'] = 'OK';
        $check_res['data'] = NULL;
        $this->insert_rollcall($stu_id, $tea_id, $room_id, $req_time);
        return $check_res;
    }
    
    /**
     * 二维码的数据入库
     * @param $tea_id
     * @param $room_id
     * @param $qr_content       二维码的内容（不是教师端二维码请求结果）
     * @return mixed            该二维码在数据库表中的ID
     */
    private function insert_qr_msg($tea_id, $room_id, $qr_content)
    {
        $room_id = substr($room_id, 0, 4);
        $insert_data = array('tea_id'=>$tea_id, 'room_id'=>$room_id, 'content'=>$qr_content, 'active'=>'1');
        $this->db->insert('qr_msg', $insert_data);
        return $this->db->insert_id();
    }

    /**
     * 学生签到信息入库
     * @param $stu_id       学生学号
     * @param $tea_id       教师工号
     * @param $room_id      教室编号
     * @param $req_time     请求时间 时间戳
     * @return mixed        该条数据在数据库表中的ID
     */
    private function insert_rollcall($stu_id, $tea_id, $room_id, $req_time)
    {
        $grade = substr($stu_id, 0, 2);
        $year = date('Y', $req_time);
        $room_id = substr($room_id, 0, 4);
        $table_name = 'rollcall_' . $year . '_' . $grade;

        $insert_data = array('stu_id'=>$stu_id, 'tea_id'=>$tea_id, 'room_id'=>$room_id);
        $this->db->insert($table_name, $insert_data);
        return $this->db->insert_id();
    }
    
}
