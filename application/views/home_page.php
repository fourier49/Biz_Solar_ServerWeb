<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="<?=base_url('css/monitor.css')?>" type="text/css" media="all" />
    <link rel="stylesheet" type="text/css" href="<?=base_url('js/ext-lib/resources/css/ext-all.css')?>" />
    <link rel="stylesheet" type="text/css" href="<?=base_url('js/ext-lib/models/share.css')?>" />
    <link rel="stylesheet" type="text/css" href="<?=base_url('js/ext-lib/models/grid.css')?>" />
	<link rel="stylesheet" type="text/css" href="<?=base_url('js/calendar/calendar.css')?>" media="screen"/>
	
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
    <script type="text/javascript" src="<?=base_url('js/ext-lib/ext-all.js')?>"></script>
    <script type="text/javascript" src="<?=base_url('js/highcharts/highcharts.js')?>"></script>
	<script type="text/javascript" src="<?=base_url('js/highcharts/modules/exporting.js')?>"></script>
	<script type="text/javascript" src="<?=base_url('js/calendar/calendar.js')?>"></script>
	<script type="text/javascript" src="<?=base_url('js/jquery.timers.js')?>"></script>
	
	<title>Solar Panel Monitor</title>
</head>
<body align="center">
<center> 
<table id="framework" border=0 cellpadding=0 cellspacing=0 width=1000 align="center">
<tr>
	<td><img width=1000 src="<?=base_url('images/banner.png')?>"/></td>
</tr>
<tr>
	<td id="MenuBar"><?=$content_for_menu?></td>
</tr>
<tr height="550px">
	<td id="MainBlock"><?=$content_for_layout?></td>
</tr>
<tr><td id="Bottom" >
	#No.43, Sec. 4, Keelung Rd., Da'an Dist., Taipei City 106, Taiwan (R.O.C.)　886-2-27333141<br>
	Copyright 2012 by NTUST-All rights reserved .
	</td>
</tr>
</table>
</center> 
</body>
</html>