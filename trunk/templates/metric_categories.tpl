{ section name=cgy loop=$categories }
	<div class='sidechoice padded' onClick='show_metric_config("{$categories[cgy].id}");'>
		{$categories[cgy].name}
	</div>
{ sectionelse }
	<div class='padded' style='color: #999;'>
		no categories exist
	</div>
{ /section }
