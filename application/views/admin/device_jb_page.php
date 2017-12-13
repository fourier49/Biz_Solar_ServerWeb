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
        //.css('top', e.pageY + moveDown)
        //.css('left', e.pageX + moveLeft)
        //.appendTo('body');
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
</script>

<div id="title">Array Manager ／ J-BOX</div>

<table id="subTitle"><tr>
<th>
Array Manager: AB<?=$am['mac']?>
</th>
<td id="jb_select">
</td>
</tr></table>

<table id="list">
	<tr>
		<th>&nbsp;</th>
		<th>Mac</th>
		<th>Voltage</th>
		<th>Current</th>
		<th>Temperature</th>
		<th>Power</th>
		<th>State</th>
		<th>Last Update</th>
	</tr>
	<?	$i=1;
		if($query->num_rows() > 0)
		foreach($query->result_array() as $row) {?>
	<tr>
		<td><?=$i;?>.</td>
		<td><a href="#" id="trigger" name="<?=$i;?>">DC<?=$row['jb_mac']?></a></td>
		<td><?=$row['voltage']?> V</td>
		<td><?=$row['current']?> A</td>
		<td><?=$row['temp']?> ℃</td>
		<td><?=$row['power']?> W</td>
		<td><?=$row['state']?></td>
		<td><?=$row['update_time']?></td>
	</tr>
	<?	$i++;} ?>
</table>

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