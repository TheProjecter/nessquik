{ if $plugin_type == "plugin" }

	{section name=plg loop=$plugins}
	<div style='margin: 5px;'>
		<input type='hidden' id='plugid_{$plugins[plg].counter}' value='{$plugins[plg].id}'>
	
		<table style='width: 100%;'>
			<tr>
				<td style='width: 10%;'>
					<div style='cursor: pointer; text-align: center;'>
						<img src='images/add.png' onClick='addEvent("plugin", "{$plugins[plg].id}", "{$plugins[plg].shortdesc}");' alt='Add this plugin to the list' title='Add this plugin to the list'>
					</div>
				</td>
				<td style='width: 80%;'>
					<div style='text-align: left; color: blue; cursor: pointer;' onClick='toggle_desc("{$plugins[plg].id}");'>
						{$plugins[plg].shortdesc|truncate:60}
					</div>
				</td>
			</tr>
			{ if $short_plugin_listing == 0 }
			<tr>
				<td></td>
				<td>
					<table>
						<tr>
							<td style='vertical-align: top; text-align: left;'>Family:</td>
							<td style='text-align: left;'>{$plugins[plg].family}</td>
						</tr>
						<tr>
							<td style='vertical-align: top; text-align: left;'>NASL Script:</td>
							<td style='text-align: left;'>{$plugins[plg].nasl}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			{ /if }
		</table>
		<div id='invis{$plugins[plg].id}' style='display: none;'>
		<table style='width: 100%;'>
			<tr>
				<td style='width: 10%;'></td>
				<td>
					<div id='invis_data{$plugins[plg].id}' style='text-align: left; background-color: #f7f7f7; border: 1px solid #ccc;'></div>
				</td>
			</tr>
		</table>
		</div>
	</div>
	{/section}

{ elseif $plugin_type == "family" }
	{section name=plg loop=$plugins}
	<div style='margin: 5px;'>
		<input type='hidden' id='plugid_{$plugins[plg].counter}' value='{$plugins[plg].family}'>
		<table width='100%' cellspacing='0'>
			<tr>
				<td style='width: 10%; text-align: center; vertical-align: top;'>
					<div style='cursor: pointer;'>
						<img src='images/add.png' onClick='addEvent("family", "{$plugins[plg].family}", "{$plugins[plg].family}");' alt='Add {$plugins[plg].family} to the list' title='Add {$plugins[plg].family} to the list'>
					</div>
				</td>
				<td style='width: 90%; text-align: left;'>
					<div>
						<span>{$plugins[plg].family}</span>
					</div>
				</td>
			</tr>
		</table>
	</div>
	{ sectionelse }
		<div style='width: 90%; text-align: center; color: #999;'>no families of plugins were found</div>
	{/section}

{ elseif $plugin_type == "severity" }

	{section name=plg loop=$plugins}
	<div style='margin: 5px;'>
		<input type='hidden' id='plugid_{$plugins[plg].counter}' value='{$plugins[plg].severity}'>
		<table width='100%' cellspacing='0'>
			<tr>
				<td style='width: 10%; text-align: center; vertical-align: top;'>
					<div style='cursor: pointer;'>
						<img src='images/add.png' onClick='addEvent("severity", "{$plugins[plg].severity}", "{$plugins[plg].severity}");' alt='Add {$plugins[plg].severity} to the list' title='Add {$plugins[plg].severity} to the list'>
					</div>
				</td>
				<td style='width: 90%; text-align: left;'>
					<div>
						<span>{$plugins[plg].severity}</span>
					</div>
				</td>
			</tr>
		</table>
	</div>
	{ sectionelse }
		<div style='width: 90%; text-align: center; color: #999;'>no plugins with severities were found</div>
	{/section}

{ elseif $plugin_type == "special" }

	{section name=plg loop=$plugins}
	<div style='margin: 5px;'>
		<input type='hidden' id='plugid_{$plugins[plg].counter}' value='{$plugins[plg].id}'>
		<table width='100%' cellspacing='0'>
			<tr>
				<td style='width: 10%; text-align: center; vertical-align: top;'>
					<div style='cursor: pointer;'>
						<img src='images/add.png' onClick='addEvent("special", "{$plugins[plg].id}", "{$plugins[plg].name}");' alt='Add {$plugins[plg].name} to the list' title='Add {$plugins[plg].name} to the list'>
					</div>
				</td>
				<td style='width: 90%; text-align: left;'>
					<div>
						<span>{$plugins[plg].name}</span>
					</div>
				</td>
			</tr>
		</table>
	</div>
	{ sectionelse }
		<div style='width: 90%; text-align: center; color: #999;'>no special plugins were found</div>
	{/section}

{ /if }
