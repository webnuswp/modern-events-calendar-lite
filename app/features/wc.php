<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC WooCommerce class.
 * @author Webnus <info@webnus.biz>
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
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();

        // General Options
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Pro version is required
        if(!$this->getPRO()) return false;

        // WC Hooks
        $this->factory->action('init', array($this, 'hooks'));
    }

    public function hooks()
    {
        // WC System
        $WC_status = (isset($this->settings['wc_status']) and $this->settings['wc_status'] and class_exists('WooCommerce')) ? true : false;

        // WC system is disabled
        if(!$WC_status) return false;

        // WC library
        $wc = $this->getWC();

        // WooCommerce
        $this->factory->action('woocommerce_order_status_completed', array($wc, 'completed'), 10, 1);
        $this->factory->action('woocommerce_thankyou', array($wc, 'paid'), 10, 1);
        $this->factory->action('woocommerce_new_order_item', array($wc, 'meta'), 10, 2);
        $this->factory->action('woocommerce_order_status_cancelled', array($wc, 'cancelled'), 10, 1);
        $this->factory->action('woocommerce_order_status_refunded', array($wc, 'cancelled'), 10, 1);

        $this->factory->filter('woocommerce_order_item_display_meta_key', array($this, 'display_key'), 10, 2);
        $this->factory->filter('woocommerce_order_item_display_meta_value', array($this, 'display_value'), 10, 2);
    }

    public function display_key($display_key, $meta)
    {
        if($meta->key == 'mec_event_id') $display_key = __('Event', 'modern-events-calendar-lite');
        elseif($meta->key == 'mec_date') $display_key = __('Date', 'modern-events-calendar-lite');
        elseif($meta->key == 'mec_transaction_id') $display_key = __('Transaction ID', 'modern-events-calendar-lite');

        return $display_key;
    }

    public function display_value($display_value, $meta)
    {
        if($meta->key == 'mec_event_id') $display_value = '<a href="'.get_permalink($meta->value).'">'.get_the_title($meta->value).'</a>';
        elseif($meta->key == 'mec_transaction_id') $display_value = $meta->value;
        elseif($meta->key == 'mec_date')
        {
            $date_format = (isset($this->settings['booking_date_format1']) and trim($this->settings['booking_date_format1'])) ? $this->settings['booking_date_format1'] : 'Y-m-d';
            $time_format = get_option('time_format');

            $dates = explode(':', $meta->value);

            $start_datetime = date($date_format.' '.$time_format, $dates[0]);
            $end_datetime = date($date_format.' '.$time_format, $dates[1]);

            $display_value = sprintf(__('%s to %s', 'modern-events-calendar-lite'), $start_datetime, $end_datetime);
        }

        return $display_value;
    }
}