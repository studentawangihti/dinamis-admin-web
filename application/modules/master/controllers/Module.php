<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Module extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Module_model');
    }

    public function index() {
        $data['page_title'] = 'Manajemen Navigasi';
        
        // Mengambil data menu (sudah diurutkan ASC berdasarkan ID di Model)
        $data['modules'] = $this->Module_model->get_all();
        
        // Mengambil daftar menu yang bisa dijadikan Induk (Parent)
        $data['parents'] = $this->Module_model->get_parents();
        
        $this->render('v_module', $data);
    }

    public function save() {
        $is_update  = $this->input->post('is_update');
        $nav_id     = $this->input->post('nav_id', TRUE);
        
        // 1. Validasi ID Wajib
        if (empty($nav_id)) {
            $this->session->set_flashdata('error', 'Gagal! Kode ID Navigasi wajib diisi.');
            redirect('module');
        }

        // 2. Siapkan Data
        $data = [
            'nav_id'     => $nav_id,
            'nav_parent' => $this->input->post('nav_parent') ?: NULL, // Set NULL jika string kosong
            'nav_nm'     => $this->input->post('nav_nm', TRUE),
            'nav_url'    => $this->input->post('nav_url', TRUE),
            'icon'       => $this->input->post('icon', TRUE),
            'active_st'  => $this->input->post('active_st')
        ];

        if ($is_update == 1) {
            // --- MODE UPDATE ---
            $this->Module_model->update($nav_id, $data);
            $this->session->set_flashdata('success', 'Menu navigasi berhasil diperbarui!');
        } else {
            // --- MODE INSERT ---
            // Cek Duplikasi ID sebelum insert
            $exist = $this->db->get_where('app_nav', ['nav_id' => $nav_id])->row();
            
            if ($exist) {
                $this->session->set_flashdata('error', 'Gagal! ID <b>'.$nav_id.'</b> sudah digunakan oleh menu lain.');
            } else {
                $status = $this->Module_model->insert($data);
                if ($status) {
                    $this->session->set_flashdata('success', 'Menu baru berhasil ditambahkan dan didaftarkan ke Superadmin.');
                } else {
                    $this->session->set_flashdata('error', 'Terjadi kesalahan database.');
                }
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
}