<?$nowYear = date('Y');	$nowMonth = date('n');	$nowDay = date('j');
//$wkey = "721a9aefe6093902131603";
//$xml = file_get_contents("http://free.worldweatheronline.com/feed/weather.ashx?q=Taipei,Taiwan&format=xml&num_of_days=1&key=".$wkey);
//$weather_request = simplexml_load_string($xml)->request;
//$weather = simplexml_load_string($xml)->weather;

$woeid = "2306179";
$query_url = 'http://weather.yahooapis.com/forecastrss?w=' . $woeid . '&u=c';  
$xml_file = file_get_contents($query_url);
$error = TRUE;

if($xml = simplexml_load_file($query_url)){  
        
    $error = strpos(strtolower($xml->channel->description), 'error');//server response but no weather data for woeid  
      
}else{  
      
    $error = TRUE;//no response from weather server  
      
}  
    
if(!$error){  
    $weather['city'] = $xml->channel->children('yweather', TRUE)->location->attributes()->city; 
    $weather['country'] = $xml->channel->children('yweather', TRUE)->location->attributes()->country; 
	$weather['sunrise'] = $xml->channel->children('yweather', TRUE)->astronomy->attributes()->sunrise; 
	$weather['sunset'] = $xml->channel->children('yweather', TRUE)->astronomy->attributes()->sunset;
	
    $weather['temp'] = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->temp;    
    $weather['conditions'] = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->text;
	$weather['low'] = $xml->channel->item->children('yweather', TRUE)->forecast->attributes()->low;
	$weather['high'] = $xml->channel->item->children('yweather', TRUE)->forecast->attributes()->high;
	$weather['link'] = $xml->channel->item->link;
	
    $description = $xml->channel->item->description;  
      
    $imgpattern = '/src="(.*?)"/i';  
    preg_match($imgpattern, $description, $matches);  
  
    $weather['icon_url']= $matches[1];  
}
else {
	$weather['city'] = "";
	$weather['country'] = "";
	$weather['sunrise'] = "";
	$weather['sunset'] = "";
	$weather['temp'] = "";
	$weather['conditions'] = "";
	$weather['low'] = "";
	$weather['high'] = "";
	$weather['link'] = "";
	$description['low'] = "";
	$weather['icon_url'] = NULL;
}

?>

<table>
<tr>
	<td colspan="2">
	<div id="title">Area - <?=$area['name']?></div>
	</td>
</tr>

<tr>
	<td class="leftBlock">
	<table>
		<tr>
		<th>Country: </th>
		<td><?=$area['country']?></td>
		</tr><tr>
		<th>Address: </th>
		<td><?=$area['address']?></td>
		</tr><tr>
		<th>Power: </th>
		<td><?=$area_power;?> KW</td>
		</tr>
	</table>
	<br>
	Weather: 
	<span style="float:right;"><?=$weather['city']?>, <?=$weather['country']?></span>
	
	<div style="border-bottom:1px #B2BBBF solid;">
	<table>
	<tr>
		<td rowspan=2><img src="<?=$weather['icon_url']?>"></td>
		<td style="padding-top:10px;">
		<?=$weather['conditions']?>, <?=$weather['temp']?>°c (<?=$weather['low']?>°c~<?=$weather['high']?>°c)
		</td>
	</tr>
	<tr>
		<td>
		Sunrise: <?=$weather['sunrise']?>, Sunset: <?=$weather['sunset']?>
		</td>	
	</tr>
	</table>
	</div>
	<a style="float:right; text-decoration: none; font-size:80%" href="<?=$weather['link']?>" target="_blank">Power by Yahoo! Weather.</a><br>
	
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
	
	</td><td>
	
	<div id="am-grid"></div>
	</td>
</tr>
<tr>
	<td colspan="2">
	<div id="line-chart" style="height:220px;"></div>
	</td>
</tr>

</table>
<form id="delete_form" method="post" action="<?=site_url("home/am/r/".$area['area_id']);?>">
<input type="hidden" name="am_id" value="">
</form>

<script type="text/javascript" src="<?
if($this->session->userdata('super_id') > 1)	
	echo base_url('js/ext-lib/models/grid/am_super.js');
else 	
	echo base_url('js/ext-lib/models/grid/am.js');
?>"></script>

<script language="JavaScript"  type="text/javascript">
function myconfirm(am_id, name) {
	answer = confirm("Are you sure you want to remove Array Manager 'A8" + name + "' ?");
	if (answer) {
		var form = document.getElementById('delete_form');
		form.elements['am_id'].value = am_id;
		form.submit();
	}
}
</script>
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
			$.post("<?=site_url("home/get_area_power");?>",
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
				//,tickInterval:500
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
					?>[<?=$row['x']*1000?>, <?=(int)$row['y']?>],<?}?>
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
		
		//setInterval("reloadLineChart()",2000);
		$('body').everyTime('5s','A',
		function(){
			$.post("<?=site_url("home/get_area_power");?>",
				{ year: <?=$nowYear?>, month: <?=$nowMonth?>, day: <?=$nowDay?>, level: 1 },
				function (msg) {
					setLineChart(msg.name, msg.data);
				}, "json"
			);
		},360,true);
		
		
    });
	/*
	$.get(
	"http://free.worldweatheronline.com/feed/weather.ashx?q=Taipei,Taiwan&format=xml&num_of_days=1&key=721a9aefe6093902131603",
		function (msg) {
		alert("get weather");
			//$('#w_cloudcover').html(msg);
		}, "json"
	);*/
    
});
</script>
