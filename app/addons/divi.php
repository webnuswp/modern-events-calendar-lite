<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Divi addon class
 * @author Webnus <info@webnus.biz>
 */
class MEC_addon_divi extends MEC_base
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
        // Divi is not installed
        $theme = wp_get_theme(); // gets the current theme
        if ( 'Divi' != $theme->name ) return false;
        add_action( 'divi_extensions_init', array($this, 'mecdivi_initialize_extension') );	
        return true;
    }

	/**
	 * Creates the extension's main class instance.
	 *
	 * @since 1.0.0
	 */
	public function mecdivi_initialize_extension() {
		require_once plugin_dir_path( __FILE__ ) . 'divi/includes/Divi.php';
		require_once plugin_dir_path( __FILE__ ) . 'divi/includes/MECShortcodesForDivi.php';
	}

}
