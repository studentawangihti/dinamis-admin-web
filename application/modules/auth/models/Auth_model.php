<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    public function get_user_by_username($username) {
        // Mapping kolom baru ke variabel standar CodeIgniter login kita
        $this->db->select('u.user_id as id, u.auth_nm as username, u.auth_hash as password, u.role_id, r.role_nm as role_name');
        $this->db->from('app_user u');
        $this->db->join('app_role r', 'r.role_id = u.role_id', 'left');
        
        $this->db->where('u.auth_nm', $username);
        $this->db->where('u.active_st', 1);
        $this->db->where('u.deleted_st', 0);
        
        return $this->db->get()->row();
    }
}