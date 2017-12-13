<script language="JavaScript"  type="text/javascript">
function myconfirm() {
	answer = confirm("Are you sure you want to edit it？");
	if (answer) {
		document.getElementById('the_form').elements['mysubmit'].value = 1;
		document.getElementById('the_form').submit();
	}
}
</script>

<div id="title">Client ／ Area ／ Device ／ Edit</div>

<table id="subTitle"><tr>
<th>
Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
- Area: <a href="<?=site_url("admin/area_device/0/".$area['area_id']);?>"><?=$area['name']?></a>
</th>
</tr></table>

<form id="the_form" method="post" action="<?=site_url("admin/area_device/e/".$area['area_id']."/1/".$device['am_id']);?>">

<div align="center">
<table id="vtable">
	<tr>
		<th>*Mac</th>
		<td>A8<?=$device['mac'];?></td>
	</tr>
	<tr>
		<th>*IP</th>
		<td><input type="text" name="ip" value="<?=set_value('ip',$device['ip']);?>" size=25></td>
	</tr>
	<tr>
		<th>*Port</th>
		<td><input type="text" name="port" value="<?=set_value('port',$device['port']);?>" size=25>
		<font size=2>(1~65536)</font></td></td>
	</tr>
	<tr>
		<th>*Row</th>
		<td><input type="text" name="row" value="<?=set_value('row',$device['row']);?>" size=25>
		<font size=2>(1~65536)</font></td>
	</tr>
	<tr>
		<th>*Col</th>
		<td><input type="text" name="col" value="<?=set_value('col',$device['col']);?>" size=25>
		<font size=2>(1~65536)</font></td>
	</tr>
	<tr>
		<th>*Update Period (sec.)</th>
		<td><input type="text" name="period" value="<?=set_value('period',$device['period']);?>" size=25>
		<font size=2>(1~65536)</font></td>
	</tr>

</table>
	<input type="hidden" name="mysubmit" value="">
	<input value="Submit" type="button" onclick="javascript:myconfirm();">&nbsp;&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/area_device/0/".$area['area_id']);?>'">
	<?=validation_errors();?>
</div>

</form>