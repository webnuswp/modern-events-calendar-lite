<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_admincalendar extends MEC_base
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
        $this->status = (isset($_GET['admincalendar']) and (boolean) sanitize_text_field($_GET['admincalendar']));
    }

    /**
     * Initialize admin calendar feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Temporary
        return;

        // Disabled?
        if(!isset($this->settings['admin_calendar']) or (isset($this->settings['admin_calendar']) and !$this->settings['admin_calendar'])) return;

        // Admin Calendar
        $this->factory->action('admin_head-edit.php', array($this, 'output'));

        // Assets
        if($this->status) $this->factory->action('admin_enqueue_scripts', array($this, 'assets'), 0);
    }

    public function output()
    {
        global $current_screen;

        // Add it only on Event Page
        if('mec-events' != $current_screen->post_type) return;

        if($this->status)
        {
            $HTML = $this->getRender()->vmonth(array('sk-options' => array('monthly_view' => array('style' => 'admin'))));
            $this->factory->params('footer', function() use($HTML)
            {
                ?>
                <style>#posts-filter, ul.subsubsub{display: none;}</style>
                <script>
                jQuery(document).ready(function($)
                {
                    $('hr.wp-header-end').before('<a href="<?php echo esc_url($this->main->remove_qs_var('admincalendar')); ?>" class="add-new-h2"><?php esc_html_e('Classic View', 'modern-events-calendar-lite'); ?></a>');
                    $('#posts-filter').before(`<div><?php echo MEC_kses::full($HTML); ?></div>`);
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
                    $('hr.wp-header-end').before('<a href="<?php echo esc_url($this->main->add_qs_vars(array('admincalendar'=>1))); ?>" class="add-new-h2"><?php esc_html_e('Calendar View', 'modern-events-calendar-lite'); ?></a>');
                });
                </script>
                <?php
            });
        }
    }

    public function assets()
    {
        // JavaScript
        wp_enqueue_script('mec-admin-calendar-script', $this->main->asset('js/admin-calendar.js'), array('jquery'), $this->main->get_version());

        // Styles
        wp_enqueue_style('mec-admin-calendar-style', $this->main->asset('css/admin-calendar.min.css'), array('mec-backend-style'), $this->main->get_version());
    }
}