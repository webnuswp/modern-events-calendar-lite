<?php
/** no direct access **/
defined('MECEXEC') or die();
if ( Elementor\Plugin::$instance->editor->is_edit_mode() || Elementor\Plugin::$instance->preview->is_preview_mode() ) {
the_content();
}
do_action( 'mec_esb_content' , $event );