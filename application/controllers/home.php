<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller
{
	public function index()
	{
        $this->User->check_login();
		//if($this->User->check_super())
		//	$this->sub_account();
		//else
			$this->area();
	}
    
    public function login()
    {
        if($this->session->userdata('account') != "")
            $this->Alert->web_goto('home/index'); 
        
        else if($this->input->post('account') != NULL)
        {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('account', 'account', 'required');
			$this->form_validation->set_rules('pwd', 'pwd', 'required');
		
			if($this->form_validation->run() && 
				$this->User->login($this->input->post('account'), $this->input->post('pwd')) )
                $this->Alert->web_goto('home/index');
            else {
				$data['login_failed'] = 1;
				$this->load->view('login_page', $data);
			}
        }
		else
			$this->load->view('login_page');
    }
    
    public function logout()
    {
        $this->session->sess_destroy();
        $this->login();
    }
	
/******************************************************************************
 * Sub Account
 ******************************************************************************/	
	public function sub_account($client_id=NULL)
	{
		$this->User->check_login();
		
		if($client_id != NULL) {
			$this->User->set_user($client_id);
			$this->area();
		}
		else {
			$super_id = $this->session->userdata('super_id');
			$data['client'] = $this->Admin_model->client_select_one($super_id);	
			$this->layout->view('account_page',$data);
		}
	}
	
	//Get Sub Account List of one Client
	public function get_sub_account()
	{
		$this->User->check_login();
		if($this->User->check_super() == false)	return;
		$super_id = $this->session->userdata('super_id');
		
		$start = $this->input->get('start');
		$limit = $this->input->get('limit');
		//$page = $this->input->get('page');
		$sort = $this->input->get('sort');
		$dir = $this->input->get('dir');

		$sql = "SELECT A.*, B.area_num FROM
				(SELECT client_id, account, name, insert_time FROM Client 
					WHERE super_id = '$super_id' ORDER BY ".$sort." ".$dir." LIMIT ".$start.",".$limit.") A 
				LEFT JOIN
					(SELECT client_id, COUNT(client_id)'area_num' FROM ClientArea GROUP BY client_id) B 
				ON A.client_id = B.client_id";
		
		$data = $this->db->query($sql)->result_object();
		$sql2 = "SELECT COUNT(`super_id`)'total' FROM Client WHERE super_id = '$super_id'";
		$totalCount = $this->db->query($sql2)->row()->total;

		echo '({"totalCount":"'.$totalCount.'","results":'.json_encode($data).'})';
	}
	
/******************************************************************************
 * Area
 ******************************************************************************/	
	public function area($op=NULL, $area_id=NULL)
	{
		$this->User->check_login();
		$client_id = $this->session->userdata('user_id');
		if($this->User->check_super() == false)	$op=NULL;
		
		switch($op)
		{
			// New Area ------
			case 'n':
				if($this->input->post('submit') != NULL)
				{	
					$area['client_id'] = $this->session->userdata('user_id');
					$area['name'] = $this->input->post('name');
					$area['country'] = $this->input->post('country');
					$area['address'] = $this->input->post('address');
					$this->Admin_model->area_insert($area);
					$this->Alert->alert_goto('Add successfully!','home/area');
				}
				else
					$this->layout->view('area_new_page');
				break;
				
			// Edit Area ------
			case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('country', 'Country', '');
				$this->form_validation->set_rules('address', 'Address', '');
				
				if($this->input->post('mysubmit') != NULL)
				{
					$area['client_id'] = $this->session->userdata('user_id');
					$area['area_id'] = $area_id;
					$area['name'] = $this->input->post('name');
					$area['country'] = $this->input->post('country');
					$area['address'] = $this->input->post('address');
					$this->Admin_model->area_edit($area);
					$this->Alert->web_goto('home/area');
				}
				else {
					$data['area'] = $this->Admin_model->area_select_one($area_id)->row_array();
					$this->layout->view('area_edit_page',$data);
				}
				break;
			
			// Delete Area ------
			case 'd':
				$area_id = $this->input->post('area_id');
				
				if( $this->Admin_model->area_delete($area_id) )
					$this->Alert->web_goto('home/area');
				else
					$this->Alert->alert('Delete failed!');
				break;
			
			// Show All Areas ------
			default:
				$data['alert'] = $this->Monitor_model->alert_jb_select($client_id);	
				$this->layout->view('area_page', $data);
				break;
		}
	}
	 
	//Get Area Info List of one Client
	public function get_area()
	{
		$this->User->check_login();
		$client_id = $this->session->userdata('user_id');
		
		$start = $this->input->get('start');
		$limit = $this->input->get('limit');
		//$page = $this->input->get('page');
		$sort = $this->input->get('sort');
		$dir = $this->input->get('dir');
		
		if($this->User->check_super() && $client_id == $this->session->userdata('super_id'))
		{ 
			$table = "Area";
			$sql = "SELECT A.*, B.area_power FROM
				( SELECT * FROM Area WHERE `client_id` = '$client_id' 
				) A";
		} else {
			$table = "ClientArea";
			$sql = "SELECT A.*, B.area_power FROM
				( SELECT * FROM Area WHERE `area_id` IN 
					(SELECT `area_id` FROM ClientArea WHERE `client_id` = '$client_id')
				) A";
		}
		
		$sql .= " LEFT JOIN 
		(
			SELECT SUM(am_power)'area_power', area_id FROM 
			(
			SELECT SUM(block_power)'am_power', am_id FROM 
			(
			SELECT `block_id`, (SUM(`power`)*0.001)'block_power' FROM JunctionBox WHERE `block_id` IN (
				SELECT `block_id` FROM Block WHERE `am_id` IN (
					SELECT `am_id` FROM ArrayManager WHERE `area_id` IN (
						SELECT `area_id` FROM ".$table." WHERE `client_id` = '$client_id'
						)
					)
				) GROUP BY `block_id`
			) C LEFT JOIN Block ON C.block_id = Block.block_id 
				GROUP BY am_id
			) D LEFT JOIN ArrayManager ON D.am_id = ArrayManager.am_id 
				GROUP BY area_id
		) B 
		ON A.area_id = B.area_id ORDER BY ".$sort." ".$dir." LIMIT ".$start.",".$limit;
				
		$data = $this->db->query($sql)->result_object();
		$sql2 = "SELECT COUNT(`area_id`)'total' FROM ".$table." WHERE client_id = '$client_id'";
		$totalCount = $this->db->query($sql2)->row()->total;
		/*
		$data[0]['area_id'] = '1';$data[0]['name'] = '111';
		$data[1]['area_id'] = '2';$data[1]['name'] = '222';
		$totaldata = 2;
		*/
		echo '({"totalCount":"'.$totalCount.'","results":'.json_encode($data).'})';
	}
	
	//Get Area Power History Data
	public function get_area_power()
	{
		$this->User->check_login();
		$result = array('name' => '', 'categories' => array(), 'data' => array());
		
		$level = (int)$this->input->post('level');
		$year = (int)$this->input->post('year');
		$month = (int)$this->input->post('month');
		$day = (int)$this->input->post('day');
		$area_id = $this->session->userdata('area_id');
		$power_sum = 0;
		
		if($level < 0) {
			$query = $this->Monitor_model->area_power_select($area_id, $year, null, null);
			$power_sum = $this->Monitor_model->area_power_sum($area_id, $year, null, null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("Y", mktime(0, 0, 0, 1, 1, $year));
		}
		else if($level == 0) {
			$query = $this->Monitor_model->area_power_select($area_id, $year, $month, null);
			$power_sum = $this->Monitor_model->area_power_sum($area_id, $year, $month,  null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("M, Y", mktime(0, 0, 0, $month, 1, $year));
		}
		else if($level == 1) {
			$query = $this->Monitor_model->area_power_select($area_id, $year, $month, $day);
			$power_sum = $this->Monitor_model->area_power_sum($area_id, $year, $month, $day);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['data'][] = array( (int)$row['x']*1000, (int)$row['y'] );
			}
			$result['name'] = date("Y/m/d", mktime(0, 0, 0, $month, $day, $year))." ~ ".
								date("Y/m/d", mktime(0, 0, 0, $month, $day+6, $year));
		}
		
		$result['name'] .= " production: ".(int)$power_sum." kWh";

		echo json_encode($result);
	}
	

/******************************************************************************
 * Array Manager
 ******************************************************************************/
	public function am($op=NULL, $area_id, $am_id=NULL)
	{
		$this->User->check_login();
		if($this->User->check_super() == false)	$op=NULL;
		if($area_id < 1)	$area_id = $this->session->userdata('area_id');
		else	$this->User->set_area($area_id);
		
		$data['area'] = $this->Admin_model->area_select_one($area_id)->row_array();
		$data['power'] = $this->Monitor_model->area_power_select($area_id)->result_array();
		$power_sum = $this->Monitor_model->area_power_sum($area_id);
		$data['power_name'] = date('Y')." production: ".(int)$power_sum." kWh";
		$data['power_week'] = $this->Monitor_model->area_power_select($area_id, date('Y'), date('n'), date('j'))->result_array();
		//$data['power_week'] = $this->Monitor_model->area_power_select($area_id, date('Y'), date('n'), date('j')-date('w'))->result_array();
		$power_week_sum = $this->Monitor_model->area_power_sum($area_id, date('Y'), date('n'), date('j'));
		//$power_week_sum = $this->Monitor_model->area_power_sum($area_id, date('Y'), date('n'), date('j')-date('w'));
		$data['power_week_name'] = 
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j'), date('Y')))." ~ ".
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j')+6, date('Y')))." production: ".(int)$power_week_sum." kWh";
		/*
		$data['power_week_name'] = 
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j')-date('w'), date('Y')))." ~ ".
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j')-date('w')+6, date('Y')))." production: ".(int)$power_week_sum." kWh";
		*/
		switch($op)
		{
			// Edit, Move Array Manager
			/*case 'e':
			
				$this->load->library('form_validation');
				$this->form_validation->set_rules('ip', 'IP', 'required|valid_ip');
				$this->form_validation->set_rules('port', 'Port', 'required|is_natural_no_zero|less_than[65536]');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{
					$device['am_id'] = $am_id;
					$device['ip'] = $this->input->post('ip');
					$device['port'] = $this->input->post('port');
					$this->Admin_model->am_edit($device);
					$this->Alert->alert_goto('Edit successfully!','home/am/0/'.$area_id);
				}
				else {
					$data['device'] = $this->Admin_model->am_select_one($am_id)->row_array();
					$this->layout->view('am_edit_page',$data);
				}
				break;*/
				
			// Remove Array Manager ------
			case 'r':
				$am_id = $this->input->post('am_id');
				//Set area_id, row, col to default
				$this->db->where_in('am_id', $am_id);
				$this->db->update('ArrayManager', array('area_id' => 2,'row' => 1,'col' => 1) ); 
				$this->Alert->alert_goto('Remove successfully!','home/am/0/'.$area_id);
				break;
			
			default:
				$sql = "
				SELECT (SUM(`power`)*0.001)'power' FROM JunctionBox WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` IN (
						SELECT `am_id` FROM ArrayManager WHERE `area_id` = '$area_id'
						)
				) LIMIT 1";
				$data['area_power'] = $this->db->query($sql)->row()->power;
				$this->layout->view('am_page',$data);
				break;
		}
	}
	
	//Get Array Manager Info List of one Area
	public function get_am()
	{
		$this->User->check_login();
		$area_id = $this->session->userdata('area_id');
		
		$start = $this->input->get('start');
		$limit = $this->input->get('limit');
		//$page = $this->input->get('page');
		$sort = $this->input->get('sort');
		$dir = $this->input->get('dir');

		$sql = "
		SELECT A.*, B.am_power FROM
		( SELECT am_id, mac, ip, port, period, register_time FROM ArrayManager WHERE area_id = '$area_id' 
		) A
		LEFT JOIN 
		(
			SELECT SUM(block_power)'am_power', am_id FROM 
			(
			SELECT `block_id`, (SUM(`power`)*0.001)'block_power' FROM JunctionBox WHERE `block_id` IN (
				SELECT `block_id` FROM Block WHERE `am_id` IN (
					SELECT `am_id` FROM ArrayManager WHERE `area_id` = '$area_id'
					)
				) GROUP BY `block_id`
			) C LEFT JOIN Block ON C.block_id = Block.block_id 
				GROUP BY am_id
		) B 
		ON A.am_id = B.am_id ORDER BY ".$sort." ".$dir." LIMIT ".$start.",".$limit;
		
		$data = $this->db->query($sql)->result_object();
		$sql2 = "SELECT COUNT(`am_id`)'total' FROM ArrayManager WHERE area_id = '$area_id'";
		$totalCount = $this->db->query($sql2)->row()->total;

		echo '({"totalCount":"'.$totalCount.'","results":'.json_encode($data).'})';
	}
	
	//Get Am Power History Data
	public function get_am_power()
	{
		$this->User->check_login();
		$result = array('name' => '', 'categories' => array(), 'data' => array());
		
		$level = (int)$this->input->post('level');
		$year = (int)$this->input->post('year');
		$month = (int)$this->input->post('month');
		$day = (int)$this->input->post('day');
		$am_id = $this->session->userdata('am_id');
		$power_sum = 0;
		
		if($level < 0) {
			$query = $this->Monitor_model->am_power_select($am_id, $year, null, null);
			$power_sum = $this->Monitor_model->am_power_sum($am_id, $year, null, null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("Y", mktime(0, 0, 0, 1, 1, $year));
		}
		else if($level == 0) {
			$query = $this->Monitor_model->am_power_select($am_id, $year, $month, null);
			$power_sum = $this->Monitor_model->am_power_sum($am_id, $year, $month,  null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("M, Y", mktime(0, 0, 0, $month, 1, $year));
		}
		else if($level == 1) {
			$query = $this->Monitor_model->am_power_select($am_id, $year, $month, $day);
			$power_sum = $this->Monitor_model->am_power_sum($am_id, $year, $month, $day);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['data'][] = array( (int)$row['x']*1000, (double)$row['y'] );
			}
			$result['name'] = date("Y/m/d", mktime(0, 0, 0, $month, $day, $year))." ~ ".
								date("Y/m/d", mktime(0, 0, 0, $month, $day+6, $year));
		}
		
		$result['name'] .= " production: ".(int)$power_sum." kWh";

		echo json_encode($result);
	}
	

/******************************************************************************
 * Block
 ******************************************************************************/
	public function block($op=NULL, $am_id, $block_id=NULL)
	{
		$this->User->check_login();
		if($am_id < 1)	$area_id = $this->session->userdata('am_id');
		else	$this->User->set_am($am_id);
		
		$data['am'] = $this->Admin_model->am_select_one($am_id)->row_array();
		$data['area'] = $this->Admin_model->area_select_one($data['am']['area_id'])->row_array();
		
		$data['power'] = $this->Monitor_model->am_power_select($am_id)->result_array();
		$power_sum = $this->Monitor_model->am_power_sum($am_id);
		$data['power_name'] = date('Y')." production: ".(int)$power_sum." kWh";
		$data['power_week'] = $this->Monitor_model->am_power_select($am_id, date('Y'), date('n'), date('j'))->result_array();
		$power_week_sum = $this->Monitor_model->am_power_sum($am_id, date('Y'), date('n'), date('j'));
		$data['power_week_name'] = 
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j'), date('Y')))." ~ ".
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j')+6, date('Y')))." production: ".(int)$power_week_sum." kWh";
		
		if($this->User->check_super() == false)	$op=NULL;
		
		switch($op)
		{
			// New Block ------
			case 'n':
				if( $this->input->post('submit') != NULL )
				{	
					$block['am_id'] = $am_id;
					$block['row'] = $this->input->post('row');
					$block['col'] = $this->input->post('col');
					$this->Admin_model->block_insert($block);
					$this->Alert->alert_goto('Add successfully!','home/block/0/'.$am_id);
				}
				else {
					$this->layout->view('block_edit_page',$data);
				}
				break;
				
			// Edit Block ------
			case 'e':
				$this->load->library('form_validation');
				$this->form_validation->set_rules('row', 'Row', 'required|is_natural_no_zero');
				$this->form_validation->set_rules('col', 'Col', 'required|is_natural_no_zero');
				
				if($this->input->post('mysubmit') != NULL && $this->form_validation->run())
				{
					$block['block_id'] = $block_id;
					$block['row'] = $this->input->post('row');
					$block['col'] = $this->input->post('col');
					$this->Admin_model->block_edit($block);
					$this->Alert->alert_goto('Edit successfully!','home/block/0/'.$am_id);
				}
				else {
					$data['block'] = $this->Admin_model->block_select(NULL,$block_id)->row_array();
					$this->layout->view('block_edit_page',$data);
				}
				break;
				
			// Delete Block ------
			case 'd':
				$block_id = $this->input->post('block_id');
				
				if( $this->Admin_model->block_delete($block_id) )
					$this->Alert->web_goto('home/block/0/'.$am_id);
				else
					$this->Alert->alert('Delete failed!');
					
				break;
			
			default:
				$query = $this->Monitor_model->block_select($am_id);

				$index = 0;
				$block_pos = array();
				$block_info = array();
				foreach($query->result_array() as $tmp)
				{
					$block_pos[$tmp['row']][$tmp['col']] = array(
						'power'=>$tmp['power'], 'index'=>$index, 'state'=>(int)$tmp['state2']);

					$block_info[$index] = array(
						'block_id'=>(int)$tmp['block_id'], 'row'=>(int)$tmp['row'], 'col'=>(int)$tmp['col'], 
						'V'=>(int)$tmp['voltage'], 'A'=>(int)$tmp['current'], 'T'=>(int)$tmp['temp'], 
						'W'=>$tmp['power'], 'Time'=>$tmp['update_time'], 'S'=>(int)$tmp['state2'] );
					$index++;
				}
				$data['block'] = $block_pos;
				$data['block_info'] = $block_info;
				
				$sql = "
				SELECT (SUM(`power`)*0.001)'power', (AVG(`voltage`)*0.01)'voltage', (AVG(`current`)*0.01)'current', AVG(`temp`)'temp', MAX(`update_time`)'update_time'
				FROM JunctionBox WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` = '$am_id'
				) LIMIT 1";
				$data['am_data'] = $this->db->query($sql)->row_array();
				
				$sql = "SELECT MAX(`row`)'max_row', MAX(`col`)'max_col', COUNT(`block_id`)'num' FROM Block WHERE `am_id` = '$am_id' LIMIT 1";
				$info = $this->db->query($sql)->row_array();
				$data['max_row'] = $info['max_row'];
				$data['max_col'] = $info['max_col'];
				$data['block_num'] = $info['num'];
				
				$this->layout->view('block_page',$data);
				break;
		}
	}
	
	//Get Block Info List of one Array Manager
	public function get_block()
	{
		$this->User->check_login();
		$am_id = $this->session->userdata('am_id');
		
		$start = $this->input->get('start');
		$limit = $this->input->get('limit');
		$sort = $this->input->get('sort');
		$dir = $this->input->get('dir');

		$sql = "
		SELECT A.block_name, B.* FROM `Block` A 
		LEFT JOIN (
			SELECT `block_id`, COUNT('block_id')'jb_num', (SUM(`power`)*0.001)'block_power',
			MAX(`state2`)'state', MAX(`update_time`)'update_time' 
			FROM JunctionBox WHERE `block_id` IN (
				SELECT `block_id` FROM Block WHERE `am_id` = '$am_id' 	
			) GROUP BY `block_id` 
		) B ON A.block_id = B.block_id WHERE `am_id` = ".$am_id."
		ORDER BY ".$sort." ".$dir." LIMIT ".$start.",".$limit;
		
		$data = $this->db->query($sql)->result_object();
		$sql2 = "SELECT COUNT(`block_id`)'total' FROM Block WHERE am_id = '$am_id'";
		$totalCount = $this->db->query($sql2)->row()->total;

		echo '({"totalCount":"'.$totalCount.'","results":'.json_encode($data).'})';
	}
	
	//Get Block Power History Data
	public function get_block_power()
	{
		$this->User->check_login();
		$result = array('name' => '', 'categories' => array(), 'data' => array());
		
		$level = (int)$this->input->post('level');
		$year = (int)$this->input->post('year');
		$month = (int)$this->input->post('month');
		$day = (int)$this->input->post('day');
		$block_id = $this->session->userdata('block_id');
		$power_sum = 0;
		
		if($level < 0) {
			$query = $this->Monitor_model->block_power_select($block_id, $year, null, null);
			$power_sum = $this->Monitor_model->block_power_sum($block_id, $year, null, null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("Y", mktime(0, 0, 0, 1, 1, $year));
		}
		else if($level == 0) {
			$query = $this->Monitor_model->block_power_select($block_id, $year, $month, null);
			$power_sum = $this->Monitor_model->block_power_sum($block_id, $year, $month,  null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("M, Y", mktime(0, 0, 0, $month, 1, $year));
		}
		else if($level == 1) {
			$query = $this->Monitor_model->block_power_select($block_id, $year, $month, $day);
			$power_sum = $this->Monitor_model->block_power_sum($block_id, $year, $month, $day);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['data'][] = array( (int)$row['x']*1000, (double)$row['y'] );
			}
			$result['name'] = date("Y/m/d", mktime(0, 0, 0, $month, $day, $year))." ~ ".
								date("Y/m/d", mktime(0, 0, 0, $month, $day+6, $year));
		}
		
		$result['name'] .= " production: ".(int)$power_sum." kWh";

		echo json_encode($result);
	}
/*
	public function block_edit($am_id, $op=0)
	{
		$this->User->check_login();
		if($this->User->check_super() == false)	return;

		if($this->input->post('begin_x') > 0) {
			$begin_x = $this->input->post('begin_x');
			$begin_y = $this->input->post('begin_y');
			$end_x = $this->input->post('end_x');
			$end_y = $this->input->post('end_y');
			
			switch($op)
			{
			case 0:
				$msg = $this->Monitor_model->block_insert($am_id, $begin_x, $begin_y, $end_x, $end_y);
			break;
			
			case 1:
				$msg = $this->Monitor_model->block_update_pos($am_id, $begin_x, $begin_y, $end_x, $end_y);
			break;
			
			case 2:
				$msg = $this->Monitor_model->block_delete($am_id, $begin_x, $begin_y, $end_x, $end_y);
			break;
			
			default:
			break;
			}
			
			if($msg == false)
				$this->Alert->alert_goto("Failed to edit!", 'home/block_edit/'.$am_id.'/'.$op);
		}
		
		
		$query = $this->Monitor_model->block_select_jb_num($am_id);

		$block_pos = array();
		foreach($query->result_array() as $tmp)
		{
			$block_pos[$tmp['row']][$tmp['col']]['exist'] = 1;
			$block_pos[$tmp['row']][$tmp['col']]['jb_num'] = (int)$tmp['jb_num'];
		}
		$data['block'] = $block_pos;
		$data['am_id'] = $am_id;
		$data['op'] = $op;
		//$this->Alert->alert($op);
		$this->layout->view('block_edit_page',$data);
	}
*/
	function jb_deploy($block_id, $op=0) 
	{
		$this->User->check_login();
		if($this->User->check_super() == false)	return;

		if($this->input->post('end_col') > 0) {

			$end_col = $this->input->post('end_col');
			$end_row = $this->input->post('end_row');
			
			switch($op)
			{
			case 0: // Move J-box
				$jb_mac = $this->input->post('jb_mac');
				$msg = $this->Monitor_model->jb_update_row_col($jb_mac, $end_col, $end_row);
			break;
			
			case 1: // Move J-Box to block 
				$begin_col = $this->input->post('begin_col');
				$begin_row = $this->input->post('begin_row');
				$block_id_new = $this->input->post('block_id_new');
				//$this->Alert->alert($begin_col." ".$begin_row." ".$end_col." ".$end_row);
				$msg = $this->Monitor_model->jb_update_blockid(
								$block_id, $block_id_new, $begin_col, $begin_row, $end_col, $end_row);
			break;
			
			default:
			break;
			}
			
			if($msg == false)
				$this->Alert->alert_goto("Failed to move J-Box!", 'home/jb_deploy/'.$block_id.'/'.$op);
		}
		
		$am_id = $this->session->userdata('am_id');
		$data['block'] = $this->Monitor_model->block_select_jb_num($am_id);
		
		$query = $this->Monitor_model->jb_select_row_col($block_id);

		$jb = array();
		$unset_jb = array();
		foreach($query->result_array() as $tmp)
		{
			if($tmp['row'] < 0 && $tmp['col'] < 0)
				$unset_jb[] = $tmp['jb_mac'];
			else
				$jb[$tmp['row']][$tmp['col']] = $tmp['jb_mac'];
		}
		$data['jb'] = $jb;
		$data['unset_jb'] = $unset_jb;
		$data['block_id'] = $block_id;
		$data['op'] = $op;
		//$this->Alert->alert($op);
		
		$this->load->view('jb_deploy_page',$data);
	}

/******************************************************************************
 * Junction Box
 ******************************************************************************/
	public function jb($op=NULL, $block_id, $jb_mac=NULL)
	{
		$this->User->check_login();
		$this->User->set_block($block_id);
		$data['block'] = $this->Admin_model->block_select(NULL, $block_id)->row_array();
		$data['am'] = $this->Admin_model->am_select_one($data['block']['am_id'])->row_array();
		$data['area'] = $this->Admin_model->area_select_one($data['am']['area_id'])->row_array();
		
		if($this->User->check_super() == false)	$op = NULL;
		
		$data['power'] = $this->Monitor_model->block_power_select($block_id)->result_array();
		$power_sum = $this->Monitor_model->block_power_sum($block_id);
		$data['power_name'] = date('Y')." production: ".(int)$power_sum." kWh";
		$data['power_week'] = $this->Monitor_model->block_power_select($block_id, date('Y'), date('n'), date('j'))->result_array();
		$power_week_sum = $this->Monitor_model->block_power_sum($block_id, date('Y'), date('n'), date('j'));
		$data['power_week_name'] = 
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j'), date('Y')))." ~ ".
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j')+6, date('Y')))." production: ".(int)$power_week_sum." kWh";
		
		
		$sql = "
			SELECT (SUM(`power`)*0.001)'power', (AVG(`voltage`)*0.01)'voltage', 
			(AVG(`current`)*0.01)'current', AVG(`temp`)'temp', MAX(`update_time`)'update_time'
			FROM JunctionBox WHERE `block_id` = ".$block_id." LIMIT 1";
		$data['block_data'] = $this->db->query($sql)->row_array();
		$query = $this->Admin_model->jb_select($block_id);
		$jb = array();	$jb_data = array();
		$max_col = 0;
		$max_row = 0;
				
		foreach($query->result_array() as $tmp)
		if($tmp['row'] > 0 && $tmp['col'] > 0)
		{
			$jb[$tmp['row']][$tmp['col']]['jb_mac'] = $tmp['jb_mac'];
			$jb[$tmp['row']][$tmp['col']]['state'] = $tmp['state'];
			$jb[$tmp['row']][$tmp['col']]['power'] = $tmp['power'];
			
			$jb_data[$tmp['jb_mac']] = array(
				'S'=>$tmp['state'],	'power'=>(int)$tmp['power'],
				'V'=>$tmp['voltage'], 'A'=>$tmp['current'], 'T'=>$tmp['temp'], 
				'W'=>$tmp['power'], 'Time'=>$tmp['update_time'] );
				
			if($tmp['col'] > $max_col)
				$max_col = $tmp['col'];
			if($tmp['row'] > $max_row)
				$max_row = $tmp['row'];
		}
		
		$data['jb'] = $jb;
		$data['jb_data'] = $jb_data;
		$data['max_col'] = $max_col;
		$data['max_row'] = $max_row;
		
		$this->layout->view('jb_page',$data);
		
			
		
		/*
		switch($op)
		{
			// Move JB to new Pos
			case 'm':
				$new_pos = $this->input->post('new_pos');
				$old_pos = $this->input->post('old_pos');
				$this->Monitor_model->jb_pos_update($block_id, $old_pos, $new_pos);
				return; break;
			
			default:
				$data['power'] = $this->Monitor_model->block_power_select($block_id)->result_array();
				$power_sum = $this->Monitor_model->block_power_sum($block_id);
				$data['power_name'] = date('Y')." production: ".(int)$power_sum." kWh";
				$data['power_week'] = $this->Monitor_model->block_power_select($block_id, date('Y'), date('n'), date('j'))->result_array();
				$power_week_sum = $this->Monitor_model->block_power_sum($block_id, date('Y'), date('n'), date('j'));
				$data['power_week_name'] = 
					date("Y/n/j", mktime(0, 0, 0, date('n'), date('j'), date('Y')))." ~ ".
					date("Y/n/j", mktime(0, 0, 0, date('n'), date('j')+6, date('Y')))." production: ".(int)$power_week_sum." kWh";
			
				$sql = "
				SELECT (SUM(`power`)*0.001)'power', (AVG(`voltage`)*0.01)'voltage', (AVG(`current`)*0.01)'current', AVG(`temp`)'temp', MAX(`update_time`)'update_time'
				FROM JunctionBox WHERE `block_id` = ".$block_id." LIMIT 1";
				$data['block_data'] = $this->db->query($sql)->row_array();

				$query = $this->Admin_model->jb_select($block_id);
				$jb = array(); $num = 0;
				
				foreach($query->result_array() as $tmp)
				{
					$pos = (int)$tmp['pos'];
					$jb[$pos] = array(
						'jb_mac'=>$tmp['mac'], 'S'=>$tmp['state'],	'power'=>(int)$tmp['power'],
						'V'=>$tmp['voltage'], 'A'=>$tmp['current'], 'T'=>$tmp['temp'], 
						'W'=>$tmp['power'], 'Time'=>$tmp['update_time'] );
					$num++;
				}
				$data['jb'] = $jb;
				$data['jb_num'] = $num;
				$this->layout->view('jb_page',$data);
				break;
		}
		*/
	}
	
	//Get J-Box Info List of one Array Manager
	public function get_jb()
	{
		$this->User->check_login();
		$block_id = $this->session->userdata('block_id');
		
		$start = $this->input->get('start');
		$limit = $this->input->get('limit');
		//$page = $this->input->get('page');
		$sort = $this->input->get('sort');
		$dir = $this->input->get('dir');

		$sql = "SELECT HEX(`jb_mac`)'jb_mac', pos, (`voltage`*0.01)'voltage', (`current`*0.01)'current', `temp`, `power`, `update_time` 
				FROM JunctionBox WHERE block_id = '$block_id' 
				ORDER BY ".$sort." ".$dir." LIMIT ".$start.",".$limit;
		$data = $this->db->query($sql)->result_object();
		
		$sql2 = "SELECT COUNT(`state`)'total' FROM JunctionBox WHERE block_id = '$block_id'";
		$totalCount = $this->db->query($sql2)->row()->total;

		echo '({"totalCount":"'.$totalCount.'","results":'.json_encode($data).'})';
	}

	//Get Junction Box Power History Data
	public function get_jb_power()
	{
		$this->User->check_login();
		$result = array('name' => '', 'categories' => array(), 'data' => array());
		
		$level = (int)$this->input->post('level');
		$year = (int)$this->input->post('year');
		$month = (int)$this->input->post('month');
		$day = (int)$this->input->post('day');
		$jb_mac = $this->session->userdata('jb_mac');
		$power_sum = 0;
		
		if($level < 0) {
			$query = $this->Monitor_model->jb_power_select($jb_mac, $year, null, null);
			$power_sum = $this->Monitor_model->jb_power_sum($jb_mac, $year, null, null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("Y", mktime(0, 0, 0, 1, 1, $year));
		}
		else if($level == 0) {
			$query = $this->Monitor_model->jb_power_select($jb_mac, $year, $month, null);
			$power_sum = $this->Monitor_model->jb_power_sum($jb_mac, $year, $month,  null);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['categories'][] = $row['categories'];
				$result['data'][] = array(
					'y' => (int)$row['power'], 
					'drilldown' => array('name' => $row['categories'])
				);
			}
			$result['name'] = date("M, Y", mktime(0, 0, 0, $month, 1, $year));
		}
		else if($level == 1) {
			$query = $this->Monitor_model->jb_power_select($jb_mac, $year, $month, $day);
			$power_sum = $this->Monitor_model->jb_power_sum($jb_mac, $year, $month, $day);
			
			if($query->num_rows() > 0)
			foreach($query->result_array() as $row) {
				$result['powerdata'][] = array( (int)$row['x']*1000, (int)$row['y'] );
				$result['tempdata'][] = array( (int)$row['x']*1000, (int)$row['temp'] );
			}
			$result['name'] = date("Y/m/d", mktime(0, 0, 0, $month, $day, $year))." ~ ".
								date("Y/m/d", mktime(0, 0, 0, $month, $day+6, $year));
		}
		
		$result['name'] .= " production: ".(int)$power_sum." kWh";

		echo json_encode($result);
	}
	
	public function jb_detail($op=NULL, $jb_mac)
	{
		$this->User->check_login();
		$this->User->set_jb($jb_mac);
		$block_id = $this->session->userdata('block_id');
		$data['jb'] = $this->Admin_model->jb_select(NULL, $jb_mac)->row_array();
		$data['block'] = $this->Admin_model->block_select(NULL, $block_id)->row_array();
		$data['am'] = $this->Admin_model->am_select_one($data['block']['am_id'])->row_array();
		$data['area'] = $this->Admin_model->area_select_one($data['am']['area_id'])->row_array();
		
		$data['power'] = $this->Monitor_model->jb_power_select($jb_mac)->result_array();
		$power_sum = $this->Monitor_model->jb_power_sum($jb_mac);
		$data['power_name'] = date('Y')." production: ".(int)$power_sum." kWh";
		$data['power_week'] = $this->Monitor_model->jb_power_select($jb_mac, date('Y'), date('n'), date('j'))->result_array();
		$power_week_sum = $this->Monitor_model->jb_power_sum($jb_mac, date('Y'), date('n'), date('j'));
		$data['power_week_name'] = 
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j'), date('Y')))." ~ ".
			date("Y/n/j", mktime(0, 0, 0, date('n'), date('j')+6, date('Y')))." production: ".(int)$power_week_sum." kWh";
		
		if($this->User->check_super() == false)	$op=NULL;
		
		switch($op)
		{
			// Move JB to the block
			case 'm':/*
				if($this->input->post('mysubmit'))
				{
				$jb_mac = $this->input->post('jb_mac');
				$move_block_id = $this->input->post('block_id');
				
				$sql = "UPDATE JunctionBox SET `block_id` = '$move_block_id' WHERE `jb_mac` IN (";
				foreach($jb_mac as $row)
					$sql.= "UNHEX('".$row."'),";
				$sql .= "-1)";
				
				$this->db->query($sql);
				}
				$this->Alert->web_goto('home/jb_detail/0/'.$block_id);*/
				break;
			
			default:
				$this->layout->view('jb_detail_page',$data);
				break;
		}
	}
	
/******************************************************************************
 * Test code
 ******************************************************************************/
	public function test_add_paneldata()
	{
		$this->User->check_login();
		//$mac = "13649644";
		for($i=1; $i<1000001; $i++) {
			//$sql = "INSERT INTO `PVWEB`.`PanelData` (`junctionbox_mac`,`power`,`voltage`,`current`,`temp`)
			//		VALUES ('1".($i+2000001)."','11','11','11','11')";
			$sql = "INSERT INTO `PVWEB`.`PanelData2` (`junctionbox_id`,`power`)
					VALUES ('$i','11')";
			$this->db->query($sql);
			//$mac++;
			msleep(10);
			//$this->Alert->alert('新增第 '.$i.' 筆'); 
		}
		$this->Alert->alert('共新增 '.$i.' 筆'); 
		
		//if($query)	$this->Alert->alert('執行成功');
	}
	
	
	public function test()
	{
	
	
	$sql = "
	SELECT A.*, B.am_power FROM
	( SELECT * FROM Area WHERE client_id = '$client_id' 
		ORDER BY ".$sort." ".$dir." LIMIT ".$start.",".$limit." ) A
	LEFT JOIN 
	(
		SELECT SUM(block_power)'am_power', am_id FROM 
		(
		SELECT `block_id`, (SUM(`power`)*0.01)'block_power' FROM JunctionBox WHERE `block_id` IN (
			SELECT `block_id` FROM Block WHERE `am_id` IN (
				SELECT `am_id` FROM ArrayManager WHERE `area_id` IN (
					SELECT `area_id` FROM Area WHERE `client_id` = '$client_id'
					)
				)
			) GROUP BY `block_id`
		) C LEFT JOIN Block ON C.block_id = Block.block_id 
			GROUP BY am_id
	) B
	ON A.area_id = B.area_id ";
	
	
	$sql = "
	SELECT * FROM
		(SELECT * FROM Block WHERE `am_id` = ".$am_id.") A
	LEFT JOIN 
	(
		SELECT `block_id`, (SUM(`power`)*0.001)'power', AVG(`voltage`)'voltage', AVG(`current`)'current', AVG(`temp`)'temp', MAX(`update_time`)'update_time'
		FROM JunctionBox WHERE `block_id` IN (
			SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
		) GROUP BY `block_id`
	) B
	ON A.block_id = B.block_id";
	
	$sql = "
	INSERT INTO `BlockData` (`block_id` ,`power` ,`time`)
	VALUES 
	('11',  '10000',  '2012-01-02 01:00:00'),
	('11',  '20000',  '2012-02-02 02:00:00'),
	('11',  '30000',  '2012-03-02 03:00:00'),
	('11',  '40000',  '2012-04-02 04:00:00'),
	('11',  '50000',  '2012-05-02 05:00:00'),
	('11',  '60000',  '2012-06-02 06:00:00'),
	('11',  '70000',  '2012-07-02 07:00:00'),
	('11',  '60000',  '2012-08-02 07:00:00'),
	('11',  '50000',  '2012-09-02 07:00:00'),
	('11',  '40000',  '2012-10-02 07:00:00'),
	('11',  '30000',  '2012-11-02 07:00:00'),
	('11',  '20000',  '2012-12-02 07:00:00')
	";
	
	$sql = "
	INSERT INTO `BlockData` (`block_id` ,`power` ,`time`) VALUES 
	('11',  '2000',  '2012-12-21 07:00:00'),
	('11',  '3000',  '2012-12-22 07:00:00'),
	('11',  '2000',  '2012-12-23 07:00:00'),
	('11',  '5000',  '2012-12-24 07:00:00'),
	('11',  '2000',  '2012-12-25 07:00:00'),
	('11',  '6000',  '2012-12-26 07:00:00'),
	('11',  '3000',  '2012-12-27 07:00:00'),
	('11',  '5000',  '2012-12-28 07:00:00'),
	('11',  '2000',  '2012-12-29 07:00:00'),
	('11',  '3000',  '2012-12-30 07:00:00')
	";
	$sql = "DELETE FROM `BlockData` WHERE `power` < 10000";
	
	}
}
