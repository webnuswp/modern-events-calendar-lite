<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Paid Membership Pro addon class
 * @author Webnus <info@webnus.net>
 */
class MEC_addon_PMP extends MEC_base
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
     * Initialize the PMP addon
     * @author Webnus <info@webnus.net>
     * @return boolean
     */
    public function init()
    {
        $event_restriction = (isset($this->settings['pmp_status']) and $this->settings['pmp_status']);
        $booking_restriction = (isset($this->settings['pmp_booking_restriction']) and $this->settings['pmp_booking_restriction']);

        // Module is not enabled
        if(!$event_restriction and !$booking_restriction) return false;

        // Event Restriction
        if($event_restriction)
        {
            // Metabox
            add_action('admin_menu', array($this, 'metabox'));

            // Display Access Error
            add_filter('mec_show_event_details_page', array($this, 'check'), 10, 2);
        }

        // Booking Restriction
        if($booking_restriction)
        {
            add_filter('mec_booking_module_abort', array($this, 'booking_abort'), 10, 2);
        }

        return true;
    }

    public function metabox()
    {
        if(!defined('PMPRO_VERSION')) return;

        // Register
        add_meta_box('pmpro_page_meta', esc_html__('Require Membership', 'modern-events-calendar-lite'), 'pmpro_page_meta', $this->main->get_main_post_type(), 'side', 'high');
    }

    public function check($status, $event_id)
    {
        if(!defined('PMPRO_VERSION')) return $status;

        // Has Access
        if(function_exists('pmpro_has_membership_access'))
        {
            $response = pmpro_has_membership_access($event_id, NULL, true);
            $available = (isset($response[0]) ? $response[0] : true);

            if(!$available)
            {
                $post_membership_levels_ids = $response[1];
                $post_membership_levels_names = $response[2];

                $content = pmpro_get_no_access_message('', $post_membership_levels_ids, $post_membership_levels_names);
                $status = '<div class="mec-wrap mec-no-access-error"><h1>'.get_the_title($event_id).'</h1>'.MEC_kses::page($content).'</div>';
            }
        }

        return $status;
    }

    public function booking_abort($abort, $event)
    {
        if(!function_exists('pmpro_getMembershipLevelsForUser')) return $abort;

        // Event ID
        $event_id = $event->ID;
        if(!$event_id) return $abort;

        // Event Categories
        $categories = (isset($event->data) and isset($event->data->categories) and is_array($event->data->categories)) ? $event->data->categories : [];

        // Event has no category
        if(!count($categories)) return $abort;

        // User ID
        $user_id = get_current_user_id();

        // Booking Restriction Options
        $options = ((isset($this->settings['pmp_booking']) and is_array($this->settings['pmp_booking'])) ? $this->settings['pmp_booking'] : array());

        $needed_levels = array();
        foreach($options as $level_id => $cats)
        {
            foreach($categories as $category)
            {
                if(in_array($category['id'], $cats)) $needed_levels[] = $level_id;
            }
        }

        $needed_levels = array_unique($needed_levels);
        if($needed_levels and !pmpro_hasMembershipLevel($needed_levels, $user_id))
        {
            return pmpro_get_no_access_message('', $needed_levels);
        }

        return $abort;
    }
}