<?php

namespace MEC\SingleBuilder\Widgets\EventDate;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventDate extends WidgetBase {

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

		$occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '';
		$start_datetime = !trim($occurrence) && isset($event_detail->date['start']) ? $event_detail->date['start'] : array( 'date' => $occurrence );
		$occurrence_end_date = trim($occurrence) ? Base::get_main()->get_end_date_by_occurrence($event_detail->data->ID, $start_datetime['date']) : '';
		$end_datetime = isset($event_detail->date['end']) ? $event_detail->date['end'] : array( 'date' => $occurrence_end_date );

		$date_format = isset($settings['single_date_format1']) ? $settings['single_date_format1'] : get_option( 'date_format' );
		$date_label = Base::get_main()->date_label( $start_datetime, $end_datetime, $date_format );

		$html = '';
		$midnight_event = Base::get_main()->is_midnight_event($event_detail);
		ob_start();
			?>
			<div class="mec-event-meta">
				<div class="mec-single-event-date">
					<i class="mec-sl-calendar"></i>
					<h3 class="mec-date"><?php esc_html_e('Date', 'modern-events-calendar-lite'); ?></h3>
					<dl>
						<?php if($midnight_event): ?>
							<dd><abbr class="mec-events-abbr"><?php echo Base::get_main()->dateify( $event_detail, $date_format ); ?></abbr></dd>
						<?php else: ?>
							<dd><abbr class="mec-events-abbr"><?php echo wp_kses( $date_label, array('span' => array( 'class' => array(), 'itemprop' => array() ) ) ); ?></abbr></dd>
						<?php endif; ?>
					</dl>
					<?php echo Base::get_main()->holding_status( $event_detail ); ?>
				</div>
			</div>
			<?php

		$html = ob_get_clean();

		return $html;
	}
}
