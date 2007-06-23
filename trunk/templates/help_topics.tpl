<div style='color: #333; font-weight: bold;'>
	{$category_name}
</div>
{if $topics }
	<ol>
		{ section name=tpc loop=$topics }
			<li style='padding-bottom: 10px;'>
				<span class='helplink' onClick='Element.toggle("q{$topics[tpc].count}");'>
					{ $topics[tpc].question }
				</span>
				<div id='q{$topics[tpc].count}'>
					{ $topics[tpc].answer }
				</div>
		{ /section }
	</ol>

	<script type='text/javascript'>
		var counter = {$topic_count};

	{ literal }
		for (i = 1; i <= counter; i++) {
			Element.hide('q'+i);
		}
	</script>
	{ /literal }
{ else }
	<center>No topics were found in this category</center><br>
{ /if }
