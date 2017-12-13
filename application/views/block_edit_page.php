<?
if(empty($op)) $op = 0;
$edge = 50;
$style = "width:".$edge."px; height:".$edge."px; border:2px #cccccc solid; cursor:pointer;";
?>
<table>
<tr><td style="width:90px; vertical-align:top; text-align:left;">
<div style="font-weight:bold; padding-top:10px; color: #214566; font-size:16px;">Block :</div>

<div id="op0" class="block_edit_btn<?if($op==0) echo " block_edit_btn_selected";?>" onclick="changeOP(this,0)">
Add New
</div>
<div id="op1" class="block_edit_btn<?if($op==1) echo " block_edit_btn_selected";?>" onclick="changeOP(this,1)">
Move
</div>
<div id="op2" class="block_edit_btn<?if($op==2) echo " block_edit_btn_selected";?>" onclick="changeOP(this,2)">
Remove
</div>

<div id="new_begin"></div>
<div id="new_end"></div>

</td><td>

<table id="block" onSelectStart="event.returnValue=false">
	<tr>
	<?for($j = 0; $j < 18; $j++) {?>
	<th style="padding-bottom:1px">
	<?=$j;?>
	</th>
	<?} ?>
	</tr>

	<?
	for($i = 1; $i <= 17; $i++) {?>
	<tr>
	<th>
	<?=$i;?>
	</th>
	<?for($j = 1; $j <= 17; $j++) {
	
	if(!empty($block[$i][$j]['exist'])) {
	?>
	<td href="#" id="exists" style="<?=$style;
	?>" onmousedown="select_begin(<?=$j?>,<?=$i?>);" onmouseover="select_change(<?=$j?>,<?=$i?>)" onmouseup="select_end(<?=$j?>,<?=$i?>)" onclick="click_block(<?=$j?>,<?=$i?>)">
	<?="( ".$j.",".$i." )<br>jb:".$block[$i][$j]['jb_num'];
	} else {?>
	<td style="<?=$style;
	?>" onmousedown="select_begin(<?=$j?>,<?=$i?>)" onmouseover="select_change(<?=$j?>,<?=$i?>)" onmouseup="select_end(<?=$j?>,<?=$i?>)" onclick="click_block(<?=$j?>,<?=$i?>)">
	<?}?>
	</td>
	<?} ?>
	</tr>
	<?} ?>
</table>

</td></tr>
</table>

<form id="form" method="post" action="<?=site_url("home/block_edit/".$am_id);?>">
<input name="begin_x" type="hidden" value="">
<input name="begin_y" type="hidden" value="">
<input name="end_x" type="hidden" value="">
<input name="end_y" type="hidden" value="">
</form>

<script>
var op = <?=$op;?>;
var selecting = false;
var begin_x = 0;
var begin_y = 0;
var end_x = 0;
var end_y = 0;

function changeOP(e, value) {
	op = value;
	document.getElementById("op0").className = "block_edit_btn";
	document.getElementById("op1").className = "block_edit_btn";
	document.getElementById("op2").className = "block_edit_btn";
	e.className += " block_edit_btn_selected";
	selecting = false;
	restore();
}

function select_begin(x, y) {
	switch(op)
	{
	case 0:
	case 2:
		begin_x = end_x = x;
		begin_y = end_y = y;
		document.getElementById("new_begin").innerHTML = "( " + x + " , " + y + " ) ";
		selecting = true;
	break;

	default:
	break;
	}
}

function select_change(x, y) {

if(selecting == true)
	switch(op)
	{
	case 0:
		if(x < begin_x || y < begin_y) { 
			x = begin_x; 
			y = begin_y;
		}
		document.getElementById("new_end").innerHTML = "( " + x + " , " + y + " ) ";
		
		var table = document.getElementById("block");
		for (var i = begin_y, row; row = table.rows[i]; i++)
		for (var j = begin_x, col; col = row.cells[j]; j++) {
			if( col.id != "exists")
				col.className =  (i <= y && j <= x) ? "block-empty-selected" : "block-empty";
		}	  
	break;
	
	case 2:
		if(x < begin_x || y < begin_y) { 
			x = begin_x; 
			y = begin_y;
		}
		document.getElementById("new_end").innerHTML = "( " + x + " , " + y + " ) ";
		
		var table = document.getElementById("block");
		for (var i = begin_y, row; row = table.rows[i]; i++)
		for (var j = begin_x, col; col = row.cells[j]; j++) {
			if( col.id == "exists")
				col.className = (i <= y && j <= x) ? "block-exist-selected" : "block-exist";
		}
	break;
	
	default:
	break;
	}

}
	
	

function select_end(x, y) {
if(op != 1)
{
	if(x < begin_x || y < begin_y)
	{ 
		x = begin_x; 
		y = begin_y;
	}
	end_x = x;
	end_y = y;
	selecting = false;
	document.getElementById("new_end").innerHTML = "( " + x + " , " + y + " ) ";
	
	switch(op)
	{
	case 0:
		answer = confirm("Are you sure you want to add new blocks?");	break;
	case 2:
		answer = confirm("Are you sure you want to remove the blocks?");	break;
	default:	
	break;
	}
	
	if (answer)	send();
	else	location.href = "<?=site_url("home/block_edit/".$am_id);?>" + "/" + op;
}
}


function click_block(x, y) {
if(op == 1) 
{
	var td = document.getElementById("block").rows[y].cells[x];
	
	if(selecting == false && td.id == "exists")
	{
		begin_x = x;
		begin_y = y;
		selecting = true;
		var table = document.getElementById("block");
		for (var i = 1, row; row = table.rows[i]; i++)
		for (var j = 1, col; col = row.cells[j]; j++) {
			col.className =  (col.id == "exists") ? "block-exist-disable" : "block-empty";
		}
		td.className = "block-exist-selected";
	}
	else if(selecting == true && td.id != "exists")
	{
		end_x = x;
		end_y = y;
		selecting = false;
			
		answer = confirm("Are you sure you want to move the block?");
		if (answer)
			send();
		else
			location.href = "<?=site_url("home/block_edit/".$am_id);?>" + "/" + op;
	}
}
}

function restore()
{
	var table = document.getElementById("block");
	
	switch(op)
	{
	case 0: 
		for (var i = 1, row; row = table.rows[i]; i++)
		for (var j = 1, col; col = row.cells[j]; j++)
			col.className = (col.id=="exists")?"block-exist-disable":"block-empty";
	break;
		
	case 1: 
		for (var i = 1, row; row = table.rows[i]; i++)
		for (var j = 1, col; col = row.cells[j]; j++)
			col.className = (col.id=="exists")?"block-exist":"block-empty-disable";
	break;
		
	case 2: 
		for (var i = 1, row; row = table.rows[i]; i++)
		for (var j = 1, col; col = row.cells[j]; j++)
			col.className = (col.id=="exists")?"block-exist":"block-empty-disable";
	break;
		
	default:
	break;
	}
}

function send()
{
	var form = document.getElementById('form');
	form.action += "/"+op;
	form.elements['begin_x'].value = begin_x;
	form.elements['begin_y'].value = begin_y;
	form.elements['end_x'].value = end_x;
	form.elements['end_y'].value = end_y;
	form.submit();
}

restore();

</script>