<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_model extends CI_Model {

    // Ambil semua menu yang aktif untuk ditampilkan di tabel
    public function get_all_modules() {
        return $this->db->where('deleted_st', 0)
                        ->order_by('nav_id', 'ASC')
                        ->get('app_nav')
                        ->result();
    }

    // Ambil list permission milik role tertentu (untuk checkbox checked/unchecked)
    public function get_access_list($role_id) {
        $query = $this->db->get_where('app_permission', ['role_id' => $role_id])->result();
        $data = [];
        foreach($query as $row) {
            $data[$row->nav_id] = 1; // Flag bahwa role ini punya akses ke nav_id ini
        }
        return $data;
    }

    // Update Permission (Insert / Delete)
    public function update_access($role_id, $nav_id, $value) {
        $exists = $this->db->get_where('app_permission', ['role_id' => $role_id, 'nav_id' => $nav_id])->row();
        $user_login = $this->session->userdata('username');
        $now = date('Y-m-d H:i:s');

        if ($value == 1) {
            // Jika Dicentang (Give Access)
            if (!$exists) {
                return $this->db->insert('app_permission', [
                    'role_id' => $role_id,
                    'nav_id' => $nav_id,
                    'active_st' => 1,
                    'created_by' => $user_login,
                    'created_at' => $now
                ]);
            }
        } else {
            // Jika Uncheck (Revoke Access)
            if ($exists) {
                // Hard Delete (Menghapus baris permission)
                $this->db->where('permission_id', $exists->permission_id);
                return $this->db->delete('app_permission');
            }
        }
        return true;
    }
}