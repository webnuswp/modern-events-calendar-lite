<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Available Spot class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_skin_available_spot extends MEC_skins
{
    /**
     * @var string
     */
    public $skin = 'available_spot';

    public $event_id;
    public $date_format1;
    public $date_format2;

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
    }

    /**
     * Initialize the skin
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     */
    public function initialize($atts)
    {
        $this->atts = $atts;
        $this->skin_options = (isset($this->atts['sk-options']) and isset($this->atts['sk-options'][$this->skin])) ? $this->atts['sk-options'][$this->skin] : array();

        // Date Formats
        $this->date_format1 = (isset($this->skin_options['date_format1']) and trim($this->skin_options['date_format1'])) ? $this->skin_options['date_format1'] : 'j';
        $this->date_format2 = (isset($this->skin_options['date_format2']) and trim($this->skin_options['date_format2'])) ? $this->skin_options['date_format2'] : 'F';

        // Search Form Status
        $this->sf_status = false;

        $this->id = mt_rand(100, 999);

        // Set the ID
        if(!isset($this->atts['id'])) $this->atts['id'] = $this->id;

        // HTML class
        $this->html_class = '';
        if(isset($this->atts['html-class']) and trim($this->atts['html-class']) != '') $this->html_class = $this->atts['html-class'];

        // From Widget
        $this->widget = (isset($this->atts['widget']) and trim($this->atts['widget'])) ? true : false;

        // Init MEC
        $this->args['mec-skin'] = $this->skin;

        // Event ID
        $this->event_id = isset($this->skin_options['event_id']) ? $this->skin_options['event_id'] : '-1';
    }

    /**
     * Search and returns the filtered events
     * @author Webnus <info@webnus.biz>
     * @return array of objects
     */
    public function search()
    {
        $events = array();

        // Get next upcoming event ID
        if($this->event_id == '-1')
        {
            $events[] = $this->main->get_next_upcoming_event();
        }
        else
        {
            $rendered = $this->render->data($this->event_id, (isset($this->atts['content']) ? $this->atts['content'] : ''));

            $data = new stdClass();
            $data->ID = $this->event_id;
            $data->data = $rendered;
            $data->dates = $this->render->dates($this->event_id, $rendered, $this->maximum_dates);
            $data->date = isset($data->dates[0]) ? $data->dates[0] : array();

            $events[] = $data;
        }

        return $events;
    }
}