<?php

namespace MEC\SingleBuilder\Widgets\EventPublicDownload;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventPublicDownload extends WidgetBase {

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

		$single = new \MEC_skin_single();
		$html = $single->display_public_download_module( $events_detail );

		if ( empty( $html ) && $this->is_editor_mode() ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('This widget is displayed if event Public Download is set. In order for the widget in this page to be displayed correctly, please set Public Download for your last event.', 'modern-events-calendar-lite').'</span>'
					. '<a href="https://webnus.net/dox/modern-events-calendar/single-event-page-settings/#Public_Download_Module" target="_blank">' . esc_html__('How to set Public Download', 'modern-events-calendar-lite') . ' </a>'
				.'</p></div>';

		}

		return $html;
	}
}
