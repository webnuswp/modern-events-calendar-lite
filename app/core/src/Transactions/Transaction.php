<?php

namespace MEC\Transactions;

class Transaction {

	/**
	 * Transaction Data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Transaction ID
	 *
	 * @var string
	 */
	private $transaction_id;

	/**
	 * Tickets details
	 *
	 * @var array
	 */
	private $tickets_details;

	/**
	 * Use cache
	 *
	 * @var bool
	 */
	private $use_cache;

	/**
	 * Price details
	 *
	 * @var array
	 */
	private $price_details;

	/**
	 * Ticket variations status
	 *
	 * @var bool
	 */
	private $ticket_variations_status;

	/**
	 * Fee taxes status
	 *
	 * @var bool
	 */
	private $taxes_fees_status;

	/**
	 * Book object class
	 *
	 * @var \MEC_book
	 */
	private $bookClass;

	/**
	 * Gateways
	 *
	 * @var \MEC_book
	 */
	private $gateways;

	/**
	 * Gateways options
	 *
	 * @var \MEC_book
	 */
	private $gateways_options;

	/**
	 * Event tickets
	 *
	 * @var array
	 */
	private $event_tickets;

	/**
	 * Constructor
	 *
	 * @param string $transaction_id
	 * @param null|array $data
	 */
	public function __construct( $transaction_id, $data = null ) {

		$this->bookClass = new \MEC_book();
		$this->gateways = $this->get_gateways();
		$this->gateways_options = \MEC\Base::get_main()->get_gateways_options();

		$this->transaction_id = $transaction_id ? $transaction_id : static::generate_transaction_id();

		$this->ticket_variations_status = (bool)\MEC\Settings\Settings::getInstance()->get_settings('ticket_variations_status');
		$this->taxes_fees_status = (bool)\MEC\Settings\Settings::getInstance()->get_settings('taxes_fees_status');

		if( !is_null( $data ) && is_array( $data ) ) {

			$this->set_data( $data );
		}else{

			$this->set_data(
				$this->get_saved_data()
			);
		}
	}

	public static function generate_transaction_id() {

		$method = \MEC\Settings\Settings::getInstance()->get_settings('booking_tid_gen_method');
        $method = !empty( $method ) ? $method : 'random';

        $string = str_shuffle('ABCDEFGHJKLMNOPQRSTUVWXYZ');
        $prefix = substr($string, 0, 3);

        if($method === 'ordered') {

			$start = \MEC\Settings\Settings::getInstance()->get_settings('booking_tid_start_from');
            $start = ( !empty( $start ) && is_numeric( $start ) && $start >= 1 ) ? $start : 10000;
            $existing = get_option('mec_tid_current', 1);

            $number = max($start, $existing)+1;

            $key = $prefix.$number;
            update_option('mec_tid_current', $number);
        } else {

            $key = $prefix.mt_rand(10000, 99999);
        }

        // If the key exist then generate another key
        if(get_option($key, false) !== false) $key = static::generate_transaction_id();

        return $key;
    }

	public function prepare_data( $data ) {

		$event_id = $data['event_id'] ?? $this->get_event_id();
		$book_id = $this->bookClass->get_book_id_transaction_id( $this->transaction_id );
		$gateway = $book_id ? get_post_meta( $book_id, 'mec_gateway', true ) : false;
		$is_partial_payment = $book_id ? false : $this->bookClass->partial_payment->is_enabled();

		return wp_parse_args(
			$data,
			array(
				'tickets' => array(),
				'first_for_all' => false,
				'event_id' => 0,
				'translated_event_id' => 0,
				'date' => '',
				'all_dates' => array(),
				'timestamps' => array(),
				'booking_id' => $book_id,
				// 'price_details' => array(),
				'total' => 0,
				'discount' => 0,
				'price' => 0,
				'payable' => 0,
				'coupon' => '',
				'fields' => array(),
				'gateway' => $gateway ? $gateway : 'MEC_gateway_pay_locally',

				'order_id' => '',
				'WCTax' => '',
				'wc_coupons' => array(),
				'wc_discounts' => array(),

				'is_partial_payment' => $is_partial_payment ? 1 : 0,
				'partial_payment_settings' => $is_partial_payment ? $this->bookClass->partial_payment->get_validated_payable_options( $event_id ) : array(),
			)
		);
	}

	/**
	 * Return saved data
	 *
	 * @return array
	 */
	private function get_saved_data() {

		$data = get_option( $this->transaction_id, array() );

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Update saved data
	 *
	 * @param bool $reset_cache
	 *
	 * @return string
	 */
	public function update_data( $reset_cache = true ) {

		update_option( $this->transaction_id, $this->data, false );

		if( $reset_cache ) {

			$this->update_cache_tickets_details();
		}

		return $this->transaction_id;
	}

	/**
	 * Return data
	 *
	 * @return array
	 */
	public function get_data() {

		return $this->data;
	}

	public function set_data( $data ) {

		$this->data = $this->prepare_data( $data );
	}

	public function get_ticket_variations_status() {

		if( 'MEC_gateway_woocommerce' == $this->get_gateway() ) {

			return false;
		}

		return $this->ticket_variations_status;
	}

	public function get_taxes_fees_status() {

		return $this->taxes_fees_status;
	}

	public function get_event_tickets() {

		if( is_null( $this->event_tickets ) ) {

			$event_tickets = get_post_meta( $this->get_event_id(), 'mec_tickets', true );
			$this->event_tickets = is_array( $event_tickets ) ? $event_tickets : array();
		}

		return $this->event_tickets;
	}

	public function get_all_tickets_details( $return_cached = true ) {

		if( is_null( $this->tickets_details ) || !$return_cached ) {

			$this->tickets_details = $this->get_all_occurrences_tickets_details( $return_cached );
		}

		return $this->tickets_details;
	}

	public function get_cached_tickets_details() {

		return get_option( $this->transaction_id . '_cached', false );
	}

	public function update_cache_tickets_details( $data = null ) {

		if( is_null( $data ) ) {

			$data = $this->get_all_tickets_details( false );
		}

		update_option( $this->transaction_id . '_cached', $data, false );
	}

	public function reset_cache_tickets_details() {

		$this->tickets_details = null;

		delete_option( $this->transaction_id . '_cached' );
	}

	public function _get_total() {

		return $this->data['total'] ?? '';
	}

	public function _get_payable() {

		return $this->data['payable'] ?? '';
	}

	public function get_event_id() {

		return $this->data['event_id'] ?? 0;
	}

	public function is_first_for_all() {

		return (bool)( $this->data['first_for_all'] ?? 0 );
	}

	public function get_translated_event_id() {

		return $this->data['translated_event_id'] ?? 0;
	}

	public function get_dates() {

		$all_dates = isset( $this->data['all_dates'] ) && is_array( $this->data['all_dates'] ) && !empty( $this->data['all_dates'] ) ? $this->data['all_dates'] : array( $this->data['date'] ?? '' );

		foreach( $all_dates as $k => $timestamps ){

			if( empty( $timestamps ) || is_array( $timestamps ) ) {

				unset( $all_dates[ $k ] );
			}
		}

        return array_values( $all_dates );
	}

	public function get_date() {

		$all_dates = $this->get_dates();

		return current( $all_dates );
	}

	public function get_invoice_key() {

		return $this->data['invoice_key'] ?? '';
	}

	public function get_book_id() {

		return $this->data['booking_id'] ?? $this->bookClass->get_book_id_transaction_id( $this->transaction_id );
	}

	public function get_author_id() {

		$book_id = $this->get_book_id();
		if( !$book_id ) {

			return get_current_user_id();
		}

		$book = get_post( $book_id );

		return is_a( $book, '\WP_Post' ) ? $book->post_author : 0;
	}

	public function get_gateway() {

		return $this->data['gateway'] ?? '';
	}

	public function get_gateways() {

		return array (
			'MEC_gateway_pay_locally' => (object) array(
			   'key' => 'MEC_gateway_pay_locally',
			   'label' => __('Pay Locally', 'modern-events-calendar-lite'),
			   'id' => 1,
			),
			'MEC_gateway_paypal_express' => (object) array(
			   'key' => 'MEC_gateway_paypal_express',
			   'label' => __('PayPal Express', 'modern-events-calendar-lite'),
			   'id' => 2,
			),
			'MEC_gateway_woocommerce' =>
			(object) array(
			   'key' => 'MEC_gateway_woocommerce',
			   'label' => __('Pay by WooCommerce', 'modern-events-calendar-lite'),
			   'id' => 6,
			),
			'MEC_gateway_paypal_credit_card' => (object) array(
			   'key' => 'MEC_gateway_paypal_credit_card',
			   'label' => __('PayPal Credit Card', 'modern-events-calendar-lite'),
			   'id' => 3,
			),
			'MEC_gateway_stripe' => (object) array(
			   'key' => 'MEC_gateway_stripe',
			   'label' => __('Stripe', 'modern-events-calendar-lite'),
			   'id' => 5,
			),
			'MEC_gateway_stripe_connect' => (object) array(
			   'key' => 'MEC_gateway_stripe_connect',
			   'label' => __('Stripe Connect', 'modern-events-calendar-lite'),
			   'id' => 7,
			),
			'MEC_gateway_bank_transfer' => (object) array(
			   'key' => 'MEC_gateway_bank_transfer',
			   'label' => __('Bank Transfer', 'modern-events-calendar-lite'),
			   'id' => 8,
			),
			'MEC_gateway_paypal_standard' => (object) array(
			   'key' => 'MEC_gateway_paypal_standard',
			   'label' => __('PayPal Standard', 'modern-events-calendar-lite'),
			   'id' => 9,
			),
			'MEC_gateway_add_to_woocommerce_cart' => (object) array(
			   'key' => 'MEC_gateway_add_to_woocommerce_cart',
			   'label' => __('Add to cart', 'modern-events-calendar-lite'),
			   'id' => 1995,
			),
		);
	}

	public function get_gateway_number() {

		$gateway_number = '';
		$gateway = $this->get_gateway();
		$current_gateway = $this->gateways[ $gateway ] ?? false;
		if( $current_gateway && isset( $current_gateway->id ) ) {

			$gateway_number = $current_gateway->id;
		}

		return $gateway_number;
	}

	public function get_gateway_label( $gateway = null ) {

		$gateway_label = '';
		if( is_null( $gateway ) ){

			$gateway = $this->get_gateway();
		}

		$current_gateway = $this->gateways[ $gateway ] ?? false;
		if( $current_gateway && isset( $current_gateway->id ) ) {

			$gateway_label = $current_gateway->label;
		}

		return $gateway_label;
	}

	public function get_tickets() {

		return $this->data['tickets'] ?? array();
	}

	public function get_coupon() {

		return $this->data['coupon'] ?? '';
	}

	public function get_wc_coupons() {

		$coupons = $this->data['wc_coupon'] ?? array();

		return is_array( $coupons ) ? $coupons : explode( ',', $coupons );
	}

	public function get_wc_discounts() {

		$discounts = $this->data['wc_discounts'] ?? array();

		return is_array( $discounts ) ? $discounts : array();
	}

	public function get_order_id() {

		return $this->data['order_id'] ?? 0;
	}

	public function is_wc(){

		return $this->get_order_id() ? true : false;
	}

	public function get_discount() {

		return $this->data['discount'] ?? '';
	}

	public function get_fixed_fields() {

		return $this->data['fields'] ?? array();
	}

	public function is_partial_payment() {

		$is_partial_payment = $this->data['is_partial_payment'] ?? false;

		return (bool) $is_partial_payment;
	}

	public function get_partial_payment_settings() {

		$settings = $this->data['partial_payment_settings'] ?? array();

		return is_array( $settings ) ? $settings : array();
	}


	public function get_ticket_price( $ticket, $start_timestamp ) {

		return $this->bookClass->get_ticket_regular_price_key(
			$ticket,
			current_time('Y-m-d'),
			$this->get_event_id(),
			'price',
			$start_timestamp
		);
	}

	public function get_ticket_variations_details( $ticket ) {

		$variation_details = array();
		if( ! $this->get_ticket_variations_status() ) {

			return $variation_details;
		}

		$event_id = $this->get_event_id();
		$ticket_id = $ticket['id'] ?? 0;
		$variations = $ticket['variations'] ?? array();

		if( is_array( $variations ) && count( $variations ) ) {

			$ticket_variations = \MEC\Base::get_main()->ticket_variations( $event_id, $ticket_id );

			foreach( $ticket_variations as $key => $ticket_variation ) {

				if(!is_numeric($key)) continue;
				if(!isset($ticket_variation['title']) or (isset($ticket_variation['title']) and !trim($ticket_variation['title']))) continue;

				$variation_count = isset($variations[$key]) ? $variations[$key] : 0;
				if(!$variation_count or ($variation_count and $variation_count < 0)) continue;
				$v_price = (isset($ticket_variation['price']) and trim($ticket_variation['price']) != '') ? $ticket_variation['price'] : 0;

				$variation_amount = $v_price * $variation_count;
				$variation_title = $ticket_variation['title'].' ('.esc_html($variation_count).')';

				// Price Details
				if(!isset($variation_details[$key])) {

					$variation_details[ $key ] = array(
						'variation_key' => $key,
						'price' => $v_price,
						'amount'=> $variation_amount,
						'description'=> __( $variation_title, 'modern-events-calendar-lite'),
						'type'=> 'variation',
						'count' => $variation_count
					);
				} else {

					$variation_details[$key]['amount'] += $variation_amount;

					$new_count = ((int) $variation_details[$key]['count'] + $variation_count);
					$variation_details[$key]['count'] = $new_count;
					$variation_details[$key]['description'] = esc_html__($ticket_variation['title'].' ('.$new_count.')', 'modern-events-calendar-lite');
				}
			}
		}

		return $variation_details;
	}

	public function get_gateway_options() {

		$gateway_number = $this->get_gateway_number();

		return $this->gateways_options[ $gateway_number ] ?? array();
	}

	public function is_disabled_fees_for_gateway() {

		$fees_disabled_gateways = \MEC\Settings\Settings::getInstance()->get_settings( 'fees_disabled_gateways' );
		$fees_disabled_gateways = is_array( $fees_disabled_gateways ) ? $fees_disabled_gateways : array();
		$gateway_number = $this->get_gateway_number();

		if( 'MEC_gateway_woocommerce' == $this->get_gateway() ) {

			return true;
		}

		if( in_array( $gateway_number, $fees_disabled_gateways ) ) {

			return true;
		}

		return false;
	}

	public function get_order( $order_id ) {

		if( !$order_id ) {

			return false;
		}

		if( function_exists( 'wc_get_order' ) ) {

			$order = wc_get_order( $order_id );
			return is_a( $order, '\WC_Order' ) ? $order : false;
		}

		return false;
	}

	public function get_order_fees() {

		$wc_fees = [];
		$prices_include_tax = 'yes' === get_option( 'woocommerce_prices_include_tax', 'no' );
        if( $prices_include_tax ) {

			return $wc_fees;
		}

		$order_id = $this->get_order_id();
		$order = $this->get_order( $order_id );
		if( $order ) {

			foreach ($order->get_tax_totals() as $key => $tax) {

				$tax_value = \WC_Tax::get_rate_percent_value($tax->rate_id);
				$wc_fees[ 'wc_'. $key ] = [
					'title' => 'WooCommerce ' . $tax->label,
					'amount' => $tax_value,
					'type' => 'amount',
					'type2' => 'wc',
				];
			}
		}

		return $wc_fees;
	}

	public function get_event_fees() {

		$fees = array();
		$event_id = $this->get_event_id();
		$mec_fees = $this->bookClass->get_fees( $event_id );

		$disabled_fees_for_gateway = $this->is_disabled_fees_for_gateway();

		$gateway = $this->get_gateway();
		$gateway_options = $this->get_gateway_options();

		$can_use_mec_fees = $disabled_fees_for_gateway;

		switch( $gateway ) {

			case 'MEC_gateway_add_to_woocommerce_cart':

				$use_wc_taxes = isset( $gateway_options['use_woo_taxes'] ) && 'on' == $gateway_options['use_woo_taxes'] ? true : false;
				$use_mec_taxes = isset( $gateway_options['use_mec_taxes'] ) && 'on' == $gateway_options['use_mec_taxes'] ? true : false;

				$can_use_woo_fees = $use_wc_taxes;
				$can_use_mec_fees = ( !$disabled_fees_for_gateway && $use_mec_taxes );
				break;
			default:

				$can_use_mec_fees = $disabled_fees_for_gateway;
				$can_use_woo_fees = false;
				break;

		}

		if( !$can_use_mec_fees ) {

			$fees = array_merge_recursive( $fees, $mec_fees );
		}

		if( $can_use_woo_fees ) {

			$order_fees = $this->get_order_fees();
			$fees = array_merge_recursive( $fees, $order_fees );
		}

		return apply_filters( 'mec_transaction_get_event_fees', $fees, $event_id, $this, $mec_fees );
	}

	public function get_ticket_fees_details( $ticket, $total_tickets_count, $total_tickets_dates ) {

		$fee_details = array();
		if( ! $this->get_taxes_fees_status() ) {

			return $fee_details;
		}

		$tickets_amount = $ticket['tickets_amount'] ?? 0;
		$tickets_count = $ticket['count'] ?? 1;
		$variations_amount = $ticket['variations_amount'] ?? 0;

		$fees = $this->get_event_fees();

		foreach( $fees as $key => $fee ) {

			$fee_amount = 0;
			if(!is_numeric($key)) continue;

			$type = $fee['type'] ?? '';
			switch( $type ) {
				case 'amount_per_date':

					$fee_amount = $fee['amount'] * $total_tickets_dates;
					$fee_amount = $fee_amount / $total_tickets_count;
					break;
				case 'percent': // per ticket

					$fee_amount = ( ( $tickets_amount + $variations_amount ) * $fee['amount'] ) / 100;
					break;
				case 'amount': // per ticket

					$fee_amount = $tickets_count * $fee['amount'];
					break;
				case 'amount_per_booking':

					$fee_amount = $fee['amount'];
					$fee_amount = $fee_amount / $total_tickets_count;
					break;
			}

			// Price Details
			if( ! isset($fee_details[ $key ] ) ) {

				$fee_details[ $key ] = array(
					'fee_key' => $key,
					'amount'=> $fee_amount,
					'description'=>__($fee['title'], 'modern-events-calendar-lite'),
					'type'=>'fee',
					'fee_type'=> $fee['type'],
					'fee_amount'=> $fee['amount']
				);
			} else {

				$fee_details[ $key ]['amount'] += $fee_amount;
			}
		}

		return $fee_details;
	}

	public function get_ticket_order_discounts_details( $ticket, $total_tickets_amount_with_variations ) {

		$discounts_details = array();
		$ticket_price = $ticket['ticket_price'] ?? 0;
		$ticket_variations_amount = $ticket['variations_amount'] ?? 0;
		$ticket_count = $ticket['count'] ?? 0;

		$order_id = $this->get_order_id();
		$order = $this->get_order( $order_id );
		if ( $order ) {

			$coupons = $order->get_coupon_codes();

			foreach( $coupons as $coupon_code ) {

				$coupon = new \WC_Coupon( $coupon_code );
				$coupon_discount_type = $coupon->get_discount_type();
                $coupon_discount = $coupon->get_amount();

				$discount_ticket_amount = 0;
				$discount_ticket_variation_amount = 0;
				$total_ticket_discount = 0;
				if ( 'percent' === $coupon_discount_type ) {

					$discount_ticket_amount = ($ticket_price * $coupon_discount) / 100;
					$discount_ticket_variation_amount = ($ticket_variations_amount * $coupon_discount) / 100;
					$total_ticket_discount = $discount_ticket_amount + $discount_ticket_variation_amount;
				} else {

					$percent = ($ticket_price * 100) / $total_tickets_amount_with_variations;
					$discount_ticket_amount = ($coupon_discount * $percent) / 100;
					$discount_ticket_variation_amount = $ticket_variations_amount * $percent / 100;
					$total_ticket_discount = $discount_ticket_amount + $discount_ticket_variation_amount;
				}

				if( ! isset( $discounts_details[ "wc_coupon_{$coupon_code}" ] ) ) {

					$discounts_details[ "wc_coupon_{$coupon_code}" ] = array(
						'discount_key' => "wc_coupon_{$coupon_code}",
						'ticket_amount' => $discount_ticket_amount,
						'tickets_amount' => $discount_ticket_amount * $ticket_count,
						'variation_amount' => $discount_ticket_variation_amount,
						'variations_amount' => $discount_ticket_variation_amount * $ticket_count,
						'amount'=> $total_ticket_discount,
						'fee' => $total_ticket_discount * $ticket_count,
						'description'=> esc_html__( 'Discount by WC Coupon', 'modern-events-calendar-lite'),
						'coupon_code' => $coupon_code,
						'type'=> 'wc_coupon',
						'discount_type'=> $coupon_discount_type,
						'discount_amount'=> $coupon_discount,
					);
				} else {

					$discounts_details[ "wc_coupon_{$coupon_code}" ]['amount'] += $total_ticket_discount;
				}
			}
        }

		return $discounts_details;
	}

	public function get_ticket_discounts_details( $ticket, $total_tickets_amount_with_variations ) {

		if( 'MEC_gateway_woocommerce' == $this->get_gateway() ) {

			return $this->get_ticket_order_discounts_details( $ticket, $total_tickets_amount_with_variations );
		}

		$discounts_details = array();
		$ticket_price = $ticket['ticket_price'] ?? 0;
		$ticket_variations_amount = $ticket['variations_amount'] ?? 0;
		$ticket_count = $ticket['count'] ?? 0;

		$event_id = $this->get_event_id();
		$booking_options = get_post_meta( $event_id, 'mec_booking', true );
        if( !is_array( $booking_options ) ) {

			$booking_options = array();
		}

		// User Discount
		$user_id = $this->get_author_id();
		if( $user_id ) {
			// User
			$user = get_user_by( 'id', $user_id );

			$roles = is_a( $user, '\WP_User' ) && isset( $user->roles ) ? (array)$user->roles : array();

			$loggedin_discount = (isset($booking_options['loggedin_discount']) ? $booking_options['loggedin_discount'] : 0);
			$role_discount = $loggedin_discount;

			foreach( $roles as $key => $role ) {

				// If role discount is higher than the preset role OR a previous roles discount, set it to the new higher discount
				if(
					isset($booking_options['roles_discount_'.$role])
					&& is_numeric($booking_options['roles_discount_'.$role])
					&& $booking_options['roles_discount_'.$role] > $role_discount
					){

					$role_discount = $booking_options['roles_discount_'.$role];
				}
			}

			$discount_ticket_amount = 0;
			$discount_ticket_variation_amount = 0;
			if( trim( $role_discount ) and is_numeric( $role_discount ) ){

				$discount_ticket_amount = $ticket_price * $role_discount / 100;
				// $discount_ticket_variation_amount = $ticket_variations_amount * $role_discount / 100;
				$total_ticket_discount = $discount_ticket_amount + $discount_ticket_variation_amount;
				if( ! isset( $discounts_details[ 'roles_discount' ] ) ) {

					$discounts_details[ 'roles_discount' ] = array(
						'discount_key' => 'roles_discount',
						'ticket_amount' => $discount_ticket_amount,
						'tickets_amount' => $discount_ticket_amount * $ticket_count,
						'variation_amount' => $discount_ticket_variation_amount,
						'variations_amount' => $discount_ticket_variation_amount * $ticket_count,
						'amount'=> $total_ticket_discount * $ticket_count,
						'fee' => $total_ticket_discount,
						'description'=>__( 'User Discount', 'modern-events-calendar-lite'),
						'type'=> 'roles_discount',
						'discount_type'=> 'roles_discount',
						'discount_amount'=> $role_discount,
					);
				} else {

					// $discounts_details[ 'roles_discount' ]['amount'] += $fee_amount;
				}
			}
		}

		// MEC Coupon Discount
		$coupon = $this->get_coupon();
		if ( $coupon ) {

            $term = get_term_by( 'name', $coupon, 'mec_coupon' );
            $coupon_id = isset($term->term_id) ? $term->term_id : 0;

            if ( $coupon_id ) {

                $coupon_discount_type = get_term_meta($coupon_id, 'discount_type', true);
                $coupon_discount = get_term_meta($coupon_id, 'discount', true);

				$discount_ticket_amount = 0;
				$discount_ticket_variation_amount = 0;
				$total_ticket_discount = 0;
				if ( 'percent' === $coupon_discount_type ) {

					$discount_ticket_amount = ($ticket_price * $coupon_discount) / 100;
					$discount_ticket_variation_amount = ($ticket_variations_amount * $coupon_discount) / 100;
					$total_ticket_discount = $discount_ticket_amount + $discount_ticket_variation_amount;
				} else {

					$percent = ($ticket_price * 100) / $total_tickets_amount_with_variations;
					$discount_ticket_amount = ($coupon_discount * $percent) / 100;
					$discount_ticket_variation_amount = $ticket_variations_amount * $percent / 100;
					$total_ticket_discount = $discount_ticket_amount + $discount_ticket_variation_amount;
				}

				if( ! isset( $discounts_details[ 'coupon_discount' ] ) ) {

					$discounts_details[ 'coupon_discount' ] = array(
						'discount_key' => 'coupon_discount',
						'ticket_amount' => $discount_ticket_amount,
						'tickets_amount' => $discount_ticket_amount * $ticket_count,
						'variation_amount' => $discount_ticket_variation_amount,
						'variations_amount' => $discount_ticket_variation_amount * $ticket_count,
						'amount'=> $total_ticket_discount,
						'fee' => $total_ticket_discount * $ticket_count,
						'description'=>__( 'Discount', 'modern-events-calendar-lite'),
						'type'=> 'coupon_discount',
						'discount_type'=> $coupon_discount_type,
						'discount_amount'=> $coupon_discount,
					);
				} else {

					$discounts_details[ 'coupon_discount' ]['amount'] += $total_ticket_discount;
				}
            }
        }

		// WC Coupon Discount
		$wc_discounts = $this->get_ticket_order_discounts_details( $ticket, $total_tickets_amount_with_variations );
		$discounts_details = array_merge_recursive( $discounts_details, $wc_discounts );

		return $discounts_details;
	}

	public function get_all_occurrences_tickets_details( $return_cached = true ) {

		if( $return_cached ) {

			$cached_data = $this->get_cached_tickets_details();
			if( $cached_data ) {

				return $cached_data;
			}
		}

		$timestamps = $this->get_dates();
		$event_tickets = $this->get_event_tickets();
		$saved_tickets = $this->get_tickets();
		$removed_tickets = $this->data['removed_tickets'] ?? array();

		$tickets = array();
		$row_id = 0;
		foreach( $timestamps as $timestamp ) {

			$timestamp_ex = explode(':', $timestamp);
			$start_timestamp = $timestamp_ex[0];
			$end_timestamp = $timestamp_ex[1];

			if( !is_numeric( $start_timestamp ) || !$start_timestamp ){

				continue;
			}

			foreach( $saved_tickets as $key => $ticket ) {

				$is_removed_ticket = $removed_tickets[ $key ][ $timestamp ] ?? false;
				if( $is_removed_ticket ) {

					continue;
				}

				$ticket_id = $ticket['id'] ?? null;
				$ticket_count = $ticket['count'] ?? 1;
				$attendee_key = $key;

				if( is_null( $ticket_id ) ) {

					continue;
				}

				$t_price = 0;
				if( isset( $event_tickets[ $ticket_id ]['price'] ) ){

					$t_price = $this->get_ticket_price(
						$event_tickets[ $ticket_id ],
						$start_timestamp
					);
				}

				if( !is_numeric( $t_price ) ) {

					$t_price = 0;
				}

				$tickets_amount = $t_price * $ticket_count;

				$variations = $this->get_ticket_variations_details( $ticket );
				$variations_amount = array_sum(
					array_column( $variations, 'amount' )
				);


				$ticket_info = $event_tickets[ $ticket_id ] ?? array();

				$ticket_start_hour = isset($ticket_info['ticket_start_time_hour']) ? $ticket_info['ticket_start_time_hour'] : 8;
				$ticket_start_minute = isset($ticket_info['ticket_start_time_minute']) ? $ticket_info['ticket_start_time_minute'] : 0;
				$ticket_start_ampm = isset($ticket_info['ticket_start_time_ampm']) ? $ticket_info['ticket_start_time_ampm'] : 'AM';
				$ticket_end_hour = isset($ticket_info['ticket_end_time_hour']) ? $ticket_info['ticket_end_time_hour'] : 6;
				$ticket_end_minute = isset($ticket_info['ticket_end_time_minute']) ? $ticket_info['ticket_end_time_minute'] : 0;
				$ticket_end_ampm = isset($ticket_info['ticket_end_time_ampm']) ? $ticket_info['ticket_end_time_ampm'] : 'PM';

				$ticket_start_minute_s = $ticket_start_minute;
				$ticket_end_minute_s = $ticket_end_minute;

				if($ticket_start_minute == '0') $ticket_start_minute_s = '00';
				if($ticket_start_minute == '5') $ticket_start_minute_s = '05';
				if($ticket_end_minute == '0') $ticket_end_minute_s = '00';
				if($ticket_end_minute == '5') $ticket_end_minute_s = '05';

				$ticket_start_seconds = \MEC\Base::get_Main()->time_to_seconds(
					\MEC\Base::get_Main()->to_24hours($ticket_start_hour, $ticket_start_ampm),
					$ticket_start_minute_s
				);

				$ticket_end_seconds = \MEC\Base::get_Main()->time_to_seconds(
					\MEC\Base::get_Main()->to_24hours($ticket_end_hour, $ticket_end_ampm),
					$ticket_end_minute_s
				);

				$ticket_limit = $ticket_info['limit'] ?? '';
				$tickets_amount_with_variations = $tickets_amount + $variations_amount;

				$ticket['row_id'] = ++$row_id;
				$ticket['attendee_key'] = $attendee_key;
				$ticket['ticket_id'] = $event_tickets[ $ticket_id ]['id'] ?? '';
				$ticket['ticket_name'] = $event_tickets[ $ticket_id ]['name'] ?? '';
				$ticket['ticket_price'] = $t_price;
				$ticket['tickets_amount'] = $tickets_amount;
				$ticket['tickets_amount_with_variations'] = $tickets_amount_with_variations;
				$ticket['partial_payment_tickets_amount_with_variations'] =  $this->get_partial_payment_amount( $tickets_amount_with_variations );
				$ticket['ticket_limit'] = $ticket_limit;
				$ticket['variations_amount'] = $variations_amount;
				$ticket['variations_details'] = $variations;
				$ticket['date'] = $timestamp;
				$ticket['product_id'] = $ticket[ 'product_ids' ][ $timestamp ] ?? 0;

				//TODO: check
				$ticket_date_timestamp_day_start = strtotime( date( 'Y-m-d', $start_timestamp ) );
				$ticket_date_timestamp_day_end = strtotime( date( 'Y-m-d', $end_timestamp ) );
				$ticket['ticket_timestamp_start'] = $ticket_date_timestamp_day_start + $ticket_start_seconds;
				$ticket['ticket_timestamp_end'] = $ticket_date_timestamp_day_end + $ticket_end_seconds;

				$ticket = apply_filters( 'mec_transaction_get_ticket_details', $ticket, $key, $ticket_id, $saved_tickets, $event_tickets );

				$tickets[] = $ticket;
			}
		}


		$total_tickets_count = $this->calculate_total_tickets_count( $tickets );
		$total_tickets_dates = $this->get_total_tickets_dates( $tickets );
		$total_tickets_amount_with_variations =$this->calculate_total_tickets_amount_with_variations( $tickets );

		foreach( $tickets as $k => $ticket ) {

			$fees = $this->get_ticket_fees_details( $ticket, $total_tickets_count, $total_tickets_dates );

			$fees_amount = 0;
			$fees_per_ticket_amount = 0;
			$per_ticket_fee_types = array(
				'percent',
				'amount',
			);
			foreach( $fees as $fee ){

				$fee_type = $fee['fee_type'] ?? '';
				$fee_amount = $fee['amount'] ?? 0;
				$fees_amount += $fee_amount;

				if( in_array( $fee_type, $per_ticket_fee_types ) ) {

					$fees_per_ticket_amount += $fee_amount;
				}
			}
			$ticket['fees_per_ticket_amount'] = $fees_per_ticket_amount;
			$ticket['fees_amount'] = $fees_amount;
			$ticket['fees_details'] = $fees;


			$discounts = $this->get_ticket_discounts_details( $ticket, $total_tickets_amount_with_variations );
			$discounts_amount = array_sum(
				array_column( $discounts, 'amount' )
			);
			$ticket['discounts_amount'] = $discounts_amount;
			$ticket['discounts_details'] = $discounts;

			$tickets[ $k ] = $ticket;
		}


		$this->update_cache_tickets_details( $tickets );

		return $tickets;
	}

	public function get_all_tickets_variations_detail( $tickets = null ) {

		if( is_null( $tickets ) ){

			$tickets = $this->get_all_tickets_details();
		}

		$_variations_details = array();
		foreach( $tickets as $ticket ) {

			$_variations_details = array_merge(
				$_variations_details,
				$ticket['variations_details'] ?? array()
			);
		}

		$variations_details = array();
		foreach( $_variations_details as $k => $variation_details ) {

			$variation_key = $variation_details['variation_key'] ?? 0;
			$amount = $variation_details['amount'] ?? 0;
			$count = $variation_details['count'] ?? 0;

			if( !isset( $variations_details[ $variation_key ] ) ) {

				$variations_details[ $variation_key ] = $variation_details;
			} else {

				$variations_details[ $variation_key ]['amount'] += $amount;
				$variations_details[ $variation_key ]['count'] += $count;
			}
		}

		return $variations_details;
	}

	public function get_all_tickets_fees_detail( $tickets = null ) {

		if( is_null( $tickets ) ){

			$tickets = $this->get_all_tickets_details();
		}

		$_fees_details = array();
		foreach( $tickets as $ticket ) {

			$_fees_details = array_merge(
				$_fees_details,
				$ticket['fees_details'] ?? array()
			);
		}

		$fees_details = array();
		foreach( $_fees_details as $k => $fee_details ) {

			$fee_key = $fee_details['fee_key'] ?? 0;
			$amount = $fee_details['amount'] ?? 0;
			$count = $fee_details['count'] ?? 1;

			if( !isset( $fees_details[ $fee_key ] ) ) {

				$fees_details[ $fee_key ] = $fee_details;
			} else {

				if( !isset( $fees_details[ $fee_key ]['amount'] ) ){

					$fees_details[ $fee_key ]['amount'] = 0;
				}
				$fees_details[ $fee_key ]['amount'] += $amount;

				if( !isset( $fees_details[ $fee_key ]['count'] ) ){

					$fees_details[ $fee_key ]['count'] = 0;
				}
				$fees_details[ $fee_key ]['count'] += $count;
			}
		}

		return $fees_details;
	}

	public function get_all_tickets_discounts_detail( $tickets = null ) {

		if( is_null( $tickets ) ){

			$tickets = $this->get_all_tickets_details();
		}

		$_discounts_details = array();
		foreach( $tickets as $ticket ) {

			$_discounts_details = array_merge(
				$_discounts_details,
				$ticket['discounts_details'] ?? array()
			);
		}

		$discounts_details = array();
		foreach( $_discounts_details as $k => $discount_details ) {

			$discount_key = $discount_details['discount_key'] ?? 0;
			$amount = $discount_details['amount'] ?? 0;
			$count = $discount_details['count'] ?? 1;

			if( !isset( $discounts_details[ $discount_key ] ) ) {

				$discounts_details[ $discount_key ] = $discount_details;
			} else {

				if( !isset( $discounts_details[ $discount_key ]['amount'] ) ){

					$discounts_details[ $discount_key ]['amount'] = 0;
				}
				$discounts_details[ $discount_key ]['amount'] += $amount;

				if( !isset( $discounts_details[ $discount_key ]['count'] ) ){

					$discounts_details[ $discount_key ]['count'] = 0;
				}
				$discounts_details[ $discount_key ]['count'] += $count;
			}
		}

		return $discounts_details;
	}

	public function calculate_total_tickets_detail( $tickets, $key ) {

		$total = 0;
		switch( $key ) {

			case 'discount':

				$discounts_details = $this->get_all_tickets_discounts_detail( $tickets );

				$total = array_sum(
					array_column( $discounts_details, 'amount' )
				);
				break;

			case 'fees_amount':

				$fees_details = $this->get_all_tickets_fees_detail( $tickets );

				$total = array_sum(
					array_column( $fees_details, 'amount' )
				);
				break;

			case 'variations_amount':

				$variations_details = $this->get_all_tickets_variations_detail( $tickets );

				$total = array_sum(
					array_column( $variations_details, 'amount' )
				);
				break;

			default:
				$total = array_sum(
					array_column( $tickets, $key )
				);
		}

		return $total;
	}

	public function calculate_total_tickets_amount( $tickets ) {

		return $this->calculate_total_tickets_detail( $tickets, 'tickets_amount' );
	}

	public function calculate_total_tickets_amount_with_variations( $tickets ) {

		return $this->calculate_total_tickets_detail( $tickets, 'tickets_amount_with_variations' );
	}



	public function calculate_total_tickets_count( $tickets ) {

		return $this->calculate_total_tickets_detail( $tickets, 'count' );
	}

	public function calculate_total_tickets_variations_amount( $tickets ) {

		return $this->calculate_total_tickets_detail( $tickets, 'variations_amount' );
	}

	public function calculate_total_tickets_fees_amount( $tickets ) {

		return $this->calculate_total_tickets_detail( $tickets, 'fees_amount' );
	}

	public function calculate_total_tickets_discounts_amount( $tickets ) {

		return $this->calculate_total_tickets_detail( $tickets, 'discounts_amount' );
	}

	public function remove_ticket( $ticket_key, $date, $update = true ) {

		$tickets = $this->get_tickets();

		$ticket_product_ids = $tickets[ $ticket_key ]['product_ids'] ?? array();
		unset( $ticket_product_ids[ $date ] );
		$tickets[ $ticket_key ]['product_ids'] = $ticket_product_ids;

		$this->data['tickets'] = $tickets;

		if( empty( $ticket_product_ids ) ) {

			unset( $this->data['tickets'][ $ticket_key ] );
			unset( $this->data['removed_tickets'][ $ticket_key ] );
		} else {

			$this->data['removed_tickets'][ $ticket_key ][ $date ] = true;
		}

		$this->reset_cache_tickets_details();

		$saved_dates = $this->get_dates();
		$ticket_dates = $this->get_tickets_dates();

		if( count( $saved_dates ) !== count( $ticket_dates ) ) {

			$this->data['timestamps'] = $ticket_dates;
			$this->data['all_dates'] = $ticket_dates;
			$this->data['date'] = current( $ticket_dates );
		}

		if( $update ) {

			$this->update_data();
		}
	}

	public function remove_ticket_by_product_id( $product_id ) {

		$attendee_key = get_post_meta( $product_id, 'attendee_key', true );
		$date = get_post_meta( $product_id, 'mec_date', true );

		$ticket_key = (int)$attendee_key;

		$this->remove_ticket( $ticket_key, $date, true );

		$tickets = $this->get_tickets();
		if( !empty( $tickets ) ) {

			$this->create_products_from_items( false, true );
		}

		//TODO: update fees
	}

	public function _get_tickets_dates( $tickets ) {

		$dates = array_column( $tickets, 'date' );
		$dates = array_unique( $dates );

		return $dates;
	}

	public function get_tickets_emails( $tickets ) {

		$emails = array_column( $tickets, 'email' );
		$emails = array_unique( $emails );

		return $emails;
	}

	public function get_total_tickets_dates( $tickets ) {

		return count( $this->_get_tickets_dates( $tickets ) );
	}

	public function get_details( $filters = array() ) {

		$tickets_details = $this->get_all_tickets_details();

		if( !empty( $filters ) ){

			foreach( $tickets_details as $td_key => $ticket_details ) {

				foreach( $filters as $f_key => $f_value ) {

					$t_value = $ticket_details[ $f_key ] ?? '';
					if( $t_value != $f_value ) {

						unset( $tickets_details[ $td_key ] );
					}
				}
			}
		}

		$total_tickets_amount = $this->calculate_total_tickets_amount( $tickets_details );
		$total_tickets_amount_with_variations = $this->calculate_total_tickets_amount_with_variations( $tickets_details );
		$total_tickets_count = $this->calculate_total_tickets_count( $tickets_details );
		$total_variations_amount = $this->calculate_total_tickets_variations_amount( $tickets_details );
		$total_fees_amount = $this->calculate_total_tickets_fees_amount( $tickets_details );
		$fees_details = $this->get_all_tickets_fees_detail( $tickets_details );
		$total_discounts_amount = $this->calculate_total_tickets_discounts_amount( $tickets_details );
		$discounts_details = $this->get_all_tickets_discounts_detail( $tickets_details );
		$variations_details = $this->get_all_tickets_variations_detail( $tickets_details );
		$dates = $this->_get_tickets_dates( $tickets_details );
		$total_dates = $this->get_total_tickets_dates( $tickets_details );
		$emails = $this->get_tickets_emails( $tickets_details );


		//TODO: add discount
		return array(
			'tickets_dates' => $dates,
			'tickets_emails' => $emails,
			'tickets_details' => $tickets_details,
			'fees_details' => $fees_details,
			'discounts_details' => $discounts_details,
			'variations_details' => $variations_details,
			'summary' => array(
				'total_tickets_amount' => $total_tickets_amount,
				'total_tickets_amount_with_variations' => $total_tickets_amount_with_variations,
				'total_tickets_count' => $total_tickets_count,
				'total_variations_amount' => $total_variations_amount,
				'total_fee_amount' => $total_fees_amount,
				'total_discounts_amount' => $total_discounts_amount,
				'subtotal_amount_with_fee' => $total_tickets_amount + $total_variations_amount + $total_fees_amount,
				'subtotal_amount_without_fee' => $total_tickets_amount + $total_variations_amount,
				'total_amount_with_fee' => $total_tickets_amount + $total_variations_amount + $total_fees_amount - $total_discounts_amount,
				'total_amount_without_fee' => $total_tickets_amount + $total_variations_amount - $total_discounts_amount,
				'total_dates' => $total_dates,
			)
		);
	}

	private function get_all_occurrences_details() {

		$this->price_details = $this->get_details(array(
			// 'attendee_key' => 0,
		));

		return $this->price_details;
	}

	public function get_price_details() {

		if( is_null( $this->price_details ) ){

			$this->get_all_occurrences_details();
		}

		return $this->price_details;
	}

	public function get_tickets_details( $filters = array() ) {

		$details = $this->get_details( $filters );

		$column_key = 'tickets_details';

		return $details[ $column_key ];
	}

	public function get_variations_details( $filters = array() ) {

		$details = $this->get_details( $filters );

		$column_key = 'variations_details';

		return $details[ $column_key ];
	}

	public function get_variations( $filters = array() ) {

		$details = $this->get_details( $filters );

		$column_key = 'total_variations_amount';

		return $details['summary'][ $column_key ];
	}

	public function get_partial_payment_amount( $total ) {

		if( !$this->is_partial_payment() ) {

			return $total;
		}

		$payable = $total;

		[$payable_amount, $payable_type] = $this->get_partial_payment_settings();

        if($payable_type === 'percent') {

            $payable = $total * ($payable_amount / 100);
        } elseif($payable_type === 'amount') {

            $payable = min($total, $payable_amount);
        }

		return $payable;
	}

	public function calculate_partial_payment( $filters = array(), $apply_fees = true ) {

		$total = $this->get_total( $filters, $apply_fees );

		$payable = $this->get_partial_payment_amount( $total );

        return $payable;
	}

	public function get_fees_details( $filters = array() ) {

		$details = $this->get_details( $filters );

		$column_key = 'fees_details';

		return $details[ $column_key ];
	}

	public function get_emails( $filters = array()  ) {

		$details = $this->get_details( $filters );

		$column_key = 'tickets_emails';

		return $details[ $column_key ];
	}

	public function get_tickets_dates( $filters = array()  ) {

		$details = $this->get_details( $filters );

		$column_key = 'tickets_dates';

		return $details[ $column_key ];
	}

	public function get_fees( $filters = array() ) {

		$details = $this->get_details( $filters );

		$column_key = 'total_fee_amount';

		return $details['summary'][ $column_key ];
	}

	public function get_discounts( $filters = array() ) {

		$details = $this->get_details( $filters );

		$column_key = 'total_discounts_amount';

		return $details['summary'][ $column_key ];
	}

	public function get_subtotal( $filters = array(), $apply_fees = true  ) {

		$details = $this->get_details( $filters );

		$column_key = 'subtotal_amount_with_fee';//total_amount_with_fee | total_amount_without_fee

		if( !$apply_fees ) {

			$column_key = 'subtotal_amount_without_fee';
		}

		return $details['summary'][ $column_key ];
	}

	public function get_total( $filters = array(), $apply_fees = true ) {

		$details = $this->get_details( $filters );

		$column_key = 'total_amount_with_fee';//total_amount_with_fee | total_amount_without_fee

		if( !$apply_fees ) {

			$column_key = 'total_amount_without_fee';
		}

		return $details['summary'][ $column_key ];
	}

	public function get_payable( $filters = array(), $apply_fees = true ) {

		$total = $this->get_total( $filters, $apply_fees );
		$payable = $total;

        // Calculate Payable
        if( $this->is_partial_payment() ) {

			$payable = $this->calculate_partial_payment( $filters, $apply_fees );
		}

		return $payable;
	}

	public function get_total_dates( $filters = array() ) {

		$details = $this->get_details( $filters );

		$column_key = 'total_dates';

		return $details['summary'][ $column_key ];
	}

	public function get_attendees_info( $filters = array() ) {

		$info = array();
		$tickets_details = $this->get_tickets_details( $filters );
		foreach( $tickets_details as $ticket_details ) {

			$email = $ticket_details['email'];
			$date = $ticket_details['date'];
			$name = $ticket_details['name'];
			$detail_key = "$email-$date-$name";
			$count = $ticket_details['count'];
			if( !isset( $info[ $detail_key ] ) ) {

				$info[ $detail_key ] = array(
					'name' => $name,
					'email' => $email,
					'date' => $date,
					'count' => $ticket_details['count'],
					'reg' => $ticket_details['reg'] ?? array(),
				);
			} else {

				$info[ $detail_key ]['count'] += $count;
			}
		}

		return $info;
	}

	public function validate_for_add_book() {

		$errors = array();
		$date_format = 'Y-m-d';
		$event_id = $this->get_event_id();
		$tickets_details = $this->get_tickets_details();
        $dates = $this->get_tickets_dates();

        foreach( $dates as $date ) {

            $t_occurrences = explode( ':', $date );
            $occurrence_time = $t_occurrences[0];
            $availability[ $occurrence_time ] = $this->bookClass->get_tickets_availability(
				$event_id,
				$occurrence_time
			);
        }

		foreach( $tickets_details as $ticket ) {

			$t_occurrences = explode( ':', $ticket['date'] );
			$occurrence = $t_occurrences[0];
			$occurrence_availability = $availability[ $occurrence ] ?? array();
			$ticket_id = $ticket['id'];
			$ticket_name = $ticket['ticket_name'];
			$ticket_count = $ticket['count'];

			$ticket_availability = $occurrence_availability[ $ticket_id ] ?? false;
			$str_replace = !empty( $ticket_name ) ? '<strong>'. $ticket_name .'</strong>' : '';
			$ticket_message_sold_out =  sprintf(
				__('The %s ticket is sold out in %s. You can try another ticket or another date.', 'modern-events-calendar-lite'),
				$str_replace,
				date_i18n( $date_format, $occurrence )
			);

			if( -1 != $ticket_availability && ( !$ticket_availability || $ticket_count > $ticket_availability ) ) {

				$errors[ "$ticket_id-$occurrence" ] = array(
					'success' => 0,
					'message'=>sprintf(
						\MEC\Base::get_main()->m(
							'booking_restriction_message3',
							$ticket_message_sold_out
						),
						$ticket_availability
					),
					'code'=>'LIMIT_REACHED'
				);
			}
		}

		if( !empty( $errors ) ) {

			return $errors;
		}

		return true;
	}

	public function get_closest_occurrence() {

		$all_dates = $this->get_dates();

		if( count( $all_dates ) > 1 ){

            foreach( $all_dates as $timestamps ){

                $ex = explode( ':', $timestamps );
                $start_timestamp = $ex[0];
                $end_timestamp = $ex[1];
                $current_timestamp = current_time('timestamp');
                if( $current_timestamp >= $end_timestamp ){

                    continue;
                }

                return $timestamps;
            }
        }

		$timestamps = current( $all_dates );

		return $timestamps;
	}

	public function get_closest_occurrence_start_timestamp() {

		$timestamps = $this->get_closest_occurrence();
		$ex = explode( ':', $timestamps );

		return $ex[0];
	}

	public function get_closest_occurrence_end_timestamp() {

		$timestamps = $this->get_closest_occurrence();
		$ex = explode( ':', $timestamps );

		return $ex[1];
	}

	public function register_user( $attendee, $args = array() ) {

        $user = \MEC::getInstance('app.libraries.user');
        return $user->register($attendee, $args);
    }

	public function create_book_from_transaction( $args = array(), $rebuild = false ) {

		$book_id = $this->get_book_id();
		if( $book_id && !$rebuild ){

			return $book_id;
		}

        $attendees   = $this->get_tickets();
        $attention_date = $this->get_date();
        $attention_times = explode(':', $attention_date);
        $date = date('Y-m-d H:i:s', trim($attention_times[0]));

        // Is there any attendee?
        if (!count( $attendees )) {

			return new \WP_Error(
				'NO_TICKET',
				__( 'There is no attendee for booking!', 'modern-events-calendar-lite')
			);
        }

        $main_attendee = isset($attendees[0]) ? $attendees[0] : array();
        $name          = $main_attendee['name'] ?? '';
        $ticket_ids = '';
        $attendees_info = array();

        foreach ($attendees as $attendee) {

            $ticket_ids .= $attendee['id'] . ',';
            if (!array_key_exists($attendee['email'], $attendees_info)) {

                $attendees_info[$attendee['email']] = array(
                    'count' => $attendee['count']
                );
            } else {

                $attendees_info[ $attendee['email'] ]['count'] = ($attendees_info[$attendee['email']]['count'] + $attendee['count']);
            }
        }

        $user_id = $this->register_user( $main_attendee );
		$user = \MEC::getInstance('app.libraries.user');

		$gateway = $args['mec_gateway'] ?? $this->get_gateway();
		$gateway_label = $args['mec_gateway_label'] ?? $this->get_gateway_label( $gateway );
        $book_id = $this->bookClass->add(
            array(
                'post_author' => $user_id,
                'post_type' => 'mec-books',
                'post_title' => $name.' - '.$user->get($user_id)->user_email,
                'post_date' => $date,
                'attendees_info' => $attendees_info,
                'mec_attendees' => $attendees,
				'mec_gateway' => $gateway,
            	'mec_gateway_label' => $gateway_label,
            ),
            $this->transaction_id,
            ',' . $ticket_ids
        );

		//TODO: update book meta

		// Fires after completely creating a new booking
        do_action('mec_booking_completed', $book_id);

		return $book_id;
	}

	public function create_products_from_items( $rebuild = false, $update = false ) {

		$product_ids = array();

		$saved_data = $this->get_saved_data();
		$tickets_details = $this->get_tickets_details();
		foreach( $tickets_details as $ticket_detail ) {
			//TODO: is first for all
			$attendee_key = $ticket_detail['attendee_key'];
			$date = $ticket_detail['date'];
			$ex_date = explode( ':', $date );
			$start_timestamp = $ex_date[0];
			$product_id = $ticket_detail['product_id'] ?? 0;

			if( !$product_id || $rebuild || $update ) {

				$product_id = $this->create_ticket_product( $ticket_detail, $update );

				$saved_data['tickets'][ $attendee_key ][ 'product_ids' ][ $date ] = $product_id;
			}

			$product_ids[] = $product_id;
		}


		$saved_fees_product_ids = $saved_data['fees_product_ids'] ?? array();
		$related_products = $product_ids;
		$per_ticket_fee_types = array(
			'percent',
			'amount',
		);
		// $dates = $this->get_tickets_dates();
		// foreach( $dates as $timestamp ) {

			$filters = array(
				// 'date' => $timestamp,
			);
			$fees_details = $this->get_fees_details( $filters );
			$fees_product_ids = array();
			foreach( $fees_details as $fee_key => $fee_detail ) {

				$fee_type = $fee_detail['fee_type'];
				if( in_array( $fee_type, $per_ticket_fee_types ) ) {

					continue;
				}

				$product_id = $saved_fees_product_ids[ $fee_key ] ?? 0;
				$fee_detail['product_id'] = $product_id;

				if( !$product_id || $rebuild || $update ) {

					$product_id = $this->create_fee_product( $fee_detail, $related_products, $update );

					$fees_product_ids[ $fee_key ] = $product_id;
				}

				$product_ids[] = $product_id;
			}
		// }

		$saved_data['fees_product_ids'] = $fees_product_ids;

		$this->set_data( $saved_data ); // new data
		$this->update_data();

		return $product_ids;
	}

	public function create_product( $args ) {

		$transaction_id = $this->transaction_id;

		$product_type = \MEC\Settings\Settings::getInstance()->get_settings( 'ticket_product_type' );
		$product_type = $product_type ? $product_type : 'virtual';
        $is_virtual = ( 'virtual' === $product_type ) ? 'yes' : 'no';
		$event_id = $this->get_event_id();

		$meta_input = wp_parse_args(
			$args['meta_input'] ?? array(),
			array(
				'_visibility' => false,
				'_stock_status' => 'instock',
				'total_sales' => '0',
				'_downloadable' => 'no',
				'_purchase_note' => '',
				'_featured' => 'no',
				'_weight' => '',
				'_length' => '',
				'_width' => '',
				'_height' => '',
				'_sku' => '',
				'_product_attributes' => array(),
				'_sale_price_dates_from' => '',
				'_sale_price_dates_to' => '',
				'_sold_individually' => '',
				'_manage_stock' => 'no',
				'_backorders' => 'no',
				'_stock' => '',
				'_product_image_gallery' => '',

				'transaction_id' => $transaction_id,
				'_mec_event_id' => $event_id,
				'event_id' => $event_id, //TODO: remove
				'event_name' => get_the_title( $event_id ),

				'first_for_all' => $this->is_first_for_all() ? 'yes' : 'no',
				'_virtual' => $is_virtual,
				'_regular_price' => '',
				'_sale_price' => '',
				'_price' => '',

				'cantChangeQuantity' => true,
				// 'm_product_type' => '',
				// 'related_products' => '',
			)
		);

		unset( $args['meta_input'] );

		$defaults = array(
            'post_content' => '',
            'post_status'  => 'MEC_Tickets',
            'post_title'   => $args['product_title'],
            'post_parent'  => '',
            'post_type'    => 'product',
			'meta_input' => $meta_input
        );

		$args = wp_parse_args( $args, $defaults );

        // Create post
        $post_id = wp_insert_post( $args );
		wp_set_object_terms($post_id, 'MEC-Woo-Cat', 'product_cat', true);
        wp_set_object_terms($post_id, 'simple', 'product_type');

		$terms = array('exclude-from-search', 'exclude-from-catalog');
		wp_set_post_terms($post_id, $terms, 'product_visibility', false);



        if (has_post_thumbnail( $event_id )) {
            $image                = wp_get_attachment_image_src(get_post_thumbnail_id( $event_id ), 'full');
            $event_featured_image = str_replace(get_site_url(), $_SERVER['DOCUMENT_ROOT'], $image[0]);

            if ($event_featured_image) {
                set_post_thumbnail($post_id, attachment_url_to_postid($image[0]));
            }
        }

        if (isset($args['m_product_type'])) {

			update_post_meta($post_id, 'm_product_type', $args['m_product_type']);
            update_post_meta($post_id, 'related_products', $args['related_products']);
        }

		if (isset($args['cantChangeQuantity'])) {

			update_post_meta($post_id, 'cantChangeQuantity', true);
		}

		return $post_id;
	}

	public function create_ticket_product( $ticket_detail, $update = false ) {

		$ticket_id = $ticket_detail['id'] ?? '';
		$ticket_product_id = $ticket_detail['product_id'] ?? 0;
		$attendee_key = $ticket_detail['attendee_key'] ?? '';

		$ticket_price = $ticket_detail['ticket_price'] ?? 0;
		$ticket_sale_price = $ticket_detail['ticket_sale_price'] ?? $ticket_price;
		$variations_amount = $ticket_detail['variations_amount'] ?? 0;
		$discounts_amount = $ticket_detail['discounts_amount'] ?? 0;
		$fees_per_ticket_amount = $ticket_detail['fees_per_ticket_amount'] ?? 0;

		$ticket_price += $fees_per_ticket_amount;
		$ticket_sale_price += $fees_per_ticket_amount;

		$ticket_price += $variations_amount;
		$ticket_sale_price += $variations_amount - $discounts_amount;

		$ticket_sale_price = $this->get_partial_payment_amount( $ticket_sale_price );

		$variations = $ticket_detail['variations_details'] ?? array();

		$args = array(
			'product_title' => __( 'Ticket', 'modern-events-calendar-lite') . ' (' . $ticket_detail['ticket_name'] . ') - ' . $this->transaction_id,
			'meta_input' => array(
				'_regular_price' => $ticket_price,
				'_sale_price' => $ticket_sale_price,
				'_price' => $ticket_sale_price,

				'ticket_id' => $ticket_id,
				'ticket_name' => $ticket['ticket_name'] ?? '',
				'attendee_key' => $attendee_key,
				'mec_ticket' => $ticket_detail,
				'mec_date' => $ticket_detail['date'],
				'ticket_used_count' => $ticket_detail['count'],
			)
		);

		if( $update && $ticket_product_id ) {

			$args['ID'] = $ticket_product_id;
		}

		$post_id = $this->create_product( $args );

		$ticket_sales_with_wooCommerce_product = false;

        $event_tickets = $this->get_event_tickets();
        $event_ticket = isset( $event_tickets[$ticket_id] ) && is_array( $event_tickets[$ticket_id] ) ? $event_tickets[$ticket_id] : [];
        $ticket_custom_categories = isset( $event_ticket['category_ids'] ) && !empty( $event_ticket['category_ids'] ) ? (array)$event_ticket['category_ids'] : [];
        if( false == $ticket_sales_with_wooCommerce_product && !empty( $ticket_custom_categories ) ){

			foreach($ticket_custom_categories as $k => $category_id){

                $ticket_custom_categories[$k] = intval($category_id);
            }

            wp_set_object_terms($post_id, $ticket_custom_categories, 'product_cat', true);
        }

		foreach( $variations as $variation ) {

			$variation_data[] = [
				'MEC_WOO_V_max'   => @$variation['max'],
				'MEC_WOO_V_title' => $variation['description'],
				'MEC_WOO_V_price' => $variation['price'],
				'MEC_WOO_V_count' => $variation['count'],
			];

			add_post_meta($post_id, 'MEC_Variation_Data', json_encode($variation_data , JSON_UNESCAPED_UNICODE ));
		}

		return $post_id;
	}

	public function create_fee_product( $fee_detail, $related_products = array(), $update = false ) {

		$fee_amount = $fee_detail['amount'];
		$fee_description = $fee_detail['description'];
		$fee_type = $fee_detail['fee_type'];
		$fee_product_id = $fee_detail['product_id'] ?? 0;

		$fee_sale_amount = $this->get_partial_payment_amount( $fee_amount );

		$args = array(
			'product_title' => "$fee_description - {$this->transaction_id}",
			'meta_input' => array(
				'_regular_price' => $fee_amount,
				'_sale_price' => $fee_sale_amount,
				'_price' => $fee_sale_amount,

				'ticket_used_count' => 1,
				'm_product_type' => $fee_type,
				'related_products' => $related_products
			),
		);

		if( $update && $fee_product_id ) {

			$args['ID'] = $fee_product_id;
		}

		$post_id = $this->create_product( $args );

		return $post_id;
	}

	public static function upgrade_db() {

		$books = get_posts(array(
			'post_type' => 'mec-books',
			'posts_per_page' => 100,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'mec_transaction_upgraded',
					'compare' => 'NOT EXISTS',
				)
			)
		));

		if( 0 === count( $books ) ) {

			update_option( 'mec_transaction_version', MEC_VERSION );
			return;
		}

		add_filter( 'mec_transaction_get_event_fees', array( __CLASS__, 'filter_transaction_get_event_fees' ), 10, 3 );

		$transaction_ids = [];
		foreach( $books as $book_id ) {

			$transaction_ids[ $book_id ] = get_post_meta( $book_id, 'mec_transaction_id', true );
		}

		foreach( $transaction_ids as $book_id => $transaction_id ) {

			update_post_meta( $book_id, 'mec_transaction_upgraded', 'yes' );

			$transaction = get_option( $transaction_id, false );
			if( !$transaction ) {
				continue;
			}

			if( isset( $transaction['old_tickets'] ) ){

				$transaction['tickets'] = $transaction['old_tickets'];
			}else{

				$transaction['old_tickets'] = $transaction['tickets'];
			}

			if( !isset( $transaction['booking_id'] ) ){

				$transaction['booking_id'] = $book_id;
			}

			if( !isset( $transaction['event_id'] ) ){

				$transaction['booking_id'] = $book_id;
			}

			if( !isset( $transaction['gateway'] ) ){

				$gateway = get_post_meta( $book_id, 'mec_gateway', true );
				$transaction['gateway'] = !empty( $gateway ) ? $gateway : 'MEC_gateway_pay_locally';
			}

			if( !isset( $transaction['applied_fee'] ) ){

				$has_fee = in_array(
					'fee',
					array_column( $transaction['price_details']['details'] ?? array(), 'type' )
				);

				$transaction['applied_fee'] = $has_fee;
			}

			if( !isset( $transaction['applied_discount'] ) && $transaction['discount'] ){

				$has_discount = in_array(
					'discount',
					array_column( $transaction['price_details']['details'] ?? array(), 'type' )
				);

				$transaction['applied_discount'] = $has_discount;
			}

			if( isset( $transaction['tickets'][0]['_name'] ) ){

				$book = new \MEC_book();
				$attendees = $book->get_attendees( $book_id );
				if( count( $attendees ) !== count( $transaction['tickets'] ) ) {

					$transaction['tickets'] = $attendees;
				}

				if( isset( $transaction['tickets'][0]['date'] ) ){

					$ticket_date = false;
					$new_tickets = array();
					foreach( $transaction['old_tickets'] as $old_ticket ) {

						if( $ticket_date && $ticket_date !== $old_ticket['date'] ) {

							continue;
						}

						if( !$ticket_date ) {

							$ticket_date = $old_ticket['date'];
						}

						$ticket = $old_ticket;

						unset( $ticket['date'] );
						unset( $ticket['product_id'] );

						$ticket['count'] = 1;

						$new_tickets[] = $ticket;
					}

					$transaction['tickets'] = $new_tickets;
				}

				foreach( $transaction['tickets'] as $k => $ticket ) {

					$ticket;
					$attendee_name = $ticket['_name'];
					unset( $ticket['_name'] );
					unset( $ticket['date'] );
					if( !empty( $attendee_name ) ) {

						$ticket['name'] = $attendee_name;
					}

					$transaction['tickets'][ $k ] = $ticket;
				}
			}

			$woo_order_id = get_post_meta( $book_id, 'mec_order_id', true );
			$transaction['order_id'] = $woo_order_id; //TODO: add in

			if( $woo_order_id ) {

				$transaction['wc'] = true;
			}

			$total = $transaction['total'] ?? 0;
			$payable = $transaction['payable'] ?? 0;
			if( $total < $payable ) {

				$transaction['total'] = $payable;
				$transaction['payable'] = $total;
			}else{

				// $transaction['total'] = $total;
				// $transaction['payable'] = $payable;
			}


			update_option( $transaction_id, $transaction, false );

			$total = $transaction['total'] ?? 0;
			$payable = $transaction['payable'] ?? $total;

			$transactionObject = new \MEC\Transactions\Transaction( $transaction_id );
			if(
				$transactionObject->get_subtotal() != $total
				||
				$transactionObject->get_total() != $payable
				) {

					/*
					error_log( "Transaction error {$book_id}-{$transaction_id}");
					echo '<pre>';
					var_dump( $book_id );
					var_dump($transactionObject->get_subtotal() . '!=' . $transaction['total']);
					var_dump($transactionObject->get_total() . '!=' . $transaction['payable']);
					echo '<a href="' . get_edit_post_link( $book_id ) .'" target="_blank">'. $book_id .'</a><br>' ;
					echo '<a href="' . get_delete_post_link( $book_id ) .'" target=""> delete '. $book_id .'</a><br>' ;
					echo '<br><br><br>';
					print_r($transactionObject->get_all_occurrences_details());
					wp_die(print_r($transaction));
					*/
			} else {

				$transactionObject->update_cache_tickets_details();
			}
		}
	}

	public static function filter_transaction_get_event_fees( $fees, $event_id, $class ) {

		$transaction = $class->get_saved_data();
		$saved_fees = array_filter( $transaction['price_details']['details'], function( $a ){

			return $a['type'] === 'fee';
		});
		$saved_fees = array_values( $saved_fees );
		$saved_fees_titles = array_column( $saved_fees, 'description' );

		foreach( $fees as $k => $fee ) {

			$index = array_search( $fee['title'], $saved_fees_titles );
			if( false !== $index ){

				$fees[ $k ]['type'] = $saved_fees[ $index ]['fee_type'];
				$fees[ $k ]['amount'] = $saved_fees[ $index ]['fee_amount'];
			} else {

				unset( $fees[ $k ] );
			}
		}

		return $fees;
	}
}
