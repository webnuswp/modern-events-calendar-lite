<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC book class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_book extends MEC_base
{
    /**
     * @var array
     */
    public $settings;
    public $main;
    public $PT;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();
        
        // MEC Book Post Type Name
        $this->PT = $this->main->get_book_post_type();
        
        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Get invoice (Ticket price + Fees) based on tickets
     * @author Webnus <info@webnus.biz>
     * @param array $tickets
     * @param int $event_id
     * @param array $event_tickets
     * @param array $variations
     * @return array
     */
    public function get_price_details($tickets, $event_id, $event_tickets, $variations = array())
    {
        $total = 0;
        $details = array();

        $total_tickets_count = 0;
        foreach($tickets as $ticket_id=>$count)
        {
            if(!$count) continue;
            if(!isset($event_tickets[$ticket_id])) continue;
            
            $total_tickets_count += $count;
            
            $t_price = (isset($event_tickets[$ticket_id]) and isset($event_tickets[$ticket_id]['price'])) ? $this->get_ticket_price($event_tickets[$ticket_id], current_time('Y-m-d')) : 0;
            $total = $total+($t_price*$count);
        }
        
        $details[] = array('amount'=>$total, 'description'=>sprintf(__('%s Price', 'modern-events-calendar-lite'), $this->main->m('tickets', __('Tickets', 'modern-events-calendar-lite'))), 'type'=>'tickets');

        // Default variations amount
        $total_variations_amount = 0;

        // Variations module is enabled and some variations bought
        if(isset($this->settings['ticket_variations_status']) and $this->settings['ticket_variations_status'] and is_array($variations) and count($variations))
        {
            $ticket_variations = $this->main->ticket_variations($event_id);

            foreach($ticket_variations as $key=>$ticket_variation)
            {
                if(!is_numeric($key)) continue;
                if(!isset($ticket_variation['title']) or (isset($ticket_variation['title']) and !trim($ticket_variation['title']))) continue;

                $variation_count = isset($variations[$key]) ? $variations[$key] : 0;
                if(!$variation_count or ($variation_count and $variation_count < 0)) continue;

                $variation_amount = $ticket_variation['price']*$variation_count;
                $variation_title = $ticket_variation['title'].' ('.$variation_count.')';
                $details[] = array('amount'=>$variation_amount, 'description'=>__($variation_title, 'modern-events-calendar-lite'), 'type'=>'variation');

                $total_variations_amount += $variation_amount;
            }
        }
        
        // Default fee amount
        $total_fee_amount = 0;
        
        // Fees module is enabled
        if(isset($this->settings['taxes_fees_status']) and $this->settings['taxes_fees_status'])
        {
            $fees = $this->get_fees($event_id);
            
            foreach($fees as $key=>$fee)
            {
                $fee_amount = 0;
                if(!is_numeric($key)) continue;
                
                if($fee['type'] == 'percent') $fee_amount += (($total+$total_variations_amount)*$fee['amount'])/100;
                elseif($fee['type'] == 'amount') $fee_amount += ($total_tickets_count*$fee['amount']);
                elseif($fee['type'] == 'amount_per_booking') $fee_amount += $fee['amount'];

                $details[] = array('amount'=>$fee_amount, 'description'=>__($fee['title'], 'modern-events-calendar-lite'), 'type'=>'fee');
                
                $total_fee_amount += $fee_amount;
            }
        }
        
        return array('total'=>($total+$total_fee_amount+$total_variations_amount), 'details'=>$details);
    }
    
    /**
     * Get fees of a certain event
     * @author Webnus <info@webnus.biz>
     * @param int $event_id
     * @return array
     */
    public function get_fees($event_id)
    {
        $fees_global_inheritance = get_post_meta($event_id, 'mec_fees_global_inheritance', true);
        if(is_string($fees_global_inheritance) and trim($fees_global_inheritance) == '') $fees_global_inheritance = 1;
        
        // Event fees
        $fees = get_post_meta($event_id, 'mec_fees', true);

        // Get fees from global options
        if($fees_global_inheritance)
        {
            $fees = isset($this->settings['fees']) ? $this->settings['fees'] : array();
        }
        
        return $fees;
    }
    
    /**
     * Save a temporary booking
     * @author Webnus <info@webnus.biz>
     * @param array $data
     * @return int
     */
    public function temporary($data = array())
    {
        $transaction_id = $this->get_transaction_id();
        update_option($transaction_id, $data);
        
        return $transaction_id;
    }
    
    /**
     * Generate a transaction id for bookings
     * @author Webnus <info@webnus.biz>
     * @return string
     */
    public function get_transaction_id()
    {
        $string = str_shuffle('ABCDEFGHJKLMNOPQRSTUVWXYZ');
        $key = substr($string, 0, 3).mt_rand(10000, 99999);
        
        // If the key exist then generate another key
        if(get_option($key, false) !== false) $key = $this->get_transaction_id();
        
        return $key;
    }
    
    /**
     * Get transaction data
     * @author Webnus <info@webnus.biz>
     * @param string $transaction_id
     * @return array
     */
    public function get_transaction($transaction_id)
    {
        return get_option($transaction_id, array());
    }
    
    /**
     * Update a transaction
     * @author Webnus <info@webnus.biz>
     * @param string $transaction_id
     * @param array $data
     */
    public function update_transaction($transaction_id, $data)
    {
        update_option($transaction_id, $data);
    }
    
    /**
     * Add a booking
     * @author Webnus <info@webnus.biz>
     * @param array $values
     * @param string $transaction_id
     * @param int $ticket_ids
     * @return int
     */
    public function add($values, $transaction_id, $ticket_ids)
    {
        // Transaction Data
        $transaction = $this->get_transaction($transaction_id);
        $event_id = $transaction['event_id'];
        
        // Default values
        if(!isset($values['post_date'])) $values['post_date'] = $transaction['date'];
        if(!isset($values['post_status'])) $values['post_status'] = 'publish';
        
        $book_id = wp_insert_post($values);

        // Publish it
        wp_publish_post($book_id);
        
        update_post_meta($book_id, 'mec_verified', 0);
        update_post_meta($book_id, 'mec_verification_key', md5(time().mt_rand(10000, 99999)));
        update_post_meta($book_id, 'mec_cancellation_key', md5(time().mt_rand(10000, 99999)));
        
        update_post_meta($book_id, 'mec_confirmed', 0);
        update_post_meta($book_id, 'mec_transaction_id', $transaction_id);
        
        update_post_meta($book_id, 'mec_event_id', $event_id);
        update_post_meta($book_id, 'mec_date', $transaction['date']);
        update_post_meta($book_id, 'mec_ticket_id', $ticket_ids);
        update_post_meta($book_id, 'mec_booking_time', current_time('Y-m-d H:i:s'));
        if(isset($values['mec_attendees'])) update_post_meta($book_id, 'mec_attendees', $values['mec_attendees']);
        
        $price = isset($transaction['price']) ? $transaction['price'] : (isset($transaction['total']) ? $transaction['total'] : 0);
        update_post_meta($book_id, 'mec_price', $price);
        
        // A coupon applied
        if(isset($transaction['coupon']))
        {
            $coupon_id = $this->coupon_get_id($transaction['coupon']);
            if($coupon_id)
            {
                wp_set_object_terms($book_id, $coupon_id, 'mec_coupon');
                update_post_meta($book_id, 'mec_coupon_code', $transaction['coupon']);
            }
        }
        
        // Fires after adding a new booking to send notifications etc
        do_action('mec_booking_added', $book_id);
        
        // Auto verification for free bookings is enabled
        if($price <= 0 and isset($this->settings['booking_auto_verify_free']) and $this->settings['booking_auto_verify_free'] == 1)
        {
            $this->verify($book_id);
        }
        
        // Auto verification for paid bookings is enabled
        if($price > 0 and isset($this->settings['booking_auto_verify_paid']) and $this->settings['booking_auto_verify_paid'] == 1)
        {
            $this->verify($book_id);
        }
        
        // Auto confirmation for free bookings is enabled
        if($price <= 0 and isset($this->settings['booking_auto_confirm_free']) and $this->settings['booking_auto_confirm_free'] == 1)
        {
            $this->confirm($book_id);
        }
        
        // Auto confirmation for paid bookings is enabled
        if($price > 0 and isset($this->settings['booking_auto_confirm_paid']) and $this->settings['booking_auto_confirm_paid'] == 1)
        {
            $this->confirm($book_id);
        }
        
        return $book_id;
    }
    
    /**
     * Confirm a booking
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function confirm($book_id)
    {
        update_post_meta($book_id, 'mec_confirmed', 1);
        
        // Fires after confirming a booking to send notifications etc.
        do_action('mec_booking_confirmed', $book_id);
        
        return true;
    }
    
    /**
     * Reject a booking
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function reject($book_id)
    {
        update_post_meta($book_id, 'mec_confirmed', -1);
        
        // Fires after rejecting a booking to send notifications etc.
        do_action('mec_booking_rejected', $book_id);
        
        return true;
    }
    
    /**
     * Make a booking pending
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function pending($book_id)
    {
        update_post_meta($book_id, 'mec_confirmed', 0);
        
        // Fires after pending a booking to send notifications etc.
        do_action('mec_booking_pended', $book_id);
        
        return true;
    }
    
    /**
     * Verify a booking
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function verify($book_id)
    {
        update_post_meta($book_id, 'mec_verified', 1);
        
        // Fires after verifying a booking to send notifications etc.
        do_action('mec_booking_verified', $book_id);
        
        return true;
    }
    
    /**
     * Cancel a booking
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function cancel($book_id)
    {
        update_post_meta($book_id, 'mec_verified', -1);
        update_post_meta($book_id, 'mec_cancelled_date', date('Y-m-d H:i:s', current_time('timestamp', 0)));

        // Fires after canceling a booking to send notifications etc.
        do_action('mec_booking_canceled', $book_id);
        
        return true;
    }
    
    /**
     * Waiting a booking
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function waiting($book_id)
    {
        update_post_meta($book_id, 'mec_verified', 0);
        
        // Fires after waiting a booking to send notifications etc.
        do_action('mec_booking_waiting', $book_id);
        
        return true;
    }
    
    /**
     * Returns ticket availabilities of an event for a certain date
     * @author Webnus <info@webnus.biz>
     * @param int $event_id
     * @param string $date
     * @param string $mode
     * @return array
     */
    public function get_tickets_availability($event_id, $date, $mode = 'availability')
    {
        $availability = array();
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        // No Ticket Found!
        if(!is_array($tickets) or (is_array($tickets) and !count($tickets)))
        {
            if($mode == 'reservation') return 0;
            else return $availability;
        }
        
        $booking_options = get_post_meta($event_id, 'mec_booking', true);
        if(!is_array($booking_options)) $booking_options = array();
        
        $total_bookings_limit = (isset($booking_options['bookings_limit']) and trim($booking_options['bookings_limit'])) ? $booking_options['bookings_limit'] : 100;
        $bookings_limit_unlimited = isset($booking_options['bookings_limit_unlimited']) ? $booking_options['bookings_limit_unlimited'] : 0;
        
        if($bookings_limit_unlimited == '1') $total_bookings_limit = '-1';

        // Total Booking Limit
        $total_bookings_limit_original = $total_bookings_limit;

        $ex = explode(':', $date);
        $date = $ex[0];
        
        $ex = explode('-', $date);
        
        $year = $ex[0];
        $month = $ex[1];
        $day = $ex[2];

        $booked = 0;
        foreach($tickets as $ticket_id=>$ticket)
        {
            $limit = (isset($ticket['limit']) and trim($ticket['limit']) != '') ? $ticket['limit'] : -1;

            $query = new WP_Query(array
            (
                'post_type'=>$this->PT,
                'posts_per_page'=>-1,
                'post_status'=>array('publish', 'pending', 'draft', 'future', 'private'),
                'year'=>$year,
                'monthnum'=>$month,
                'day'=>$day,
                'meta_query'=>array
                (
                    array('key'=>'mec_event_id', 'value'=>$event_id, 'compare'=>'='),
                    array('key'=>'mec_ticket_id', 'value'=>','.$ticket_id.',', 'compare'=>'LIKE'),
                    array('key'=>'mec_verified', 'value'=>'-1', 'compare'=>'!='), // Don't include canceled bookings
                    array('key'=>'mec_confirmed', 'value'=>'-1', 'compare'=>'!='), // Don't include rejected bookings
                )
            ));

            $bookings = 0;
            if($query->have_posts())
            {
                // The Loop
                while($query->have_posts())
                {
                    $query->the_post();

                    $ticket_ids_string = trim(get_post_meta(get_the_ID(), 'mec_ticket_id', true), ', ');
                    $ticket_ids_count = array_count_values(explode(',', $ticket_ids_string));

                    $bookings += isset($ticket_ids_count[$ticket_id]) ? $ticket_ids_count[$ticket_id] : 0;
                }
            }

            if($total_bookings_limit > 0) $total_bookings_limit = max(($total_bookings_limit - $bookings), 0);

            $booked += $bookings;

            // Restore original Post Data
            wp_reset_postdata();

            if((isset($ticket['unlimited']) and $ticket['unlimited'] == 1) or $limit == -1)
            {
                $availability[$ticket_id] = ($total_bookings_limit > 0) ? $total_bookings_limit : -1;
                continue;
            }

            if($limit == '') $limit = 0;

            // Unlimited Total
            if($total_bookings_limit == '-1') $ticket_availability = $limit-$bookings;
            else $ticket_availability = min(($limit-$bookings), max($total_bookings_limit, 0));

            $availability[$ticket_id] = $ticket_availability >= 0 ? $ticket_availability : 0;
        }

        // For the time being set reservation parameter
        if($mode == 'reservation') return $booked;

        // Set Total Booking Limit
        $availability['total'] = $total_bookings_limit;

        // Do not send higher limit for tickets compared to total limit
        if($total_bookings_limit != '-1' and $total_bookings_limit > 0)
        {
            $new_availability = array();
            foreach($availability as $ticket_id=>$limit)
            {
                if(is_numeric($ticket_id)) $new_availability[$ticket_id] = min($limit, $total_bookings_limit);
                else $new_availability[$ticket_id] = $limit;
            }

            return $new_availability;
        }

        // Total Booking Limit Reached
        if($total_bookings_limit_original != -1 and $booked >= $total_bookings_limit_original)
        {
            $new_availability = array();
            foreach($availability as $ticket_id=>$limit)
            {
                if(is_numeric($ticket_id)) $new_availability[$ticket_id] = 0;
                else $new_availability[$ticket_id] = $limit;
            }

            return $new_availability;
        }
        
        return $availability;
    }
    
    /**
     * Check validity of a coupon
     * @author Webnus <info@webnus.biz>
     * @param string $coupon
     * @param int $event_id
     * @return int
     */
    public function coupon_check_validity($coupon, $event_id)
    {
        $term = get_term_by('name', $coupon, 'mec_coupon');
        $coupon_id = isset($term->term_id) ? $term->term_id : 0;
        
        // Coupon is not exists
        if(!$coupon_id) return 0;

        // Usage Limit
        $usage_limit = get_term_meta($coupon_id, 'usage_limit', true);
        $status = ($usage_limit == '-1' or (int) $usage_limit > $term->count) ? 1 : -1;

        // Expiration Date
        if($status === 1)
        {
            $expiration_date = get_term_meta($coupon_id, 'expiration_date', true);
            if(trim($expiration_date) and strtotime($expiration_date) < strtotime(date('Y-m-d')))
            {
                $status = -2;
            }
        }

        // Event Specification
        if($status === 1)
        {
            $target_event = get_term_meta($coupon_id, 'target_event', true);
            if(trim($target_event) and trim($event_id) and $target_event != $event_id)
            {
                $status = -3;
            }
        }

        return $status;
    }
    
    /**
     * Apply a coupon to a transaction
     * @author Webnus <info@webnus.biz>
     * @param string $coupon
     * @param int $transaction_id
     * @return int
     */
    public function coupon_apply($coupon, $transaction_id)
    {
        $transaction = $this->get_transaction($transaction_id);
        $event_id = isset($transaction['event_id']) ? $transaction['event_id'] : NULL;

        // Verify validity of coupon
        if($this->coupon_check_validity($coupon, $event_id) != 1) return 0;

        $total = $transaction['total'];
        $discount = $this->coupon_get_discount($coupon, $total);
        
        $after_discount = $total - $discount;
        
        $transaction['price_details']['total'] = $after_discount;
        $transaction['price_details']['details'][] = array('amount'=>$discount, 'description'=>__('Discount', 'modern-events-calendar-lite'), 'type'=>'discount');
        
        $transaction['discount'] = $discount;
        $transaction['price'] = $after_discount;
        $transaction['coupon'] = $coupon;
        
        $this->update_transaction($transaction_id, $transaction);
        
        return $discount;
    }
    
    /**
     * Get discount of a coupon
     * @author Webnus <info@webnus.biz>
     * @param string $coupon
     * @param int $total
     * @return int
     */
    public function coupon_get_discount($coupon, $total)
    {
        $term = get_term_by('name', $coupon, 'mec_coupon');
        $coupon_id = isset($term->term_id) ? $term->term_id : 0;
        
        // Coupon is not exists
        if(!$coupon_id) return 0;
        
        $discount_type = get_term_meta($coupon_id, 'discount_type', true);
        $discount = get_term_meta($coupon_id, 'discount', true);

        if($discount_type == 'percent') $discount_amount = ($total*$discount)/100;
        else $discount_amount = min($discount, $total);
        
        return $discount_amount;
    }
    
    /**
     * Get id of a coupon by coupon number
     * @author Webnus <info@webnus.biz>
     * @param string $coupon
     * @return int
     */
    public function coupon_get_id($coupon)
    {
        $term = get_term_by('name', $coupon, 'mec_coupon');
        $coupon_id = isset($term->term_id) ? $term->term_id : 0;
        
        return $coupon_id;
    }

    /**
     * Get invoice link for certain transaction
     * @author Webnus <info@webnus.biz>
     * @param $transaction_id
     * @return string
     */
    public function get_invoice_link($transaction_id)
    {
        if(isset($this->settings['booking_invoice']) and !$this->settings['booking_invoice']) return '';

        $main = $this->getMain();

        $url = $main->URL('site');
        $url = $main->add_qs_var('method', 'mec-invoice', $url);
        return $main->add_qs_var('id', $transaction_id, $url);
    }

    public function get_bookings_by_transaction_id($transaction_id)
    {
        $main = $this->getMain();

        return get_posts(array(
            'posts_per_page' => -1,
            'post_type' => $main->get_book_post_type(),
            'meta_key' => 'mec_transaction_id',
            'meta_value' => $transaction_id,
        ));
    }

    public function get_thankyou_page($page_id, $transaction_id)
    {
        $main = $this->getMain();
        return $main->add_qs_var('transaction', $transaction_id, get_permalink($page_id));
    }

    public function invoice_link_shortcode()
    {
        $transaction = isset($_GET['transaction']) ? sanitize_text_field($_GET['transaction']) : NULL;
        if(!$transaction) return NULL;

        $book = $this->getBook();
        return '<a href="'.$book->get_invoice_link($transaction).'" target="_blank">'.__('Download Invoice', 'modern-events-calendar-lite').'</a>';
    }

    public function get_total_attendees($book_id)
    {
        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        $count = 0;
     
        if(is_array($attendees))
        {
            foreach($attendees as $key => $attendee)
            {
                if($key === 'attachments') continue;

                if(!isset($attendee[0]['MEC_TYPE_OF_DATA'])) $count++;
                elseif($attendee[0]['MEC_TYPE_OF_DATA'] != 'attachment') $count++;
            }
        }

        return $count;
    }

    public function get_transaction_id_book_id($book_id)
    {
        return get_post_meta($book_id, 'mec_transaction_id', true);
    }

    public function get_ticket_price_label($ticket, $date)
    {
        return $this->get_ticket_price_key($ticket, $date, 'price_label');
    }

    public function get_ticket_price($ticket, $date)
    {
        return $this->get_ticket_price_key($ticket, $date, 'price');
    }

    public function get_ticket_price_key($ticket, $date, $key)
    {
        $data = isset($ticket[$key]) ? $ticket[$key] : NULL;
        $price_dates = (isset($ticket['dates']) and is_array($ticket['dates'])) ? $ticket['dates'] : array();

        if(!count($price_dates)) return $data;

        $time = strtotime($date);
        foreach($price_dates as $price_date)
        {
            $start = $price_date['start'];
            $end = $price_date['end'];

            if($time >= strtotime($start) and $time <= strtotime($end))
            {
                if($key == 'price_label') $data = $price_date['label'];
                else $data = $price_date[$key];
            }
        }

        return $data;
    }

    /**
     * Returns tickets prices of an event for a certain date
     * @author Webnus <info@webnus.biz>
     * @param int $event_id
     * @param string $date
     * @param string $key
     * @return array
     */
    public function get_tickets_prices($event_id, $date, $key = 'price')
    {
        $prices = array();
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        // No Ticket Found!
        if(!is_array($tickets) or (is_array($tickets) and !count($tickets))) return $prices;

        foreach($tickets as $ticket_id=>$ticket)
        {
            $prices[$ticket_id] = $this->get_ticket_price_key($ticket, $date, $key);
        }

        return $prices;
    }
}