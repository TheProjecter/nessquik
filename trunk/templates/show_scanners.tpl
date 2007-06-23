{ if $scanners }
	<table width='100%' cellspacing='0'>
		<tr>
			<td style='width: 10%;'></td>
			<td style='width: 80%; font-weight: bold;'>
				scanner name
			</td>
			<td style='width: 10%; font-weight: bold;'>
				client key
			</td>
		</tr>
	{ section name=scn loop=$scanners }
		<tr>
			<td style='width: 10%; text-align: center;'>
				<img src='images/delete.png' class='hyperlink' onClick='do_delete_scanner("{$scanners[scn].id}")' alt='Delete scanner {$scanners[scn].name}' title='Delete scanner {$scanners[scn].name}'>
			</td>
			<td>
				<span class='surflinks' style='padding: 0px;' onClick='show_edit_scanner("{$scanners[scn].id}")'>{$scanners[scn].name}</span>
			</td>
			<td>
				{$scanners[scn].key}
			</td>
		</tr>
	{ /section }
	</table>
	<br>
{ else }
<center>No scanners were found</center>
<br>
{ /if }
