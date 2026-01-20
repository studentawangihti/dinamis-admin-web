<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Permission extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Permission_model');
        $this->load->model('role/Role_model');
    }

    public function index() {
        $data['page_title'] = 'Hak Akses';
        $data['roles'] = $this->Role_model->get_all();
        $data['selected_role'] = $this->input->get('role_id') ?: '01.01'; // Default Superadmin
        $data['modules'] = $this->Permission_model->get_all_modules();
        $data['permissions'] = $this->Permission_model->get_access_list($data['selected_role']);
        $this->render('index', $data);
    }

    public function change() {
        $role_id = $this->input->post('role_id');
        $nav_id  = $this->input->post('module_id');
        $value   = $this->input->post('value'); // 1 = Give Access, 0 = Revoke

        $this->Permission_model->update_access($role_id, $nav_id, $value);
        echo json_encode(['status' => true]);
    }
}