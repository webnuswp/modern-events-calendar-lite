<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_adminupcoming extends MEC_base
{
    public $factory;
    public $main;
    public $settings;
    public $status;

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

        // MEC Settings
        $this->settings = $this->main->get_settings();

        // Admin Calendar Status
        $this->status = (isset($_GET['adminview']) and $_GET['adminview'] === 'upcoming');
    }

    /**
     * Initialize admin upcoming feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Temporary
        return;

        // Disabled?
        if(!isset($this->settings['admin_upcoming_events']) or (isset($this->settings['admin_upcoming_events']) and !$this->settings['admin_upcoming_events'])) return;

        // Admin Upcoming List
        $this->factory->action('admin_head-edit.php', array($this, 'output'));

        // Assets
        if($this->status) $this->factory->action('admin_enqueue_scripts', array($this, 'assets'), 0);

        // Download Bookings
        if(isset($_GET['mec-dl-bookings']) && $_GET['mec-dl-bookings']) $this->factory->action('init', [$this, 'download_bookings']);
    }

    public function output()
    {
        global $current_screen;

        // Add it only on Event Page
        if('mec-events' != $current_screen->post_type) return;

        if($this->status)
        {
            $HTML = $this->getRender()->vlist([
                'sk-options' => ['list' => [
                    'style' => 'admin',
                    'month_divider' => 0,
                    'include_events_times' => 1,
                    'pagination' => 'loadmore',
                ]]
            ]);

            $this->factory->params('footer', function() use($HTML)
            {
                ?>
                <script>
                jQuery(document).ready(function($)
                {
                    $('hr.wp-header-end').before('<a href="<?php echo esc_url($this->main->remove_qs_var('adminview')); ?>" class="add-new-h2"><?php esc_html_e('Classic View', 'modern-events-calendar-lite'); ?></a>');
                    $('#posts-filter').before(`<div><?php echo $HTML; ?></div>`);
                });
                </script>
                <?php
            });
        }
        else
        {
            $this->factory->params('footer', function()
            {
                ?>
                <script>
                jQuery(document).ready(function($)
                {
                    $('hr.wp-header-end').before('<a href="<?php echo esc_url($this->main->add_qs_vars(array('adminview'=>'upcoming'))); ?>" class="add-new-h2"><?php esc_html_e('Upcoming View', 'modern-events-calendar-lite'); ?></a>');
                });
                </script>
                <?php
            });
        }
    }

    public function assets()
    {
        // JavaScript
        wp_enqueue_script('mec-admin-upcoming', $this->main->asset('js/admin-upcoming.js'), array('jquery'), $this->main->get_version());

        // Style
        wp_enqueue_style('mec-admin-upcoming', $this->main->asset('css/admin-upcoming.min.css'), array('mec-backend-style'), $this->main->get_version());
    }

    public function download_bookings()
    {
        // Not logged in?
        if(!get_current_user_id()) return;

        // Check Capability
        $capability = (current_user_can('administrator') ? 'manage_options' : 'mec_bookings');
        if(!current_user_can($capability)) return;

        $event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
        $occurrence = isset($_GET['occurrence']) ? $_GET['occurrence'] : 0;

        // Invalid Data
        if(!$event_id or !$occurrence) return;

        // Bookings
        $bookings = $this->main->get_bookings_by_event_occurrence($event_id, $occurrence);

        // No booking
        if(!count($bookings)) return;

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bookings-'.md5(time().mt_rand(100, 999)).'.csv');

        $booking_ids = array();
        foreach($bookings as $booking) $booking_ids[] = $booking->ID;

        $book = new MEC_feature_books();
        $book->csvexcel($booking_ids);

        exit;
    }
}