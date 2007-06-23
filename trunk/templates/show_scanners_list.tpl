<table width='100%' cellspacing='0'>
{ section name=scn loop=$scanners }
	<tr>
		<td style='width: 10%; text-align: center; vertical-align: top;'>
			<img src='images/delete.png' class='hyperlink' onClick='do_delete_specific_scanner("{$scanners[scn].id}")' alt='Delete scanner from group' title='Delete scanner from group'>
		</td>
		<td style='width: 90%; text-align: left;'>
			<div>
				<span style='color: #000;'>{$scanners[scn].name}</span>
			</div>
		</td>
	</tr>
{ /section }
</table>
