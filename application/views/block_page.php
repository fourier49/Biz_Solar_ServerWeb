<? 
$nowYear = date('Y');
$nowMonth = date('n');
$nowDay = date('j');
?>

<table>
<tr>
	<td colspan="2">
	<div id="title">Array Manager - A8<?=$am['mac']?></div>
	</td>
</tr>

<tr>
	<td width=350 class="leftBlock">
	<table>
		<tr>
		<th>Area: </th>
		<td><?=$area['name']?></td>
		</tr><tr>
		<th>Voltage: </th>
		<td><?=(int)$am_data['voltage']?> V</td>
		</tr><tr>
		<th>Current: </th>
		<td><?=(int)$am_data['current']?> A</td>
		</tr><tr>
		<th>Temperature: </th>
		<td><?=(int)$am_data['temp']?> ℃</td>
		</tr><tr>
		<th>Power: </th>
		<td><?=$am_data['power']?> KW</td>
		</tr><tr>
		<th>Last Updated: </th>
		<td><?=$am_data['update_time']?></td>
		</tr>
	</table>
	<br>
	Power Production :
	<form>
	<table style="margin:0px 0px 0px 10px; width:95%;">
	<tr><td colspan=2>
		<input id="searchY" type="radio" name="searchOP" onchange="changeSearchOP(0)" value="0" checked>
		<label for="searchY" style="margin-right:10px"> Year</label>
		<input id="searchM" type="radio" name="searchOP" onchange="changeSearchOP(1)" value="1">
		<label for="searchM" style="margin-right:10px"> Month</label>
		<input id="searchD" type="radio" name="searchOP" onchange="changeSearchOP(2)" value="2">
		<label for="searchD"> Week</label>
	</td></tr>
	<tr><td>
		<select name="year" id="searchYear" style="width:80px;">
			<?for($i=2012; $i<$nowYear; $i++) {?>
				<option value="<?=$i?>"><?=$i?></option>
			<?}?>
			<option value="<?=$i?>" selected><?=$nowYear?></option>
		</select> / 
		<select name="month" id="searchMonth" style="width:80px;" disabled>
			<?for($i=1; $i<13; $i++) {?>
				<option value="<?=$i?>"<?if($i==$nowMonth) echo " selected";
				?>><?=date('M', mktime(0, 0, 0, $i, 1, $nowYear))?></option>
			<?}?>
		</select> / 
		<select name="day" id="searchDay" style="width:80px; margin-right:10px;" disabled>
			<?for($i=1; $i<32; $i++) {?>
				<option value="<?=$i?>"<?if($i==$nowDay) echo " selected";
				?>><?=$i?></option>
			<?}?>
		</select> 			
		<input type="button" value="..." onclick="displayCalendarSelectBox(document.forms[0].year,document.forms[0].month,document.forms[0].day,false,false,this)">
	</td><td style="text-align:right;">
		<input href="#" id="trigger" type="button" value="Refresh"/>
	</td></tr>
	</table>
	</form>
	<div id="bar-chart"></div>
	</td>

	<td>
	<div id="block-grid"></div>
	
	</td>
</tr>
<tr>
	<td colspan="2">
	<div id="line-chart" style="height:220px;"></div>
	</td>
</tr>

</table>

<script type="text/javascript" src="<?
if($this->session->userdata('super_id') > 1)	
	echo base_url('js/ext-lib/models/grid/block.js');
else 	
	echo base_url('js/ext-lib/models/grid/block.js');
?>"></script>

<script type="text/javascript">
function changeSearchOP(value) {
	document.getElementById('searchMonth').disabled = true;
	document.getElementById('searchDay').disabled = true;

	if(value == 1)
		document.getElementById('searchMonth').disabled = false;
	else if(value == 2) {
		document.getElementById('searchMonth').disabled = false;
		document.getElementById('searchDay').disabled = false;
	}
}

$(function () {
    var chart, linechart;
	var mYear = <?=$nowYear?>, mMonth = <?=$nowMonth?>, mDay = <?=$nowDay?>, mLevel = 0;
	var months = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
	
	Highcharts.setOptions({
        global : { useUTC : false }
    });
	
    $(document).ready(function() {
    
        var colors = Highcharts.getOptions().colors,
            categories = [<?foreach($power as $row) echo "'".$row['categories']."',"; ?>],
            name = '<?=$power_name?>',
			
            data = [
				<?foreach($power as $row) { ?>
				 {
                    y: <?=(int)$row['power']?>,
                    color: colors[0],
                    drilldown: {
                        name: '<?=$row['categories']?>'
                    }
                },<?} ?>
			];

		function getPowerData() {
			linechart.showLoading();
			chart.showLoading();
			$.post("<?=site_url("home/get_am_power");?>",
				{ year: mYear, month: mMonth, day: mDay, level: mLevel },
				function (msg) {
					chart.hideLoading();
					linechart.hideLoading();
					if(mLevel == 1) {
						$('body').stopTime();
						setLineChart(msg.name, msg.data);
					}
					else {
						mLevel++;
						setChart(msg.name, msg.categories, msg.data);
					}
				}, "json"
			);
		}
	
        function setChart(name, categories, data) {
			chart.xAxis[0].setCategories(categories, false);
			chart.series[0].remove(false);
			chart.addSeries({
				name: name,
				data: data,
				color: colors[0]
			}, false);
			chart.redraw();
        }
    
		function setLineChart(name, data) {
			linechart.series[0].remove(false);
			linechart.addSeries({
				type: 'area',
                name: name,
				data: data,
				color: colors[0]
			}, false);
			linechart.redraw();
        }
	
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'bar-chart',
                type: 'column'
            },
            title: {
                text: ''
            },
            xAxis: {
                categories: categories
            },
            yAxis: {
                title: {
                    text: ''
                }
				,minorTickInterval: 'auto'
				,minorGridLineColor: '#EEEEEE'
            },
            plotOptions: {
                column: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function() {
                                var drilldown = this.drilldown;
                                if (drilldown) {
									if(mLevel == 0)
										mMonth = months[drilldown.name];
									else if(mLevel == 1)
										mDay = drilldown.name;
									
									getPowerData();
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    var point = this.point,
                        s = point.drilldown.name + ' -<b>' + this.y +'</b>kWh';
                    return s;
                }
            },
            series: [{
                name: name,
                data: data,
                color: colors[0]
            }],
            exporting: {
                enabled: false
            }
        });
		
		linechart = new Highcharts.Chart({
            chart: {
                renderTo: 'line-chart',
                zoomType: 'x',
                spacingRight: 20,
				width: 880
            },
            title: {
                text: ''
            },
            xAxis: {
                type: 'datetime',
                //maxZoom: 12 * 3600000, // half day
				maxZoom: 60000, // one min
				gridLineWidth: 0.5,
                title: {
                    text: null
                }
            },
            yAxis: {
                title: {
                    text: 'kW'
                },
                //min: 0.6,
                startOnTick: false,
                showFirstLabel: false
            },
            tooltip: {
                shared: true,
				borderWidth: 0,
				formatter: function() {
					var time = new Date(this.x);
					//var str = time.getDate() + 'th ' + time.getHours() + ':' + time.getMinutes();
					var str = time.getDate() + 'th ' + time.toLocaleTimeString();
					return str + ' -<b>'+ this.y +'</b>kW';
				}
            },
            legend: {
                enabled: true
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, 'rgba(10,30,40,0)']
                        ]
                    },
                    lineWidth: 1,
                    marker: {
                        enabled: false,
                        states: {
                            hover: {
                                enabled: true,
                                radius: 5
                            }
                        }
                    },
                    shadow: true,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    }
                }
            },
    
            series: [{
                type: 'area',
                name: '<?=$power_week_name?>',
                data: [
					<?foreach($power_week as $row) { 
					?>[<?=$row['x']*1000?>, <?=$row['y']?>],<?}?>
                ]
            }],
            exporting: {
                enabled: false
            }
        });
		
		$('input#trigger').click(
		function() {
			mYear = $('#searchYear').val();
			mMonth = $('#searchMonth').val();
			mDay = $('#searchDay').val();
			
			var searchOP = $('input[name=searchOP]:checked').val();
			
			mLevel = searchOP - 1;
			getPowerData();
		});
		document.getElementById('searchY').checked = true;
		
		$('body').everyTime('5s','A',
		function(){
			$.post("<?=site_url("home/get_am_power");?>",
				{ year: <?=$nowYear?>, month: <?=$nowMonth?>, day: <?=$nowDay?>, level: 1 },
				function (msg) {
					setLineChart(msg.name, msg.data);
				}, "json"
			);
		},360,true);

    });
    
});
</script>


