jQuery(document).ready(function($) {
	 function log( message ) {
	$( "<div>" ).text( message ).prependTo( "#log" );
	$( "#log" ).scrollTop( 0 );
	}


$( "#uultra_uname" ).autocomplete({
source: function( request, response ) {
$.ajax({
url: ajaxurl,
dataType: "json",
data: {
		q: request.term, action:"uultra_load_user_ticket"
		},
success: function( data ) {
response( data );
}
});
},
minLength: 1,
select: function( event, ui ) {
	
log( ui.item ?
"Selected: " + ui.item.label :
"Nothing selected, input was " + this.value);
},
open: function() {
$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
},
close: function() {
$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
}
});
		
});
