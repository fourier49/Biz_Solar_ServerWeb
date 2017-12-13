<script language="JavaScript"  type="text/javascript">
function logout() {
	answer = confirm("Are you sure you want to logout？");
	if (answer) {
		location.href = '<?=site_url("home/logout");?>';
	}
}
</script>
<table style="width:100%"><tr>
<td style="text-align: left;">
<a href="<?=site_url("home");?>">Home Page</a>
<?if($this->session->userdata('admin_login') != null) {?>
<a href="<?=site_url("home/sub_account")?>">Sub Account</a>
<?}?>
</td><td style="text-align: right;">
HI~
<?if($this->session->userdata('super_id') > 1)
echo $this->session->userdata('super_name')." - ";?>
<?=$this->session->userdata('user_name');?>

<?if($this->session->userdata('admin_login') != null) {?>
(Admin)
<?} else {?>
<a href="javascript:logout();">Logout</a>
<?}?>
</td>
</tr></table>

