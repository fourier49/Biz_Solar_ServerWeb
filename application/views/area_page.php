<table>
<tr>
	<td colspan="2">
	<div id="title">Area</div>
	</td>
</tr>

<tr>
	<td style="vertical-align:top;">
	<table class="alertBlock">
	<tr>
		<th width="20"></th>
		<th width="200">Alert - Junction Box</th>
		<th width="100">Exception</th>
	</tr>

	<? $i = 1;
	if($alert->num_rows() > 0)
	foreach($alert->result_array() as $row) {?>
	<tr>
		<td><?=$i++?>.</td>
		<td><?=$row['name']?> / A8<?=$row['mac']?>
		<a href="<?=site_url("home/jb/0/".$row['block_id'])?>">(<?=$row['row']?>,<?=$row['col']?>)</a> /<br>
		<a href="<?=site_url("home/jb_detail/0/".$row['jb_mac'])?>">DC<?=$row['jb_mac']?></a></td>
		<td><?=$row['state']?></td>
	</tr>
	<?}?>
	</table>
	</td>
	<td>
	<div id="area-grid"></div>
	<?if($this->session->userdata('super_id') > 1) {?>
	<div class="toolbar"><a href="<?=site_url("home/area/n");?>">New Area</a></div>
	<?}?>
	<form id="delete_form" method="post" action="<?=site_url("home/area/d");?>">
	<input type="hidden" name="area_id" value="">
	</form>
	</td>
</tr>
</table>

<script type="text/javascript" src="<?
if($this->session->userdata('super_id') > 1)	
	echo base_url('js/ext-lib/models/grid/area_super.js');
else 	
	echo base_url('js/ext-lib/models/grid/area.js');
?>"></script>

<script language="JavaScript"  type="text/javascript">
function myconfirm(area_id, name) {
	answer = confirm("Are you sure you want to delete area '" + name + "' ?");
	if (answer) {
		var form = document.getElementById('delete_form');
		form.elements['area_id'].value = area_id;
		form.submit();
	}
}
</script>