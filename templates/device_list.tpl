{ if $device_type == "registered" }
	<table style='width: 100%;'>
	{ section name=dev loop=$devices }
		<tr>
			<td style='width: 10%;'>
	<div style='cursor: pointer; width: 100%; text-align: center;'>
		<img src='images/add.png' onClick='add_device("registered","{$devices[dev].host}","{$devices[dev].host}");' alt='Add {$devices[dev].host} to the list' title='Add {$devices[dev].host} to the list'>
	</div>
			</td>
			<td style='text-align: left; width: 45%;'>
				{$devices[dev].ip}
			</td>
			<td style='text-align: left; width: 45%;'>
				{$devices[dev].host}
			</td>
		</tr>
	{ /section }
	</table>
{ elseif $device_type == "whitelist" }
	<table style='width: 100%;'>
	{ section name=dev loop=$devices }
		<tr>
			<td style='width: 10%;'>
	<div style='cursor: pointer; width: 100%; text-align: center;'>
		<img src='images/add.png' onClick='add_device("whitelist", "{$devices[dev].entry}", "{$devices[dev].entry}");' alt='Add {$devices[dev].entry} to the list' title='Add {$devices[dev].entry} to the list'>
	</div>
			</td>
			<td style='width: 90%;'>
				{$devices[dev].entry}
			</td>
		</tr>
	{ /section }
	</table>
{ elseif $device_type == "cluster" }
	<table style='width: 100%;'>
	{ section name=dev loop=$devices }
		<tr>
			<td style='width: 10%;'>
	<div style='cursor: pointer; width: 100%; text-align: center;'>
		<img src='images/add.png' onClick='add_device("cluster", "{$devices[dev].id}", "{$devices[dev].name}");' alt='Add {$devices[dev].name} to the list' title='Add {$devices[dev].name} to the list'>
	</div>
			</td>
			<td style='width: 90%;'>
				{$devices[dev].name}
			</td>
		</tr>
	{ /section }
	</table>
{ elseif $device_type == "saved" }
	<table style='width: 100%;'>
	{ section name=dev loop=$devices }
		<tr>
			<td style='width: 10%;'>
			{ if $status == 'R' }
				<span style='color: #7f7f7f; font-style: italics;'>$entry (Currently running)</span>
			{ elseif $status == 'P' }
				<span style='color: #7f7f7f; font-style: italics;'>$entry (Currently scheduled to be run)</span>
			{ else }
	<div style='cursor: pointer; width: 100%; text-align: center;'>
		<img src='images/add.png' onClick='add_device("saved", "{$devices[dev].id}", "{$devices[dev].name}");' alt='Add {$devices[dev].name} to the list' title='Add {$devices[dev].name} to the list'>
			{ /if }
	</div>
			</td>
			<td style='width: 90%;'>
				{$devices[dev].name}
			</td>
		</tr>
	{ /section }
	</table>
{ /if }
