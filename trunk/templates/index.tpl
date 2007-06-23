<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <title>nessquik</title>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
                <meta name="copyright" content="&copy;">
                <meta name="Author" content="Tim Rupp">
                <link rel='stylesheet' type='text/css' href='templates/styles/styles.css'>
		{ if $page == "scans" }
                	<link rel='stylesheet' type='text/css' href='templates/styles/scans.css'>
		{ elseif $page == "settings" }
                	<link rel='stylesheet' type='text/css' href='templates/styles/settings.css'>
		{ /if }

                <!-- Includes general javascript that can be displayed on all pages -->
                <script src="templates/js/prototype.js" type="text/javascript"></script>
                <script src="templates/js/scriptaculous.js" type="text/javascript"></script>
                <script src="templates/js/header.js" type="text/javascript"></script>

		{ if $page == "help" }
                <script src="templates/js/help.js" type="text/javascript"></script>
		{ /if }

		{ if $page == "settings" || $page == "create" }
                <script src="templates/js/settings.js" type="text/javascript"></script>
               	<script src="templates/js/scans.js" type="text/javascript"></script>
		{ /if }

		{ if $page == "scans" }
               	<script src="templates/js/scans.js" type="text/javascript"></script>
                <script src="templates/js/dropmenu.js" type="text/javascript"></script>
                <script src="templates/js/scanning_overview.js" type="text/javascript"></script>
		{ /if }

        </head>
        <body>
		{ if !$check_nessus }
			<div style='position: absolute; width: 20%; background-color: red; font-weight: bold; color: #fff; text-align: center; left: 40%;'>
				Nessus is not running!
			</div>
		{ /if }
		{ if !$check_secure }
			{ if $_RELEASE == "fermi" }
			<div style='position: absolute; width: 20%; background-color: red; font-weight: bold; color: #fff; text-align: center; left: 40%;'>
				Your server is not accepting KCA certificates!
			</div>
			{ else }
			<div style='position: absolute; width: 40%; background-color: red; font-weight: bold; color: #fff; text-align: center; left: 30%;'>
				You're not running nessquik over HTTPS. Please correct this, or disable HTTPS checks
			</div>
			{ /if }
		{ /if }
		<form name='nessus_reg' id='nessus_reg' action='async/process.php' method='post'>
			<div style='display: none;'>
				<input type="hidden" value="0" id="theValue" />
				<input type='hidden' name='scan_type' value='' id='scan_type'>
				<input type='hidden' id='action' name='action' value=''>
				<input type='hidden' id='page_spot' name='page_spot' value='{$the_page}'>
				<input type='hidden' id='page' name='page' value='{$page}'>
				<input type='hidden' id='username'>
			</div>

		<div style='margin-left: auto; margin-right: auto; width: 90%; text-align: left;'>
		{ if $page == "create" }
			{ if $admin }
				<div id='header'>Hello <a href='admin.php'>{$proper}</a>, what would you like to scan?</div>
			{ else }
				<div id='header'>Hello {$proper}, what would you like to scan?</div>
			{ /if }
		{ elseif $page == "settings" }
			{ if $admin }
				<div id='header'>Hi <a href='admin.php'>{$proper}</a>, you can configure your scans here.</div>
			{ else }
				<div id='header'>Hi {$proper}, you can configure your scans here.</div>
			{ /if }
		{ elseif $page == "scans" }
			{ if $admin }
				<div id='header'>Hey <a href='admin.php'>{$proper}</a>, here's a list of all your scans</div>
			{ else }
				<div id='header'>Hey {$proper}, here's a list of all your scans</div>
			{ /if }
		{ elseif $page == "help" }
			{ if $admin }
				<div id='header'>Need help <a href='admin.php'>{$proper}</a>? No problem.</div>
			{ else }
				<div id='header'>Need help {$proper}? No problem.</div>
			{ /if }
		{ /if }
			<div id='main_click'>
				<a href='index.php' class='surflinks padded'>create</a>
				<a href='index.php?page=settings' class='surflinks padded'>settings</a>
				<a href='index.php?page=scans' class='surflinks padded'>scans</a>
				<a href='index.php?page=help' class='surflinks padded'>help</a>
			</div>
		<div style='clear: left; text-align: right;'>&nbsp;</div>

		<div style='text-align: left;'>

		<table style='width: 100%;' cellpadding='0' cellspacing='0'>
			<tr>
				<td style='width: 1%;'><img src='images/left-blue.png' alt=''></td>
				<td style='width: 98%;' valign='top'>
					<div style='float: left; width: 100%; background-color: #6D84B4; height: 20px;'>&nbsp;</div>
				</td>
				<td style='width: 1%;'><img src='images/right-blue.png' alt=''></td>
			</tr>
		</table>

		{ if $page == "create" }
			{ include file='create.tpl' }
		{ elseif $page == "settings" }
			{ include file='settings.tpl' }
		{ elseif $page == "scans" }
			{ include file='scans.tpl' }
		{ elseif $page == "help" }
			{ include file='help.tpl' }
		{ /if}

		<table style='width: 100%;' cellpadding='0' cellspacing='0'>
			<tr>
				<td style='width: 1%;'><img src='images/left-blue.png' alt=''></td>
				<td style='width: 98%;' valign='top'>
					<div style='float: left; width: 100%; background-color: #6D84B4; height: 20px;'>&nbsp;</div>
				</td>
				<td style='width: 1%;'><img src='images/right-blue.png' alt=''></td>
			</tr>
		</table>

		</div>
		</div>
		</form>
	</body>
</html>
