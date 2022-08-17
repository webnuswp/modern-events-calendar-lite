<?php
/** no direct access **/

use MEC\FES\FormBuilder;

defined('MECEXEC') or die();

/**
 * Webnus MEC Occurrences class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_occurrences extends MEC_base
{
    public $factory;
    public $main;
    public $settings;
    public $db;

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

        // Import MEC DB
        $this->db = $this->getDB();
    }

    /**
     * Initialize locations feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Occurrences Status
        $occurrences_status = (isset($this->settings['per_occurrences_status']) and $this->settings['per_occurrences_status'] and $this->getPRO());

        // Feature is not enabled
        if(!$occurrences_status) return;

        // Tab
        $this->factory->filter('mec-single-event-meta-title', array($this, 'tab'), 10, 3);

        // Metabox
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_occurrences'), 18);

        // Occurrences for FES
        if(!isset($this->settings['fes_section_occurrences']) or (isset($this->settings['fes_section_occurrences']) and $this->settings['fes_section_occurrences'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_occurrences'), 18);

        // AJAX
        $this->factory->action('wp_ajax_mec_occurrences_add', array($this, 'add'));
        $this->factory->action('wp_ajax_mec_occurrences_delete', array($this, 'delete'));

        // Save Data
        $this->factory->action('mec_save_event_data', array($this, 'save'), 10, 2);
    }

    public function tab($tabs, $activated, $post)
    {
        $draft = (isset($post->post_status) and $post->post_status != 'auto-draft') ? false : true;
        $repeat_status = get_post_meta($post->ID, 'mec_repeat_status', true);

        if($draft or !$repeat_status) return $tabs;

        $tabs[__('Occurrences', 'modern-events-calendar-lite')] = 'mec-occurrences';
        return $tabs;
    }

    /**
     * Show occurrences of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.net>
     * @param object $post
     */
    public function meta_box_occurrences($post){

        FormBuilder::occurrences( $post );
    }

    public function delete()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_occurrences_delete')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $occurrence_id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';

        // Request is invalid!
        if(!trim($occurrence_id)) $this->main->response(array('success'=>0, 'code'=>'ID_IS_INVALID'));

        $this->db->q("DELETE FROM `#__mec_occurrences` WHERE `id`='".$this->db->escape($occurrence_id)."'");

        $this->main->response(array('success'=>1));
    }

    public function add()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_occurrences_add')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';

        // Date is invalid!
        if(!trim($date) or !trim($id)) $this->main->response(array('success'=>0, 'code'=>'DATE_OR_ID_IS_INVALID'));

        $dates = explode(':', $date);

        // Add Occurrence
        $occurrence_id = $this->db->q("INSERT INTO `#__mec_occurrences` (`post_id`,`occurrence`,`params`) VALUES ('".$id."','".$dates[0]."','".json_encode(array())."')", 'insert');

        $success = 1;

        ob_start();
        $this->get_occurrence_form($occurrence_id);
        $html = ob_get_clean();

        $this->main->response(array('success'=>$success, 'html'=>$html));
    }

    public function get_occurrence_form($occurrence_id)
    {
        $params = $this->get($occurrence_id);
        $data = $this->get_data($occurrence_id);

        $event_id = (isset($data['post_id']) ? $data['post_id'] : 0);
        $post = get_post($event_id);

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $datetime_format = $date_format.' '.$time_format;

        $event_status = (isset($params['event_status']) and trim($params['event_status'])) ? $params['event_status'] : 'EventScheduled';
        $moved_online_link = (isset($params['moved_online_link']) and trim($params['moved_online_link'])) ? $params['moved_online_link'] : '';
        $cancelled_reason = (isset($params['cancelled_reason']) and trim($params['cancelled_reason'])) ? $params['cancelled_reason'] : '';
        $display_cancellation_reason_in_single_page = (isset($params['display_cancellation_reason_in_single_page']) and trim($params['display_cancellation_reason_in_single_page'])) ? $params['display_cancellation_reason_in_single_page'] : '';

        $hourly_schedules = (isset($params['hourly_schedules']) and is_array($params['hourly_schedules'])) ? $params['hourly_schedules'] : array();
        $fields_data = (isset($params['fields']) and is_array($params['fields'])) ? $params['fields'] : get_post_meta($post->ID, 'mec_fields', true);

        // Status of Speakers Feature
        $speakers_status = (!isset($this->settings['speakers_status']) or (isset($this->settings['speakers_status']) and !$this->settings['speakers_status'])) ? false : true;
        $speakers = get_terms('mec_speaker', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => '0',
        ));

        // Cost
        $type = ((isset($this->settings['single_cost_type']) and trim($this->settings['single_cost_type'])) ? $this->settings['single_cost_type'] : 'numeric');

        // Links
        $read_more = (isset($params['read_more']) ? esc_attr($params['read_more']) : '');
        $more_info = (isset($params['more_info']) ? esc_attr($params['more_info']) : '');
        $more_info_title = (isset($params['more_info_title']) ? esc_attr($params['more_info_title']) : '');
        $more_info_target = (isset($params['more_info_target']) ? esc_attr($params['more_info_target']) : '');

        // Locations
        $locations = get_terms('mec_location', array('orderby'=>'name', 'hide_empty'=>'0'));
        $location_id = (isset($params['location_id']) ? esc_attr($params['location_id']) : '');

        $dont_show_map = (isset($params['dont_show_map']) ? esc_attr($params['dont_show_map']) : '');

        // Organizers
        $organizers = get_terms('mec_organizer', array('orderby'=>'name', 'hide_empty'=>'0'));
        $organizer_id = (isset($params['organizer_id']) ? esc_attr($params['organizer_id']) : '');

        // Tickets
        $tickets = get_post_meta($event_id, 'mec_tickets', true);
        if(!is_array($tickets)) $tickets = array();

        $occ_tickets = (isset($params['tickets']) and is_array($params['tickets'])) ? $params['tickets'] : array();
        ?>
        <li class="mec_occurrences_item" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>">
            <h3><span class="mec-occurrences-delete-button" data-id="<?php echo esc_attr($occurrence_id); ?>"><?php esc_html_e('Delete', 'modern-events-calendar-lite'); ?></span><?php echo date_i18n($datetime_format, $data['occurrence']); ?></h3>
            <input type="hidden" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][id]" value="<?php esc_attr_e($occurrence_id); ?>">
            <div class="mec-form-row">
                <div class="mec-col-3"><label for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_bookings_limit"><?php esc_attr_e('Total Booking Limit', 'modern-events-calendar-lite'); ?></label></div>
                <div class="mec-col-9"><input id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_bookings_limit" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][bookings_limit]" type="number" value="<?php echo (isset($params['bookings_limit']) ? esc_attr($params['bookings_limit']) : ''); ?>"></div>
            </div>
            <div class="mec-form-row">
                <div class="mec-col-3"><label for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_title"><?php esc_attr_e('Page Title', 'modern-events-calendar-lite'); ?></label></div>
                <div class="mec-col-9"><input id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_title" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][title]" type="text" value="<?php echo (isset($params['title']) ? esc_attr($params['title']) : ''); ?>"></div>
            </div>
            <div class="mec-form-row">
                <div class="mec-col-12">
                    <div class="mec-form-row">
                        <label class="label-radio">
                            <input data-id="<?php echo esc_attr($occurrence_id); ?>" class="mec-schema-event-status" type="radio" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][event_status]" value="EventScheduled" <?php echo ($event_status == 'EventScheduled' ? 'checked' : ''); ?>>
                            <?php esc_html_e('Scheduled', 'modern-events-calendar-lite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('For active events!', 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <div class="mec-form-row">
                        <label class="label-radio">
                            <input data-id="<?php echo esc_attr($occurrence_id); ?>" class="mec-schema-event-status" type="radio" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][event_status]" value="EventPostponed" <?php echo ($event_status == 'EventPostponed' ? 'checked' : ''); ?>>
                            <?php esc_html_e('Postponed', 'modern-events-calendar-lite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('If you postponed an event then you can use this status!', 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <div class="mec-form-row">
                        <label class="label-radio">
                            <input data-id="<?php echo esc_attr($occurrence_id); ?>" class="mec-schema-event-status" type="radio" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][event_status]" value="EventCancelled" <?php echo ($event_status == 'EventCancelled' ? 'checked' : ''); ?>>
                            <?php esc_html_e('Cancelled', 'modern-events-calendar-lite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('If you cancelled an event then you should select this status!', 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <div id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_cancelled_reason_wrapper" class="event-status-schema" <?php echo ($event_status == 'EventCancelled' ? '' : 'style="display: none;"'); ?>>
                        <div class="mec-form-row">
                            <label class="mec-col-2" for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_cancelled_reason"><?php esc_html_e('Reason for Cancellation', 'modern-events-calendar-lite'); ?></label>
                            <input class="mec-col-9" type="text" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_cancelled_reason" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][cancelled_reason]" value="<?php echo esc_attr($cancelled_reason); ?>" placeholder="<?php esc_html_e('Please write your reasons here', 'modern-events-calendar-lite'); ?>">
                        </div>
                        <div>
                            <p class="description"><?php esc_html_e('This will be displayed in Single Event and Shortcode/Calendar Pages', 'modern-events-calendar-lite'); ?></p>
                        </div>
                        <div class="mec-form-row">
                            <input type="hidden" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][display_cancellation_reason_in_single_page]" value="0">
                            <input <?php if(isset($display_cancellation_reason_in_single_page) and $display_cancellation_reason_in_single_page == true) echo 'checked="checked"'; ?> type="checkbox" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][display_cancellation_reason_in_single_page]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_display_cancellation_reason_in_single_page" value="1">
                            <label for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_display_cancellation_reason_in_single_page"><?php esc_html_e('Display in single event page', 'modern-events-calendar-lite'); ?></label>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="label-radio">
                            <input data-id="<?php echo esc_attr($occurrence_id); ?>" class="mec-schema-event-status" type="radio" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][event_status]" value="EventMovedOnline" <?php echo ($event_status == 'EventMovedOnline' ? 'checked' : ''); ?>>
                            <?php esc_html_e('Moved Online', 'modern-events-calendar-lite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('For the events that moved online!', 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <div id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_moved_online_link_wrapper" class="event-status-schema" <?php echo ($event_status == 'EventMovedOnline' ? '' : 'style="display: none;"'); ?>>
                        <div class="mec-form-row">
                            <label class="mec-col-2" for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_moved_online_link"><?php esc_html_e('Online Link', 'modern-events-calendar-lite'); ?></label>
                            <input class="mec-col-9" type="url" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_moved_online_link" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][moved_online_link]" value="<?php echo esc_url($moved_online_link); ?>" placeholder="https://online-platform.com/event-id">
                        </div>
                        <div>
                            <p class="description"><?php esc_html_e('Link to join online event. If you leave it empty event link will be used.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mec-form-row">
                <div class="mec-col-12">
                    <?php
                        $hourly_schedule = $this->getHourlySchedule();
                        $hourly_schedule->form(array(
                            'hourly_schedules' => $hourly_schedules,
                            'speakers_status' => $speakers_status,
                            'speakers' => $speakers,
                            'wrapper_class' => '',
                            'prefix' => 'mec_occurrences_'.esc_attr($occurrence_id).'_',
                            'name_prefix' => 'mec[occurrences]['.esc_attr($occurrence_id).']',
                        ));
                    ?>
                </div>
            </div>
            <div class="mec-form-row">
                <div class="mec-col-12">
                    <?php
                        $fields = $this->getEventFields();
                        $fields->form(array(
                            'id' => 'mec_occurrences_event_fields_'.esc_attr($occurrence_id),
                            'class' => 'no',
                            'post' => $post,
                            'data' => $fields_data,
                            'id_prefix' => 'mec_occurrences_'.esc_attr($occurrence_id).'_',
                            'name_prefix' => 'mec[occurrences]['.esc_attr($occurrence_id).']',
                            'mandatory_status' => false,
                        ));
                    ?>
                </div>
            </div>
            <div class="mec-form-row">
                <div class="mec-col-12">
                    <h4><?php echo sprintf(esc_html__('Event Main %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite'))); ?></h4>
                    <div class="mec-form-row">
                        <select name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][location_id]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_location_id" title="<?php echo esc_attr__($this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite')), 'modern-events-calendar-lite'); ?>">
                            <option value="">-----</option>
                            <option value="1"><?php esc_html_e('Hide location', 'modern-events-calendar-lite'); ?></option>
                            <?php foreach($locations as $location): ?>
                            <option <?php if($location_id == $location->term_id) echo 'selected="selected"'; ?> value="<?php echo esc_attr($location->term_id); ?>"><?php echo esc_html($location->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="mec-tooltip">
                            <div class="box top">
                                <h5 class="title"><?php esc_html_e('Location', 'modern-events-calendar-lite'); ?></h5>
                                <div class="content"><p><?php esc_attr_e('Choose one of saved locations.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/location/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                            </div>
                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                        </span>
                    </div>

                    <?php if($this->getPRO()): ?>
                    <div class="mec-form-row">
                        <div class="mec-col-12">
                            <input type="hidden" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][dont_show_map]" value="0" />
                            <input type="checkbox" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_location_dont_show_map" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][dont_show_map]" value="1" <?php echo ($dont_show_map ? 'checked="checked"' : ''); ?> /><label for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_location_dont_show_map"><?php echo esc_html__("Don't show map in single event page", 'modern-events-calendar-lite'); ?></label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mec-form-row">
                <div class="mec-col-12">
                    <h4><?php echo sprintf(esc_html__('Event Main %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'))); ?></h4>
                    <div class="mec-form-row">
                        <select name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][organizer_id]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_organizer_id" title="<?php echo esc_attr__($this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite')), 'modern-events-calendar-lite'); ?>">
                            <option value="">-----</option>
                            <option value="1"><?php esc_html_e('Hide organizer', 'modern-events-calendar-lite'); ?></option>
                            <?php foreach($organizers as $organizer): ?>
                            <option <?php if($organizer_id == $organizer->term_id) echo 'selected="selected"'; ?> value="<?php echo esc_attr($organizer->term_id); ?>"><?php echo esc_html($organizer->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="mec-tooltip">
                            <div class="box top">
                                <h5 class="title"><?php esc_html_e('Organizer', 'modern-events-calendar-lite'); ?></h5>
                                <div class="content"><p><?php esc_attr_e('Choose one of saved organizers or insert new one below.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/organizer-and-other-organizer/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                            </div>
                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mec-form-row">
                <div class="mec-col-12">
                    <h4><?php echo esc_html($this->main->m('event_cost', esc_html__('Event Cost', 'modern-events-calendar-lite'))); ?></h4>
                    <div id="mec_meta_box_cost_form">
                        <div class="mec-form-row">
                            <input type="<?php echo ($type === 'alphabetic' ? 'text' : 'number'); ?>" <?php echo ($type === 'numeric' ? 'min="0" step="any"' : ''); ?> class="mec-col-3" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][cost]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_cost" value="<?php echo (isset($params['cost']) ? esc_attr($params['cost']) : ''); ?>" title="<?php esc_html_e('Cost', 'modern-events-calendar-lite'); ?>" placeholder="<?php esc_html_e('Cost', 'modern-events-calendar-lite'); ?>"/>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(count($tickets)): ?>
            <div class="mec-form-row">
                <div class="mec-col-12">
                    <h4><?php echo esc_html($this->main->m('tickets', esc_html__('Tickets', 'modern-events-calendar-lite'))); ?></h4>
                    <div id="mec_meta_box_ticket_form">
                        <?php foreach($tickets as $key => $ticket): if(!is_numeric($key)) continue; $occ_ticket = isset($occ_tickets[$key]) ? $occ_tickets[$key] : array(); ?>
                        <div class="mec-form-row">
                            <div class="mec-col-4">
                                <b><?php echo isset($ticket['name']) ? esc_attr($ticket['name']) : esc_html__('No Name', 'modern-events-calendar-lite'); ?></b>
                            </div>
                            <div class="mec-col-4">
                                <input type="number" min="0" step="0.01" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][tickets][<?php echo esc_attr($key); ?>][price]"
                                       placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo (isset($occ_ticket['price']) and trim($occ_ticket['price'])) ? esc_attr($occ_ticket['price']) : ''; ?>" />
                            </div>
                            <div class="mec-col-4">
                                <input type="text" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][tickets][<?php echo esc_attr($key); ?>][price_label]"
                                       placeholder="<?php esc_attr_e('Price Label', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo isset($occ_ticket['price_label']) ? esc_attr($occ_ticket['price_label']) : ''; ?>" />
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="mec-form-row">
                <div class="mec-col-12">
                    <h4><?php esc_html_e('Event Links', 'modern-events-calendar-lite'); ?></h4>
                    <div class="mec-form-row">
                        <label class="mec-col-2" for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_read_more_link"><?php echo esc_html($this->main->m('read_more_link', esc_html__('Event Link', 'modern-events-calendar-lite'))); ?></label>
                        <input class="mec-col-7" type="text" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][read_more]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_read_more_link" value="<?php echo esc_attr($read_more); ?>" placeholder="<?php esc_html_e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>"/>
                        <?php do_action('extra_event_link_occurrence', $post->ID, esc_attr($occurrence_id)); ?>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-2" for="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_more_info_link"><?php echo esc_html($this->main->m('more_info_link', esc_html__('More Info', 'modern-events-calendar-lite'))); ?></label>
                        <input class="mec-col-3" type="text" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][more_info]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_more_info_link" value="<?php echo esc_attr($more_info); ?>" placeholder="<?php esc_html_e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>"/>
                        <input class="mec-col-2" type="text" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][more_info_title]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_more_info_title" value="<?php echo esc_attr($more_info_title); ?>" placeholder="<?php esc_html_e('More Information', 'modern-events-calendar-lite'); ?>"/>
                        <select class="mec-col-2" name="mec[occurrences][<?php echo esc_attr($occurrence_id); ?>][more_info_target]" id="mec_occurrences_<?php echo esc_attr($occurrence_id); ?>_more_info_target">
                            <option value="_self" <?php echo($more_info_target == '_self' ? 'selected="selected"' : ''); ?>><?php esc_html_e('Current Window', 'modern-events-calendar-lite'); ?></option>
                            <option value="_blank" <?php echo($more_info_target == '_blank' ? 'selected="selected"' : ''); ?>><?php esc_html_e('New Window', 'modern-events-calendar-lite'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <?php do_action('mec_occurrences_fields', $occurrence_id, $event_id, $data); ?>
        </li>
        <?php
    }

    public function save($post_id, $data)
    {
        if(!isset($data['occurrences']) or (isset($data['occurrences']) and !is_array($data['occurrences']))) return;

        $occurrences = $data['occurrences'];
        do_action('mec_occurrences_save', $post_id, $occurrences);

        $organizer_ids = array();
        $location_ids = array();

        foreach($occurrences as $occurrence)
        {
            // Clean Hourly Schedules
            $raw_hourly_schedules = isset($occurrence['hourly_schedules']) ? $occurrence['hourly_schedules'] : array();
            if(isset($raw_hourly_schedules[':d:'])) unset($raw_hourly_schedules[':d:']);

            $hourly_schedules = array();
            foreach($raw_hourly_schedules as $raw_hourly_schedule)
            {
                if(isset($raw_hourly_schedule['schedules'][':i:'])) unset($raw_hourly_schedule['schedules'][':i:']);
                $hourly_schedules[] = $raw_hourly_schedule;
            }

            // Hourly Schedules
            $occurrence['hourly_schedules'] = $hourly_schedules;

            $location_id = (isset($occurrence['location_id']) ? $occurrence['location_id'] : '');
            if($location_id) $location_ids[] = $location_id;

            $organizer_id = (isset($occurrence['organizer_id']) ? $occurrence['organizer_id'] : '');
            if($organizer_id) $organizer_ids[] = $organizer_id;

            // Save Occurrence
            $this->db->q("UPDATE `#__mec_occurrences` SET `params`='".json_encode($occurrence, JSON_UNESCAPED_UNICODE)."' WHERE `id`='".$this->db->escape($occurrence['id'])."'");
        }

        $organizer_ids = array_unique($organizer_ids);
        foreach($organizer_ids as $organizer_id) wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer', true);

        $location_ids = array_unique($location_ids);
        foreach($location_ids as $location_id) wp_set_object_terms($post_id, (int) $location_id, 'mec_location', true);
    }

    public function get_dates($post_id, $start, $limit = 100)
    {
        return $this->db->select("SELECT `tstart`, `tend` FROM `#__mec_dates` WHERE `post_id`='".$this->db->escape($post_id)."' AND `tstart`>='".$this->db->escape($start)."' ORDER BY `tstart` ASC LIMIT ".$this->db->escape($limit));
    }

    public function get($occurrence_id)
    {
        $JSON = $this->db->select("SELECT `params` FROM `#__mec_occurrences` WHERE `id`='".$this->db->escape($occurrence_id)."'", 'loadResult');

        if(!trim($JSON)) return array();
        else
        {
            $params = json_decode($JSON, true);

            if(!is_array($params)) return array();
            else return $params;
        }
    }

    public function get_data($occurrence_id)
    {
        return $this->db->select("SELECT * FROM `#__mec_occurrences` WHERE `id`='".$this->db->escape($occurrence_id)."'", 'loadAssoc');
    }

    public function get_all_occurrences($post_id, $start = NULL)
    {
        return $this->db->select("SELECT * FROM `#__mec_occurrences` WHERE `post_id`='".$this->db->escape($post_id)."' ".($start ? "AND `occurrence`>='".$this->db->escape($start)."'" : '')." ORDER BY `occurrence` DESC LIMIT 200", 'loadAssocList');
    }

    public static function param($post_id, $timestamp, $key, $default = NULL)
    {
        // Main
        $main = new MEC_main();

        // Cache
        $cache = $main->getCache();

        // Get
        $params = $cache->rememberOnce('mec_occ_param_'.$post_id.'_'.$timestamp.'_'.$key, function() use($main, $post_id, $timestamp)
        {
            $db = $main->getDB();
            $JSON = $db->select("SELECT `params` FROM `#__mec_occurrences` WHERE `post_id`='".$db->escape($post_id)."' AND `occurrence`='".$db->escape($timestamp)."' ORDER BY `id` DESC LIMIT 1", 'loadResult');

            if(!trim($JSON)) $params = array();
            else $params = json_decode($JSON, true);

            return is_array($params) ? $params : array();
        });

        if($key == '*') return $params;
        elseif(isset($params[$key]) and !is_array($params[$key]) and trim($params[$key]) != '') return $params[$key];
        elseif(isset($params[$key]) and is_array($params[$key])) return $params[$key];
        else return $default;
    }

    public static function fetch($date_events = array())
    {
        $occurrences = array();
        $where = '';

        foreach($date_events as $date => $events)
        {
            foreach($events as $event)
            {
                $timestamp = (isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp']) and $event->date['start']['timestamp']) ? $event->date['start']['timestamp'] : NULL;
                if(!$timestamp) continue;

                $occurrences[] = array($event->ID, $timestamp);
                $where .= "(`post_id`=".esc_sql($event->ID)." AND `occurrence`=".esc_sql($timestamp).") OR ";
            }
        }

        // No Occurrences
        if(!count($occurrences)) return;

        // Add to Cache
        self::cache_fetched_occurrences($occurrences, $where);
    }

    public static function fetch_single($event, $dates)
    {
        $occurrences = array();
        $where = '';

        foreach($dates as $date)
        {
            $timestamp = (isset($date) and isset($date['start']) and isset($date['start']['timestamp']) and $date['start']['timestamp']) ? $date['start']['timestamp'] : NULL;
            if(!$timestamp) continue;

            $occurrences[] = array($event->ID, $timestamp);
            $where .= "(`post_id`=".esc_sql($event->ID)." AND `occurrence`=".esc_sql($timestamp).") OR ";
        }

        // No Occurrences
        if(!count($occurrences)) return;

        // Add to Cache
        self::cache_fetched_occurrences($occurrences, $where);
    }

    public static function cache_fetched_occurrences($occurrences, $where)
    {
        // Main
        $main = new MEC_main();

        // Db
        $db = $main->getDB();

        // Records
        $records = $db->select("SELECT `post_id`, `occurrence`, `params` FROM `#__mec_occurrences` WHERE ".rtrim($where, 'OR '), 'loadObjectList');

        // Mapped Records
        $mapped_records = array();

        foreach($records as $record)
        {
            $JSON = $record->params;

            if(!trim($JSON)) $params = array();
            else $params = json_decode($JSON, true);

            $mapped_records[$record->post_id.':'.$record->occurrence] = (is_array($params) ? $params : array());
        }

        // Cache
        $cache = $main->getCache();

        // Add to Cache
        foreach($occurrences as $occurrence)
        {
            $post_id = $occurrence[0];
            $timestamp = $occurrence[1];

            $value = (isset($mapped_records[$post_id.':'.$timestamp]) ? $mapped_records[$post_id.':'.$timestamp] : array());
            $cache->set('mec_occ_param_'.$post_id.'_'.$timestamp, $value);
        }
    }
}