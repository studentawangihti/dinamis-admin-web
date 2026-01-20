<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    private $table = 'app_user';

    public function get_all() {
        $this->db->select('u.*, r.role_nm as role_name');
        $this->db->from('app_user u');
        $this->db->join('app_role r', 'r.role_id = u.role_id', 'left');
        $this->db->where('u.deleted_st', 0); // Hanya ambil yang aktif
        $this->db->order_by('u.user_id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_deleted() {
        $this->db->select('u.*, r.role_nm as role_name');
        $this->db->from('app_user u');
        $this->db->join('app_role r', 'r.role_id = u.role_id', 'left');
        $this->db->where('u.deleted_st', 1); // Ambil sampah
        $this->db->order_by('u.deleted_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * GENERATE AUTO ID (Format P0001)
     * Cek data Aktif (P) dan Sampah (X)
     */
    public function generate_id() {
        // 1. Cari Max ID di Data Aktif (P...)
        $this->db->select('user_id');
        $this->db->like('user_id', 'P', 'after'); 
        $this->db->order_by('user_id', 'DESC');
        $this->db->limit(1);
        $query_active = $this->db->get($this->table)->row();

        $max_active = 0;
        if ($query_active) {
            // Ambil angka setelah huruf P (index 1 sampai akhir)
            $max_active = intval(substr($query_active->user_id, 1));
        }

        // 2. Cari Max ID di Data Sampah (X...)
        $this->db->select('user_id');
        $this->db->like('user_id', 'X', 'after'); 
        $this->db->order_by('user_id', 'DESC');
        $this->db->limit(1);
        $query_trash = $this->db->get($this->table)->row();

        $max_trash = 0;
        if ($query_trash) {
            // Ambil angka setelah huruf X
            $max_trash = intval(substr($query_trash->user_id, 1));
        }

        // 3. Tentukan Angka Selanjutnya
        $next_number = max($max_active, $max_trash) + 1;

        // 4. Format P + 4 Digit (P0005)
        return 'P' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }

    public function insert($data) {
        // Audit Trail
        $data['created_by'] = $this->session->userdata('username');
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        // Audit Trail
        $data['updated_by'] = $this->session->userdata('username');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('user_id', $id);
        return $this->db->update($this->table, $data);
    }

    // FUNGSI HAPUS: Ubah P0001 -> X0001
    public function delete($id) {
        // Cek format ID (harus Pxxxx)
        if (substr($id, 0, 1) != 'P') return false;

        $number_part = substr($id, 1); // 0001
        $new_id = 'X' . $number_part;  // X0001

        // Cek bentrok (jarang terjadi, tapi good practice)
        if ($this->db->get_where($this->table, ['user_id' => $new_id])->row()) {
            return false; 
        }

        $data = [
            'user_id'    => $new_id,    // Rename ID
            'active_st'  => 0,
            'deleted_st' => 1,
            'deleted_by' => $this->session->userdata('username'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('user_id', $id);
        return $this->db->update($this->table, $data);
    }

    // FUNGSI RESTORE: Ubah X0001 -> P0001 (atau Pxxxx baru)
    public function restore($id) {
        // Cek format ID sampah (harus Xxxxx)
        if (substr($id, 0, 1) != 'X') return ['status'=>false, 'msg'=>'Format ID salah'];

        $number_part = substr($id, 1);
        $target_id   = 'P' . $number_part;

        // Cek apakah P0001 sudah dipakai lagi oleh user baru?
        $check = $this->db->get_where($this->table, ['user_id' => $target_id])->row();
        
        $final_id = $target_id;
        $note = '';

        if ($check) {
            // Jika bentrok, generate ID baru (misal P0009)
            $final_id = $this->generate_id();
            $note = " (ID lama bentrok, diganti baru ke $final_id)";
        }

        $data = [
            'user_id'    => $final_id,
            'active_st'  => 1,
            'deleted_st' => 0,
            'deleted_by' => NULL,
            'deleted_at' => NULL,
            'updated_by' => $this->session->userdata('username'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('user_id', $id);
        
        if ($this->db->update($this->table, $data)) {
            return ['status'=>true, 'msg'=>'User berhasil dipulihkan'.$note];
        } else {
            return ['status'=>false, 'msg'=>'Gagal database'];
        }
    }
}