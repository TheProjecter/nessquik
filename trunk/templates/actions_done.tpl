<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <title>nessquik</title>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
                <meta name="copyright" content="&copy; GPL">
                <meta name="Author" content="Tim Rupp">
		{if $KCA_EXPIRED}
			<link rel='stylesheet' type='text/css' href='../../templates/styles/styles.css'>
		{ else }
			<link rel='stylesheet' type='text/css' href='templates/styles/styles.css'>
		{ /if }
        </head>
        <body>
		<div style='margin-left: auto; margin-right: auto; width: 90%; text-align: center;'>
		<table style='width: 100%;' cellpadding='0' cellspacing='0'>
			<tr>
				{if $KCA_EXPIRED}
					<td style='width: 1%;'><img src='../../images/left-blue.png' alt=''></td>
				{ else }
					<td style='width: 1%;'><img src='images/left-blue.png' alt=''></td>
				{ /if }
				<td style='width: 98%;' valign='top'>
					<div style='float: left; width: 100%; background-color: #6D84B4; height: 20px;'>&nbsp;</div>
				</td>
				{if $KCA_EXPIRED}
					<td style='width: 1%;'><img src='../../images/right-blue.png' alt=''></td>
				{ else }
					<td style='width: 1%;'><img src='images/right-blue.png' alt=''></td>
				{ /if }
			</tr>
		</table>

		{ if $SUCCESS == "noper" }
			<div style='display: none;'>&nbsp;</div>
		{ elseif $SUCCESS}
			<div id='header'><center>Your Scan Has Been Scheduled!</center></div><p />
		{ else }
			<div id='header'><center>Your Scan Has <b>Not</b> Been Scheduled!</center></div><p />
		{ /if }



<div style='height: 100%; margin: 10px 0px 10px 0px; border: 1px solid #fff; vertical-align: top;'>
	<table style='width: 100%; margin-bottom: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			{if $KCA_EXPIRED}
			<td style='width: 1%; background-image: url("../../images/gray-line-tl.png"); background-repeat: no-repeat;'>
				<div style='width: 15px;'>&nbsp;</div>
			</td>
			<td style='width: 100%; background-image: url("../../images/gray-middle-top.png"); background-repeat: repeat-x;'>&nbsp;</td>
			<td style='width: 1%; background-image: url("../../images/gray-line-tr.png"); background-repeat: no-repeat;'>
				<div style='width: 20px;'>&nbsp;</div>
			</td>
			{ else }
			<td class='gray-top-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-top-middle'>&nbsp;</td>
			<td class='gray-top-right'><div style='width: 20px;'>&nbsp;</div></td>
			{ /if }
		</tr>
	</table>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: top; text-align: center;'>
				{ if $MESSAGE }
					<div>
						{$MESSAGE}
					</div>
				{ /if }
				{ if $RETURN_LINK }
				<div>
					<center>
						{$RETURN_LINK}
					</center>
				</div>
				{ /if }
			</td>
		</tr>
	</table>
	<table style='width: 100%; margin-top: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			{if $KCA_EXPIRED}
			<td style='width: 1%; background-image: url("../../images/gray-line-bl.png"); background-repeat: no-repeat;'>
				<div style='width: 15px;'>&nbsp;</div>
			</td>
			<td style='width: 100%; background-image: url("../../images/gray-middle-bottom.png"); background-repeat: repeat-x;'>&nbsp;</td>
			<td style='width: 1%; background-image: url("../../images/gray-line-br.png"); background-repeat: no-repeat;'>
				<div style='width: 20px;'>&nbsp;</div>
			</td>
			{ else }
			<td class='gray-bottom-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-bottom-middle'>&nbsp;</td>
			<td class='gray-bottom-right'><div style='width: 20px;'>&nbsp;</div></td>
			{ /if }
		</tr>
	</table>
</div>
		<table style='width: 100%;' cellpadding='0' cellspacing='0'>
			<tr>
				{if $KCA_EXPIRED}
				<td style='width: 1%;'><img src='../../images/left-blue.png' alt=''></td>
				{ else }
				<td style='width: 1%;'><img src='images/left-blue.png' alt=''></td>
				{ /if }
				<td style='width: 98%;' valign='top'>
					<div style='float: left; width: 100%; background-color: #6D84B4; height: 20px;'>&nbsp;</div>
				</td>
				{ if $KCA_EXPIRED }
				<td style='width: 1%;'><img src='../../images/right-blue.png' alt=''></td>
				{ else }
				<td style='width: 1%;'><img src='images/right-blue.png' alt=''></td>
				{ /if }
			</tr>
		</table>

		</div>
	</body>
</html>
