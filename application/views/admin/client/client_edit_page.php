<script language="JavaScript"  type="text/javascript">
function myconfirm() {
	answer = confirm("Are you sure you want to edit it？");
	if (answer) {
		var form = document.getElementById('the_form');
		if(form.elements['pwd'].value == '') {
			form.elements['pwd'].value = "666666";
			form.elements['pwdconf'].value = "666666";
		}
		form.elements['mysubmit'].value = 1;
		form.submit();
	}
}
</script>

<div id="title">Client ／ Edit</div>

<table id="subTitle"><tr>
<th>Client: <?=$client['name']?>（<?=$client['account']?>）</th>
</tr></table>

<form id="the_form" enctype="multipart/form-data" method="post" action="<?=site_url("admin/client/e/".$client['client_id']);?>">

<div align="center">
<table id="vtable">
	<tr>
		<th>*Account</th>
		<td><input type="text" name="account" value="<?=set_value('account',$client['account']);?>" size=25 autocomplete="off">
		</td><td><font size=2>alpha & numeric, <br>length: 6~20</font></td>
	</tr>
	<tr>
		<th>*Name</th>
		<td><input type="text" name="name" value="<?=set_value('name',$client['name']);?>" size=25 autocomplete="off"></td>
		<td></td>
	</tr>
	<tr>
		<th>New Password</th>
		<td><input type="password" name="pwd" value="" size=25>
		</td><td><font size=2>alpha & numeric, <br>length: 6~20</font></td>
	</tr>
	<tr>
		<th>Password Confirm</th>
		<td><input type="password" name="pwdconf" value="" size=25></td>
		<td></td>
	</tr>
</table>
	<input type="hidden" name="mysubmit" value="">
	<input value="Submit" type="button" onclick="javascript:myconfirm();">&nbsp;&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/client");?>'">
	<?=validation_errors();?>
</div>

</form>