<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Solar System Monitor Login</title>
</head>
<body align="center">

<form action="<?=site_url("home/login");?>" method="post">
<div align="center" style="margin:50px;">

<div style="width:552px; height:390px; background-image:url(<?=base_url('images/login_bg.png');?>); background-repeat:no-repeat;">
	<div style="padding-top:132px;">
	<input type="text" name="account" style="width:180px; font-size:14px; padding:3px"/>
	</div>
	<div style="padding-top:20px;">
	<input type="password" name="pwd" style="width:180px; font-size:14px; padding:3px"/><br><br>&nbsp;
	<?if(!empty($login_failed)) echo "<font color=red>User account or password is incorrect!</font>"?>
	</div>
	<div style="padding-top:20px; padding-left:350px;">
	<input type="image" alt="submit" value="送出" src="<?=base_url('images/login_btn.png')?>"/>
	</div>
</div>

</div>
</form>

</body>
</html>