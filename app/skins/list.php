<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC list class.
 * @author Webnus <info@webnus.net>
 */
class MEC_skin_list extends MEC_skins
{
    /**
     * @var string
     */
    public $skin = 'list';

    public $date_format_classic_1;
    public $date_format_minimal_1;
    public $date_format_minimal_2;
    public $date_format_minimal_3;
    public $date_format_modern_1;
    public $date_format_modern_2;
    public $date_format_modern_3;
    public $date_format_standard_1;
    public $date_format_acc_1;
    public $date_format_acc_2;
    public $display_price;
    public $display_available_tickets;
    public $date_format_fluent_1;
    public $reason_for_cancellation;
    public $display_label;
    public $loadMoreRunning;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        parent::__construct();

        // MEC Render
        $this->render = $this->getRender();
    }

    /**
     * Registers skin actions into WordPress
     * @author Webnus <info@webnus.net>
     */
    public function actions()
    {
        $this->factory->action('wp_ajax_mec_list_load_more', array($this, 'load_more'));
        $this->factory->action('wp_ajax_nopriv_mec_list_load_more', array($this, 'load_more'));

        // Fluent View
        $this->factory->action('wp_ajax_mec_list_load_month', array($this, 'load_month'));
        $this->factory->action('wp_ajax_nopriv_mec_list_load_month', array($this, 'load_month'));
    }

    /**
     * Initialize the skin
     * @author Webnus <info@webnus.net>
     * @param array $atts
     */
    public function initialize($atts)
    {
        $this->atts = $atts;

        // Skin Options
        $this->skin_options = (isset($this->atts['sk-options']) and isset($this->atts['sk-options'][$this->skin])) ? $this->atts['sk-options'][$this->skin] : array();

        // Date Formats
        $this->date_format_classic_1 = (isset($this->skin_options['classic_date_format1']) and trim($this->skin_options['classic_date_format1'])) ? $this->skin_options['classic_date_format1'] : 'M d Y';

        $this->date_format_minimal_1 = (isset($this->skin_options['minimal_date_format1']) and trim($this->skin_options['minimal_date_format1'])) ? $this->skin_options['minimal_date_format1'] : 'd';
        $this->date_format_minimal_2 = (isset($this->skin_options['minimal_date_format2']) and trim($this->skin_options['minimal_date_format2'])) ? $this->skin_options['minimal_date_format2'] : 'M';
        $this->date_format_minimal_3 = (isset($this->skin_options['minimal_date_format3']) and trim($this->skin_options['minimal_date_format3'])) ? $this->skin_options['minimal_date_format3'] : 'l';

        $this->date_format_modern_1 = (isset($this->skin_options['modern_date_format1']) and trim($this->skin_options['modern_date_format1'])) ? $this->skin_options['modern_date_format1'] : 'd';
        $this->date_format_modern_2 = (isset($this->skin_options['modern_date_format2']) and trim($this->skin_options['modern_date_format2'])) ? $this->skin_options['modern_date_format2'] : 'F';
        $this->date_format_modern_3 = (isset($this->skin_options['modern_date_format3']) and trim($this->skin_options['modern_date_format3'])) ? $this->skin_options['modern_date_format3'] : 'l';

        $this->date_format_standard_1 = (isset($this->skin_options['standard_date_format1']) and trim($this->skin_options['standard_date_format1'])) ? $this->skin_options['standard_date_format1'] : 'd M';

        $this->date_format_acc_1 = (isset($this->skin_options['accordion_date_format1']) and trim($this->skin_options['accordion_date_format1'])) ? $this->skin_options['accordion_date_format1'] : 'd';
        $this->date_format_acc_2 = (isset($this->skin_options['accordion_date_format2']) and trim($this->skin_options['accordion_date_format2'])) ? $this->skin_options['accordion_date_format2'] : 'F';

        // Fluent view - Date Formats
        $this->date_format_fluent_1 = (isset($this->skin_options['fluent_date_format1']) and trim($this->skin_options['fluent_date_format1'])) ? $this->skin_options['fluent_date_format1'] : 'F d, Y';

        // Search Form Options
        $this->sf_options = (isset($this->atts['sf-options']) and isset($this->atts['sf-options'][$this->skin])) ? $this->atts['sf-options'][$this->skin] : array();

        // Search Form Status
        $this->sf_status = isset($this->atts['sf_status']) ? $this->atts['sf_status'] : true;
        $this->sf_display_label = isset($this->atts['sf_display_label']) ? $this->atts['sf_display_label'] : false;
        $this->sf_reset_button = isset($this->atts['sf_reset_button']) ? $this->atts['sf_reset_button'] : false;
        $this->sf_refine = isset($this->atts['sf_refine']) ? $this->atts['sf_refine'] : false;

        // Generate an ID for the sking
        $this->id = isset($this->atts['id']) ? $this->atts['id'] : mt_rand(100, 999);

        // Set the ID
        if(!isset($this->atts['id'])) $this->atts['id'] = $this->id;

        // Show "Load More" button or not
        $this->load_more_button = isset($this->skin_options['load_more_button']) ? $this->skin_options['load_more_button'] : true;

        // Show Month Divider or not
        $this->month_divider = isset($this->skin_options['month_divider']) ? $this->skin_options['month_divider'] : true;

        // Toggle Month Divider or not
        $this->toggle_month_divider = isset($this->skin_options['toggle_month_divider']) ? $this->skin_options['toggle_month_divider'] : 0;

        // Map on top
        $this->map_on_top = isset($this->skin_options['map_on_top']) ? $this->skin_options['map_on_top'] : false;

        // Map geolocation
        $this->geolocation = ((isset($this->skin_options['map_on_top']) and (isset($this->skin_options['set_geolocation']))) and ($this->skin_options['map_on_top'] == '1' and $this->skin_options['set_geolocation'] == '1')) ? true : false;

        // Reason for Cancellation
        $this->reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

        // Display Label
        $this->display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;

        // Event Times
        $this->include_events_times = isset($this->skin_options['include_events_times']) ? $this->skin_options['include_events_times'] : false;
        $this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;

        // Geolocation Focus
        $this->geolocation_focus = isset($this->skin_options['set_geolocation_focus']) ? $this->skin_options['set_geolocation_focus'] : 0;

        // The style
        $this->style = isset($this->skin_options['style']) ? $this->skin_options['style'] : 'modern';
        if($this->style == 'fluent' and !is_plugin_active('mec-fluent-layouts/mec-fluent-layouts.php')) $this->style = 'modern';

        // Override the style if the style forced by us in a widget etc
        if(isset($this->atts['style']) and trim($this->atts['style']) != '') $this->style = $this->atts['style'];

        // HTML class
        $this->html_class = '';
        if(isset($this->atts['html-class']) and trim($this->atts['html-class']) != '') $this->html_class = $this->atts['html-class'];

        // Booking Button
        $this->booking_button = isset($this->skin_options['booking_button']) ? (int) $this->skin_options['booking_button'] : 0;

        // SED Method
        $this->sed_method = isset($this->skin_options['sed_method']) ? $this->skin_options['sed_method'] : '0';
        if($this->style === 'accordion') $this->sed_method = 0;

        // Order Method
        $this->order_method = (isset($this->skin_options['order_method']) and trim($this->skin_options['order_method'])) ? $this->skin_options['order_method'] : 'ASC';

        // Image popup
        $this->image_popup = isset($this->skin_options['image_popup']) ? $this->skin_options['image_popup'] : 0;

        // From Widget
        $this->widget = (isset($this->atts['widget']) and trim($this->atts['widget'])) ? true : false;

        // From Full Calendar
        $this->from_full_calendar = (isset($this->skin_options['from_fc']) and trim($this->skin_options['from_fc'])) ? true : false;

        // Display Price
        $this->display_price = (isset($this->skin_options['display_price']) and trim($this->skin_options['display_price'])) ? true : false;

        // Fluent view - Available Tickets
        $this->display_available_tickets = (isset($this->skin_options['display_available_tickets']) and trim($this->skin_options['display_available_tickets'])) ? $this->skin_options['display_available_tickets'] : '';

        // Init MEC
        $this->args['mec-init'] = true;
        $this->args['mec-skin'] = $this->skin;

        // Post Type
        $this->args['post_type'] = $this->main->get_main_post_type();

        // Post Status
        $this->args['post_status'] = 'publish';

        // Keyword Query
        $this->args['s'] = $this->keyword_query();

        // Taxonomy
        $this->args['tax_query'] = $this->tax_query();

        // Meta
        $this->args['meta_query'] = $this->meta_query();

        // Tag
        if(apply_filters('mec_taxonomy_tag', '') === 'post_tag') $this->args['tag'] = $this->tag_query();

        // Author
        $this->args['author'] = $this->author_query();

        // Pagination Options
        $this->paged = get_query_var('paged', 1);
        $this->limit = (isset($this->skin_options['limit']) and trim($this->skin_options['limit'])) ? $this->skin_options['limit'] : 12;

        $this->args['posts_per_page'] = $this->limit;
        $this->args['paged'] = $this->paged;

        // Sort Options
        $this->args['orderby'] = 'meta_value_num';
        $this->args['order'] = (in_array($this->order_method, array('ASC', 'DESC')) ? $this->order_method : 'ASC');
        $this->args['meta_key'] = 'mec_start_day_seconds';

        // Exclude Posts
        if(isset($this->atts['exclude']) and is_array($this->atts['exclude']) and count($this->atts['exclude'])) $this->args['post__not_in'] = $this->atts['exclude'];

        // Include Posts
        if(isset($this->atts['include']) and is_array($this->atts['include']) and count($this->atts['include'])) $this->args['post__in'] = $this->atts['include'];

        // Show Only Expired Events
        $this->show_only_expired_events = (isset($this->atts['show_only_past_events']) and trim($this->atts['show_only_past_events'])) ? '1' : '0';

        // Maximum Date Range.
        $this->maximum_date_range = $this->get_end_date();

        // Show Past Events
        if($this->show_only_expired_events)
        {
            $this->atts['show_past_events'] = '1';
            $this->args['order'] = 'DESC';
        }

        // Show Past Events
        $this->args['mec-past-events'] = isset($this->atts['show_past_events']) ? $this->atts['show_past_events'] : '0';

        if(strpos($this->style, 'fluent') === false)
        {
            // Start Date
            $this->start_date = $this->get_start_date();
        }
        else
        {
            // Start Date
            list($this->year, $this->month, $this->day) = $this->get_start_date();

            $this->start_date = date('Y-m-d', strtotime($this->year.'-'.$this->month.'-'.$this->day));
            $this->active_day = $this->year . '-' . $this->month.'-' . $this->day;
        }

        // We will extend the end date in the loop
        $this->end_date = $this->start_date;

        // Show Ongoing Events
        $this->show_ongoing_events = (isset($this->atts['show_only_ongoing_events']) and trim($this->atts['show_only_ongoing_events'])) ? '1' : '0';
        if($this->show_ongoing_events)
        {
            $this->args['mec-show-ongoing-events'] = $this->show_ongoing_events;
            if(strpos($this->style, 'fluent') === false)
            {
                $this->maximum_date = $this->start_date;
            }
        }

        // Include Ongoing Events
        $this->include_ongoing_events = (isset($this->atts['show_ongoing_events']) and trim($this->atts['show_ongoing_events'])) ? '1' : '0';
        if($this->include_ongoing_events) $this->args['mec-include-ongoing-events'] = $this->include_ongoing_events;

        // Set start time
        if(isset($this->atts['seconds']))
        {
            $this->args['mec-seconds'] = $this->atts['seconds'];
            $this->args['mec-seconds-date'] = isset($this->atts['seconds_date']) ? $this->atts['seconds_date'] : $this->start_date;
        }

        // Apply Maximum Date
        if(strpos($this->style, 'fluent') === false)
        {
            $apply_sf_date = isset($_REQUEST['apply_sf_date']) ? sanitize_text_field($_REQUEST['apply_sf_date']) : 0;
            if($apply_sf_date == 1 and isset($this->sf) and isset($this->sf['month']) and trim($this->sf['month'])) $this->maximum_date = date('Y-m-t', strtotime($this->start_date));
        }

        // Found Events
        $this->found = 0;

        // Detect Load More Running
        $this->loadMoreRunning = false;
    }

    /**
     * Search and returns the filtered events
     * @author Webnus <info@webnus.net>
     * @return array of objects
     */
    public function search()
    {
        if(strpos($this->style, 'fluent') === false)
        {
            return parent::search();
        }
        else
        {
            if($this->show_only_expired_events)
            {
                if($this->loadMoreRunning)
                {
                    $start = $this->start_date;

                    if($this->month == date('m', current_time('timestamp', 0))) $end = date('Y-m-d', current_time('timestamp', 0));
                    else $end = date('Y-m-t', strtotime($this->start_date));
                }
                else
                {
                    $now = current_time('timestamp', 0);
                    $startDateTime = strtotime(date($this->year.$this->month.'t')) + (int) $this->main->get_gmt_offset_seconds();
                    $now = $startDateTime < $now ? $startDateTime : $now;

                    $start = date('Y-m-d H:i:s', $now);
                    $end = date('Y-m-d', strtotime($this->year.$this->month.'01'));
                }
            }
            else
            {
                $start = $this->start_date;
                $end = date('Y-m-t', strtotime($this->start_date));

                // Set a certain maximum date from shortcode page.
                if(trim($this->maximum_date) == '' and (isset($this->maximum_date_range) and trim($this->maximum_date_range))) $this->maximum_date = $this->maximum_date_range;
            }

            // Date Events
            if($this->show_only_expired_events && $this->loadMoreRunning) $this->show_only_expired_events = '0';

            $dates = $this->period($start, $end, true);
            ksort($dates);

            if($this->show_only_expired_events && $this->loadMoreRunning) $this->show_only_expired_events = '1';

            // Limit
            $this->args['posts_per_page'] = $this->limit;

            $i = 0;
            $found = 0;
            $events = array();
            $qs = array();

            foreach($dates as $date=>$IDs)
            {
                // No Event
                if(!is_array($IDs) or (is_array($IDs) and !count($IDs))) continue;

                // Check Finish Date
                if(isset($this->maximum_date) and strtotime($date) > strtotime($this->maximum_date)) break;

                // Include Available Events
                $this->args['post__in'] = array_unique($IDs);

                // Count of events per day
                $IDs_count = array_count_values($IDs);

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
                $this->args = apply_filters('mec_skin_query_args', $this->args, $this);

                // Query Key
                $q_key = base64_encode(json_encode($this->args));

                // Get From Cache
                if(isset($qs[$q_key])) $query = $qs[$q_key];
                // Search & Cache
                else
                {
                    $query = new WP_Query($this->args);
                    $qs[$q_key] = $query;
                }

                if($query->have_posts())
                {
                    if(!isset($events[$date])) $events[$date] = array();

                    // Day Events
                    $d = array();

                    // The Loop
                    while($query->have_posts())
                    {
                        $query->the_post();
                        $ID = get_the_ID();

                        $ID_count = isset($IDs_count[$ID]) ? $IDs_count[$ID] : 1;
                        for($i = 1; $i <= $ID_count; $i++)
                        {
                            $rendered = $this->render->data($ID);

                            $data = new stdClass();
                            $data->ID = $ID;
                            $data->data = $rendered;

                            $data->date = array
                            (
                                'start' => array('date' => $date),
                                'end' => array('date' => $this->main->get_end_date($date, $rendered))
                            );

                            $d[] = $this->render->after_render($data, $this, $i);
                            $found++;
                        }

                        if($found >= $this->limit)
                        {
                            // Next Offset
                            $this->next_offset = ($query->post_count-($query->current_post+1)) >= 0 ? ($query->current_post+1)+$this->offset : 0;

                            usort($d, array($this, 'sort_day_events'));
                            $events[$date] = $d;

                            // Restore original Post Data
                            wp_reset_postdata();

                            break 2;
                        }
                    }

                    usort($d, array($this, 'sort_day_events'));
                    $events[$date] = $d;
                }

                // Restore original Post Data
                wp_reset_postdata();
                $i++;
            }

            // Initialize Occurrences' Data
            MEC_feature_occurrences::fetch($events);

            // Set Offset for Last Page
            if($found < $this->limit)
            {
                // Next Offset
                $this->next_offset = $found + ((isset($date) and $this->start_date === $date) ? $this->offset : 0);
            }

            // Set found events
            $this->found = $found;

            return $events;
        }
    }

    /**
     * Returns start day of skin for filtering events
     * @author Webnus <info@webnus.net>
     * @return string|array
     */
    public function get_start_date()
    {
        // Default date
        $date = current_time('Y-m-d');

        if(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'today') $date = current_time('Y-m-d');
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'tomorrow') $date = date('Y-m-d', strtotime('Tomorrow'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'yesterday') $date = date('Y-m-d', strtotime('Yesterday'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'start_last_month') $date = date('Y-m-d', strtotime('first day of last month'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'start_current_month') $date = date('Y-m-d', strtotime('first day of this month'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'start_next_month') $date = date('Y-m-d', strtotime('first day of next month'));
        elseif(isset($this->skin_options['start_date_type']) and $this->skin_options['start_date_type'] == 'date') $date = date('Y-m-d', strtotime($this->skin_options['start_date']));

        // Hide past events
        if(isset($this->atts['show_past_events']) and !trim($this->atts['show_past_events']))
        {
            $today = current_time('Y-m-d');
            if(strtotime($date) < strtotime($today)) $date = $today;
        }

        // Show only expired events
        if(isset($this->show_only_expired_events) and $this->show_only_expired_events)
        {
            $now = date('Y-m-d H:i:s', current_time('timestamp', 0));
            if(strtotime($date) > strtotime($now)) $date = $now;
        }

        if(strpos($this->style, 'fluent') === false) return $date;
        else
        {
            $time = strtotime($date);
            return array(date('Y', $time), date('m', $time), date('d', $time));
        }
    }

    /**
     * Load more events for AJAX requert
     * @author Webnus <info@webnus.net>
     * @return void
     */
    public function load_more()
    {
        $this->sf = (isset($_REQUEST['sf']) and is_array($_REQUEST['sf'])) ? $this->main->sanitize_deep_array($_REQUEST['sf']) : array();
        $apply_sf_date = isset($_REQUEST['apply_sf_date']) ? sanitize_text_field($_REQUEST['apply_sf_date']) : 1;
        $atts = $this->sf_apply(((isset($_REQUEST['atts']) and is_array($_REQUEST['atts'])) ? $this->main->sanitize_deep_array($_REQUEST['atts']) : array()), $this->sf, $apply_sf_date);

        // Initialize the skin
        $this->initialize($atts);

        // Override variables
        if(strpos($this->style, 'fluent') === false)
        {
            $this->start_date = isset($_REQUEST['mec_start_date']) ? sanitize_text_field($_REQUEST['mec_start_date']) : date('y-m-d');
        }
        else
        {
            $this->maximum_date = isset($_REQUEST['mec_maximum_date']) ? sanitize_text_field($_REQUEST['mec_maximum_date']) : '';
            $mecStartDate = isset($_REQUEST['mec_start_date']) ? sanitize_text_field($_REQUEST['mec_start_date']) : date('y-m-d');
            $this->start_date = strtotime($mecStartDate) > strtotime($this->maximum_date) ? $this->maximum_date :  $mecStartDate;
            $this->year = isset($_REQUEST['mec_year']) ? sanitize_text_field($_REQUEST['mec_year']) : NULL;
            $this->month = isset($_REQUEST['mec_month']) ? sanitize_text_field($_REQUEST['mec_month']) : NULL;
            $this->loadMoreRunning = true;
        }

        $this->end_date = $this->start_date;
        $this->offset = isset($_REQUEST['mec_offset']) ? sanitize_text_field($_REQUEST['mec_offset']) : 0;

        // Apply Maximum Date
        if($apply_sf_date == 1 and isset($this->sf) and isset($this->sf['month']) and trim($this->sf['month'])) $this->maximum_date = date('Y-m-t', strtotime($this->start_date));

        // Return the events
        $this->atts['return_items'] = true;

        // Fetch the events
        $this->fetch();

        // Return the output
        $output = $this->output();

        echo json_encode($output);
        exit;
    }

    /**
    * Load month for AJAX requert / Fluent View
    * @author Webnus <info@webnus.net>
    * @return void
    */
    public function load_month()
    {
        $this->sf = (isset($_REQUEST['sf']) and is_array($_REQUEST['sf'])) ? $this->main->sanitize_deep_array($_REQUEST['sf']) : array();
        $apply_sf_date = isset($_REQUEST['apply_sf_date']) ? sanitize_text_field($_REQUEST['apply_sf_date']) : 1;
        $atts = $this->sf_apply(((isset($_REQUEST['atts']) and is_array($_REQUEST['atts'])) ? $this->main->sanitize_deep_array($_REQUEST['atts']) : array()), $this->sf, $apply_sf_date);

        $navigator_click = isset($_REQUEST['navigator_click']) ? (bool) sanitize_text_field($_REQUEST['navigator_click']) : false;

        // Initialize the skin
        $this->initialize($atts);

        // Search Events If Not Found In Current Month
        $c = 0;
        $break = false;

        do
        {
            if($c > 12) $break = true;
            if($c and !$break)
            {
                if(intval($this->month) == 12)
                {
                    $this->year = intval($this->year)+1;
                    $this->month = '01';
                }

                $this->month = sprintf("%02d", intval($this->month)+1);
            }
            else
            {
                // Start Date
                $this->year = isset($_REQUEST['mec_year']) ? sanitize_text_field($_REQUEST['mec_year']) : current_time('Y');
                $this->month = isset($_REQUEST['mec_month']) ? sanitize_text_field($_REQUEST['mec_month']) : current_time('m');
            }

            if($this->show_only_expired_events)
            {
                $this->start_date = date('Y-m-d', strtotime($this->year.'-'.$this->month.'-01'));
                $this->active_day = date('Y-m-t', strtotime($this->year.'-'.$this->month.'-01'));
            }
            else
            {
                $this->start_date = date('Y-m-d', strtotime($this->year.'-'.$this->month.'-01'));

                $day = current_time('d');
                $this->active_day = $this->year.'-'.$this->month.'-'.$day;

                // If date is not valid then use the first day of month
                if(!$this->main->validate_date($this->active_day, 'Y-m-d')) $this->active_day = $this->year.'-'.$this->month.'-01';
            }

            // We will extend the end date in the loop
            $this->end_date = $this->start_date;

            // Return the events
            $this->atts['return_items'] = true;

            // Fetch the events
            $this->fetch();

            // Break the loop if not resault
            if($break) break;

            // Set active day to current day if not resault
            if(count($this->events)) $this->active_day = key($this->events);
            if($navigator_click) break;

            $c++;
        }
        while(!count($this->events));

        // Return the output
        $output = $this->output();

        echo json_encode($output);
        exit;
    }
}