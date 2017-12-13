<script language="JavaScript"  type="text/javascript">
function myconfirm()
{
	var form = document.getElementById('form');
	var begin_id = parseInt(form.elements['first_mac'].value, 16);
	var end_id = parseInt(form.elements['last_mac'].value, 16);
	var new_num = end_id - begin_id + 1;
	if(new_num < 1) alert("Invalid Mac input!");
	answer = confirm("Are you sure you want to add " + new_num + " new devices?");
	if (answer) {
		form.elements['mysubmit'].value = 1;
		form.submit();
	}
}

</script>

<div id="title">Array Manager ／ 
<?	
if($op == 'reg') echo "Unset"; 
else if($op == 'dis') echo "Disable";
else if($op == 's') echo "Search";
else echo "Unregistered";
?></div>

<table id="subTitle"><tr>
<th>
<a href="<?=site_url("admin/am");?>">Unregistered</a>
<a href="<?=site_url("admin/am/reg");?>">Unset</a>
<a href="<?=site_url("admin/am/dis");?>">Disable</a>
</th>
<td>
<form method="post" action="<?=site_url("admin/am/s");?>">
Mac : A8<input type="text" name="mac" value="<?if(!empty($mac)) echo $mac;?>" size=10>
<input type="submit" name="submit" value="Search">
</form>
</td>
<form id="form" method="post" action="<?=site_url("admin/am/n");?>">
<td>Add New Mac : 
A8<input type="text" name="first_mac" value="<?=set_value('first_mac');?>" size=10>
~
A8<input type="text" name="last_mac" value="<?=set_value('last_mac');?>" size=10>
</td>
<td width=70>
	<input value="Submit" type="button" onclick="javascript:myconfirm();">
	<input type="hidden" name="mysubmit" value="">
</td>
</tr></table>

</form>

<table id="list">
	<tr>
		<th width=40>&nbsp;</th>
		<th>Mac</th>
		<th>IP</th>
		<th>Port</th>
		<th>Registered</th>
		<th>Insert Time</th>
	</tr>
	<?	$i=1; $page0 = $page-1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$page0*20+$i++;?>.</td>
		<td><a href="<?=site_url("admin/area_block/0/".$row['am_id']);?>">A8<?=$row['mac']?></a></td>
		<td><?=$row['ip']?></td>
		<td><?=$row['port']?></td>
		<td><?=$row['register_time']?></td>
		<td><?=$row['insert_time']?></td>
	</tr>
	<?	} ?>
</table>
<div class="page">
Page -
<?=$page;?> / <?=$query->num_pages;?>
<font style="margin-left:15px; margin-right:5px;">
<?if($page > 1) echo '<a href="'.site_url("admin/am/".$op."/".($page-1)).'">Previous</a>';
else echo 'Previous';?></font>
<?if($page < $query->num_pages) echo '<a href="'.site_url("admin/am/".$op."/".($page+1)).'">Next</a>';
else echo 'Next';?>
</div>
