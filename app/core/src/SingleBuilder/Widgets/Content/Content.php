<?php

namespace MEC\SingleBuilder\Widgets\Content;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class Content extends WidgetBase {

	/**
	 *  Get HTML Output
	 *
	 * @param int $event_id
	 * @param array $atts
	 *
	 * @return string
	 */
	public function output( $event_id = 0, $atts = array() ){

		if( !$event_id ){

			$event_id = $this->get_event_id();
		}

		if(!$event_id){
			return '';
		}

		$content = get_the_content( '', false, $event_id );
		$content = wpautop( $content );
		$content = do_shortcode( $content );

		if( !$this->is_editor_mode() ){

			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
		}else{

			if( 'yes' === get_post_meta( $event_id, '_elementor_edit_mode', true ) ){

				$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $event_id );
			}
		}

		if ( empty( $content ) && $this->is_editor_mode() ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('This widget is displayed if event content is set. In order for the widget in this page to be displayed correctly, please set content for your last event.', 'modern-events-calendar-lite').'</span>'
				.'</p></div>';
		} else {

			$html = '<div class="mec-single-event-description mec-events-content">'
				. $content .
			'</div>';
		}

		return $html;
	}
}
