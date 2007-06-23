<div style='height: 100%; margin: 10px 0px 10px 0px; border: 1px solid #fff; vertical-align: top;'>
	<div id='content_new_topic'>
	<input type='hidden' name='help_id' value='{$help_id}'>
		<table style='width: 100%; padding-left: 20px;'>
			<tr>
				<td style='width: 100%; font-weight: bold; color: #4D69A2;' colspan='2'>
					Category
				</td>
			</tr>
			<tr>
				<td style='width: 100%; text-align: center;' colspan='2'>
					<select name='category_id' class='input_select' style='width: 95%;'>
						<option value=''>:: choose a category ::
						<optgroup label='Admin'>
						{ section name=actg loop=$admin_categories }
							{ if $selected_category == $admin_categories[actg].id }
							<option value='{$admin_categories[actg].id}' selected='selected'>{$admin_categories[actg].name}
							{ else }
							<option value='{$admin_categories[actg].id}'>{$admin_categories[actg].name}
							{ /if }
						{ /section }
						</optgroup>
						<optgroup label='General'>
						{ section name=gctg loop=$general_categories }
							{ if $selected_category == $general_categories[gctg].id }
							<option value='{$general_categories[gctg].id}' selected='selected'>{$general_categories[gctg].name}
							{ else }
							<option value='{$general_categories[gctg].id}'>{$general_categories[gctg].name}
							{ /if }
						{ /section }
					</select>
				</td>
			</tr>
			<tr>
				<td style='width: 100%; font-weight: bold; color: #4D69A2;' colspan='2'>
					Question
				</td>
			</tr>
			<tr>
				<td style='width: 100%; text-align: center;' colspan='2'>
					<input type='text' id='question' name='question' class='input_txt' style='width: 95%;' value='{$question}' maxlength='255'>
				</td>
			</tr>
			<tr>
				<td style='width: 100%; font-weight: bold; color: #4D69A2;' colspan='2'>
					Answer
				</td>
			</tr>
			<tr>
				<td style='width: 100%; text-align: center;' colspan='2'>
					<center><textarea id="answer" name="answer" rows="10" cols="50" class='input_txt' style="width: 95%;">{$answer}</textarea></center>
				</td>
			</tr>
		</table>
		<br>
		<table style='width: 100%; padding-left: 20px;'>
			<tr>
				<td style='width: 100%; text-align: center;'>
					<input type='button' name='add' value='Save Topic' class='input_btn' onClick='do_edit_specific_help_topic();'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type='reset' value='Undo Changes' class='input_btn'>
				</td>
			</tr>
		</table>
	</div>
</div>
