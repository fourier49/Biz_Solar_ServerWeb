<?php
class Monitor_model extends CI_Model {

    function __construct()
    {
        // 呼叫模型(Model)的建構函數
        parent::__construct();
    }

/*********************************************************
 * Block
 *********************************************************/
	
	function block_select($am_id)
	{
		$sql = "
		SELECT * FROM
		(SELECT * FROM Block WHERE `am_id` = ".$am_id.") A
		LEFT JOIN 
		(
			SELECT `block_id`'id', (SUM(`power`)*0.001)'power', MAX(`state2`)'state2',
					(AVG(`voltage`)*0.01)'voltage', (AVG(`current`)*0.01)'current', AVG(`temp`)'temp', MAX(`update_time`)'update_time'
			FROM JunctionBox WHERE `block_id` IN (
				SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
			) GROUP BY `block_id`
		) B ON A.block_id = B.id 
		";
		
		return $this->db->query($sql);
	}
	
	function block_select_jb_num($am_id)
	{
		$sql = "
		SELECT * FROM
		(SELECT * FROM Block WHERE `am_id` = ".$am_id.") A
		LEFT JOIN 
		(
			SELECT `block_id`'id', COUNT(`block_id`)'jb_num'
			FROM JunctionBox WHERE `block_id` IN (
				SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
			) GROUP BY `block_id`
		) B ON A.block_id = B.id 
		";
		
		return $this->db->query($sql);
	}
	
	function block_insert($am_id, $begin_x, $begin_y, $end_x, $end_y)
	{
		$block = array();
		for($i=$begin_y; $i<=$end_y; $i++)
		for($j=$begin_x; $j<=$end_x; $j++)
		{
			$sql = "SELECT `block_id` FROM `Block` WHERE `am_id` = ".$am_id.
					" && `row` = ".$i." && `col` = ".$j." LIMIT 1";
			if($this->db->query($sql)->num_rows() < 1)
				$block[] = array('am_id'=>$am_id, 'row'=>$i, 'col'=>$j);
		}
		if(count($block) > 0) {
			$this->db->insert_batch('Block', $block);
			return true;
		}
		else {
			return false;
		}
	}
	
	function block_update_pos($am_id, $begin_x, $begin_y, $end_x, $end_y)
	{
		if($begin_x < 2 && $begin_y < 2)	return false;
		$sql = "UPDATE `Block` SET `row` = ".$end_y.", `col` = ".$end_x." 
				WHERE `am_id` = ".$am_id." && `row` = ".$begin_y." && `col` = ".$begin_x." LIMIT 1";
		$this->db->query($sql);
		if($this->db->affected_rows() < 1)	return false;
		else	return true;
	}
	
	function block_delete($am_id, $begin_x, $begin_y, $end_x, $end_y)
	{
		$sql = "SELECT block_id FROM Block WHERE `am_id` = ".$am_id." && `row` = 1 && `col` = 1 LIMIT 1";
		$default_block_id = $this->db->query($sql)->row()->block_id;
		
		$sql = "UPDATE `JunctionBox` SET `block_id` = ".$default_block_id."
				WHERE `block_id` IN ( 
				SELECT `block_id` FROM `Block` WHERE `am_id` = ".$am_id."
				&& `row` >= ".$begin_y." && `row` <= ".$end_y."
				&& `col` >= ".$begin_x." && `col` <= ".$end_x." 
				&& `block_id` != ".$default_block_id." )";
		
		$this->db->query($sql);

		$sql = "DELETE FROM `Block` WHERE `am_id` = ".$am_id."
				&& `row` >= ".$begin_y." && `row` <= ".$end_y."
				&& `col` >= ".$begin_x." && `col` <= ".$end_x." 
				&& `block_id` != ".$default_block_id;
				
		$this->db->query($sql);
		if($this->db->affected_rows() < 1)	return false;
		else	return true;
	}

/*********************************************************
 * J-Box
 *********************************************************/
	function jb_select_row_col($block_id)
	{
		$sql = "SELECT HEX(`jb_mac`)'jb_mac', row, col FROM JunctionBox WHERE `block_id` = ".$block_id;
		
		return $this->db->query($sql);
	}
	
	function alert_jb_select($client_id)
	{
		if($this->session->userdata('super_id') > 1
			&& $this->session->userdata('user_id') == $this->session->userdata('super_id'))
			$table = "Area";
		else
			$table = "ClientArea";
					
		$sql = "
		SELECT A.*, Block.row, Block.col, ArrayManager.mac, Area.name FROM
		( 	SELECT HEX(`jb_mac`)'jb_mac', block_id, state FROM JunctionBox 
			WHERE `block_id` IN (
				SELECT `block_id` FROM Block WHERE `am_id` IN (
					SELECT `am_id` FROM ArrayManager WHERE `area_id` IN (
						SELECT `area_id` FROM ".$table." WHERE `client_id` = ".$client_id."
					)
				) 
			) && state IS NOT NULL LIMIT 0,10 
		) A 
		LEFT JOIN Block ON A.block_id = Block.block_id
		LEFT JOIN ArrayManager ON Block.am_id = ArrayManager.am_id
		LEFT JOIN Area ON ArrayManager.area_id = Area.area_id";
		
		return $this->db->query($sql);
	}
	
	function jb_pos_update($block_id, $old_pos, $new_pos)
	{			
		$sql = "UPDATE `JunctionBox` SET `pos` = 0 WHERE `block_id` = ".$block_id." && `pos` = ".$old_pos." LIMIT 1";
		$this->db->query($sql);
		if( intval($new_pos) < intval($old_pos) )
			$sql = "UPDATE `JunctionBox` SET `pos` = `pos`+1 
					WHERE `block_id` = ".$block_id." && `pos` >= ".$new_pos." && `pos` < ".$old_pos;
		else
			$sql = "UPDATE `JunctionBox` SET `pos` = `pos`-1 
					WHERE `block_id` = ".$block_id." && `pos` <= ".$new_pos." && `pos` > ".$old_pos;
		$this->db->query($sql);
		$sql = "UPDATE `JunctionBox` SET `pos` = ".$new_pos." WHERE `block_id` = ".$block_id." && `pos` < 1 LIMIT 1";
		$this->db->query($sql);
	}
	
	function jb_update_blockid($block_id, $block_id_new, $begin_col, $begin_row, $end_col, $end_row)
	{
		$block = array();
		
		$sql = "UPDATE `JunctionBox` SET `block_id` = ".$block_id_new.", `row` = -1, `col` = -1
			WHERE `block_id` = ".$block_id.
			" && `row` >= ".$begin_row." && `row` <= ".$end_row.
			" && `col` >= ".$begin_col." && `col` <= ".$end_col;
				
		$this->db->query($sql);
		if($this->db->affected_rows() < 1)	return false;
		else	return true;
	}
	
	function jb_update_row_col($jb_mac, $end_col, $end_row)
	{
		$sql = "UPDATE `JunctionBox` SET `row` = ".$end_row.", `col` = ".$end_col." 
				WHERE `jb_mac` = UNHEX('".$jb_mac."') LIMIT 1";
		$this->db->query($sql);
		if($this->db->affected_rows() < 1)	return false;
		else	return true;
	}
	
/*********************************************************
 * Power
 *********************************************************/	
	function area_power_select($area_id, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT UNIX_TIMESTAMP(`time`)'x', (SUM(`power`)*0.001)'y'
				FROM BlockData WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` IN (
						SELECT `am_id` FROM ArrayManager WHERE `area_id` = ".$area_id."
					)
				) && `time` > '$timeFrom' && `time` < '$timeTo' GROUP BY `time` ORDER BY `time`";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, ++$month, 1, $year));
			$sql = "SELECT date_format(`time`,'%e')'categories', (SUM(`power`)/60000)'power'
				FROM BlockData WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` IN (
						SELECT `am_id` FROM ArrayManager WHERE `area_id` = ".$area_id."
					)
				) && `time` > '$timeFrom' && `time` < '$timeTo' 
				GROUP BY Day(`time`) ORDER BY `time`";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT date_format(`time`,'%b')'categories', (SUM(`power`)/60000)'power'
				FROM BlockData WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` IN (
						SELECT `am_id` FROM ArrayManager WHERE `area_id` = ".$area_id."
					)
				) && `time` > '$timeFrom' && `time` < '$timeTo' 
				GROUP BY MONTH(`time`) ORDER BY `time`";
		}
		
		return $this->db->query($sql);
	}
	
	function area_power_sum($area_id, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` IN (
						SELECT `block_id` FROM Block WHERE `am_id` IN (
							SELECT `am_id` FROM ArrayManager WHERE `area_id` = ".$area_id."
						)
					) && `time` > '$timeFrom' && `time` < '$timeTo'";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, ++$month, 1, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` IN (
						SELECT `am_id` FROM ArrayManager WHERE `area_id` = ".$area_id."
					)
				) && `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` IN (
						SELECT `block_id` FROM Block WHERE `am_id` IN (
							SELECT `am_id` FROM ArrayManager WHERE `area_id` = ".$area_id."
						)
					) && `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		
		if($this->db->query($sql)->num_rows() > 0)
			return $this->db->query($sql)->row()->power;
		else
			return 0;
	}
	
	function am_power_select($am_id, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT UNIX_TIMESTAMP(`time`)'x', (SUM(`power`)*0.001)'y'
				FROM BlockData WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
				) && `time` > '$timeFrom' && `time` < '$timeTo' GROUP BY `time` ORDER BY `time`";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, 0, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, 0, ++$month, 1, $year));
			$sql = "SELECT date_format(`time`,'%e')'categories', (SUM(`power`)/60000)'power'
				FROM BlockData WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
				) && `time` > '$timeFrom' && `time` < '$timeTo' 
				GROUP BY Day(`time`) ORDER BY `time`";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT date_format(`time`,'%b')'categories', (SUM(`power`)/60000)'power'
				FROM BlockData WHERE `block_id` IN (
					SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
				) && `time` > '$timeFrom' && `time` < '$timeTo' 
				GROUP BY MONTH(`time`) ORDER BY `time`";
		}
		
		return $this->db->query($sql);
	}
	
	function am_power_sum($am_id, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` IN (
						SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
					) && `time` > '$timeFrom' && `time` < '$timeTo'";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, ++$month, 1, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` IN (
						SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
					) && `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` IN (
						SELECT `block_id` FROM Block WHERE `am_id` = ".$am_id."
					) && `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		
		if($this->db->query($sql)->num_rows() > 0)
			return $this->db->query($sql)->row()->power;
		else
			return 0;
	}
	
	function block_power_select($block_id, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT UNIX_TIMESTAMP(`time`)'x', (`power`*0.001)'y'
				FROM BlockData WHERE `block_id` = ".$block_id."
				&& `time` > '$timeFrom' && `time` < '$timeTo' ORDER BY `time`";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, ++$month, 1, $year));
			$sql = "SELECT date_format(`time`,'%e')'categories', (SUM(`power`)/60000)'power'
				FROM BlockData WHERE `block_id`  = ".$block_id."
				&& `time` > '$timeFrom' && `time` < '$timeTo' 
				GROUP BY Day(`time`) ORDER BY `time`";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT date_format(`time`,'%b')'categories', (SUM(`power`)/60000)'power'
			FROM BlockData WHERE `block_id` = ".$block_id."
			&& `time` > '$timeFrom' && `time` < '$timeTo' 
			GROUP BY MONTH(`time`) ORDER BY `time`";
		}
		
		return $this->db->query($sql);
	}
	
	function block_power_sum($block_id, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` = ".$block_id."
					&& `time` > '$timeFrom' && `time` < '$timeTo'";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, ++$month, 1, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` = ".$block_id."
					&& `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM BlockData WHERE `block_id` = ".$block_id."
					&& `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		
		if($this->db->query($sql)->num_rows() > 0)
			return $this->db->query($sql)->row()->power;
		else
			return 0;
	}
	
	function jb_power_select($jb_mac, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT UNIX_TIMESTAMP(`time`)'x', (`power`)'y', `temp`
				FROM PanelData WHERE `jb_mac` = UNHEX('".$jb_mac."')
				&& `time` > '$timeFrom' && `time` < '$timeTo' ORDER BY `time`";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, ++$month, 1, $year));
			$sql = "SELECT date_format(`time`,'%e')'categories', (SUM(`power`)/60000)'power'
				FROM PanelData WHERE `jb_mac` = UNHEX('".$jb_mac."')
				&& `time` > '$timeFrom' && `time` < '$timeTo' 
				GROUP BY Day(`time`) ORDER BY `time`";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT date_format(`time`,'%b')'categories', (SUM(`power`)/60000)'power'
			FROM PanelData WHERE `jb_mac` = UNHEX('".$jb_mac."')
			&& `time` > '$timeFrom' && `time` < '$timeTo' 
			GROUP BY MONTH(`time`) ORDER BY `time`";
		}
		
		return $this->db->query($sql);
	}
	
	function jb_power_sum($jb_mac, $year=null, $month=null, $day=null)
	{
		if($year == null) $year = date("Y");
		
		if($day != null) 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, $day+7, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM PanelData WHERE `jb_mac` = UNHEX('".$jb_mac."')
					&& `time` > '$timeFrom' && `time` < '$timeTo'";
		}	
		else if($month != null)
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, $month, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, ++$month, 1, $year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM PanelData WHERE `jb_mac` = UNHEX('".$jb_mac."')
					&& `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		else 
		{
			$timeFrom = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, $year));
			$timeTo = date("Y-m-d H:i:s", mktime(0, 0, -1, 1, 1, ++$year));
			$sql = "SELECT (SUM(`power`)/60000)'power' FROM PanelData WHERE `jb_mac` = UNHEX('".$jb_mac."')
					&& `time` > '$timeFrom' && `time` < '$timeTo'";
		}
		
		if($this->db->query($sql)->num_rows() > 0)
			return $this->db->query($sql)->row()->power;
		else
			return 0;
	}
}