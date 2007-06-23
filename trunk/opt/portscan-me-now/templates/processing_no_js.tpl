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

			<center><h2>{$hdrtext}</h2></center>

			<center>
			Your IP address is: <b>{$client_ip}</b>
			<p>
			This scanning can take a long or short time to complete depending on how your host is configured.<br>
			The results will be displayed below when the scan finishes.<br>
			If you run a personal firewall, the results may be incomplete.<br> Note that this scan only scans the IP
			address of your client, thus if you are attempting to scan a web VHOST, this scan will only find ports
			present in the default web service.
			</p>
			<p>
			Consult the <a href='portscanmenowfaq.html' target='_blank'><i>PortScanMeNow</i> FAQ</a> if you encounter
			problems or have questions interpreting the results
			</p>
			</center>

			<center><h3>DO NOT REFRESH THIS PAGE</h3></center><br>

			<div id='processing_steps'>
				<center>
					<div class='percentbox' style='z-index: 0; float: center;'>Starting the scan - Please Wait</div>
				</center>
