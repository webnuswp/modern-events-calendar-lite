<?php

namespace MEC\Forms;

use MEC\Settings\Settings;
use MEC\Singleton;

class CustomForm extends Singleton {

	public function get_fields( $group_id, $event_id = null, $translated_event_id = null ) {

		if(is_null($event_id)){

			$event_id = ('mec-events' === get_post_type()) ? get_the_ID() : null;
		}

		$form_id  = str_replace( array( '_reg', '_bfixed', '' ), '', $group_id );
		$group_id .= '_fields';
		$primary_group_id = $group_id;

		if ( false === strpos( $form_id, 'general' ) && ( !isset($_GET['page']) || 'MEC-settings' !== $_GET['page'] ) ) {

			$status_key = 'form_' . $form_id . '_status';
			$status     = Settings::getInstance()->get_settings( $status_key );
			switch ( $status ) {
				case 'enable':

					break;
				case 'default':
				default:
					$group_keys = explode( '_', $form_id );
					$group_id   = str_replace( end( $group_keys ), 'general', $group_id );
					break;
			}
		}

		$options = Settings::getInstance()->get_options();
		$fields  = isset( $options[ $group_id ] ) ? $options[ $group_id ] : array();


		return apply_filters( 'mec_get_custom_form_fields', $fields, $event_id, $form_id, $group_id, $primary_group_id,$translated_event_id );
	}


	public function get_reg_fields( $group_id, $event_id = null, $translated_event_id = null ) {

		$group_id  .= '_reg';
		$mec_email = false;
		$mec_name  = false;

		$fields = $this->get_fields( $group_id, $event_id, $translated_event_id );

		if ( !is_array( $fields ) ) {
			$fields = array();
		}

		foreach ( $fields as $field ) {
			if ( isset( $field['type'] ) ) {
				if ( 'name' === $field['type'] ) {
					$mec_name = true;
				}
				if ( 'mec_email' === $field['type'] ) {
					$mec_email = true;
				}
			} else {
				break;
			}
		}

		if ( !$mec_name ) {
			array_unshift(
				$fields,
				array(
					'mandatory' => '0',
					'type'      => 'name',
					'label'     => esc_html__( 'Name', 'mec' ),
				)
			);
		}

		if ( !$mec_email ) {
			array_unshift(
				$fields,
				array(
					'mandatory' => '0',
					'type'      => 'mec_email',
					'label'     => esc_html__( 'Email', 'mec' ),
				)
			);
		}

		return $fields;
	}

	public function get_fixed_fields( $group_id, $event_id = null, $translated_event_id = null ) {

		$group_id .= '_bfixed';

		return $this->get_fields( $group_id, $event_id, $translated_event_id );
	}

	public function display_reg_fields( $group_id, $event_id = null, $attendee_id = 0, $translated_event_id = null, $data = array() ) {

		if ( is_null( $event_id ) ) {

			$event_id = get_the_ID();
		}

		$fields = $this->get_reg_fields( $group_id, $event_id );
		ob_start();
		DisplayFields::display_fields( $group_id, 'reg', $fields, $attendee_id,[], $data );

		return ob_get_clean();
	}

	public function display_fixed_fields( $group_id, $event_id = null, $translated_event_id = null, $data = array() ) {

		if ( is_null( $event_id ) ) {

			$event_id = get_the_ID();
		}

		$fields = $this->get_fixed_fields( $group_id, $event_id );
		ob_start();
		DisplayFields::display_fields( $group_id, 'fixed', $fields, 0, [], $data );

		return ob_get_clean();
	}

	public function display_fields( $group_id, $event_id = null, $attendee_id = 0,$data = [] ) {

		$fixed_data = isset($data['fixed']) && is_array($data['fixed']) ? $data['fixed'] : [];
		$html = $this->display_fixed_fields( $group_id, $event_id, null, $fixed_data );

		$reg_data = isset($data['reg']) && is_array($data['reg']) ? $data['reg'] : [];
		$html .= $this->display_reg_fields( $group_id, $event_id, $attendee_id, null, $reg_data );

		return $html;
	}

}