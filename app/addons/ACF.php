<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC ACF addon class
 * @author Webnus <info@webnus.net>
 */
class MEC_addon_ACF extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // MEC Factory class
        $this->factory = $this->getFactory();
    }
    
    /**
     * Initialize the ACF addon
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        $this->factory->action('mec_wc_product_created', [$this, 'sync_acf_fields'], 10, 2);
        $this->factory->action('mec_wc_product_updated', [$this, 'sync_acf_fields'], 10, 2);
    }

    /**
     * @param int $product_id
     * @param int $event_id
     * @return void
     */
    public function sync_acf_fields($product_id, $event_id)
    {
        // ACF Plugin is not installed and activated
        if(!class_exists('ACF')) return;

        // Event ACF Data
        $data = get_fields($event_id, false);

        // Data is invalid
        if(!is_array($data)) return;
        if(!count($data)) return;

        // Store data for Product
        foreach($data as $key => $value)
        {
            update_field($key, $value, $product_id);
        }
    }
}