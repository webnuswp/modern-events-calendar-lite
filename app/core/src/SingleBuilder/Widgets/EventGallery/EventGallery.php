<?php

namespace MEC\SingleBuilder\Widgets\EventGallery;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventGallery extends WidgetBase {

	public function display_gallery( $event_id ) {

		if( !( isset($this->settings['event_gallery_status']) and $this->settings['event_gallery_status'] ) ) {

			return '';
		}

		$gallery = get_post_meta( $event_id, 'mec_event_gallery', true );
        if( !is_array( $gallery ) ) {

			$gallery = [];
		}

        // Gallery
        if( count( $gallery ) ) {
            // Featured Image
            $featured_image_id = get_post_thumbnail_id( $event_id );

            // Add Featured Image to Gallery
            if($featured_image_id and !in_array( $featured_image_id, $gallery ) ) {

				array_unshift( $gallery, $featured_image_id );
			}

            // First Image
            $first_image_id = $gallery[0];

            $gallery_list = '<ul class="mec-event-gallery-list">';
            foreach( $gallery as $gallery_image_id ) {

                $thumbnail = wp_get_attachment_image_src($gallery_image_id, 'thumbnail');
                if(!$thumbnail) continue;

                list($src) = $thumbnail;
                $attachment_title = get_the_title($gallery_image_id);

                $gallery_list .= '<li><img data-full-src="'.esc_url( wp_get_attachment_url( $gallery_image_id ) ).'" src="'.esc_url( $src ).'" alt="'.esc_attr( $attachment_title ).'" /></li>';
            }
            $gallery_list .= '</ul>';

            $first_attachment_title = get_the_title( $first_image_id );

			return '<div class="mec-event-gallery-wrapper"><div class="mec-event-gallery-image-list-wrapper">'.$gallery_list.'</div><div class="mec-event-gallery-image"><img src="'.esc_url( wp_get_attachment_url( $first_image_id ) ).'" alt="'.esc_attr( $first_attachment_title ).'" /></div></div>';
        }

		return '';
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

		$html = '';
		$settings = $this->settings;

		$event_gallery_module = isset($this->settings['event_gallery_status']) and $this->settings['event_gallery_status'] ? true : false;
		if( $event_gallery_module ) {

			$html = $this->display_gallery( $event_id );
		}

		if ( ( true === $this->is_editor_mode || ( $atts['edit_mode'] ?? false ) ) && empty( $html ) ) {

			$html = '<div class="mec-content-notification"><p>'
				.'<span>'. esc_html__('This widget is displayed if event gallery is set. In order for the widget in this page to be displayed correctly, please set Event Gallery module for your last event.', 'modern-events-calendar-lite').'</span>'
				. '<a href="https://webnus.net/dox/modern-events-calendar/event-gallery-module/" target="_blank">' . esc_html__('How to set Event Gallery module', 'modern-events-calendar-lite') . ' </a>'
			.'</p></div>';
		}

		return  $html;
	}
}
