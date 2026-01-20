<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* Load MX_Controller class */
require APPPATH . "third_party/MX/Controller.php";

class MY_Controller extends MX_Controller {

    protected $data = [];
    protected $user_id;
    protected $role_id;
    protected $role_name;

    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'text']);

        // Default Permission (Aman = 0)
        $this->data['can_read']   = 0;
        $this->data['can_create'] = 0;
        $this->data['can_update'] = 0;
        $this->data['can_delete'] = 0;

        // Cek Login
        if (!$this->session->userdata('is_logged_in')) {
            redirect('auth');
        }

        $this->user_id   = $this->session->userdata('user_id');
        $this->role_id   = $this->session->userdata('role_id');
        $this->role_name = $this->session->userdata('role_name');

        // Load Global Data
        $this->_load_settings();
        $this->_load_sidebar_menu();
        $this->_check_access_control();
    }

    public function render($view_file, $view_data = []) {
        $this->data = array_merge($this->data, $view_data);
        $this->data['content_body'] = $this->load->view($view_file, $this->data, TRUE);
        echo Modules::run('layout/index', $this->data);
    }

    private function _load_settings() {
        // Load setting jika tabel settings ada
        if ($this->db->table_exists('settings')) {
            $settings = $this->db->get('settings')->result();
            foreach ($settings as $row) {
                $this->data[$row->setting_key] = $row->setting_value;
            }
        }
        $this->data['active_user_name'] = $this->session->userdata('username');
        $this->data['active_role_name'] = $this->role_name;
    }

    private function _load_sidebar_menu() {
        // Ambil menu berdasarkan Role ID dari tabel app_permission
        $this->db->select('m.*');
        $this->db->from('app_nav m');
        $this->db->join('app_permission p', 'p.nav_id = m.nav_id');
        $this->db->where('p.role_id', $this->role_id);
        $this->db->where('m.active_st', 1);
        $this->db->where('m.deleted_st', 0);
        $this->db->order_by('m.nav_id', 'ASC'); // Urutkan string '01.01'
        
        $modules = $this->db->get()->result_array();

        // Build Recursive Tree
        $this->data['sidebar_menu'] = $this->_build_tree($modules, NULL);
    }

    private function _build_tree(array $elements, $parentId = NULL) {
        $branch = array();
        foreach ($elements as $element) {
            $is_child = false;
            // Logika Parent: Jika parentId NULL, cari yang nav_parent kosong/NULL
            if ($parentId === NULL) {
                if (empty($element['nav_parent'])) $is_child = true;
            } else {
                if ($element['nav_parent'] == $parentId) $is_child = true;
            }

            if ($is_child) {
                $children = $this->_build_tree($elements, $element['nav_id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    private function _check_access_control() {
        $current_controller = $this->router->fetch_class(); 
        $whitelist = ['layout', 'ajax', 'profile', 'auth', 'welcome']; 
        
        if (in_array(strtolower($current_controller), $whitelist)) {
            return;
        }

        // Cari ID menu berdasarkan URL (nav_url)
        // Kita cari yang match parsial atau exact
        $module = $this->db->group_start()
                        ->like('nav_url', $current_controller) // 'user' match 'master/user'
                        ->or_where('nav_url', strtolower($current_controller))
                     ->group_end()
                     ->get('app_nav')
                     ->row();

        if ($module) {
            // Cek di tabel app_permission
            $permission = $this->db->get_where('app_permission', [
                'role_id' => $this->role_id,
                'nav_id'  => $module->nav_id
            ])->row();

            if (!$permission) {
                show_error('⛔ <strong>Akses Ditolak!</strong><br>Anda tidak memiliki izin mengakses modul ini.', 403, '403 Forbidden');
                exit;
            }
            
            // Karena DB baru tidak punya kolom can_create, kita anggap FULL ACCESS jika ada di permission
            $this->data['can_read']   = 1;
            $this->data['can_create'] = 1;
            $this->data['can_update'] = 1;
            $this->data['can_delete'] = 1;
        }
    }
}