<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('role/Role_model');
    }

    public function index() {
        $data['page_title'] = 'Management User';
        $data['users'] = $this->User_model->get_all();
        $data['deleted_users'] = $this->User_model->get_deleted(); // Data Sampah
        $data['roles'] = $this->Role_model->get_all();
        
        $this->render('index', $data);
    }

    public function save() {
        $is_update = $this->input->post('is_update');
        
        $data = [
            'auth_nm'   => $this->input->post('username', TRUE),
            'role_id'   => $this->input->post('role_id'),
            'active_st' => $this->input->post('is_active')
        ];

        // Password Logic (Hanya update jika diisi)
        $password = $this->input->post('password');
        if (!empty($password)) {
            $data['auth_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($is_update == 1) {
            // --- UPDATE ---
            $id = $this->input->post('id'); // ID dari hidden input
            $this->User_model->update($id, $data);
            $this->session->set_flashdata('success', 'Data user diperbarui!');
        } else {
            // --- INSERT (AUTO ID) ---
            $new_id = $this->User_model->generate_id();
            $data['user_id'] = $new_id;

            // Validasi Password Wajib
            if(empty($password)){
                $this->session->set_flashdata('error', 'Password wajib diisi untuk user baru.');
                redirect('user');
            }

            $this->User_model->insert($data);
            $this->session->set_flashdata('success', 'User baru berhasil ditambahkan! (ID: '.$new_id.')');
        }

        redirect('user');
    }

    public function delete($id) {
        $id = urldecode($id);
        
        // Proteksi Akun Sendiri & Admin Utama
        if ($id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Anda tidak bisa menghapus akun sendiri!');
            redirect('user');
        }
        if ($id == 'P0001') {
            $this->session->set_flashdata('error', 'Superadmin tidak boleh dihapus!');
            redirect('user');
        }

        if ($this->User_model->delete($id)) {
            $this->session->set_flashdata('success', 'User berhasil dihapus (Pindah ke Recycle Bin).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus user.');
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