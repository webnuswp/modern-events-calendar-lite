<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_maintenance extends MEC_base
{
    public $factory;
    public $main;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();

        // Main Library
        $this->main = $this->getMain();
    }
    
    /**
     * Initialize maintenance feature
     *
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        $this->factory->action('mec_maintenance', [$this, 'maintenance']);
    }

    /**
     * MEC Maintenance Jobs
     *
     * @return void
     */
    public function maintenance()
    {
        // Settings
        $settings = $this->main->get_settings();

        // Trash Interval
        $trash_interval = isset($settings['events_trash_interval']) ? (int) $settings['events_trash_interval'] : 0;

        // Do Events Trash
        if($trash_interval) $this->events('trash', $trash_interval);

        // Purge Interval
        $purge_interval = isset($settings['events_purge_interval']) ? (int) $settings['events_purge_interval'] : 0;

        // Do Events Purge
        if($purge_interval) $this->events('purge', $purge_interval);
    }

    public function events($type, $interval)
    {
        // Date
        $date = date('Y-m-d', strtotime('-'.$interval.' Months'));

        // DB
        $db = $this->getDB();

        // Events
        $event_ids = $db->select("SELECT post_id FROM `#__mec_events` WHERE `end` != '0000-00-00' AND `end` < '".$date."'", 'loadColumn');

        // Trash / Purge
        foreach($event_ids as $event_id)
        {
            if($type === 'trash') wp_trash_post($event_id);
            elseif($type === 'purge') wp_delete_post($event_id, true);
        }
    }
}