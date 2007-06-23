{ if $action == "pre" }
		<div id='pre_results'>
		<table align='center' style='background-color: #efefef; width: 95%; border: 1px solid #ccc;'>
			<tr>
				<td style='vertical-align: middle;'>
					<div id='processing_steps' style='background-color: #fff; margin: 10px; padding: 10px; border: 1px solid #ccc;'>
						<span style='color: #000;'>
							<h5>Your scan is running, please be patient</h5>
							<img src='../images/spinner.gif'>
						</span><p>
					</div>
				</td>
			</tr>
		</table>
		</div>
{ else }
		<table align='center' style='background-color: #efefef; width: 95%; border: 1px solid #ccc;'>
			<tr>
				<td style='vertical-align: middle;'>
					<div id='processing_steps_2' style='background-color: #fff; margin: 10px; padding: 10px; border: 1px solid #ccc;'>
					<table width='100%'>
						<tr>
							<td align='left' valign='top' width='50%'>
								<h5>Scan Complete</h5>
							</td>
							<td align='right' valign='top' width='50%'>
								<span style='font-size: small;'>
									<a href='../index.php'>Create a new scan</a>
								</span>
							</td>
						</tr>
					</table>
		
					{ $results }

					<table width='100%'>
						<tr>
							<td width='50%'></td>
							<td align='right' width='50%'>
								<span style='font-size: small;'>
									<a href='../index.php'>Create a new scan</a>
								</span>
							</td>
						</tr>
					</table>

					</div>
				</td>
			</tr>
		</table>

{ /if }
