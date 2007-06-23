<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <title>nessquik scan-me-now</title>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
                <meta name="copyright" content="&copy; GPL">
                <meta name="Author" content="Tim Rupp">
                <link rel='stylesheet' type='text/css' href='templates/styles.css'>
        </head>
        <body>
		<div style='margin-left: auto; margin-right: auto; width: 90%; text-align: left;'>
			<div style='clear: left; text-align: right;'>&nbsp;</div>

			<div style='text-align: left;'>

		<table style='width: 100%;' cellpadding='0' cellspacing='0'>
			<tr>
				<td style='width: 1%;'><img src='images/left-blue.png'></td>
				<td style='width: 98%;' valign='top'>
					<div style='float: left; width: 100%; background-color: #6D84B4; height: 20px;'>&nbsp;</div>
				</td>
				<td style='width: 1%;'><img src='images/right-blue.png'></td>
			</tr>
		</table>
<div style='height: 100%; margin: 10px 0px 10px 0px; border: 1px solid #fff; vertical-align: top;'>
	<table style='width: 100%; margin-bottom: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-top-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-top-middle'>&nbsp;</td>
			<td class='gray-top-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='vertical-align: top; text-align: left;'>
				<div id='work'>
			<!--
			This is a very long comment of randon junk to push IE to display the Scanning...
			Please Wait text while the scanning is processing. This is because IE has a buffer
			to fill before displaying the HTML text unlike Firefox which will display the text as it comes in.
			-->

			<center><h2>Performing Full Nessus Scan</h2></center>

			<center>
			Your IP address is: <b>{$client_ip}</b>
			<p>
			This scanning can take up to <b>45 minutes (or longer)</b> to complete and the results will be displayed below.
			</p>
			If you run a personal firewall, the results may be incomplete.<br>
			Note that this scan only scans the IP address of your client.<br>
			If you are attempting to scan a web VHOST, this scan will only find vulnerabilities present in the default web service.<br>
			Consult the ScanMeNow FAQ if you encounter problems or have questions interpreting the results<br>
			</center>

			<center><h3>DO NOT REFRESH THIS PAGE</h3></center><br>

			<div id='processing_steps' class='percentbox2'>
				<center>Getting Plugins - Please Wait</center>
			</div>
			<div id='processing_output'></div>
			<br>
			</td>
		</tr>
	</table>
	<table style='width: 100%; margin-top: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-bottom-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-bottom-middle'>&nbsp;</td>
			<td class='gray-bottom-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
</div>
</td>
</tr>
</table>
		<table style='width: 100%;' cellpadding='0' cellspacing='0'>
			<tr>
				<td style='width: 1%;'><img src='images/left-blue.png'></td>
				<td style='width: 98%;' valign='top'>
					<div style='float: left; width: 100%; background-color: #6D84B4; height: 20px;'>&nbsp;</div>
				</td>
				<td style='width: 1%;'><img src='images/right-blue.png'></td>
			</tr>
		</table>

		</div>
		</form>
	</body>
</html>
