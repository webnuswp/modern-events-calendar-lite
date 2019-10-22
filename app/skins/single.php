<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC single class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_skin_single extends MEC_skins
{
    /**
     * @var string
     */
    public $skin = 'single';

    public $uniqueid;
    public $date_format1;
    
    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Registers skin actions into WordPress
     * @author Webnus <info@webnus.biz>
     */
    public function actions()
    {
        $this->factory->action('wp_ajax_mec_load_single_page', array($this, 'load_single_page'));
        $this->factory->action('wp_ajax_nopriv_mec_load_single_page', array($this, 'load_single_page'));
    }
    
    /**
     * Initialize the skin
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     */
    public function initialize($atts)
    {
        $this->atts = $atts;

        // MEC Settings
        $this->settings = $this->main->get_settings();
        
        // Date Formats
        $this->date_format1 = (isset($this->settings['single_date_format1']) and trim($this->settings['single_date_format1'])) ? $this->settings['single_date_format1'] : 'M d Y';

        // Single Event Layout
        $this->layout = isset($this->atts['layout']) ? $this->atts['layout'] : NULL;
        
        // Search Form Status
        $this->sf_status = false;
        
        // HTML class
        $this->html_class = '';
        if(isset($this->atts['html-class']) and trim($this->atts['html-class']) != '') $this->html_class = $this->atts['html-class'];
        
        // From Widget
        $this->widget = (isset($this->atts['widget']) and trim($this->atts['widget'])) ? true : false;
        
        // Init MEC
        $this->args['mec-skin'] = $this->skin;
        
        $this->id = isset($this->atts['id']) ? $this->atts['id'] : 0;
        $this->uniqueid = mt_rand(1000, 10000);
        $this->maximum_dates = isset($this->atts['maximum_dates']) ? $this->atts['maximum_dates'] : 6;
    }

    /**
     * Related Post in Single
     * @author Webnus <info@webnus.biz>
     */    
    public function display_related_posts_widget($event_id)
    {
        if ( !isset( $this->settings['related_events'] ) ) return;
        if ( isset( $this->settings['related_events'] ) && $this->settings['related_events'] != '1' ) return;

        $related_args = array(
            'post_type' => 'mec-events',
            'posts_per_page' => 4,
            'post_status' => 'publish',
            'post__not_in' => array($event_id),
            'orderby' => 'ASC',
            'tax_query' => array(),
        );

        if (isset($this->settings['related_events_basedon_category']) && $this->settings['related_events_basedon_category'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_category', array('fields'=>'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_category',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }
        if (isset($this->settings['related_events_basedon_organizer']) && $this->settings['related_events_basedon_organizer'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_organizer', array('fields'=>'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_organizer',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }
        if (isset($this->settings['related_events_basedon_location']) && $this->settings['related_events_basedon_location'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_location', array('fields'=>'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_location',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }
        if (isset($this->settings['related_events_basedon_speaker']) && $this->settings['related_events_basedon_speaker'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_speaker', array('fields'=>'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_speaker',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }
        if (isset($this->settings['related_events_basedon_label']) && $this->settings['related_events_basedon_label'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_label', array('fields'=>'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_label',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }
        if (isset($this->settings['related_events_basedon_tag']) && $this->settings['related_events_basedon_tag'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'post_tag', array('fields'=>'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        $related_args['tax_query']['relation'] = 'OR';

        $related_args = apply_filters('mec_add_to_related_post_query', $related_args,$event_id);

        $query = new WP_Query($related_args);

        if ( $query->have_posts() ):
            $start_hour = get_post_meta( get_the_ID(), 'mec_start_time_hour', true);
            $start_min = (get_post_meta( get_the_ID(), 'mec_start_time_minutes', true) < '10') ? '0' . get_post_meta( get_the_ID(), 'mec_start_time_minutes', true) : get_post_meta( get_the_ID(), 'mec_start_time_minutes', true);
            $start_ampm = get_post_meta( get_the_ID(), 'mec_start_time_ampm', true);
            $end_hour = get_post_meta( get_the_ID(), 'mec_end_time_hour', true);
            $end_min = (get_post_meta( get_the_ID(), 'mec_end_time_minutes', true) < '10') ? '0' . get_post_meta( get_the_ID(), 'mec_end_time_minutes', true) : get_post_meta( get_the_ID(), 'mec_end_time_minutes', true);
            $end_ampm = get_post_meta( get_the_ID(), 'mec_end_time_ampm', true);
            $time =  ( get_post_meta( get_the_ID(), 'mec_allday', true) == '1' ) ? __('All of the day' , 'modern-events-calendar-lite') : $start_hour . ':' .  $start_min . ' ' . $start_ampm . ' - ' . $end_hour . ':' .  $end_min . ' ' . $end_ampm;
            ?>
            <div class="row mec-related-events-wrap">
                <h3 class="mec-rec-events-title"><?php echo __('Related Events' ,'modern-events-calendar-lite'); ?></h3>
                <div class="mec-related-events">
                <?php while ( $query->have_posts() ): $query->the_post(); ?>
                    <article class="mec-related-event-post col-md-3 col-sm-3">
                        <figure>
                            <a href="<?php echo get_the_permalink(); ?>">
                            <?php
                            if ( get_the_post_thumbnail(get_the_ID(),'thumblist') ) :
                                echo get_the_post_thumbnail(get_the_ID(),'thumblist');
                            else :
                                echo '<img src="'. plugin_dir_url(__FILE__ ) .'../../assets/img/no-image.png'.'" />';
                            endif;
                            ?>
                            </a>
                        </figure>
                        <div class="mec-related-event-content">
                            <span><?php
                            $date = date(get_option('date_format'), strtotime(get_post_meta( get_the_ID(), 'mec_start_date', true )));
                            echo $date;
                            ?></span>
                            <h5><a class="mec-color-hover" href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h5>
                        </div>
                        
                    </article>
                <?php endwhile; ?>
                </div>
            </div>
            <?php
        endif;
        wp_reset_postdata();
    }

    /**
     * Breadcrumbs in Single
     * @author Webnus <info@webnus.biz>
     */    
    public function display_breadcrumb_widget($page_id)
    {	
        $breadcrumbs_icon = '<i class="mec-color mec-sl-arrow-right"></i>'; // breadcrumbs_icon between crumbs
        $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
        global $post;
        $homeURL = esc_url(home_url('/'));
        echo '<div class="mec-address"><a href="' . esc_url($homeURL) . '"> ' . __('Home', 'modern-events-calendar-lite') . ' </a> ' . $breadcrumbs_icon . ' ';
        $crumbs_title = $this->main->get_archive_title();
        $MEC_CPT = get_post_type_object(get_post_type());
        $slug = $MEC_CPT->rewrite;
        echo '<a href="' . $homeURL . $slug['slug'] . '/">' . $crumbs_title . '</a>';
        if ($showCurrent == 1) echo ' ' . $breadcrumbs_icon . ' ' . '<span class="mec-current">' . get_the_title($page_id) . '</span></div>';
    }

    /**
     * Search and returns the filtered events
     * @author Webnus <info@webnus.biz>
     * @return array of objects
     */
    public function search()
    {
        // Original Event ID for Multilingual Websites
        $original_event_id = $this->main->get_original_event($this->id);

        $events = array();
        $rendered = $this->render->data($this->id, (isset($this->atts['content']) ? $this->atts['content'] : ''));

        // Event Repeat Type
        $repeat_type = !empty($rendered->meta['mec_repeat_type']) ?  $rendered->meta['mec_repeat_type'] : '';

        $occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : date('Y-m-d');

        if(strtotime($occurrence) and in_array($repeat_type, array('certain_weekdays', 'custom_days', 'weekday', 'weekend'))) $occurrence = date('Y-m-d', strtotime($occurrence));
        elseif(strtotime($occurrence)) $occurrence = date('Y-m-d', strtotime('-1 day', strtotime($occurrence)));
        else $occurrence = NULL;

        $data = new stdClass();
        $data->ID = $this->id;
        $data->data = $rendered;

        // Get Event Dates
        $dates = $this->render->dates($this->id, $rendered, $this->maximum_dates, $occurrence);

        // Remove First Date if it is already started!
        if(!isset($_GET['occurrence']) or (isset($_GET['occurrence']) and !trim($_GET['occurrence'])))
        {
            $start_date = (isset($dates[0]['start']) and isset($dates[0]['start']['date'])) ? $dates[0]['start']['date'] : current_time('Y-m-d H:i:s');
            $end_date = (isset($dates[0]['end']) and isset($dates[0]['end']['date'])) ? $dates[0]['end']['date'] : current_time('Y-m-d H:i:s');

            $s_time = '';
            $s_time .= sprintf("%02d", $dates[0]['start']['hour']).':';
            $s_time .= sprintf("%02d", $dates[0]['start']['minutes']);
            $s_time .= trim($dates[0]['start']['ampm']);

            $start_time = date('D M j Y G:i:s', strtotime($start_date.' '.$s_time));

            $e_time = '';
            $e_time .= sprintf("%02d", $dates[0]['end']['hour']).':';
            $e_time .= sprintf("%02d", $dates[0]['end']['minutes']);
            $e_time .= trim($dates[0]['end']['ampm']);

            $end_time = date('D M j Y G:i:s', strtotime($end_date.' '.$e_time));

            $d1 = new DateTime($start_time);
            $d2 = new DateTime(current_time("D M j Y G:i:s"));
            $d3 = new DateTime($end_time);

            // MEC Settings
            $settings = $this->main->get_settings();

            // Booking OnGoing Event Option
            $ongoing_event_book = (isset($settings['booking_ongoing']) and $settings['booking_ongoing'] == '1') ? true : false;

            if($ongoing_event_book)
            {
                if($d3 < $d2)
                {
                    unset($dates[0]);

                    // Get Event Dates
                    $dates = $this->render->dates($this->id, $rendered, $this->maximum_dates);
                }
            }
            else
            {
                if($d1 < $d2)
                {
                    unset($dates[0]);

                    // Get Event Dates
                    $dates = $this->render->dates($this->id, $rendered, $this->maximum_dates);
                }
            }
        }

        $data->dates = $dates;
        $data->date = isset($data->dates[0]) ? $data->dates[0] : array();

        // Set some data from original event in multilingual websites
        if($this->id != $original_event_id)
        {
            $original_tickets = get_post_meta($original_event_id, 'mec_tickets', true);

            $rendered_tickets = array();
            foreach($original_tickets as $ticket_id=>$original_ticket)
            {
                if(!isset($data->data->tickets[$ticket_id])) continue;
                $rendered_tickets[$ticket_id] = array(
                    'name' => $data->data->tickets[$ticket_id]['name'],
                    'description' => $data->data->tickets[$ticket_id]['description'],
                    'price' => $original_ticket['price'],
                    'price_label' => $original_ticket['price_label'],
                    'limit' => $original_ticket['limit'],
                    'unlimited' => $original_ticket['unlimited'],
                );
            }

            if(count($rendered_tickets)) $data->data->tickets = $rendered_tickets;
            else $data->data->tickets = $original_tickets;

            $data->ID = $original_event_id;
            $data->dates = $this->render->dates($original_event_id, $rendered, $this->maximum_dates, $occurrence);
            $data->date = isset($data->dates[0]) ? $data->dates[0] : array();
        }

        $events[] = $data;
        return $events;
    }

    // Get event
    public function get_event_mec($event_ID)
    {
        // Original Event ID for Multilingual Websites
        $original_event_id = $this->main->get_original_event($event_ID);

        $events = array();
        $rendered = $this->render->data($event_ID, (isset($this->atts['content']) ? $this->atts['content'] : ''));

        // Event Repeat Type
        $repeat_type = !empty($rendered->meta['mec_repeat_type']) ?  $rendered->meta['mec_repeat_type'] : '';

        $occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : NULL;
        
        if(strtotime($occurrence) and in_array($repeat_type, array('certain_weekdays', 'custom_days'))) $occurrence = date('Y-m-d', strtotime($occurrence));
        elseif(strtotime($occurrence)) $occurrence = date('Y-m-d', strtotime('-1 day', strtotime($occurrence)));
        else $occurrence = NULL;

        $data = new stdClass();
        $data->ID = $event_ID;
        $data->data = $rendered;
        $data->dates = $this->render->dates($event_ID, $rendered, $this->maximum_dates, $occurrence);
        $data->date = isset($data->dates[0]) ? $data->dates[0] : array();

        // Set some data from original event in multilingual websites
        if($event_ID != $original_event_id)
        {
            $original_tickets = get_post_meta($original_event_id, 'mec_tickets', true);

            $rendered_tickets = array();
            foreach($original_tickets as $ticket_id=>$original_ticket)
            {
                if(!isset($data->data->tickets[$ticket_id])) continue;
                $rendered_tickets[$ticket_id] = array(
                    'name' => $data->data->tickets[$ticket_id]['name'],
                    'description' => $data->data->tickets[$ticket_id]['description'],
                    'price' => $original_ticket['price'],
                    'price_label' => $original_ticket['price_label'],
                    'limit' => $original_ticket['limit'],
                    'unlimited' => $original_ticket['unlimited'],
                );
            }

            if(count($rendered_tickets)) $data->data->tickets = $rendered_tickets;
            else $data->data->tickets = $original_tickets;

            $data->ID = $original_event_id;
            $data->dates = $this->render->dates($original_event_id, $rendered, $this->maximum_dates, $occurrence);
            $data->date = isset($data->dates[0]) ? $data->dates[0] : array();
        }

        $events[] = $data;
        return $events;
    }

    // 

    
    
    /**
     * Load Single Event Page for AJAX requert
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function load_single_page()
    {
        $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;
        $layout = isset($_GET['layout']) ? sanitize_text_field($_GET['layout']) : 'm1';
        
        // Initialize the skin
        $this->initialize(array('id'=>$id, 'layout'=>$layout));
        
        // Fetch the events
        $this->fetch();
        
        // Return the output
        echo $this->output();
        exit;
    }

    /**
     * @author Webnus <info@webnus.biz>
     * @param string $k
     * @param array $arr
     * @return mixed
     */
    public function found_value($k, $arr)
    {
        $dummy = new Mec_Single_Widget();
        $settings = $dummy->get_settings(); 

        $arr = end($settings);
        $ids = array();

        if(is_array($arr) or is_object($arr))
        {
            foreach($arr as $key=>$value)
            {
                if($key === $k) $ids[] = $value;
            }
        }

        return isset($ids[0]) ? $ids[0] : array();
    }

    /**
     * @param object next/prev Widget
     * @return void
     */
    function display_next_prev_widget($event)
    {
        echo $this->main->module('next-event.details', array('event'=>$event));
    }

    /**
     * @param object social Widget
     * @return void
     */
    function display_social_widget($event)
    {
        if (!isset($this->settings['social_network_status']) or (isset($this->settings['social_network_status']) and !$this->settings['social_network_status'])) return;
        $url = isset($event->data->permalink) ? $event->data->permalink : '';
        if (trim($url) == '') return;
        $socials = $this->main->get_social_networks();
        ?>
        <div class="mec-event-social mec-frontbox">
            <h3 class="mec-social-single mec-frontbox-title"><?php _e('Share this event', 'mec-single-builder'); ?></h3>
            <div class="mec-event-sharing">
                <div class="mec-links-details">
                    <ul>
                        <?php
                        foreach ($socials as $social) {
                            if (!isset($this->settings['sn'][$social['id']]) or (isset($this->settings['sn'][$social['id']]) and !$this->settings['sn'][$social['id']])) continue;
                            if (is_callable($social['function'])) echo call_user_func($social['function'], $url, $event);
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php 
    }

    /**
     * @param object Location widget
     * @return void
     */
    function display_location_widget($event)
    {
        if (isset($event->data->locations[$event->data->meta['mec_location_id']]) and !empty($event->data->locations[$event->data->meta['mec_location_id']])) {
        echo '<div class="mec-event-meta">';
        $location = $event->data->locations[$event->data->meta['mec_location_id']];
        ?>
        <div class="mec-single-event-location">
            <?php if ($location['thumbnail']) : ?>
                <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail']); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
            <?php endif; ?>
            <i class="mec-sl-location-pin"></i>
            <h3 class="mec-events-single-section-title mec-location"><?php echo $this->main->m('taxonomy_location', __('Location', 'mec-single-builder')); ?></h3>
            <dd class="author fn org"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></dd>
            <dd class="location">
                <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
            </dd>
        </div>
        <?php
        echo '</div>';
        }
    }

    /**
     * @param object Other Location widget
     * @return void
     */
    function display_other_location_widget($event)
    {
        echo '<div class="mec-event-meta">';
        $this->show_other_locations($event); // Show Additional Locations
        echo '</div>';
    }

    /**
     * @param object Local Time widget
     * @return void
     */
    function display_local_time_widget($event)
    {
        echo '<div class="mec-event-meta mec-local-time-details mec-frontbox">';			
        echo $this->main->module('local-time.details', array('event'=>$event));
        echo '</div>';
    }


    /**
     * @param object Local Time widget
     * @return void
     */
    function display_attendees_widget($event)
    {
        echo $this->main->module('attendees-list.details', array('event'=>$event));
    }

    /**
     * @param object booking widget
     * @return void
     */
    function display_booking_widget($event,$event_m)
    {
        $occurrence = (isset($event->date['start']['date']) ? $event->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
        if ($this->main->is_sold($event, (trim($occurrence) ? $occurrence : $event->date['start']['date'])) and count($event->dates) <= 1) : ?>
            <div class="mec-sold-tickets warning-msg"><?php _e('Sold out!', 'modern-events-calendar-lite'); ?></div>
            <?php elseif ($this->main->can_show_booking_module($event)) :
            $data_lity_class = '';
            if (isset($this->settings['single_booking_style']) and $this->settings['single_booking_style'] == 'modal') $data_lity_class = 'lity-hide '; ?>
                <div id="mec-events-meta-group-booking-<?php echo $single->uniqueid; ?>" class="<?php echo $data_lity_class; ?>mec-events-meta-group mec-events-meta-group-booking">
                    <?php echo $this->main->module('booking.default', array('event' => $event_m)); ?>
            </div>
        <?php
        endif;
    }

    
    /**
     * @param object category widget
     * @return void
     */
    function display_category_widget($event)
    {
        if (isset($event->data->categories)) {
            echo '<div class="mec-single-event-category mec-event-meta mec-frontbox">';
            ?>
            <i class="mec-sl-folder"></i>
            <dt><?php echo $this->main->m('taxonomy_categories', __('Category', 'modern-events-calendar-lite')); ?></dt>
            <?php
            foreach ($event->data->categories as $category) {
                $icon = get_metadata('term', $category['id'], 'mec_cat_icon', true);
                $icon = isset($icon) && $icon != '' ? '<i class="' . $icon . ' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';
                echo '<dd class="mec-events-event-categories">
                    <a href="' . get_term_link($category['id'], 'mec_category') . '" class="mec-color-hover" rel="tag">' . $icon . $category['name'] . '</a></dd>';
            }
            echo '</div>';
        }
    }

    /**
     * @param object cost widget
     * @return void
     */
    function display_cost_widget($event)
    {
        if (isset($event->data->meta['mec_cost']) and $event->data->meta['mec_cost'] != '') {
            echo '<div class="mec-event-meta">';
            ?>
            <div class="mec-event-cost">
                <i class="mec-sl-wallet"></i>
                <h3 class="mec-cost"><?php echo $this->main->m('cost', __('Cost', 'mec-single-builder')); ?></h3>
                <dd class="mec-events-event-cost"><?php echo (is_numeric($event->data->meta['mec_cost']) ? $this->main->render_price($event->data->meta['mec_cost']) : $event->data->meta['mec_cost']); ?></dd>
            </div>
            <?php
            echo '</div>';
        }
    }


    /**
     * @param object countdown widget
     * @return void
     */
    function display_countdown_widget($event)
    {
        echo '<div class="mec-events-meta-group mec-events-meta-group-countdown">';
        echo $this->main->module('countdown.details', array('event' => $event));
        echo '</div>';
    }
    
    /**
     * @param object export widget
     * @return void
     */
    function display_export_widget($event)
    {
        echo $this->main->module('export.details', array('event'=>$event));
    }

    /**
     * @param object map widget
     * @return void
     */
    function display_map_widget($event)
    {
        echo '<div class="mec-events-meta-group mec-events-meta-group-gmap">';
        echo $this->main->module('googlemap.details', array('event'=>$event));
        echo '</div>';
    }

    
    /**
     * @param object date widget
     * @return void
     */
    function display_date_widget($event)
    {
        $occurrence = (isset($event->date['start']['date']) ? $event->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
        $occurrence_end_date 	= trim($occurrence) ? $this->main->get_end_date_by_occurrence($event->data->ID, (isset($event->date['start']['date']) ? $event->date['start']['date'] : $occurrence)) : '';
        echo '<div class="mec-event-meta">';
        // Event Date
        if (isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start'])) {
            ?>
            <div class="mec-single-event-date">
                <i class="mec-sl-calendar"></i>
                <h3 class="mec-date"><?php _e('Date', 'mec-single-builder'); ?></h3>
                <dd><abbr class="mec-events-abbr"><?php echo $this->main->date_label((trim($occurrence) ? array('date' => $occurrence) : $event->date['start']), (trim($occurrence_end_date) ? array('date' => $occurrence_end_date) : (isset($event->date['end']) ? $event->date['end'] : NULL)), 'M d Y'); ?></abbr></dd>
            </div>
            <?php
        }
        echo '</div>';
    }

    /**
     * @param object More Info widget
     * @return void
     */
    function display_more_info_widget($event)
    {
        if (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') {
            echo '<div class="mec-event-meta">';
            ?>
            <div class="mec-event-more-info">
                <i class="mec-sl-info"></i>
                <h3 class="mec-cost"><?php echo $this->main->m('more_info_link', __('More Info', 'mec-single-builder')); ?></h3>
                <dd class="mec-events-event-more-info"><a class="mec-more-info-button a mec-color-hover" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php echo ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : __('Read More', 'mec-single-builder')); ?></a></dd>
            </div>
            <?php
            echo '</div>';
        }
    }

    /**
     * @param object Speakers Widget
     * @return void
     */
    public function display_speakers_widget($event)
    {
        echo $this->main->module('speakers.details', array('event'=>$event));
    }

    /**
     * @param object label Widget
     * @return void
     */
    public function display_label_widget($event)
    {
        if (isset($event->data->labels) and !empty($event->data->labels)) {
            echo '<div class="mec-event-meta">';
            $mec_items = count($event->data->labels);
            $mec_i = 0; ?>
            <div class="mec-single-event-label">
                <i class="mec-fa-bookmark-o"></i>
                <h3 class="mec-cost"><?php echo $this->main->m('taxonomy_labels', __('Labels', 'mec-single-builder')); ?></h3>
                <?php foreach ($event->data->labels as $labels => $label) :
                    $seperator = (++$mec_i === $mec_items) ? '' : ',';
                    echo '<dd style="color:' . $label['color'] . '">' . $label["name"] . $seperator . '</dd>';
                endforeach; ?>
            </div>
            <?php
            echo '</div>';
        }
    }


    /**
     * @param object qrcode Widget
     * @return void
     */
    public function display_qrcode_widget($event)
    {
        echo $this->main->module('qrcode.details', array('event'=>$event));
    }

    /**
     * @param object weather Widget
     * @return void
     */
    public function display_weather_widget($event)
    {
        echo $this->main->module('weather.details', array('event' => $event));
    }

    /**
     * @param object time Widget
     * @return void
     */
    public function display_time_widget($event)
    {
        echo '<div class="mec-event-meta">';
        // Event Time
        if (isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start'])) {
            if (isset($event->data->meta['mec_hide_time']) and $event->data->meta['mec_hide_time'] == '0') {
                $time_comment = isset($event->data->meta['mec_comment']) ? $event->data->meta['mec_comment'] : '';
                $allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
                ?>
                    <div class="mec-single-event-time">
                        <i class="mec-sl-clock " style=""></i>
                        <h3 class="mec-time"><?php _e('Time', 'mec-single-builder'); ?></h3>
                        <i class="mec-time-comment"><?php echo (isset($time_comment) ? $time_comment : ''); ?></i>

                        <?php if ($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])) : ?>
                            <dd><abbr class="mec-events-abbr"><?php echo $event->data->time['start']; ?><?php echo (trim($event->data->time['end']) ? ' - ' . $event->data->time['end'] : ''); ?></abbr></dd>
                        <?php else : ?>
                            <dd><abbr class="mec-events-abbr"><?php _e('All of the day', 'mec-single-builder'); ?></abbr></dd>
                        <?php endif; ?>
                    </div>
                <?php
            }
        }
        echo '</div>';
    }

    /**
     * @param object
     * @return void
     */
    public function display_register_button_widget($event)
    {
        // MEC Settings
        $settings = $this->main->get_settings();
        
        if ($this->main->can_show_booking_module($event)) : ?>
            <div class="mec-reg-btn mec-frontbox">
                <?php $data_lity = $data_lity_class =  ''; if( isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ){ $data_lity = 'data-lity'; $data_lity_class = 'mec-booking-data-lity'; }  ?>
                <a class="mec-booking-button mec-bg-color <?php echo $data_lity_class; ?> <?php if (isset($this->settings['single_booking_style']) and $this->settings['single_booking_style'] != 'modal') echo 'simple-booking'; ?>" href="#mec-events-meta-group-booking-<?php echo $single->uniqueid; ?>" <?php echo $data_lity; ?>><?php echo esc_html($this->main->m('register_button', __('REGISTER', 'mec-single-builder'))); ?></a>
            <?php elseif (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') : ?>
                <a class="mec-booking-button mec-bg-color" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php if (isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) echo esc_html(trim($event->data->meta['mec_more_info_title']), 'mec-single-builder');
                else echo esc_html($this->main->m('register_button', __('REGISTER', 'mec-single-builder')));
                ?></a>
            </div>
        <?php endif;
    }

    /**
     * @param object other organizers Widget
     * @return void
     */
    public function display_other_organizer_widget($event)
    {
        if(isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) && !empty($event->data->organizers[$event->data->meta['mec_organizer_id']]) )
        {
            echo '<div class="mec-event-meta">';
            $this->show_other_organizers(event);
            echo '</div>';
        }
    }


    /**
     * @param object organizer Widget
     * @return void
     */
    public function display_organizer_widget($event)
    {
        if(isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) && !empty($event->data->organizers[$event->data->meta['mec_organizer_id']]) ) {
        echo '<div class="mec-event-meta">';
        $organizer = $event->data->organizers[$event->data->meta['mec_organizer_id']];
        ?>
        <div class="mec-single-event-organizer">
            <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
            <?php endif; ?>
            <h3 class="mec-events-single-section-title"><?php echo $this->main->m('taxonomy_organizer', __('Organizer', 'mec-single-builder')); ?></h3>
            <?php if(isset($organizer['thumbnail'])): ?>
                <dd class="mec-organizer">
                    <i class="mec-sl-home"></i>
                    <h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
                </dd>
            <?php endif;
            if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
            <dd class="mec-organizer-tel">
                <i class="mec-sl-phone"></i>
                <h6><?php _e('Phone', 'mec-single-builder'); ?></h6>
                <a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
            </dd>
            <?php endif; 
            if(isset($organizer['email']) && !empty($organizer['email'])): ?>
            <dd class="mec-organizer-email">
                <i class="mec-sl-envelope"></i>
                <h6><?php _e('Email', 'mec-single-builder'); ?></h6>
                <a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
            </dd>
            <?php endif;
            if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
            <dd class="mec-organizer-url">
                <i class="mec-sl-sitemap"></i>
                <h6><?php _e('Website', 'mec-single-builder'); ?></h6>
                <span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://'.$organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
            </dd>
            <?php endif; ?>
        </div>
        <?php
        echo '</div>';
        }
    }

    /**
     * @param object $event
     * @return void
     */
    public function show_other_organizers($event)
    {
        $additional_organizers_status = (!isset($this->settings['additional_organizers']) or (isset($this->settings['additional_organizers']) and $this->settings['additional_organizers'])) ? true : false;
        if(!$additional_organizers_status) return;

        $organizers = array();
        if ( isset($event->data->organizers) && !empty($event->data->organizers) ) :
        foreach($event->data->organizers as $o) if($o['id'] != $event->data->meta['mec_organizer_id']) $organizers[] = $o;

        if(!count($organizers)) return;
        ?>
        <div class="mec-single-event-additional-organizers">
            <h3 class="mec-events-single-section-title"><?php echo $this->main->m('other_organizers', __('Other Organizers', 'modern-events-calendar-lite')); ?></h3>
            <?php foreach($organizers as $organizer): if($organizer['id'] == $event->data->meta['mec_organizer_id']) continue; ?>
                <div class="mec-single-event-additional-organizer">
                    <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                        <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
                    <?php endif; ?>
                    <?php if(isset($organizer['thumbnail'])): ?>
                        <dd class="mec-organizer">
                            <i class="mec-sl-home"></i>
                            <h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
                        </dd>
                    <?php endif;
                    if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
                        <dd class="mec-organizer-tel">
                            <i class="mec-sl-phone"></i>
                            <h6><?php _e('Phone', 'modern-events-calendar-lite'); ?></h6>
                            <a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
                        </dd>
                    <?php endif;
                    if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                        <dd class="mec-organizer-email">
                            <i class="mec-sl-envelope"></i>
                            <h6><?php _e('Email', 'modern-events-calendar-lite'); ?></h6>
                            <a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
                        </dd>
                    <?php endif;
                    if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
                        <dd class="mec-organizer-url">
                            <i class="mec-sl-sitemap"></i>
                            <h6><?php _e('Website', 'modern-events-calendar-lite'); ?></h6>
                            <span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://'.$organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
                        </dd>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        endif;
    }

    /**
     * @param object $event
     * @return void
     */
    public function show_other_locations($event)
    {
        $additional_locations_status = (!isset($this->settings['additional_locations']) or (isset($this->settings['additional_locations']) and $this->settings['additional_locations'])) ? true : false;
        if(!$additional_locations_status) return;

        $locations = array();
        foreach($event->data->locations as $o) if($o['id'] != $event->data->meta['mec_location_id']) $locations[] = $o;

        if(!count($locations)) return;
        ?>
        <div class="mec-single-event-additional-locations">
            <?php $i = 2 ?>
            <?php foreach($locations as $location): if($location['id'] == $event->data->meta['mec_location_id']) continue; ?>
                <div class="mec-single-event-location">
                    <?php if($location['thumbnail']): ?>
                    <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
                    <?php endif; ?>
                    <i class="mec-sl-location-pin"></i>
                    <h3 class="mec-events-single-section-title mec-location"><?php echo $this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite')); ?> <?php echo $i; ?></h3>
                    <dd class="author fn org"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></dd>
                    <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address></dd>
                </div>
                <?php $i++ ?>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * @param object $event
     * @return void
     */
    public function display_hourly_schedules_widget($event)
    {
        if(isset($event->data->hourly_schedules) and is_array($event->data->hourly_schedules) and count($event->data->hourly_schedules)):

        // Status of Speakers Feature
        $speakers_status = (!isset($this->settings['speakers_status']) or (isset($this->settings['speakers_status']) and !$this->settings['speakers_status'])) ? false : true;
        $speakers = array();
        ?>
        <div class="mec-event-schedule mec-frontbox">
            <h3 class="mec-schedule-head mec-frontbox-title"><?php _e('Hourly Schedule','modern-events-calendar-lite'); ?></h3>
            <?php foreach($event->data->hourly_schedules as $day): ?>
                <?php if(count($event->data->hourly_schedules) >= 1 and isset($day['title'])): ?>
                    <h4 class="mec-schedule-part"><?php echo $day['title']; ?></h4>
                <?php endif; ?>
                <div class="mec-event-schedule-content">
                    <?php foreach($day['schedules'] as $schedule): ?>
                    <dl>
                        <dt class="mec-schedule-time"><span class="mec-schedule-start-time mec-color"><?php echo $schedule['from']; ?></span> - <span class="mec-schedule-end-time mec-color"><?php echo $schedule['to']; ?></span> </dt>
                        <dt class="mec-schedule-title"><?php echo $schedule['title']; ?></dt>
                        <dt class="mec-schedule-description"><?php echo $schedule['description']; ?></dt>

                        <?php if($speakers_status and isset($schedule['speakers']) and is_array($schedule['speakers']) and count($schedule['speakers'])): ?>
                        <dt class="mec-schedule-speakers">
                            <h6><?php echo $this->main->m('taxonomy_speakers', __('Speakers:', 'modern-events-calendar-lite')); ?></h6>
                            <?php $speaker_count = count($schedule['speakers']);  $i = 0; ?>
                            <?php foreach($schedule['speakers'] as $speaker_id): $speaker = get_term($speaker_id); array_push($speakers, $speaker_id); ?>
                            <a class="mec-color-hover mec-hourly-schedule-speaker-lightbox" href="#mec_hourly_schedule_speaker_lightbox_<?php echo $speaker->term_id; ?>" data-lity><?php echo $speaker->name; ?></a><?php if( ++$i != $speaker_count ) echo ","; ?>
                            <?php endforeach; ?>
                        </dt>
                        <?php endif; ?>
                    </dl>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if(count($speakers)): $speakers = array_unique($speakers); foreach($speakers as $speaker_id): $speaker = get_term($speaker_id); ?>
            <div class="lity-hide mec-hourly-schedule-speaker-info" id="mec_hourly_schedule_speaker_lightbox_<?php echo $speaker->term_id; ?>">
                <!-- Speaker Thumbnail -->
                <?php if($thumbnail = trim(get_term_meta($speaker->term_id, 'thumbnail', true))): ?>
                <div class="mec-hourly-schedule-speaker-thumbnail">
                    <img src="<?php echo $thumbnail; ?>" alt="<?php echo $speaker->name; ?>">
                </div>
                <?php endif; ?>
                <div class="mec-hourly-schedule-speaker-details">
                    <!-- Speaker Name -->
                    <div class="mec-hourly-schedule-speaker-name">
                        <?php echo $speaker->name; ?>
                    </div>
                    <!-- Speaker Job Title -->
                    <?php if($job_title = trim(get_term_meta($speaker->term_id, 'job_title', true))): ?>
                    <div class="mec-hourly-schedule-speaker-job-title mec-color">
                        <?php echo $job_title; ?>
                    </div>
                    <?php endif; ?>
                    <div class="mec-hourly-schedule-speaker-contact-information">
                        <!-- Speaker Telephone -->
                        <?php if($tel = trim(get_term_meta($speaker->term_id, 'tel', true))): ?>
                            <a href="tel:<?php echo $tel; ?>"><i class="mec-fa-phone"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Email -->
                        <?php if($email = trim(get_term_meta($speaker->term_id, 'email', true))): ?>
                            <a href="mailto:<?php echo $email; ?>" target="_blank"><i class="mec-fa-envelope"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Facebook page -->
                        <?php if($facebook = trim(get_term_meta($speaker->term_id, 'facebook', true))): ?>
                        <a href="<?php echo $facebook; ?>" target="_blank"><i class="mec-fa-facebook"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Twitter -->
                        <?php if($twitter = trim(get_term_meta($speaker->term_id, 'twitter', true))): ?>
                        <a href="<?php echo $twitter; ?>" target="_blank"><i class="mec-fa-twitter"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Google Plus -->
                        <?php if($instagram = trim(get_term_meta($speaker->term_id, 'instagram', true))): ?>
                        <a href="<?php echo $instagram; ?>" target="_blank"><i class="mec-fa-instagram"></i></a>
                        <?php endif; ?>
                    </div>
                    <!-- Speaker Description -->
                    <?php if(trim($speaker->description)): ?>
                    <div class="mec-hourly-schedule-speaker-description">
                        <?php echo $speaker->description; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php endif;
    }
}