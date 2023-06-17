<?php

namespace MEC\SingleBuilder\Widgets\FeaturedImage;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class FeaturedImage extends WidgetBase {

	public function get_thumbnail( $event_id, $width = 512 ,$height = 512 ){

		return get_the_post_thumbnail(
			$event_id,
			array(
				$width,
				$height,
			)
		);
	}

	/**
	 *  Get HTML Output
	 *
	 * @param int $event_id
	 * @param array $atts
	 * @return string
	 */
	public function output( $event_id = 0, $atts = array() ){

		if( !$event_id ){

			$event_id = $this->get_event_id();
		}

		if(!$event_id){
			return '';
		}

		$settings = $this->settings;
		$atts = shortcode_atts( [
				'image_width' => 500,
				'image_height' => 500,
			],
			$atts
		);

		$html = $this->get_thumbnail($event_id,$atts['image_width'],$atts['image_height']);

		if ( true === $this->is_editor_mode && empty($html) ) {

			$width = $atts['image_width'] ? $atts['image_width'] : '';
			$height = $atts['image_height'] ? $atts['image_height'] : '';
			$html = '<img src="' . esc_attr( plugins_url('empty-pic.jpg' , __FILE__ ) ) . '" width="'. esc_attr( $width ) .'" height="'. esc_attr( $height ).'"> ';
		}

		return  $html;
	}
}
