<div id='help' style='background-color: #fff;'>
<input type='hidden' id='current_content_type' value=''>
<table width='100%'>
<tr>
<td style='width: 20%; vertical-align: top;'>
	<div style='width: 90%; margin-top: 10px; overflow: hidden;'>
		<span style='font-weight: bold;'>
			Help Categories
		</span>
		<div id='help_categories' class='bullet_list'></div>
	</div>
	<div id='help_modify_container' style='width: 90%; margin-top: 10px;'>
		<span style='font-weight: bold;'>
			Content
		</span>
		<div class='bullet_list'>
			<div class='sidechoice padded' onClick='show_add_help_content();'>
				add
			</div>
			<div class='sidechoice padded' onClick='show_change_help_content("A");'>
				change admin
			</div>
			<div class='sidechoice padded' onClick='show_change_help_content("G");'>
				change general
			</div>
		</div>
	</div>
</td>
<td style='width: 80%; vertical-align: top;'>
<div style='height: 100%; margin: 10px 0px 0px 0px; border: 1px solid #fff; vertical-align: top;'>
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
					<div style='color: #4D69A2; font-weight: bold; text-align: center;'>
						to the left are help categories
					</div>
					<ul>
						<li style='padding-bottom: 10px;'>help topics are available for common admin questions
						<li style='padding-bottom: 10px;'>new help topics and categories can be added as needed
						<li style='padding-bottom: 10px;'>topics from either the admin view or general view can be changed
						<li style='padding-bottom: 10px;'>removing topics and categories is also possible
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
<br>

<script type='text/javascript'>
	show_help_categories();

	Element.hide('workbox');
</script>
