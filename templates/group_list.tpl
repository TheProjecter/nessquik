{ if $group_action == "add" }
<table style='width: 100%;'>
{ section name=grp loop=$groups }
	<tr>
		<td style='width: 10%;'>
			<div style='cursor: pointer; width: 100%; text-align: center;'>
				<img src='images/add.png' onClick='add_group("{$groups[grp].id}","{$groups[grp].name}");' alt='Add {$groups[grp].name} to the list' title='Add {$groups[grp].name} to the list'>
			</div>
		</td>
		<td style='text-align: left; width: 90%;'>
			{$groups[grp].name}
		</td>
	</tr>
{ /section }
</table>
{ elseif $group_action == "remove" }
{ section name=grp loop=$groups }
	<div id='group{$groups[grp].count}Div'>
		<table style='width: 100%;'>
		<tr>
			<td style='width: 10%;'>
				<input type='hidden' name='groups[{$groups[grp].count}]' value='{$groups[grp].id}'>
				<div style='cursor: pointer; width: 100%; text-align: center;'>
					<img src='images/delete.png' onClick='remove_group("group{$groups[grp].count}Div");' alt='Delete {$groups[grp].name} from the list' title='Delete {$groups[grp].name} from the list'>
				</div>
			</td>
			<td style='text-align: left; width: 90%;'>
				<div class='groups'>
					{$groups[grp].name}
				</div>
			</td>
		</tr>
		</table>
	</div>
{ /section }
{ /if }
