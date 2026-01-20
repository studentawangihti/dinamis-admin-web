<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

    private $table = 'app_role';

    public function get_all() {
        // Hanya ambil yang tidak dihapus
        $this->db->where('deleted_st', 0);
        $this->db->order_by('role_id', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['role_id' => $id])->row();
    }

    /**
     * GENERATE AUTO ID
     * Format: XX.YY (Prefix.Sequence)
     */
    public function generate_id($prefix = '01') {
        // Cari ID terakhir berdasarkan prefix (misal 01.)
        $this->db->select('role_id');
        $this->db->like('role_id', $prefix . '.', 'after'); // Cari 01.%
        $this->db->order_by('role_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table)->row();

        if ($query) {
            // Jika ada (misal 01.04), ambil ekornya (04) tambah 1
            $last_id = $query->role_id;
            $parts = explode('.', $last_id);
            $number = intval(end($parts)) + 1;
        } else {
            // Jika belum ada, mulai dari 01
            $number = 1;
        }

        // Format ulang jadi 2 digit (01.05)
        return $prefix . '.' . str_pad($number, 2, '0', STR_PAD_LEFT);
    }

    public function insert($data) {
        $data['created_by'] = $this->session->userdata('username');
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        $data['updated_by'] = $this->session->userdata('username');
        $data['updated_at'] = date('Y-m-d H:i:s'); // Perbaikan: Gunakan updated_at
        
        $this->db->where('role_id', $id);
        return $this->db->update($this->table, $data);
    }

    // FUNGSI HAPUS: Pindah ke Prefix 99 (Recycle Bin)
    public function delete($id) {
        // 1. Pecah ID (misal: 01.05) menjadi array ['01', '05']
        $parts = explode('.', $id);
        
        // Validasi format ID harus ada titiknya
        if (count($parts) < 2) {
            return false; // Format salah, batalkan
        }

        $suffix = end($parts); // Ambil digit terakhir (05)
        $new_id = '99.' . $suffix; // Gabungkan dengan prefix sampah (99.05)

        // 2. Cek apakah ID Sampah (99.05) sudah ada?
        // Jika sudah ada, kita tidak bisa menimpa, harus return error atau false
        $check = $this->db->get_where($this->table, ['role_id' => $new_id])->row();
        if ($check) {
            // Opsional: Anda bisa membuat logika penambahan angka jika bentrok
            // Tapi untuk sekarang kita return false agar user tahu
            return false; 
        }

        // 3. Mulai Transaksi (Penting untuk integritas data)
        $this->db->trans_start();

        // A. Update Tabel Relasi (User & Permission)
        // Kita pindahkan user yang memegang role 01.05 ke 99.05
        // Agar user tidak kehilangan role/permission saat role-nya dihapus soft
        $this->db->where('role_id', $id)->update('app_user', ['role_id' => $new_id]);
        $this->db->where('role_id', $id)->update('app_permission', ['role_id' => $new_id]);

        // B. Update Tabel Role (Pindahkan ID dan Soft Delete)
        $data = [
            'role_id'    => $new_id,    // Ubah ID jadi 99.xx
            'active_st'  => 0,          // Matikan status
            'deleted_st' => 1,          // Tandai terhapus
            'deleted_by' => $this->session->userdata('username'),
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('role_id', $id);
        $this->db->update($this->table, $data);

        // 4. Selesaikan Transaksi
        $this->db->trans_complete();

        return $this->db->trans_status(); // Return True jika sukses, False jika gagal
    }
}