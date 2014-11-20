<?php

function shiftnav_searchbar( $placeholder = "Search..." ){

	?>
	<!-- ShiftNav Search Bar -->
	<div class="shiftnav-search">
		<form role="search" method="get" class="shiftnav-searchform" action="<?php echo home_url( '/' ); ?>">
			<input type="text" placeholder="<?php echo $placeholder; ?>" value="" name="s" class="shiftnav-search-input" />
			<input type="submit" class="shiftnav-search-submit" value="&#xf002;" />
		</form>
	</div>
	<!-- end .shiftnav-search -->

	<?php
}
function shiftnav_searchbar_shortcode( $atts , $content ){

	extract( shortcode_atts( array(
		'placeholder' => __( 'Search...' , 'shiftnav' ),
	), $atts ) );

	ob_start();
	shiftnav_searchbar( $placeholder );
	$s = ob_get_clean();
	return $s;
}
add_shortcode( 'shiftnav-search' , 'shiftnav_searchbar_shortcode' );

function shiftnav_content_searchbar(){
	shiftnav_searchbar();
}
//add_action( 'shiftnav_before' , 'shiftnav_content_searchbar' , 30 );




function shiftnav_searchtoggle( $placeholder = "Search..." , $position = '' ){
	?>

	<!-- ShiftNav Search Bar Toggle -->
	<a class="shiftnav-searchbar-toggle <?php if( $position ) echo 'shiftnav-searchbar-toggle-pos-'.$position; ?> shiftnav-toggle-main-block shiftnav-toggle-main-ontop"><i class="fa fa-search"></i></a>

	<!-- ShiftNav Search Bar Drop -->
	<div class="shiftnav-searchbar-drop">
		<form role="search" method="get" class="shiftnav-searchform" action="<?php echo home_url( '/' ); ?>">
			<input type="text" placeholder="<?php echo $placeholder; ?>" value="" name="s" class="shiftnav-search-input" />
			<input type="submit" class="shiftnav-search-submit" value="&#xf002;" />
		</form>
	</div>
	<!-- end .shiftnav-searchbar-drop -->

	<?php
}

function shiftnav_search_toggle_shortcode( $atts , $content ){

	extract( shortcode_atts( array(
		'placeholder' 	=> __( 'Search...' , 'shiftnav' ),
		'position'		=> ''
	), $atts ) );


	ob_start();
	shiftnav_searchtoggle( $placeholder , $position );
	$s = ob_get_clean();
	return $s;
}
add_shortcode( 'shiftnav-search-toggle' , 'shiftnav_search_toggle_shortcode' );

