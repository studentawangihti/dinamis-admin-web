<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

    private $table = 'app_role';

    public function get_all() {
        return $this->db->where('deleted_st', 0)
                        ->order_by('role_id', 'ASC')
                        ->get($this->table)
                        ->result();
    }

    public function get_deleted() {
        return $this->db->where('deleted_st', 1)
                        ->order_by('deleted_at', 'DESC')
                        ->get($this->table)
                        ->result();
    }

    // --- AUTO ID GENERATOR (CERDAS) ---
    public function generate_id($prefix) {
        // 1. Cari Max ID di Data Aktif (01.xx)
        $this->db->select('role_id');
        $this->db->like('role_id', $prefix . '.', 'after'); 
        $this->db->order_by('role_id', 'DESC');
        $this->db->limit(1);
        $query_active = $this->db->get($this->table)->row();

        $max_active = 0;
        if ($query_active) {
            $parts = explode('.', $query_active->role_id);
            $max_active = intval(end($parts));
        }

        // 2. Cari Max ID di Data Sampah/Deleted (Takutnya ada 01.05 yg dihapus, kita jgn pake 05 lagi)
        $this->db->select('role_id');
        $this->db->like('role_id', $prefix . '.', 'after'); 
        $this->db->where('deleted_st', 1); // Cek yg dihapus
        $this->db->order_by('role_id', 'DESC');
        $this->db->limit(1);
        $query_trash = $this->db->get($this->table)->row();

        $max_trash = 0;
        if ($query_trash) {
            $parts = explode('.', $query_trash->role_id);
            $max_trash = intval(end($parts));
        }

        // 3. Ambil nilai tertinggi dari keduanya + 1
        $next_number = max($max_active, $max_trash) + 1;

        // 4. Format Output: 01.05
        return $prefix . '.' . str_pad($next_number, 2, '0', STR_PAD_LEFT);
    }

    public function insert($data) {
        $data['created_by'] = $this->session->userdata('username');
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        $data['updated_by'] = $this->session->userdata('username');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('role_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        // Soft Delete: Active=0, Deleted=1
        $data = [
            'active_st'  => 0,
            'deleted_st' => 1,
            'deleted_by' => $this->session->userdata('username'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('role_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function restore($id) {
        // Cek apakah ID ini masih aman untuk dikembalikan?
        // (Sangat jarang terjadi bentrok karena logic generate_id diatas, tapi jaga-jaga)
        $check = $this->db->where('role_id', $id)->where('deleted_st', 0)->get($this->table)->row();
        
        if ($check) {
            return ['status' => false, 'msg' => 'Gagal! ID Role ini sudah digunakan oleh data baru.'];
        }

        $data = [
            'active_st'  => 1,
            'deleted_st' => 0,
            'deleted_by' => NULL,
            'deleted_at' => NULL,
            'updated_by' => $this->session->userdata('username'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('role_id', $id);
        if ($this->db->update($this->table, $data)) {
            return ['status' => true, 'msg' => 'Role berhasil dipulihkan.'];
        }
        return ['status' => false, 'msg' => 'Database Error.'];
    }
}