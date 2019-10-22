<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC elementor addon class
 * @author Webnus <info@webnus.biz>
 */
class MEC_addon_elementor extends MEC_base
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
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // MEC Factory class
        $this->factory = $this->getFactory();
        
        // MEC Main class
        $this->main = $this->getMain();
    }
    
    /**
     * Initialize the Elementor addon
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Elementor is not installed
        if(!did_action('elementor/loaded')) return false;

        add_action('elementor/widgets/widgets_registered', array($this, 'register_shortcode'));
        return true;
    }

    /**
     * Register MEC Elementor Shortcode
     * @author Webnus <info@webnus.biz>
     */
    public function register_shortcode()
    {
        require_once MEC_ABSPATH.'app/addons/elementor/shortcode.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor\MEC_addon_elementor_shortcode());
    }
    
    /**
     * Register the addon in Elementor
     * @author Webnus <info@webnus.biz>
     */
    public function map()
    {
    }
}