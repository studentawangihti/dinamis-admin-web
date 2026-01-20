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
        $this->render('index', $data);
    }

    public function save() {
        $is_update = $this->input->post('is_update');
        $nav_id    = $this->input->post('nav_id', TRUE);
        
        $data = [
            'nav_id'     => $nav_id,
            'nav_parent' => $this->input->post('nav_parent') ?: NULL,
            'nav_nm'     => $this->input->post('nav_nm', TRUE),
            'nav_url'    => $this->input->post('nav_url', TRUE),
            'icon'       => $this->input->post('icon', TRUE),
            'active_st'  => $this->input->post('active_st')
        ];

        if ($is_update == 1) {
            $this->Module_model->update($nav_id, $data);
            $this->session->set_flashdata('success', 'Berhasil update!');
        } else {
            // Cek Duplicate
            if($this->db->get_where('app_nav', ['nav_id'=>$nav_id])->row()){
                $this->session->set_flashdata('error', 'ID sudah ada!');
                redirect('module');
            }
            $this->Module_model->insert($data);
            $this->session->set_flashdata('success', 'Berhasil simpan!');
        }
        redirect('module');
    }

    public function delete($id) {
        $this->Module_model->delete($id);
        $this->session->set_flashdata('success', 'Berhasil dihapus!');
        redirect('module');
    }
}