<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class GOV_Class{
    function __construct(){
        $this->view_path = GOV_PG_MAL_DIR.DIRECTORY_SEPARATOR."html";
    }
    function load_view($fname, $data = []){
        $view_path = get_class($this).DIRECTORY_SEPARATOR.$fname;
        $this->load_rview($view_path, $data);
    }
    function load_rview($viewPath, $data){
        $path_to_html = $this->view_path.DIRECTORY_SEPARATOR.$viewPath;
        if (  ! file_exists($path_to_html) || is_dir($path_to_html) || ! is_file($path_to_html)){
            return FALSE;
        }
        foreach($data as $key=>$value) 
            $$key = $value; 
        include($path_to_html);
    }
    function admin_notices($mode, $str) {
        $this->str = $str;
        if ($mode === "success")
            $this->admin_notice_success();
        if ($mode === "error")
            $this->admin_notice_error();
    }
    private function admin_notice_success(){
        $view_path = get_parent_class($this).DIRECTORY_SEPARATOR."admin_notice_success.php";
        $this->load_rview($view_path, ["str" => $this->str]);
    }
    private function admin_notice_error(){
        $view_path = get_parent_class($this).DIRECTORY_SEPARATOR."admin_notice_error.php";
        $this->load_rview($view_path, ["str" => $this->str]);
    }
}


