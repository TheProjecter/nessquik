<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <title>nessquik administration</title>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
                <meta name="copyright" content="&copy;">
                <meta name="Author" content="Tim Rupp">
                <link rel='stylesheet' type='text/css' href='templates/styles/styles.css'>

                <!-- Includes general javascript that can be displayed on all pages -->
		{ if $page == "help" }
		<!--
		<script language="javascript" type="text/javascript" src="templates/tinymce/tiny_mce.js"></script>
		-->
		{ /if }

                <script src="templates/js/prototype.js" type="text/javascript"></script>
                <script src="templates/js/scriptaculous.js" type="text/javascript"></script>
                <script src="templates/js/header.js" type="text/javascript"></script>
                <script src="templates/js/calendar.js" type="text/javascript"></script>

		{ if $page == "scans" }
                <script src="templates/js/admin_scans.js" type="text/javascript"></script>
                <script src="templates/js/scanning_overview.js" type="text/javascript"></script>
                <script src="templates/js/dropmenu.js" type="text/javascript"></script>
		{ /if }

		{ if $page == "metrics" }
                <script src="templates/js/admin_metrics.js" type="text/javascript"></script>
		{ /if }

		{ if $page == "accounts" }
                <script src="templates/js/admin_accounts.js" type="text/javascript"></script>
		{ /if }

		{ if $page == "wlist" }
                <script src="templates/js/whitelist.js" type="text/javascript"></script>
		{ /if }

		{ if $page == "settings" }
                <script src="templates/js/admin_settings.js" type="text/javascript"></script>
		{ /if }

		{ if $page == "help" }
                <script src="templates/js/admin_help.js" type="text/javascript"></script>
		{ /if }
        </head>
        <body>
		{ if !$check_nessus }
			<div style='position: absolute; width: 20%; background-color: red; font-weight: bold; color: #fff; text-align: center; left: 40%;'>
				Nessus is not running!
			</div>
		{ /if }
		{ if !$check_secure && $_RELEASE == "fermi" }
			<div style='position: absolute; width: 20%; background-color: red; font-weight: bold; color: #fff; text-align: center; left: 40%;'>
				Your server is not accepting KCA certificates!
			</div>
		{ /if }
		<form name='nessus_reg' id='nessus_reg' action=''>
			<input type='hidden' id='cur_user_id' name='cur_user_id' value='0'>
			<input type='hidden' id='action' name='action' value=''>

		<div style='margin-left: auto; margin-right: auto; width: 90%; text-align: left;'>
		{ if $page == "admin" }
			<div id='header'>Administer nessquik stuff here</div>
		{ elseif $page == "accounts" }
			<div id='header'>Change account and group settings</div>
		{ elseif $page == "wlist" }
			<div id='header'>Whitelist editor</div>
		{ elseif $page == "scans" }
			<div id='header'>From here you can view scans run by others</div>
		{ elseif $page == "help" }
			<div id='header'>Admin help is provided too</div>
		{ elseif $page == "settings" }
			<div id='header'>Control panel for nessquik</div>
		{ elseif $page == "metrics" }
			<div id='header'>Scan metrics and reports</div>
		{ /if }
			<div id='main_click'>
				<a href='index.php' class='surflinks padded'>home</a>
				<a href='admin.php?page=admin' class='surflinks padded'>admin</a>
				{ if $_RELEASE == "fermi" }
				<a href='admin.php?page=wlist' class='surflinks padded'>whitelist</a>
				{ /if }
				<a href='admin.php?page=settings' class='surflinks padded'>settings</a>
				<!--<a href='admin.php?page=accounts' class='surflinks padded'>accounts</a>-->
				{ if $_RELEASE == "fermi" }
				<a href='admin.php?page=metrics' class='surflinks padded'>metrics</a>
				{ /if }
				<a href='admin.php?page=scans' class='surflinks padded'>scans</a>
				<a href='admin.php?page=help' class='surflinks padded'>help</a>
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

		{ if $page == "admin" }
			{ include file='admin_main.tpl' }
		{ elseif $page == "accounts" }
			{ include file='admin_accounts.tpl' }
		{ elseif $page == "scans" }
			{ include file='admin_scans.tpl' }
		{ elseif $page == "help" }
			{ include file='admin_help.tpl' }
		{ elseif $page == "wlist" }
			{ include file='whitelist.tpl' }
		{ elseif $page == "settings" }
			{ include file='admin_settings.tpl' }
		{ elseif $page == "metrics" }
			{ include file='admin_metrics.tpl' }
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
