<table style='width: 100%;'>
	<tr>
		<td><span style='color: #333; font-weight: bold; font-size: 13px;'>Whitelist for <span id='disp_user'>{$username}</span></span></td>
	</tr>
</table>
<table style='width: 100%; padding-left: 20px;' cellspacing='0'>
{ section name=ent loop=$entries }
	<tr style='background-color: #fff;'>
		<td style='width: 1%;'>
			<img src='images/delete.png' class='hyperlink' onClick='do_delete_whitelist_entry("{$entries[ent].id}")' alt='Delete entry from whitelist' title='Delete entry from whitelist'>
		</td>
		<td>
			<div style='padding-left: 20px;'>
				{$entries[ent].entry}
			</div>
		</td>
	</tr>
{ /section }
</table>
