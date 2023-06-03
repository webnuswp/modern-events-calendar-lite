<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC The Newsletter Plugin addon class
 * @link https://www.thenewsletterplugin.com/
 * @author Webnus <info@webnus.net>
 */
class MEC_addon_TNP extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_main
     */
    public $main;
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // MEC Factory class
        $this->factory = $this->getFactory();
        
        // MEC Main class
        $this->main = $this->getMain();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize the TNP addon
     * @author Webnus <info@webnus.net>
     * @return boolean
     */
    public function init()
    {
        $this->factory->action('newsletter_register_blocks', array($this, 'register'));

        return true;
    }

    public function register()
    {
        if(!class_exists('TNP_Composer')) return;

        TNP_Composer::register_block(MEC_ABSPATH.'app'.DS.'addons'.DS.'tnp'.DS.'simple');
    }
}