<div id="title">New Area</div>

<form enctype="multipart/form-data" method="post" action="<?=site_url("home/area/n");?>">

<div align="center">
<table id="vtable">
	<tr>
		<th>*Name</th>
		<td><input type="text" name="name" value="" size=25 required></td>
	</tr>
	<tr>
		<th>Country</th>
		<td><input type="text" name="country" value="" size=25></td>
	</tr>
	<tr>
		<th>Address</th>
		<td><input type="text" name="address" value="" size=25></td>
	</tr>
</table>
	<input name="submit" value="Submit" type="submit">&nbsp;&nbsp;&nbsp;
	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("home/area");?>'">
</div>

</form>