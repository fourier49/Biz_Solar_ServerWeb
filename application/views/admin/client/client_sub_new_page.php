<script language="JavaScript"  type="text/javascript">
function myconfirm()
{
	var select_num = document.getElementById('select_num').innerHTML;
	if( select_num < 1 )
		alert("No device selected!");
	else {
		document.getElementById('form').elements['mysubmit'].value = 1;
		document.getElementById('form').submit();
	}
}

function CheckAll() 
{
	var ck = document.getElementById('form').elements["area_id[]"];
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
	var ck = document.getElementById('form').elements["area_id[]"];
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

<div id="title">Client ／ Sub-Account ／ Add</div>

<table id="subTitle"><tr>
<th>Super Client: <?=$super['name']?>（<?=$super['account']?>）</th>
</tr></table>

<form id="form" method="post" action="<?=site_url("admin/client_sub/n/".$super['client_id']);?>">

<div align="center">

<table width=100%>
<tr><td style="vertical-align:top;">

<table id="vtable">
	<tr>
		<th>*Account</th>
		<td><input type="text" name="account" value="<?=set_value('account');?>" size=25 autocomplete="off" required/>
		</td><td><font size=2>alpha & numeric, <br>length: 6~20</font></td>
	</tr>
	<tr>
		<th>*Name</th>
		<td><input type="text" name="name" value="<?=set_value('name');?>" size=25 autocomplete="off" required/></td>
		<td></td>
	</tr>
	<tr>
		<th>*Password</th>
		<td><input type="password" name="pwd" size=25 required/>
		</td><td><font size=2>alpha & numeric, <br>length: 6~20</font></td>
	</tr>	
	<tr>
		<th>*Password Confirm</th>
		<td><input type="password" name="pwdconf" size=25 required/></td>
		<td></td>
	</tr>
</table>
	<?=validation_errors();?>
</div>

</td><td style="vertical-align:top; width:300px;">

<div style="font-size:85%">Granted Area : <font id="select_num">0</font></div>
<table id="list">
	<tr>
		<th width=20><input type="checkbox" name="allbox" onclick="CheckAll()"></th>
		<th width=20></th>
		<th>Name</th>
	</tr>
	<?	$i=1;
		foreach($query->result_array() as $row) {?>
	<tr>
		<td>
		<input type="checkbox" name="area_id[]" value="<?=$row['area_id'];?>" onclick="javascript:select_count();">
		</td>
		<td><?=$i++;?>.</td>
		<td><?=$row['name']?></td>
	</tr>
	<?	} ?>
</table>

</td><td width=3%;>

</td></tr>
</table>

<input value="Submit" type="button" onclick="javascript:myconfirm();">&nbsp;&nbsp;&nbsp;
<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/client_sub/0/".$super['client_id']);?>'"/>
<input name="mysubmit" value="" type="hidden"/>
</form>