<?php

namespace MEC\SingleBuilder\Widgets\EventZoom;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventZoom extends WidgetBase {

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

		$settings = $this->settings;
		$events_detail = $this->get_event_detail($event_id);

		$html = '';
		if ( true === $this->is_editor_mode && isset( $events_detail->data->meta['mec_zoom_event'] ) && 'none' === $events_detail->data->meta['mec_zoom_event'] ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('Please make sure the last event has appropriate content for this widget.', 'modern-events-calendar-lite').'</span>'
					. '<a href="https://webnus.net/dox/modern-events-calendar/zoom-integration-addon/" target="_blank">' . esc_html__('How to set up Zoom event', 'modern-events-calendar-lite') . ' </a>'
				.'</p></div>';
		} else {

			ob_start();
				if ( isset( $atts['zoom_event_type'] ) && $atts['zoom_event_type'] == 'badge' ) {

					do_action('mec_single_zoom_badge', $events_detail->data );
				} else {

					do_action('mec_single_after_content', $events_detail );
				}
			$html = ob_get_clean();
		}

		return $html;
	}
}
