<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC WooCommerce class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_wc extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_main
     */
    public $main;

    /**
     * @var array
     */
    public $settings;

    /**
     * @var array
     */
    public $ml_settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();

        // General Options
        $this->settings = $this->main->get_settings();

        // MEC Multilingual Settings
        $this->ml_settings = $this->main->get_ml_settings();
    }
    
    /**
     * Initialize
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Pro version is required
        if(!$this->getPRO()) return;

        // WC Hooks
        if(isset($this->settings['wc_status']) and $this->settings['wc_status']) $this->factory->action('init', array($this, 'hooks'));
    }

    public function hooks()
    {
        // WooCommerce is not installed
        if(!class_exists('WooCommerce')) return;

        // WC library
        $wc = $this->getWC();

        // WooCommerce
        $this->factory->action('woocommerce_order_status_completed', array($wc, 'completed'));
        $this->factory->action('woocommerce_thankyou', array($wc, 'paid'));
        $this->factory->action('woocommerce_new_order_item', array($wc, 'meta'), 10, 2);
        $this->factory->action('woocommerce_order_status_cancelled', array($wc, 'cancelled'));
        $this->factory->action('woocommerce_order_status_refunded', array($wc, 'cancelled'));
        $this->factory->action('woocommerce_after_checkout_validation', array($this, 'validate'),10,2);
        $this->factory->action('wp', [$this, 'single_product_page']);

        $this->factory->filter('woocommerce_order_item_display_meta_key', array($this, 'display_key'), 10, 2);
        $this->factory->filter('woocommerce_order_item_display_meta_value', array($this, 'display_value'), 10, 2);
        $this->factory->filter('woocommerce_cart_item_name', array($this, 'display_name'), 10, 2);
        $this->factory->filter('woocommerce_cart_item_thumbnail', array($this, 'display_thumbnail'), 10, 2);
        $this->factory->filter('woocommerce_quantity_input_args', array($this, 'adjust_quantity'), 10, 2);
    }

    public function display_key($display_key, $meta)
    {
        if($meta->key == 'mec_event_id') $display_key = esc_html__('Event', 'modern-events-calendar-lite');
        elseif($meta->key == 'mec_date') $display_key = esc_html__('Date', 'modern-events-calendar-lite');
        elseif($meta->key == 'mec_other_dates') $display_key = esc_html__('Other Dates', 'modern-events-calendar-lite');
        elseif($meta->key == 'mec_transaction_id') $display_key = esc_html__('Transaction ID', 'modern-events-calendar-lite');

        return $display_key;
    }

    public function display_value($display_value, $meta)
    {
        if($meta->key == 'mec_event_id') $display_value = '<a href="'.get_permalink($meta->value).'">'.get_the_title($meta->value).'</a>';
        elseif($meta->key == 'mec_transaction_id') $display_value = $meta->value;
        elseif($meta->key == 'mec_date')
        {
            $date_format = (isset($this->ml_settings['booking_date_format1']) and trim($this->ml_settings['booking_date_format1'])) ? $this->ml_settings['booking_date_format1'] : 'Y-m-d';
            $time_format = get_option('time_format');

            if(strpos($date_format, 'h') !== false or strpos($date_format, 'H') !== false or strpos($date_format, 'g') !== false or strpos($date_format, 'G') !== false) $datetime_format = $date_format;
            else $datetime_format = $date_format.' '.$time_format;

            $dates = explode(':', $meta->value);

            $start_datetime = date_i18n($datetime_format, $dates[0]);
            $end_datetime = date_i18n($datetime_format, $dates[1]);

            $display_value = sprintf(esc_html__('%s to %s', 'modern-events-calendar-lite'), $start_datetime, $end_datetime);
        }
        elseif($meta->key == 'mec_other_dates')
        {
            $date_format = (isset($this->ml_settings['booking_date_format1']) and trim($this->ml_settings['booking_date_format1'])) ? $this->ml_settings['booking_date_format1'] : 'Y-m-d';
            $time_format = get_option('time_format');

            if(strpos($date_format, 'h') !== false or strpos($date_format, 'H') !== false or strpos($date_format, 'g') !== false or strpos($date_format, 'G') !== false) $datetime_format = $date_format;
            else $datetime_format = $date_format.' '.$time_format;

            $dates = (is_array($meta->value) ? $meta->value : explode(',', $meta->value));

            $date_values = array();
            foreach($dates as $date)
            {
                if(!trim($date)) continue;

                $timestamps = explode(':', $date);
                if(!isset($timestamps[0]) or !isset($timestamps[1])) continue;

                $start_datetime = date_i18n($datetime_format, $timestamps[0]);
                $end_datetime = date_i18n($datetime_format, $timestamps[1]);

                $date_values[] = sprintf(esc_html__('%s to %s', 'modern-events-calendar-lite'), $start_datetime, $end_datetime);
            }

            $display_value = implode('<br>', $date_values);
        }

        return $display_value;
    }

    public function display_name($name, $item)
    {
        if(!isset($item['mec_event_id']) or !trim($item['mec_event_id'])) return $name;
        if(!isset($item['mec_date']) or !trim($item['mec_date'])) return $name;

        $date_format = (isset($this->ml_settings['booking_date_format1']) and trim($this->ml_settings['booking_date_format1'])) ? $this->ml_settings['booking_date_format1'] : get_option('date_format');
        $other_dates = (isset($item['mec_other_dates']) and is_array($item['mec_other_dates'])) ? $item['mec_other_dates'] : array();

        $dates = array_merge(array($item['mec_date']), $other_dates);

        $formatted_dates = array();
        foreach($dates as $d)
        {
            $timestamps = explode(':', $d);
            $formatted_dates[] = date_i18n($date_format, $timestamps[0]);
        }

        $name .= ' ('.implode(', ', $formatted_dates).')';
        return $name;
    }

    public function display_thumbnail($image, $item)
    {
        if(!isset($item['mec_event_id']) or !trim($item['mec_event_id'])) return $image;
        if(!isset($item['product_id']) or !trim($item['product_id'])) return $image;

        $product_id = $item['product_id'];
        if(has_post_thumbnail($product_id)) return $image;

        $event_id = $item['mec_event_id'];
        if(has_post_thumbnail($event_id)) return get_the_post_thumbnail($event_id);

        return $image;
    }

    public function validate($data, $errors)
    {
        // Cart Items
        $items = WC()->cart->get_cart();

        // Book
        $book = $this->getBook();

        $printed = false;
        $all_items = array();
        foreach($items as $item)
        {
            $event_id = ($item['mec_event_id'] ?? NULL);
            if(!$event_id) continue;

            $product_id = ($item['product_id'] ?? NULL);
            $mec_ticket = get_post_meta($product_id, 'mec_ticket', true);

            $ex = explode(':', $mec_ticket);
            $ticket_id = ($ex[1] ?? NULL);
            if(!$ticket_id) continue;

            $quantity = ($item['quantity'] ?? 1);

            $date = ($item['mec_date'] ?? NULL);
            $timestamps = explode(':', $date);
            $timestamp = $timestamps[0];

            if(!isset($all_items[$event_id])) $all_items[$event_id] = array();
            if(!isset($all_items[$event_id][$ticket_id])) $all_items[$event_id][$ticket_id] = array();

            if(!isset($all_items[$event_id][$ticket_id][$timestamp])) $all_items[$event_id][$ticket_id][$timestamp] = $quantity;
            else $all_items[$event_id][$ticket_id][$timestamp] += $quantity;

            $availability = $book->get_tickets_availability($event_id, $timestamp);
            $tickets = get_post_meta($event_id, 'mec_tickets', true);

            // Ticket is not available
            if(!isset($availability[$ticket_id]) or ($availability[$ticket_id] != -1 and $availability[$ticket_id] < $quantity))
            {
                $printed = true;
                if($availability[$ticket_id] == '0') $errors->add('validation', sprintf(esc_html__('%s ticket is sold out!', 'modern-events-calendar-lite'), $tickets[$ticket_id]['name']));
                else $errors->add('validation', sprintf(esc_html__('Only %s slots remained for %s ticket so you cannot book %s ones.', 'modern-events-calendar-lite'), $availability[$ticket_id], $tickets[$ticket_id]['name'], $quantity));
            }
        }

        // Error already printed
        if($printed) return;

        foreach($all_items as $event_id => $tickets)
        {
            // User Booking Limits
            list($limit, $unlimited) = $book->get_user_booking_limit($event_id);

            $total_quantity = 0;
            foreach($tickets as $ticket_id => $timestamps)
            {
                foreach($timestamps as $timestamp => $quantity)
                {
                    $availability = $book->get_tickets_availability($event_id, $timestamp);
                    $tickets = get_post_meta($event_id, 'mec_tickets', true);

                    $total_quantity += $quantity;

                    // Ticket is not available
                    if(!isset($availability[$ticket_id]) or ($availability[$ticket_id] != -1 and $availability[$ticket_id] < $quantity))
                    {
                        if($availability[$ticket_id] == '0') $errors->add('validation', sprintf(esc_html__('%s ticket is sold out!', 'modern-events-calendar-lite'), $tickets[$ticket_id]['name']));
                        else $errors->add('validation', sprintf(esc_html__('Only %s slots remained for %s ticket so you cannot book %s ones.', 'modern-events-calendar-lite'), $availability[$ticket_id], $tickets[$ticket_id]['name'], $quantity));
                    }
                }
            }

            // Take Care of User Limit
            if(!$unlimited and $total_quantity > $limit)
            {
                $errors->add('validation', sprintf($this->main->m('booking_restriction_message3', esc_html__("Maximum allowed number of tickets that you can book is %s.", 'modern-events-calendar-lite')), $limit));
            }
        }
    }

    /**
     * @param $args
     * @param WC_Product $product
     * @return mixed
     */
    public function adjust_quantity($args, $product)
    {
        $mec_product = get_post_meta($product->get_id(), 'mec_ticket', true);

        // Make the quantity input as read-only so nobody can change its value
        if($mec_product and isset($args['input_value']) and is_numeric($args['input_value']))
        {
            $args['min_value'] = $args['input_value'];
            $args['max_value'] = $args['input_value'];
        }

        return $args;
    }

    public function single_product_page()
    {
        // WooCommerce Product
        if(is_product())
        {
            global $post;

            // MEC Product
            $is_mec = get_post_meta($post->ID, 'mec_ticket', true);
            if($is_mec)
            {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
            }
        }
    }
}