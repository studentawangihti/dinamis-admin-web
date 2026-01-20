<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Permission extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Karena sekarang satu folder (master), panggil model langsung
        $this->load->model('Permission_model');
        $this->load->model('Role_model'); 
    }

    public function index() {
        $data['page_title'] = 'Hak Akses';
        
        // Ambil list role untuk filter
        $data['roles'] = $this->Role_model->get_all();
        
        // Default ke Superadmin (01.01) jika tidak ada pilihan di URL
        $data['selected_role'] = $this->input->get('role_id') ?: '01.01'; 
        
        // Load Modules & Permissions
        $data['modules'] = $this->Permission_model->get_all_modules();
        $data['permissions'] = $this->Permission_model->get_access_list($data['selected_role']);
        
        // Panggil View baru: v_permission
        $this->render('v_permission', $data);
    }

    public function change() {
        // Fungsi AJAX untuk update checkbox
        $role_id = $this->input->post('role_id');
        $nav_id  = $this->input->post('module_id');
        $value   = $this->input->post('value'); // 1 = Give Access, 0 = Revoke

        $this->Permission_model->update_access($role_id, $nav_id, $value);
        
        // Return JSON untuk AJAX
        header('Content-Type: application/json');
        echo json_encode(['status' => true]);
    }
}