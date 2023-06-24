<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Partial Payment class.
 * @author Webnus <info@webnus.net>
 */
class MEC_partial extends MEC_base
{
    /**
     * @var MEC_main
     */
    public $main;

    /**
     * @var array
     */
    public $settings;

    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();

        // General Options
        $this->settings = $this->main->get_settings();
    }

    public function is_enabled()
    {
        return isset($this->settings['booking_partial_payment']) and $this->settings['booking_partial_payment'];
    }

    public function is_payable_per_event_enabled()
    {
        return $this->is_enabled() and isset($this->settings['booking_payable_per_event']) and $this->settings['booking_payable_per_event'];
    }

    public function is_fes_pp_section_enabled()
    {
        return $this->is_payable_per_event_enabled() and (!isset($this->settings['fes_section_booking_pp']) or (isset($this->settings['fes_section_booking_pp']) and $this->settings['fes_section_booking_pp']));
    }

    /**
     * @param $payable
     * @param string $type
     * @return array
     */
    public function validate_payable_options($payable, $type)
    {
        // Payable Type Validation
        if(!in_array($type, ['percent', 'amount'])) $type = 'percent';

        // Payable Validation
        if($type === 'percent')
        {
            $payable = (int) $payable;
            $payable = max(1, min($payable, 100));
        }
        else $payable = max(1, $payable);

        return [$payable, $type];
    }

    /**
     * @param int $total
     * @param int $event_id
     * @return int
     */
    public function calculate($total, $event_id)
    {
        [$payable_amount, $payable_type] = $this->get_validated_payable_options($event_id);

        $payable = $total;
        if($payable_type === 'percent')
        {
            $payable = $total * ($payable_amount / 100);
        }
        elseif($payable_type === 'amount')
        {
            $payable = min($total, $payable_amount);
        }

        return $payable;
    }

    /**
     * Get Payable Options
     * @param int $event_id
     * @return array
     */
    public function get_validated_payable_options($event_id)
    {
        // Global Options
        $payable = isset($this->settings['booking_payable']) ? $this->settings['booking_payable'] : 100;
        $payable_type = isset($this->settings['booking_payable_type']) ? $this->settings['booking_payable_type'] : 'percent';

        $booking_options = get_post_meta($event_id, 'mec_booking', true);
        if(!is_array($booking_options)) $booking_options = [];

        // Event Options
        $payable_inherit = isset($booking_options['bookings_payable_inherit']) ? (boolean) $booking_options['bookings_payable_inherit'] : true;
        if(!$payable_inherit)
        {
            if(isset($booking_options['bookings_payable']) and trim($booking_options['bookings_payable']) !== '') $payable = $booking_options['bookings_payable'];
            if(isset($booking_options['bookings_payable_type']) and trim($booking_options['bookings_payable_type']) !== '') $payable_type = $booking_options['bookings_payable_type'];
        }

        return $this->validate_payable_options($payable, $payable_type);
    }
}