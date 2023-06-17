<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Cart class.
 * @author Webnus <info@webnus.net>
 */
class MEC_cart extends MEC_base
{
    /**
     * @var MEC_main
     */
    private $main;
    private $settings;
    private $ticket_names = array();

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Main
        $this->main = $this->getMain();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }

    public function add($transaction_id)
    {
        $cart_id = $this->get_cart_id();

        $cart = $this->get_cart($cart_id);
        $cart[] = $transaction_id;

        $this->update_cart($cart_id, $cart);

        // Add to Ticket Names
        $this->ticket_names = array_merge($this->ticket_names, $this->get_ticket_names($transaction_id));

        return $this;
    }

    public function remove($transaction_id)
    {
        $cart_id = $this->get_cart_id();

        $cart = $this->get_cart($cart_id);
        if(!in_array($transaction_id, $cart)) return false;

        $key = array_search($transaction_id, $cart);
        if($key !== false) unset($cart[$key]);

        $this->update_cart($cart_id, $cart);

        return true;
    }

    public function next()
    {
        $ticket_names = implode(', ', $this->ticket_names);
        if(trim($ticket_names) === '') $ticket_names = esc_html__('Ticket', 'modern-events-calendar-lite');

        // Checkout URL
        if(isset($this->settings['cart_after_add']) and $this->settings['cart_after_add'] == 'checkout') return array('type' => 'url', 'url' => $this->get_checkout_url());
        // Optional Checkout URL
        if(isset($this->settings['cart_after_add']) and $this->settings['cart_after_add'] == 'optional_cart') return array('type' => 'message', 'message' => '<div class="woocommerce-notices-wrapper"><div class="woocommerce-message" role="alert"><a href="'.esc_url($this->get_cart_url()).'" tabindex="1" class="button wc-forward" target="_parent">'.esc_html__('View cart', 'modern-events-calendar-lite').'</a> '.esc_html(sprintf(_n('“%s” has been added to your cart.', '“%s” have been added to your cart.', count($this->ticket_names), 'modern-events-calendar-lite'), $ticket_names)).'</div></div>');
        // Optional Cart URL
        if(isset($this->settings['cart_after_add']) and $this->settings['cart_after_add'] == 'optional_chckout') return array('type' => 'message', 'message' => '<div class="woocommerce-notices-wrapper"><div class="woocommerce-message" role="alert"><a href="'.esc_url($this->get_checkout_url()).'" tabindex="1" class="button wc-forward" target="_parent">'.esc_html__('Checkout', 'modern-events-calendar-lite').'</a> '.esc_html(sprintf(_n('“%s” has been added to your cart.', '“%s” have been added to your cart.', count($this->ticket_names), 'modern-events-calendar-lite'), $ticket_names)).'</div></div>');
        // Cart URL
        else return array('type' => 'url', 'url' => $this->get_cart_url());
    }

    public function get_cart($cart_id)
    {
        $cart = get_option('mec_cart_'.$cart_id, NULL);
        if(is_null($cart))
        {
            $cart = array();
            update_option('mec_cart_'.$cart_id, $cart, false);
        }

        if(!is_array($cart)) $cart = array();
        return $cart;
    }

    public function update_cart($cart_id, $value)
    {
        return update_option('mec_cart_'.$cart_id, $value, false);
    }

    public function archive_cart($cart_id)
    {
        $value = $this->get_cart($cart_id);
        return update_option('mec_cart_'.$cart_id.'_archived', $value, false);
    }

    public function get_archived_cart($cart_id)
    {
        $cart = get_option('mec_cart_'.$cart_id.'_archived', NULL);

        if(!is_array($cart)) $cart = array();
        return $cart;
    }

    public function get_cart_id()
    {
        $cart_id = (isset($_COOKIE['mec_cart']) and trim($_COOKIE['mec_cart'])) ? sanitize_text_field($_COOKIE['mec_cart']) : NULL;
        if(!$cart_id and !headers_sent()) $cart_id = $this->get_fresh_cart_id();

        return $cart_id;
    }

    public function get_fresh_cart_id()
    {
        $cart_id = mt_rand(100000000, 999999999);
        setcookie('mec_cart', $cart_id, (time()+(30*86400)), '/');

        return $cart_id;
    }

    public function get_checkout_url()
    {
        $page_id = (isset($this->settings['checkout_page']) and trim($this->settings['checkout_page'])) ? $this->settings['checkout_page'] : NULL;
        return ($page_id ? get_permalink($page_id) : home_url());
    }

    public function get_cart_url()
    {
        $page_id = (isset($this->settings['cart_page']) and trim($this->settings['cart_page'])) ? $this->settings['cart_page'] : NULL;
        return ($page_id ? get_permalink($page_id) : home_url());
    }

    public function get_ticket_names($transaction_id)
    {
        $book = $this->getBook();
        $transaction = $book->get_transaction($transaction_id);

        $event_id = ((isset($transaction['event_id']) and $transaction['event_id']) ? $transaction['event_id'] : 0);
        $tickets = ((isset($transaction['tickets']) and is_array($transaction['tickets'])) ? $transaction['tickets'] : array());

        $event_tickets = get_post_meta($event_id, 'mec_tickets', true);
        if(!is_array($event_tickets)) $event_tickets = array();

        $names = array();
        foreach($tickets as $tkey => $ticket)
        {
            if(!is_numeric($tkey)) continue;

            $ticket_id = (isset($ticket['id']) and $ticket['id']) ? $ticket['id'] : 0;
            if(!$ticket_id) continue;

            $ticket = isset($event_tickets[$ticket_id]) ? $event_tickets[$ticket_id] : array();
            $ticket_name = (isset($ticket['name']) ? $ticket['name'] : '');

            if(trim($ticket_name)) $names[] = $ticket_name;
        }

        return array_unique($names);
    }

    public function get_payable($cart = NULL)
    {
        if(is_null($cart))
        {
            $cart_id = $this->get_cart_id();
            $cart = $this->get_cart($cart_id);
        }

        // Booking Library
        $book = $this->getBook();

        $payable = 0;
        foreach($cart as $transaction_id)
        {
            $TO = $book->get_TO($transaction_id);

            $payable += $TO->get_payable();
        }

        return $payable;
    }

    public function is_free($cart = NULL)
    {
        $payable = $this->get_payable($cart);
        return ($payable > 0 ? false : true);
    }

    public function clear($cart_id)
    {
        // Save it for future usage
        $this->archive_cart($cart_id);

        // Make it empty
        $this->update_cart($cart_id, array());

        // New Cart ID
        $this->get_fresh_cart_id();
    }

    public function get_first_event_id($cart = NULL)
    {
        if(is_null($cart))
        {
            $cart_id = $this->get_cart_id();
            $cart = $this->get_cart($cart_id);
        }

        // Booking Library
        $book = $this->getBook();

        $event_id = NULL;
        foreach($cart as $transaction_id)
        {
            $TO = $book->get_TO($transaction_id);

            $event_id = $TO->get_event_id();
            break;
        }

        return $event_id;
    }

    public function get_invoice_link($cart_id)
    {
        if(isset($this->settings['mec_cart_invoice']) and !$this->settings['mec_cart_invoice']) return '';

        $url = $this->main->URL('site');
        $url = $this->main->add_qs_var('method', 'mec-cart-invoice', $url);

        // Invoice Key
        $url = $this->main->add_qs_var('mec-key', $cart_id, $url);

        return apply_filters('mec_cart_invoice_url', $url, $cart_id);
    }

    public function is_done($cart_id)
    {
        return (bool) $this->get_archived_cart($cart_id);
    }
}