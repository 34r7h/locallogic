jQuery(document).ready(function($) {



function avia_yoast_edit_analysis()
{
	var builder = jQuery('#avia_builder:visible'),
		results = jQuery('#focuskwresults li:eq(3) span');
		
	if(builder.length)
	{
		results.removeClass('wrong').addClass('good');
		results.html('Content Analysis disabled since it does not work with complex layout building tools <br/><small>(No worries though: Search engines won\'t have difficulties to fetch your content)</small>');
	}
}


/*activate functions*/
setTimeout(avia_yoast_edit_analysis, 1000);

});

