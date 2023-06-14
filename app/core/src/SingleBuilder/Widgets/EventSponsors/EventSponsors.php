<?php

namespace MEC\SingleBuilder\Widgets\EventSponsors;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventSponsors extends WidgetBase {

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
		$event_detail = $this->get_event_detail($event_id);
		$sponsors = (isset($event_detail->data->sponsors) and is_array($event_detail->data->sponsors)) ? $event_detail->data->sponsors : array();

		$html = '';
		if ( true === $this->is_editor_mode && ( empty($sponsors) || (!isset($settings['sponsors_status']) || !$settings['sponsors_status']) ) ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('This widget is displayed if sponsor is set. In order for the widget in this page to be displayed correctly, please set sponsor for your last event.', 'modern-events-calendar-lite').'</span>'
					. '<a href="https://webnus.net/dox/modern-events-calendar/sponsor/" target="_blank">' . esc_html__('How to set sponsor', 'modern-events-calendar-lite') . ' </a>'
				.'</p></div>';
		} elseif ( true === $this->is_editor_mode && isset($settings['sponsors_status']) && $settings['sponsors_status'] ) {

			$html = Base::get_main()->module('sponsors.details', array('event'=>$event_detail));
		} else {

			ob_start();
				// Event Sponsor
				echo Base::get_main()->module('sponsors.details', array('event'=>$event_detail));
			$html = ob_get_clean();
		}

		return $html;
	}
}
