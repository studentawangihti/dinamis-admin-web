<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Load kedua model langsung (karena satu folder)
        $this->load->model('User_model');
        $this->load->model('Role_model'); 
    }

    public function index() {
        $data['page_title'] = 'Management User';
        $data['users'] = $this->User_model->get_all();
        $data['deleted_users'] = $this->User_model->get_deleted();
        $data['roles'] = $this->Role_model->get_all();
        
        // Memanggil View di folder master/views/v_user.php
        $this->render('v_user', $data);
    }

    public function save() {
        $is_update = $this->input->post('is_update');
        $data = [
            'auth_nm'   => $this->input->post('username', TRUE),
            'role_id'   => $this->input->post('role_id'),
            'active_st' => $this->input->post('active_st')
        ];

        $password = $this->input->post('password');
        if (!empty($password)) {
            $data['auth_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($is_update == 1) {
            $id = $this->input->post('id');
            $this->User_model->update($id, $data);
            $this->session->set_flashdata('success', 'Data user diperbarui!');
        } else {
            $new_id = $this->User_model->generate_id();
            $data['user_id'] = $new_id;
            
            if(empty($password)){
                $this->session->set_flashdata('error', 'Password wajib diisi.');
                redirect('user');
            }
            $this->User_model->insert($data);
            $this->session->set_flashdata('success', 'User berhasil ditambahkan! (ID: '.$new_id.')');
        }
        redirect('user'); // URL tetap pendek berkat Routes
    }

    public function delete($id) {
        $id = urldecode($id);
        if ($id == 'P0001' || $id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Tidak bisa menghapus akun ini.');
            redirect('user');
        }

        if ($this->User_model->delete($id)) {
            $this->session->set_flashdata('success', 'User berhasil dihapus (Recycle Bin).');
        }
        redirect('user');
    }

    public function restore($id) {
        $id = urldecode($id);
        $res = $this->User_model->restore($id);
        if($res['status']) {
            $this->session->set_flashdata('success', $res['msg']);
        } else {
            $this->session->set_flashdata('error', $res['msg']);
        }
        redirect('user');
    }
}