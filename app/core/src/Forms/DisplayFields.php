<?php


namespace MEC\Forms;

use MEC\Settings\Settings;

class DisplayFields {

	public static function display_fields( $group_id, $form_type, $fields = null, $j = null, $settings = array(), $data = array() ) {

		if ( !is_array( $fields ) || empty( $fields ) ) {

			return;
		}

		$lock_prefilled = isset( $settings['lock_prefilled'] ) ? $settings['lock_prefilled'] : false;
		?>
		<!-- Custom fields begin -->
		<?php
		foreach ( $fields as $field_id => $field ) :

			if(in_array($field_id, [':i:',':fi:','_i_','_fi_',], true)){

				continue;
			}

			$type = isset( $field['type'] ) ? $field['type'] : false;
			if ( false === $type ) {
				continue;
			}

			$j          = !is_null($j) ? $j : $field_id;
			$field_id = isset($field['key']) && !empty($field['key']) ? $field['key'] : $field_id;
			$html_id  = 'mec_field_' . $group_id . '_' . $type . '_' . $j;
			$required = ( ( isset( $field['required'] ) && $field['required'] ) || ( isset( $field['mandatory'] ) && $field['mandatory'] ) ) ? 'required="required"' : '';
			$field_label = isset($field['label']) ? $field['label'] : null;

			$field_name = strtolower( str_replace( [
					' ',
					',',
					':',
					'"',
					"'",
			], '_', $field_label ) );

			$field_id = strtolower( str_replace( [
				' ',
				',',
				':',
				'"',
				"'",
			], '_', $field_id ) );

			if ( isset( $field['single_row'] ) && 'enable' === $field['single_row'] ) : ?>
				<div class="clearfix"></div>
			<?php endif; ?>

			<?php
			$class = '';
			if ( isset( $field['inline'] ) && 'enable' === $field['inline'] ) {
				$class = ' col-md-6';
			} elseif ( isset( $field['inline_third'] ) && 'enable' === $field['inline_third'] ) {
				$class = ' col-md-4';
			} else {
				$class = ' col-md-12';
			}

			if(is_admin() && !wp_doing_ajax()){

				$class .= ' mec-form-row';
			}
			?>
			<div class="mec-field-<?php echo esc_attr( $field['type'] ); ?> <?php echo $required ? 'mec-reg-mandatory' : ''; ?> <?php echo esc_attr( $class ); ?>" data-field-id="<?php echo esc_attr( $j ); ?>">
				<?php
				global $current_user;
				$attributes = '';
				switch ( $type ) {
					case 'first_name':
						$field_type     = 'text';
						$field_id       = 'first_name';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : esc_html__('First Name', 'modern-events-calendar-lite');
						$value      = $current_user->first_name;
						break;
					case 'last_name':
						$field_type     = 'text';
						$field_id       = 'last_name';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : esc_html__('Last Name', 'modern-events-calendar-lite');
						$value      = $current_user->last_name;
						break;
					case 'mec_email':
						$field_type     = 'email';
						$field_id       = $type;
						$field['label'] = isset( $field['label'] ) ? $field['label'] : esc_html__('Email', 'modern-events-calendar-lite');
						$value          = isset( $current_user->user_email ) ? $current_user->user_email : '';
					case 'email':
						$field_type     = 'email';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : 'Email';
						$value          = isset( $current_user->user_email ) ? $current_user->user_email : '';
						break;
					case 'text':
						$field_type     = 'text';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : '';
						$value          = '';
						break;
					case 'date':
						$field_type     = 'date';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : 'Date';
						$value          = '';
						$class          = 'mec-date-picker';
						$attributes     = ' min="' . esc_attr( date( 'Y-m-d', strtotime( '-100 years' ) ) ) . '" max="' . esc_attr( date( 'Y-m-d', strtotime( '+100 years' ) ) ) . '" onload="mec_add_datepicker()"';
						break;
					case 'file':
						$field_type     = 'file';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : 'File';
						$value          = '';
						break;
					case 'tel':
						$field_type     = 'tel';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : 'Tel';
						$value          = '';
						break;
					case 'textarea':
						$field_type     = 'textarea';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : '';
						$value          = '';
						break;
					case 'select':
						$field_type     = 'select';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : '';
						$value          = '';
						$selected       = '';
						break;
					case 'radio':
					case 'checkbox':
						$field_type = $type;
						$value      = '';
						break;
					case 'agreement':

						break;

				}

				if( 'fixed' === $form_type || ( 'reg' === $form_type && in_array($field_id,['mec_email','first_name','last_name'],true) ) ){

					$field_id = 'mec_email' === $field_id ? 'email' : $field_id;
					$value = isset($data[$field_id]) ? $data[$field_id] : $value;
				} else {

					$value = isset($data[$form_type][$field_id]) ? $data[$form_type][$field_id] : $value;
				}

				$lock_field = !empty( $value );
				$lock_field = ( $lock_field && ( $lock_prefilled == 1 or ( $lock_prefilled == 2 and $j == 1 ) ) ) ? 'readonly' : '';

				if('reg' === $form_type){

					$field_name = 'rsvp[attendees][' . esc_attr($j) . '][' . esc_attr($form_type) . '][' . esc_attr($field_id) . ']';
				}else{

					$field_name = 'rsvp[' . esc_attr($form_type) . '][' . esc_attr($field_id) . ']';
				}
				// Display Label
				if ( isset( $field['label'] ) && !empty( $field['label'] ) && 'agreement' !== $type ) {

					$label_field = '<label for="' . esc_attr( $html_id ) . '" style="display:block" class="' . ( $required ? 'required' : '' ) . '">'
						 . esc_html__( $field['label'], 'modern-events-calendar-lite')
						 . ( $required ? '<span class="wbmec-mandatory">*</span>' : '' )
						 . '</label>';

					echo is_admin() ? '<div class="mec-col-2">'.\MEC_kses::form($label_field).'</div>' : \MEC_kses::form($label_field);
				}

				$input_html = '';
				// Display Input
				switch ( $type ) {
					case 'first_name':
					case 'last_name':
					case 'mec_email':

						$placeholder = ( isset( $field['placeholder'] ) && $field['placeholder'] ) ? esc_html__( $field['placeholder'], 'modern-events-calendar-lite') : esc_html__( $field['label'], 'modern-events-calendar-lite');
						$input_html = '<input id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $class ) . '" type="' . esc_attr( $field_type ) . '" name="rsvp[attendees][' . esc_attr($j) . '][' . esc_attr($type) . ']" value="' . esc_attr(trim( $value )) . '" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . '  />';

						break;
					case 'text':
					case 'date':
					case 'file':
					case 'email':
					case 'tel':

						$placeholder = ( isset( $field['placeholder'] ) && $field['placeholder'] ) ? esc_html__( $field['placeholder'], 'modern-events-calendar-lite') : esc_html__( $field['label'], 'modern-events-calendar-lite');
						$input_html = '<input id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $class ) . '" type="' . esc_attr( $field_type ) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr(trim( $value )) . '" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . '  />';

						break;
					case 'textarea':

						$placeholder = ( isset( $field['placeholder'] ) && $field['placeholder'] ) ? esc_html__( $field['placeholder'], 'modern-events-calendar-lite') : esc_html__( $field['label'], 'modern-events-calendar-lite');
						$input_html = '<textarea id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr(trim( $value )) . '" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . '  ></textarea>';

						break;
					case 'select':

						$placeholder = '';
						$input_html = '<select id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $class ) . '" name="'.esc_attr($field_name).'" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . ' >';
						$rd = 0;
						$selected = $value;
						$options = isset($field['options']) ? $field['options'] : [];
						foreach ( $options as $field_option ) {
							$rd++;
							$option_text  = isset( $field_option['label'] ) ? esc_html__( $field_option['label'], 'modern-events-calendar-lite') : '';
							$option_value = ( $rd === 1 and isset( $field['ignore'] ) and $field['ignore'] ) ? '' : esc_attr__( $field_option['label'], 'modern-events-calendar-lite');

							$input_html .= '<option value="' . esc_attr($option_value) . '" ' . selected( $selected, $option_value, false ) . '>' . esc_html($option_text) . '</option>';
						}
						$input_html .= '</select>';

						break;
					case 'radio':
					case 'checkbox':
						$options = isset($field['options']) ? $field['options'] : [];
						foreach ( $options as $field_option ) {
							$current_value = esc_html__( $field_option['label'], 'modern-events-calendar-lite');
							$checked = in_array($current_value,(array)$value);
							$input_html .= '<label for="' . esc_attr($html_id . $j . '_' . strtolower( str_replace( ' ', '_', $field_option['label'] ) )) . '">'
								 . '<input type="' . esc_attr( $field_type ) . '" id="mec_' . esc_attr($form_type . '_field_' . $type . $j . '_' . $field_id . '_' . strtolower( str_replace( ' ', '_', $field_option['label'] ) )) . '" name="' . esc_attr( $field_name ) . '[]" value="' . esc_attr($current_value) . '" '.checked($checked,true,false).'/>'
								 . esc_html__( $field_option['label'], 'modern-events-calendar-lite')
								 . '</label>';
						}

						break;
					case 'agreement':

						$checked = isset( $field['status'] ) ? $field['status'] : 'checked';
						$input_html = '<label for="' . esc_attr($html_id . $j) . '">'
							 . '<input type="checkbox" id="' . esc_attr($html_id . $j) . '" name="' . esc_attr( $field_name ) . '" value="1" ' . checked( $checked, 'checked', false ) . ' onchange="mec_agreement_change(this);"/>'
							 . ( $required ? '<span class="wbmec-mandatory">*</span>' : '' )
							 . sprintf( esc_html__( stripslashes( $field['label'] ), 'modern-events-calendar-lite'), '<a href="' . get_the_permalink( $field['page'] ) . '" target="_blank">' . get_the_title( $field['page'] ) . '</a>' )
							 . '</label>';

						break;

					case 'p':

						$input_html = '<p>' . do_shortcode( stripslashes( $field['content'] ) ) . '</p>';

						break;
				}

				echo is_admin() ? '<div class="mec-col-2">'.\MEC_kses::form($input_html).'</div>' : \MEC_kses::form($input_html);
				?>
			</div>
		<?php endforeach;

	}

}