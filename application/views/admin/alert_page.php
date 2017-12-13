<div id="title">Alert Event</div>

<table id="list">
	<tr>
		<th>&nbsp;</th>
		<th>Client</th>
		<th>Area</th>
		<th>ArrayManager</th>
		<th>J-Box</th>
		<th>Exception</th>
		<th>Time</th>
	</tr>
	<tr>
		<td>1.</td>
		<td>test</td>
		<td>area xxx</td>
		<td>A8xxxxxxxxxx</td>
		<td>DCxxxxxxxxxx</td>
		<td>UnderPower</td>
		<td>2013-01-01 00:00:00</td>
	</tr>
	<?	$i=1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$i++;?>.</td>
		<td><a href="<?=site_url("admin/area/0/".$row['client_id']);?>"><?=$row['client_name']?></a></td>
		<td><?=$row['area_name']?></td>
		<td><?=$row['am_mac']?></td>
		<td><?=$row['jb_mac']?></td>
		<td><?=$row['state']?></td>
		<td><?=$row['insert_time']?></td>
	</tr>
	<?	} ?>
</table>
