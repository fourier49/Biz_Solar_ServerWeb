<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller
{
	public function index()
	{
        $this->Admin_model->check_login();
		//$this->layout->setLayout('admin/admin_page');
		$this->client();
	}
    
// -------------------------------------------------------------------------------
// Login, Logout
// -------------------------------------------------------------------------------
    public function login()
    {
        if($this->session->userdata('account') != "")
            $this->Alert->web_goto('admin/index'); 

        else if($this->input->post('account') != NULL)
        {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('account', 'account', 'required');
			$this->form_validation->set_rules('pwd', 'pwd', 'required');
		
			if($this->form_validation->run() && 
				$this->Admin_model->login($this->input->post('account'), $this->input->post('pwd')) )
                $this->Alert->web_goto('admin/index');
            else {
				$data['login_failed'] = 1;
				$this->load->view('admin/login_page', $data);
			}
        }
		else
			$this->load->view('admin/login_page');
    }
    
    public function logout()
    {
		$this->Admin_model->check_login();
        $this->session->sess_destroy();
        $this->login();
    }
	
	
// -------------------------------------------------------------------------------
// Client & Account Management：
// 		show, add new, edit, delete
// -------------------------------------------------------------------------------
	public function client($op=1, $client_id=NULL)
	{
		$this->Admin_model->check_login();
		switch($op)
		{
			// New Client ---------------------------
			case 'n':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('account', 'Account', 'required|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('pwd', 'Password', 'required|matches[pwdconf]|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('pwdconf', 'Password Confirm', 'required');
				
				if($this->input->post('submit') != NULL && $this->form_validation->run())
				{	
					$client['account'] = $this->input->post('account');
					$client['name'] = $this->input->post('name');
					$client['pwd'] = $this->input->post('pwd');
					$msg = $this->Admin_model->client_insert($client);
					
					if(!$msg) 	$this->Alert->alert('Duplicate account! Please input another one.');
					else		$this->Alert->alert_goto('Add successfully!','admin/client');
				}
				else
					$this->layout->view_admin('admin/client/client_new_page');
				break;
				
			// Edit Client ---------------------------
			case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('account', 'Account', 'required|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('pwd', 'Password', 'required|matches[pwdconf]|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('pwdconf', 'Password Confirm', 'required');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{
					$client['client_id'] = $client_id;
					$client['account'] = $this->input->post('account');
					$client['name'] = $this->input->post('name');
					if($this->input->post('pwd') != "666666")
						$client['pwd'] = $this->input->post('pwd'); //New password
					$msg = $this->Admin_model->client_edit($client);
					
					if(!$msg) 	$this->Alert->alert('Duplicate account! Please input another one.');
					else		$this->Alert->alert_goto('Edit successfully!','admin/client');
				}
				else {
					$data['client'] = $this->Admin_model->client_select_one($client_id);
					$this->layout->view_admin('admin/client/client_edit_page',$data);
				}
				break;
				
			// Delete Client ---------------------------
			case 'd':
				$client_id = $this->input->post('client_id');
					
				if( $this->Admin_model->client_delete($client_id) )
					$this->Alert->alert_goto('Delete successfully!','admin/client');
				else
					$this->Alert->alert('Delete failed!');
					
				break;
			
			// Search Client ------
			case 's':
				$data['keyword'] = $this->input->post('keyword');
				$data['query'] = $this->Admin_model->client_select($op, $data['keyword']);
				$this->layout->view_admin('admin/client/client_page',$data);

				break;
			
			// Show All Clients ---------------------------
			default:
				$data['query'] = $this->Admin_model->client_select($op);
				$this->layout->view_admin('admin/client/client_page',$data);
				break;
		}
	}
	
	public function client_sub($op=1, $super_id, $client_id=NULL)
	{
		$this->Admin_model->check_login();
		$data['super'] = $this->Admin_model->client_select_one($super_id);
		
		switch($op)
		{
			// New Sub Account ---------------------------
			case 'n':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('account', 'Account', 'required|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('pwd', 'Password', 'required|matches[pwdconf]|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('pwdconf', 'Password Confirm', 'required');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{	
					$client['account'] = $this->input->post('account');
					$client['name'] = $this->input->post('name');
					$client['pwd'] = $this->input->post('pwd');
					$client['super_id'] = $super_id;
					$area_id = $this->input->post('area_id');
					$msg = $this->Admin_model->client_sub_insert($client);

					if(!$msg) 	
						$this->Alert->alert('Duplicate account! Please input another one.');
					else {
						$sql = "SELECT client_id FROM Client WHERE account = '".$client['account']."'";
						$client_id = $this->db->query($sql)->row()->client_id;
						
						$client_area = array();
						foreach($area_id as $row)
							$client_area[] = array('client_id' => $client_id ,'area_id' => $row);
							
						$this->db->insert_batch('ClientArea', $client_area); 
						$this->Alert->alert_goto('Add successfully!','admin/client_sub/0/'.$super_id);
					}
				}
				else {
					$data['query'] = $this->Admin_model->area_select($super_id);
					$this->layout->view_admin('admin/client/client_sub_new_page',$data);
				}
				break;
				
			// Edit Sub Account ---------------------------
			case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('account', 'Account', 'required|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('pwd', 'Password', 'required|matches[pwdconf]|min_length[6]|max_length[20]|alpha_numeric');
				$this->form_validation->set_rules('pwdconf', 'Password Confirm', 'required');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{
					$client['client_id'] = $client_id;
					$client['account'] = $this->input->post('account');
					$client['name'] = $this->input->post('name');
					if($this->input->post('pwd') != "666666")
						$client['pwd'] = $this->input->post('pwd'); //New password
					$msg = $this->Admin_model->client_edit($client);
					
					if(!$msg) 	$this->Alert->alert('Duplicate account! Please input another one.');
					else		$this->Alert->alert_goto('Edit successfully!','admin/client_sub/0/'.$super_id);
				}
				else {
					$data['client'] = $this->Admin_model->client_select_one($client_id);
					
					$sql = "SELECT area_id FROM ClientArea WHERE client_id = '$client_id'";
					$data['area'] = $this->db->query($sql)->result_array();
					
					$sql = "SELECT A.*, (Area.area_id)'area_id', (Area.name)'area_name' FROM 
							(SELECT area_id FROM ClientArea WHERE client_id = '$client_id') A
							LEFT JOIN Area ON A.area_id = Area.area_id";
					
					$data['query'] = $this->db->query($sql);
					$this->layout->view_admin('admin/client/client_sub_edit_page',$data);
				}
				break;
				
			// Delete Sub Account ---------------------------
			case 'd':
				$client_id = $this->input->post('client_id');
					
				if( $this->Admin_model->client_delete($client_id) )
					$this->Alert->alert_goto('Delete successfully!','admin/client_sub/0/'.$super_id);
				else
					$this->Alert->alert('Delete failed!');
					
				break;
			
			// Show All Sub Accounts ---------------------------
			default:
				$data['query'] = $this->Admin_model->client_sub_select($super_id);
				$this->layout->view_admin('admin/client/client_sub_page',$data);
				break;
		}
	}
	
	public function client_sub_area($client_id)
	{
		$this->Admin_model->check_login();
		$data['client'] = $this->Admin_model->client_select_one($client_id);
		$data['super'] = $this->Admin_model->client_select_one($data['client']['super_id']);
		
		$sql = "SELECT Area.* FROM 
					(SELECT area_id FROM ClientArea WHERE client_id = '$client_id') A
					LEFT JOIN Area ON A.area_id = Area.area_id";
					
		$data['query'] = $this->db->query($sql);
		$this->layout->view_admin('admin/client/client_sub_area_page',$data);
	}
	
// -------------------------------------------------------------------------------
// Area Management：
// 		show, add new, edit, delete
// -------------------------------------------------------------------------------
	public function area($op=1, $client_id=NULL, $area_id=NULL)
	{
		$this->Admin_model->check_login();
		$data['client'] = $this->Admin_model->client_select_one($client_id);
		
		switch($op)
		{
			// New Area ------
			case 'n':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('country', 'Country', '');
				$this->form_validation->set_rules('address', 'Address', '');
				
				if($this->input->post('submit') != NULL && $this->form_validation->run())
				{	
					$area['client_id'] = $client_id;
					$area['name'] = $this->input->post('name');
					$area['country'] = $this->input->post('country');
					$area['address'] = $this->input->post('address');
					$this->Admin_model->area_insert($area);
					$this->Alert->alert_goto('Add successfully!','admin/area/0/'.$client_id);
				}
				else
					$this->layout->view_admin('admin/client/area_new_page',$data);
				break;
				
			// Edit Area ------
			case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('country', 'Country', '');
				$this->form_validation->set_rules('address', 'Address', '');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{
					$area['client_id'] = $client_id;
					$area['area_id'] = $area_id;
					$area['name'] = $this->input->post('name');
					$area['country'] = $this->input->post('country');
					$area['address'] = $this->input->post('address');
					$this->Admin_model->area_edit($area);
					$this->Alert->web_goto('admin/area/0/'.$client_id);
				}
				else {
					$data['area'] = $this->Admin_model->area_select_one($area_id)->row_array();
					$this->layout->view_admin('admin/client/area_edit_page',$data);
				}
				break;
			
			// Delete Area ------
			case 'd':
				$area_id = $this->input->post('area_id');
				
				if( $this->Admin_model->area_delete($area_id) )
					$this->Alert->web_goto('admin/area/0/'.$client_id);
				else
					$this->Alert->alert('Delete failed!');
					
				break;
			
			// Show All Areas ------
			default:
				$data['query'] = $this->Admin_model->area_select($client_id);
				$this->layout->view_admin('admin/client/area_page',$data);
				break;
		}
	}
	
	// Devices Deploying of one Area =====================
	public function area_device($op=1, $area_id, $page=1, $am_id=NULL)
	{
		$this->Admin_model->check_login();
		$data['area'] = $this->Admin_model->area_select_one($area_id)->row_array();
		$data['client'] = $this->Admin_model->client_select_one($data['area']['client_id']);
		
		switch($op)
		{
			// Add Array Manager into Area
			case 'a':
				if( $this->input->post('mysubmit') != NULL )
				{	
					$am_id = $this->input->post('am_id');
					$this->db->where_in('am_id', $am_id);
					$this->db->update('ArrayManager', array('area_id' => $area_id) ); 
					$this->Alert->alert_goto('Add successfully!','admin/area_device/0/'.$area_id);
				}
				else {
					$data['query'] = $this->Admin_model->am_reg_select();
					$this->layout->view_admin('admin/client/device_add_page',$data);
				}
				break;

			// Edit Array Manager
				case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('row', 'Row', 'required|is_natural_no_zero');
				$this->form_validation->set_rules('col', 'Col', 'required|is_natural_no_zero');
				$this->form_validation->set_rules('ip', 'IP', 'required|valid_ip');
				$this->form_validation->set_rules('port', 'Port', 'required|is_natural_no_zero|less_than[65536]');
				$this->form_validation->set_rules('period', 'Update Period', 'required|is_natural_no_zero');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{
					$device['am_id'] = $am_id;
					$device['row'] = $this->input->post('row');
					$device['col'] = $this->input->post('col');
					$device['ip'] = $this->input->post('ip');
					$device['port'] = $this->input->post('port');
					$device['period'] = $this->input->post('period');
					$this->Admin_model->am_edit($device);
					$this->Alert->alert_goto('Edit successfully!','admin/area_device/0/'.$area_id);
				}
				else {
					$data['device'] = $this->Admin_model->am_select_one($am_id)->row_array();
					$this->layout->view_admin('admin/client/device_edit_page',$data);
				}
				break;
				
			// Remove Array Manager ------
			case 'r':
				if( $this->input->post('mysubmit') != NULL )
				{
					$am_id = $this->input->post('am_id');
					//Set area_id, row, col to default
					$this->db->where_in('am_id', $am_id);
					$this->db->update('ArrayManager', array('area_id' => 1,'row' => 1,'col' => 1) ); 
					$this->Alert->alert_goto('Remove successfully!','admin/area_device/0/'.$area_id);
				}
				else {
					$data['query'] = $this->Admin_model->am_select($area_id, $page);
					$this->layout->view_admin('admin/client/device_remove_page',$data);
				}
				break;
				
			// Search Array Manager ------
			case 's':
				$data['mac'] = $this->input->post('mac');
				$data['query'] = $this->Admin_model->am_select($area_id, $page, $data['mac']);
				$this->layout->view_admin('admin/client/device_page',$data);

				break;
			
			default:
				$data['query'] = $this->Admin_model->am_select($area_id, $page);
				$this->layout->view_admin('admin/client/device_page',$data);
				break;
		}
	}
	
	public function area_block($op=1, $am_id, $block_id=NULL)
	{
		$this->Admin_model->check_login();
		$data['am'] = $this->Admin_model->am_select_one($am_id)->row_array();
		$data['area'] = $this->Admin_model->area_select_one($data['am']['area_id'])->row_array();
		$data['client'] = $this->Admin_model->client_select_one($data['area']['client_id']);

		switch($op)
		{
			// New Block ------
			case 'n':
				if( $this->input->post('submit') != NULL )
				{	
					$block['am_id'] = $am_id;
					$block['block_name'] = $this->input->post('block_name');
					$block['row'] = $this->input->post('row');
					$block['col'] = $this->input->post('col');
					$this->Admin_model->block_insert($block);
					$this->Alert->alert_goto('Add successfully!','admin/area_block/0/'.$am_id);
				}
				else {
					$this->layout->view_admin('admin/client/block_new_page',$data);
				}
				break;
				
			// Edit Block ------
			case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('block_name', 'Block Name', 'required');
				$this->form_validation->set_rules('row', 'Row', 'required|is_natural_no_zero');
				$this->form_validation->set_rules('col', 'Col', 'required|is_natural_no_zero');
				
				if($this->input->post('submit') != NULL && $this->form_validation->run())
				{
					$block['block_id'] = $block_id;
					$block['block_name'] = $this->input->post('block_name');
					$block['row'] = $this->input->post('row');
					$block['col'] = $this->input->post('col');
					$this->Admin_model->block_edit($block);
					$this->Alert->alert_goto('Edit successfully!','admin/area_block/0/'.$am_id);
				}
				else {
					$data['block'] = $this->Admin_model->block_select(NULL,$block_id)->row_array();
					$this->layout->view_admin('admin/client/block_edit_page',$data);
				}
				break;
				
			// Delete Block ------
			case 'd':
				$block_id = $this->input->post('block_id');
				
				if( $this->Admin_model->block_delete($block_id) )
					$this->Alert->web_goto('admin/area_block/0/'.$am_id);
				else
					$this->Alert->alert('Delete failed!');
					
				break;
			
			default:
				$data['query'] = $this->Admin_model->block_select($am_id);
				$this->layout->view_admin('admin/client/block_page',$data);
				break;
		}
	}
	
	// Display Junction Boxes of one Array Manager =====================
	public function area_device_jb($op=1, $block_id, $jb_mac=NULL)
	{
		$this->Admin_model->check_login();
		$data['block'] = $this->Admin_model->block_select(NULL, $block_id)->row_array();
		$data['am'] = $this->Admin_model->am_select_one($data['block']['am_id'])->row_array();
		$data['area'] = $this->Admin_model->area_select_one($data['am']['area_id'])->row_array();
		$data['client'] = $this->Admin_model->client_select_one($data['area']['client_id']);
		
		switch($op)
		{
			// Move JB to the block
			case 'm':
				if($this->input->post('mysubmit'))
				{
				
				$jb_mac = $this->input->post('jb_mac');
				$move_block_id = $this->input->post('block_id');
				
				//$this->Alert->alert($move_block_id);
				
				$sql = "UPDATE JunctionBox SET `block_id` = '$move_block_id' WHERE `jb_mac` IN (";
				foreach($jb_mac as $row)
					$sql.= "UNHEX('".$row."'),";
				$sql .= "-1)";
				
				$this->db->query($sql);
				}
				$this->Alert->web_goto('admin/area_device_jb/0/'.$block_id);
				break;
				
			// Edit JB ------
			case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('pos', 'Pos', 'required|is_natural_no_zero');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{
					$jb['pos'] = $this->input->post('pos');
					$jb['s/n'] = $this->input->post('s/n');
					$jb['firmware_vs'] = $this->input->post('firmware_vs');
					$jb['hardware_vs'] = $this->input->post('hardware_vs');
					$jb['device_spec'] = $this->input->post('device_spec');
					$jb['manufacture_date'] = $this->input->post('manufacture_date');

					$this->Admin_model->jb_edit($jb_mac, $jb);
					$this->Alert->alert_goto('Edit successfully!','admin/area_device_jb/0/'.$block_id);
				}
				else {
					$data['jb'] = $this->Admin_model->jb_select(NULL,$jb_mac)->row_array();
					$this->layout->view_admin('admin/client/jb_edit_page',$data);
				}
				break;
				
			default:
				$data['query'] = $this->Admin_model->jb_select($block_id);
				$data['all_block'] = $this->Admin_model->block_select($data['block']['am_id']);
				$this->layout->view_admin('admin/client/jb_page',$data);
				break;
		}
	}
	
// -------------------------------------------------------------------------------
// Device(Array Manager & Junction Box) Management 
// -------------------------------------------------------------------------------
	public function am($op=1, $page=1)
	{
		$this->Admin_model->check_login();
		$this->layout->setMenu(2);
		$data['op'] = $op;
		$data['page'] = $page;
		$this->load->library('form_validation');
		$this->form_validation->set_rules('first_mac', 'First Mac', 'required|alpha_numeric|exact_length[10]');
		$this->form_validation->set_rules('last_mac', 'Last Mac', 'required|alpha_numeric|exact_length[10]');
		
		switch($op)
		{
			case 'n':
				$first_mac = $this->input->post('first_mac');
				$last_mac = $this->input->post('last_mac');

				$msg = $this->Admin_model->am_insert($first_mac, $last_mac);
				if($msg)
					$this->Alert->alert('Add failed! Duplicate mac as follows: \n'.$msg);
				else
					$this->Alert->alert_goto("Add successfully!", 'admin/am');
				break;
			
			// Search Array Manager ------
			case 's':
				$data['mac'] = $this->input->post('mac');
				$data['query'] = $this->Admin_model->am_adv_select($op, $page, $data['mac']);
				$this->layout->view_admin('admin/am_page',$data);
				break;
				
			default:	
				$data['query'] = $this->Admin_model->am_adv_select($op, $page);
				$this->layout->view_admin('admin/am_page',$data);
				break;
		}
	}
	
	public function am_jb($op=1, $am_id, $jb_mac=NULL)
	{
		$this->Admin_model->check_login();
		$this->layout->setMenu(2);
		$data['am'] = $this->Admin_model->am_select_one($am_id)->row_array();
		
		if($data['am']['area_id'] > 0 ) {
			$client_id = $this->Admin_model->area_select_one($data['am']['area_id'])->row()->client_id;
			$data['client'] = $this->Admin_model->client_select_one($client_id);
		}
		switch($op)
		{
			default:
				//$data['query'] = $this->Admin_model->jb_select($block_id);
				$this->layout->view_admin('admin/am_jb_page',$data);
				break;
		}
	}
	
	public function jb($op=1)
	{
		$this->Admin_model->check_login();
		$this->layout->setMenu(3);

		switch($op)
		{
			// Search Junction Box ------
			case 's':
				if($this->input->post('submit'))
				{
				$data['jb_mac'] = $this->input->post('jb_mac');
				$sql = "SELECT A.*, (C.am_id)'am_id', (C.mac)'am_mac' FROM 
						(
							SELECT HEX(`jb_mac`)'mac', `s/n`, `firmware_vs`, hardware_vs, `device_spec`, `manufacture_date`, 
							`pos`, `block_id`, `state`, `update_time`, 
							(`voltage`*0.01)'voltage', (`current`*0.01)'current', `temp`, `power` 
							FROM JunctionBox WHERE HEX(`jb_mac`) LIKE '".$data['jb_mac']."%' ORDER BY jb_mac 
						) A
							LEFT JOIN Block as B ON A.block_id = B.block_id
							LEFT JOIN ArrayManager as C ON B.am_id = C.am_id
						";
						
				$data['query'] = $this->db->query($sql);
				$this->layout->view_admin('admin/jb_page',$data);
				}
				break;
			
			//Show all disable Junction Box ------
			default:
				$sql = "SELECT HEX(`jb_mac`)'mac', `s/n`, `firmware_vs`, hardware_vs, `device_spec`, `manufacture_date`, 
						`pos`, `block_id`, `state`, `update_time`, 
						(`voltage`*0.01)'voltage', (`current`*0.01)'current', `temp`, `power` 
						FROM JunctionBox WHERE block_id = 2 ORDER BY jb_mac";
				$data['query'] = $this->db->query($sql);
				$this->layout->view_admin('admin/jb_page',$data);
				break;
		}
	}
	
// -------------------------------------------------------------------------------
// Alert Event
// -------------------------------------------------------------------------------
	public function alert()
	{
		$this->Admin_model->check_login();
		$this->layout->setMenu(5);
		
		$sql = "
		SELECT A.*, Client.client_id, Client.name'client_name', 
		Area.name'area_name', ArrayManager.mac'am_mac' FROM 
		(SELECT am_id, HEX(`jb_mac`)'jb_mac', state FROM Alert 
		ORDER BY time DESC LIMIT 0,50) A 
		LEFT JOIN ArrayManager ON ArrayManager.am_id = A.am_id 
		LEFT JOIN Area ON Area.area_id = ArrayManager.area_id
		LEFT JOIN Client ON Client.client_id = Area.client_id
		";
		$data['query'] = $this->db->query($sql);
		$this->layout->view_admin('admin/alert_page',$data);
	}

// -------------------------------------------------------------------------------
// System Log
// -------------------------------------------------------------------------------
	public function log($log_id=NULL)
	{
		$this->Admin_model->check_login();
		$this->layout->setMenu(4);
		
		if($log_id != NULL)
		{
			$sql = "SELECT * FROM Log WHERE log_id = ".$log_id;
			$data['log'] = $this->db->query($sql)->row_array();
			$this->layout->view_admin('admin/log_detail_page',$data);
		}	
		else
		{	
			$sql = "SELECT * FROM Log WHERE 1=1 LIMIT 0,50";
			$data['query'] = $this->db->query($sql);
			$this->layout->view_admin('admin/log_page',$data);
		}
	}
	
// -------------------------------------------------------------------------------
// Login User Monitor
// -------------------------------------------------------------------------------
	public function go_monitor($client_id)
	{
		$this->Admin_model->check_login();
		$this->Admin_model->login_monitor($client_id);
		
		$this->Alert->web_goto('home/index');
	}
	
// -------------------------------------------------------------------------------
// simulated data
// -------------------------------------------------------------------------------
	public function testdata()
	{
		$this->Admin_model->check_login();
		$power = 1000;
		$temp = 25;
		
		$mac = "0000000012";
		for($day = 1; $day < 366; $day++)
		for($hour = 0; $hour < 24; $hour++)
		{
			$time = date("Y-m-d H:i:s", mktime($hour, 0, 0, 1, $day, 2013));
			
			if($hour > 6 && $hour < 19) {
				$t = ($hour-13)*60;
				$power = (int)( pow( 2 , -pow(0.01*$t, 2) ) * 1000 );
				$temp = (int)( pow( 2 , -pow(0.01*$t, 2) ) * 15 ) + 25;
				$sql = "INSERT INTO `PanelData2` (`jb_mac`,`power`,`temp`,`time`) VALUES (UNHEX('$mac'),'$power','$temp','$time')";
				
				for($min = 1; $min < 60; $min++)
				{
					$time = date("Y-m-d H:i:s", mktime($hour, $min, 0, 1, $day, 2013));
					$power = (int)( pow( 2 , -pow(0.01*($t+$min), 2) ) * 1000 );
					$temp = (int)( pow( 2 , -pow(0.01*($t+$min), 2) ) * 15 ) + 25;
					$sql.= ",(UNHEX('$mac'),'$power','$temp','$time')";
				}
			}
			else {
				$power = 0;
				$temp = 25;
				$sql = "INSERT INTO `PanelData2` (`jb_mac`,`power`,`temp`,`time`) VALUES (UNHEX('$mac'),'$power','$temp','$time')";
				
				for($min = 1; $min < 60; $min++)
				{
					$time = date("Y-m-d H:i:s", mktime($hour, $min, 0, 1, $day, 2013));
					$sql.= ",(UNHEX('$mac'),'$power','$temp','$time')";
				}
			}
			
			$this->db->query($sql);
			//msleep(10);
		}
		
		/*
		$block_id = 11;
		for($day = 1; $day < 366; $day++)
		for($hour = 0; $hour < 24; $hour++)
		{
			$time = date("Y-m-d H:i:s", mktime($hour, 0, 0, 1, $day, 2013));
			
			if($hour > 6 && $hour < 19) {
				$t = ($hour-13)*60;
				$power = (int)( pow( 2 , -pow(0.01*$t, 2) ) * 2000 );
				$sql = "INSERT INTO `BlockData2` (`block_id`,`power`,`time`) VALUES ('$block_id','$power','$time')";
				
				for($min = 1; $min < 60; $min++)
				{
					$time = date("Y-m-d H:i:s", mktime($hour, $min, 0, 1, $day, 2013));
					$power = (int)( pow( 2 , -pow(0.01*($t+$min), 2) ) * 2000 );
					$sql.= ",('$block_id','$power','$time')";
				}
			} 
			else {
				$power = 0;
				$sql = "INSERT INTO `BlockData2` (`block_id`,`power`,`time`) VALUES ('$block_id','$power','$time')";
				
				for($min = 1; $min < 60; $min++)
				{
					$time = date("Y-m-d H:i:s", mktime($hour, $min, 0, 1, $day, 2013));
					$sql.= ",('$block_id','$power','$time')";
				}
			}
			
			$this->db->query($sql);
		}
		*/
		
		//sql = "SELECT COUNT(`temp`) FROM `PanelData2`";
		//$this->Alert->alert('共新增 '.$i.' 筆'); 
		
		$this->Alert->alert('執行成功');
	}
}
