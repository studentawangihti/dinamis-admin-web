<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Permission extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Permission_model');
        $this->load->model('Role_model'); 
    }

    public function index() {
        $data['page_title'] = 'Manajemen Hak Akses';
        
        // 1. Ambil Semua Role untuk Dropdown
        $data['roles'] = $this->Role_model->get_all();
        
        // 2. Tentukan Role mana yang sedang diedit (Default: Superadmin / 01.01)
        $data['selected_role'] = $this->input->get('role_id') ?: '01.01'; 
        
        // 3. Ambil Semua Menu (Modules)
        $data['modules'] = $this->Permission_model->get_all_modules();
        
        // 4. Ambil Permission milik Role yang dipilih (Array Key = nav_id)
        $data['permissions'] = $this->Permission_model->get_access_list($data['selected_role']);
        
        $this->render('v_permission', $data);
    }

    // Fungsi AJAX untuk Simpan Perubahan
    public function change() {
        // Validasi Request
        if (!$this->input->is_ajax_request()) {
            show_404(); // Atau exit
        }

        $role_id = $this->input->post('role_id');
        $nav_id  = $this->input->post('nav_id');
        $state   = $this->input->post('state'); 

        // Proteksi Superadmin (PENTING)
        if ($role_id == '01.01' && ($nav_id == '01' || $nav_id == '01.01') && $state == 0) {
            echo json_encode(['status' => false, 'msg' => 'Akses Master untuk Superadmin tidak boleh dimatikan!']);
            return;
        }

        // Panggil Model
        $this->Permission_model->update_access($role_id, $nav_id, $state);
        
        // Response JSON (Penting agar JS bisa baca status: true)
        echo json_encode(['status' => true, 'msg' => 'Akses berhasil diubah.']);
    }
}