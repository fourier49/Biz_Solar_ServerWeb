<?php
class Alert extends CI_Model {

    function __construct()
    {
        // 呼叫模型(Model)的建構函數
        parent::__construct();
    }

    function alert($alert)
    {
		echo "<!DOCTYPE HTML><html lang=\"en\">";
		echo "<head>";
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
		echo "<script language='Javascript'>";
		echo "alert('$alert');";
		echo "history.go(-1);";
		echo "</script>";
		echo "</head>";
		echo "</html>";
		exit();
    }
	
	function alert_goto($alert, $herf)
	{
		$herf = site_url($herf);
		echo "<!DOCTYPE HTML><html lang=\"en\">";
		echo "<head>";
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
		echo "<script language='Javascript'>";
		echo "alert('$alert');";
		echo "location.href='$herf';";
		echo "</script>";
		echo "</head>";
		echo "</html>";
		exit();
	}
	
	function web_goto($herf)
	{
		$herf = site_url($herf);
		echo "<!DOCTYPE HTML><html lang=\"en\">";
		echo "<head>";
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
		echo "<script language='Javascript'>";
		echo "location.href='$herf';";
		echo "</script>";
		echo "</head>";
		echo "</html>";
		exit();
	}
}