<?php

namespace MEC\SingleBuilder\Widgets\EventMoreInfo;

use MEC\Base;
use MEC_feature_occurrences;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventMoreInfo extends WidgetBase {

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
		$start_timestamp = $event_detail->date['start']['timestamp'] ?? '';
		$data = (isset($event_detail->data->meta['mec_fields']) and is_array($event_detail->data->meta['mec_fields'])) ? $event_detail->data->meta['mec_fields'] : get_post_meta($event_detail->ID, 'mec_fields', true);

		$more_info = $event_detail->data->meta['mec_more_info'] ?? '';
		$more_info_title = $event_detail->data->meta['mec_more_info_title'] ?? esc_html__('Read More', 'modern-events-calendar-lite');
		$more_info_target = $event_detail->data->meta['mec_more_info_target'] ?? '_self';

		$html = '';
		if ( true === $this->is_editor_mode && ( !( $more_info != '' ) ) ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('This widget is displayed if read more is set. In order for the widget in this page to be displayed correctly, please set read more for your last event.', 'modern-events-calendar-lite').'</span>'
					. '<a href="https://webnus.net/dox/modern-events-calendar/add-event/" target="_blank">' . esc_html__('How to set read more', 'modern-events-calendar-lite') . ' </a>'
				.'</p></div>';
		} else {

			$more_info = MEC_feature_occurrences::param($event_id, $start_timestamp, 'more_info', $more_info);
            $more_info_title = MEC_feature_occurrences::param($event_id, $start_timestamp, 'more_info_title', $more_info_title);
			$more_info_target = MEC_feature_occurrences::param($event_id, $start_timestamp, 'more_info_target', $more_info_target);

			ob_start();
			if ( trim($more_info) && $more_info != 'http://') {
				?>
				<style>.mec-event-more-info h3{display:inline;}</style>
				<div class="mec-event-meta">
					<div class="mec-event-more-info">
						<i class="mec-sl-info"></i>
						<h3 class="mec-more-info-label"><?php echo Base::get_main()->m('more_info_link', esc_html__('More Info', 'modern-events-calendar-lite')); ?></h3>
						<dd class="mec-events-event-more-info"><a class="mec-more-info-button a mec-color-hover" target="<?php echo esc_attr( $more_info_target ); ?>" href="<?php echo esc_attr( $more_info ); ?>"><?php echo esc_html( $more_info_title ); ?></a></dd>
					</div>
				</div>
				<?php
			}
			$html = ob_get_clean();
		}

		return $html;
	}
}
