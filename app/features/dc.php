<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Dynamic Content
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_dc extends MEC_base
{
    public $factory;
    public $main;
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

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize admin calendar feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Dynamic Content Shortcode
        $this->factory->shortcode('MEC_dc', array($this, 'output'));
    }

    public function output($atts = array())
    {
        $event_id = (isset($atts['event']) ? $atts['event'] : NULL);

        if(!$event_id) $event_id = (isset($atts['id']) ? $atts['id'] : NULL);
        if(!$event_id)
        {
            global $post;
            if($post->post_type === $this->main->get_main_post_type()) $event_id = $post->ID;
        }

        // Key
        $key = (isset($atts['key']) ? $atts['key'] : NULL);

        // Invalid Data
        if(!$event_id or !$key) return '';

        $post = get_post($event_id);

        // Invalid Post
        if($post->post_type !== $this->main->get_main_post_type()) return '';

        // Event
        if(isset($GLOBALS['mec-event'])) $event = $GLOBALS['mec-event'];
        else
        {
            $events = (new MEC_skin_single())->get_event_mec($event_id);
            $event = current($events);
        }

        return $this->render($event, $key);
    }

    public function render($event, $key)
    {
        $meta = $event->data->meta;
        $date = $event->date;

        if($key === 'start_date')
        {
            $date_format = get_option('date_format');
            return $this->main->date_i18n($date_format, strtotime($meta['mec_start_datetime']), $event);
        }
        elseif($key === 'start_time')
        {
            $time_format = get_option('time_format');
            return $this->main->date_i18n($time_format, strtotime($meta['mec_start_datetime']), $event);
        }
        elseif($key === 'start_datetime')
        {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $datetime_format = $date_format.' '.$time_format;

            return $this->main->date_i18n($datetime_format, strtotime($meta['mec_start_datetime']), $event);
        }
        elseif($key === 'end_date')
        {
            $date_format = get_option('date_format');
            return $this->main->date_i18n($date_format, strtotime($meta['mec_end_datetime']), $event);
        }
        elseif($key === 'end_time')
        {
            $time_format = get_option('time_format');
            return $this->main->date_i18n($time_format, strtotime($meta['mec_end_datetime']), $event);
        }
        elseif($key === 'end_datetime')
        {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $datetime_format = $date_format.' '.$time_format;

            return $this->main->date_i18n($datetime_format, strtotime($meta['mec_end_datetime']), $event);
        }
        elseif($key === 'occurrence')
        {
            $date_format = get_option('date_format');
            $midnight_event = $this->main->is_midnight_event($event);

            if($midnight_event) return $this->main->dateify($event, $date_format);
            else return $this->main->date_label($date['start'], $date['end'], $date_format, ' - ', true, 0, $event);
        }
        elseif($key === 'occurrence_start_date')
        {
            $date_format = get_option('date_format');
            return $this->main->date_i18n($date_format, $date['start']['timestamp'], $event);
        }
        elseif($key === 'occurrence_start_time')
        {
            $time_format = get_option('time_format');
            return $this->main->date_i18n($time_format, $date['start']['timestamp'], $event);
        }
        elseif($key === 'occurrence_start_datetime')
        {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $datetime_format = $date_format.' '.$time_format;

            return $this->main->date_i18n($datetime_format, $date['start']['timestamp'], $event);
        }
        elseif($key === 'occurrence_end_date')
        {
            $date_format = get_option('date_format');
            return $this->main->date_i18n($date_format, $date['end']['timestamp'], $event);
        }
        elseif($key === 'occurrence_end_time')
        {
            $time_format = get_option('time_format');
            return $this->main->date_i18n($time_format, $date['end']['timestamp'], $event);
        }
        elseif($key === 'occurrence_end_datetime')
        {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $datetime_format = $date_format.' '.$time_format;

            return $this->main->date_i18n($datetime_format, $date['end']['timestamp'], $event);
        }
        elseif($key === 'location_name')
        {
            $location_id = $this->main->get_master_location_id($event);
            $location = get_term($location_id);

            return ($location and isset($location->name)) ? $location->name : '';
        }
        elseif($key === 'location_address')
        {
            $location_id = $this->main->get_master_location_id($event);
            return get_term_meta($location_id, 'address', true);
        }
        elseif($key === 'location_url')
        {
            $location_id = $this->main->get_master_location_id($event);
            return get_term_meta($location_id, 'url', true);
        }
        elseif($key === 'organizer_name')
        {
            $organizer_id = $this->main->get_master_organizer_id($event);
            $organizer = get_term($organizer_id);

            return ($organizer and isset($organizer->name)) ? $organizer->name : '';
        }
        elseif($key === 'organizer_tel')
        {
            $organizer_id = $this->main->get_master_organizer_id($event);
            return get_term_meta($organizer_id, 'tel', true);
        }
        elseif($key === 'organizer_email')
        {
            $organizer_id = $this->main->get_master_organizer_id($event);
            return get_term_meta($organizer_id, 'email', true);
        }
        elseif($key === 'organizer_url')
        {
            $organizer_id = $this->main->get_master_organizer_id($event);
            return get_term_meta($organizer_id, 'url', true);
        }
        elseif($key === 'cost')
        {
            return $this->main->get_event_cost($event);
        }
        elseif($key === 'more_info_url')
        {
            $more_info_url = (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') ? $event->data->meta['mec_more_info'] : '';
            if(isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $more_info_url = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info', $more_info_url);

            return $more_info_url;
        }
        elseif($key === 'more_info_tag')
        {
            $more_info_url = (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') ? $event->data->meta['mec_more_info'] : '';
            if(isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $more_info_url = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info', $more_info_url);

            $more_info_target = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_target', (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'));
            $more_info_title = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_title', ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : esc_html__('Read More', 'modern-events-calendar-lite')));

            return '<a target="'.esc_attr($more_info_target).'" href="'.esc_url($more_info_url).'">'.esc_html($more_info_title).'</a>';
        }
        else return '';
    }
}