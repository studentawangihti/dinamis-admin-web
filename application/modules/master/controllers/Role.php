<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Load model dari folder models yang sama (satu modul)
        $this->load->model('Role_model');
    }

    public function index() {
        $data['page_title'] = 'Management Role';
        $data['roles'] = $this->Role_model->get_all(); 
        $data['deleted_roles'] = $this->Role_model->get_deleted(); 
        
        // Memanggil View di folder master/views/v_role.php
        $this->render('v_role', $data); 
    }

    public function save() {
        $is_update = $this->input->post('is_update');
        $role_tp   = $this->input->post('role_tp'); 
        
        $data = [
            'role_nm'   => $this->input->post('role_nm', TRUE),
            'role_tp'   => $role_tp,
            'active_st' => $this->input->post('active_st')
        ];

        if ($is_update == 1) {
            $id = $this->input->post('id');
            $this->Role_model->update($id, $data);
            $this->session->set_flashdata('success', 'Role berhasil diperbarui!');
        } else {
            $new_id = $this->Role_model->generate_id($role_tp);
            $data['role_id'] = $new_id;
            
            if($this->db->get_where('app_role', ['role_id'=>$new_id])->row()){
                $this->session->set_flashdata('error', 'Gagal! ID '.$new_id.' bentrok. Coba lagi.');
            } else {
                $this->Role_model->insert($data);
                $this->session->set_flashdata('success', 'Role baru berhasil ditambahkan! (ID: '.$new_id.')');
            }
        }
        redirect('role'); // URL tetap pendek berkat Routes
    }

    public function delete($id = NULL) {
        $id = urldecode($id);
        if (empty($id) || $id == '01.01') {
            $this->session->set_flashdata('error', 'Gagal hapus data.');
            redirect('role');
        }

        if ($this->Role_model->delete($id)) {
            $this->session->set_flashdata('success', 'Role berhasil dihapus (Recycle Bin).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus database.');
        }
        redirect('role');
    }

    public function restore($id) {
        $id = urldecode($id);
        $res = $this->Role_model->restore($id);
        if ($res['status']) {
            $this->session->set_flashdata('success', $res['msg']);
        } else {
            $this->session->set_flashdata('error', $res['msg']);
        }
        redirect('role');
    }
}