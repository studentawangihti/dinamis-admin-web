<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Layout extends MX_Controller {
    
    public function index($data = []) {
        // Load view main_wrapper.php yang berisi <html><body>...
        // Data dikirim dari MY_Controller
        $this->load->view('main_wrapper', $data);
    }
}