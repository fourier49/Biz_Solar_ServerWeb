<script language="JavaScript"  type="text/javascript">
function logout() {
	answer = confirm("Are you sure you want to logout？");
	if (answer) {
		location.href = '<?=site_url("admin/logout");?>';
	}
}
</script>

<a id="<?if($page==1) echo "selected";?>" href="<?=site_url('admin/client')?>">Client</a>
<a id="<?if($page==2) echo "selected";?>" href="<?=site_url('admin/am')?>">ArrayManager</a>
<a id="<?if($page==3) echo "selected";?>" href="<?=site_url('admin/jb')?>">JunctionBox</a>
<a id="<?if($page==4) echo "selected";?>" href="<?=site_url('admin/log')?>">System Log</a>
<a id="<?if($page==5) echo "selected";?>" href="<?=site_url('admin/alert')?>">Alert Event</a>
<a href="javascript:logout();">Logout</a>
