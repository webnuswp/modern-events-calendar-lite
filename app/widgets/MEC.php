<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Widget
 * @author Webnus <info@webnus.biz>
 */
class MEC_MEC_widget extends WP_Widget
{
    /**
     * @var MEC_render
     */
    public $render;

    /**
     * @var MEC_main
     */
    public $main;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // MEC Render Class
        $this->render = MEC::getInstance('app.libraries.render');
        
        // MEC Main Class
        $this->main = MEC::getInstance('app.libraries.main');
        
        parent::__construct('MEC_MEC_widget', __('Modern Events Calendar', 'modern-events-calendar-lite'), array('description'=>__('Show events based on created shortcodes.', 'modern-events-calendar-lite')));
    }

    /**
     * How to display the widget on the screen.
     * @param array $args
     * @param array $instance
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function widget($args, $instance)
    {
        // Inclue OWL Assets. It's needee if Widget is set to load grid view
        $this->main->load_owl_assets();

        // Before Widget
        echo (isset($args['before_widget']) ? $args['before_widget'] : '');
        
        // Print the widget title
        if(!empty($instance['title']))
        {
			echo (isset($args['before_title']) ? $args['before_title'] : '').apply_filters('widget_title', $instance['title']).(isset($args['after_title']) ? $args['after_title'] : '');
		}
        
        $calendar_id = isset($instance['calendar_id']) ? $instance['calendar_id'] : 0;
        $current_hide = isset($instance['current_hide']) ? $instance['current_hide'] : '';
        $atts = array('html-class'=>'mec-widget '.$current_hide, 'style'=>'classic', 'widget'=>true);
        
        // Print the skin output
        echo $this->render->widget($calendar_id, $atts);
        
        // After Widget
        echo (isset($args['after_widget']) ? $args['after_widget'] : '');
    }

    /**
     * Displays the widget settings controls on the widget panel.
     * @param array $instance
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function form($instance)
    {
        $calendars = get_posts(array('post_type'=>'mec_calendars', 'posts_per_page'=>'-1', 'meta_query'=>array(array('key'=>'skin', 'value'=>array('list', 'grid', 'monthly_view'), 'compare'=>'IN'))));
        $current_hide = isset($instance['current_hide']) ? $instance['current_hide'] : '';
        $checkbox_state = false;

        echo '<p class="mec-widget-row-container">'
        .'<label for="'.$this->get_field_id('title').'">'.__('Title:', 'modern-events-calendar-lite').'</label>'
        .'<input class="widefat" type="text" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" value="'.(isset($instance['title']) ? $instance['title'] : '').'" />'
        .'</p>';
        
        if(count($calendars))
        {
            echo '<p class="mec-widget-row-container">'
                .'<label for="'.$this->get_field_id('calendar_id').'">'.__('Shortcode:', 'modern-events-calendar-lite').'</label>'
                .'<select class="widefat" name="'.$this->get_field_name('calendar_id').'" id="'.$this->get_field_id('calendar_id').'" onchange="mec_show_widget_check(this);"><option value="">-----</option>';
            
            foreach($calendars as $calendar) 
            {
                $skin = get_post_meta($calendar->ID, 'skin', true);
                $checkbox_state = (trim($skin) == 'monthly_view' and (isset($instance['calendar_id']) and $instance['calendar_id'] == $calendar->ID)) ? true : false;
                echo '<option data-skin="'.trim($skin).'" value="'.$calendar->ID.'" '.((isset($instance['calendar_id']) and $instance['calendar_id'] == $calendar->ID) ? 'selected="selected"' : '').'>'.$calendar->post_title.'</option>';
            }
            
            echo '</select>'.'</p>'.'<p class="mec-widget-row-container mec-current-check-wrap '.(($checkbox_state) ? '':'mec-util-hidden').'"><label for="'.$this->get_field_id('current_hide').'">'.__('Enable No Event Block Display: ', 'modern-events-calendar-lite').'</label><input type="checkbox" id="'.$this->get_field_id('current_hide').'" name="'.$this->get_field_name('current_hide').'" value="current-hide"'.checked($current_hide, 'current-hide').'>';
        }
        else
        {
            echo '<p class="mec-widget-row-container"><a href="'.$this->main->add_qs_var('post_type', 'mec_calendars', $this->main->URL('admin').'edit.php').'">'.__('Create some calendars first.').'</a></p>';
        }
    }

    /**
     * Update the widget settings.
     * @author Webnus <info@webnus.biz>
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = $new_instance['title'];
        $instance['calendar_id'] = $new_instance['calendar_id'];
        $instance['current_hide'] = $new_instance['current_hide'];

        return $instance;
    }
}