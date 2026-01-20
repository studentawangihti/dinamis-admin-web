<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {

    /**
     * FIX HMVC Error: Call to undefined method MY_Loader::_ci_object_to_array()
     * Fungsi ini dihapus di CI 3.1.3+, jadi kita harus menambahkannya kembali secara manual.
     */
    protected function _ci_object_to_array($data)
    {
        return (is_object($data)) ? get_object_vars($data) : $data;
    }

}