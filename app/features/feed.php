<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC feed class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_feed extends MEC_base
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
     * @var MEC_feed
     */
    public $feed;
    public $PT;
    public $events;
    public $settings;

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
        
        // Import MEC Feed
        $this->feed = $this->getFeed();
        
        // MEC Post Type Name
        $this->PT = $this->main->get_main_post_type();

        // General Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize feed feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        remove_all_actions('do_feed_rss2');
        $this->factory->action('do_feed_rss2', array($this, 'rss2'));

        // Include Featured Image
        if(!isset($this->settings['include_image_in_feed']) or (isset($this->settings['include_image_in_feed']) and $this->settings['include_image_in_feed']))
        {
            add_filter('get_the_excerpt', array($this, 'include_featured_image'), 10, 2);
        }

        if(!is_admin()) $this->factory->action('init', array($this, 'ical'), 999);
    }
    
    /**
     * Do the feed
     * @author Webnus <info@webnus.net>
     * @param string $for_comments
     */
    public function rss2($for_comments)
    {
        $rss2 = MEC::import('app.features.feed.rss2', true, true);

        if(get_query_var('post_type') == $this->PT)
        {
            // Fetch Events
            $this->events = $this->fetch();

            // Include Feed template
            include_once $rss2;
        }
        elseif(get_query_var('taxonomy') == 'mec_category')
        {
            $q = get_queried_object();
            $term_id = $q->term_id;

            // Fetch Events
            $this->events = $this->fetch($term_id);

            // Include Feed template
            include_once $rss2;
        }
        else do_feed_rss2($for_comments); // Call default function
    }
    
    /**
     * Returns the events
     * @author Webnus <info@webnus.net>
     * @param $category
     * @return array
     */
    public function fetch($category = NULL)
    {
        $args = array(
            'sk-options'=>array(
                'list'=>array(
                    'limit'=>get_option('posts_per_rss', 12),
                )
            ),
            'category'=>$category
        );

        $EO = new MEC_skin_list(); // Events Object
        $EO->initialize($args);
        $EO->search();
        
        return $EO->fetch();
    }

    /**
     * @param string $excerpt
     * @param WP_Post $post
     * @return string
     */
    public function include_featured_image($excerpt, $post = NULL)
    {
        // Only RSS
        if(!is_feed()) return $excerpt;

        // Get Current Post
        if(!$post) $post = get_post();
        if(!$post) return $excerpt;

        // It's not event
        if($post->post_type != $this->main->get_main_post_type()) return $excerpt;

        $image = get_the_post_thumbnail($post);
        if(trim($image)) $excerpt = $image.' '.$excerpt;

        return $excerpt;
    }

    public function ical()
    {
        $ical_feed = (isset($_GET['mec-ical-feed']) and sanitize_text_field($_GET['mec-ical-feed']));
        if(!$ical_feed) return false;

        // Feed is not enabled
        if(!isset($this->settings['ical_feed']) or (isset($this->settings['ical_feed']) and !$this->settings['ical_feed'])) return false;

        $only_upcoming_events = (isset($this->settings['ical_feed_upcoming']) and $this->settings['ical_feed_upcoming']);
        if($only_upcoming_events)
        {
            $event_ids = $this->main->get_upcoming_event_ids(current_time('timestamp'), 'publish');
        }
        else
        {
            $events = $this->main->get_events('-1');

            $event_ids = array();
            foreach($events as $event) $event_ids[] = $event->ID;
        }

        // Filtered Events
        $filtered_ids = null;

        // Filter Criteria
        $locations_str = isset($_REQUEST['mec_locations']) ? trim($_REQUEST['mec_locations'], ', ') : '';
        $categories_str = isset($_REQUEST['mec_categories']) ? trim($_REQUEST['mec_categories'], ', ') : '';
        $organizers_str = isset($_REQUEST['mec_organizers']) ? trim($_REQUEST['mec_organizers'], ', ') : '';

        // Filter Events
        if(trim($locations_str, ', ') || trim($categories_str, ', ') || trim($organizers_str, ', '))
        {
            $locations = [];
            if(trim($locations_str, ', ')) $locations = array_map('trim', explode(',', $locations_str));

            $categories = [];
            if(trim($categories_str, ', ')) $categories = array_map('trim', explode(',', $categories_str));

            $organizers = [];
            if(trim($organizers_str, ', ')) $organizers = array_map('trim', explode(',', $organizers_str));

            $filtered = $this->main->get_filtered_events($locations, $categories, $organizers);

            $filtered_ids = [];
            foreach($filtered as $filtered_post) $filtered_ids[] = $filtered_post->ID;
        }

        if(is_array($filtered_ids))
        {
            // No Events Found
            if(!count($filtered_ids)) $event_ids = [];
            else
            {
                $new_event_ids = [];
                foreach($filtered_ids as $filtered_id)
                {
                    if(in_array($filtered_id, $event_ids)) $new_event_ids[] = $filtered_id;
                }

                $event_ids = $new_event_ids;
            }
        }

        $output = '';
        foreach($event_ids as $event_id) $output .= $this->main->ical_single($event_id, '', '', !$only_upcoming_events);

        // Include in iCal
        $ical_calendar = $this->main->ical_calendar($output);

        // Content Type
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="mec-events-'.date('YmdTHi').'.ics"');

        // Print the Calendar
        echo MEC_kses::full($ical_calendar);
        exit;
    }
}