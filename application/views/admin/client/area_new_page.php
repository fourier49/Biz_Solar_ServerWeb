<div id="title">Client ／ Area ／ Add</div>

<table id="subTitle"><tr>
<th width=48>Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
</th>
</tr></table>

<form enctype="multipart/form-data" method="post" action="<?=site_url("admin/area/n/".$client['client_id']);?>">

<div align="center">
<table id="vtable">
	<tr>
		<th>*Name</th>
		<td><input type="text" name="name" value="<?=set_value('name');?>" size=25></td>
	</tr>
	<tr>
		<th>Country</th>
		<td><input type="text" name="country" value="<?=set_value('country');?>" size=25></td>
	</tr>
	<tr>
		<th>Address</th>
		<td><input type="text" name="address" value="<?=set_value('address');?>" size=25></td>
	</tr>
</table>
	<input name="submit" value="Submit" type="submit">&nbsp;&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/area/0/".$client['client_id']);?>'">
	<?=validation_errors();?>
</div>

</form>