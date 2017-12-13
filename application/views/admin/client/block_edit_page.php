<div id="title">Client ／ Area ／ Device ／ Block ／ Edit</div>

<table id="subTitle"><tr>
<th>
Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
- Area: <a href="<?=site_url("admin/area_device/0/".$area['area_id']);?>"><?=$area['name']?></a>
- Array Manager: AB<?=$am['mac']?>
</th>
</tr></table>

<form enctype="multipart/form-data" method="post" action="<?=site_url("admin/area_block/e/".$am['am_id']."/".$block['block_id']);?>">

<div align="center">
<table id="vtable">
	<tr>
		<th>*Block Name</th>
		<td><input type="text" name="block_name" value="<?=set_value('block_name',$block['block_name']);?>" size=25 required></td>
	</tr>
	<tr>
		<th>*Row</th>
		<td><input type="text" name="row" value="<?=set_value('row',$block['row']);?>" size=25 required></td>
	</tr>
	<tr>
		<th>*Column</th>
		<td><input type="text" name="col" value="<?=set_value('col',$block['col']);?>" size=25 required></td>
	</tr>

</table>
	<input name="submit" value="Submit" type="submit">&nbsp;&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/area_block/0/".$am['am_id']);?>'">
</div>

</form>