<?php
error_reporting(E_ALL);
if (!defined('BASEPATH'))exit('No direct script access allowed');
class Test extends My_Controller 
{
    function __construct() {
        parent::__construct();
    }
    function test() {
        $theme_view ='application';
        $this->view_data['next_reference'] = Project::last();
        $this->view_data['category_list'] = Project::get_categories();
        $this->theme_view = 'modal';
        $this->view_data['title'] = $this->lang->line('application_create_project');
        $this->view_data['form_action'] = 'projects/create';
        $this->content_view = 'test/_project';
        
    }

    
}
