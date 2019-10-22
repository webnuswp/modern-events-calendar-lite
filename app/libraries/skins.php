<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC skins class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_skins extends MEC_base
{
    /**
     * Default skin
     * @var string
     */
    public $skin = 'list';
    
    /**
     * @var array
     */
    public $atts = array();
    
    /**
     * @var array
     */
    public $args = array();
    
    /**
     * @var int
     */
    public $maximum_dates = 6;
    
	/**
     * Offset for don't load duplicated events in list/grid views on load more action
     * @var int
     */
	public $offset = 0;
	
	/**
     * Offset for next load more action
     * @var int
     */
	public $next_offset = 0;
	
    /**
     * Single Event Display Method
     * @var string
     */
    public $sed_method = '0';

    /**
     * @var MEC_schedule
     */
    public $schedule;

    public $factory;
    public $main;
    public $db;
    public $file;
    public $render;
    public $request;
    public $found;
    public $multiple_days_method;
    public $hide_time_method;
    public $skin_options;
    public $style;
    public $show_only_expired_events;
    public $limit;
    public $paged;
    public $start_date;
    public $end_date;
    public $show_ongoing_events;
    public $maximum_date;
    public $html_class;
    public $sf;
    public $sf_status;
    public $sf_options;
    public $id;
    public $events;
    public $widget;
    public $count;
    public $settings;
    public $layout;
    public $year;
    public $month;
    public $day;
    public $next_previous_button;
    public $active_date;
    public $today;
    public $weeks;
    public $week;
    public $week_of_days;
    public $events_str;
    public $active_day;
    public $load_more_button;
    public $month_divider;
    public $toggle_month_divider;
    public $image_popup;
    public $map_on_top;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // MEC factory library
        $this->factory = $this->getFactory();
        
        // MEC main library
        $this->main = $this->getMain();
        
        // MEC file library
        $this->file = $this->getFile();
        
        // MEC db library
        $this->db = $this->getDB();
        
        // MEC render library
        $this->render = $this->getRender();
        
        // MEC request library
        $this->request = $this->getRequest();

        // MEC schedule library
        $this->schedule = $this->getSchedule();

        // MEC Settings
        $this->settings = $this->main->get_settings();
        
        // Found Events
        $this->found = 0;
        
        // How to show multiple days events
        $this->multiple_days_method = $this->main->get_multiple_days_method();
        
        // Hide event on start or on end
        $this->hide_time_method = $this->main->get_hide_time_method();
    }
    
    /**
     * Registers skin actions into WordPress hooks
     * @author Webnus <info@webnus.biz>
     */
    public function actions()
    {
    }
    
    /**
     * Loads all skins
     * @author Webnus <info@webnus.biz>
     */
    public function load()
    {
        // MEC add filters
        $this->factory->filter('posts_join', array($this, 'join'), 10, 2);
        
        $skins = $this->main->get_skins();
        foreach($skins as $skin=>$skin_name)
        {
            $path = MEC::import('app.skins.'.$skin, true, true);
            $skin_path = apply_filters('mec_skin_path', $skin);

            if($skin_path != $skin and $this->file->exists($skin_path)) $path = $skin_path;
            if(!$this->file->exists($path)) continue;

            include_once $path;

            $skin_class_name = 'MEC_skin_'.$skin;

            // Create Skin Object Class
            $SKO = new $skin_class_name();

            // init the actions
            $SKO->actions();
        }
        
        // Init Single Skin
        include_once MEC::import('app.skins.single', true, true);
        
        // Register the actions
        $SKO = new MEC_skin_single();
        $SKO->actions();
    }
    
    /**
     * Get path of one skin file
     * @author Webnus <info@webnus.biz>
     * @param string $file
     * @return string
     */
    public function get_path($file = 'tpl')
    {
        return MEC::import('app.skins.'.$this->skin.'.'.$file, true, true);
    }
    
    /**
     * Returns path of skin tpl
     * @author Webnus <info@webnus.biz>
     * @return string
     */
    public function get_tpl_path()
    {
        $path = $this->get_path('tpl');
        
        // Apply filters
        $filtered_path = apply_filters('mec_get_skin_tpl_path', $this->skin);
        if($filtered_path != $this->skin and $this->file->exists($filtered_path)) $path = $filtered_path;
        
        return $path;
    }
    
    /**
     * Returns path of skin render file
     * @author Webnus <info@webnus.biz>
     * @return string
     */
    public function get_render_path()
    {
        $path = $this->get_path('render');
        
        // Apply filters
        $filtered_path = apply_filters('mec_get_skin_render_path', $this->skin);
        if($filtered_path != $this->skin and $this->file->exists($filtered_path)) $path = $filtered_path;
        
        return $path;
    }
    
    /**
     * Returns calendar file path of calendar views
     * @author Webnus <info@webnus.biz>
     * @param string $style
     * @return string
     */
    public function get_calendar_path($style = 'calendar')
    {
        $path = $this->get_path($style);
        
        // Apply filters
        $filtered_path = apply_filters('mec_get_skin_calendar_path', $this->skin);
        if($filtered_path != $this->skin and $this->file->exists($filtered_path)) $path = $filtered_path;
        
        return $path;
    }
    
    /**
     * Generates skin output
     * @author Webnus <info@webnus.biz>
     * @return string
     */
    public function output()
    {
        if(!$this->main->getPRO() and in_array($this->skin, array('agenda', 'yearly_view', 'timetable', 'masonry', 'map', 'available_spot')))
        {
            return '';
        }

        // Include needed assets for loading single event details page
        if($this->sed_method) $this->main->load_sed_assets();
        
        ob_start();
        include $this->get_tpl_path();
        return ob_get_clean();
    }
    
    /**
     * Returns keyword query for adding to WP_Query
     * @author Webnus <info@webnus.biz>
     * @return null|string
     */
    public function keyword_query()
    {
        // Add keyword to filters
        if(isset($this->atts['s']) and trim($this->atts['s']) != '') return $this->atts['s'];
        else return NULL;
    }
    
    /**
     * Returns taxonomy query for adding to WP_Query
     * @author Webnus <info@webnus.biz>
     * @return array
     */
    public function tax_query()
    {
        $tax_query = array('relation'=>'AND');
        
        // Add event label to filter
        if(isset($this->atts['label']) and trim($this->atts['label'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_label',
                'field'=>'term_id',
                'terms'=>explode(',', trim($this->atts['label'], ', '))
            );
        }
        
        // Add event category to filter
        if(isset($this->atts['category']) and trim($this->atts['category'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_category',
                'field'=>'term_id',
                'terms'=>explode(',', trim($this->atts['category'], ', '))
            );
        }
        
        // Add event location to filter
        if(isset($this->atts['location']) and trim($this->atts['location'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_location',
                'field'=>'term_id',
                'terms'=>explode(',', trim($this->atts['location'], ', '))
            );
        }
        
        // Add event organizer to filter
        if(isset($this->atts['organizer']) and trim($this->atts['organizer'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_organizer',
                'field'=>'term_id',
                'terms'=>explode(',', trim($this->atts['organizer'], ', '))
            );
        }

        // Add event speaker to filter
        if(isset($this->atts['speaker']) and trim($this->atts['speaker'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_speaker',
                'field'=>'term_id',
                'terms'=>explode(',', trim($this->atts['speaker'], ', '))
            );
        }

        //Event types
        if(isset($this->atts['event_type']) and trim($this->atts['event_type'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_event_type',
                'field'=>'term_id',
                'terms'=>explode(',', trim($this->atts['event_type'], ', '))
            );
        }

        if(isset($this->atts['event_type_2']) and trim($this->atts['event_type_2'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_event_type_2',
                'field'=>'term_id',
                'terms'=>explode(',', trim($this->atts['event_type_2'], ', '))
            );
        }
        
        return $tax_query;
    }
    
    /**
     * Returns meta query for adding to WP_Query
     * @author Webnus <info@webnus.biz>
     * @return array
     */
    public function meta_query()
    {
        $meta_query = array();
        $meta_query['relation'] = 'AND';
        
        return $meta_query;
    }
    
    /**
     * Returns tag query for adding to WP_Query
     * @author Webnus <info@webnus.biz>
     * @return array
     */
    public function tag_query()
    {
        $tag = '';
        
        // Add event tags to filter
        if(isset($this->atts['tag']) and trim($this->atts['tag'], ', ') != '')
        {
            if(is_numeric($this->atts['tag']))
            {
                $term = get_term_by('id', $this->atts['tag'], 'post_tag');
                if($term) $tag = $term->name;
            }
            else $tag = $this->atts['tag'];
        }
        
        return $tag;
    }
    
    /**
     * Returns author query for adding to WP_Query
     * @author Webnus <info@webnus.biz>
     * @return array
     */
    public function author_query()
    {
        $author = '';
        
        // Add event authors to filter
        if(isset($this->atts['author']) and trim($this->atts['author'], ', ') != '')
        {
            $author = $this->atts['author'];
        }
        
        return $author;
    }
    
    /**
     * Set the current day for filtering events in WP_Query
     * @author Webnus <info@webnus.biz>
     * @param String $today
     * @return void
     */
    public function setToday($today = NULL)
    {
        if(is_null($today)) $today = date('Y-m-d');
        
        $this->args['mec-today'] = $today;
        $this->args['mec-now'] = strtotime($this->args['mec-today']);
        
        $this->args['mec-year'] = date('Y', $this->args['mec-now']);
        $this->args['mec-month'] = date('m', $this->args['mec-now']);
        $this->args['mec-day'] = date('d', $this->args['mec-now']);
        
        $this->args['mec-week'] = (int) ((date('d', $this->args['mec-now']) - 1) / 7) + 1;
        $this->args['mec-weekday'] = date('N', $this->args['mec-now']);
    }
    
    /**
     * Join MEC table with WP_Query for filtering the events
     * @author Webnus <info@webnus.biz>
     * @param string $join
     * @param object $wp_query
     * @return string
     */
    public function join($join, $wp_query)
    {
        if(is_string($wp_query->query_vars['post_type']) and $wp_query->query_vars['post_type'] == $this->main->get_main_post_type() and $wp_query->get('mec-init', false))
        {
            $join .= $this->db->_prefix(" LEFT JOIN `#__mec_events` AS mece ON #__posts.ID = mece.post_id LEFT JOIN `#__mec_dates` AS mecd ON #__posts.ID = mecd.post_id");
        }

        return $join;
    }

    /**
     * @param string $start
     * @param string $end
     * @param boolean $exclude
     * @return array
     */
    public function period($start, $end, $exclude = false)
    {
        // Search till the end of End Date!
        if(date('H:i:s', strtotime($end)) == '00:00:00') $end .= ' 23:59:59';

        $seconds_start = strtotime($start);
        $seconds_end = strtotime($end);

        $order = "`tstart` ASC";
        $where_OR = "(`tstart`>='".$seconds_start."' AND `tend`<='".$seconds_end."') OR (`tstart`<='".$seconds_end."' AND `tend`>='".$seconds_end."') OR (`tstart`<='".$seconds_start."' AND `tend`>='".$seconds_start."')";
        // (Start: In, Finish: In) OR (Start: Before or In, Finish: After) OR (Start: Before, Finish: In or After)

        if($this->show_only_expired_events)
        {
            $column = 'tstart';

            if($this->hide_time_method == 'plus1') $seconds_start -= 3600;
            elseif($this->hide_time_method == 'plus2') $seconds_start -= 7200;
            elseif($this->hide_time_method == 'end') $column = 'tend';

            $order = "`tstart` DESC";
            $where_OR = "`".$column."`<'".$seconds_start."'";
        }
        elseif($this->show_ongoing_events)
        {
            $now = current_time('timestamp', 0);
            $where_OR = "(`tstart`<='".$now."' AND `tend`>='".$now."')";
        }

        $where_AND = '1';

        // Exclude Events
        if(isset($this->atts['exclude']) and is_array($this->atts['exclude']) and count($this->atts['exclude'])) $where_AND .= " AND `post_id` NOT IN (".implode(',', $this->atts['exclude']).")";

        // Include Events
        if(isset($this->atts['include']) and is_array($this->atts['include']) and count($this->atts['include'])) $where_AND .= " AND `post_id` IN (".implode(',', $this->atts['include']).")";

        $query = "SELECT * FROM `#__mec_dates` WHERE (".$where_OR.") AND (".$where_AND.") ORDER BY ".$order;
        $mec_dates = $this->db->select($query, 'loadObjectList');

        // Today and Now
        $today = current_time('Y-m-d');
        $now = current_time('timestamp', 0);

        $dates = array();
        foreach($mec_dates as $mec_date)
        {
            $s = strtotime($mec_date->dstart);
            $e = strtotime($mec_date->dend);

            // Hide Events Based on Start Time
            if(!$this->show_only_expired_events and !$this->args['mec-past-events'] and $s <= strtotime($today))
            {
                if($this->hide_time_method == 'start' and $now >= $mec_date->tstart) continue;
                elseif($this->hide_time_method == 'plus1' and $now >= $mec_date->tstart+3600) continue;
                elseif($this->hide_time_method == 'plus2' and $now >= $mec_date->tstart+7200) continue;
            }

            // Hide Events Based on End Time
            if(!$this->show_only_expired_events and !$this->args['mec-past-events'] and $e <= strtotime($today))
            {
                if($this->hide_time_method == 'end' and $now >= $mec_date->tend) continue;
            }

            if($this->multiple_days_method == 'first_day' or ($this->multiple_days_method == 'first_day_listgrid' and in_array($this->skin, array('list', 'grid', 'slider', 'carousel'))))
            {
                // Hide Shown Events on AJAX
                if(defined('DOING_AJAX') and DOING_AJAX and $s != $e and $s < strtotime($start)) continue;

                $d = date('Y-m-d', $s);

                if(!isset($dates[$d])) $dates[$d] = array();
                $dates[$d][] = $mec_date->post_id;
            }
            else
            {
                while($s <= $e)
                {
                    if((!$this->show_only_expired_events and $seconds_start <= $s and $s <= $seconds_end) or ($this->show_only_expired_events and $seconds_start >= $s and $s >= $seconds_end))
                    {
                        $d = date('Y-m-d', $s);
                        if(!isset($dates[$d])) $dates[$d] = array();

                        // Check for exclude events
                        if($exclude)
                        {
                            $current_id = !isset($current_id) ? 0 : $current_id;

                            if(!isset($not_in_day))
                            {
                                $query = "SELECT `post_id`,`not_in_days` FROM `#__mec_events`";
                                $not_in_day =  $this->db->select($query);
                            }

                            if(array_key_exists($mec_date->post_id, $not_in_day) and trim($not_in_day[$mec_date->post_id]->not_in_days))
                            {
                                $days =  $not_in_day[$mec_date->post_id]->not_in_days;
                                $current_id = $mec_date->post_id;
                            }
                            else $days = '';

                            if(strpos($days, $d) === false) $dates[$d][] = $mec_date->post_id; 
                            else $dates[$d][] = NULL;
                        }
                        else
                        {
                            $dates[$d][] = $mec_date->post_id;
                        }
                    }

                    $s += 86400;
                }
            }
        }

        return $dates;
    }
    
    /**
     * Perform the search
     * @author Webnus <info@webnus.biz>
     * @return array of objects \stdClass
     */
    public function search()
    {
        if($this->show_only_expired_events)
        {
            $start = $this->start_date;
            $end = date('Y-m-01', strtotime('-10 Year', strtotime($start)));
        }
        else
        {
            $start = $this->start_date;
            $end = date('Y-m-t', strtotime('+10 Year', strtotime($start)));
        }

        // Date Events
        $dates = $this->period($start, $end, true);

        // Limit
        $this->args['posts_per_page'] = 1000;

        $i = 0;
        $found = 0;
        $events = array();

        foreach($dates as $date=>$IDs)
        {
            // Include Available Events
            $this->args['post__in'] = $IDs;

            // Check Finish Date
            if(isset($this->maximum_date) and strtotime($date) > strtotime($this->maximum_date)) break;

            // Extending the end date
            $this->end_date = $date;

            // Continue to load rest of events in the first date
            if($i === 0) $this->args['offset'] = $this->offset;
            // Load all events in the rest of dates
            else 
            {
                $this->offset = 0;
                $this->args['offset'] = 0;
            }

            // The Query
            $query = new WP_Query($this->args);
            if($query->have_posts())
            {
                // The Loop
                while($query->have_posts())
                {
                    $query->the_post();

                    if(!isset($events[$date])) $events[$date] = array();

                    $rendered = $this->render->data(get_the_ID());

                    $data = new stdClass();
                    $data->ID = get_the_ID();
                    $data->data = $rendered;

                    $data->date = array
                    (
                        'start'=>array('date'=>$date),
                        'end'=>array('date'=>$this->main->get_end_date($date, $rendered))
                    );

                    $events[$date][] = $data;
                    $found++;

                    if($found >= $this->limit)
                    {
                        // Next Offset
                        $this->next_offset = ($query->post_count-($query->current_post+1)) >= 0 ? ($query->current_post+1)+$this->offset : 0;

                        // Restore original Post Data
                        wp_reset_postdata();

                        break 2;
                    }
                }
            }

            // Restore original Post Data
            wp_reset_postdata();

            $i++;
        }

        // Set Offset for Last Page
        if($found < $this->limit)
        {
            // Next Offset
            $this->next_offset = $found;
        }

        // Set found events
        $this->found = $found;

        return $events;
    }
    
    /**
     * Run the search command
     * @author Webnus <info@webnus.biz>
     * @return array of objects
     */
    public function fetch()
    {
        // Events! :)
        return $this->events = $this->search();
    }
    
    /**
     * Draw Monthly Calendar
     * @author Webnus <info@webnus.biz>
     * @param string|int $month
     * @param string|int $year
     * @param array $events
     * @param string $style
     * @return string
     */
    public function draw_monthly_calendar($year, $month, $events = array(), $style = 'calendar')
    {
        $calendar_path = $this->get_calendar_path($style);
        
        // Generate Month
        ob_start();
        include $calendar_path;
        return ob_get_clean();
    }

    /**
     * @param object $event
     * @return string
     */
    public function get_event_classes($event)
    {
        // Labels are not set
        if(!isset($event->data) or (isset($event->data) and !isset($event->data->labels))) return NULL;

        // No Labels
        if(!is_array($event->data->labels) or (is_array($event->data->labels) and !count($event->data->labels))) return NULL;

        $classes = '';
        foreach($event->data->labels as $label)
        {
            if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
            $classes .= ' '.$label['style'];
        }

        return trim($classes);
    }
    
    /**
     * Generates Search Form
     * @author Webnus <info@webnus.biz>
     * @return string
     */
    public function sf_search_form()
    {
        // If no fields specified
        if(!count($this->sf_options)) return '';
        
        $display_style = $fields = $end_div = '';
        $first_row = 'not-started';
        $display_form = array();
        foreach($this->sf_options as $field=>$options)
        {
            $type = isset($options['type']) ? $options['type'] : '';
            $display_form[] = $options['type'];
            $fields_array = array('category', 'location', 'organizer', 'speaker', 'tag', 'label');
            $fields_array = apply_filters( 'mec_filter_fields_search_array', $fields_array );
                if(in_array($field,$fields_array) and $first_row == 'not-started')
                {
                    $first_row = 'started';
                    if ( $this->sf_options['category']['type'] != 'dropdown' and $this->sf_options['location']['type'] != 'dropdown' and $this->sf_options['organizer']['type'] != 'dropdown' and  (isset($this->sf_options['speaker']['type']) && $this->sf_options['speaker']['type'] != 'dropdown') and  (isset($this->sf_options['tag']['type']) && $this->sf_options['tag']['type'] != 'dropdown') and  $this->sf_options['label']['type'] != 'dropdown' )
                    {
                        $display_style = 'style="display: none;"';
                    }
                    $fields .= '<div class="mec-dropdown-wrap" ' . $display_style . '>';
                    $end_div = '</div>';
                }
                if(!in_array($field, $fields_array) and $first_row == 'started')
                {
                    $first_row = 'finished';
                    $fields .= '</div>';
                }
                $fields .= $this->sf_search_field($field, $options);
        }
        $form = '';
        if(trim($fields) && ( in_array('dropdown', $display_form ) || in_array('text_input', $display_form ) ) ) $form .= '<div id="mec_search_form_'.$this->id.'" class="mec-search-form mec-totalcal-box">'.$fields.'</div>';
        
        return $form;
    }
    
    /**
     * Generate a certain search field
     * @author Webnus <info@webnus.biz>
     * @param string $field
     * @param array $options
     * @return string
     */
    public function sf_search_field($field, $options)
    {
        $type = isset($options['type']) ? $options['type'] : '';
        
        // Field is disabled
        if(!trim($type)) return '';

        // Status of Speakers Feature
        $speakers_status = (!isset($this->settings['speakers_status']) or (isset($this->settings['speakers_status']) and !$this->settings['speakers_status'])) ? false : true;
        
        $output = '';
        
        if($field == 'category')
        {
            if($type == 'dropdown')
            {
                $output .= '<div class="mec-dropdown-search">
                    <i class="mec-sl-folder"></i>';
                
                $output .= wp_dropdown_categories(array
                (
                    'echo'=>false,
                    'taxonomy'=>'mec_category',
                    'name'=>' ',
                    'include'=>((isset($this->atts['category']) and trim($this->atts['category'])) ? $this->atts['category'] : ''),
                    'id'=>'mec_sf_category_'.$this->id,
                    'hierarchical'=>true,
                    'show_option_none'=>$this->main->m('taxonomy_category', __('Category', 'modern-events-calendar-lite')),
                    'option_none_value'=>'',
                    'selected'=>(isset($this->atts['category']) ? $this->atts['category'] : ''),
                    'orderby'=>'name',
                    'order'=>'ASC',
                    'show_count'=>0,
                ));
                
                $output .= '</div>';
            }
        }
        elseif($field == 'location')
        {
            if($type == 'dropdown')
            {
                $output .= '<div class="mec-dropdown-search">
                    <i class="mec-sl-location-pin"></i>';
                
                $output .= wp_dropdown_categories(array
                (
                    'echo'=>false,
                    'taxonomy'=>'mec_location',
                    'name'=>' ',
                    'include'=>((isset($this->atts['location']) and trim($this->atts['location'])) ? $this->atts['location'] : ''),
                    'id'=>'mec_sf_location_'.$this->id,
                    'hierarchical'=>true,
                    'show_option_none'=>$this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite')),
                    'option_none_value'=>'',
                    'selected'=>(isset($this->atts['location']) ? $this->atts['location'] : ''),
                    'orderby'=>'name',
                    'order'=>'ASC',
                    'show_count'=>0,
                ));
                
                $output .= '</div>';
            }
        }
        elseif($field == 'organizer')
        {
            if($type == 'dropdown')
            {
                $output .= '<div class="mec-dropdown-search">
                    <i class="mec-sl-user"></i>';
                
                $output .= wp_dropdown_categories(array
                (
                    'echo'=>false,
                    'taxonomy'=>'mec_organizer',
                    'name'=>' ',
                    'include'=>((isset($this->atts['organizer']) and trim($this->atts['organizer'])) ? $this->atts['organizer'] : ''),
                    'id'=>'mec_sf_organizer_'.$this->id,
                    'hierarchical'=>true,
                    'show_option_none'=>$this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite')),
                    'option_none_value'=>'',
                    'selected'=>(isset($this->atts['organizer']) ? $this->atts['organizer'] : ''),
                    'orderby'=>'name',
                    'order'=>'ASC',
                    'show_count'=>0,
                ));
                
                $output .= '</div>';
            }
        }
        elseif($field == 'speaker' and $speakers_status)
        {
            if($type == 'dropdown')
            {
                $output .= '<div class="mec-dropdown-search">
                    <i class="mec-sl-microphone"></i>';
                
                $output .= wp_dropdown_categories(array
                (
                    'echo'=>false,
                    'taxonomy'=>'mec_speaker',
                    'name'=>' ',
                    'include'=>((isset($this->atts['speaker']) and trim($this->atts['speaker'])) ? $this->atts['speaker'] : ''),
                    'id'=>'mec_sf_speaker_'.$this->id,
                    'hierarchical'=>true,
                    'show_option_none'=>$this->main->m('taxonomy_speaker', __('Speaker', 'modern-events-calendar-lite')),
                    'option_none_value'=>'',
                    'selected'=>(isset($this->atts['speaker']) ? $this->atts['speaker'] : ''),
                    'orderby'=>'name',
                    'order'=>'ASC',
                    'show_count'=>0,
                ));
                
                $output .= '</div>';
            }
        }
        elseif($field == 'tag')
        {
            if($type == 'dropdown')
            {
                $output .= '<div class="mec-dropdown-search">
                    <i class="mec-sl-tag"></i>';
                
                $output .= wp_dropdown_categories(array
                (
                    'echo'=>false,
                    'taxonomy'=>'post_tag',
                    'name'=>' ',
                    'include'=>((isset($this->atts['tag']) and trim($this->atts['tag'])) ? $this->atts['tag'] : ''),
                    'id'=>'mec_sf_tag_'.$this->id,
                    'hierarchical'=>true,
                    'show_option_none'=>$this->main->m('taxonomy_tag', __('Tag', 'modern-events-calendar-lite')),
                    'option_none_value'=>'',
                    'selected'=>(isset($this->atts['tag']) ? $this->atts['tag'] : ''),
                    'orderby'=>'name',
                    'order'=>'ASC',
                    'show_count'=>0,
                ));
                
                $output .= '</div>';
            }
        }
        elseif($field == 'label')
        {
            if($type == 'dropdown')
            {
                $output .= '<div class="mec-dropdown-search">
                    <i class="mec-sl-pin"></i>';
                
                $output .= wp_dropdown_categories(array
                (
                    'echo'=>false,
                    'taxonomy'=>'mec_label',
                    'name'=>' ',
                    'include'=>((isset($this->atts['label']) and trim($this->atts['label'])) ? $this->atts['label'] : ''),
                    'id'=>'mec_sf_label_'.$this->id,
                    'hierarchical'=>true,
                    'show_option_none'=>$this->main->m('taxonomy_label', __('Label', 'modern-events-calendar-lite')),
                    'option_none_value'=>'',
                    'selected'=>(isset($this->atts['label']) ? $this->atts['label'] : ''),
                    'orderby'=>'name',
                    'order'=>'ASC',
                    'show_count'=>0,
                ));
                
                $output .= '</div>';
            }
        }
        elseif($field == 'month_filter')
        {
            if($type == 'dropdown')
            {
                $time = isset($this->start_date) ? strtotime($this->start_date) : '';

                $output .= '<div class="mec-date-search">
                    <i class="mec-sl-calendar"></i>
                    <select id="mec_sf_month_'.$this->id.'">';

                $output .= in_array($this->skin, array('list', 'grid')) ? '<option id="mec_sf_skip_date_'.$this->id.'" value="ignore_date">'.__('Ignore month and years', 'modern-events-calendar-lite').'</option>' : '';

                $m = date('m', $time);
                $Y = date('Y', $time);

                for($i = 1; $i <= 12; $i++) $output .= '<option value="'.($i < 10 ? '0'.$i : $i).'" '.($i == $m ? 'selected="selected"' : '').'>'.date_i18n('F', mktime(0, 0, 0, $i, 10)).'</option>';
                $output .= '</select><select id="mec_sf_year_'.$this->id.'">';

                $start_year = $min_start_year = $this->db->select("SELECT MIN(cast(meta_value as unsigned)) AS date FROM `#__postmeta` WHERE `meta_key`='mec_start_date'", 'loadResult');
                $end_year = $max_end_year = $this->db->select("SELECT MAX(cast(meta_value as unsigned)) AS date FROM `#__postmeta` WHERE `meta_key`='mec_end_date'", 'loadResult');

                if(!trim($start_year)) $start_year = date('Y', strtotime('-4 Years', $time));
                if(!trim($end_year) or $end_year < date('Y', strtotime('+4 Years', $time))) $end_year = date('Y', strtotime('+4 Years', $time));

                if(!isset($this->atts['show_past_events']) or (isset($this->atts['show_past_events']) and !$this->atts['show_past_events']))
                {
                    $start_year = $Y;
                    $end_year = date('Y', strtotime('+8 Years', $time));
                }

                if(isset($this->show_only_expired_events) and $this->show_only_expired_events)
                {
                    $start_year = $min_start_year;
                    $end_year = $Y;
                }

                for($i = $start_year; $i <= $end_year; $i++)
                {
                    $output .= '<option value="'.$i.'" '.($i == $Y ? 'selected="selected"' : '').'>'.$i.'</option>';
                }

                $output .= '</select></div>';
            }
        }
        elseif($field == 'text_search')
        {
            if($type == 'text_input')
            {
                $output .= '<div class="mec-text-input-search">
                    <i class="mec-sl-magnifier"></i>
                    <input type="search" value="'.(isset($this->atts['s']) ? $this->atts['s'] : '').'" id="mec_sf_s_'.$this->id.'" />
                </div>';
            }
        }
        
        $output = apply_filters('mec_search_fields_to_box',$output,$field,$type,$this->atts,$this->id);

        return $output;
    }
    
    public function sf_apply($atts, $sf = array(), $apply_sf_date = 1)
    {
        // Return normal atts if sf is empty
        if(!count($sf)) return $atts;

        // Apply Text Search Query
        if(isset($sf['s'])) $atts['s'] = $sf['s'];
        
        // Apply Category Query
        if(isset($sf['category']) and trim($sf['category'])) $atts['category'] = $sf['category'];
        
        // Apply Location Query
        if(isset($sf['location']) and trim($sf['location'])) $atts['location'] = $sf['location'];
        
        // Apply Organizer Query
        if(isset($sf['organizer']) and trim($sf['organizer'])) $atts['organizer'] = $sf['organizer'];

        // Apply speaker Query
        if(isset($sf['speaker']) and trim($sf['speaker'])) $atts['speaker'] = $sf['speaker'];

        // Apply tag Query
        if(isset($sf['tag']) and trim($sf['tag'])) $atts['tag'] = $sf['tag'];
        
        // Apply Label Query
        if(isset($sf['label']) and trim($sf['label'])) $atts['label'] = $sf['label'];

        
        // Apply SF Date or Not
        if($apply_sf_date == 1)
        {
            // Apply Month of Month Filter
            if(isset($sf['month']) and trim($sf['month'])) $this->request->setVar('mec_month', $sf['month']);
            
            // Apply Year of Month Filter
            if(isset($sf['year']) and trim($sf['year'])) $this->request->setVar('mec_year', $sf['year']);
            
            // Apply to Start Date
            if(isset($sf['month']) and trim($sf['month']) and isset($sf['year']) and trim($sf['year']))
            {
                $start_date = $sf['year'].'-'.$sf['month'].'-'.(isset($sf['day']) ? $sf['day'] : '01');
                $this->request->setVar('mec_start_date', $start_date);
                
                $skins = $this->main->get_skins();
                foreach($skins as $skin=>$label)
                {
                    $atts['sk-options'][$skin]['start_date_type'] = 'date';
                    $atts['sk-options'][$skin]['start_date'] = $start_date;
                }
            }
        }
        
        $atts = apply_filters('add_to_search_box_query',$atts, $sf );
        
        return $atts;
    }
}