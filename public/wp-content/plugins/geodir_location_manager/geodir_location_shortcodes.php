<?php
function geodir_current_location_sc( $args, $caption = '' ) {
	geodir_get_current_location( $args ); //its in geodir_location_template_tags.php
}

function geodir_location_switcher_sc( $args, $caption = '' ) {
	geodir_get_location_switcher( $args );
}

function geodir_location_list_sc( $args, $caption = '' ) {
	geodir_get_location_list( $args );
}


function geodir_location_tab_switcher_sc( $args, $caption = '' ) {
	echo "<span class='geodir_shortcode_location_tab_container'>";
	geodir_location_tab_switcher( $args );
	echo "</span>";
}
?>