<input type='hidden' id='severity' name='severity' value='{$severity}'>
<input type='hidden' id='profile_id' name='profile_id' value='{$profile_id}'>
<input type='hidden' id='username' name='username' value='{$username}'>

<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;'>
	<tr>
		<td>
			Graph the severity
			&nbsp;&nbsp;&nbsp;
			<select class='input_select' onChange='set_severity(this.options[this.selectedIndex].value)'>
				<option value='all'>all
				{ section name=sev loop=$severities }
				<option value='{$severities[sev].val}'>{$severities[sev].name}
				{ /section }
			</select>
			&nbsp;&nbsp;&nbsp;
			limit the graph by the profile
			&nbsp;&nbsp;&nbsp;
			<select class='input_select' onChange='set_profile_id(this.options[this.selectedIndex].value);'>
				<option value='all'>all
				{ section name=pst loop=$profile_list }
				<option value='{$profile_list[pst].id}'>{$profile_list[pst].name}
				{ /section }
			</select>
			&nbsp;&nbsp;&nbsp;
			or the user
			&nbsp;&nbsp;&nbsp;
			<select class='input_select' onChange='set_username(this.options[this.selectedIndex].value);'>
				<option value='all'>all
				{ section name=usr loop=$usernames }
				<option value='{$usernames[usr].username}'>{$usernames[usr].username}
				{ /section }
			</select>
		</td>
	</tr>
</table>
<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;' align='center'>
	<tr>
		<td>
			<div style="padding-top: 10px; padding-bottom: 15px; text-align: center;">
				<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
			</div>
			<br>
		</td>
	</tr>
</table>
<table style='width: 100%; border-right: 1px solid #ccc; border-left: 1px solid #ccc;' align='center'>
	<tr>
		<td align='center'>
			<img id='the_graph' src='async/admin_metrics.php?action=view_metric&metric_id={$metric_id}&severity={$severity}&profile_id={$profile_id}&username={$username}&begin={$begin}&end={$end}'>
		</td>
	</tr>
</table>
{literal}
<script type='text/javascript'>
	set_severity = function(severity) {
		$('severity').value = severity;

		update_img_src();
	}

	set_profile_id = function(profile_id) {
		$('profile_id').value = profile_id;

		update_img_src();
	}

	set_username = function(username) {
		$('username').value = username;

		update_img_src();
	}
	
	update_img_src = function() {
		var metric_id 	= $F('metric_id');
		var severity	= $F('severity');
		var profile_id	= $F('profile_id');
		var username	= $F('username');

		var begin	= $F('calendar_bt');
		var end		= $F('calendar_et');

		var new_url 	= 'async/admin_metrics.php?action=view_metric';
		new_url 	= new_url + '&metric_id='+metric_id;
		new_url		= new_url + '&severity='+severity;
		new_url		= new_url + '&profile_id='+profile_id;
		new_url		= new_url + '&username='+username;
		new_url		= new_url + '&begin='+begin;
		new_url		= new_url + '&end='+end;

		$('the_graph').src = new_url;
	}
</script>
{/literal}
