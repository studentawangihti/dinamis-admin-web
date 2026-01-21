<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Module extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Module_model');
    }

    public function index() {
        $data['page_title'] = 'Navigation Management';
        $data['modules'] = $this->Module_model->get_all();
        $data['parents'] = $this->Module_model->get_parents();
        
        // TAMBAHKAN BARIS INI:
        $data['deleted_modules'] = $this->Module_model->get_deleted();
        
        $this->render('v_module', $data);
    }

    public function save() {
        $is_update = $this->input->post('is_update');
        $nav_id    = $this->input->post('nav_id', TRUE);
        
        // Validasi ID Kosong
        if (empty($nav_id)) {
            $this->session->set_flashdata('error', 'ID Navigasi wajib diisi.');
            redirect('module');
        }

        $data = [
            'nav_id'     => $nav_id,
            'nav_parent' => $this->input->post('nav_parent') ?: NULL,
            'nav_nm'     => $this->input->post('nav_nm', TRUE),
            'nav_url'    => $this->input->post('nav_url', TRUE),
            'icon'       => $this->input->post('icon', TRUE),
            'active_st'  => $this->input->post('active_st')
        ];

        if ($is_update == 1) {
            // --- MODE UPDATE ---
            $this->Module_model->update($nav_id, $data);
            $this->session->set_flashdata('success', 'Menu berhasil diperbarui!');
        } else {
            // --- MODE INSERT BARU ---
            
            // 1. Cek Apakah ID ini sudah ada di database? (Cek tabel mentah)
            // Kita tidak pakai Model get_all karena itu memfilter deleted_st=0
            $exist = $this->db->get_where('app_nav', ['nav_id' => $nav_id])->row();
            
            if ($exist) {
                // ID DITEMUKAN! Sekarang cek statusnya.
                
                if ($exist->deleted_st == 1) {
                    // SKENARIO: ID ada di TONG SAMPAH
                    $this->session->set_flashdata('error', '
                        <strong>Gagal Membuat Menu!</strong><br>
                        ID <b>'.$nav_id.'</b> ('.$exist->nav_nm.') sudah ada di <b>Tong Sampah</b>.<br>
                        Sistem menyarankan: Silakan <b>Restore</b> data lama tersebut atau <b>Hapus Permanen</b> terlebih dahulu jika ingin menggunakan ID ini lagi.
                    ');
                } else {
                    // SKENARIO: ID SUDAH AKTIF (Duplikasi Biasa)
                    $this->session->set_flashdata('error', 'Gagal! ID <b>'.$nav_id.'</b> sudah digunakan oleh menu aktif: <b>'.$exist->nav_nm.'</b>.');
                }
            } else {
                // SKENARIO: ID BELUM ADA (Aman)
                $this->Module_model->insert($data);
                $this->session->set_flashdata('success', 'Menu baru berhasil ditambahkan!');
            }
        }
        redirect('module');
    }

    public function delete($id) {
        // Proteksi menu sistem agar tidak sengaja terhapus (Hardcode ID krusial)
        $protected = ['00', '01', '01.01', '01.01.03'];
        
        if (in_array($id, $protected)) {
             $this->session->set_flashdata('error', 'Menu sistem Inti tidak boleh dihapus!');
        } else {
             $this->Module_model->delete($id);
             $this->session->set_flashdata('success', 'Menu berhasil dihapus (Soft Delete).');
        }
        redirect('module');
    }

    // Fungsi Mengembalikan Data dari Sampah
    public function restore($id) {
        $id = urldecode($id);
        
        // Panggil fungsi restore di model dan tangkap responnya (array)
        $result = $this->Module_model->restore($id);

        if ($result['status']) {
            $this->session->set_flashdata('success', $result['msg']);
        } else {
            $this->session->set_flashdata('error', $result['msg']);
        }
        redirect('module');
    }

    public function hard_delete($id) {
        $id = urldecode($id);
        
        // 1. PROTEKSI: Jangan hapus menu sistem
        $protected = ['00', '01', '01.01']; 
        if(in_array($id, $protected)) {
             $this->session->set_flashdata('error', 'Dilarang menghapus permanen menu sistem!');
             redirect('module');
        }

        // 2. EKSEKUSI
        if ($this->Module_model->hard_delete($id)) {
            $this->session->set_flashdata('success', 'Menu berhasil dihapus <b>PERMANEN</b>. ID '.$id.' sekarang tersedia kembali.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data dari database.');
        }
        redirect('module');
    }
}