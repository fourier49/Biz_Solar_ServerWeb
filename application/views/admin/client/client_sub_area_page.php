<div id="title">Client ／ Sub-Account ／ Area</div>

<table id="subTitle"><tr>
<th>
Super Client: <a href="<?=site_url("admin/client_sub/0/".$super['client_id']);
						?>"><?=$super['name']?>（<?=$super['account']?>）</a>
- Sub: <?=$client['name']?>（<?=$client['account']?>）</th>
</tr></table>

<table id="list">
	<tr>
		<th>&nbsp;</th>
		<th>Name</th>
		<th>Country</th>
		<th>Address</th>
	</tr>
	<?	$i=1;
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$i++;?>.</td>
		<td><?=$row['name']?></td>
		<td><?=$row['country']?></td>
		<td><?=$row['address']?></td>
	</tr>
	<?	} ?>
</table>
