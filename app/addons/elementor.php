<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC elementor addon class
 * @author Webnus <info@webnus.net>
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
     * @author Webnus <info@webnus.net>
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
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Elementor is not installed
        if(!did_action('elementor/loaded')) return false;

        add_action('elementor/widgets/register', array($this, 'register_shortcode'));

        add_action( 'elementor/preview/enqueue_styles', function() {
            wp_enqueue_style( 'mec-elementor-owl-carousel-css', plugins_url( '../../assets/packages/owl-carousel/owl.carousel.min.css', __FILE__ ), array() );
            wp_enqueue_style( 'mec-frontend-style',     plugins_url( '../../assets/css/frontend.min.css', __FILE__ ), array() );
        });

		add_action('elementor/editor/after_enqueue_scripts', function() {
			wp_enqueue_script('mec-elementor-owl-carousel-js',  plugin_dir_url( __FILE__ ) . '../../assets/packages/owl-carousel/owl.carousel.min.js');
            wp_enqueue_script('mec-elementor-frontend-js',      plugin_dir_url( __FILE__ ) . '../../assets/js/frontend.js');
		});

        return true;
    }

    /**
     * Register MEC Elementor Shortcode
     * @author Webnus <info@webnus.net>
     */
    public function register_shortcode()
    {
        require_once MEC_ABSPATH.'app/addons/elementor/shortcode.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new \Elementor\MEC_addon_elementor_shortcode());
    }

    /**
     * Register the addon in Elementor
     * @author Webnus <info@webnus.net>
     */
    public function map()
    {
    }
}