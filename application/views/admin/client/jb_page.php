<script type="text/javascript">
$(function() {
    var moveLeft = 20;
    var moveDown = 5;
	var jb = <?= json_encode($query->result_array()); ?>

    $('a#trigger').hover(
		function(e) {
		var i = parseInt($(this).attr('name'))-1;
		$('#sn').html(jb[i]['s/n']);
		$('#firmware_vs').html(jb[i]['firmware_vs']);
		$('#hardware_vs').html(jb[i]['hardware_vs']);
		$('#device_spec').html(jb[i]['device_spec']);
		$('#manufacture_date').html(jb[i]['manufacture_date']);
		$('#insert_time').html(jb[i]['insert_time']);

        $('div#pop-up').show();
		}, function() {
          $('div#pop-up').hide();
        }
	);

	$('a#trigger').mousemove(function(e) {
        $("div#pop-up").css('top', e.pageY + moveDown).css('left', e.pageX + moveLeft);
    });
	
	$('a#trigger').click(
		function() {
		var i = parseInt($(this).attr('name'))-1;
		$('#jb_select').html(	
			'Mac: DC' + jb[i]['mac'] + ', S/N: ' + jb[i]['s/n'] + 
			', Firmware VS: ' + jb[i]['firmware_vs'] + 
			', Hardware VS: ' + jb[i]['hardware_vs'] + 
			',<br>Device Spec: ' + jb[i]['device_spec'] + 
			', Manufacture Date: ' + jb[i]['manufacture_date']
		);
	});
  
});

function myconfirm()
{
	var select_num = document.getElementById('select_num').innerHTML;
	if( select_num < 1 )
		alert("No junctionbox selected!");
	else {
		answer = confirm("Are you sure you want to move " + select_num + " junctionboxs?");
		if (answer) {
			document.getElementById('form').elements['mysubmit'].value = 1;
			document.getElementById('form').submit();
		}
	}
}

function CheckAll() 
{
	var ck = document.getElementById('form').elements["jb_mac[]"];
	var ckAll = document.getElementById('form').elements["allbox"];
	
	if (!ck) { //當沒有checkbox時
		ckAll.checked = false;
	} else if (!ck.length) { //當只有一個checkbox時
		ck.checked = ckAll.checked;
	} else { //當有兩個以上的checkbox時
		for (var i=0; i<ck.length; i++)
			ck[i].checked = ckAll.checked;
	}
	select_count();
}

function select_count()
{
	var ck = document.getElementById('form').elements["jb_mac[]"];
	var count = 0;
	
	if(!ck.length) {
		if(ck.checked) count++;
	} else {
		for(var i=0; i<ck.length; i++)
			if(ck[i].checked)	count++;
	}
	document.getElementById('select_num').innerHTML = count;
}
</script>

<div id="title">Client ／ Area ／ Device ／ Block ／ J-BOX</div>

<form id="form" method="post" action="<?=site_url("admin/area_device_jb/m/".$block['block_id']);?>">

<table id="subTitle"><tr>
<th>
Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=
$client['name']?>（<?=$client['account']?>）</a>
- Area: <a href="<?=site_url("admin/area_device/0/".$area['area_id']);?>"><?=$area['name']?></a>
- Array Manager: <a href="<?=site_url("admin/area_block/0/".$am['am_id']);?>">AB<?=$am['mac']?></a>
(<?=$block['row']?>,<?=$block['col']?>)
</th>
<td id="select_num">0</td>
<td width=210>JBs Move to: 
<select name="block_id" style="width:120px">
<? foreach ($all_block->result_array() as $row) { ?>
<option value="<?=$row['block_id'];?>">(<?=$row['row'];?>,<?=$row['col'];?>) - <?=$row['jb_num'];?> JBs</option><?
} ?>
</select></td>
<td>
<input value="Submit" type="button" onclick="javascript:myconfirm();">
<input type="hidden" name="mysubmit" value="">
</td>
</tr>
<tr>
<td colspan=4 id="jb_select">
</td>
</tr></table>

<table id="list">
	<tr>
		<th width=10><input type="checkbox" name="allbox" onclick="CheckAll()"></th>
		<th>&nbsp;</th>
		<th>Mac</th>
		<th>Pos</th>
		<th>Voltage</th>
		<th>Current</th>
		<th>Temperature</th>
		<th>Power</th>
		<th>State</th>
		<th>Last Update</th>
		<th width=40>Edit</th>
	</tr>
	<?	$i=1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td>
		<input type="checkbox" name="jb_mac[]" value="<?=$row['mac'];?>" onclick="javascript:select_count();">
		</td>
		<td><?=$i;?>.</td>
		<td><a href="#" id="trigger" name="<?=$i;?>">DC<?=$row['mac']?></a></td>
		<td><?=$row['pos']?></td>
		<td><?=$row['voltage']?> V</td>
		<td><?=$row['current']?> A</td>
		<td><?=$row['temp']?> ℃</td>
		<td><?=$row['power']?> W</td>
		<td><?=$row['state']?></td>
		<td><?=$row['update_time']?></td>
		<td><a href="<?=site_url('admin/area_device_jb/e/'.$block['block_id'].'/'.$row['mac']);?>">
			<img src="<?=base_url('images/button/edit.png')?>"/></a>
		</td>
	</tr>
	<?	$i++;} ?>
</table>

</form>

<div id="pop-up">
<table>
	<tr>
		<th>S/N : </th>
		<td id="sn"></td>
	</tr><tr>
		<th>Firmware VS : </th>
		<td id="firmware_vs"></td>
	</tr><tr>
		<th>Hardware VS : </th>
		<td id="hardware_vs"></td>
	</tr><tr>
		<th>Device Spec : </th>
		<td id="device_spec"></td>
	</tr><tr>
		<th>Manufacture Date : </th>
		<td id="manufacture_date"></td>
	</tr><tr>
		<th>Insert Time : </th>
		<td id="insert_time"></td>
	</tr>
</table>
</div>