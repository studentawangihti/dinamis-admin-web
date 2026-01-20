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
        $data['roles'] = $this->Role_model->get_all();
        $this->render('index', $data);
    }

    public function save() {
        $is_update = $this->input->post('is_update');
        $id = $this->input->post('id'); // User ID manual (P0001)

        $data = [
            'user_id'   => $id,
            'auth_nm'   => $this->input->post('username', TRUE),
            'role_id'   => $this->input->post('role_id'),
            'active_st' => $this->input->post('is_active')
        ];

        // Logika Password: Hanya update jika input tidak kosong
        $password = $this->input->post('password');
        if (!empty($password)) {
            $data['auth_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($is_update) {
            $this->User_model->update($id, $data);
            $this->session->set_flashdata('success', 'User berhasil diperbarui!');
        } else {
            // Validasi Duplicate ID
            if($this->db->get_where('app_user', ['user_id'=>$id])->row()){
                $this->session->set_flashdata('error', 'Gagal! User ID sudah digunakan.');
                redirect('user');
            }
            // Validasi Password Wajib untuk User Baru
            if(empty($password)){
                $this->session->set_flashdata('error', 'Gagal! Password wajib diisi untuk user baru.');
                redirect('user');
            }
            $this->User_model->insert($data);
            $this->session->set_flashdata('success', 'User berhasil ditambahkan!');
        }
        redirect('user');
    }

    public function delete($id) {
        if ($id == 'P0001') {
            $this->session->set_flashdata('error', 'User Admin Utama tidak boleh dihapus!');
        } else {
            $this->User_model->delete($id);
            $this->session->set_flashdata('success', 'User berhasil dihapus!');
        }
        redirect('user');
    }
}