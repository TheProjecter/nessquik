<div>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<!--
	<div id='account_choices_container' style='width: 90%; margin-top: 10px; overflow: hidden;'>
		<span style='font-weight: bold;'>
			Accounts
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_add_account();'>
				add
			</div>
			<div class='sidechoice padded' onClick='show_accounts();'>
				change
			</div>
		</div>
	</div>
	-->
	<div id='group_choices_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Groups
		</span>
		<div class='bullet_list' id='scan_choices'>
			<div class='sidechoice padded' onClick='show_add_group();'>
				add
			</div>
			<div class='sidechoice padded' onClick='show_groups();'>
				change
			</div>
		</div>
	</div>
</td>
<td style='width: 80%; vertical-align: top;'>
<div style='height: 100%; vertical-align: top;'>
	<table style='width: 100%; margin-bottom: -8px;' cellpadding='0' cellspacing='0'>
		<tr>
			<td class='gray-top-left'><div style='width: 15px;'>&nbsp;</div></td>
			<td class='gray-top-middle'>&nbsp;</td>
			<td class='gray-top-right'><div style='width: 20px;'>&nbsp;</div></td>
		</tr>
	</table>
	<div id='workbox'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: top;'>
				<div id='work_container2'></div>
			</td>
		</tr>
	</table>
	</div>

	<div id='welcome_box'>
	<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
		<tr>
			<td style='width: 100%; vertical-align: top;' align='center'>
				<div id='scans_welcome' style='padding-left: 10px; text-align: left; width: 60%; height: 320px;'>
					<table style='width: 100%; height: 100%; vertical-align: middle;'>
						<tr>
							<td>
					<!--
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left you can manipulate accounts and groups
					</div>
					<ul>
						<li style='padding-bottom: 10px;'>add new accounts so that people can use nessquik
						<li style='padding-bottom: 10px;'>groups can be used to specify who can use which scanners
						<li style='padding-bottom: 10px;'>change account or group settings as needed
					</ul>
					-->
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left you can manipulate groups
					</div>
					<ul>
						<li style='padding-bottom: 10px;'>add new groups so that plugin profiles can be made
						<li style='padding-bottom: 10px;'>groups can be used to specify who can use which scanners
						<li style='padding-bottom: 10px;'>change group settings as needed
					</ul>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
	</div>

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
</div>

<script type='text/javascript'>
	Element.hide('workbox');
</script>
