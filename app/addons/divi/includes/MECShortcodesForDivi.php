<?php
if( ! function_exists( 'MECDIVI_et_builder_load_actions' )) {
    function MECDIVI_et_builder_load_actions( $actions ) {
        $actions[] = 'MECDIVI_load_mec_shortcode';

        return $actions;
    }
}

add_filter( 'et_builder_load_actions', 'MECDIVI_et_builder_load_actions' );

if( ! function_exists( 'MECDIVI_load_mec_shortcode' )) {
function MECDIVI_load_mec_shortcode() {
        $post_id = $_POST['shortcode_id'];
 		echo do_shortcode( '[MEC id="'.$post_id.'"]' );
        wp_die();
    }
}
add_action( 'wp_ajax_nopriv_MECDIVI_load_mec_shortcode', 'MECDIVI_load_mec_shortcode' );
add_action( 'wp_ajax_MECDIVI_load_mec_shortcode', 'MECDIVI_load_mec_shortcode' );