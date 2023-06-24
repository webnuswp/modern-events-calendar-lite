<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC profile class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_profile extends MEC_base
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
     * @var MEC_book
     */
    public $book;

    /**
     * @var string
     */
    public $PT;

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

        // Import MEC Book
        $this->book = $this->getBook();

        // Booking Post Type
        $this->PT = $this->main->get_book_post_type();
    }

    /**
     * Initialize profile feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Profile Shortcode
        $this->factory->shortcode('MEC_profile', array($this, 'profile'));
    }

    /**
     * Show user profile
     * @param array $atts
     * @return string
     */
    public function profile($atts = array())
    {
        // Force to array
        if(!is_array($atts)) $atts = array();

        // Show login/register message if user is not logged in and guest submission is not enabled.
        if(!is_user_logged_in())
        {
            // Show message
            $message = sprintf(esc_html__('Please %s/%s in order to see your bookings / profile.', 'modern-events-calendar-lite'), '<a href="'.wp_login_url($this->main->get_full_url()).'">'.esc_html__('Login', 'modern-events-calendar-lite').'</a>', '<a href="'.wp_registration_url().'">'.esc_html__('Register', 'modern-events-calendar-lite').'</a>');

            ob_start();
            include MEC::import('app.features.profile.message', true, true);
            return ob_get_clean();
        }

        // Needs Pro
        if(!$this->getPRO())
        {
            // Show message
            $message = sprintf(esc_html__('To use this feature you should upgrade to %s first.', 'modern-events-calendar-lite'), '<a href="'.esc_url($this->main->get_pro_link()).'" target="_blank">'.esc_html__('MEC Pro', 'modern-events-calendar-lite').'</a>');

            ob_start();
            include MEC::import('app.features.profile.message', true, true);
            return ob_get_clean();
        }


        $path = MEC::import('app.features.profile.profile', true, true);

        ob_start();
        include $path;
        return ob_get_clean();
    }
}