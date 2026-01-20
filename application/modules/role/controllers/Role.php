<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Role_model');
    }

public function index() {
        $data['page_title'] = 'Management Role';
        $data['roles'] = $this->Role_model->get_all(); // Data Aktif
        $data['deleted_roles'] = $this->Role_model->get_deleted(); // Data Sampah
        
        $this->render('index', $data);
    }

    public function save() {
        $is_update = $this->input->post('is_update'); // 1 atau 0
        $role_tp   = $this->input->post('role_tp');   // Tipe: 01 atau 02
        
        $data = [
            'role_nm'   => $this->input->post('role_nm', TRUE),
            'role_tp'   => $role_tp,
            'active_st' => $this->input->post('active_st')
        ];

        if ($is_update == 1) {
            // --- MODE UPDATE ---
            $id = $this->input->post('id'); // Ambil ID dari hidden field
            $this->Role_model->update($id, $data);
            $this->session->set_flashdata('success', 'Role berhasil diperbarui!');
        } else {
            // --- MODE INSERT (AUTO ID) ---
            // Generate ID berdasarkan Tipe Role (01 atau 02)
            $new_id = $this->Role_model->generate_id($role_tp);
            
            $data['role_id'] = $new_id; // Set ID baru
            
            // Cek Duplicate (Jaga-jaga)
            if($this->db->get_where('app_role', ['role_id'=>$new_id])->row()){
                $this->session->set_flashdata('error', 'Gagal! ID '.$new_id.' sudah ada. Coba lagi.');
                redirect('role');
            }

            $this->Role_model->insert($data);
            $this->session->set_flashdata('success', 'Role baru berhasil ditambahkan! (ID: '.$new_id.')');
        }

        redirect('role');
    }

    public function delete($id = NULL) {
        $id = urldecode($id);

        if (empty($id)) {
            $this->session->set_flashdata('error', 'Error: ID Role tidak ditemukan.');
            redirect('role');
            return;
        }

        if ($id == '01.01') {
            $this->session->set_flashdata('error', 'Role Superadmin tidak boleh dihapus!');
            redirect('role');
            return;
        }

        // Eksekusi Model
        $deleted = $this->Role_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Role berhasil dihapus dan dipindahkan ke Tong Sampah (Prefix 99).');
        } else {
            // Pesan jika gagal (biasanya karena ID sampah bentrok)
            $this->session->set_flashdata('error', 'Gagal menghapus! Kemungkinan ID Sampah (99.xx) untuk nomor ini sudah ada.');
        }

        redirect('role');
    }
    
    public function restore($id) {
        $id = urldecode($id); // Decode 99.05

        if (empty($id)) {
            $this->session->set_flashdata('error', 'ID tidak valid.');
            redirect('role');
        }

        $result = $this->Role_model->restore($id);

        if ($result['status']) {
            $this->session->set_flashdata('success', $result['msg']);
        } else {
            $this->session->set_flashdata('error', $result['msg']);
        }
        
        redirect('role');
    }
}