<div id='settings_chooser' style='padding-left: 10px; padding-right: 10px;'>
	<div style='color: #4D69A2; font-weight: bold;'>
		Report settings
	</div>
	<div style='text-align: center;'>
		<table style='width: 80%;'>
			<tr>
				<td style='width: 30%;'>
					<span style='font-weight: bold;'>title</span>
				</td>
				<td style='width: 70%;' colspan='2'>
					<input type='text' style='width: 100%;' name='report_title' class='input_txt'>
				</td>
			</tr>
			<tr>
				<td style='width: 30%;'>
					<span style='font-weight: bold;'>type</span>
				</td>
				<td style='width: 35%;'>
					<input type='radio' name='report_type' onClick='show_report_type("specific");' checked='checked'>specific items
				</td>
				<td style='width: 35%;'>
					<input type='radio' name='report_type' onClick='show_report_type("list");'>a list of items
				</td>
			</tr>
		</table>
	</div>
	<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
		<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
	</div>
	<div style='color: #4D69A2; font-weight: bold;'>
		What to include in the report
	</div>
	<br><br>
	<div id='report_display_area' style='text-align: center;'></div>
	<div style="text-align: center;">
		<input type='button' value='View Report' class='input_btn'>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type='button' value='Save Report' class='input_btn'>
	</div>
</div>
