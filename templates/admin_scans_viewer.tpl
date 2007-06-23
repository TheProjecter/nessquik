{ if $type == "user" }
	<div style='width: 100%; text-align: center;'>
		<input type='hidden' id='refine_scan_type' value='user'>
		<input type='text' id='refine_scan' class='input_txt' style='width: 80%; color: #999; text-align: center;' value='refine the list of scans by entering a username' onClick='first_clear("refine_scan");' onKeyUp='refine_scans_list("user");' onBlur='change_back("refine_scan");'>
		<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
			<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
		</div>
	</div>
	<div id='scans_container'></div>
{ elseif $type == "group" }
	<div style='width: 100%; text-align: center;'>
		<input type='hidden' id='refine_scan_type' value='user'>
		<input type='text' id='refine_scan' class='input_txt' style='width: 80%; color: #999; text-align: center;' value='refine the list of scans by entering a group' onClick='first_clear("refine_scan");' onKeyUp='refine_scans_list("group");' onBlur='change_back("refine_scan");'>
		<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
			<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
		</div>
	</div>
	<div id='scans_container'></div>
{ else }
	<center>Unknown view type specified</center>
{ /if }
