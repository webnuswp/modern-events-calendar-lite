<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC daily_view class.
 * @author Webnus <info@webnus.net>
 */
class MEC_skin_daily_view extends MEC_skins
{
    /**
     * @var string
     */
    public $skin = 'daily_view';

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Registers skin actions into WordPress
     * @author Webnus <info@webnus.net>
     */
    public function actions()
    {
        $this->factory->action('wp_ajax_mec_daily_view_load_month', array($this, 'load_month'));
        $this->factory->action('wp_ajax_nopriv_mec_daily_view_load_month', array($this, 'load_month'));
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

        $this->style = isset($this->skin_options['style']) ? $this->skin_options['style'] : 'classic';

        // Search Form Options
        $this->sf_options = (isset($this->atts['sf-options']) and isset($this->atts['sf-options'][$this->skin])) ? $this->atts['sf-options'][$this->skin] : array();

        // Search Form Status
        $this->sf_status = isset($this->atts['sf_status']) ? $this->atts['sf_status'] : true;
        $this->sf_display_label = isset($this->atts['sf_display_label']) ? $this->atts['sf_display_label'] : false;
        $this->sf_reset_button = isset($this->atts['sf_reset_button']) ? $this->atts['sf_reset_button'] : false;
        $this->sf_refine = isset($this->atts['sf_refine']) ? $this->atts['sf_refine'] : false;

        // Generate an ID for the skin
        $this->id = isset($this->atts['id']) ? $this->atts['id'] : mt_rand(100, 999);

        // Set the ID
        if(!isset($this->atts['id'])) $this->atts['id'] = $this->id;

        // Next/Previous Month
        $this->next_previous_button = isset($this->skin_options['next_previous_button']) ? $this->skin_options['next_previous_button'] : true;

        // HTML class
        $this->html_class = '';
        if(isset($this->atts['html-class']) and trim($this->atts['html-class']) != '') $this->html_class = $this->atts['html-class'];

        // Booking Button
        $this->booking_button = isset($this->skin_options['booking_button']) ? (int) $this->skin_options['booking_button'] : 0;

        // SED Method
        $this->sed_method = isset($this->skin_options['sed_method']) ? $this->skin_options['sed_method'] : '0';

        // Image popup
        $this->image_popup = isset($this->skin_options['image_popup']) ? $this->skin_options['image_popup'] : '0';

        // reason_for_cancellation
        $this->reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

        // display_label
        $this->display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;

        // From Widget
        $this->widget = (isset($this->atts['widget']) and trim($this->atts['widget']));

        // From Full Calendar
        $this->from_full_calendar = (isset($this->skin_options['from_fc']) and trim($this->skin_options['from_fc']));

        // Display Price
        $this->display_price = (isset($this->skin_options['display_price']) and trim($this->skin_options['display_price']));

        // Detailed Time
        $this->display_detailed_time = (isset($this->skin_options['detailed_time']) and trim($this->skin_options['detailed_time']));

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
        $this->args['orderby'] = 'mec_start_day_seconds ID';
        $this->args['order'] = 'ASC';
        $this->args['meta_key'] = 'mec_start_day_seconds';

        // Show Only Expired Events
        $this->show_only_expired_events = (isset($this->atts['show_only_past_events']) and trim($this->atts['show_only_past_events'])) ? '1' : '0';

        // Show Past Events
        if($this->show_only_expired_events) $this->atts['show_past_events'] = '1';

        // Show Past Events
        $this->args['mec-past-events'] = isset($this->atts['show_past_events']) ? $this->atts['show_past_events'] : '0';

        // Start Date
        list($this->year, $this->month, $this->day) = $this->get_start_date();

        $this->start_date = $this->year.'-'.$this->month.'-01';
        $this->active_day = $this->year.'-'.$this->month.'-'.$this->day;

        // Set the maximum date in current month
        if($this->show_only_expired_events) $this->maximum_date = date('Y-m-d H:i:s', current_time('timestamp'));

        // We will extend the end date in the loop
        $this->end_date = $this->start_date;

        // Show Ongoing Events
        $this->show_ongoing_events = (isset($this->atts['show_only_ongoing_events']) and trim($this->atts['show_only_ongoing_events'])) ? '1' : '0';
        if($this->show_ongoing_events) $this->args['mec-show-ongoing-events'] = $this->show_ongoing_events;

        // Include Ongoing Events
        $this->include_ongoing_events = (isset($this->atts['show_ongoing_events']) and trim($this->atts['show_ongoing_events'])) ? '1' : '0';
        if($this->include_ongoing_events) $this->args['mec-include-ongoing-events'] = $this->include_ongoing_events;

        // Auto Month Rotation
        $this->auto_month_rotation = !isset($this->settings['auto_month_rotation']) || $this->settings['auto_month_rotation'];

        do_action('mec-daily-initialize-end', $this);
    }

    /**
     * Search and returns the filtered events
     * @author Webnus <info@webnus.net>
     * @return array of objects
     */
    public function search()
    {
        if($this->show_only_expired_events)
        {
            $start = date('Y-m-d H:i:s', current_time('timestamp'));
            $end = $this->start_date;
        }
        else
        {
            $start = $this->start_date;
            $end = date('Y-m-t', strtotime($this->start_date));
        }

        // Date Events
        $dates = $this->period($start, $end, true);

        $s = $this->start_date;

        $sorted = array();
        while(date('m', strtotime($s)) == $this->month)
        {
            if(isset($dates[$s])) $sorted[$s] = $dates[$s];
            else $sorted[$s] = array();

            $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
        }

        $dates = $sorted;

        // Limit
        $this->args['posts_per_page'] = $this->limit;

        $events = array();
        $qs = array();

        foreach($dates as $date=>$IDs)
        {
            // Check Finish Date
            if(isset($this->maximum_date) and trim($this->maximum_date) and strtotime($date) > strtotime($this->maximum_date))
            {
                $events[$date] = array();
                continue;
            }

            // Include Available Events
            $this->args['post__in'] = array_unique($IDs);

            // Count of events per day
            $IDs_count = array_count_values($IDs);

            // Extending the end date
            $this->end_date = $date;

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

            if(is_array($IDs) and count($IDs) and $query->have_posts())
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
                    }
                }

                usort($d, array($this, 'sort_day_events'));
                $events[$date] = $d;
            }
            else
            {
                $events[$date] = array();
            }

            // Restore original Post Data
            wp_reset_postdata();
        }

        // Initialize Occurrences' Data
        MEC_feature_occurrences::fetch($events);

        return $events;
    }

    /**
     * Returns start day of skin for filtering events
     * @author Webnus <info@webnus.net>
     * @return array
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
            $yesterday = date('Y-m-d', strtotime('Yesterday'));
            if(strtotime($date) > strtotime($yesterday)) $date = $yesterday;
        }

        $time = strtotime($date);
        return array(date('Y', $time), date('m', $time), date('d', $time));
    }

    /**
     * Returns label of dates
     * @author Webnus <info@webnus.net>
     * @return string
     */
    public function get_date_labels()
    {
        $labels = '<div id="mec-owl-calendar-d-table-'.esc_attr($this->id).'-'.date('Ym', strtotime($this->start_date)).'" class="mec-daily-view-date-labels mec-owl-carousel mec-owl-theme">';

        foreach($this->events as $date=>$events)
        {
            $time = strtotime($date);
            $labels .= '<div class="mec-daily-view-day '.(count($events) ? 'mec-has-event' : '').'" id="mec_daily_view_day'.esc_attr($this->id).'_'.date('Ymd', $time).'" data-events-count="'.esc_attr(count($events)).'" data-month-id="'.date('Ym', $time).'" data-day-id="'.date('Ymd', $time).'" data-day-weekday="'.esc_attr($this->main->date_i18n('l', $time)).'" data-day-monthday="'.date('j', $time).'">'.esc_html($this->main->date_i18n('j', $time)).'</div>';
        }

        return $labels.'</div>';
    }

    /**
     * Load month for AJAX requert
     * @author Webnus <info@webnus.net>
     * @return void
     */
    public function load_month()
    {
        $this->sf = (isset($_REQUEST['sf']) and is_array($_REQUEST['sf'])) ? $this->main->sanitize_deep_array($_REQUEST['sf']) : array();
        $apply_sf_date = isset($_REQUEST['apply_sf_date']) ? sanitize_text_field($_REQUEST['apply_sf_date']) : 1;
        $atts = $this->sf_apply(((isset($_REQUEST['atts']) and is_array($_REQUEST['atts'])) ? $this->main->sanitize_deep_array($_REQUEST['atts']) : array()), $this->sf, $apply_sf_date);

        $navigator_click = isset($_REQUEST['navigator_click']) && sanitize_text_field($_REQUEST['navigator_click']);

        // Initialize the skin
        $this->initialize($atts);

        //  Daily view search repeat if not found in current month
        $c = 0;
        $break = false;

        do
        {
            if($c > 12) $break=true;
            if($c and !$break)
            {
                if(intval($this->month == 12))
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

            $this->day = '1';

            $this->start_date = $this->year.'-'.$this->month.'-01';

            // We will extend the end date in the loop
            $this->end_date = $this->start_date;

            // Return the events
            $this->atts['return_items'] = true;

            // Fetch the events
            $this->fetch();

            // Break the loop if not resault
            if($break) break;
            if($navigator_click) break;

            // Auto Rotation is Disabled
            if(!$this->auto_month_rotation) break;

            $c++;

        }
         while(!array_filter($this->events) and count($this->sf));

        // Return the output
        $output = $this->output();

        echo json_encode($output);
        exit;
    }
}