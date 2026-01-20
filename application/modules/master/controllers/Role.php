<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Role_model');
    }

    public function index() {
        $data['page_title'] = 'Manajemen Role (Jabatan)';
        $data['roles'] = $this->Role_model->get_all(); 
        $data['deleted_roles'] = $this->Role_model->get_deleted(); 
        
        $this->render('v_role', $data); 
    }

    public function save() {
        $is_update = $this->input->post('is_update');
        $role_tp   = $this->input->post('role_tp'); 
        $role_nm   = $this->input->post('role_nm', TRUE);

        // 1. Validasi Input Wajib
        if (empty($role_nm) || empty($role_tp)) {
            $this->session->set_flashdata('error', 'Gagal! Nama Role dan Kelompok wajib diisi.');
            redirect('role');
        }

        $data = [
            'role_nm'   => $role_nm,
            'role_tp'   => $role_tp,
            'active_st' => $this->input->post('active_st')
        ];

        if ($is_update == 1) {
            // --- UPDATE ---
            $id = $this->input->post('id');
            
            // Proteksi Superadmin
            if ($id == '01.01') {
                // Jangan biarkan Superadmin dimatikan statusnya
                $data['active_st'] = 1; 
            }

            $this->Role_model->update($id, $data);
            $this->session->set_flashdata('success', 'Data role berhasil diperbarui!');
        } else {
            // --- INSERT ---
            // Generate Auto ID (Cerdas)
            $new_id = $this->Role_model->generate_id($role_tp);
            $data['role_id'] = $new_id;
            
            // Cek Bentrok ID (Safety Net)
            if($this->db->get_where('app_role', ['role_id'=>$new_id])->row()){
                $this->session->set_flashdata('error', 'Gagal! ID '.$new_id.' sedang diproses sistem. Silakan coba lagi.');
            } else {
                $this->Role_model->insert($data);
                $this->session->set_flashdata('success', 'Role <b>'.$role_nm.'</b> berhasil ditambahkan! (ID: '.$new_id.')');
            }
        }
        redirect('role');
    }

    public function delete($id = NULL) {
        $id = urldecode($id);

        if (empty($id)) {
            $this->session->set_flashdata('error', 'ID Role tidak ditemukan.');
            redirect('role');
        }

        // Proteksi Superadmin & Administrator (Hardcode ID Penting)
        $protected = ['01.01']; 
        if (in_array($id, $protected)) {
            $this->session->set_flashdata('error', 'Role Superadmin dilindungi dan tidak boleh dihapus!');
            redirect('role');
        }

        if ($this->Role_model->delete($id)) {
            $this->session->set_flashdata('success', 'Role berhasil dihapus dan masuk ke Recycle Bin.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data dari database.');
        }
        redirect('role');
    }

    public function restore($id) {
        $id = urldecode($id);
        if (empty($id)) redirect('role');

        $result = $this->Role_model->restore($id);

        if ($result['status']) {
            $this->session->set_flashdata('success', $result['msg']);
        } else {
            $this->session->set_flashdata('error', $result['msg']);
        }
        redirect('role');
    }
}