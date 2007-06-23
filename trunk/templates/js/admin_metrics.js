function hide_all() {
	Element.hide('workbox');
	Element.hide('work_container2');
	Element.hide('welcome_box');
	Element.hide('graph_box');
}

function show_metric_config(metric_id) {
	$('action').value 	= 'show_metric_config';
	$('metric_id').value	= metric_id;

	var url 		= "async/admin_metrics.php";
	var params 		= Form.serialize('nessus_reg');

	new Ajax.Updater(
		{ success: 'graph_box' },
		url, 
		{
			method: 'post',
			parameters: params,
			evalScripts: true
		});

	hide_all();
	Element.show('graph_box');
}

function show_graph_categories() {
	var url 	= "async/admin_metrics.php";
	var params	= "action=show_graph_categories";

	new Ajax.Updater(
		{success: 'graph_categories'},
		url, 
		{
			method: 'post',
			parameters: params
		});
}

function show_report_categories() {
	var url 	= "async/admin_metrics.php";
	var params	= "action=show_report_categories";

	new Ajax.Updater(
		{success: 'report_categories'},
		url, 
		{
			method: 'post',
			parameters: params
		});
}

var begin_cal 		= new nessCal("calendar_bt");
begin_cal.year_d	= 'cal_year_bt';
begin_cal.month_d 	= 'cal_month_bt';
begin_cal.day_d		= 'cal_day_bt';
begin_cal.hour_d	= 'cal_hour_bt';
begin_cal.min_d		= 'cal_minute_bt';
begin_cal.ampm_d	= 'cal_ampm_bt';

begin_cal.year_v	= 'cal_year_val_bt';
begin_cal.month_v 	= 'cal_month_val_bt';
begin_cal.day_v		= 'cal_day_val_bt';
begin_cal.hour_v	= 'cal_hour_val_bt';
begin_cal.min_v		= 'cal_minute_val_bt';
begin_cal.ampm_v	= 'cal_ampm_val_bt';

var end_cal		= new nessCal("calendar_et");
end_cal.year_d		= 'cal_year_et';
end_cal.month_d 	= 'cal_month_et';
end_cal.day_d		= 'cal_day_et';
end_cal.hour_d		= 'cal_hour_et';
end_cal.min_d		= 'cal_minute_et';
end_cal.ampm_d		= 'cal_ampm_et';

end_cal.year_v		= 'cal_year_val_et';
end_cal.month_v 	= 'cal_month_val_et';
end_cal.day_v		= 'cal_day_val_et';
end_cal.hour_v		= 'cal_hour_val_et';
end_cal.min_v		= 'cal_minute_val_et';
end_cal.ampm_v		= 'cal_ampm_val_et';
