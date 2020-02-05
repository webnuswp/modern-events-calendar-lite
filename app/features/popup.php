<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_popup extends MEC_base
{
    public $factory;
    public $main;

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
    }
    
    /**
     * Initialize popup feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Shortcode Popup
        $this->factory->action('restrict_manage_posts', array($this, 'add_shortcode_popup'));

        // Shortcode Save
        $this->factory->action('wp_ajax_mec_popup_shortcode', array($this, 'shortcode_save'));
    }

    public function add_shortcode_popup($post_type)
    {
        if($post_type != $this->main->get_shortcode_post_type()) return;

        $path = MEC::import('app.features.popup.shortcode', true, true);
        include $path;
    }

    public function shortcode_save()
    {
        // Security Nonce
        $wpnonce = isset($_POST['_mecnonce']) ? $_POST['_mecnonce'] : NULL;

        // Check if our nonce is set.
        if(!trim($wpnonce)) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce($wpnonce, 'mec_shortcode_popup')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $params = (isset($_POST['shortcode']) and is_array($_POST['shortcode'])) ? $_POST['shortcode'] : array();

        $skin = isset($params['skin']) ? $params['skin'] : 'list';
        $title = isset($params['name']) ? $params['name'] : ucwords(str_replace('_', ' ', $skin));

        $show_past_events = isset($params['show_past_events']) ? $params['show_past_events'] : 1;
        $show_only_past_events = isset($params['show_only_past_events']) ? $params['show_only_past_events'] : 0;
        $show_only_ongoing_events = isset($params['show_only_ongoing_events']) ? $params['show_only_ongoing_events'] : 0;

        $sed = isset($params['sed']) ? $params['sed'] : 0;
        $style = isset($params['style']) ? $params['style'] : 'clean';
        $event = isset($params['event']) ? $params['event'] : 0;

        $skin_options = array(
            'list' => array(
                'style' => $style,
                'start_date_type' => 'today',
                'start_date' => '',
                'maximum_date_range' => '',
                'include_events_times' => 0,
                'load_more_button' => 1,
                'month_divider' => 1,
                'map_on_top' => 0,
                'set_geolocation' => 0,
                'toggle_month_divider' => 0,
            ),
            'grid' => array(
                'style' => $style,
                'start_date_type' => 'today',
                'start_date' => '',
                'maximum_date_range' => '',
                'count' => 3,
                'load_more_button' => 1,
                'map_on_top' => 0,
                'set_geolocation' => 0,
            ),
            'agenda' => array(
                'style' => $style,
                'start_date_type' => 'today',
                'start_date' => '',
                'maximum_date_range' => '',
                'month_divider' => 1,
                'load_more_button' => 1,
            ),
            'full_calendar' => array(
                'start_date_type' => 'start_current_month',
                'default_view' => 'list',
                'monthly_style' => $style,
                'list' => 1,
                'yearly' => 0,
                'monthly' => 1,
                'weekly' => 1,
                'daily' => 1,
                'display_price' => 0,
            ),
            'yearly_view' => array(
                'style' => $style,
                'start_date_type' => 'start_current_year',
                'start_date' => '',
                'next_previous_button' => 1,
            ),
            'monthly_view' => array(
                'style' => $style,
                'start_date_type' => 'start_current_month',
                'start_date' => '',
                'next_previous_button' => 1,
            ),
            'map' => array(
                'start_date_type' => 'today',
                'start_date' => '',
                'limit' => 200,
                'geolocation' => 0,
            ),
            'daily_view' => array(
                'start_date_type' => 'today',
                'start_date' => '',
                'next_previous_button' => 1,
            ),
            'weekly_view' => array(
                'start_date_type' => 'start_current_week',
                'start_date' => '',
                'next_previous_button' => 1,
            ),
            'timetable' => array(
                'style' => $style,
                'start_date_type' => 'start_current_week',
                'start_date' => '',
                'number_of_days' => 5,
                'week_start' => -1,
                'start_time' => 8,
                'end_time' => 20,
                'next_previous_button' => 1,
            ),
            'masonry' => array(
                'start_date_type' => 'today',
                'start_date' => '',
                'maximum_date_range' => '',
                'filter_by' => '',
                'fit_to_row' => 0,
                'masonry_like_grid' => 0,
                'load_more_button' => 1,
            ),
            'cover' => array(
                'style' => $style,
                'event_id' => $event,
            ),
            'countdown' => array(
                'style' => $style,
                'event_id' => $event,
            ),
            'available_spot' => array(
                'event_id' => $event,
            ),
            'carousel' => array(
                'style' => $style,
                'start_date_type' => 'today',
                'start_date' => '',
                'count' => 3,
                'autoplay' => 1,
            ),
            'slider' => array(
                'style' => $style,
                'start_date_type' => 'today',
                'start_date' => '',
                'autoplay' => 1,
            ),
            'timeline' => array(
                'start_date_type' => 'today',
                'start_date' => '',
                'maximum_date_range' => '',
                'load_more_button' => 1,
                'month_divider' => 0,
            ),
            'tile' => array(
                'start_date_type' => 'start_current_month',
                'start_date' => '',
                'count' => 4,
                'next_previous_button' => 1,
            ),
        );

        $sk = isset($skin_options[$skin]) ? $skin_options[$skin] : array('style' => $style, 'start_date_type' => 'today', 'start_date' => '');

        $sk['sed_method'] = $sed;
        $sk['image_popup'] = 0;

        $sf = array();
        $sf_status = 0;

        if($skin == 'full_calendar')
        {
            $sf = array('month_filter'=>array('type'=>'dropdown'), 'text_search'=>array('type'=>'text_input'));
            $sf_status = 1;
        }

        // Create Default Calendars
        $metas = array(
            'label' => '',
            'category' => '',
            'location' => '',
            'organizer' => '',
            'tag' => '',
            'author' => '',
            'skin' => $skin,
            'sk-options' => array(
                $skin => $sk
            ),
            'sf-options' => array($skin => $sf),
            'sf_status' => $sf_status,
            'show_past_events' => $show_past_events,
            'show_only_past_events' => $show_only_past_events,
            'show_only_ongoing_events' => $show_only_ongoing_events,
        );

        $post = array('post_title'=>$title, 'post_content'=>'', 'post_type'=>'mec_calendars', 'post_status'=>'publish');
        $post_id = wp_insert_post($post);

        foreach($metas as $key=>$value) update_post_meta($post_id, $key, $value);

        $this->main->response(array('success'=>1, 'id'=>$post_id));
    }
}