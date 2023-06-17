<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Partial Payment class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_partial extends MEC_base
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
     * Partial Payment Library
     * @var MEC_partial
     */
    public $partial_payment;

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

        // Import Partial Payment
        $this->partial_payment = $this->getPartialPayment();
    }
    
    /**
     * Initialize
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Pro version is required
        if(!$this->getPRO()) return false;

        // Partial Payment is not enabled
        if(!$this->partial_payment->is_enabled()) return false;

        // Validate Settings Form
        add_filter('mec_validate_general_settings_options', [$this, 'validate_settings_form'], 10, 1);

        return true;
    }

    public function validate_settings_form($all_options)
    {
        // It's not a settings form
        if(!isset($all_options['settings'])) return $all_options;

        $payable = isset($all_options['settings']['booking_payable']) ? $all_options['settings']['booking_payable'] : 100;
        $payable_type = isset($all_options['settings']['booking_payable_type']) ? $all_options['settings']['booking_payable_type'] : 'percent';

        // Validate
        [$payable, $payable_type] = $this->partial_payment->validate_payable_options($payable, $payable_type);

        $all_options['settings']['booking_payable'] = $payable;
        $all_options['settings']['booking_payable_type'] = $payable_type;

        return $all_options;
    }
}