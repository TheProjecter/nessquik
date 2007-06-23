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

			<div id='processing_steps'>
				<center>
					<div class='percentbox' style='z-index: 0; float: center;'>Getting Plugins - Please Wait</div>
				</center>
