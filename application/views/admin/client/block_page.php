<script language="JavaScript"  type="text/javascript">
function myconfirm(block_id, jb_num) {
	if(<?=$query->num_rows()?> < 2)
		alert("There must be one block at least!");
	else if(jb_num > 0)
		alert("Cannot delete the block with J-Boxs!\nPlease move them to other blocks first.");
	else {
		answer = confirm("Are you sure you want to delete the block?");
		if (answer) {
			var form = document.getElementById('delete_form');
			form.elements['block_id'].value = block_id;
			form.submit();
		}
	}
}
</script>

<div id="title">Client ／ Area ／ Device ／ Block</div>

<table id="subTitle"><tr>
<th>
Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
- Area: <a href="<?=site_url("admin/area_device/0/".$area['area_id']);?>"><?=$area['name']?></a>
- Array Manager: AB<?=$am['mac']?>
</th>
<td><a href="<?=site_url("admin/area_block/n/".$am['am_id']);?>">Add</a></td>
</tr></table>

<table id="list">
	<tr>
		<th>&nbsp;</th>
		<th>Block Name</th>
		<th>J-box Num</th>
		<th>Row</th>
		<th>Col</th>
		<th width=50>Edit</th>
		<th width=40>Delete</th>
	</tr>
	<?	$i=1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$i;?>.</td>
		<td><?=$row['block_name']?></td>
		<td><a href="<?=site_url("admin/area_device_jb/0/".$row['block_id']);?>"><?=$row['jb_num']?></a></td>
		<td><?=$row['row']?></td>
		<td><?=$row['col']?></td>
		<td><a href="<?=site_url('admin/area_block/e/'.$am['am_id'].'/'.$row['block_id']);?>">
			<img src="<?=base_url('images/button/edit.png')?>"/></a>
		</td>
		<td><a href="javascript:myconfirm(<?=$row['block_id'];?>,<?=$row['jb_num']?>);">
			<img src="<?=base_url('images/button/delete.png');?>"/></a>
		</td>
	</tr>
	<?	$i++;} ?>
</table>

<form id="delete_form" method="post" action="<?=site_url("admin/area_block/d/".$am['am_id']);?>">
<input type="hidden" name="block_id" value="">
</form>