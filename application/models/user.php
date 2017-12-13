<?php
class User extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function login($account, $pwd)
    {
		$query = $this->db->get_where('Client', array('account' => $account, 'pwd' => $pwd) );
            
		if($query->num_rows() == 1)
        {
            $this->session->set_userdata('user_id', $query->row()->client_id);
			$this->session->set_userdata('user_name', $query->row()->name);
			if($query->row()->super_id == 1) {
				$this->session->set_userdata('super_id', $query->row()->client_id);
				$this->session->set_userdata('super_name', $query->row()->name);
			}
			else
				$this->session->set_userdata('super_id', 1);
			
			return true;
        }
        else
			return false;
    }
	
	function logout()
	{
		$this->session->sess_destroy();
	}
	
	function check_login()
	{
		if($this->session->userdata('user_id') == NULL)
			$this->Alert->web_goto('home/login');
	}
	
	function check_super()
	{
		if($this->session->userdata('super_id') > 1)
			return true;
		else
			return false;
	}
	
	function set_user($client_id)
	{
		$super_id = $this->session->userdata('super_id');
		if( $this->check_super() == false ) return;
		$query = $this->db->get_where('Client', array('client_id' => $client_id) );
            
		if($query->num_rows() == 1)
        {
			$this->session->set_userdata('user_id', $query->row()->client_id);
			$this->session->set_userdata('user_name', $query->row()->name);
		}
	}
	
	function set_area($area_id)
    {
        $this->session->set_userdata('area_id', $area_id);
    }
	
	function set_am($am_id)
    {
        $this->session->set_userdata('am_id', $am_id);
    }
	
	function set_block($block_id)
    {
        $this->session->set_userdata('block_id', $block_id);
    }
	
	function set_jb($jb_mac)
    {
        $this->session->set_userdata('jb_mac', $jb_mac);
    }
	
}