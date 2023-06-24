<?php


namespace MEC\BookingForm;

class DisplayFields {

	public static function display_fields( $group_id, $form_type, $fields = null, $j = null, $settings = array(), $data = array() ) {

		if ( !is_array( $fields ) || empty( $fields ) ) {

			return;
		}

		$is_editor = isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ? true : false;
		$is_dashboard = is_admin() && !wp_doing_ajax() && !$is_editor ? true : false;

		$lock_prefilled = isset( $settings['lock_prefilled'] ) ? $settings['lock_prefilled'] : false;

		if( 'reg' === $form_type && 'book' === $group_id ){

			$field_base_name = $group_id . '[tickets][' . esc_attr($j) . ']';
		}elseif( 'bfixed' === $form_type && 'book' === $group_id ){

			$field_base_name = $group_id . '[fields]';
		}else{

			$field_base_name = $group_id . '[' . esc_attr($form_type) . ']';
		}

		?>
		<!-- Custom fields begin -->
		<?php
		foreach ( $fields as $f_id => $field ) :

			if(in_array($f_id, [':i:',':fi:','_i_','_fi_',], true)){

				continue;
			}

			$type = isset( $field['type'] ) ? $field['type'] : false;
			if ( false === $type ) {
				continue;
			}

			$j          = !is_null($j) ? $j : $f_id;
			$field_id = isset($field['key']) && !empty($field['key']) ? $field['key'] : $f_id;
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

			if( !isset( $field['inline'] ) && in_array( $type, array( 'name', 'mec_email' )) ){

				$field['inline'] = 'enable';
			}

			$classes = array();

			$single_row = isset($field['single_row']) && $field['single_row'] == 'enable' ? true : false;
			$full_width = isset($field['full_width']) && $field['full_width'] == 'enable' ? true : false;

			if ( isset( $field['inline'] ) && 'enable' === $field['inline'] ) {
				$classes[] = 'col-md-6';
			} elseif ( isset( $field['inline_third'] ) && 'enable' === $field['inline_third'] ) {
				$classes[] = 'col-md-4';
			} else {
				$classes[] = 'col-md-12'; // 'col-md-6'
			}

			if( $is_dashboard ){

				$classes[] = 'mec-form-row';
			}

			if( isset( $field['mandatory'] ) && $field['mandatory'] ){

				$classes[] = 'mec-reg-mandatory';
			}

			if( $single_row ){

				$classes[] = 'clearfix';
			}
			?>
			<li class="mec-<?php echo esc_attr( $group_id ); ?>-field-<?php echo esc_attr( $field['type'] ); ?> mec-<?php echo esc_attr( $group_id ); ?>-<?php echo esc_attr($form_type); ?>-field-<?php echo esc_attr( $field['type'] ); ?> <?php echo esc_attr( join( ' ', $classes ) ); ?>" data-field-id="<?php echo esc_attr( $f_id ); ?>" data-ticket-id="<?php echo esc_attr($j); ?>">
				<?php
				global $current_user;
				$attributes = '';
				$has_icon = false;
				$class = '';
				switch ( $type ) {
					case 'name':
						$field_type     = 'text';
						$field_id       = 'name';
						$field['label'] = isset( $field['label'] ) ? $field['label'] : esc_html__('Last Name', 'modern-events-calendar-lite');
						$value      	= $current_user->first_name . ' ' . $current_user->last_name;
						$has_icon 		= isset( $field['has_icon'] ) ? $field['has_icon'] : true;
						$icon_content 	= \MEC\Base::get_main()->svg('form/user-icon');
						break;
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
						$value      	= $current_user->last_name;
						break;
					case 'mec_email':
						$field_type     = 'email';
						$field_id       = $type;
						$field['label'] = isset( $field['label'] ) ? $field['label'] : esc_html__('Email', 'modern-events-calendar-lite');
						$value          = isset( $current_user->user_email ) ? $current_user->user_email : '';
						$has_icon 		= isset( $field['has_icon'] ) ? $field['has_icon'] : true;
						$icon_content 	= \MEC\Base::get_main()->svg('form/email-icon');
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

						$value = '';
						break;

				}

				$primary_field_ids = [
					'mec_email',
					'name',
					'first_name',
					'last_name'
				];
				$primary_field_id = $field_id;
				if( 'fixed' === $form_type || ( 'reg' === $form_type && in_array($field_id, $primary_field_ids ,true) ) ){

					$field_id = 'mec_email' === $field_id ? 'email' : $field_id;
					$value = isset($data[$field_id]) ? $data[$field_id] : $value;
				} else {

					$value = isset($data[$form_type][$field_id]) ? $data[$form_type][$field_id] : $value;
				}

				$lock_field = !empty( $value );
				$lock_field = ( $lock_field && ( $lock_prefilled == 1 or ( $lock_prefilled == 2 and $j == 1 ) ) ) ? 'readonly' : '';

				if( 'reg' === $form_type && !in_array($primary_field_id,$primary_field_ids,true) )  {

					$field_name = $field_base_name . '[reg][' . esc_attr($field_id) . ']';
				}else{

					$field_name = $field_base_name . '[' . esc_attr($field_id) . ']';
				}

				// Display Label
				if ( isset( $field['label'] ) && !empty( $field['label'] ) && 'agreement' !== $type ) {

					$label_field = '<label for="' . esc_attr( $html_id ) . '" style="display:block" class="' . ( $required ? 'required' : '' ) . '">'
						 . esc_html__( $field['label'], 'modern-events-calendar-lite')
						 . ( $required ? '<span class="wbmec-mandatory">*</span>' : '' )
						 . '</label>';

					echo $is_dashboard ? '<div class="mec-col-2">'.\MEC_kses::form($label_field).'</div>' : \MEC_kses::form($label_field);
				}

				$input_html = '';
				$field_class = $class;
				// Display Input
				switch ( $type ) {
					case 'name':
					case 'first_name':
					case 'last_name':
					case 'mec_email':

						$placeholder = ( isset( $field['placeholder'] ) && $field['placeholder'] ) ? esc_html__( $field['placeholder'], 'modern-events-calendar-lite') : esc_html__( $field['label'], 'modern-events-calendar-lite');
						$input_html = '<input id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $field_class ) . '" type="' . esc_attr( $field_type ) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr(trim( $value )) . '" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . '  />';

						break;
					case 'text':
					case 'date':
					case 'file':
					case 'email':
					case 'tel':

						$placeholder = ( isset( $field['placeholder'] ) && $field['placeholder'] ) ? esc_html__( $field['placeholder'], 'modern-events-calendar-lite') : esc_html__( $field['label'], 'modern-events-calendar-lite');
						$input_html = '<input id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $field_class ) . '" type="' . esc_attr( $field_type ) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr(trim( $value )) . '" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . '  />';

						break;
					case 'textarea':

						$placeholder = ( isset( $field['placeholder'] ) && $field['placeholder'] ) ? esc_html__( $field['placeholder'], 'modern-events-calendar-lite') : esc_html__( $field['label'], 'modern-events-calendar-lite');
						$input_html = '<textarea id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $field_class ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr(trim( $value )) . '" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . '  ></textarea>';

						break;
					case 'select':

						$placeholder = '';
						$input_html = '<select id="' . esc_attr( $html_id ) . '" class="' . esc_attr( $field_class ) . '" name="'.esc_attr($field_name).'" placeholder="' . esc_attr( $placeholder ) . '" ' . $required . '  ' . $lock_field . '  ' . $attributes . ' >';
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
						$options = isset($field['options']) ? $field['options'] : [];
						foreach ( $options as $field_option ) {
							$current_value = esc_html__( $field_option['label'], 'modern-events-calendar-lite');
							$checked = in_array($current_value,(array)$value);
							$input_html .= '<label>'
								 . '<input type="' . esc_attr( $field_type ) . '" id="mec_' . esc_attr($form_type . '_field_' . $type . $j . '_' . $field_id . '_' . strtolower( str_replace( ' ', '_', $field_option['label'] ) )) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr($current_value) . '" '.checked($checked,true,false).'/>'
								 . esc_html__( $field_option['label'], 'modern-events-calendar-lite')
								 . '</label>';
						}

						break;

					case 'checkbox':
						$options = isset($field['options']) ? $field['options'] : [];
						foreach ( $options as $field_option ) {
							$current_value = esc_html__( $field_option['label'], 'modern-events-calendar-lite');
							$checked = in_array($current_value,(array)$value);
							$input_html .= '<label>'
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

				if( !empty( $has_icon ) ) {

					$wrapper_class = "mec-{$group_id}-{$type}-field-wrapper";
					$icon_class = "mec-{$group_id}-{$type}-field-icon";
					$input_html = '<span class="mec-field-wrapper '. $wrapper_class .'">'
					 	. '<span class="mec-field-icon '. $icon_class .'">' . $icon_content .' </span>'
						. $input_html
					.'</span>';
				}
				echo $is_dashboard ? '<div class="mec-col-2">'.\MEC_kses::form($input_html).'</div>' : \MEC_kses::form($input_html);
				?>
			</li>
		<?php endforeach;

	}

}