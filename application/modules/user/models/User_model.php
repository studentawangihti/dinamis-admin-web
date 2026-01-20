<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    private $table = 'app_user';

    public function get_all() {
        $this->db->select('u.*, r.role_nm as role_name');
        $this->db->from('app_user u');
        $this->db->join('app_role r', 'r.role_id = u.role_id', 'left');
        $this->db->where('u.deleted_st', 0); // Hanya ambil yang belum dihapus
        return $this->db->get()->result();
    }

    public function insert($data) {
        // Audit Trail Create
        $data['created_by'] = $this->session->userdata('username');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        // Audit Trail Update
        $data['updated_by'] = $this->session->userdata('username');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('user_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        // Audit Trail Soft Delete
        $data = [
            'deleted_st' => 1,
            'deleted_by' => $this->session->userdata('username'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('user_id', $id);
        return $this->db->update($this->table, $data);
    }
}