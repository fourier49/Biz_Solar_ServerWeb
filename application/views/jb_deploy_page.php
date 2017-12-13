<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="<?=base_url('css/monitor.css')?>" type="text/css" media="all" />
	<title>Solar Panel Monitor</title>
</head>
<body align="center" onSelectStart="event.returnValue=false">

<?
if(empty($op)) $op = 0;
$edge = 30; $max_col = 50;
$style = "width:".$edge."px; height:".$edge."px; border:2px #cccccc solid; cursor:pointer;";
?>

<div id="MainBlock" style="width:<?=(($edge+2)*$max_col+220);?>px">
<center>

<form id="form" method="post" action="<?=site_url("home/jb_deploy/".$block_id);?>">

<table>
<tr>
<td rowspan=2 style="vertical-align:top; padding-top:10px; text-align:center; border-right:3px dotted gray">
UNSET <br>J-BOX<br>( <?=count($unset_jb);?> )
<table id="unset_jb" class="block" style="width:40px; margin-top:5px;">
<? $id = 1;
	foreach($unset_jb as $row) {?>
	<tr><td id="exists" title="DC<?=$row;?>" class="block-exist" height=35 onclick="click_unset(this, '<?=$row;?>')"><?=$id++;?></td></tr>
<? } ?>
</table>

</td><td style="text-align:left; padding-bottom:10px;">

<table width=800>
<tr><td style="width:25%; padding-left:10px;">
<div id="op0" class="block_edit_btn<?if($op==0) echo " block_edit_btn_selected";?>" onclick="changeOP(this,0)">
Move One J-Box
</div>
</td><td style="width:50%; padding-left:10px;">
<div id="op1" class="block_edit_btn<?if($op==1) echo " block_edit_btn_selected";?>" onclick="changeOP(this,1)" style="padding-top:9px; padding-bottom:6px;">
Move J-Box from <strong>Block <?
$id = 1;
foreach($block->result_array() as $row) {
	if($row['block_id'] == $block_id)	echo $id;
}
?></strong> to
<select name="block_id_new">
	<? $id = 1;
	foreach($block->result_array() as $row) {?>
	<option value="<?=$row['block_id']?>"><?="Block ".($id++)." ( jb:".$row['jb_num']." )";?></option>
	<?}?>
</select>
</div>

</td><td style="width:25%; padding-left:20px; vertical-align:bottom">
1. Choose Operation<br>
2. Select Target J-Box
</td></tr>
</table>

</td></tr>
<tr><td>

<table id="block">
	<tr>
	<?for($j = 0; $j < $max_col+1; $j++) {?>
	<th style="padding-bottom:1px">
	<?=$j;?>
	</th>
	<?} ?>
	</tr>

	<?
	for($i = 1; $i <= $max_col; $i++) {?>
	<tr>
	<th>
	<?=$i;?>
	</th>
	<?for($j = 1; $j <= $max_col; $j++) {
	
	if(!empty($jb[$i][$j])) {
	?>
	<td id="exists" title="DC<?=$jb[$i][$j];?> ( <?=$j?> , <?=$i?> )" style="<?=$style;
	?>" onmousedown="select_begin(<?=$j?>,<?=$i?>);" onmouseover="select_change(<?=$j?>,<?=$i?>)" onmouseup="select_end(<?=$j?>,<?=$i?>)" onclick="click_block(<?=$j?>,<?=$i?>,'<?=$jb[$i][$j];?>')">
	<?} else {?>
	<td title="( <?=$j?> , <?=$i?> )" style="<?=$style;
	?>" onmousedown="select_begin(<?=$j?>,<?=$i?>)" onmouseover="select_change(<?=$j?>,<?=$i?>)" onmouseup="select_end(<?=$j?>,<?=$i?>)" onclick="click_block(<?=$j?>,<?=$i?>,'')">
	<?}?>
	</td><? } ?>
	</tr><? } ?>
</table>

</td></tr>
</table>


<input name="jb_mac" type="hidden" value="">
<input name="begin_row" type="hidden" value="">
<input name="begin_col" type="hidden" value="">
<input name="end_row" type="hidden" value="">
<input name="end_col" type="hidden" value="">
</form>

</center> 
</div>


<script>
var op = <?=$op;?>;
var selecting = false;
var begin_col = 0;
var begin_row = 0;
var end_col = 0;
var end_row = 0;

function changeOP(e, value) {
	op = value;
	document.getElementById("op0").className = "block_edit_btn";
	document.getElementById("op1").className = "block_edit_btn";
	e.className += " block_edit_btn_selected";
	selecting = false;
	restore();
}

function click_block(x, y, jb_mac) {
if(op == 0) 
{
	var td = document.getElementById("block").rows[y].cells[x];
	
	if(selecting == false && td.id == "exists")
	{
		document.getElementById('form').elements['jb_mac'].value = jb_mac;
		var table = document.getElementById("block");
		for (var i = 1, row; row = table.rows[i]; i++)
		for (var j = 1, col; col = row.cells[j]; j++)
			col.className = (col.id=="exists")?"block-exist-disable":"block-empty";
			
		td.className = "block-exist-selected";
		selecting = true;
	}
	else if(selecting == true && td.id != "exists")
	{
		end_col = x;
		end_row = y;
		send();
	}
}
}

function click_unset(e, jb_mac) {
if(op == 0 && selecting == false) 
{
	document.getElementById('form').elements['jb_mac'].value = jb_mac;
	e.className = "block-exist-selected";
	var table = document.getElementById("block");
	for (var i = 1, row; row = table.rows[i]; i++)
	for (var j = 1, col; col = row.cells[j]; j++)
		col.className = (col.id=="exists")?"block-exist-disable":"block-empty";
	
	selecting = true;
}
}

function select_begin(x, y) {
if(op == 1 && selecting == false)
{
	begin_col = end_col = x;
	begin_row = end_row = y;
	selecting = true;
}
}

function select_change(x, y) {
if(op == 1 && selecting == true) 
{
	if(x < begin_col || y < begin_row) { 
		x = begin_col; 
		y = begin_row;
	}
	
	var table = document.getElementById("block");
	for (var i = begin_row, row; row = table.rows[i]; i++)
	for (var j = begin_col, col; col = row.cells[j]; j++) {
		if( col.id == "exists")
			col.className = (i <= y && j <= x) ? "block-exist-selected" : "block-exist";
	}  
}
}

function select_end(x, y) {
if(op == 1 && selecting == true)
{
	if(x < begin_col || y < begin_row)
	{ 
		x = begin_col; 
		y = begin_row;
	}
	end_col = x;
	end_row = y;
	selecting = false;
	answer = confirm("Are you sure you want to move these J-Box to other block?");
	if (answer)	send();
	else	restore();
}
}

function restore()
{
	var table = document.getElementById("block");
	for (var i = 1, row; row = table.rows[i]; i++)
	for (var j = 1, col; col = row.cells[j]; j++)
		col.className = (col.id=="exists")?"block-exist":"block-empty-disable";
	
	table = document.getElementById("unset_jb");
	var num = table.rows.length;
	for(var i = 1; i < num; i++)
		table.rows[i].cells[0].className = "block-exist";
	selecting == false;
}

function send()
{
	var form = document.getElementById('form');
	form.action += "/"+op;
	form.elements['begin_col'].value = begin_col;
	form.elements['begin_row'].value = begin_row;
	form.elements['end_col'].value = end_col;
	form.elements['end_row'].value = end_row;
	form.submit();
}

restore();

</script>

</body>
</html>