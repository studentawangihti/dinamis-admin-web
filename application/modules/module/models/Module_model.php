<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Module_model extends CI_Model {
    private $table = 'app_nav';

    public function get_all() {
        $this->db->select('m.*, p.nav_nm as parent_name, p.nav_id as parent_code');
        $this->db->from('app_nav m');
        $this->db->join('app_nav p', 'p.nav_id = m.nav_parent', 'left');
        $this->db->where('m.deleted_st', 0);
        $this->db->order_by('m.nav_id', 'ASC'); 
        return $this->db->get()->result();
    }

    public function get_parents() {
        $this->db->where('deleted_st', 0);
        $this->db->group_start();
            $this->db->where('nav_parent', NULL);
            $this->db->or_where('nav_url', '#');
        $this->db->group_end();
        $this->db->order_by('nav_id', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function insert($data) {
        $data['created_by'] = $this->session->userdata('username');
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        $data['updated_by'] = $this->session->userdata('username');
        $this->db->where('nav_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        $data = ['deleted_st' => 1, 'deleted_by' => $this->session->userdata('username'), 'deleted_at' => date('Y-m-d H:i:s')];
        $this->db->where('nav_id', $id);
        return $this->db->update($this->table, $data);
    }
}