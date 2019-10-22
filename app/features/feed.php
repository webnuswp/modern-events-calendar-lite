<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC feed class.
 * @author Webnus <info@webnus.biz>
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

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
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
    }
    
    /**
     * Initialize feed feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        remove_all_actions('do_feed_rss2');
        $this->factory->action('do_feed_rss2', array($this, 'rss2'), 10, 1);
    }
    
    /**
     * Do the feed
     * @author Webnus <info@webnus.biz>
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
     * @author Webnus <info@webnus.biz>
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
}