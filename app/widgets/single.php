<?php

/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Single Widget
 * @author Webnus <info@webnus.net>
 */
class MEC_single_widget extends WP_Widget
{
	/**
	 * Unique identifier.
	 */
	protected $widget_slug = 'MEC_single_widget';

	/**
	 * Constructor method
	 * @author Webnus <info@webnus.net>
	 */
	public function __construct()
	{
		parent::__construct($this->get_widget_slug(), esc_html__('MEC Single Sidebar Items', 'modern-events-calendar-lite'), array('classname' => $this->get_widget_slug() . '-class', 'description' => esc_html__('To manage event details page elements.', 'modern-events-calendar-lite')));

		// Refreshing the widget's cached output with each new post
		add_action('save_post', array($this, 'flush_widget_cache'));
		add_action('deleted_post', array($this, 'flush_widget_cache'));
		add_action('switch_theme', array($this, 'flush_widget_cache'));
	}

	/**
	 * @return string
	 */
	public function get_widget_slug()
	{
		return $this->widget_slug;
	}

	/**
	 * How to display the widget on the screen.
	 * @author Webnus <info@webnus.net>
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance)
	{
        /** @var MEC_main $main */
        $main = MEC::getInstance('app.libraries.main');

        // Not Single Event Page
        if(!is_singular($main->get_main_post_type())) return;

        // General Settings
        $settings = $main->get_settings();

        $layout = (isset($settings['single_single_style']) ? $settings['single_single_style'] : 'modern');
        echo MEC_kses::full($this->get_layout_output($layout, $settings));
	}

    public function get_layout_output($layout, $settings)
    {
        $single = (isset($GLOBALS['mec-widget-single']) ? $GLOBALS['mec-widget-single'] : NULL);
        $event = (isset($GLOBALS['mec-widget-event']) ? $GLOBALS['mec-widget-event'] : NULL);

        if(!$single or !$event) return NULL;

        $occurrence = (isset($GLOBALS['mec-widget-occurrence']) ? $GLOBALS['mec-widget-occurrence'] : NULL);
        $occurrence_end_date = (isset($GLOBALS['mec-widget-occurrence_end_date']) ? $GLOBALS['mec-widget-occurrence_end_date'] : NULL);
        $cost = (isset($GLOBALS['mec-widget-cost']) ? $GLOBALS['mec-widget-cost'] : NULL);
        $more_info = (isset($GLOBALS['mec-widget-more_info']) ? $GLOBALS['mec-widget-more_info'] : NULL);
        $location_id = (isset($GLOBALS['mec-widget-location_id']) ? $GLOBALS['mec-widget-location_id'] : NULL);
        $location = (isset($GLOBALS['mec-widget-location']) ? $GLOBALS['mec-widget-location'] : NULL);
        $organizer_id = (isset($GLOBALS['mec-widget-organizer_id']) ? $GLOBALS['mec-widget-organizer_id'] : NULL);
        $organizer = (isset($GLOBALS['mec-widget-organizer']) ? $GLOBALS['mec-widget-organizer'] : NULL);
        $more_info_target = (isset($GLOBALS['mec-widget-more_info_target']) ? $GLOBALS['mec-widget-more_info_target'] : NULL);
        $more_info_title = (isset($GLOBALS['mec-widget-more_info_title']) ? $GLOBALS['mec-widget-more_info_title'] : NULL);

        $path = MEC::import('app.widgets.single.'.$layout, true, true);

        ob_start();
        include $path;
        return $output = ob_get_clean();
	}

	/**
	 * @param array $instance
	 * @return void
	 */
	public function form($instance)
	{
        /** @var MEC_main $main */
        $main = MEC::getInstance('app.libraries.main');

        // General Settings
        $settings = $main->get_settings();

		$data_time = isset($instance['data_time']) ? esc_attr($instance['data_time']) : '';
		$local_time = isset($instance['local_time']) ? esc_attr($instance['local_time']) : '';
		$event_cost = isset($instance['event_cost']) ? esc_attr($instance['event_cost']) : '';
		$more_info = isset($instance['more_info']) ? esc_attr($instance['more_info']) : '';
		$event_label = isset($instance['event_label']) ? esc_attr($instance['event_label']) : '';
		$event_location = isset($instance['event_location']) ? esc_attr($instance['event_location']) : '';
		$event_categories = isset($instance['event_categories']) ? esc_attr($instance['event_categories']) : '';
		$event_orgnizer = isset($instance['event_orgnizer']) ? esc_attr($instance['event_orgnizer']) : '';
		$event_speakers = isset($instance['event_speakers']) ? esc_attr($instance['event_speakers']) : '';
        $event_sponsors = isset($instance['event_sponsors']) ? esc_attr($instance['event_sponsors']) : '';
		$register_btn = isset($instance['register_btn']) ? esc_attr($instance['register_btn']) : '';
		$attende_module = isset($instance['attende_module']) ? esc_attr($instance['attende_module']) : '';
		$next_module = isset($instance['next_module']) ? esc_attr($instance['next_module']) : '';
		$links_module = isset($instance['links_module']) ? esc_attr($instance['links_module']) : '';
		$weather = isset($instance['weather_module']) ? esc_attr($instance['weather_module']) : '';
		$google_map = isset($instance['google_map']) ? esc_attr($instance['google_map']) : '';
		$qrcode = isset($instance['qrcode_module']) ? esc_attr($instance['qrcode_module']) : '';
		$public_download = isset($instance['public_download_module']) ? esc_attr($instance['public_download_module']) : '';
		$custom_fields = isset($instance['custom_fields_module']) ? esc_attr($instance['custom_fields_module']) : '';
		$virtual_events = isset($instance['virtual_events_module']) ? esc_attr($instance['virtual_events_module']) : '';
        ?>
		<ul class="mec-sortable">
			<li>
				<input class="checkbox" type="checkbox" <?php checked($data_time, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('data_time')); ?>" name="<?php echo esc_attr($this->get_field_name('data_time')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('data_time')); ?>"><?php esc_html_e('Date Time Module', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($local_time, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('local_time')); ?>" name="<?php echo esc_attr($this->get_field_name('local_time')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('local_time')); ?>"><?php esc_html_e('Local Time', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($event_cost, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('event_cost')); ?>" name="<?php echo esc_attr($this->get_field_name('event_cost')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('event_cost')); ?>"><?php esc_html_e('Event Cost', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($more_info, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('more_info')); ?>" name="<?php echo esc_attr($this->get_field_name('more_info')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('more_info')); ?>"><?php esc_html_e('More Info', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($event_label, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('event_label')); ?>" name="<?php echo esc_attr($this->get_field_name('event_label')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('event_label')); ?>"><?php esc_html_e('Event Label', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($event_location, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('event_location')); ?>" name="<?php echo esc_attr($this->get_field_name('event_location')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('event_location')); ?>"><?php esc_html_e('Event Location', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($event_categories, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('event_categories')); ?>" name="<?php echo esc_attr($this->get_field_name('event_categories')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('event_categories')); ?>"><?php esc_html_e('Event Categories', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($event_orgnizer, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('event_orgnizer')); ?>" name="<?php echo esc_attr($this->get_field_name('event_orgnizer')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('event_orgnizer')); ?>"><?php esc_html_e('Event Organizer', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($event_speakers, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('event_speakers')); ?>" name="<?php echo esc_attr($this->get_field_name('event_speakers')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('event_speakers')); ?>"><?php esc_html_e('Event Speakers', 'modern-events-calendar-lite'); ?></label>
			</li>
            <?php if(isset($settings['sponsors_status']) and $settings['sponsors_status']): ?>
            <li>
                <input class="checkbox" type="checkbox" <?php checked($event_sponsors, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('event_sponsors')); ?>" name="<?php echo esc_attr($this->get_field_name('event_sponsors')); ?>" />
                <label for="<?php echo esc_attr($this->get_field_id('event_sponsors')); ?>"><?php esc_html_e('Event Sponsors', 'modern-events-calendar-lite'); ?></label>
            </li>
            <?php endif; ?>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($register_btn, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('register_btn')); ?>" name="<?php echo esc_attr($this->get_field_name('register_btn')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('register_btn')); ?>"><?php esc_html_e('Register Button', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($attende_module, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('attende_module')); ?>" name="<?php echo esc_attr($this->get_field_name('attende_module')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('attende_module')); ?>"><?php esc_html_e('Attendees Module', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($next_module, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('next_module')); ?>" name="<?php echo esc_attr($this->get_field_name('next_module')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('next_module')); ?>"><?php esc_html_e('Next Event', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($links_module, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('links_module')); ?>" name="<?php echo esc_attr($this->get_field_name('links_module')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('links_module')); ?>"><?php esc_html_e('Social Module', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($weather, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('weather_module')); ?>" name="<?php echo esc_attr($this->get_field_name('weather_module')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('weather_module')); ?>"><?php esc_html_e('Weather Module', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($google_map, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('google_map')); ?>" name="<?php echo esc_attr($this->get_field_name('google_map')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('google_map')); ?>"><?php esc_html_e('Google Map', 'modern-events-calendar-lite'); ?></label>
			</li>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($qrcode, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('qrcode_module')); ?>" name="<?php echo esc_attr($this->get_field_name('qrcode_module')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('qrcode_module')); ?>"><?php esc_html_e('QR Code', 'modern-events-calendar-lite'); ?></label>
			</li>
            <li>
                <input class="checkbox" type="checkbox" <?php checked($public_download, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('public_download_module')); ?>" name="<?php echo esc_attr($this->get_field_name('public_download_module')); ?>" />
                <label for="<?php echo esc_attr($this->get_field_id('public_download_module')); ?>"><?php esc_html_e('Public Download', 'modern-events-calendar-lite'); ?></label>
            </li>
            <li>
                <input class="checkbox" type="checkbox" <?php checked($custom_fields, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('custom_fields_module')); ?>" name="<?php echo esc_attr($this->get_field_name('custom_fields_module')); ?>" />
                <label for="<?php echo esc_attr($this->get_field_id('custom_fields_module')); ?>"><?php esc_html_e('Custom Fields', 'modern-events-calendar-lite'); ?></label>
            </li>

			<?php if(!function_exists('is_plugin_active')) include_once(ABSPATH . 'wp-admin/includes/plugin.php'); ?>
			<?php if(is_plugin_active('mec-virtual-events/mec-virtual-events.php')): ?>
			<li>
				<input class="checkbox" type="checkbox" <?php checked($virtual_events, 'on'); ?> id="<?php echo esc_attr($this->get_field_id('virtual_events_module')); ?>" name="<?php echo esc_attr($this->get_field_name('virtual_events_module')); ?>" />
				<label for="<?php echo esc_attr($this->get_field_id('virtual_events_module')); ?>"><?php esc_html_e('Virtual Event', 'modern-events-calendar-lite'); ?></label>
			</li>
			<?php endif;  ?>
		</ul>
        <?php
	}

	public function flush_widget_cache()
	{
		wp_cache_delete($this->get_widget_slug(), 'widget');
	}

	/**
	 * Update the widget settings.
	 * @author Webnus <info@webnus.net>
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['data_time'] = isset($new_instance['data_time']) ? strip_tags($new_instance['data_time']) : '';
		$instance['local_time'] = isset($new_instance['local_time']) ? strip_tags($new_instance['local_time']) : '';
		$instance['event_cost'] = isset($new_instance['event_cost']) ? strip_tags($new_instance['event_cost']) : '';
		$instance['more_info'] = isset($new_instance['more_info']) ? strip_tags($new_instance['more_info']) : '';
		$instance['event_label'] = isset($new_instance['event_label']) ? strip_tags($new_instance['event_label']) : '';
		$instance['event_location'] = isset($new_instance['event_location']) ? strip_tags($new_instance['event_location']) : '';
		$instance['event_categories'] = isset($new_instance['event_categories']) ? strip_tags($new_instance['event_categories']) : '';
		$instance['event_orgnizer'] = isset($new_instance['event_orgnizer']) ? strip_tags($new_instance['event_orgnizer']) : '';
		$instance['event_speakers'] = isset($new_instance['event_speakers']) ? strip_tags($new_instance['event_speakers']) : '';
		$instance['event_sponsors'] = isset($new_instance['event_sponsors']) ? strip_tags($new_instance['event_sponsors']) : '';
		$instance['register_btn'] = isset($new_instance['register_btn']) ? strip_tags($new_instance['register_btn']) : '';
		$instance['attende_module'] = isset($new_instance['attende_module']) ? strip_tags($new_instance['attende_module']) : '';
		$instance['next_module'] = isset($new_instance['next_module']) ? strip_tags($new_instance['next_module']) : '';
		$instance['links_module'] = isset($new_instance['links_module']) ? strip_tags($new_instance['links_module']) : '';
		$instance['weather_module'] = isset($new_instance['weather_module']) ? strip_tags($new_instance['weather_module']) : '';
		$instance['google_map'] = isset($new_instance['google_map']) ? strip_tags($new_instance['google_map']) : '';
		$instance['qrcode_module'] = isset($new_instance['qrcode_module']) ? strip_tags($new_instance['qrcode_module']) : '';
		$instance['public_download_module'] = isset($new_instance['public_download_module']) ? strip_tags($new_instance['public_download_module']) : '';
		$instance['custom_fields_module'] = isset($new_instance['custom_fields_module']) ? strip_tags($new_instance['custom_fields_module']) : '';
		$instance['virtual_events_module'] = isset($new_instance['virtual_events_module']) ? strip_tags($new_instance['virtual_events_module']) : '';

		$this->flush_widget_cache();

		$alloptions = wp_cache_get('alloptions', 'options');
		if(isset($alloptions['MEC_single_widget'])) delete_option('MEC_single_widget');

		return $instance;
	}
}
