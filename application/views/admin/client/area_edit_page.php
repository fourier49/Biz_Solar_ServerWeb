<script language="JavaScript"  type="text/javascript">function myconfirm() {	answer = confirm("Are you sure you want to edit it？");	if (answer) {		document.getElementById('the_form').elements['mysubmit'].value = 1;		document.getElementById('the_form').submit();	}}</script><div id="title">Client ／ Area ／ Edit</div><table id="subTitle"><tr><th>Client: <a href="<?=site_url("admin/area/0/".$client['client_id']);?>"><?=$client['name']?>（<?=$client['account']?>）</a>- Area: <?=$area['name']?></th></tr></table><form id="the_form" method="post" action="<?=site_url("admin/area/e/".$client['client_id']."/".$area['area_id']);?>"><div align="center"><table id="vtable">	<tr>		<th>*Name</th>		<td><input type="text" name="name" value="<?=set_value('name',$area['name']);?>" size=25></td>	</tr>	<tr>		<th>*Country</th>		<td><input type="text" name="country" value="<?=set_value('country',$area['country']);?>" size=25></td>	</tr>	<tr>		<th>*Address</th>		<td><input type="text" name="address" value="<?=set_value('address',$area['address']);?>" size=25></td>	</tr></table>	<input type="hidden" name="mysubmit" value="">	<input value="Submit" type="button" onclick="javascript:myconfirm();">&nbsp;&nbsp;&nbsp;	<input value="Cancel" type="button" onclick="location.href = '<?=site_url("admin/area/0/".$client['client_id']);?>'">	<?=validation_errors();?></div></form>