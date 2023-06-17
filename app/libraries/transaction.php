<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Transaction class.
 * @author Webnus <info@webnus.net>
 */
class MEC_transaction extends MEC_base
{
    public $transaction_id;
    public $transaction;
    public $settings;
    public $ml_settings;

    /**
     * @var MEC_book
     */
    private $book;

    /**
     * @var MEC_Main
     */
    private $main;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     * @param $transaction_id
     */
    public function __construct($transaction_id)
    {
        $this->main = $this->getMain();
        $this->transaction_id = $transaction_id;
        $this->settings = $this->main->get_settings();
        $this->ml_settings = $this->main->get_ml_settings();

        $this->book = $this->getBook();
        $this->transaction = $this->book->get_transaction($transaction_id);
    }

    public function get_total()
    {
        return (isset($this->transaction['total']) ? $this->transaction['total'] : NULL);
    }

    public function get_discount()
    {
        return (isset($this->transaction['discount']) ? $this->transaction['discount'] : NULL);
    }

    public function get_price()
    {
        return (isset($this->transaction['price']) ? $this->transaction['price'] : 0);
    }

    public function get_payable()
    {
        return (isset($this->transaction['payable']) ? $this->transaction['payable'] : 0);
    }

    public function get_price_html()
    {
        $total = $this->get_total();
        $payable = $this->get_payable();

        if($total == $payable) return '<span class="mec-transaction-price">'.esc_html($this->render_price($payable)).'</span>';
        else return '<span class="mec-transaction-price"><span class="mec-line-through">'.esc_html($this->render_price($total)).'</span><br>'.esc_html($this->render_price($payable)).'</span>';
    }

    public function render_price($amount)
    {
        return $this->main->render_price($amount, $this->get_event_id());
    }

    public function is_free()
    {
        return ($this->get_payable() ? false : true);
    }

    public function get_event_id()
    {
        return (isset($this->transaction['event_id']) ? $this->transaction['event_id'] : 0);
    }

    public function get_array()
    {
        return $this->transaction;
    }

    /**
     * @return WP_Post
     */
    public function get_event()
    {
        return get_post($this->get_event_id());
    }

    public function get_event_link()
    {
        $event = $this->get_event();
        return '<a href="'.esc_url(get_permalink($event)).'" target="_blank">'.MEC_kses::element($event->post_title).'</a>';
    }

    public function get_event_featured_image()
    {
        $event = $this->get_event();
        return get_the_post_thumbnail($event);
    }

    public function get_tickets()
    {
        $tickets = ((isset($this->transaction['tickets']) and is_array($this->transaction['tickets'])) ? $this->transaction['tickets'] : array());

        // Remove Useless Key
        if(isset($tickets['attachments'])) unset($tickets['attachments']);

        return $tickets;
    }

    public function get_event_tickets()
    {
        $tickets = get_post_meta($this->get_event_id(), 'mec_tickets', true);
        if(!is_array($tickets)) $tickets = array();

        return $tickets;
    }

    public function get_tickets_html()
    {
        $html = '<ul>';
        $event_tickets = $this->get_event_tickets();

        $tickets = $this->get_tickets();
        $rendered = array();

        foreach($tickets as $t)
        {
            $ticket_id = $t['id'];

            $ticket = (isset($event_tickets[$ticket_id]) ? $event_tickets[$ticket_id] : array());
            $count = $t['count'];

            if(!isset($rendered[$ticket_id])) $rendered[$ticket_id] = array('count' => 0, 'names' => array());

            $rendered[$ticket_id]['name'] = $ticket['name'];
            $rendered[$ticket_id]['count'] += $count;
            $rendered[$ticket_id]['names'][] = $t['name'];
        }

        foreach($rendered as $ticket_id => $row)
        {
            $names = array_unique($row['names']);

            $names_html = '';
            foreach($names as $name) $names_html .= '<h6>'.esc_html(stripslashes($name)).'</h6>';

            $html .= '<li><h5>'.esc_html(stripslashes($row['name'])).($row['count'] > 1 ? esc_html(' ('.$row['count'].')') : '').'</h5>'.$names_html.'</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    public function get_dates()
    {
        $all_dates = ((isset($this->transaction['all_dates']) and is_array($this->transaction['all_dates'])) ? $this->transaction['all_dates'] : array());
        $date = (isset($this->transaction['date']) ? $this->transaction['date'] : NULL);

        return (count($all_dates) ? $all_dates : array($date));
    }

    public function get_dates_html()
    {
        $html = '<ul>';

        $date_format = (isset($this->ml_settings['booking_date_format1']) and trim($this->ml_settings['booking_date_format1'])) ? $this->ml_settings['booking_date_format1'] : get_option('date_format');
        $time_format = get_option('time_format');

        $timestamps = $this->get_dates();
        foreach($timestamps as $timestamp)
        {
            $times = explode(':', $timestamp);

            $html .= '<li>'.sprintf(esc_html__('%s to %s', 'modern-events-calendar-lite'), $this->main->date_i18n($date_format.' '.$time_format, $times[0]), $this->main->date_i18n($date_format.' '.$time_format, $times[1])).'</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    public function get_coupon()
    {
        return (isset($this->transaction['coupon']) ? $this->transaction['coupon'] : NULL);
    }

    public function get_price_details()
    {
        return ((isset($this->transaction['price_details']) and is_array($this->transaction['price_details'])) ? $this->transaction['price_details'] : array());
    }

    public function get_price_details_html()
    {
        $price_details = $this->get_price_details();

        $html  = '<ul class="mec-checkout-price-details">';
        foreach($price_details['details'] as $detail)
        {
            $html .= '<li class="mec-checkout-price-detail mec-checkout-price-detail-type '.sanitize_html_class($detail['type']).'">
                <span class="mec-checkout-price-detail-description">'.esc_html($detail['description']).'</span>
                <span class="mec-checkout-price-detail-amount">'.MEC_kses::element($this->main->render_price($detail['amount'], $this->get_event_id())).'</span>
            </li>';
        }

        $html .= '</ul>';
        return $html;
    }
}