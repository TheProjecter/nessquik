<table style='width: 100%;'>
	<tr>
		<td style='width: 5%; text-align: center; vertical-align: top;'>
			<img src='images/delete.png' style='cursor: pointer;' onClick='do_delete_help_category("{$category_id}");'>
		</td>
		<td style='width: 95%;'>
			<span style='color: #4D69A2; font-weight: bold;'>{$category_name}</span>
		</td>
	</tr>
</table>
<table style='width: 100%;'>
{ section name=cnt loop=$content }
	<tr>
		<td style='width: 5%; text-align: center; vertical-align: top;'>
			<img src='images/delete.png' style='cursor: pointer;' onClick='do_delete_help_topic("{$content[cnt].help_id}");'>
		</td>
		<td style='width: 5%;'>&nbsp;</td>
		<td style='width: 90%;'>
			<span class='surflinks' style='padding: 0px;' onClick='edit_specific_help_topic("{$content[cnt].help_id}");'>{$content[cnt].question}</span>
		</td>
	</tr>
{ /section }
</table>
<br>
