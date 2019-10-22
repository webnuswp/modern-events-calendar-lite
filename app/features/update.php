<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC update class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_update extends MEC_base
{
    public $factory;
    public $main;
    public $db;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();
        
        // Import MEC DB
        $this->db = $this->getDB();

        // Import MEC Factory
        $this->factory = $this->getFactory();
    }
    
    /**
     * Initialize update feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
		// Plugin is not installed yet so no need to run these upgrades
        if(!get_option('mec_installed', 0)) return;

        // Run the Update Function
        $this->factory->action('wp_loaded', array($this, 'update'));
    }

    public function update()
    {
        $version = get_option('mec_version', '1.0.0');

        // It's updated to latest version
        if(version_compare($version, $this->main->get_version(), '>=')) return;

        // Run the updates one by one
        if(version_compare($version, '1.0.3', '<')) $this->version103();
        if(version_compare($version, '1.3.0', '<')) $this->version130();
        if(version_compare($version, '1.5.0', '<')) $this->version150();
        if(version_compare($version, '2.2.0', '<')) $this->version220();
        if(version_compare($version, '2.9.0', '<')) $this->version290();
        if(version_compare($version, '3.2.0', '<')) $this->version320();
        if(version_compare($version, '3.5.0', '<')) $this->version350();
        if(version_compare($version, '4.0.0', '<')) $this->version400();
        if(version_compare($version, '4.3.0', '<')) $this->version430();
        if(version_compare($version, '4.4.6', '<')) $this->version446();
        if(version_compare($version, '4.6.1', '<')) $this->version461();

        // Update to latest version to prevent running the code twice
        update_option('mec_version', $this->main->get_version());
    }
    
    /**
     * Update database to version 1.0.3
     * @author Webnus <info@webnus.biz>
     */
    public function version103()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = array();
        
        // Merge new options with previous options
        $current['notifications']['new_event'] = array
        (
            'status'=>'1',
            'subject'=>'A new event is added.',
            'recipients'=>'',
            'content'=>"Hello,

            A new event just added. The event title is %%event_title%% and it's status is %%event_status%%
            The new event may need to be published. Please use this link for managing your website events: %%admin_link%%

            Regards,
            %%blog_name%%"
        );
        
        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }
    
    /**
     * Update database to version 1.3.0
     * @author Webnus <info@webnus.biz>
     */
    public function version130()
    {
        $this->db->q("ALTER TABLE `#__mec_events` ADD `days` TEXT NULL DEFAULT NULL, ADD `time_start` INT(10) NOT NULL DEFAULT '0', ADD `time_end` INT(10) NOT NULL DEFAULT '0'");
    }
    
    /**
     * Update database to version 1.5.0
     * @author Webnus <info@webnus.biz>
     */
    public function version150()
    {
        $this->db->q("ALTER TABLE `#__mec_events` ADD `not_in_days` TEXT NOT NULL DEFAULT '' AFTER `days`");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `days` `days` TEXT NOT NULL DEFAULT ''");
    }

    /**
     * Update database to version 2.2.0
     * @author Webnus <info@webnus.biz>
     */
    public function version220()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = array();

        // Merge new options with previous options
        $current['notifications']['booking_reminder'] = array
        (
            'status'=>'0',
            'subject'=>'Booking Reminder',
            'recipients'=>'',
            'days'=>'1,3',
            'content'=>"Hello,

            This email is to remind you that you booked %%event_title%% event on %%book_date%% date.
            We're looking forward to see you at %%event_location_address%%. You can contact %%event_organizer_email%% if you have any questions.

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version290()
    {
        $this->db->q("UPDATE `#__postmeta` SET `meta_value`=CONCAT(',', `meta_value`) WHERE `meta_key`='mec_ticket_id'");
        $this->db->q("UPDATE `#__postmeta` SET `meta_value`=CONCAT(`meta_value`, ',') WHERE `meta_key`='mec_ticket_id'");
    }

    public function version320()
    {
        $this->db->q("ALTER TABLE `#__mec_events` DROP INDEX `repeat`;");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `rinterval` `rinterval` VARCHAR(10);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `year` `year` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `month` `month` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `day` `day` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `week` `week` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `weekday` `weekday` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` CHANGE `weekdays` `weekdays` VARCHAR(80);");
        $this->db->q("ALTER TABLE `#__mec_events` ADD INDEX( `start`, `end`, `repeat`, `rinterval`, `year`, `month`, `day`, `week`, `weekday`, `weekdays`, `time_start`, `time_end`);");
    }

    public function version350()
    {
        $this->db->q("CREATE TABLE IF NOT EXISTS `#__mec_dates` (
          `id` int(10) UNSIGNED NOT NULL,
          `post_id` int(10) NOT NULL,
          `dstart` date NOT NULL,
          `dend` date NOT NULL,
          `type` enum('include','exclude') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'include'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->db->q("ALTER TABLE `#__mec_dates` ADD PRIMARY KEY (`id`), ADD KEY `post_id` (`post_id`), ADD KEY `type` (`type`);");
        $this->db->q("ALTER TABLE `#__mec_dates` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;");

        $custom_days = $this->db->select("SELECT * FROM `#__mec_events` WHERE `days`!=''", 'loadAssocList');
        foreach($custom_days as $custom_day)
        {
            $days = explode(',', trim($custom_day['days'], ', '));

            $new_days_str = '';
            foreach($days as $day)
            {
                if(!trim($day)) continue;

                $start = $day;
                $end = $day;

                $this->db->q("INSERT INTO `#__mec_dates` (`post_id`,`dstart`,`dend`,`type`) VALUES ('".$custom_day['post_id']."','$start','$end','include')");

                $new_days_str .= $start.':'.$end.',';
            }

            $new_days_str = trim($new_days_str, ', ');

            $this->db->q("UPDATE `#__mec_events` SET `days`='".$new_days_str."' WHERE `post_id`='".$custom_day['post_id']."'");
            update_post_meta($custom_day['post_id'], 'mec_in_days', $new_days_str);
        }
    }

    public function version400()
    {
        // Add Columns
        $this->db->q("ALTER TABLE `#__mec_dates` ADD `tstart` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `dend`;");
        $this->db->q("ALTER TABLE `#__mec_dates` ADD `tend` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `tstart`;");

        // Add Indexes
        $this->db->q("ALTER TABLE `#__mec_dates` ADD INDEX (`tstart`);");
        $this->db->q("ALTER TABLE `#__mec_dates` ADD INDEX (`tend`);");

        // Drop Columns
        $this->db->q("ALTER TABLE `#__mec_dates` DROP COLUMN `type`;");

        // Scheduler
        $schedule = $this->getSchedule();

        // Add Schedule for All Events
        $events = $this->main->get_events();
        foreach($events as $event) $schedule->reschedule($event->ID, 50);

        // Scheduler Cron job
        if(!wp_next_scheduled('mec_scheduler')) wp_schedule_event(time(), 'hourly', 'mec_scheduler');
    }

    public function version430()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = array();

        // Merge new options with previous options
        $current['notifications']['cancellation_notification'] = array
        (
            'status'=>'0',
            'subject'=>'Your booking is canceled.',
            'recipients'=>'',
            'send_to_admin'=>'1',
            'send_to_organizer'=>'0',
            'send_to_user'=>'0',
            'content'=>"Hi %%name%%,

            For your information, your booking for %%event_title%% at %%book_date%% is canceled.

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }

    public function version446()
    {
        if(!wp_next_scheduled('mec_syncScheduler')) wp_schedule_event(time(), 'daily', 'mec_syncScheduler');
    }

    public function version461()
    {
        // Get current MEC options
        $current = get_option('mec_options', array());
        if(is_string($current) and trim($current) == '') $current = array();

        // Merge new options with previous options
        $current['notifications']['user_event_publishing'] = array
        (
            'status'=>'0',
            'subject'=>'Your event gets published!',
            'recipients'=>'',
            'content'=>"Hello %%name%%,

            Your event gets published. You can check it below:

            <a href=\"%%event_link%%\">%%event_title%%</a>

            Regards,
            %%blog_name%%"
        );

        // Update it only if options already exists.
        if(get_option('mec_options') !== false)
        {
            // Save new options
            update_option('mec_options', $current);
        }
    }
}