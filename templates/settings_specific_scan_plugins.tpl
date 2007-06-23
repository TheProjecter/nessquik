{ section name=plu loop=$plugins }
	<div id='file{$plugins[plu].id}Div'>

	{ if $plugins[plu].type == "plu" }
		<input type='hidden' name='item[{$plugins[plu].id}]' id='item{$plugins[plu].id}' value='p:{$plugins[plu].val}'>
	{ elseif $plugins[plu].type == "fam" }
		<input type='hidden' name='item[{$plugins[plu].id}]' id='item{$plugins[plu].id}' value='f:{$plugins[plu].val}'>
	{ elseif $plugins[plu].type == "sev" }
		<input type='hidden' name='item[{$plugins[plu].id}]' id='item{$plugins[plu].id}' value='s:{$plugins[plu].val}'>
	{ elseif $plugins[plu].type == "all" }
		<input type='hidden' name='item[{$plugins[plu].id}]' id='item{$plugins[plu].id}' value='a:{$plugins[plu].val}'>
	{ elseif $plugins[plu].type == "spe" }
		<input type='hidden' name='item[{$plugins[plu].id}]' id='item{$plugins[plu].id}' value='sp:{$plugins[plu].val}'>
	{ /if }

	<table width='100%'><tr><td style='width: 10%; text-align: center;'>
		<span style='cursor: pointer;'>
			<img src='images/delete.png' onClick="removeEvent('file{$plugins[plu].id}Div');">
		</span>

	{ if $plugins[plu].type == "plu" }
		</td><td width='90%' style='text-align: left;'><div class='plugs'>{$plugins[plu].disp}</div></td></tr></table>
	{ elseif $plugins[plu].type == "fam" }
		</td><td width='90%' style='text-align: left;'><div class='fams'>{$plugins[plu].disp}</div></td></tr></table>
	{ elseif $plugins[plu].type == "sev" }
		</td><td width='90%' style='text-align: left;'><div class='sevs'>{$plugins[plu].disp}</div></td></tr></table>
        { elseif $plugins[plu].type == "all" }
		</td><td width='90%' style='text-align: left;'><div class='plugs'>All Plugins Available: all plugins</div></td></tr></table>
	{ elseif $plugins[plu].type == "spe" }
		</td><td width='90%' style='text-align: left;'><div class='specs'>Special Plugin: {$plugins[plu].disp}</div></td></tr></table>
        { /if }

	</div>
{ /section }
