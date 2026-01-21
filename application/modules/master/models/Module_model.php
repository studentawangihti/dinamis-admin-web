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

    // --- LOGIKA HAPUS ---

    // 1. Cek apakah menu ini punya sub-menu aktif?
    public function cek_anak($id) {
        $this->db->where('nav_parent', $id);
        $this->db->where('deleted_st', 0);
        return $this->db->count_all_results($this->table); // Return jumlah anak
    }

    // 2. Soft Delete
    public function delete($id) {
        $data = [
            'active_st'  => 0, // Matikan
            'deleted_st' => 1, // Tandai hapus
            'deleted_by' => $this->session->userdata('username'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('nav_id', $id);
        return $this->db->update($this->table, $data);
    }

    // --- LOGIKA INSERT/UPDATE (Sama seperti sebelumnya) ---
    public function insert($data) {
        $data['created_by'] = $this->session->userdata('username');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->trans_start(); // Mulai Transaksi
        
        // 1. Insert ke Tabel Menu (app_nav)
        $this->db->insert($this->table, $data);
        
        // 2. Insert Otomatis ke Permission Superadmin (01.01)
        // PERBAIKAN: Cek dulu apakah permission sudah ada?
        $cek_perm = $this->db->get_where('app_permission', [
            'role_id' => '01.01',
            'nav_id'  => $data['nav_id']
        ])->row();

        if ($cek_perm) {
            // JIKA SUDAH ADA: Cukup update statusnya jadi aktif (jangan insert lagi)
            $this->db->where('permission_id', $cek_perm->permission_id);
            $this->db->update('app_permission', ['active_st' => 1]);
        } else {
            // JIKA BELUM ADA: Baru lakukan Insert
            $this->db->insert('app_permission', [
                'role_id'    => '01.01', 
                'nav_id'     => $data['nav_id'],
                'active_st'  => 1,
                'created_by' => 'system',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $this->db->trans_complete(); // Selesai Transaksi
        return $this->db->trans_status();
    }

    public function update($id, $data) {
        $data['updated_by'] = $this->session->userdata('username');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('nav_id', $id);
        return $this->db->update($this->table, $data);
    }

    // --- FITUR RECYCLE BIN ---

    public function get_deleted() {
        return $this->db->where('deleted_st', 1)
                        ->order_by('deleted_at', 'DESC')
                        ->get($this->table)
                        ->result();
    }

    public function restore($id) {
        // 1. CEK KONFLIK: Apakah ID ini sudah dipakai oleh menu aktif lain?
        // (Misal: Menu '02' dihapus, lalu user buat menu baru dengan ID '02'. 
        // Maka menu lama '02' tidak boleh di-restore).
        $conflict = $this->db->where('nav_id', $id)
                             ->where('deleted_st', 0)
                             ->count_all_results($this->table);

        if ($conflict > 0) {
            return ['status' => false, 'msg' => 'Gagal! ID <b>'.$id.'</b> sudah digunakan oleh menu aktif lain.'];
        }

        // 2. PROSES RESTORE MENU
        $data = [
            'active_st'  => 0, // Restore dalam kondisi Non-Aktif (Safety)
            'deleted_st' => 0,
            'deleted_by' => NULL,
            'deleted_at' => NULL,
            'updated_by' => $this->session->userdata('username'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('nav_id', $id);
        $this->db->update($this->table, $data);

        // 3. RESTORE PERMISSION (PENTING)
        // Kembalikan permission Superadmin agar menu ini bisa diakses lagi
        // Update permission yang mungkin tertinggal (active_st = 0)
        $this->db->where('nav_id', $id);
        $this->db->update('app_permission', ['active_st' => 0]); // Ikut non-aktif

        return ['status' => true, 'msg' => 'Menu berhasil dipulihkan (Kondisi Non-Aktif).'];
    }

    public function hard_delete($id) {
        // 1. HAPUS PERMISSION (PENTING!)
        // Hapus semua jejak permission agar jika nanti ID ini dipakai lagi, 
        // tidak muncul error "Duplicate entry"
        $this->db->where('nav_id', $id);
        $this->db->delete('app_permission');
        
        // 2. HAPUS MENU FISIK
        $this->db->where('nav_id', $id);
        return $this->db->delete($this->table);
    }
}