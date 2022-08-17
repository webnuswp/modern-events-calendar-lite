<?php

namespace MEC\Events;

use MEC\PostBase;
use MEC\Settings\Settings;

class Event extends PostBase {

	/**
	 * Constructor
	 *
	 * @param int|\WP_Post|array $data
	 */
	public function __construct( $data, $load_post = true ) {

		$this->type = 'event';
		parent::__construct( $data, $load_post );
	}

	public function get_tickets() {

		return $this->get_meta( 'mec_tickets' );
	}

	public function get_content( $content = null ) {

		return is_null( $content ) ? $this->main->get_post_content( $this->ID ) : $content;
	}

	public function get_datetime() {

		$date = $this->get_data( 'date' );

		if ( ! is_null( $date ) ) {

			return $date;
		}

		$types = array(
			'start',
			'end',
		);

		$datetime = array(
			'hour'    => '',
			'minutes' => '',
			'ampm'    => '',
		);

		$datetimes = array(
			'start' => $datetime,
			'end'   => $datetime,
		);

		foreach ( $types as $type ) {

			$date    = $this->get_meta( 'mec_' . $type . '_date' );
			$hours   = $this->get_meta(  'mec_' . $type . '_time_hour' );
			$minutes = $this->get_meta(  'mec_' . $type . '_time_minutes' );
			$ampm    = $this->get_meta(  'mec_' . $type . '_time_ampm' );

			$datetime = array(
				'date'    => $date,
				'hours'    => sprintf('%02d',$hours),
				'minutes' => sprintf('%02d',$minutes),
				'ampm'    => $ampm ? $ampm : '',
			);

			$datetime['datetime'] = "{$date} {$datetime['hours']}:{$datetime['minutes']} {$datetime['ampm']}";
			$datetime['timestamp'] = strtotime($datetime['datetime']);
			$datetimes[ $type ]    = $datetime;
		}

		$this->data['datetimes'] = $datetimes;

		return $datetimes;
	}

	public function get_repeating_status(){

		return $this->get_meta('mec_repeat_status');
	}

	public function get_repeating_type(){

		return $this->get_meta('mec_repeat_type');
	}

	public function get_occurrences_times( $start, $limit = 100 ) {

		$k           = 'mec-occurrences-'.$this->ID.'-' . $start . $limit;
		$occurrences = wp_cache_get( $k );
		if ( empty( $occurrences ) ) {

			$feature = \MEC::getInstance('app.libraries.feature_occurrences');
			$occurrences = $feature->get_dates( $this->ID, $start, $limit );
			wp_cache_set( $k, $occurrences, 'mec-occurrences', 120 );
		}

		return $occurrences;
	}

	public function get_occurrence_data( $occurrence_id ){

		return \MEC::getInstance('app.features.feature_occurrences')->get($occurrence_id);
	}


	public function get_occurrence_time( $start ){

		$occurrence = $this->get_occurrences_times( $start, 1 );
		$occurrence = is_array($occurrence) ? current($occurrence) : [];

		return (object)$occurrence;
	}

	public function get_end_timestamp_occurrence($timestamp){

		if(empty($timestamp) || !is_numeric($timestamp)){

			return null;
		}

		$end_occurrence = $this->get_occurrence_time( $timestamp );
		$event_end_datetime = isset($end_occurrence->tend) ? $end_occurrence->tend : false;

		if(false === $event_end_datetime){

			$start_date = date('Y-m-d',$timestamp);
			$end_date = \MEC\Base::get_main()->get_end_date_by_occurrence( $this->ID, $start_date );
			$datetimes = $this->get_datetime($this->ID);
			$event_end_datetime = strtotime("{$end_date} {$datetimes['hours']}:{$datetimes['minutes']} {$datetimes['ampm']}");
		}

		return $event_end_datetime;
	}

	public function get_detail( $skin_type = 'single', $start_timestamp = '' ){

		$start_timestamp = !empty( $start_timestamp ) ? $start_timestamp : strtotime('Yesterday');

		$event_id = $this->ID;
		$main = new \MEC_Main();
		$render = $main->getRender();

		$rendered = $render->data($event_id);

		$dates = $render->dates($event_id, NULL, 6, date('Y-m-d H:i', $start_timestamp));

		$data = new \stdClass();
		$data->ID = $event_id;
		$data->data = $rendered;
		$data->dates = $dates;
		$data->date = isset($dates[0]) ? $dates[0] : $this->get_datetime($event_id);

		$skin = new \stdClass();
		$skin->skin = $skin_type;
		$skin->multiple_days_method = Settings::getInstance()->get_settings('multiple_day_show_method');

		return $render->after_render( $data, $skin );
	}

	public function get_notifications_settings($group_id = null){

		$notifications = $this->get_meta('mec_notifications');

		if( !is_null( $group_id ) ){

			return isset($notifications[$group_id]) ? $notifications[$group_id] : null;
		}

		return $notifications;

	}

	/**
	 * Return event link
	 *
	 * @param int $start_timestamp
	 * @param bool $replace_read_more_link
	 *
	 * @return string
	 */
	public function get_permalink( $start_timestamp = '', $replace_read_more_link = true, $single_date_method = '' ){

		if( $replace_read_more_link ){

			$custom_link = $this->get_meta('mec_read_more');
			if( !empty( $custom_link ) ){

				return $custom_link;
			}
		}

		if( is_null( $single_date_method ) ){

			$single_date_method = \MEC\Settings\Settings::getInstance()->get_settings( 'single_date_method' );
		}

		$url = isset( $this->data['data']->permalink ) ? $this->data['data']->permalink : get_the_permalink( $this->get_id() );

		if( !( empty( $single_date_method ) || 'next' === $single_date_method ) ) {

			if( empty( $start_timestamp ) ){

				$start_timestamp = current_time('timestamp', 0);
				$occurrence = $this->get_occurrence_time( $start_timestamp );
				$start_timestamp = $occurrence->tstart;
				$start_datetime = date( 'Y-m-d H:i', $start_timestamp );
			}

			$start_date = date( 'Y-m-d', $start_timestamp );
			$start_time = date( 'H:i', $start_timestamp );

			$url =  \MEC\Base::get_main()->add_qs_var('occurrence', $start_date, $url);

			$repeat_type = $this->get_meta( 'mec_repeat_type' );
			if( 'custom_days' === $repeat_type ){

				$url = \MEC\Base::get_main()->add_qs_var('time', $start_timestamp, $url);
			}
	   	}

		return apply_filters( 'mec_get_event_permalink', $url, $this );
	}

	/**
	 * Return terms
	 *
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function get_terms( $taxonomy ){

		return wp_get_post_terms( $this->get_id(), $taxonomy );
	}

	/**
	 * Return terms ids
	 *
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function get_terms_ids( $taxonomy ){

		$terms = $this->get_terms( $taxonomy );

		$ids = array();
		foreach( $terms as $term ){

			$ids[ $term->term_id ] = $term->term_id;
		}

		return $ids;
	}

	/**
	 * Return event custom data
	 *
	 * @return array
	 */
	public function get_custom_data(){

		$data = array();
		$event_fields_data = $this->get_meta( 'mec_fields' );
        if(!is_array($event_fields_data)) $event_fields_data = array();

		$event_fields = \MEC\Base::get_main()->get_event_fields();
        foreach($event_fields as $f => $event_field){

            if(!is_numeric($f)) {

                continue;
            }

            $label = isset($event_field['label']) ? $event_field['label'] : '';
            $value = isset($event_fields_data[$f]) ? $event_fields_data[$f] : NULL;

			if(is_array($value)) {

				$value = implode(', ', $value);
			}

			$data[ $f ] = array(
				'label' => $label,
				'value' => $value,
			);
        }

		return $data;
	}
}
