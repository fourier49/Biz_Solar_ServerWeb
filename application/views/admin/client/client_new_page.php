<div id="title">Client ／ Add</div>

<form enctype="multipart/form-data" method="post" action="<?=site_url("admin/client/n");?>">

<div align="center">
<table id="vtable">
	<tr>
		<th>*Account</th>
		<td><input type="text" name="account" value="<?=set_value('account');?>" size=25 autocomplete="off" required>
		</td><td><font size=2>alpha & numeric, <br>length: 6~20</font></td>
	</tr>
	<tr>
		<th>*Name</th>
		<td><input type="text" name="name" value="<?=set_value('name');?>" size=25 autocomplete="off" required></td>
		<td></td>
	</tr>
	<tr>
		<th>*Password</th>
		<td><input type="password" name="pwd" size=25 required>
		</td><td><font size=2>alpha & numeric, <br>length: 6~20</font></td>
	</tr>	
	<tr>
		<th>*Password Confirm</th>
		<td><input type="password" name="pwdconf" size=25 required></td>
		<td></td>
	</tr>
</table>
	<input name="submit" value="Submit" type="submit">&nbsp;&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/client");?>'">
	<?=validation_errors();?>
</div>

</form>