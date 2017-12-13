<script language="JavaScript"  type="text/javascript">
function myconfirm(client_id, account) {
	answer = confirm("Are you sure you want to delete client '" + account + "' and all related data?");
	if (answer) {
		var form = document.getElementById('delete_form');
		form.elements['client_id'].value = client_id;
		form.submit();
	}
}
</script>

<div id="title">Client</div>

<table id="subTitle"><tr>
<th>
<form method="post" action="<?=site_url("admin/client/s");?>">
Account/Name : <input name="keyword" value="<?if(!empty($keyword)) echo $keyword;?>" size=12>
<input type="submit" name="submit" value="Search">
</form>
</th>
<td><a href="<?=site_url('admin/client/n')?>">Add</a></td>
</tr></table>

<table id="list">
	<tr>
		<th>&nbsp;</th>
		<th>Account</th>
		<th>Name</th>
		<th>Sub Account</th>
		<th>Area Num</th>
		<th>Insert Time</th>
		<th width=30>Edit</th>
		<th width=40>Delete</th>
		<th width=60></th>
	</tr>
	<?	$i=1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$i++;?>.</td>
		<td>
			<a href="<?=site_url("admin/area/0/".$row['client_id'])?>"><?=$row['account']?></a>
		</td>
		<td><?=$row['name']?></td>
		<td><a href="<?=site_url('admin/client_sub/0/'.$row['client_id'])?>">( <?=$row['account_num']?> )</a></td>
		<td><?=$row['area_num']?></td>
		<td><?=$row['insert_time']?></td>
		<td><a href="<?=site_url('admin/client/e/'.$row['client_id']);?>">
			<img src="<?=base_url('images/button/edit.png')?>"/></a>
		</td>
		<td><a href="javascript:myconfirm(<?=$row['client_id'];?>, '<?=$row['account']?>');">
			<img src="<?=base_url('images/button/delete.png');?>"/></a>
		</td>
		<td>
			<a href="<?=site_url("admin/go_monitor/".$row['client_id'])?>" target="_blank">Monitor</a>
		</td>
	</tr>
	<?	} ?>
</table>

<form id="delete_form" method="post" action="<?=site_url("admin/client/d");?>">
<input type="hidden" name="client_id" value="">
</form>
