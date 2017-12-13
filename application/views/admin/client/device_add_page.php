<script language="JavaScript"  type="text/javascript">
function myconfirm()
{
	var select_num = document.getElementById('select_num').innerHTML;
	if( select_num < 1 )
		alert("No device selected!");
	else {
		answer = confirm("Are you sure you want to add " + select_num + " devices into area?");
		if (answer) {
			document.getElementById('form').elements['mysubmit'].value = 1;
			document.getElementById('form').submit();
		}
	}
}

function CheckAll() 
{
	var ck = document.getElementById('form').elements["am_id[]"];
	var ckAll = document.getElementById('form').elements["allbox"];
	
	if (!ck) { //當沒有checkbox時
		ckAll.checked = false;
	} else if (!ck.length) { //當只有一個checkbox時
		ck.checked = ckAll.checked;
	} else { //當有兩個以上的checkbox時
		for (var i=0; i<ck.length; i++)
			ck[i].checked = ckAll.checked;
	}
	select_count();
}

function select_count()
{
	var ck = document.getElementById('form').elements["am_id[]"];
	var count = 0;
	
	if(!ck.length) {
		if(ck.checked) count++;
	} else {
		for(var i=0; i<ck.length; i++)
			if(ck[i].checked)	count++;
	}
	document.getElementById('select_num').innerHTML = count;
}
</script>

<div id="title">Client ／ Area ／ Device ／ Add</div>

<form id="form" method="post" action="<?=site_url("admin/area_device/a/".$area['area_id']);?>">

<table id="subTitle"><tr>
<th>
Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
- Area: <a href="<?=site_url("admin/area_device/0/".$area['area_id']);?>"><?=$area['name']?></a>
</th>
<td>Selected:</td>
<td id="select_num">0</td>
<td width=150>
	<input value="Submit" type="button" onclick="javascript:myconfirm();">&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href ='<?=site_url("admin/area_device/0/".$area['area_id']);?>'">
</td>
</tr></table>

<table id="list">
	<tr>
		<th><input type="checkbox" name="allbox" onclick="CheckAll()"></th>
		<th>&nbsp;</th>
		<th>Mac</th>
		<th>IP</th>
		<th>Port</th>
		<th>Registered</th>
	</tr>
	<?	$i=1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td>
		<input type="checkbox" name="am_id[]" value="<?=$row['am_id'];?>" onclick="javascript:select_count();">
		</td>
		<td><?=$i++;?>.</td>
		<td><a href="">A8<?=$row['mac']?></a></td>
		<td><?=$row['ip']?></td>
		<td><?=$row['port']?></td>
		<td><?=$row['register_time']?></td>
	</tr>
	<?	} ?>
</table>
	
<input type="hidden" name="mysubmit" value="">

</form>