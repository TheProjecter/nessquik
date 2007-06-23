<div style='height: 100%; margin: 10px 0px 10px 0px; border: 1px solid #fff; vertical-align: top;'>
	<table style='width: 100%; padding-left: 20px;'>
		<tr>
			<td style='width: 100%; font-weight: bold; color: #4D69A2;' colspan='2'>
				What type of content are you adding?
			</td>
		</tr>
		<tr>
			<td style='width: 50%; text-align: center;'>
				<input type='radio' id='content_type_category' name='content_type' onClick='Element.hide("content_new_topic"); Element.show("content_new_category");'>a new category
			</td>
			<td style='width: 50%; text-align: center;'>
				<input type='radio' id='content_type_topic' name='content_type' onClick='Element.hide("content_new_category"); show_add_help_content();' checked='checked'>a new help topic
			</td>
		</tr>
	</table>
	<br>
		<hr style="width: 90%; color: #d8dfea; background-color: #d8dfea; border: 0px;">
	<br>
	<div id='content_new_topic'>
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
							<option value='{$admin_categories[actg].id}'>{$admin_categories[actg].name}
						{ /section }
						</optgroup>
						<optgroup label='General'>
						{ section name=gctg loop=$general_categories }
							<option value='{$general_categories[gctg].id}'>{$general_categories[gctg].name}
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
					<input type='text' id='question' name='question' class='input_txt' style='width: 95%;' maxlength='255'>
				</td>
			</tr>
			<tr>
				<td style='width: 100%; font-weight: bold; color: #4D69A2;' colspan='2'>
					Answer
				</td>
			</tr>
			<tr>
				<td style='width: 100%; text-align: center;' colspan='2'>
					<center><textarea id="answer" name="answer" rows="10" cols="50" class='input_txt' style="width: 95%;"></textarea></center>
				</td>
			</tr>
		</table>
		<br>
		<table style='width: 100%; padding-left: 20px;'>
			<tr>
				<td style='width: 100%; text-align: center;'>
					<input type='button' name='add' value='Add Topic' class='input_btn' onClick='do_add_help_content();'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type='reset' value='Clear' class='input_btn'>
				</td>
			</tr>
		</table>
	</div>

	<div id='content_new_category'>
		<table style='width: 100%; padding-left: 20px;'>
			<tr>
				<td style='width: 100%; font-weight: bold; color: #4D69A2;' colspan='2'>
					Category Name
				</td>
			</tr>
			<tr>
				<td style='width: 100%; text-align: center;' colspan='2'>
					<span styl='padding-left: 17px;'></span>
					<input type='text' id='category_name' name='category_name' class='input_txt' style='width: 95%;' maxlength='255'>
				</td>
			</tr>
			<tr>
				<td style='width: 100%; font-weight: bold; color: #4D69A2;' colspan='2'>
					Category Access
				</td>
			</tr>
			<tr>
				<td style='width: 50%; text-align: center;'>
					<input type='radio' name='category_access' value='A'>Administrator
				</td>
				<td style='width: 50%; text-align: center;'>
					<input type='radio' name='category_access' value='G' checked='checked'>General viewing
				</td>
			</tr>
		</table>
		<br>
		<table style='width: 100%; padding-left: 20px;'>
			<tr>
				<td style='width: 100%; text-align: center;'>
					<input type='button' name='add' value='Add Category' class='input_btn' onClick='do_add_help_category();'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type='reset' value='Clear' class='input_btn'>
				</td>
			</tr>
		</table>

	</div>
</div>

<script language="javascript" type="text/javascript">
	Element.hide('content_new_category');
</script>
