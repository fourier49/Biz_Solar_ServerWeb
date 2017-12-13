<script language="JavaScript"  type="text/javascript">
function myconfirm() {
	answer = confirm("Are you sure you want to edit it？");
	if (answer) {
		document.getElementById('the_form').elements['mysubmit'].value = 1;
		document.getElementById('the_form').submit();
	}
}
</script>

<div id="title">Client ／ Area ／ Device ／ Block ／ J-BOX ／ Edit</div>

<table id="subTitle"><tr>
<th>
Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
- Area: <a href="<?=site_url("admin/area_device/0/".$area['area_id']);?>"><?=$area['name']?></a>
- Array Manager: <a href="<?=site_url("admin/area_block/0/".$am['am_id']);?>">AB<?=$am['mac']?></a>
<a href="<?=site_url("admin/area_device_jb/0/".$block['block_id']);?>">(<?=$block['row']?>,<?=$block['col']?>)</a>
</th>
</tr></table>

<form id="the_form" method="post" action="<?=site_url("admin/area_device_jb/e/".$block['block_id']."/".$jb['mac']);?>">

<div align="center">
<table id="vtable">
	<tr>
		<th>Mac</th>
		<td>DC<?=$jb['mac'];?></td>
	</tr>
	<tr>
		<th>*Pos</th>
		<td><input type="text" name="pos" value="<?=set_value('pos',$jb['pos']);?>" size=30 required></td>
	</tr>
	<tr>
		<th>S/N</th>
		<td><input type="text" name="s/n" value="<?=set_value('s/n',$jb['s/n']);?>" size=30></td>
	</tr>
	<tr>
		<th>Firmware Version</th>
		<td><input type="text" name="firmware_vs" value="<?=set_value('firmware_vs',$jb['firmware_vs']);?>" size=30></td>
	</tr>
	<tr>
		<th>Hardware Version</th>
		<td><input type="text" name="hardware_vs" value="<?=set_value('hardware_vs',$jb['hardware_vs']);?>" size=30></td>
	</tr>
	<tr>
		<th>Device Specification</th>
		<td><input type="text" name="device_spec" value="<?=set_value('device_spec',$jb['device_spec']);?>" size=30></td>
	</tr>
	<tr>
		<th>Manufacture Date</th>
		<td><input type="text" name="manufacture_date" value="<?=set_value('manufacture_date',$jb['manufacture_date']);?>" size=30></td>
	</tr>

</table>
	<input type="hidden" name="mysubmit" value="">
	<input value="Submit" type="button" onclick="javascript:myconfirm();">&nbsp;&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/area_device_jb/0/".$block['block_id']);?>'">
	<?=validation_errors();?>
</div>

</form>