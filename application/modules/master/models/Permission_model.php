<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_model extends CI_Model {

    // Ambil semua menu aktif, urut berdasarkan ID agar Hierarki rapi
    public function get_all_modules() {
        return $this->db->where('deleted_st', 0)
                        ->order_by('nav_id', 'ASC')
                        ->get('app_nav')
                        ->result();
    }

    // Ambil list permission spesifik role
    public function get_access_list($role_id) {
        $query = $this->db->select('nav_id, active_st')
                          ->where('role_id', $role_id)
                          ->where('active_st', 1) // Hanya yang aktif
                          ->get('app_permission')
                          ->result();
        
        $data = [];
        foreach($query as $row) {
            $data[$row->nav_id] = 1; // Jadikan nav_id sebagai Key array agar mudah dicek di View
        }
        return $data;
    }

    // Update Access (AJAX Logic)
    public function update_access($role_id, $nav_id, $state) {
        // 1. Cek apakah data permission sudah ada di DB?
        $check = $this->db->get_where('app_permission', [
            'role_id' => $role_id,
            'nav_id'  => $nav_id
        ])->row();

        $user = $this->session->userdata('username');
        $now  = date('Y-m-d H:i:s');

        if ($check) {
            // DATA ADA: Cukup Update Statusnya
            $this->db->where('permission_id', $check->permission_id);
            $this->db->update('app_permission', [
                'active_st' => $state, // 1 atau 0
                'updated_by'=> $user,
                'updated_at'=> $now
            ]);
        } else {
            // DATA TIDAK ADA & Request Nyala (1): Insert Baru
            if ($state == 1) {
                $this->db->insert('app_permission', [
                    'role_id'   => $role_id,
                    'nav_id'    => $nav_id,
                    'active_st' => 1,
                    'created_by'=> $user,
                    'created_at'=> $now
                ]);
            }
            // Jika data tidak ada & request mati (0), biarkan saja (hemat database)
        }
        return true;
    }
}