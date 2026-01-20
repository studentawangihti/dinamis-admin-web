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
     * GENERATE AUTO ID (CERDAS)
     * Mengecek Data Aktif DAN Data Sampah agar urutan tidak bentrok.
     */
    public function generate_id($prefix) {
        // 1. Cari Angka Tertinggi di Data Aktif (Format: 01.XX)
        $this->db->select('role_id');
        $this->db->like('role_id', $prefix . '.', 'after'); 
        $this->db->where('active_st', 1); // Hanya yg aktif
        $this->db->order_by('role_id', 'DESC');
        $this->db->limit(1);
        $query_active = $this->db->get($this->table)->row();

        $max_active = 0;
        if ($query_active) {
            $parts = explode('.', $query_active->role_id);
            $max_active = intval(end($parts));
        }

        // 2. Cari Angka Tertinggi di Tong Sampah (Format: 99.XX) 
        // TAPI hanya yang asalnya dari tipe role ini (role_tp = $prefix)
        $this->db->select('role_id');
        $this->db->like('role_id', '99.', 'after'); 
        $this->db->where('role_tp', $prefix); // Kuncinya disini: Cek role_tp
        $this->db->order_by('role_id', 'DESC');
        $this->db->limit(1);
        $query_trash = $this->db->get($this->table)->row();

        $max_trash = 0;
        if ($query_trash) {
            $parts = explode('.', $query_trash->role_id);
            $max_trash = intval(end($parts));
        }

        // 3. Bandingkan mana yang lebih besar
        $next_number = max($max_active, $max_trash) + 1;

        // 4. Format ulang jadi 2 digit (01.06)
        return $prefix . '.' . str_pad($next_number, 2, '0', STR_PAD_LEFT);
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

    // Ambil data yang terhapus (untuk ditampilkan di tong sampah)
    public function get_deleted() {
        $this->db->where('deleted_st', 1);
        $this->db->order_by('deleted_at', 'DESC');
        return $this->db->get($this->table)->result();
    }

    // FUNGSI RESTORE (Mengembalikan Data)
    public function restore($id) {
        // 1. Ambil data role yang ada di tong sampah (misal 99.05)
        $role = $this->db->get_where($this->table, ['role_id' => $id])->row();
        
        if (!$role) return ['status' => false, 'msg' => 'Data tidak ditemukan.'];

        // 2. Susun Target ID Asli
        // Ambil ekornya (05) dari 99.05
        $parts = explode('.', $id);
        $suffix = end($parts); 
        
        // Gabungkan dengan Tipe Role aslinya (misal 01.05)
        $target_id = $role->role_tp . '.' . $suffix;

        // 3. Cek Bentrok: Apakah ID 01.05 sudah dipakai orang lain saat role ini dihapus?
        $check = $this->db->get_where($this->table, ['role_id' => $target_id])->row();
        
        $final_id = $target_id;
        $note = '';

        if ($check) {
            // Jika ID lama sudah terpakai, kita Generate ID Baru (misal jadi 01.06)
            $final_id = $this->generate_id($role->role_tp);
            $note = " (ID lama bentrok, diganti baru ke $final_id)";
        }

        // 4. Mulai Transaksi Restore
        $this->db->trans_start();

        // A. Update Tabel Relasi (User & Permission) ke ID Final
        $this->db->where('role_id', $id)->update('app_user', ['role_id' => $final_id]);
        $this->db->where('role_id', $id)->update('app_permission', ['role_id' => $final_id]);

        // B. Update Tabel Role (Pindah ID, Aktifkan Kembali)
        $data = [
            'role_id'    => $final_id,
            'active_st'  => 1,          // Aktifkan lagi
            'deleted_st' => 0,          // Hapus bendera delete
            'deleted_by' => NULL,
            'deleted_at' => NULL,
            'updated_by' => $this->session->userdata('username'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('role_id', $id);
        $this->db->update($this->table, $data);

        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            return ['status' => true, 'msg' => 'Role berhasil dipulihkan' . $note];
        } else {
            return ['status' => false, 'msg' => 'Gagal memulihkan database.'];
        }
    }
}