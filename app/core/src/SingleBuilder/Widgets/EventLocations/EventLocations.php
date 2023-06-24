<?php

namespace MEC\SingleBuilder\Widgets\EventLocations;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventLocations extends WidgetBase {

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
		$locations    = isset($event_detail->data->locations) ? $event_detail->data->locations : array();
		$primary_location_id = \MEC\Base::get_main()->get_master_location_id( $event_detail );
		$location_term = get_term_by( 'id', $primary_location_id, 'mec_location' );

		$html = '';
		if ( true === $this->is_editor_mode && ( empty($locations) || !isset($locations[$primary_location_id]) ) ) {

			$html = '<div class="mec-content-notification"><p>'
					.'<span>'. esc_html__('This widget is displayed if location is set. In order for the widget in this page to be displayed correctly, please set location for your last event.', 'modern-events-calendar-lite').'</span>'
					. '<a href="https://webnus.net/dox/modern-events-calendar/location/" target="_blank">' . esc_html__('How to set location', 'modern-events-calendar-lite') . ' </a>'
				.'</p></div>';
		} elseif ( !empty($locations) && isset($locations[$primary_location_id]) and !empty($locations[$primary_location_id])) {

			$single        = new \MEC_skin_single();
			ob_start();
			$location = $locations[$primary_location_id];

			echo '<div class="mec-event-meta">';
				?>
				<div class="mec-single-event-location">
					<?php if ($location['thumbnail']) : ?>
						<img class="mec-img-location" src="<?php echo esc_url($location['thumbnail']); ?>" alt="<?php echo (isset($location['name']) ? esc_attr($location['name']) : ''); ?>">
					<?php endif; ?>
					<i class="mec-sl-location-pin"></i>
					<h3 class="mec-events-single-section-title mec-location"><?php echo Base::get_main()->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite')); ?></h3>
					<dd class="author fn org"><?php echo (isset($location['name']) ? esc_html($location['name']) : ''); ?></dd>
					<dd class="location">
						<address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address>
					</dd>

					<?php if(isset($location['url']) and trim($location['url'])): ?>
						<dd class="mec-location-url">
							<i class="mec-sl-sitemap"></i>
							<h6><?php esc_html_e('Website', 'modern-events-calendar-lite'); ?></h6>
							<span><a href="<?php echo esc_url($location['url']); ?>" class="mec-color-hover" target="_blank"><?php echo esc_url( $location['url'] ); ?></a></span>
						</dd>
					<?php endif;

					$location_description_setting = isset( $settings['location_description'] ) ? $settings['location_description'] : '';
					if($location_description_setting == '1'):
						?>
						<dd class="mec-location-description">
							<p><?php echo esc_html( $location_term->description ); ?></p>
						</dd>
					<?php endif; ?>
				</div>
				<?php
				$single->show_other_locations($event_detail); // Show Additional Locations
			echo '</div>';

			$html = ob_get_clean();
		}

		return $html;
	}
}
