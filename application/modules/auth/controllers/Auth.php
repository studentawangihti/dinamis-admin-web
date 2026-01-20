<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Auth_model');
    }

    public function index() {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard'); // Redirect jika sudah login
        }
        $this->load->view('auth/login');
    }

    public function process() {
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password');

        $user = $this->Auth_model->get_user_by_username($username);

        if ($user) {
            // Verifikasi Password Hash
            if (password_verify($password, $user->password)) {
                // Set Session Data
                $session_data = [
                    'user_id'   => $user->id,
                    'username'  => $user->username,
                    'role_id'   => $user->role_id,
                    'role_name' => $user->role_name,
                    'is_logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);
                
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Password salah!');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('error', 'Username tidak ditemukan atau akun tidak aktif.');
            redirect('auth');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }

    // -- TOOLS SEMENTARA: Jalankan url /auth/generate_pass untuk dapat hash --
    public function generate_pass($pass = 'admin123') {
        echo password_hash($pass, PASSWORD_BCRYPT);
    }
}