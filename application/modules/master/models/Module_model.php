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

    // Mengambil menu yang bisa dijadikan Parent (Induk)
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
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->trans_start(); // Mulai Transaksi
        
        // 1. Insert ke Tabel Menu
        $this->db->insert($this->table, $data);
        
        // 2. Insert Otomatis ke Permission Superadmin (01.01)
        // Agar menu langsung muncul setelah dibuat
        $this->db->insert('app_permission', [
            'role_id'    => '01.01', 
            'nav_id'     => $data['nav_id'],
            'active_st'  => 1,
            'created_by' => $this->session->userdata('username'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->trans_complete(); // Selesai Transaksi
        return $this->db->trans_status();
    }

    public function update($id, $data) {
        $data['updated_by'] = $this->session->userdata('username');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('nav_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        // Soft Delete
        $data = [
            'deleted_st' => 1,
            'deleted_by' => $this->session->userdata('username'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('nav_id', $id);
        return $this->db->update($this->table, $data);
    }
}