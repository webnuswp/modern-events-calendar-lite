<?php
/** no direct access **/

use MEC\SingleBuilder\Widgets\EventOrganizers\EventOrganizers;

defined('MECEXEC') or die();

/**
 * Webnus MEC single class.
 * @author Webnus <info@webnus.net>
 */
class MEC_skin_single extends MEC_skins
{
    /**
     * @var string
     */
    public $skin = 'single';

    public $uniqueid;
    public $date_format1;
    public $display_cancellation_reason;

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
        $this->factory->action('wp_ajax_mec_load_single_page', array($this, 'load_single_page'));
        $this->factory->action('wp_ajax_nopriv_mec_load_single_page', array($this, 'load_single_page'));
    }

    /**
     * Initialize the skin
     * @author Webnus <info@webnus.net>
     * @param array $atts
     */
    public function initialize($atts)
    {
        $this->atts = $atts;

        // MEC Settings
        $this->settings = $this->main->get_settings();
        $this->ml_settings = $this->main->get_ml_settings();

        // Date Formats
        $this->date_format1 = (isset($this->ml_settings['single_date_format1']) and trim($this->ml_settings['single_date_format1'])) ? $this->ml_settings['single_date_format1'] : 'M d Y';

        // Single Event Layout
        $this->layout = isset($this->atts['layout']) ? $this->atts['layout'] : NULL;

        // Search Form Status
        $this->sf_status = false;
        $this->sf_display_label = false;
        $this->sf_reset_button = false;
        $this->sf_refine = false;

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
     * @author Webnus <info@webnus.net>
     * @param mixed $event
     */
    public function display_related_posts_widget($event)
    {
        if(!isset($this->settings['related_events'])) return;
        if(isset($this->settings['related_events']) && $this->settings['related_events'] != '1') return;

        if(is_numeric($event)) $event_id = $event;
        elseif(is_object($event) and isset($event->ID)) $event_id = $event->ID;
        else return;

        $limit = (isset($this->settings['related_events_limit']) and trim($this->settings['related_events_limit'])) ? $this->settings['related_events_limit'] : 30;

        $related_args = array(
            'post_type' => $this->main->get_main_post_type(),
            'posts_per_page' => max($limit, 20),
            'post_status' => 'publish',
            'post__not_in' => array($event_id),
            'tax_query' => array(),
            'meta_query' => array(
                'mec_start_date' => array(
                    'key' => 'mec_start_date',
                ),
                'mec_start_day_seconds' => array(
                    'key' => 'mec_start_day_seconds',
                ),
            ),
            'orderby' => array(
                'mec_start_date' => 'ASC',
                'mec_start_day_seconds' => 'ASC',
            ),
        );

        if(isset($this->settings['related_events_basedon_category']) && $this->settings['related_events_basedon_category'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_category', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_category',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_organizer']) && $this->settings['related_events_basedon_organizer'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_organizer', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_organizer',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_location']) && $this->settings['related_events_basedon_location'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_location', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_location',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_speaker']) && $this->settings['related_events_basedon_speaker'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_speaker', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_speaker',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_label']) && $this->settings['related_events_basedon_label'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_label', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_label',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_tag']) && $this->settings['related_events_basedon_tag'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, apply_filters('mec_taxonomy_tag', ''), array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => apply_filters('mec_taxonomy_tag', ''),
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        // Display Expired Events
        $display_expired_events = (isset($this->settings['related_events_display_expireds']) && $this->settings['related_events_display_expireds']);

        $related_args['tax_query']['relation'] = 'OR';
        $related_args = apply_filters('mec_add_to_related_post_query', $related_args, $event_id);

        $now = current_time('timestamp');
        $printed = 0;

        $query = new WP_Query($related_args);
        if($query->have_posts())
        {
            ?>
            <div class="row mec-related-events-wrap">
                <h3 class="mec-rec-events-title"><?php echo esc_html__('Related Events', 'modern-events-calendar-lite' ); ?></h3>
                <div class="mec-related-events">
                    <?php while($query->have_posts()): if($printed >= min($limit, 4)) break; $query->the_post(); ?>
                        <?php
                            // Event Repeat Type
                            $repeat_type = get_post_meta(get_the_ID(), 'mec_repeat_type', true);

                            $occurrence = date('Y-m-d');
                            if(!in_array($repeat_type, array('certain_weekdays', 'custom_days', 'weekday', 'weekend', 'advanced')))
                            {
                                $new_occurrence = date('Y-m-d', strtotime('-1 day', strtotime($occurrence)));
                                if(in_array($repeat_type, array('monthly')) and date('m', strtotime($new_occurrence)) != date('m', strtotime($occurrence))) $new_occurrence = date('Y-m-d', strtotime($occurrence));

                                $occurrence = $new_occurrence;
                            }

                            $dates = $this->render->dates(get_the_ID(), NULL, 5, $occurrence);

                            $t = 0;
                            do {
                                $d = isset($dates[$t]) ? $dates[$t] : array();

                                $timestamp = (isset($d['start']) and isset($d['start']['timestamp'])) ? $d['start']['timestamp'] : 0;
                                $t++;
                            } while (isset($dates[$t]) and $t <= 5 and $timestamp < $now);

                            // Don't show Expired Events
                            if($display_expired_events or ($timestamp and $timestamp > $now)):

                            $printed += 1;
                            $mec_date = (isset($d['start']) and isset($d['start']['date'])) ? $d['start']['date'] : get_post_meta(get_the_ID(), 'mec_start_date', true);
                            $date = $this->main->date_i18n(get_option('date_format'), strtotime($mec_date));

                            $event_link = $this->main->get_event_date_permalink(get_the_permalink(), $mec_date);

                            // Custom Link
                            $read_more = get_post_meta(get_the_ID(), 'mec_read_more', true);
                            $read_more_occ_url = MEC_feature_occurrences::param(get_the_ID(), $timestamp, 'read_more', $read_more);

                            if($read_more_occ_url and filter_var($read_more_occ_url, FILTER_VALIDATE_URL)) $event_link = $read_more_occ_url;
                        ?>
                        <article class="mec-related-event-post col-md-3 col-sm-12">
                            <figure>
                                <a href="<?php echo esc_url($event_link); ?>">
                                    <?php
                                        if(get_the_post_thumbnail(get_the_ID(), 'thumblist')) echo get_the_post_thumbnail(get_the_ID(), 'thumblist');
                                        else echo '<img src="' . esc_url($this->main->asset('img/no-image.png')).'" />';
                                    ?>
                                </a>
                            </figure>
                            <div class="mec-related-event-content">
                                <span><?php echo esc_html($date); ?></span>
                                <h5>
                                    <a class="mec-color-hover" href="<?php echo esc_url($event_link); ?>"><?php echo get_the_title(); ?></a>
                                    <?php if($display_expired_events && $timestamp && $timestamp < $now): ?>
                                    <span class="mec-holding-status mec-holding-status-expired"><?php esc_html_e('Expired!', 'modern-events-calendar-lite' ); ?></span>
                                    <?php endif; ?>
                                </h5>
                            </div>
                        </article>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php
        }

        wp_reset_postdata();
    }

    public function display_next_previous_events($event)
    {
        if(!isset($this->settings['next_previous_events'])) return;
        if(isset($this->settings['next_previous_events']) && $this->settings['next_previous_events'] != '1') return;

        if(is_numeric($event)) $event_id = $event;
        elseif(is_object($event) and isset($event->ID)) $event_id = $event->ID;
        else return;

        $p_exclude = array($event_id);
        $n_exclude = array($event_id);

        $pskip = (isset($_REQUEST['pskip']) and is_numeric($_REQUEST['pskip']) and $_REQUEST['pskip'] > 0) ? sanitize_text_field($_REQUEST['pskip']) : NULL;
        if($pskip) $p_exclude[] = $pskip;

        $nskip = (isset($_REQUEST['nskip']) and is_numeric($_REQUEST['nskip']) and $_REQUEST['nskip'] > 0) ? sanitize_text_field($_REQUEST['nskip']) : NULL;
        if($nskip) $n_exclude[] = $nskip;

        $date = $event->date;
        $timestamp = (isset($date['start']) and isset($date['start']['timestamp'])) ? $date['start']['timestamp'] : NULL;

        $args = array(
            'post_type' => $this->main->get_main_post_type(),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'ASC',
            'tax_query' => array(),
        );

        if(isset($this->settings['next_previous_events_category']) && $this->settings['next_previous_events_category'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_category', array('fields' => 'slugs'));
            $args['tax_query'][] = array(
                'taxonomy' => 'mec_category',
                'field'    => 'slug',
                'terms' => $post_terms
            );
        }

        if(isset($this->settings['next_previous_events_organizer']) && $this->settings['next_previous_events_organizer'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_organizer', array('fields' => 'slugs'));
            $args['tax_query'][] = array(
                'taxonomy' => 'mec_organizer',
                'field'    => 'slug',
                'terms' => $post_terms
            );
        }

        if(isset($this->settings['next_previous_events_location']) && $this->settings['next_previous_events_location'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_location', array('fields' => 'slugs'));
            $args['tax_query'][] = array(
                'taxonomy' => 'mec_location',
                'field'    => 'slug',
                'terms' => $post_terms
            );
        }

        if(isset($this->settings['next_previous_events_speaker']) && $this->settings['next_previous_events_speaker'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_speaker', array('fields' => 'slugs'));
            $args['tax_query'][] = array(
                'taxonomy' => 'mec_speaker',
                'field'    => 'slug',
                'terms' => $post_terms
            );
        }

        if(isset($this->settings['next_previous_events_label']) && $this->settings['next_previous_events_label'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_label', array('fields' => 'slugs'));
            $args['tax_query'][] = array(
                'taxonomy' => 'mec_label',
                'field'    => 'slug',
                'terms' => $post_terms
            );
        }

        if(isset($this->settings['next_previous_events_tag']) && $this->settings['next_previous_events_tag'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, apply_filters('mec_taxonomy_tag', ''), array('fields' => 'slugs'));
            $args['tax_query'][] = array(
                'taxonomy' => apply_filters('mec_taxonomy_tag', ''),
                'field'    => 'slug',
                'terms' => $post_terms
            );
        }

        $args['tax_query']['relation'] = 'OR';

        $p_args = array_merge($args, array('post__not_in' => $p_exclude));
        $n_args = array_merge($args, array('post__not_in' => $n_exclude));

        $p_args = apply_filters('mec_next_previous_query', $p_args, $event_id);
        $n_args = apply_filters('mec_next_previous_query', $n_args, $event_id);

        $p_IDs = array();
        $n_IDs = array();

        $query = new WP_Query($p_args);
        if($query->have_posts())
        {
            while($query->have_posts())
            {
                $query->the_post();
                $p_IDs[] = get_the_ID();
            }
        }

        wp_reset_postdata();

        if($p_args === $n_args) $n_IDs = $p_IDs;
        else
        {
            $query = new WP_Query($n_args);
            if($query->have_posts())
            {
                while($query->have_posts())
                {
                    $query->the_post();
                    $n_IDs[] = get_the_ID();
                }
            }

            wp_reset_postdata();
        }

        // No Event Found!
        if(!count($p_IDs) and !count($n_IDs)) return;

        $p = $this->db->select("SELECT `post_id`, `tstart` FROM `#__mec_dates` WHERE `tstart`<='".$timestamp."' AND `post_id` IN (".implode(',', $p_IDs).") ORDER BY `tstart` DESC LIMIT 1", 'loadAssoc');
        $n = $this->db->select("SELECT `post_id`, `tstart` FROM `#__mec_dates` WHERE `tstart`>='".$timestamp."' AND `post_id` IN (".implode(',', $n_IDs).") ORDER BY `tstart` ASC LIMIT 1", 'loadAssoc');

        // No Event Found!
        if(!isset($p['post_id']) and !isset($n['post_id'])) return;

        echo '<ul class="mec-next-previous-events">';

        if(is_array($p) and isset($p['post_id']))
        {
            $p_url = $this->main->get_event_date_permalink(get_permalink($p['post_id']), date('Y-m-d', $p['tstart']));
            $p_url = $this->main->add_qs_var('pskip', $event_id, $p_url);

            echo '<li class="mec-previous-event"><a class="mec-color mec-bg-color-hover mec-border-color" href="'.esc_url($p_url).'"><i class="mec-fa-long-arrow-left"></i>'. esc_html__('PRV Event', 'modern-events-calendar-lite' ) .'</a></li>';
        }

        if(is_array($n) and isset($n['post_id']))
        {
            $n_url = $this->main->get_event_date_permalink(get_permalink($n['post_id']), date('Y-m-d', $n['tstart']));
            $n_url = $this->main->add_qs_var('nskip', $event_id, $n_url);

            echo '<li class="mec-next-event"><a class="mec-color mec-bg-color-hover mec-border-color" href="'.esc_html($n_url).'">'. esc_html__('NXT Event', 'modern-events-calendar-lite' ) .'<i class="mec-fa-long-arrow-right"></i></a></li>';
        }

        echo '</ul>';
    }

    /**
     * Fluent Related Post in Single
     * @author Webnus <info@webnus.net>
     * @param integer $event_id
     */
    public function fluent_display_related_posts_widget($event_id)
    {
        if(!is_plugin_active('mec-fluent-layouts/mec-fluent-layouts.php')) return;
        if(!isset($this->settings['related_events'])) return;
        if(isset($this->settings['related_events']) && $this->settings['related_events'] != '1') return;

        $related_args = array(
            'post_type' => $this->main->get_main_post_type(),
            'posts_per_page' => 3,
            'post_status' => 'publish',
            'post__not_in' => array($event_id),
            'orderby' => 'ASC',
            'tax_query' => array(),
        );

        if(isset($this->settings['related_events_basedon_category']) && $this->settings['related_events_basedon_category'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_category', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_category',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_organizer']) && $this->settings['related_events_basedon_organizer'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_organizer', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_organizer',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_location']) && $this->settings['related_events_basedon_location'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_location', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_location',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_speaker']) && $this->settings['related_events_basedon_speaker'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_speaker', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_speaker',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_label']) && $this->settings['related_events_basedon_label'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, 'mec_label', array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => 'mec_label',
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        if(isset($this->settings['related_events_basedon_tag']) && $this->settings['related_events_basedon_tag'] == 1)
        {
            $post_terms = wp_get_object_terms($event_id, apply_filters('mec_taxonomy_tag', ''), array('fields' => 'slugs'));
            $related_args['tax_query'][] = array(
				'taxonomy' => apply_filters('mec_taxonomy_tag', ''),
				'field'    => 'slug',
				'terms' => $post_terms
			);
        }

        $related_args['tax_query']['relation'] = 'OR';
        $related_args = apply_filters('mec_add_to_related_post_query', $related_args, $event_id);

        $query = new WP_Query($related_args);
        if($query->have_posts())
        {
            ?>
            <div class="mec-related-events-wrap">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="mec-rec-events-title"><?php echo esc_html__('Related Events', 'modern-events-calendar-lite' ); ?></h3>
                    </div>
                </div>
                <div class="mec-related-events row">
                    <?php while($query->have_posts()): $query->the_post(); ?>
                        <div class="col-md-4 col-sm-4">
                            <article class="mec-related-event-post">
                                <figure>
                                    <a href="<?php echo get_the_permalink(); ?>">
                                        <?php
                                            if (get_the_post_thumbnail(get_the_ID(), 'thumblist')){
                                                echo MEC_Fluent\Core\pluginBase\MecFluent::generateThumbnail(MEC_Fluent\Core\pluginBase\MecFluent::generateThumbnailURL(get_the_ID(), 322, 250, true), 322, 250);
                                            } else {
                                                echo '<img src="' . esc_url($this->main->asset('img/no-image.png')) . '" />';
                                            }
                                        ?>
                                    </a>
                                    <div class="mec-date-wrap<?php echo get_the_post_thumbnail(get_the_ID(), 'thumblist') ? ' mec-has-img' : ''; ?>">
                                        <?php
                                        $rendered = $this->render->data(get_the_ID());
                                        $dates = $this->render->dates(get_the_ID(), NULL, 1, date('Y-m-d', strtotime('Yesterday')));

                                        $data = new stdClass();
                                        $data->ID = get_the_ID();
                                        $data->data = $rendered;
                                        $data->dates = $dates;
                                        $data->date = $dates[0];

                                        $event = $this->render->after_render($data, $this);
                                        ?>
                                        <div class="mec-event-date">
                                            <span class="mec-event-day-num"><?php echo esc_html($this->main->date_i18n('d', strtotime($event->date['start']['date']))); ?></span>
                                            <span><?php echo esc_html($this->main->date_i18n('F, Y', strtotime($event->date['start']['date']))); ?></span>
                                        </div>
                                        <div class="mec-event-day">
                                            <span><?php echo esc_html($this->main->date_i18n('l', strtotime($event->date['start']['date']))); ?></span>
                                        </div>
                                    </div>
                                </figure>
                                <div class="mec-related-content">
                                    <div class="mec-related-event-content">
                                        <h5 class="mec-event-title">
                                            <a class="mec-color-hover" href="<?php echo esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'], false, $event->data->time)); ?>"><?php echo get_the_title(); ?></a>
                                        </h5>

                                        <?php
                                            $location_id = $this->main->get_master_location_id($event);
                                            $location = ($location_id ? $this->main->get_location_data($location_id) : array());
                                        ?>
                                        <?php if(isset($location['address']) and trim($location['address'])): ?>
                                            <div class="mec-event-location">
                                                <i class="mec-sl-location-pin"></i>
                                                <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address>
                                            </div>
                                        <?php endif; ?>

                                        <?php echo MEC_kses::element($this->main->display_time($event->data->time['start'], $event->data->time['end'])); ?>
                                    </div>
                                    <div class="mec-event-footer">
                                        <?php $soldout = $this->main->get_flags($event); ?>
                                        <a class="mec-booking-button" href="<?php echo esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'], false, $event->data->time)); ?>"><?php echo (is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%')) ? $this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite' )) : $this->main->m('view_detail', esc_html__('View Detail', 'modern-events-calendar-lite' )) ; ?></a>
                                        <?php if(isset($this->settings['social_network_status']) and $this->settings['social_network_status'] != '0') : ?>
                                            <ul class="mec-event-sharing-wrap">
                                                <li class="mec-event-share">
                                                    <a href="#" class="mec-event-share-icon">
                                                        <i class="mec-sl-share"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <ul class="mec-event-sharing">
                                                        <?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?>
                                                    </ul>
                                                </li>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php
        }

        wp_reset_postdata();
    }

    /**
     * Breadcrumbs in Single
     * @param $page_id
     * @author Webnus <info@webnus.net>
     */
    public function display_breadcrumb_widget($page_id)
    {
        /**
         * Home Page
         */
        $homeURL = esc_url(home_url('/'));
        echo '<div class="mec-address"><a href="' . esc_url($homeURL) . '"> ' . esc_html__('Home', 'modern-events-calendar-lite' ) . ' </a> <i class="mec-color mec-sl-arrow-right"></i> ';

        $archive_title = $this->main->get_archive_title();
        $archive_link = $this->main->get_archive_url();

        $referer_url = wp_get_referer();
        if(trim($referer_url))
        {
            $referer_page_id = url_to_postid($referer_url);
            if($referer_page_id and strpos(get_post_field('post_content', $referer_page_id), '[MEC') !== false)
            {
                $archive_link = $referer_url;
                $archive_title = get_the_title($referer_page_id);
            }
        }

        /**
         * Archive Page
         */
        if($archive_link) echo '<a href="' . esc_url($archive_link) . '">' . esc_html($archive_title) . '</a> <i class="mec-color mec-sl-arrow-right"></i> ';

        /**
         * Categories Page
         */
        if(!isset($this->settings['breadcrumbs_category']) or (isset($this->settings['breadcrumbs_category']) and $this->settings['breadcrumbs_category']))
        {
            $categories = wp_get_post_terms($page_id, 'mec_category');
            if(!is_array($categories)) $categories = array();

            foreach($categories as $category) echo '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a> <i class="mec-color mec-sl-arrow-right"></i> ';
        }

        /**
         * Current Event
         */
        echo '<span class="mec-current">' . get_the_title($page_id) . '</span></div>';
    }

    /**
     * Search and returns the filtered events
     * @return array of objects
     * @throws Exception
     * @author Webnus <info@webnus.net>
     */
    public function search()
    {
        // Original Event ID for Multilingual Websites
        $original_event_id = $this->main->get_original_event($this->id);

        $events = array();
        $rendered = $this->render->data($this->id, ($this->atts['content'] ?? ''));

        // Event Repeat Type
        $repeat_type = (!empty($rendered->meta['mec_repeat_type']) ? $rendered->meta['mec_repeat_type'] : '');

        $occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : (isset($this->atts['occurrence']) ? sanitize_text_field($this->atts['occurrence']) : current_time('Y-m-d'));
        $occurrence_time = isset($_GET['time']) ? (int) sanitize_text_field($_GET['time']) : NULL;

        $md_start = $this->main->get_start_of_multiple_days($this->id, $occurrence);
        if($md_start) $occurrence = $md_start;

        $md_start_time = $this->main->get_start_time_of_multiple_days($this->id, $occurrence_time);
        if($md_start_time) $occurrence_time = $md_start_time;

        if(strtotime($occurrence) and in_array($repeat_type, array('certain_weekdays', 'weekday', 'weekend', 'advanced'))) $occurrence = date('Y-m-d', strtotime($occurrence));
        elseif(strtotime($occurrence) and $repeat_type === 'custom_days') $occurrence = date('Y-m-d', strtotime($occurrence)).' 00:00:00';
        elseif(strtotime($occurrence))
        {
            $new_occurrence = date('Y-m-d', strtotime('-1 day', strtotime($occurrence)));
            if(in_array($repeat_type, array('monthly')) and date('m', strtotime($new_occurrence)) != date('m', strtotime($occurrence))) $new_occurrence = date('Y-m-d', strtotime($occurrence));

            $occurrence = $new_occurrence;
        }
        else $occurrence = NULL;

        $data = new stdClass();
        $data->ID = $this->id;
        $data->requested_id = $this->id;
        $data->data = $rendered;

        // Get Event Dates
        $dates = $this->render->dates($this->id, $rendered, $this->maximum_dates, ($occurrence_time ? date('Y-m-d H:i:s', $occurrence_time) : $occurrence));

        // Remove First Date if it is already started!
        if(count($dates) > 1 and (!isset($_GET['occurrence']) or (isset($_GET['occurrence']) and !trim($_GET['occurrence']))))
        {
            $all_dates = $dates;

            // MEC Settings
            $settings = $this->main->get_settings();

            foreach($dates as $d => $date)
            {
                $start_date = (isset($dates[$d]['start']) and isset($dates[$d]['start']['date'])) ? $dates[$d]['start']['date'] : current_time('Y-m-d H:i:s');
                $end_date = (isset($dates[$d]['end']) and isset($dates[$d]['end']['date'])) ? $dates[$d]['end']['date'] : current_time('Y-m-d H:i:s');

                $s_time = '';
                if(!empty($dates))
                {
                    $s_time .= sprintf("%02d", $dates[$d]['start']['hour']).':';
                    $s_time .= sprintf("%02d", $dates[$d]['start']['minutes']);
                    $s_time .= trim($dates[$d]['start']['ampm']);
                }

                $start_time = date('D M j Y G:i:s', strtotime($start_date.' '.$s_time));

                $e_time = '';
                if(!empty($dates))
                {
                    $e_time .= sprintf("%02d", $dates[$d]['end']['hour']).':';
                    $e_time .= sprintf("%02d", $dates[$d]['end']['minutes']);
                    $e_time .= trim($dates[$d]['end']['ampm']);
                }

                $end_time = date('D M j Y G:i:s', strtotime($end_date.' '.$e_time));

                $d1 = new DateTime($start_time);
                $d2 = new DateTime(current_time("D M j Y G:i:s"));
                $d3 = new DateTime($end_time);

                // Booking OnGoing Event Option
                $ongoing_event_book = (isset($settings['booking_ongoing']) and $settings['booking_ongoing'] == '1') ? true : false;
                if($ongoing_event_book)
                {
                    if($d3 < $d2)
                    {
                        unset($dates[$d]);
                    }
                }
                else
                {
                    if($d1 < $d2)
                    {
                        unset($dates[$d]);
                    }
                }
            }

            if(count($dates) === 0) $dates = array(end($all_dates));
        }

        $data->dates = $this->main->remove_canceled_dates($data, array_values($dates));
        $data->date = isset($data->dates[0]) ? $data->dates[0] : array();

        // Set some data from original event in multilingual websites
        if($this->id != $original_event_id)
        {
            $original_tickets = get_post_meta($original_event_id, 'mec_tickets', true);
            if(!is_array($original_tickets)) $original_tickets = array();

            $rendered_tickets = array();
            foreach($original_tickets as $ticket_id=>$original_ticket)
            {
                if(!isset($data->data->tickets[$ticket_id])) continue;
                $rendered_tickets[$ticket_id] = array(
                    'name' => $data->data->tickets[$ticket_id]['name'],
                    'description' => $data->data->tickets[$ticket_id]['description'],
                    'price' => $original_ticket['price'],
                    'price_label' => $data->data->tickets[$ticket_id]['price_label'],
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

        $event = $this->render->after_render($data, $this);

        // Global Event
        $GLOBALS['mec_current_event'] = $event;

        $start_timestamp = (isset($event->data->time['start_timestamp']) ? $event->data->time['start_timestamp'] : (isset($event->date['start']['timestamp']) ? $event->date['start']['timestamp'] : strtotime($event->date['start']['date'])));
        $display_cancellation_reason = get_post_meta($this->id, 'mec_display_cancellation_reason_in_single_page', true);

        $this->display_cancellation_reason = MEC_feature_occurrences::param($this->id, $start_timestamp, 'display_cancellation_reason_in_single_page', $display_cancellation_reason);

        $events[] = $event;
        return $events;
    }

    // Get event
    public function get_event_mec($event_ID)
    {
        if(get_post_type($event_ID) != $this->main->get_main_post_type()) return false;

        // Original Event ID for Multilingual Websites
        $original_event_id = $this->main->get_original_event($event_ID);

        // MEC Settings
        $settings = $this->main->get_settings();

        $events = array();
        $rendered = $this->render->data($event_ID, ($this->atts['content'] ?? ''));

        // Event Repeat Type
        $repeat_type = !empty($rendered->meta['mec_repeat_type']) ?  $rendered->meta['mec_repeat_type'] : '';

        $occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : current_time('Y-m-d');
        $occurrence_time = isset($_GET['time']) ? (int) sanitize_text_field($_GET['time']) : NULL;

        $md_start = $this->main->get_start_of_multiple_days($event_ID, $occurrence);
        if($md_start) $occurrence = $md_start;

        $md_start_time = $this->main->get_start_time_of_multiple_days($event_ID, $occurrence_time);
        if($md_start_time) $occurrence_time = $md_start_time;

        if(strtotime($occurrence) and in_array($repeat_type, array('certain_weekdays', 'custom_days', 'weekday', 'weekend'))) $occurrence = date('Y-m-d', strtotime($occurrence));
        elseif(strtotime($occurrence))
        {
            $new_occurrence = date('Y-m-d', strtotime('-1 day', strtotime($occurrence)));
            if(in_array($repeat_type, array('monthly')) and date('m', strtotime($new_occurrence)) != date('m', strtotime($occurrence))) $new_occurrence = date('Y-m-d', strtotime($occurrence));

            $occurrence = $new_occurrence;
        }
        else $occurrence = NULL;

        $data = new stdClass();
        $data->ID = $event_ID;
        $data->requested_id = $event_ID;
        $data->data = $rendered;

        $maximum_dates = $this->maximum_dates;
        if(isset($settings['booking_maximum_dates']) and trim($settings['booking_maximum_dates'])) $maximum_dates = $settings['booking_maximum_dates'];

        // Apply Maximum of 100
        $maximum_dates = min($maximum_dates, 100);

        // Get Event Dates
        $dates = $this->render->dates($event_ID, $rendered, $maximum_dates, ($occurrence_time ? date('Y-m-d H:i:s', $occurrence_time) : $occurrence));

        // Remove First Date if it is already started!
        if(count($dates) > 1 and (!isset($_GET['occurrence']) or (isset($_GET['occurrence']) and !trim($_GET['occurrence']))))
        {
            $all_dates = $dates;

            foreach($dates as $d => $date)
            {
                $start_date = (isset($dates[$d]['start']) and isset($dates[$d]['start']['date'])) ? $dates[$d]['start']['date'] : current_time('Y-m-d H:i:s');
                $end_date = (isset($dates[$d]['end']) and isset($dates[$d]['end']['date'])) ? $dates[$d]['end']['date'] : current_time('Y-m-d H:i:s');

                $s_time = '';
                if(!empty($dates))
                {
                    $s_time .= sprintf("%02d", $dates[$d]['start']['hour']).':';
                    $s_time .= sprintf("%02d", $dates[$d]['start']['minutes']);
                    $s_time .= trim($dates[$d]['start']['ampm']);
                }

                $start_time = date('D M j Y G:i:s', strtotime($start_date.' '.$s_time));

                $e_time = '';
                if(!empty($dates))
                {
                    $e_time .= sprintf("%02d", $dates[$d]['end']['hour']).':';
                    $e_time .= sprintf("%02d", $dates[$d]['end']['minutes']);
                    $e_time .= trim($dates[$d]['end']['ampm']);
                }

                $end_time = date('D M j Y G:i:s', strtotime($end_date.' '.$e_time));

                $d1 = new DateTime($start_time);
                $d2 = new DateTime(current_time("D M j Y G:i:s"));
                $d3 = new DateTime($end_time);

                // Booking OnGoing Event Option
                $ongoing_event_book = (isset($settings['booking_ongoing']) and $settings['booking_ongoing'] == '1') ? true : false;
                if($ongoing_event_book)
                {
                    if($d3 < $d2)
                    {
                        unset($dates[$d]);
                    }
                }
                else
                {
                    if($d1 < $d2)
                    {
                        unset($dates[$d]);
                    }
                }
            }

            if(count($dates) === 0) $dates = array(end($all_dates));
        }

        $data->dates = $this->main->remove_canceled_dates($data, array_values($dates));
        $data->date = count($data->dates) ? current($data->dates) : array();

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
                    'price_label' => $data->data->tickets[$ticket_id]['price_label'],
                    'limit' => $original_ticket['limit'],
                    'unlimited' => $original_ticket['unlimited'],
                );
            }

            if(count($rendered_tickets)) $data->data->tickets = $rendered_tickets;
            else $data->data->tickets = $original_tickets;

            $data->ID = $original_event_id;
            $data->dates = $this->render->dates($original_event_id, $rendered, $maximum_dates, $occurrence);
            $data->date = $data->dates[0] ?? array();
        }

        $event = $this->render->after_render($data, $this);

        // Global Event
        $GLOBALS['mec_current_event'] = $event;

        $events[] = $event;
        return $events;
    }

    /**
     * Load Single Event Page for AJAX requert
     * @author Webnus <info@webnus.net>
     * @return void
     */
    public function load_single_page()
    {
        $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;
        $layout = isset($_GET['layout']) ? sanitize_text_field($_GET['layout']) : 'm1';

        do_action('mec-ajax-load-single-page-before', $id);

        // Initialize the skin
        $this->initialize(array(
            'id' => $id,
            'layout' => $layout,
            'maximum_dates'=>(isset($this->settings['booking_maximum_dates']) ? $this->settings['booking_maximum_dates'] : 6)
        ));

        // Fetch the events
        $this->fetch();

        // Return the output
        echo MEC_kses::full($this->output());

        do_action('mec-ajax-load-single-page-after', $id);
        exit;
    }

    /**
     * @param string $k
     * @param array $arr
     * @depecated use Mec_Single_Widget::is_enabled instead.
     * @return string
     * @author Webnus <info@webnus.net>
     */
    public function found_value($k, $arr = [])
    {
        $dummy = new Mec_Single_Widget();
        $status = $dummy->is_enabled($k);

        // Legacy Return!
        return $status ? 'on' : '';
    }

    /**
     * @param object next/prev Widget
     * @return void
     */
    public function display_next_prev_widget($event)
    {
        echo MEC_kses::full($this->main->module('next-event.details', array('event' => $event)));
    }

    /**
     * @param object social Widget
     * @return void
     */
    public function display_social_widget($event)
    {
        if(!isset($this->settings['social_network_status']) or (isset($this->settings['social_network_status']) and !$this->settings['social_network_status'])) return;

        $url = isset($event->data->permalink) ? $event->data->permalink : '';
        if(trim($url) == '') return;
        $socials = $this->main->get_social_networks();
        ?>
        <div class="mec-event-social mec-frontbox">
            <h3 class="mec-social-single mec-frontbox-title"><?php esc_html_e('Share this event', 'modern-events-calendar-lite' ); ?></h3>
            <div class="mec-event-sharing">
                <div class="mec-links-details">
                    <ul>
                        <?php
                        foreach($socials as $social)
                        {
                            if(!isset($this->settings['sn'][$social['id']]) or (isset($this->settings['sn'][$social['id']]) and !$this->settings['sn'][$social['id']])) continue;
                            if(is_callable($social['function'])) echo call_user_func($social['function'], $url, $event);
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
    public function display_location_widget($event)
    {
        $location_id = $this->main->get_master_location_id($event);
        $location = ($location_id ? $this->main->get_location_data($location_id) : array());

        if($location_id and count($location))
        {
            $location_description_setting = isset($this->settings['location_description']) ? $this->settings['location_description'] : '';
            $location_terms = get_the_terms($event->data, 'mec_location');
            ?>
            <div class="mec-single-event-location">
                <?php if($location['thumbnail']): ?>
                    <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? esc_attr($location['name']) : ''); ?>">
                <?php endif; ?>
                <i class="mec-sl-location-pin"></i>
                <h3 class="mec-events-single-section-title mec-location"><?php echo esc_html($this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite' ))); ?></h3>
                <dl>
                    <dd class="author fn org"><?php echo MEC_kses::element($this->get_location_html($location)); ?></dd>
                    <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address></dd>
                    <?php if(isset($location['opening_hour']) and trim($location['opening_hour'])): ?>
                    <dd class="mec-location-opening-hour">
                        <h6><?php esc_html_e('Opening Hour', 'modern-events-calendar-lite' ); ?></h6>
                        <span><?php echo esc_html($location['opening_hour']); ?></span>
                    </dd>
                    <?php endif; ?>
                    <?php if(isset($location['url']) and trim($location['url'])): ?>
                    <dd class="mec-location-url">
                        <h6><?php esc_html_e('Website', 'modern-events-calendar-lite' ); ?></h6>
                        <span><a href="<?php echo esc_url($location['url']); ?>" class="mec-color-hover" target="_blank"><?php echo esc_html($location['url']); ?></a></span>
                    </dd>
                    <?php endif; ?>
                    <?php if($location_description_setting == '1' and is_array($location_terms) and count($location_terms)): foreach($location_terms as $location_term) { if ($location_term->term_id == $location['id'] ) {  if(isset($location_term->description) && !empty($location_term->description)): ?>
                    <dd class="mec-location-description">
                        <p><?php echo esc_html($location_term->description);?></p>
                    </dd>
                    <?php endif; } } endif; ?>
                </dl>
            </div>
            <?php
        }
    }

    /**
     * @param object Other Location widget
     * @return void
     */
    public function display_other_location_widget($event)
    {
        echo '<div class="mec-event-meta">';
        $this->show_other_locations($event); // Show Additional Locations
        echo '</div>';
    }

    /**
     * @param object Local Time widget
     * @return void
     */
    public function display_local_time_widget($event)
    {
        echo '<div class="mec-event-meta mec-local-time-details mec-frontbox">';
        echo MEC_kses::full($this->main->module('local-time.details', array('event' => $event)));
        echo '</div>';
    }

    /**
     * @param object Local Time widget
     * @return void
     */
    public function display_attendees_widget($event)
    {
        echo MEC_kses::full($this->main->module('attendees-list.details', array('event' => $event)));
    }

    /**
     * @param object $event
     * @param array $event_m
     * @return void
     */
    public function display_booking_widget($event, $event_m)
    {
        if($this->main->is_sold($event) and count($event->dates) <= 1):
        ?>
            <div class="mec-sold-tickets warning-msg"><?php esc_html_e('Sold out!', 'modern-events-calendar-lite' ); do_action('mec_booking_sold_out',$event, NULL, NULL, array($event->date)); ?></div>
        <?php elseif($this->main->can_show_booking_module($event)):
            $data_lity_class = '';
            if(isset($this->settings['single_booking_style']) and $this->settings['single_booking_style'] == 'modal') $data_lity_class = 'lity-hide '; ?>
            <div id="mec-events-meta-group-booking-<?php echo esc_attr($this->uniqueid); ?>" class="<?php echo esc_attr($data_lity_class); ?>mec-events-meta-group mec-events-meta-group-booking">
                <?php echo MEC_kses::full($this->main->module('booking.default', array('event' => $event_m))); ?>
            </div>
        <?php
        endif;
    }

    /**
     * @param object category widget
     * @return void
     */
    public function display_category_widget($event)
    {
        if(isset($event->data->categories))
        {
            echo '<div class="mec-single-event-category mec-event-meta mec-frontbox">';
            ?>
            <i class="mec-sl-folder"></i>
            <dt><?php echo esc_html($this->main->m('taxonomy_categories', esc_html__('Category', 'modern-events-calendar-lite' ))); ?></dt>
            <dl>
            <?php
            foreach($event->data->categories as $category)
            {
                $color = ((isset($category['color']) and trim($category['color'])) ? $category['color'] : '');

                $color_html = '';
                if($color) $color_html .= '<span class="mec-event-category-color" style="--background-color: '.esc_attr($color).';background-color: '.esc_attr($color).'">&nbsp;</span>';

                $icon = (isset($category['icon']) ? $category['icon'] : '');
                $icon = isset($icon) && $icon != '' ? '<i class="' . esc_attr($icon) . ' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';
                echo '<dd class="mec-events-event-categories"><a href="' . get_term_link($category['id'], 'mec_category') . '" class="mec-color-hover" rel="tag">' . MEC_kses::element($icon . esc_html($category['name']) . $color_html) . '</a></dd>';
            }

            echo '</dl></div>';
        }
    }

    /**
     * @param object cost widget
     * @return void
     */
    public function display_cost_widget($event)
    {
        $cost = $this->main->get_event_cost($event);
        if($cost)
        {
            echo '<div class="mec-event-meta">';
            ?>
            <div class="mec-event-cost">
                <i class="mec-sl-wallet"></i>
                <h3 class="mec-cost"><?php echo esc_html($this->main->m('cost', esc_html__('Cost', 'modern-events-calendar-lite' ))); ?></h3>
                <dl><dd class="mec-events-event-cost">
                    <?php
                    if(is_numeric($cost)) $rendered_cost = $this->main->render_price($cost, $event->ID);
                    else $rendered_cost = $cost;

                    echo apply_filters('mec_display_event_cost', $rendered_cost, $cost);
                    ?>
                </dd></dl>
            </div>
            <?php
            echo '</div>';
        }
    }

    /**
     * @param object countdown widget
     * @return void
     */
    public function display_countdown_widget($event)
    {
        echo '<div class="mec-events-meta-group mec-events-meta-group-countdown">';
        echo MEC_kses::full($this->main->module('countdown.details', array('event' => $event)));
        echo '</div>';
    }

    /**
     * @param object export widget
     * @return void
     */
    public function display_export_widget($event)
    {
        echo MEC_kses::full($this->main->module('export.details', array('event' => $event)));
    }

    /**
     * @param object map widget
     * @return void
     */
    public function display_map_widget($event)
    {
        echo '<div class="mec-events-meta-group mec-events-meta-group-gmap">';
        echo MEC_kses::full($this->main->module('googlemap.details', array('event' => $event)));
        echo '</div>';
    }

    /**
     * @param object date widget
     * @return void
     */
    public function display_date_widget($event)
    {
        $this->date_format1 = (isset($this->ml_settings['single_date_format1']) and trim($this->ml_settings['single_date_format1'])) ? $this->ml_settings['single_date_format1'] : 'M d Y';
        $occurrence = (isset($event->date['start']['date']) ? $event->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
        $occurrence_end_date = (isset($event->date['end']['date']) ? $event->date['end']['date'] : (trim($occurrence) ? $this->main->get_end_date_by_occurrence($event->data->ID, (isset($event->date['start']['date']) ? $event->date['start']['date'] : $occurrence)) : ''));
        $midnight_event = $this->main->is_midnight_event($event);

        echo '<div class="mec-event-meta">';

        // Event Date
        if(isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start']))
        {
            ?>
            <div class="mec-single-event-date">
                <i class="mec-sl-calendar"></i>
                <h3 class="mec-date"><?php esc_html_e('Date', 'modern-events-calendar-lite' ); ?></h3>
                <dl>
                <?php if($midnight_event): ?>
                <dd><abbr class="mec-events-abbr"><?php echo MEC_kses::element($this->main->dateify($event, $this->date_format1)); ?></abbr></dd>
                <?php else: ?>
                <dd><abbr class="mec-events-abbr"><?php echo MEC_kses::element($this->main->date_label((trim($occurrence) ? array('date' => $occurrence) : $event->date['start']), (trim($occurrence_end_date) ? array('date' => $occurrence_end_date) : (isset($event->date['end']) ? $event->date['end'] : NULL)), $this->date_format1, ' - ', true, 0, $event)); ?></abbr></dd>
                <?php endif; ?>
                </dl>
            </div>
            <?php

            do_action( 'mec_single_after_event_date', $event );
        }

        echo '</div>';
    }

    /**
     * @param object
     * @return void
     */
    public function display_more_info_widget($event)
    {
        $more_info = (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') ? $event->data->meta['mec_more_info'] : '';
        if(isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $more_info = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info', $more_info);

        if($more_info)
        {
            $more_info_target = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_target', (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'));
            $more_info_title = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_title', ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : esc_html__('Read More', 'modern-events-calendar-lite' )));
            ?>
            <div class="mec-event-meta">
                <div class="mec-event-more-info">
                    <i class="mec-sl-info"></i>
                    <h3 class="mec-cost"><?php echo esc_html($this->main->m('more_info_link', esc_html__('More Info', 'modern-events-calendar-lite' ))); ?></h3>
                    <dl><dd class="mec-events-event-more-info"><a class="mec-more-info-button a mec-color-hover" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>"><?php echo esc_html($more_info_title); ?></a></dd></dl>
                </div>
            </div>
            <?php
        }
    }

    /**
     * @param object Speakers Widget
     * @return void
     */
    public function display_speakers_widget($event)
    {
        echo MEC_kses::full($this->main->module('speakers.details', array('event' => $event)));
    }

    /**
     * @param object label Widget
     * @return void
     */
    public function display_label_widget($event)
    {
        if(isset($event->data->labels) and !empty($event->data->labels))
        {
            echo '<div class="mec-event-meta">';
            $mec_items = count($event->data->labels);
            $mec_i = 0; ?>
            <div class="mec-single-event-label">
                <i class="mec-fa-bookmark-o"></i>
                <h3 class="mec-cost"><?php echo esc_html($this->main->m('taxonomy_labels', esc_html__('Labels', 'modern-events-calendar-lite' ))); ?></h3>
                <?php foreach ($event->data->labels as $labels => $label) :
                    $seperator = (++$mec_i === $mec_items) ? '' : ',';
                    echo '<dl><dd style="color:' . esc_attr($label['color']) . '">' . esc_html($label["name"] . $seperator) . '</dd></dl>';
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
        echo MEC_kses::full($this->main->module('qrcode.details', array('event' => $event)));
    }

    /**
     * @param object weather Widget
     * @return void
     */
    public function display_weather_widget($event)
    {
        echo MEC_kses::full($this->main->module('weather.details', array('event' => $event)));
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
                        <h3 class="mec-time"><?php esc_html_e('Time', 'modern-events-calendar-lite' ); ?></h3>
                        <i class="mec-time-comment"><?php echo (isset($time_comment) ? esc_html($time_comment) : ''); ?></i>
                        <dl>
                        <?php if($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])): ?>
                            <dd><abbr class="mec-events-abbr"><?php echo esc_html($event->data->time['start']); ?><?php echo esc_html(trim($event->data->time['end']) ? ' - ' . $event->data->time['end'] : ''); ?></abbr></dd>
                        <?php else: ?>
                            <dd><abbr class="mec-events-abbr"><?php echo esc_html($this->main->m('all_day', esc_html__('All Day' , 'modern-events-calendar-lite' ))); ?></abbr></dd>
                        <?php endif; ?>
                        </dl>
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

        $more_info = (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') ? $event->data->meta['mec_more_info'] : '';
        if(isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $more_info = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info', $more_info);

        if($this->main->can_show_booking_module($event)):
        ?>
            <div class="mec-reg-btn mec-frontbox">
                <?php $data_lity_class = ''; if(isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ){ $data_lity_class = 'mec-booking-data-lity'; }  ?>
                <a class="mec-booking-button mec-bg-color <?php echo esc_attr($data_lity_class); ?> <?php if(isset($this->settings['single_booking_style']) and $this->settings['single_booking_style'] != 'modal' ) echo 'simple-booking'; ?>" href="#mec-events-meta-group-booking-<?php echo esc_attr($this->uniqueid); ?>"><?php echo esc_html($this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite' ))); ?></a>
            </div>
        <?php elseif($more_info): ?>
            <?php
                $more_info_target = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_target', (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'));
                $more_info_title = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_title', ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : esc_html__('Read More', 'modern-events-calendar-lite' )));
            ?>
            <div class="mec-reg-btn mec-frontbox">
                <a class="mec-booking-button mec-bg-color" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>">
                    <?php
                        if($more_info_title) echo esc_html__($more_info_title, 'modern-events-calendar-lite' );
                        else echo esc_html($this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite' )));
                    ?>
                </a>
            </div>
        <?php endif;
    }

    /**
     * @param object other organizers Widget
     * @return void
     */
    public function display_other_organizer_widget($event)
    {
        $organizer_id = $this->main->get_master_organizer_id($event);
        $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());

        if($organizer_id and count($organizer))
        {
            echo '<div class="mec-event-meta">';
            $this->show_other_organizers($event);
            echo '</div>';
        }
    }

    /**
     * @param object organizer Widget
     * @return void
     */
    public function display_organizer_widget($event)
    {
        $organizer_id = $this->main->get_master_organizer_id($event);
        $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());

        if($organizer_id and count($organizer))
        {
            echo '<div class="mec-event-meta">';
            ?>
            <div class="mec-single-event-organizer">
                <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                    <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? esc_attr($organizer['name']) : ''); ?>">
                <?php endif; ?>
                <h3 class="mec-events-single-section-title"><?php echo esc_html($this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite' ))); ?></h3>
                <dl>
                <?php if(isset($organizer['thumbnail'])): ?>
                    <dd class="mec-organizer">
                        <i class="mec-sl-home"></i>
                        <h6><?php echo (isset($organizer['name']) ? esc_html($organizer['name']) : ''); ?></h6>
                    </dd>
                <?php endif;
                if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
                <dd class="mec-organizer-tel">
                    <i class="mec-sl-phone"></i>
                    <h6><?php esc_html_e('Phone', 'modern-events-calendar-lite' ); ?></h6>
                    <a href="tel:<?php echo esc_attr($organizer['tel']); ?>"><?php echo esc_html($organizer['tel']); ?></a>
                </dd>
                <?php endif;
                if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                <dd class="mec-organizer-email">
                    <i class="mec-sl-envelope"></i>
                    <h6><?php esc_html_e('Email', 'modern-events-calendar-lite' ); ?></h6>
                    <a href="mailto:<?php echo esc_attr($organizer['email']); ?>"><?php echo esc_html($organizer['email']); ?></a>
                </dd>
                <?php endif;
                if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
                <dd class="mec-organizer-url">
                    <i class="mec-sl-sitemap"></i>
                    <h6><?php esc_html_e('Website', 'modern-events-calendar-lite' ); ?></h6>
                    <span><a href="<?php echo esc_url($organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo esc_html($organizer['url']); ?></a></span>
                </dd>
                <?php endif; ?>
                </dl>
                <?php EventOrganizers::display_social_links( $organizer_id ); ?>
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
        $additional_organizers_status = (!isset($this->settings['additional_organizers']) or (isset($this->settings['additional_organizers']) and $this->settings['additional_organizers']));
        if(!$additional_organizers_status) return;

        $organizer_id = $this->main->get_master_organizer_id($event);

        $organizers = array();
        if(isset($event->data->organizers) && !empty($event->data->organizers)):
        foreach($event->data->organizers as $o) if($o['id'] != $organizer_id) $organizers[$o['id']] = $o;

        if(!count($organizers)) return;

        $organizer_ids = get_post_meta($event->ID, 'mec_additional_organizer_ids', true);
        if(!is_array($organizer_ids)) $organizer_ids = array();
        $organizer_ids = array_unique($organizer_ids);
        ?>
        <div class="mec-single-event-additional-organizers">
            <h3 class="mec-events-single-section-title"><?php echo esc_html($this->main->m('other_organizers', esc_html__('Other Organizers', 'modern-events-calendar-lite' ))); ?></h3>
            <?php foreach($organizer_ids as $o_id): $o_id = apply_filters('wpml_object_id', $o_id, 'mec_organizer', true); if($o_id == $organizer_id) continue; $organizer = (isset($organizers[$o_id]) ? $organizers[$o_id] : NULL); if(!$organizer) continue; ?>
                <div class="mec-single-event-additional-organizer">
                    <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                        <?php if (class_exists('MEC_Fluent\Core\pluginBase\MecFluent') && (isset($this->settings['single_single_style']) and $this->settings['single_single_style'] == 'fluent')) { ?>
                            <img class="mec-img-organizer" src="<?php echo esc_url(MEC_Fluent\Core\pluginBase\MecFluent::generateCustomThumbnailURL($organizer['thumbnail'], 83, 83, true)); ?>" alt="<?php echo (isset($organizer['name']) ? esc_attr($organizer['name']) : ''); ?>">
                        <?php } else { ?>
                            <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? esc_attr($organizer['name']) : ''); ?>">
                        <?php } ?>
                    <?php endif; ?>
                    <dl>
                    <?php if(isset($organizer['thumbnail'])): ?>
                        <dd class="mec-organizer">
                            <i class="mec-sl-home"></i>
                            <h6><?php echo (isset($organizer['name']) ? esc_html($organizer['name']) : ''); ?></h6>
                        </dd>
                    <?php endif;
                    if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
                        <dd class="mec-organizer-tel">
                            <i class="mec-sl-phone"></i>
                            <h6><?php esc_html_e('Phone', 'modern-events-calendar-lite' ); ?></h6>
                            <a href="tel:<?php echo esc_attr($organizer['tel']); ?>"><?php echo esc_html($organizer['tel']); ?></a>
                        </dd>
                    <?php endif;
                    if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                        <dd class="mec-organizer-email">
                            <i class="mec-sl-envelope"></i>
                            <h6><?php esc_html_e('Email', 'modern-events-calendar-lite' ); ?></h6>
                            <a href="mailto:<?php echo esc_attr($organizer['email']); ?>"><?php echo esc_html($organizer['email']); ?></a>
                        </dd>
                    <?php endif;
                    if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
                        <dd class="mec-organizer-url">
                            <i class="mec-sl-sitemap"></i>
                            <h6><?php esc_html_e('Website', 'modern-events-calendar-lite' ); ?></h6>
                            <span><a href="<?php echo esc_url($organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo (isset($organizer['page_label']) and trim($organizer['page_label'])) ? esc_html($organizer['page_label']) : esc_html($organizer['url']); ?></a></span>
                        </dd>
                    <?php endif;
                    $organizer_description_setting = isset( $this->settings['addintional_organizers_description'] ) ? $this->settings['addintional_organizers_description'] : ''; $organizer_terms = get_the_terms($event->data, 'mec_organizer');  if($organizer_description_setting == '1'):
                    foreach($organizer_terms as $organizer_term) { if ($organizer_term->term_id == $organizer['id'] ) {  if(isset($organizer_term->description) && !empty($organizer_term->description)): ?>
                        <dd class="mec-organizer-description">
                            <p><?php echo esc_html($organizer_term->description); ?></p>
                        </dd>
                    <?php endif; } } endif; ?>
                    </dl>
                    <?php EventOrganizers::display_social_links( $o_id ); ?>
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
        if(!isset($event->data->locations)) return;

        $additional_locations_status = (!isset($this->settings['additional_locations']) or (isset($this->settings['additional_locations']) and $this->settings['additional_locations'])) ? true : false;
        if(!$additional_locations_status) return;

        $location_id = $this->main->get_master_location_id($event);

        $locations = array();
        foreach($event->data->locations as $l) if($l['id'] != $location_id) $locations[$l['id']] = $l;

        if(!count($locations)) return;

        $location_ids = get_post_meta($event->ID, 'mec_additional_location_ids', true);
        if(!is_array($location_ids)) $location_ids = array();
        $location_ids = array_unique($location_ids);

        $display_title = (isset($this->settings['additional_locations_disable_title']) and $this->settings['additional_locations_disable_title']) ? false : true;
        ?>
        <div class="mec-single-event-additional-locations">
            <?php $i = 2; ?>
            <?php foreach($location_ids as $l_id): $l_id = apply_filters('wpml_object_id', $l_id, 'mec_location', true); if($l_id == $location_id) continue; $location = (isset($locations[$l_id]) ? $locations[$l_id] : NULL); if(!$location) continue; ?>
                <div class="mec-single-event-location">
                    <?php if($location['thumbnail']): ?>
                    <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? esc_attr($location['name']) : ''); ?>">
                    <?php endif; ?>

                    <?php if($display_title): ?>
                    <i class="mec-sl-location-pin"></i>
                    <h3 class="mec-events-single-section-title mec-location"><?php echo esc_html($this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite' ))); ?> <?php echo esc_html($i); ?></h3>
                    <?php endif; ?>

                    <dl>
                        <dd class="author fn org"><?php echo MEC_kses::element($this->get_location_html($location)); ?></dd>
                        <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address></dd>
                        <?php if(isset($location['opening_hour']) and trim($location['opening_hour'])): ?>
                        <dd class="mec-location-opening-hour">
                            <i class="mec-sl-clock"></i>
                            <h6><?php esc_html_e('Opening Hour', 'modern-events-calendar-lite' ); ?></h6>
                            <span><?php echo esc_html($location['opening_hour']); ?></span>
                        </dd>
                        <?php endif; ?>
                        <?php
                        $location_description_setting = isset( $this->settings['addintional_locations_description'] ) ? $this->settings['addintional_locations_description'] : ''; $location_terms = get_the_terms($event->data, 'mec_location');  if($location_description_setting == '1'):
                        foreach($location_terms as $location_term) { if ($location_term->term_id == $location['id'] ) {  if(isset($location_term->description) && !empty($location_term->description)): ?>
                            <dd class="mec-location-description">
                                <p><?php echo esc_html($location_term->description); ?></p>
                            </dd>
                        <?php endif; } } endif; ?>
                    </dl>
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
        // Timestamp
        $timestamp = (isset($event->data->time['start_timestamp']) ? $event->data->time['start_timestamp'] : (isset($event->date['start']['timestamp']) ? $event->date['start']['timestamp'] : strtotime($event->date['start']['date'])));

        // Get Per Occurrence
        $hourly_schedules = MEC_feature_occurrences::param($event->data->ID, $timestamp, 'hourly_schedules', (isset($event->data->hourly_schedules) ? $event->data->hourly_schedules : array()));

        if(is_array($hourly_schedules) and count($hourly_schedules)):

        // Status of Speakers Feature
        $speakers_status = (!isset($this->settings['speakers_status']) or (isset($this->settings['speakers_status']) and !$this->settings['speakers_status'])) ? false : true;
        $speakers = array();
        ?>
        <div class="mec-event-schedule mec-frontbox">
            <h3 class="mec-schedule-head mec-frontbox-title"><?php esc_html_e('Hourly Schedule', 'modern-events-calendar-lite' ); ?></h3>
            <?php foreach($hourly_schedules as $day): ?>
                <?php if(count($hourly_schedules) >= 1 and isset($day['title'])): ?>
                    <h4 class="mec-schedule-part"><?php echo esc_html($day['title']); ?></h4>
                <?php endif; ?>
                <div class="mec-event-schedule-content">
                    <?php foreach($day['schedules'] as $schedule): ?>
                    <dl>
                        <dt class="mec-schedule-time"><span class="mec-schedule-start-time mec-color"><?php echo esc_html($schedule['from']); ?></span><?php if(trim($schedule['to'])): ?> - <span class="mec-schedule-end-time mec-color"><?php echo esc_html($schedule['to']); ?></span> <?php endif; ?></dt>
                        <dt class="mec-schedule-title"><?php echo esc_html($schedule['title']); ?></dt>
                        <dt class="mec-schedule-description"><?php echo esc_html($schedule['description']); ?></dt>

                        <?php if($speakers_status and isset($schedule['speakers']) and is_array($schedule['speakers']) and count($schedule['speakers'])): ?>
                        <dt class="mec-schedule-speakers">
                            <h6><?php echo esc_html($this->main->m('taxonomy_speakers', esc_html__('Speakers:', 'modern-events-calendar-lite' ))); ?></h6>
                            <?php $speaker_count = count($schedule['speakers']); $i = 0; ?>
                            <?php foreach($schedule['speakers'] as $speaker_id): $speaker = get_term($speaker_id); array_push($speakers, $speaker_id); ?>
                            <a class="mec-color-hover mec-hourly-schedule-speaker-lightbox" href="#mec_hourly_schedule_speaker_lightbox_<?php echo esc_attr($speaker->term_id); ?>" data-lity><?php echo esc_html($speaker->name); ?></a><?php if(++$i != $speaker_count ) echo ","; ?>
                            <?php endforeach; ?>
                        </dt>
                        <?php endif; ?>
                    </dl>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if(count($speakers)): $speakers = array_unique($speakers); foreach($speakers as $speaker_id): $speaker = get_term($speaker_id); ?>
            <div class="lity-hide mec-hourly-schedule-speaker-info" id="mec_hourly_schedule_speaker_lightbox_<?php echo esc_attr($speaker->term_id); ?>">
                <!-- Speaker Thumbnail -->
                <?php if($thumbnail = trim(get_term_meta($speaker->term_id, 'thumbnail', true))): ?>
                <div class="mec-hourly-schedule-speaker-thumbnail">
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($speaker->name); ?>">
                </div>
                <?php endif; ?>
                <div class="mec-hourly-schedule-speaker-details">
                    <!-- Speaker Name -->
                    <div class="mec-hourly-schedule-speaker-name">
                        <?php echo esc_html($speaker->name); ?>
                    </div>
                    <!-- Speaker Job Title -->
                    <?php if($job_title = trim(get_term_meta($speaker->term_id, 'job_title', true))): ?>
                    <div class="mec-hourly-schedule-speaker-job-title mec-color">
                        <?php echo esc_html($job_title); ?>
                    </div>
                    <?php endif; ?>
                    <div class="mec-hourly-schedule-speaker-contact-information">
                        <!-- Speaker Telephone -->
                        <?php if($tel = trim(get_term_meta($speaker->term_id, 'tel', true))): ?>
                            <a href="tel:<?php echo esc_attr($tel); ?>"><i class="mec-fa-phone"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Email -->
                        <?php if($email = trim(get_term_meta($speaker->term_id, 'email', true))): ?>
                            <a href="mailto:<?php echo esc_attr($email); ?>" target="_blank"><i class="mec-fa-envelope"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Website page -->
                        <?php if($website = trim(get_term_meta($speaker->term_id, 'website', true))): ?>
                        <a href="<?php echo esc_url($website); ?>" target="_blank"><i class="mec-fa-external-link-square"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Facebook page -->
                        <?php if($facebook = trim(get_term_meta($speaker->term_id, 'facebook', true))): ?>
                        <a href="<?php echo esc_url($facebook); ?>" target="_blank"><i class="mec-fa-facebook"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Twitter -->
                        <?php if($twitter = trim(get_term_meta($speaker->term_id, 'twitter', true))): ?>
                        <a href="<?php echo esc_url($twitter); ?>" target="_blank"><i class="mec-fa-twitter"></i></a>
                        <?php endif; ?>
                        <!-- Speaker Instagram -->
                        <?php if($instagram = trim(get_term_meta($speaker->term_id, 'instagram', true))): ?>
                        <a href="<?php echo esc_url($instagram); ?>" target="_blank"><i class="mec-fa-instagram"></i></a>
                        <?php endif; ?>
                        <!-- Speaker LinkedIn -->
                        <?php if($linkedin = trim(get_term_meta($speaker->term_id, 'linkedin', true))): ?>
                        <a href="<?php echo esc_url($linkedin); ?>" target="_blank"><i class="mec-fa-linkedin"></i></a>
                        <?php endif; ?>
                    </div>
                    <!-- Speaker Description -->
                    <?php if(trim($speaker->description)): ?>
                    <div class="mec-hourly-schedule-speaker-description">
                        <?php echo MEC_kses::element($speaker->description); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php endif;
    }

    public function display_data_fields($event, $sidebar = false, $shortcode = false)
    {
        $display = isset($this->settings['display_event_fields']) ? (boolean) $this->settings['display_event_fields'] : true;
        if(!$display and !$sidebar and !$shortcode) return;

        $fields = $this->main->get_event_fields();
        if(!is_array($fields) or (is_array($fields) and !count($fields))) return;

        // Start Timestamp
        $start_timestamp = (isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) ? $event->date['start']['timestamp'] : NULL;

        $data = (isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_fields']) and is_array($event->data->meta['mec_fields'])) ? $event->data->meta['mec_fields'] : get_post_meta($event->ID, 'mec_fields', true);
        if($start_timestamp) $data = MEC_feature_occurrences::param($event->ID, $start_timestamp, 'fields', $data);

        if(!is_array($data) or (is_array($data) and !count($data))) return;

        foreach($fields as $n => $item)
        {
            // n meaning number
            if(!is_numeric($n)) continue;

            $result = isset($data[$n]) ? $data[$n] : '';
            if((!is_array($result) and trim($result) == '') or (is_array($result) and !count($result))) continue;

            $content = isset($item['type']) ? $item['type'] : 'text';
            if($content === 'checkbox')
            {
                $cleaned = array();
                foreach($result as $k => $v)
                {
                    if(trim($v) !== '') $cleaned[] = $v;
                }

                $value = $cleaned;
                if(!count($value))
                {
                    $content = NULL;
                    continue;
                }
            }
        }

        if(isset($content) && $content != NULL && (isset($this->settings['display_event_fields_backend']) and $this->settings['display_event_fields_backend'] == 1) or !isset($this->settings['display_event_fields_backend']))
        {
            $date_format = get_option('date_format');
        ?>
        <div class="mec-event-data-fields mec-frontbox <?php echo ($sidebar ? 'mec-data-fields-sidebar' : ''); ?> <?php echo ($shortcode ? 'mec-data-fields-shortcode mec-util-hidden' : ''); ?>">
            <div class="mec-data-fields-tooltip">
                <div class="mec-data-fields-tooltip-box">
                    <ul class="mec-event-data-field-items">
                        <?php foreach($fields as $f => $field): if(!is_numeric($f)) continue; ?>
                        <?php
                            $value = isset($data[$f]) ? $data[$f] : '';
                            if((!is_array($value) and trim($value) == '') or (is_array($value) and !count($value))) continue;

                            $type = isset($field['type']) ? $field['type'] : 'text';
                            if($type === 'checkbox')
                            {
                                $cleaned = array();
                                foreach($value as $k => $v)
                                {
                                    if(trim($v) !== '') $cleaned[] = $v;
                                }

                                $value = $cleaned;
                                if(!count($value)) continue;
                            }

                            $icon = isset($field['icon']) ? $field['icon'] : '';
                        ?>
                        <li class="mec-event-data-field-item mec-field-item-<?php echo esc_attr($type); ?>">
                            <?php if(trim($icon)): ?>
                            <img class="mec-custom-field-icon" src="<?php echo esc_url($icon); ?>" alt="<?php echo (isset($field['label']) ? esc_attr($field['label']) : ''); ?>">
                            <?php endif; ?>

                            <?php if(isset($field['label'])): ?>
                            <span class="mec-event-data-field-name"><?php esc_html_e(stripslashes($field['label']), 'modern-events-calendar-lite' ); ?>: </span>
                            <?php endif; ?>

                            <?php if($type === 'email'): ?>
                                <span class="mec-event-data-field-value"><a href="mailto:<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></a></span>
                            <?php elseif($type === 'tel'): ?>
                                <span class="mec-event-data-field-value"><a href="tel:<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></a></span>
                            <?php elseif($type === 'url'): ?>
                                <span class="mec-event-data-field-value"><a href="<?php echo esc_url($value); ?>" target="_blank"><?php echo esc_html($value); ?></a></span>
                            <?php elseif($type === 'date'): $value = $this->main->to_standard_date($value); ?>
                                <span class="mec-event-data-field-value"><?php echo esc_html($this->main->date_i18n($date_format, strtotime($value))); ?></span>
                            <?php elseif($type === 'textarea'): ?>
                                <span class="mec-event-data-field-value"><?php echo wpautop(stripslashes($value)); ?></span>
                            <?php else: ?>
                                <span class="mec-event-data-field-value"><?php echo (is_array($value) ? esc_html(stripslashes(implode(', ', $value))) : esc_html(stripslashes($value))); ?></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        }
    }

    public function get_location_html($location)
    {
        $location_id = (isset($location['id']) ? $location['id'] : '');
        $location_name = (isset($location['name']) ? $location['name'] : '');

        $location_link = apply_filters('mec_location_single_page_link', '', $location_id, $location_name, $location);
        if(!empty($location_link)) $location_html ='<a href="'.esc_url($location_link).'">'.esc_html($location_name).'</a>';
        else $location_html = $location_name;

        return $location_html;
    }

    public function display_public_download_module($event)
    {
        $file_id = ($event and isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_public_dl_file']) and $event->data->meta['mec_public_dl_file']) ? $event->data->meta['mec_public_dl_file'] : NULL;
        if(!$file_id) return;

        $url = wp_get_attachment_url($file_id);
        if(!$url) return;

        $title = ($event and isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_public_dl_title']) and $event->data->meta['mec_public_dl_title']) ? $event->data->meta['mec_public_dl_title'] : NULL;
        $description = ($event and isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_public_dl_description']) and $event->data->meta['mec_public_dl_description']) ? $event->data->meta['mec_public_dl_description'] : NULL;

        echo MEC_kses::element('<div class="mec-public-download-details mec-frontbox">
            '.($description ? '<p>'.wp_kses(wpautop($description), array('p' => array(), 'br' => array())).'</p>' : '').'
            <a class="button" href="'.esc_url($url).'">'.(trim($title) ? esc_html($title) : esc_html__('Download', 'modern-events-calendar-lite' )).'</a>
        </div>');
    }

    public function display_disclaimer($event)
    {
        // Created by FES?
        $fes = ($event and isset($event->data, $event->data->meta, $event->data->meta['mec_created_by_fes']));

        if($fes and isset($this->settings['fes_disclaimer']) and trim($this->settings['fes_disclaimer'])) return '<p class="mec-disclaimer-alert">'.MEC_kses::element($this->settings['fes_disclaimer']).'</p>';
        return '';
    }

    public function display_trailer_url($event)
    {
        // Trailer URL
        $trailer_url = ($event and isset($event->data, $event->data->meta, $event->data->meta['mec_trailer_url'])) ? $event->data->meta['mec_trailer_url'] : '';

        // No Trailer URL
        if(!trim($trailer_url)) return '';

        $oembed = wp_oembed_get($trailer_url);

        if($oembed) $html = $oembed;
        else
        {
            $title = ($event and isset($event->data, $event->data->meta, $event->data->meta['mec_trailer_title']) and trim($event->data->meta['mec_trailer_title'])) ? $event->data->meta['mec_trailer_title'] : esc_html__('Watch Event Trailer', 'modern-events-calendar-lite' );
            $html = '<a href="'.esc_url($trailer_url).'" target="_blank">'.$title.'</a>';
        }

        return '<p class="mec-trailer">'.$html.'</p>';
    }

    public function display_image_module($event, $single_thumbnail_size = 'full')
    {
        $gallery_html = \MEC\SingleBuilder\SingleBuilder::getInstance()->output('event-gallery', $event->ID, []);

        // Gallery
        if($gallery_html) return $gallery_html;
        // Featured Image
        else
        {
            $featured_image = $event->data->thumbnails[ $single_thumbnail_size ];
            if(isset($this->settings['featured_image_caption']) and $this->settings['featured_image_caption']) $featured_image .= MEC_kses::element($this->main->display_featured_image_caption($event));

            return $featured_image;
        }
    }

    public function display_faq($event)
    {
        // FAQs
        $faqs = isset($event->data, $event->data->meta, $event->data->meta['mec_faq']) && is_array($event->data->meta['mec_faq']) ? $event->data->meta['mec_faq'] : [];

        // No FAQ
        if(!count($faqs)) return;
        ?>
        <div class="mec-event-data-fields mec-frontbox">
            <ul class="mec-faq-list">
                <?php foreach($faqs as $faq): if(!trim($faq['title']) || !trim($faq['body'])) continue; ?>
                <li class="mec-faq-item close">
                    <span class="mec-faq-toggle-icon mec-fa-chevron-up"></span>
                    <div class="mec-faq-title"><h4><?php echo esc_html($faq['title']); ?></h4></div>
                    <div class="mec-faq-content"><p><?php echo esc_html($faq['body']); ?></p></div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}