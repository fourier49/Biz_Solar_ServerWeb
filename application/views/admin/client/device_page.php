<div id="title">Client ／ Area ／ Device</div>

<table id="subTitle"><tr>
<th>
Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
- Area: <?=$area['name']?>
</th>
<td>
<a href="<?=site_url("admin/area_device/a/".$area['area_id']);?>">Add</a>
<a href="<?=site_url("admin/area_device/r/".$area['area_id']);?>">Remove</a>
</td>
</tr></table>

<table id="list">
	<tr>
		<th>&nbsp;</th>
		<th>Mac</th>
		<th>IP</th>
		<th>Port</th>
		<th>Update Period</th>
		<th>Registered</th>
		<th width=40>Edit</th>
	</tr>
	<?	$i=1;
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$i++;?>.</td>
		<td><a href="<?=site_url("admin/area_block/0/".$row['am_id']);?>">AB<?=$row['mac']?></a></td>
		<td><?=$row['ip']?></td>
		<td><?=$row['port']?></td>
		<td><?=$row['period']?> sec.</td>
		<td><?=$row['register_time']?></td>
		<td><a href="<?=site_url('admin/area_device/e/'.$area['area_id'].'/1/'.$row['am_id']);?>">
			<img src="<?=base_url('images/button/edit.png')?>"/></a>
		</td>
	</tr>
	<?	} ?>
</table>