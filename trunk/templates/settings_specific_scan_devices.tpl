{ section name=dev loop=$devices }
	<div id='device{$devices[dev].count}Div'>
	<input type='hidden' name='dev[{$devices[dev].id}]' id='dev{$devices[dev].id}' value='{$devices[dev].val}'>
	<table width='100%'><tr><td style='width: 10%; text-align: center;'>
		<span style='cursor: pointer;'>
			<img src='images/delete.png' onClick="remove_device('device{$devices[dev].count}Div');">
		</span>

	{ if $devices[dev].type == "cluster" }
		</td><td width='90%' style='text-align: left;'><div class='clusters'>Cluster: {$devices[dev].device}</div></td></tr></table>
	{ elseif $devices[dev].type == "registered" }
		</td><td width='90%' style='text-align: left;'><div class='registered'>Registered: {$devices[dev].device}</div></td></tr></table>
	{ elseif $devices[dev].type == "whitelist" }
		</td><td width='90%' style='text-align: left;'><div class='white'>Whitelist: {$devices[dev].device}</div></td></tr></table>
        { elseif $devices[dev].type == "vhost" }
		</td><td width='90%' style='text-align: left;'><div class='vhost'>Virtual Host: {$devices[dev].device}</div></td></tr></table>
	{ elseif $devices[dev].type == "general" }
		</td><td width='90%' style='text-align: left;'><div class='vhost'>General: {$devices[dev].device}</div></td></tr></table>
	{ /if }
	</div>
{ /section }
