<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC render class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_render extends MEC_base
{
    public $db;
    public $main;
    public $file;
    public $settings;
    public $post_atts;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Add image size for list and carousel 
        add_image_size('thumblist', '300', '300', true);
        add_image_size('meccarouselthumb', '474', '324', true);
        add_image_size('gridsquare', '391', '260', true);

        // Import MEC skin class
        MEC::import('app.libraries.skins');
        
        // MEC main library
        $this->main = $this->getMain();
        
        // MEC file library
        $this->file = $this->getFile();
        
        // MEC DB library
        $this->db = $this->getDB();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Do the shortcode and return its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function shortcode($atts)
    {
        $calendar_id = isset($atts['id']) ? $atts['id'] : 0;
        $atts = apply_filters('mec_calendar_atts', $this->parse($calendar_id, $atts));
        
        $skin = isset($atts['skin']) ? $atts['skin'] : $this->get_default_layout();
        return $this->skin($skin, $atts);
    }
    
    /**
     * Do the widget and return its output
     * @author Webnus <info@webnus.biz>
     * @param int $calendar_id
     * @param array $atts
     * @return string
     */
    public function widget($calendar_id, $atts = array())
    {
        $atts = apply_filters('mec_calendar_atts', $this->parse($calendar_id, $atts));
        
        $skin = isset($atts['skin']) ? $atts['skin'] : $this->get_default_layout();
        return $this->skin($skin, $atts);
    }

    /**
     * Do the yearly_view skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vyear($atts = array())
    {
        $atts = apply_filters('mec_vyear_atts', $atts);
        $skin = 'yearly_view';

        return $this->skin($skin, $atts);
    }

    /**
     * Do the monthly_view skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vmonth($atts = array())
    {
        $atts = apply_filters('mec_vmonth_atts', $atts);
        $skin = 'monthly_view';
        
        return $this->skin($skin, $atts);
    }
    
    /**
     * Do the full_calendar skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vfull($atts = array())
    {
        $atts = apply_filters('default', $atts);
        $skin = 'full_calendar';
        
        return $this->skin($skin, $atts);
    }

    /**
     * Do the default_full_calendar skin and returns its output (archive page)
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vdefaultfull($atts = array())
    {
        $atts = apply_filters('mec_vdefaultfull_atts', $atts);
        $skin = 'default_full_calendar';
        
        return $this->skin($skin, $atts);
    }

    
    /**
     * Do the weekly_view skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vweek($atts = array())
    {
        $atts = apply_filters('mec_vweek_atts', $atts);
        $skin = 'weekly_view';
        
        return $this->skin($skin, $atts);
    }

    /**
     * Do the timetable skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vtimetable($atts = array())
    {
        $atts = apply_filters('mec_vtimetable_atts', $atts);
        $skin = 'timetable';

        return $this->skin($skin, $atts);
    }

    /**
     * Do the Masonry skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vmasonry($atts = array())
    {
        $atts = apply_filters('mec_vmasonry_atts', $atts);
        $skin = 'masonry';

        return $this->skin($skin, $atts);
    }
    
    /**
     * Do the daily_view skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vday($atts = array())
    {
        $atts = apply_filters('mec_vday_atts', $atts);
        $skin = 'daily_view';
        
        return $this->skin($skin, $atts);
    }
    
    /**
     * Do the map skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vmap($atts = array())
    {
        $atts = apply_filters('mec_vmap_atts', $atts);
        $skin = 'map';
        
        return $this->skin($skin, $atts);
    }
    
    /**
     * Do the list skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vlist($atts = array())
    {
        $atts = apply_filters('mec_vlist_atts', $atts);
        $skin = 'list';
        
        return $this->skin($skin, $atts);
    }

    /**
     * Do the custom skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vcustom($atts)
    {
        if (isset($this->settings['custom_archive']) && !empty($this->settings['custom_archive']))
        echo do_shortcode( $this->settings['custom_archive'] );
    }
    
    /**
     * Do the grid skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vgrid($atts = array())
    {
        $atts = apply_filters('mec_vgrid_atts', $atts);
        $skin = 'grid';
        
        return $this->skin($skin, $atts);
    }

    /**
     * Do the agenda skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vagenda($atts = array())
    {
        $atts = apply_filters('mec_vagenda_atts', $atts);
        $skin = 'agenda';

        return $this->skin($skin, $atts);
    }
    
    /**
     * Do the default archive skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vdefault($atts = array())
    {
        $monthly_skin = (isset($this->settings['monthly_view_archive_skin']) and trim($this->settings['monthly_view_archive_skin']) != '') ? $this->settings['monthly_view_archive_skin'] : 'clean';
        $list_skin = (isset($this->settings['list_archive_skin']) and trim($this->settings['list_archive_skin']) != '') ? $this->settings['list_archive_skin'] : 'standard';
        $grid_skin = (isset($this->settings['grid_archive_skin']) and trim($this->settings['grid_archive_skin']) != '') ? $this->settings['grid_archive_skin'] : 'classic';
        $timetable_skin = (isset($this->settings['timetable_archive_skin']) and trim($this->settings['timetable_archive_skin']) != '') ? $this->settings['timetable_archive_skin'] : 'modern';

        if(!isset($this->settings['default_skin_archive']) or (isset($this->settings['default_skin_archive']) and trim($this->settings['default_skin_archive']) == ''))
        {
            return $this->vmonth(array_merge($atts, array('sk-options'=>array('monthly_view'=>array('style'=>$monthly_skin)))));
        }


        if($this->settings['default_skin_archive'] == 'monthly_view') $content = $this->vmonth(array_merge($atts, array('sk-options'=>array('monthly_view'=>array('style'=>$monthly_skin)))));
        elseif($this->settings['default_skin_archive'] == 'full_calendar') $content = $this->vdefaultfull($atts);
        elseif($this->settings['default_skin_archive'] == 'yearly_view') $content = $this->vyear($atts);
        elseif($this->settings['default_skin_archive'] == 'weekly_view') $content = $this->vweek($atts);
        elseif($this->settings['default_skin_archive'] == 'daily_view') $content = $this->vday($atts);
        elseif($this->settings['default_skin_archive'] == 'timetable') $content = $this->vtimetable(array_merge($atts, array('sk-options'=>array('timetable'=>array('style'=>$timetable_skin)))));
        elseif($this->settings['default_skin_archive'] == 'masonry') $content = $this->vmasonry($atts);
        elseif($this->settings['default_skin_archive'] == 'list') $content = $this->vlist(array_merge($atts, array('sk-options'=>array('list'=>array('style'=>$list_skin)))));
        elseif($this->settings['default_skin_archive'] == 'grid') $content = $this->vgrid(array_merge($atts, array('sk-options'=>array('grid'=>array('style'=>$grid_skin)))));
        elseif($this->settings['default_skin_archive'] == 'agenda') $content = $this->vagenda($atts);
        elseif($this->settings['default_skin_archive'] == 'map') $content = $this->vmap($atts);
        elseif($this->settings['default_skin_archive'] == 'custom') $content = $this->vcustom($atts);
        else $content = apply_filters('mec_default_skin_content', '');
        
        return $content;
    }
    
    /**
     * Do the single skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vsingle($atts)
    {
        // Force to array
        if(!is_array($atts)) $atts = array();
        
        // Get event ID
        $event_id = isset($atts['id']) ? $atts['id'] : 0;
        
        $defaults = array('maximum_dates'=>(isset($this->settings['booking_maximum_dates']) ? $this->settings['booking_maximum_dates'] : 6));
        $atts = apply_filters('mec_vsingle_atts', $this->parse($event_id, wp_parse_args($atts, $defaults)));
        
        $skin = 'single';
        return $this->skin($skin, $atts);
    }
    
    /**
     * Do the category archive skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vcategory($atts = array())
    {
        // Skin
        $skin = (isset($this->settings['default_skin_category']) and trim($this->settings['default_skin_category']) != '') ? $this->settings['default_skin_category'] : 'list';

        // Show Only Expired Events
        if(isset($this->settings['category_events_method']) and $this->settings['category_events_method'] == 2) $atts['show_only_past_events'] = 1;

        $monthly_skin = (isset($this->settings['monthly_view_category_skin']) and trim($this->settings['monthly_view_category_skin']) != '') ? $this->settings['monthly_view_category_skin'] : 'classic';
        $list_skin = (isset($this->settings['list_category_skin']) and trim($this->settings['list_category_skin']) != '') ? $this->settings['list_category_skin'] : 'standard';
        $grid_skin = (isset($this->settings['grid_category_skin']) and trim($this->settings['grid_category_skin']) != '') ? $this->settings['grid_category_skin'] : 'classic';
        $timetable_skin = (isset($this->settings['timetable_category_skin']) and trim($this->settings['timetable_category_skin']) != '') ? $this->settings['timetable_category_skin'] : 'modern';

        if($skin == 'full_calendar') $content = $this->vfull($atts);
        elseif($skin == 'yearly_view') $content = $this->vyear($atts);
        elseif($skin == 'masonry') $content = $this->vmasonry($atts);
        elseif($skin == 'timetable') $content = $this->vtimetable(array_merge($atts, array('sk-options'=>array('timetable'=>array('style'=>$timetable_skin)))));
        elseif($skin == 'monthly_view') $content = $this->vmonth(array_merge($atts, array('sk-options'=>array('monthly_view'=>array('style'=>$monthly_skin)))));
        elseif($skin == 'weekly_view') $content = $this->vweek($atts);
        elseif($skin == 'daily_view') $content = $this->vday($atts);
        elseif($skin == 'list') $content = $this->vlist(array_merge($atts, array('sk-options'=>array('list'=>array('style'=>$list_skin)))));
        elseif($skin == 'grid') $content = $this->vgrid(array_merge($atts, array('sk-options'=>array('grid'=>array('style'=>$grid_skin)))));
        elseif($skin == 'agenda') $content = $this->vagenda($atts);
        elseif($skin == 'map') $content = $this->vmap($atts);
        else $content = apply_filters('mec_default_skin_content', '');
        
        return $content;
    }
    
    /**
     * Merge args
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @param array $atts
     * @return array
     */
    public function parse($post_id, $atts = array())
    {
        if ( $this->post_atts )
        {
            return wp_parse_args($atts, $this->post_atts);
        }

        $post_atts = array();
        if($post_id) $post_atts = $this->main->get_post_meta($post_id);
        
        return wp_parse_args($atts, $post_atts);
    }
    
    /**
     * Run the skin and returns its output
     * @author Webnus <info@webnus.biz>
     * @param string $skin
     * @param array $atts
     * @return string
     */
    public function skin($skin, $atts = array())
    {
        $path = MEC::import('app.skins.'.$skin, true, true);
        $skin_path = apply_filters('mec_skin_path', $skin);
        
        if($skin_path != $skin and $this->file->exists($skin_path)) $path = $skin_path;
        if(!$this->file->exists($path))
        {
            return __('Skin controller does not exist.', 'modern-events-calendar-lite');
        }
        
        include_once $path;
        
        $skin_class_name = 'MEC_skin_'.$skin;
        
        // Create Skin Object Class
        $SKO = new $skin_class_name();
        
        // Initialize the skin
        $SKO->initialize($atts);
        
        // Fetch the events
        $SKO->fetch();
        
        // Return the output
        return $SKO->output();
    }
    
    /**
     * Returns default skin
     * @author Webnus <info@webnus.biz>
     * @return string
     */
    public function get_default_layout()
    {
        return apply_filters('mec_default_layout', 'list');
    }
    
    /**
     * Renders annd Returns all event data
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @param string $content
     * @return \stdClass
     */
    public function data($post_id, $content = NULL)
    {
        $cached = wp_cache_get($post_id, 'mec-events-data');
        if($cached) return $cached;

        $data = new stdClass();
        
        // Post Data
        $data->ID = $post_id;
        $data->title = get_the_title($post_id);
        $data->content = (is_null($content) ? $this->main->get_post_content($post_id) : $content);
        
        // All Post Data
        $post = get_post($post_id);
        $data->post = $post;
        
        // All Meta Data
        $meta = $this->main->get_post_meta($post_id);
        $data->meta = $meta;
        
        // All MEC Data
        $data->mec = $this->db->select("SELECT * FROM `#__mec_events` WHERE `post_id`='$post_id'", "loadObject");
        
        $allday = isset($data->meta['mec_allday']) ? $data->meta['mec_allday'] : 0;
        $hide_time = isset($data->meta['mec_hide_time']) ? $data->meta['mec_hide_time'] : 0;
        $hide_end_time = isset($data->meta['mec_hide_end_time']) ? $data->meta['mec_hide_end_time'] : 0;
        
        if($hide_time)
        {
            $data->time = array('start'=>'', 'end'=>'');
        }
        elseif($allday)
        {
            $data->time = array('start'=>__('All of the day', 'modern-events-calendar-lite'), 'end'=>'');
        }
        else
        {
            $data->time = array(
                'start'=>(isset($meta['mec_start_day_seconds']) ? $this->main->get_time($meta['mec_start_day_seconds']) : ''),
                'end'=>($hide_end_time ? '' : (isset($meta['mec_end_day_seconds']) ? $this->main->get_time($meta['mec_end_day_seconds']) : ''))
            );
        }

        // Hourly Schedules
        $meta_hourly_schedules = isset($meta['mec_hourly_schedules']) ? $meta['mec_hourly_schedules'] : array();
        $first_key = key($meta_hourly_schedules);

        $hourly_schedules = array();
        if(count($meta_hourly_schedules) and !isset($meta_hourly_schedules[$first_key]['schedules']))
        {
            $hourly_schedules[] = array(
                'title' => __('Day 1', 'modern-events-calendar-lite'),
                'schedules'=>$meta_hourly_schedules
            );
        }
        else $hourly_schedules = $meta_hourly_schedules;

        $data->hourly_schedules = $hourly_schedules;

        $data->tickets = isset($meta['mec_tickets']) ? $meta['mec_tickets'] : array();
        $data->color = isset($meta['mec_color']) ? $meta['mec_color'] : '';
        
        $data->permalink = ((isset($meta['mec_read_more']) and filter_var($meta['mec_read_more'], FILTER_VALIDATE_URL)) ? $meta['mec_read_more'] : get_post_permalink($post_id));
        
        // Thumbnails
        $thumbnail = get_the_post_thumbnail($post_id, 'thumbnail', array('data-mec-postid'=>$post_id));
        $thumblist = get_the_post_thumbnail($post_id, 'thumblist' , array('data-mec-postid'=>$post_id));        
        $gridsquare = get_the_post_thumbnail($post_id, 'gridsquare' , array('data-mec-postid'=>$post_id));        
        $meccarouselthumb = get_the_post_thumbnail($post_id, 'meccarouselthumb' , array('data-mec-postid'=>$post_id));
        $medium = get_the_post_thumbnail($post_id, 'medium', array('data-mec-postid'=>$post_id));
        $large = get_the_post_thumbnail($post_id, 'large', array('data-mec-postid'=>$post_id));
        $full = get_the_post_thumbnail($post_id, 'full', array('data-mec-postid'=>$post_id));
        
        if(trim($thumbnail) == '' and trim($medium) != '') $thumbnail = preg_replace("/height=\"[0-9]*\"/", 'height="150"', preg_replace("/width=\"[0-9]*\"/", 'width="150"', $medium));
        elseif(trim($thumbnail) == '' and trim($large) != '') $thumbnail = preg_replace("/height=\"[0-9]*\"/", 'height="150"', preg_replace("/width=\"[0-9]*\"/", 'width="150"', $large));
        
        $data->thumbnails = array(
            'thumbnail'=>$thumbnail,
            'thumblist'=>$thumblist,
            'gridsquare'=>$gridsquare,
            'meccarouselthumb'=>$meccarouselthumb,
            'medium'=>$medium,
            'large'=>$large,
            'full'=>$full
        );
        
        // Featured image URLs
        $data->featured_image = array(
            'thumbnail'=>esc_url(get_the_post_thumbnail_url($post_id, 'thumbnail')),
            'thumblist'=>esc_url(get_the_post_thumbnail_url($post_id, 'thumblist' )),
            'gridsquare'=>esc_url(get_the_post_thumbnail_url($post_id, 'gridsquare' )),
            'meccarouselthumb'=>esc_url(get_the_post_thumbnail_url($post_id, 'meccarouselthumb')),
            'medium'=>esc_url(get_the_post_thumbnail_url($post_id, 'medium')),
            'large'=>esc_url(get_the_post_thumbnail_url($post_id, 'large')),
            'full'=>esc_url(get_the_post_thumbnail_url($post_id, 'full'))
        );

        $taxonomies = array('mec_label', 'mec_organizer', 'mec_location', 'mec_category', 'post_tag');
        if(isset($this->settings['speakers_status']) and $this->settings['speakers_status']) $taxonomies[] = 'mec_speaker';

        $terms = wp_get_post_terms($post_id, $taxonomies, array('fields'=>'all'));

        foreach($terms as $term)
        {
            // First Validation
            if(!isset($term->taxonomy)) continue;

            if($term->taxonomy == 'mec_label') $data->labels[$term->term_id] = array('id'=>$term->term_id, 'name'=>$term->name, 'color'=>get_metadata('term', $term->term_id, 'color', true), 'style'=>get_metadata('term', $term->term_id, 'style', true));
            elseif($term->taxonomy == 'mec_organizer') $data->organizers[$term->term_id] = array('id'=>$term->term_id, 'name'=>$term->name, 'tel'=>get_metadata('term', $term->term_id, 'tel', true), 'email'=>get_metadata('term', $term->term_id, 'email', true), 'url'=>get_metadata('term', $term->term_id, 'url', true), 'thumbnail'=>get_metadata('term', $term->term_id, 'thumbnail', true));
            elseif($term->taxonomy == 'mec_location') $data->locations[$term->term_id] = array('id'=>$term->term_id, 'name'=>$term->name, 'address'=>get_metadata('term', $term->term_id, 'address', true), 'latitude'=>get_metadata('term', $term->term_id, 'latitude', true), 'longitude'=>get_metadata('term', $term->term_id, 'longitude', true), 'thumbnail'=>get_metadata('term', $term->term_id, 'thumbnail', true));
            elseif($term->taxonomy == 'mec_category') $data->categories[$term->term_id] = array('id'=>$term->term_id, 'name'=>$term->name);
            elseif($term->taxonomy == 'post_tag') $data->tags[$term->term_id] = array('id'=>$term->term_id, 'name'=>$term->name);
            elseif($term->taxonomy == 'mec_speaker')
            {
                $data->speakers[$term->term_id] = array(
                    'id'=>$term->term_id,
                    'name'=>$term->name,
                    'job_title'=>get_metadata('term', $term->term_id, 'job_title', true),
                    'tel'=>get_metadata('term', $term->term_id, 'tel', true),
                    'email'=>get_metadata('term', $term->term_id, 'email', true),
                    'facebook'=>get_metadata('term', $term->term_id, 'facebook', true),
                    'twitter'=>get_metadata('term', $term->term_id, 'twitter', true),
                    'gplus'=>get_metadata('term', $term->term_id, 'gplus', true),
                    'thumbnail'=>get_metadata('term', $term->term_id, 'thumbnail', true)
                );
            }
        }
        
        // Add mec event past index to array.
        $end_date = (isset($data->meta['mec_date']['end']) and isset($data->meta['mec_date']['end']['date'])) ? $data->meta['mec_date']['end']['date'] : current_time('Y-m-d H:i:s');

        $e_time = '';
        $e_time .= sprintf("%02d", (isset($data->meta['mec_date']['end']['hour']) ? $data->meta['mec_date']['end']['hour'] : '6')).':';
        $e_time .= sprintf("%02d", (isset($data->meta['mec_date']['end']['minutes']) ? $data->meta['mec_date']['end']['minutes'] : '0'));
        $e_time .= isset($data->meta['mec_date']['end']['ampm']) ? trim($data->meta['mec_date']['end']['ampm']) : 'PM';

        $end_time = date('D M j Y G:i:s', strtotime($end_date.' '.$e_time));

        $d1 = new DateTime(current_time("D M j Y G:i:s"));
        $d2 = new DateTime($end_time);
        
        if($d2 < $d1) $data->meta['event_past'] = true;
        else $data->meta['event_past'] = false;

        // Set to cache
        wp_cache_set($post_id, $data, 'mec-events-data', 43200);
        
        return $data;
    }
    
    /**
     * Renders and Returns event dats
     * @author Webnus <info@webnus.biz>
     * @param int $event_id
     * @param object $event
     * @param int $maximum
     * @param string $today
     * @return object|array
     */
    public function dates($event_id, $event = NULL, $maximum = 6, $today = NULL)
    {
        if(!trim($today)) $today = date('Y-m-d');
        
        // Original Start Date
        $original_start_date = $today;
        $dates = array();
        
        // Get event data if it is NULL
        if(is_null($event)) $event = $this->data($event_id);
        
        $start_date = isset($event->meta['mec_date']['start']) ? $event->meta['mec_date']['start'] : array();
        $end_date = isset($event->meta['mec_date']['end']) ? $event->meta['mec_date']['end'] : array();
        
        // Return empty array if date is not valid
        if(!isset($start_date['date']) or (isset($start_date['date']) and !strtotime($start_date['date']))) return $dates;
        
        // Return empty array if mec data is not exists on mec_events table
        if(!isset($event->mec->end)) return $dates;
        
        $allday = isset($event->meta['mec_allday']) ? $event->meta['mec_allday'] : 0;
        $hide_time = isset($event->meta['mec_hide_time']) ? $event->meta['mec_hide_time'] : 0;
        
        $event_period = $this->main->date_diff($start_date['date'], $end_date['date']);
        $event_period_days = $event_period ? $event_period->days : 0;
        
        $finish_date = array('date'=>$event->mec->end, 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']);
        $exceptional_days = (isset($event->mec->not_in_days) and trim($event->mec->not_in_days)) ? explode(',', trim($event->mec->not_in_days, ', ')) : array();
        
        // Event Passed
        $past = $this->main->is_past($finish_date['date'], $today);
        
        // Event is not passed for custom days
        if($past and isset($event->meta['mec_repeat_type']) and $event->meta['mec_repeat_type'] == 'custom_days') $past = 0;
        
        // Normal event
        if(isset($event->mec->repeat) and $event->mec->repeat == '0')
        {
            $dates[] = array(
                'start'=>$start_date,
                'end'=>$end_date,
                'allday'=>$allday,
                'hide_time'=>$hide_time,
                'past'=>$past
            );
        }
        elseif($past)
        {
            $dates[] = array(
                'start'=>$start_date,
                'end'=>$end_date,
                'allday'=>$allday,
                'hide_time'=>$hide_time,
                'past'=>$past
            );
        }
        elseif(!$past)
        {
            $repeat_type = $event->meta['mec_repeat_type'];
            $repeat_interval = 1;

            if(in_array($repeat_type, array('daily', 'weekly')))
            {
                $repeat_interval = $event->meta['mec_repeat_interval'];
                
                $date_interval = $this->main->date_diff($start_date['date'], $today);
                $passed_days = $date_interval ? $date_interval->days : 0;

                // Check if date interval is negative (It means the event didn't start yet)
                if($date_interval and $date_interval->invert == 1) $remained_days_to_next_repeat = $passed_days;
                else $remained_days_to_next_repeat = $repeat_interval - ($passed_days%$repeat_interval);

                $start_date = date('Y-m-d', strtotime('+'.$remained_days_to_next_repeat.' Days', strtotime($today)));
                if(!$this->main->is_past($finish_date['date'], $start_date) and !in_array($start_date, $exceptional_days)) $dates[] = array(
                    'start'=>array('date'=>$start_date, 'hour'=>$event->meta['mec_date']['start']['hour'], 'minutes'=>$event->meta['mec_date']['start']['minutes'], 'ampm'=>$event->meta['mec_date']['start']['ampm']),
                    'end'=>array('date'=>date('Y-m-d', strtotime('+'.$event_period_days.' Days', strtotime($start_date))), 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']),
                    'allday'=>$allday,
                    'hide_time'=>$hide_time,
                    'past'=>0
                );
                
                for($i=2; $i<=$maximum; $i++)
                {
                    $start_date = date('Y-m-d', strtotime('+'.$repeat_interval.' Days', strtotime($start_date)));
                    
                    // Event finished
                    if($this->main->is_past($finish_date['date'], $start_date)) break;
                    
                    if(!in_array($start_date, $exceptional_days)) $dates[] = array(
                        'start'=>array('date'=>$start_date, 'hour'=>$event->meta['mec_date']['start']['hour'], 'minutes'=>$event->meta['mec_date']['start']['minutes'], 'ampm'=>$event->meta['mec_date']['start']['ampm']),
                        'end'=>array('date'=>date('Y-m-d', strtotime('+'.$event_period_days.' Days', strtotime($start_date))), 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']),
                        'allday'=>$allday,
                        'hide_time'=>$hide_time,
                        'past'=>0
                    );
                }
            }
            elseif(in_array($repeat_type, array('weekday', 'weekend', 'certain_weekdays')))
            {
                $date_interval = $this->main->date_diff($start_date['date'], $today);
                $passed_days = $date_interval ? $date_interval->days : 0;
                
                // Check if date interval is negative (It means the event didn't start yet)
                if($date_interval and $date_interval->invert == 1) $today = date('Y-m-d', strtotime('+'.$passed_days.' Days', strtotime($original_start_date)));
                
                $event_days = explode(',', trim($event->mec->weekdays, ', '));
                
                $today_id = date('N', strtotime($today));
                $found = 0;
                $i = 0;
                
                while($found < $maximum)
                {
                    if($this->main->is_past($finish_date['date'], $today)) break;
                    
                    if(!in_array($today_id, $event_days))
                    {
                        $today = date('Y-m-d', strtotime('+1 Days', strtotime($today)));
                        $today_id = date('N', strtotime($today));
                    
                        $i++;
                        continue;
                    }
                    
                    $start_date = $today;
                    if(!in_array($start_date, $exceptional_days)) $dates[] = array(
                        'start'=>array('date'=>$start_date, 'hour'=>$event->meta['mec_date']['start']['hour'], 'minutes'=>$event->meta['mec_date']['start']['minutes'], 'ampm'=>$event->meta['mec_date']['start']['ampm']),
                        'end'=>array('date'=>date('Y-m-d', strtotime('+'.$event_period_days.' Days', strtotime($start_date))), 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']),
                        'allday'=>$allday,
                        'hide_time'=>$hide_time,
                        'past'=>0
                    );
                    
                    $today = date('Y-m-d', strtotime('+1 Days', strtotime($today)));
                    $today_id = date('N', strtotime($today));
                    
                    $found++;
                    $i++;
                }
            }
            elseif($repeat_type == 'monthly')
            {
                $event_days = explode(',', trim($event->mec->day, ', '));
                
                $event_start_day = $event_days[0];
                $event_end_day = $event_days[(count($event_days)-1)];
                
                $event_period_days = $event_end_day - $event_start_day;
                $found = 0;
                $i = 0;
                
                while($found < $maximum)
                {
                    $t = strtotime('+'.$i.' Months', strtotime($original_start_date));
                    if(!$t) break;

                    $today = date('Y-m-d', $t);
                    if($this->main->is_past($finish_date['date'], $today)) break;
                    
                    $year = date('Y', strtotime($today));
                    $month = date('m', strtotime($today));
                    $day = $event_start_day;
                    $hour = isset($event->meta['mec_date']['end']['hour']) ? sprintf('%02d', $event->meta['mec_date']['end']['hour']) : '06';
                    $minutes = isset($event->meta['mec_date']['end']['minutes']) ? sprintf('%02d', $event->meta['mec_date']['end']['minutes']) : '00';
                    $ampm = isset($event->meta['mec_date']['end']['ampm']) ? strtolower($event->meta['mec_date']['end']['ampm']) : 'pm';
                    
                    // Fix for 31st, 30th, 29th of some months
                    while(!checkdate($month, $day, $year)) $day--;
                    
                    $start_date = $year.'-'.$month.'-'.$day;
                    $end_time = $hour.':'.$minutes.$ampm;

                    if(strtotime($start_date.' '.$end_time) < current_time('timestamp', 0))
                    {
                        $i++;
                        continue;
                    }
                    
                    if(!in_array($start_date, $exceptional_days)) $dates[] = array(
                        'start'=>array('date'=>$start_date, 'hour'=>$event->meta['mec_date']['start']['hour'], 'minutes'=>$event->meta['mec_date']['start']['minutes'], 'ampm'=>$event->meta['mec_date']['start']['ampm']),
                        'end'=>array('date'=>date('Y-m-d', strtotime('+'.$event_period_days.' Days', strtotime($start_date))), 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']),
                        'allday'=>$allday,
                        'hide_time'=>$hide_time,
                        'past'=>0
                    );
                    
                    $found++;
                    $i++;
                }
            }
            elseif($repeat_type == 'yearly')
            {
                $event_days = explode(',', trim($event->mec->day, ', '));
                $event_months = explode(',', trim($event->mec->month, ', '));
                
                $event_start_day = $event_days[0];
                $event_end_day = $event_days[(count($event_days)-1)];
                
                $event_period_days = $event_end_day - $event_start_day;
                $found = 0;
                $i = 0;

                while($found < $maximum)
                {
                    $t = strtotime('+'.$i.' Months', strtotime($original_start_date));
                    if(!$t) break;

                    $today = date('Y-m-d', $t);
                    if($this->main->is_past($finish_date['date'], $today)) break;

                    $year = date('Y', strtotime($today));
                    $month = date('m', strtotime($today));

                    if(!in_array($month, $event_months))
                    {
                        $i++;
                        continue;
                    }
                    
                    $day = $event_start_day;
                    
                    // Fix for 31st, 30th, 29th of some months
                    while(!checkdate($month, $day, $year)) $day--;
                    
                    $event_date = $year.'-'.$month.'-'.$day;
                    if(strtotime($event_date) < strtotime($original_start_date))
                    {
                        $i++;
                        continue;
                    }
                    
                    $start_date = $event_date;
                    if(!in_array($start_date, $exceptional_days)) $dates[] = array(
                        'start'=>array('date'=>$start_date, 'hour'=>$event->meta['mec_date']['start']['hour'], 'minutes'=>$event->meta['mec_date']['start']['minutes'], 'ampm'=>$event->meta['mec_date']['start']['ampm']),
                        'end'=>array('date'=>date('Y-m-d', strtotime('+'.$event_period_days.' Days', strtotime($start_date))), 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']),
                        'allday'=>$allday,
                        'hide_time'=>$hide_time,
                        'past'=>0
                    );
                    
                    $found++;
                    $i++;
                }
            }
            elseif($repeat_type == 'custom_days')
            {
                $custom_days = explode(',', $event->mec->days);
                
                $found = 0;
                if(strtotime($event->mec->start) >= strtotime($today) and !in_array($event->mec->start, $exceptional_days))
                {
                    $dates[] = array(
                        'start'=>array('date'=>$event->mec->start, 'hour'=>$event->meta['mec_date']['start']['hour'], 'minutes'=>$event->meta['mec_date']['start']['minutes'], 'ampm'=>$event->meta['mec_date']['start']['ampm']),
                        'end'=>array('date'=>$event->mec->end, 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']),
                        'allday'=>$allday,
                        'hide_time'=>$hide_time,
                        'past'=>0
                    );
                    
                    $found++;
                }
                
                foreach($custom_days as $custom_day)
                {
                    // Found maximum dates
                    if($found >= $maximum) break;

                    $cday = explode(':', $custom_day);

                    // Date is past
                    if(strtotime($cday[0]) < strtotime($today)) continue;
                    
                    if(!in_array($cday[0], $exceptional_days)) $dates[] = array(
                        'start'=>array('date'=>$cday[0], 'hour'=>$event->meta['mec_date']['start']['hour'], 'minutes'=>$event->meta['mec_date']['start']['minutes'], 'ampm'=>$event->meta['mec_date']['start']['ampm']),
                        'end'=>array('date'=>$cday[1], 'hour'=>$event->meta['mec_date']['end']['hour'], 'minutes'=>$event->meta['mec_date']['end']['minutes'], 'ampm'=>$event->meta['mec_date']['end']['ampm']),
                        'allday'=>$allday,
                        'hide_time'=>$hide_time,
                        'past'=>0
                    );
                    
                    $found++;
                }
                
                // No future date found so the event is passed
                if(!count($dates))
                {
                    $dates[] = array(
                        'start'=>$start_date,
                        'end'=>$finish_date,
                        'allday'=>$allday,
                        'hide_time'=>$hide_time,
                        'past'=>$past
                    );
                }
            }
            elseif($repeat_type == 'advanced')
            { 
                // Get user specifed days of month for repeat
                $advanced_days = get_post_meta($event_id, 'mec_advanced_days', true);

                // Generate dates for event
                $event_info = array('start' => $start_date, 'end' => $end_date, 'allday' => $allday, 'hide_time' => $hide_time, 'finish_date' => $finish_date['date'], 'exceptional_days' => $exceptional_days, 'mec_repeat_end' => $event->meta['mec_repeat']['end'], 'occurrences' => $event->meta['mec_repeat']['end_at_occurrences']);
                $dates = $this->generate_advanced_days($advanced_days, $event_info, $maximum, $today);
            }
        }
        
        return $dates;
    }
    
    /**
     *  Render advanced dates
     * @author Webnus <info@webnus.biz>
     * @param array $advanced_days
     * @return array
     */
    function generate_advanced_days($advanced_days = array(), $event_info = array(), $maximum = 6, $today = NULL, $mode = 'render')
    {
        if(!count($advanced_days)) return array();
        if(!trim($today)) $today = date( 'Y-m-d', current_time( 'timestamp', 0 ));

        $levels = array('first', 'second', 'third', 'fourth', 'last');
        $year = date('Y');
        $dates = array();
        
        // Set last month for include current month results
        $month = date('m', strtotime('first day of last month'));
        $current_day = date("d");
        $last_day =substr(end($advanced_days), 0, 3);
        
        $maximum = intval($maximum);
        $i = 0;

        // Event info
        $exceptional_days =  array_key_exists('exceptional_days', $event_info) ? $event_info['exceptional_days'] : array();
        $start_date = $event_info['start'];
        $end_date = $event_info['end'];
        $allday = array_key_exists('allday', $event_info) ? $event_info['allday'] : 0;
        $hide_time = array_key_exists('hide_time', $event_info) ? $event_info['hide_time'] : 0;
        $finish_date = array_key_exists('finish_date', $event_info) ? $event_info['finish_date'] : '0000-00-00';
        $event_period = $this->main->date_diff($start_date['date'], $end_date['date']);
        $event_period_days = $event_period ? $event_period->days : 0;
        $mec_repeat_end = array_key_exists('mec_repeat_end', $event_info) ? $event_info['mec_repeat_end'] : '';
        $occurrences = array_key_exists('occurrences', $event_info) ? $event_info['occurrences'] : 0;

        // Include default start date to resualts
        if(!$this->main->is_past($start_date['date'], $today) and !in_array($start_date['date'], $exceptional_days))
        {
            $dates[] = array(
                'start' => $start_date,
                'end' => $end_date,
                'allday' => $allday,
                'hide_time' => $hide_time,
                'past' => 0
            );

            if($mode == 'render') $i++;
        }

        while($i < $maximum)
        {
            foreach($advanced_days as $day)
            {
                if($i >= $maximum) break;

                // Explode $day value for example (Sun.1) to Sun and 1
                $d = explode('.', $day);

                // Set indexes for {$levels} index if number day is Last(Sun.l) then indexes set 4th {$levels} index
                $index = intval($d[1]) ? (intval($d[1]) - 1) : 4;

                // Generate date
                $date = "{$year}-{$month}-{$current_day}";

                // Generate start date for example "first Sun of next month"
                $start = date('Y-m-d', strtotime("{$levels[$index]} {$d[0]} of next month", strtotime(date($date))));
                $end = date('Y-m-d', strtotime("+{$event_period_days} Days", strtotime($start)));
                
                // When ends repeat date set
                if($mode == 'render' and $this->main->is_past($finish_date, $start)) continue;

                // Jump to next level if start date is past
                if($this->main->is_past($start, $today) or in_array($start, $exceptional_days)) continue;

                // Add dates
                $dates[] = array(
                    'start' => array('date'=>$start, 'hour'=>$start_date['hour'], 'minutes'=>$start_date['minutes'], 'ampm'=>$start_date['ampm']),
                    'end' => array('date'=>$end, 'hour'=>$end_date['hour'], 'minutes'=>$end_date['minutes'], 'ampm'=>$end_date['ampm']),
                    'allday' => $allday,
                    'hide_time' => $hide_time,
                    'past' => 0
                );

                $i++;
            }

            // When ends repeat date set
            if($mode == 'render' and $this->main->is_past($finish_date, $start)) break;
            
            // Change month and years for next resualts
            if(intval($month) == 12)
            {
                $year = intval($year)+1;
                $month = '00';
            }

            $month = sprintf("%02d", intval($month)+1);
        }
        
        if($mode == 'render' and trim($mec_repeat_end) == 'occurrences' and count($dates) > $occurrences) 
        {
            $max = strtotime(reset($dates)['start']['date']);
            $pos = 0;
            
            for($i = 1; $i < count($dates); $i++)
            {
                if(strtotime($dates[$i]['start']['date']) > $max)
                {
                    $max = strtotime($dates[$i]['start']['date']);
                    $pos = $i;
                }
            }

            unset($dates[$pos]);
        }
        
        return $dates;
    }

    /**
     * Render markers
     * @author Webnus <info@webnus.biz>
     * @param array $events
     * @return array
     */
    public function markers($events)
    {
        $markers = array();

        $date_format = (isset($this->settings['google_maps_date_format1']) and trim($this->settings['google_maps_date_format1'])) ? $this->settings['google_maps_date_format1'] : 'M d Y';
        
        foreach($events as $event)
        {
            $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
            
            $latitude = isset($location['latitude']) ? $location['latitude'] : '';
            $longitude = isset($location['longitude']) ? $location['longitude'] : '';
            
            // No latitude/Longitude
            if(trim($latitude) == '' or trim($longitude) == '') continue;
            
            $key = $latitude.','.$longitude;
            if(!isset($markers[$key]))
            {
                $markers[$key] = array(
                    'latitude'=>$latitude,
                    'longitude'=>$longitude,
                    'name'=>((isset($location['name']) and trim($location['name'])) ? $location['name'] : ''),
                    'address'=>((isset($location['address']) and trim($location['address'])) ? $location['address'] : ''),
                    'event_ids'=>array($event->data->ID),
                    'lightbox'=>$this->main->get_marker_lightbox($event, $date_format),
                );
            }
            else
            {
                $markers[$key]['event_ids'][] = $event->data->ID;
                $markers[$key]['lightbox'] .= $this->main->get_marker_lightbox($event, $date_format);
            }
        }
        
        $points = array();
        foreach($markers as $key=>$marker)
        {
            $points[$key] = $marker;
            
            $points[$key]['lightbox'] = '<div><div class="mec-event-detail mec-map-view-event-detail"><i class="mec-sl-map-marker"></i> '.(trim($marker['address']) ? $marker['address'] : $marker['name']).'</div><div>'.$marker['lightbox'].'</div></div>';
            $points[$key]['count'] = count($marker['event_ids']);
            $points[$key]['infowindow'] = $this->main->get_marker_infowindow($marker);
        }
        
        return apply_filters('mec_render_markers', $points);
    }
}