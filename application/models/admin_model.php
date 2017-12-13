<?php
class Admin_model extends CI_Model {

    function __construct()
    {
        // 呼叫模型(Model)的建構函數
        parent::__construct();
    }

    function login($account, $pwd)
    {
		if($account == $this->config->item('admin_account')
			&& $pwd == $this->config->item('admin_pwd')	)
        {
            $this->session->set_userdata('admin_login', 1);
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
		if($this->session->userdata('admin_login') == NULL)
			$this->Alert->web_goto('admin/login');
	}
	
	function login_monitor($client_id)
	{
		$query = $this->db->get_where('Client', array('client_id' => $client_id) );
		
		if($query->num_rows() == 1)
        {
			$this->session->set_userdata('user_id', $query->row()->client_id);
			$this->session->set_userdata('user_name', $query->row()->name);
			if($query->row()->super_id == 1)
			{
				$this->session->set_userdata('super_id', $query->row()->client_id);
				$this->session->set_userdata('super_name', $query->row()->name);
			}
			else
				$this->session->set_userdata('super_id', 1);
		}
	}

	
// -------------------------------------------------------------------
// Client
// -------------------------------------------------------------------
	function client_select_one($client_id)
	{
		$sql = "SELECT * FROM Client WHERE client_id = ".$client_id." LIMIT 1";
		return $this->db->query($sql)->row_array();
	}
	
	function client_select($page=1, $keyword=null)
	{
		$sql = "SELECT A.*, B.account_num, C.area_num FROM
					(SELECT client_id, account, name, insert_time FROM Client WHERE super_id = 1 ";
						
		if($keyword != null)
			$sql .= "&& (account LIKE '%".$keyword."%' || name LIKE '%".$keyword."%') ";
						
		$sql .= "ORDER BY account LIMIT ".(--$page*20).", 20) A 
				LEFT JOIN
					(SELECT super_id, COUNT(client_id)'account_num' FROM Client GROUP BY super_id) B
				ON A.client_id = B.super_id
				LEFT JOIN
					(SELECT client_id, COUNT(client_id)'area_num' FROM Area GROUP BY client_id) C 
				ON A.client_id = C.client_id";
			
		return $this->db->query($sql);
	}
	
	function client_insert($client)
	{
		$sql = "SELECT client_id FROM Client WHERE account = '".$client['account']."'";
		if($this->db->query($sql)->num_rows() > 0)	return false;
		
		$sql = $this->db->insert_string('Client', $client);
		$this->db->query($sql);
		$sql = "SELECT client_id FROM Client WHERE account = '".$client['account']."'";
		$area['client_id'] = $this->db->query($sql)->row()->client_id;
		$this->area_insert($area);
		
		$this->log_insert("New Client : ".$client['account'],"");
		return true;
	}
	
	function client_edit($client)
	{
		$sql = "SELECT client_id FROM Client 
				WHERE client_id != ".$client['client_id']." && account = '".$client['account']."'";
		if($this->db->query($sql)->num_rows() > 0)	return false;
		
		$where = "client_id = ".$client['client_id'];
		$sql = $this->db->update_string('Client', $client, $where);
		$this->db->query($sql);
		$this->log_insert("Edit Client : ".$client['account'],"");
		return true;
	}

	function client_delete($client_id)
	{
		$client = $this->client_select_one($client_id);
		$sql = "DELETE LOW_PRIORITY FROM Client WHERE client_id = ".$client_id;
		if($this->db->query($sql) == NULL)	return false;
		
		$log = "Account: ".$client['account']."<br>Name: ".$client['name'].
				"<br>Insert time: ".$client['insert_time'];
		$this->log_insert("Delete Client : ".$client['account'], $log);
		
		return true;
	}

	function client_sub_select($super_id)
	{
		$sql = "SELECT A.*, B.area_num FROM
				(SELECT client_id, account, name, insert_time FROM Client 
					WHERE super_id = '$super_id' ORDER BY account) A LEFT JOIN
					(SELECT client_id, COUNT(client_id)'area_num' FROM ClientArea GROUP BY client_id) B 
				ON A.client_id = B.client_id";
		
		return $this->db->query($sql);
	}
	
	function client_sub_insert($client)
	{
		$sql = "SELECT client_id FROM Client WHERE account = '".$client['account']."'";
		if($this->db->query($sql)->num_rows() > 0)	return false;
		
		$sql = $this->db->insert_string('Client', $client);
		$this->db->query($sql);

		$this->log_insert("New Sub Account : ".$client['account'],"");
		return true;
	}
	
// -------------------------------------------------------------------
// Area
// -------------------------------------------------------------------
	function area_select_one($area_id)
	{
		$sql = "SELECT * FROM Area WHERE area_id = ".$area_id." LIMIT 1";	
		return $this->db->query($sql);
	}
	
	function area_select($client_id, $page=1)
	{
		$sql = "SELECT * FROM Area WHERE client_id = ".$client_id." ORDER BY name LIMIT ".(--$page*20).", 20";
		$query = $this->db->query($sql);
		$sql2 = "SELECT COUNT(`client_id`)'num' FROM Area WHERE client_id = ".$client_id;
		$query->num_pages = (int)($this->db->query($sql2)->row()->num / 20) + 1;
		
		return $query;
	}
	
	function area_insert($area)
	{
		$sql = $this->db->insert_string('Area', $area);
		$this->db->query($sql);
		return true;
	}
	
	function area_edit($area)
	{
		$where = "area_id = ".$area['area_id'];
		$sql = $this->db->update_string('Area', $area, $where);
		$this->db->query($sql);
		return true;
	}
	
	function area_delete($area_id)
	{
		$area = $this->area_select_one($area_id)->row_array();
		$sql = "DELETE LOW_PRIORITY FROM Area WHERE area_id = ".$area_id;
		if($this->db->query($sql) == NULL)	return false;
		
		$client = $this->client_select_one($area['client_id']);
		$log = "Name ".$area['name']."<br>Country ".$area['country'].
				"<br>Address ".$area['address']."<br>Insert time ".$area['insert_time'];
		$this->log_insert("Delete Area : ".$area['name']." ( Client ".$client['account']." )", $log);
		
		return true;
	}

// -------------------------------------------------------------------
// Device (Array Manager, Block, Junction Box)
// -------------------------------------------------------------------
	function am_select_one($am_id)
	{
		$sql = "SELECT * FROM ArrayManager WHERE am_id = ".$am_id." LIMIT 1";
		return $this->db->query($sql);
	}
	
	function am_select($area_id, $page=1, $keyword=null)
	{
		$where = "WHERE area_id = ".$area_id;
		if($keyword != null)
			$where.= " && mac LIKE '".$data['mac']."%'";
		//$sql = "SELECT * FROM ArrayManager ".$where." ORDER BY mac LIMIT ".(--$page*20).", 20";
		$sql = "SELECT * FROM ArrayManager ".$where." ORDER BY mac";
		return $this->db->query($sql);
	}
	
	function am_adv_select($op=NULL, $page=1, $keyword=null)
	{
		if($op=='reg') //Unset Array Manager
			$where = "WHERE register_time IS NOT NULL && area_id = 1";
		else if($op=='dis')	//Disable Array Manager
			$where = "WHERE area_id = 2";
		else if($op=='s') //Search from all Array Manager
			$where = "WHERE mac LIKE '".$keyword."%'";
		else //Unregistered Array Manager
			$where = "WHERE register_time IS NULL";	

		$sql = "SELECT * FROM ArrayManager ".$where." ORDER BY mac LIMIT ".(--$page*20).", 20";
		$query = $this->db->query($sql);
		$sql2 = "SELECT COUNT(`am_id`)'num' FROM ArrayManager ".$where;
		$query->num_pages = (int)($this->db->query($sql2)->row()->num / 20) + 1;
		return $query;
	}
	
	function am_reg_select($reg=NULL)
	{
		if($reg == NULL) //Unset Array Manager
			$where = "WHERE register_time IS NOT NULL && area_id = 1 ";
		else //Unregistered Array Manager
			$where = "WHERE register_time IS NULL";	
		$sql = "SELECT * FROM ArrayManager ".$where." ORDER BY mac";
		return $this->db->query($sql);
	}
	
	function am_insert($first_mac, $last_mac)
	{
		$first_mac_int = hexdec($first_mac);
		$last_mac_int = hexdec($last_mac);
		$sql = "SELECT mac FROM ArrayManager 
			WHERE CONV(`mac`,16,10) >= ".$first_mac_int." && CONV(`mac`,16,10) <= ".$last_mac_int;
		$query = $this->db->query($sql);
		$duplicate_mac = "";
		
		if($query->num_rows() > 0)
		{
			foreach($query ->result_array() as $row)
				$duplicate_mac .= ("A8".$row['mac'].", ");
			return $duplicate_mac;
		}
		else
		{
			$sql = "INSERT INTO ArrayManager (mac) VALUES ";
					
			for($mac = $first_mac_int; $mac < $last_mac_int; $mac++)
				$sql .= "(UPPER('".str_pad(base_convert($mac, 10, 16),10,'0',STR_PAD_LEFT)."')),";
						
			$sql .= "(UPPER('".str_pad(base_convert($mac, 10, 16),10,'0',STR_PAD_LEFT)."'))";
					
			$this->db->query($sql);
		}
		$log_content = $sql;
		$this->log_insert("New Array Manager : A8".$first_mac." ~ A8".$last_mac, $log_content);
		
		return NULL;
	}
	
	function am_edit($device)
	{
		$where = "am_id = ".$device['am_id'];
		$sql = $this->db->update_string('ArrayManager', $device, $where);
		$this->db->query($sql);
		return true;
	}
	
	function block_select($am_id=NULL, $block_id=NULL)
	{
		if($block_id != NULL)
			$sql = "SELECT * FROM Block WHERE block_id = ".$block_id;
		
		else 				
			$sql = "SELECT A.*, B.jb_num FROM
					(SELECT * FROM Block WHERE am_id = ".$am_id." ORDER BY row, col) A 
					LEFT JOIN (SELECT block_id, COUNT(`pos`)'jb_num' FROM JunctionBox GROUP BY block_id) B
					ON A.block_id = B.block_id";
		
		return $this->db->query($sql);
	}
	
	function block_insert($block)
	{
		$sql = $this->db->insert_string('Block', $block);
		$this->db->query($sql);
		return true;
	}
	
	function block_edit($block)
	{
		$where = "block_id = ".$block['block_id'];
		$sql = $this->db->update_string('Block', $block, $where);
		$this->db->query($sql);
		return true;
	}
	
	function jb_select($block_id=NULL, $jb_mac=NULL)
	{
		$sql = "SELECT HEX(`jb_mac`)'jb_mac', `s/n`, `firmware_vs`, hardware_vs, `device_spec`, `manufacture_date`, 
				`pos`, `row`, `col`, `block_id`, `state`, `update_time`, 
				(`voltage`*0.01)'voltage', (`current`*0.01)'current', `temp`, `(power*0.01)`
				FROM JunctionBox ";
				
		if($jb_mac != NULL)
			$sql .= "WHERE `jb_mac` = UNHEX('$jb_mac') LIMIT 1";
		else 
			$sql .= "WHERE block_id = ".$block_id." ORDER BY jb_mac";
		
		return $this->db->query($sql);
	}
	
	function jb_edit($jb_mac, $jb)
	{
		$where = "jb_mac = UNHEX('".$jb_mac."')";
		$sql = $this->db->update_string('JunctionBox', $jb, $where);
		$this->db->query($sql);
		return true;
	}
	
// -------------------------------------------------------------------
// Log
// -------------------------------------------------------------------
	function log_insert($title, $content=null)
	{
		$log['title'] = $title;
		$log['content'] = $content;
		$sql = $this->db->insert_string('Log', $log);
		$this->db->query($sql);
	}
	
}