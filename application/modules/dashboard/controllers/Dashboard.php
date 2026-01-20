<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
    
    public function index() {
        // Render view 'index' milik modul dashboard
        // Parameter kedua adalah data yang mau dikirim ke view konten
        $this->render('index', [
            'total_user' => 100, // Contoh data dummy
            'page_title' => 'Dashboard Utama'
        ]);
    }
}