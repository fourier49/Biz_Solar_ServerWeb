<div id="title">System Log</div>

<table id="list">
	<tr>
		<th>&nbsp;</th>
		<th>Title</th>
		<th>Time</th>
	</tr>
	<?	$i=1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$i++;?>.</td>
		<td>
		<a href="<?=site_url("admin/log/".$row['log_id']);?>"><?=$row['title']?></a>
		</td>
		<td><?=$row['insert_time']?></td>
	</tr>
	<?	} ?>
</table>
