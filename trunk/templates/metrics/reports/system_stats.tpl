<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
	<tr>
		<td>
<div style='padding-left: 10px; padding-right: 10px;'>
	<div style='color: #4D69A2; font-weight: bold;'>
		Plugins Summary
	</div>
	<table style='width: 100%;'>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				The total number of plugins in the database
			</td>
			<td style='vertical-align: top; width: 30%;'>
				<span style='font-weight: bold;'>{$total_plugins}</span> plugins
			</td>
		</tr>
	</table>
	<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
		<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
	</div>
	<div style='color: #4D69A2; font-weight: bold;'>
		Scans Summary
	</div>
	<table style='width: 100%;'>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				The total number of scans not ready to run
			</td>
			<td style='vertical-align: top; width: 30%;'>
				{ if $not_ready_scans == 1 }
				<span style='font-weight: bold;'>{$not_ready_scans}</span> scan
				{ else }
				<span style='font-weight: bold;'>{$not_ready_scans}</span> scans
				{ /if }
			</td>
		</tr>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				The total number of pending scans
			</td>
			<td style='vertical-align: top; width: 30%;'>
				{ if $pending_scans == 1 }
				<span style='font-weight: bold;'>{$pending_scans}</span> scan
				{ else }
				<span style='font-weight: bold;'>{$pending_scans}</span> scans
				{ /if }
			</td>
		</tr>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				The total number of running scans
			</td>
			<td style='vertical-align: top; width: 30%;'>
				{ if $running_scans == 1 }
				<span style='font-weight: bold;'>{$running_scans}</span> scan
				{ else }
				<span style='font-weight: bold;'>{$running_scans}</span> scans
				{ /if }
			</td>
		</tr>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				The total number of finished scans
			</td>
			<td style='vertical-align: top; width: 30%;'>
				{ if $finished_scans == 1 }
				<span style='font-weight: bold;'>{$finished_scans}</span> scan
				{ else }
				<span style='font-weight: bold;'>{$finished_scans}</span> scans
				{ /if }
			</td>
		</tr>
	</table>
	<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
		<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
	</div>
	<div style='color: #4D69A2; font-weight: bold;'>
		User Summary
	</div>
	<table style='width: 100%;'>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				The system has scheduled scans from this many users
			</td>
			<td style='vertical-align: top; width: 30%;'>
				{ if $counted_users == 1 }
				<span style='font-weight: bold;'>{$counted_users}</span> user
				{ else }
				<span style='font-weight: bold;'>{$counted_users}</span> users
				{ /if }
			</td>
		</tr>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				<span style='font-weight: bold;'>{$top_user_name}</span> is the most active user. 
				They have scheduled <span style='font-weight: bold;'>{$top_user_count}</span>
				{ if $top_user_count == 1 }
					scan
				{ else }
					scans
				{ /if }
			</td>
		</tr>
		<tr>
			<td style='vertical-align: top; width: 70%;'>
				{ if $bottom_user_name == "none" }
					There is currently no user who would be considered "least active"
				{ else }
					<span style='font-weight: bold;'>{$bottom_user_name}</span> is the least active user. 
					They have scheduled <span style='font-weight: bold;'>{$bottom_user_count}</span>
					{ if $bottom_user_count == 1 }
						scan
					{ else }
						scans
					{ /if }
				{ /if }
			</td>
		</tr>
	</table>
</div>
		</td>
	</tr>
</table>
