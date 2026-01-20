<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_model extends CI_Model {

    public function get_all_modules() {
        return $this->db->where('deleted_st', 0)->order_by('nav_id', 'ASC')->get('app_nav')->result();
    }

    public function get_access_list($role_id) {
        $query = $this->db->get_where('app_permission', ['role_id' => $role_id])->result();
        $data = [];
        foreach($query as $row) {
            $data[$row->nav_id] = 1; 
        }
        return $data;
    }

    public function update_access($role_id, $nav_id, $value) {
        $exists = $this->db->get_where('app_permission', ['role_id' => $role_id, 'nav_id' => $nav_id])->row();
        $user_login = $this->session->userdata('username');
        $now = date('Y-m-d H:i:s');

        if ($value == 1) {
            if (!$exists) {
                // INSERT dengan Audit
                return $this->db->insert('app_permission', [
                    'role_id' => $role_id,
                    'nav_id' => $nav_id,
                    'active_st' => 1,
                    'created_by' => $user_login,
                    'created_at' => $now
                ]);
            }
        } else {
            if ($exists) {
                // HARD DELETE (Biasanya permission dihapus fisik)
                // Jika ingin soft delete, ubah query ini jadi update deleted_st=1
                $this->db->where('permission_id', $exists->permission_id);
                return $this->db->delete('app_permission');
            }
        }
        return true;
    }
}