<?php  
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Layout
{
    
    var $obj;
    var $layout, $layout_admin;
	var $menu_admin;
	var $menu_page;
    
    function Layout()
    {
        $this->obj =& get_instance();
        $this->layout = "home_page";
		$this->layout_admin = "admin/admin_page";
		$this->menu_page = 1;
    }

    function setLayout($layout)
    {
      $this->layout = $layout;
    }
    
    function view($view, $data=null, $return=false)
    {
        $loadedData = array();
		$loadedData['content_for_menu'] = $this->obj->load->view("menu_page",NULL,true);
        $loadedData['content_for_layout'] = $this->obj->load->view($view,$data,true);

        if($return)
        {
            $output = $this->obj->load->view($this->layout, $loadedData, true);
            return $output;
        }
        else
        {
            $this->obj->load->view($this->layout, $loadedData, false);
        }
    }
	
	function view_admin($view, $data=null, $return=false)
    {
        $loadedData = array();
		$menu['page'] = $this->menu_page;
		$loadedData['content_for_menu'] = $this->obj->load->view("admin/menu_page",$menu,true);
        $loadedData['content_for_layout'] = $this->obj->load->view($view,$data,true);

        if($return)
        {
            $output = $this->obj->load->view($this->layout_admin, $loadedData, true);
            return $output;
        }
        else
        {
            $this->obj->load->view($this->layout_admin, $loadedData, false);
        }
    }
	
	function setMenu($page=1)
	{
		$this->menu_page = $page;
	}
}