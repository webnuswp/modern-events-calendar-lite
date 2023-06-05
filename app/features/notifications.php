<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Notifications Per Event class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_notifications extends MEC_base
{
    public $factory;
    public $main;
    public $settings;
    public $notif_settings;

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

        // MEC Notification Settings
        $this->notif_settings = $this->main->get_notifications();
    }

    /**
     * Initialize notifications feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Module is disabled
        if(!isset($this->settings['notif_per_event']) or (isset($this->settings['notif_per_event']) and !$this->settings['notif_per_event'])) return;

        $this->factory->action('mec_metabox_details', array($this, 'meta_box_notifications'), 30);
    }

    /**
     * Show notification meta box
     * @author Webnus <info@webnus.net>
     * @param object $post
     */
    public function meta_box_notifications($post)
    {
        $values = get_post_meta($post->ID, 'mec_notifications', true);
        if(!is_array($values)) $values = array();

        $notifications = $this->get_notifications();
    ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-notifications">
            <?php foreach($notifications as $key => $notification): if(isset($this->notif_settings[$key]) and isset($this->notif_settings[$key]['status']) and !$this->notif_settings[$key]['status']) continue; ?>
			<div class="mec-form-row">
                <h4><?php echo esc_html($notification['label']); ?></h4>
                <div class="mec-form-row">
                    <label>
                        <input type="hidden" name="mec[notifications][<?php echo esc_attr($key); ?>][status]" value="0" />
                        <input onchange="jQuery('#mec_notification_<?php echo esc_attr($key); ?>_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][<?php echo esc_attr($key); ?>][status]" <?php if(isset($values[$key]) and isset($values[$key]['status']) and $values[$key]['status']) echo 'checked="checked"'; ?> /> <?php echo esc_html__("Modify", 'modern-events-calendar-lite' ); ?>
                    </label>
                </div>
                <div id="mec_notification_<?php echo esc_attr($key); ?>_container_toggle" class="<?php if(!isset($values[$key]) or (isset($values[$key]) and !$values[$key]['status'])) echo 'mec-util-hidden'; ?>">
                    <div class="mec-form-row">
                        <div class="mec-col-2">
                            <label for="mec_notifications_<?php echo esc_attr($key); ?>_subject"><?php esc_html_e('Email Subject', 'modern-events-calendar-lite' ); ?></label>
                        </div>
                        <div class="mec-col-10">
                            <input id="mec_notifications_<?php echo esc_attr($key); ?>_subject" type="text" name="mec[notifications][<?php echo esc_attr($key); ?>][subject]" value="<?php echo ((isset($values[$key]) and isset($values[$key]['subject']) and trim($values[$key]['subject'])) ? $values[$key]['subject'] : ((isset($this->notif_settings[$key]) and isset($this->notif_settings[$key]['subject']) and trim($this->notif_settings[$key]['subject'])) ? $this->notif_settings[$key]['subject'] : '')); ?>">
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <div class="mec-col-2">
                            <label for="mec_notifications_<?php echo esc_attr($key); ?>_content"><?php esc_html_e('Email Content', 'modern-events-calendar-lite' ); ?></label>
                        </div>
                        <div class="mec-col-10">
                            <?php wp_editor(((isset($values[$key]) and isset($values[$key]['content']) and trim($values[$key]['content'])) ? stripslashes($values[$key]['content']) : ((isset($this->notif_settings[$key]) and isset($this->notif_settings[$key]['content']) and trim($this->notif_settings[$key]['content'])) ? stripslashes($this->notif_settings[$key]['content']) : '')), 'mec_notifications_'.esc_attr($key).'_content', array('textarea_name'=>'mec[notifications]['.$key.'][content]')); ?>
                        </div>
                    </div>

                    <?php
                        $section = $key;
                        $options = $values;
                        do_action('mec_display_notification_settings_for_event',$values,$section)
                    ?>
                </div>
			</div>
            <?php endforeach; ?>
            <h4><?php echo esc_html__('Placeholders', 'modern-events-calendar-lite' ); ?></h4>
            <?php $this->display_placeholders(); ?>
		</div>
    <?php
    }

    public function get_notifications()
    {
        $notifications = array(
            'booking_notification' => array(
                'label' => esc_html__('Booking Notification', 'modern-events-calendar-lite' )
            ),
            'booking_confirmation' => array(
                'label' => esc_html__('Booking Confirmation', 'modern-events-calendar-lite' )
            ),
            'booking_rejection' => array(
                'label' => esc_html__('Booking Rejection', 'modern-events-calendar-lite' )
            ),
            'email_verification' => array(
                'label' => esc_html__('Email Verification', 'modern-events-calendar-lite' )
            ),
            'cancellation_notification' => array(
                'label' => esc_html__('Booking Cancellation', 'modern-events-calendar-lite' )
            ),
            'booking_reminder' => array(
                'label' => esc_html__('Booking Reminder', 'modern-events-calendar-lite' )
            ),
            'event_finished' => array(
                'label' => esc_html__('Event Finished', 'modern-events-calendar-lite' )
            ),
            'event_soldout' => array(
                'label' => esc_html__('Event Soldout', 'modern-events-calendar-lite' )
            ),
            'admin_notification' => array(
                'label' => esc_html__('Admin Notification', 'modern-events-calendar-lite' )
            ),
        );

        return apply_filters('mec_event_notifications', $notifications);
    }

    public static function display_placeholders()
    {
        ?>
        <ul>
            <li><span>%%name%%</span>: <?php esc_html_e('Full name of attendee', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%first_name%%</span>: <?php esc_html_e('First name of attendee', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%last_name%%</span>: <?php esc_html_e('Last name of attendee', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%user_email%%</span>: <?php esc_html_e('Email of attendee', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_date%%</span>: <?php esc_html_e('Booked date of event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_time%%</span>: <?php esc_html_e('Booked time of event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_datetime%%</span>: <?php esc_html_e('Booked date and time of event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_other_datetimes%%</span>: <?php esc_html_e('Other date and times of booking for multiple date booking system', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_date_next_occurrences%%</span>: <?php esc_html_e('Date of next 20 occurrences of booked event (including the booked date)', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_datetime_next_occurrences%%</span>: <?php esc_html_e('Date and Time of next 20 occurrences of booked event (including the booked date)', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_price%%</span>: <?php esc_html_e('Booking Price', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_payable%%</span>: <?php esc_html_e('Booking Payable', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%attendee_price%%</span>: <?php esc_html_e('Attendee Price (for booking confirmation notification)', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%book_order_time%%</span>: <?php esc_html_e('Date and time of booking', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%blog_name%%</span>: <?php esc_html_e('Your website title', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%blog_url%%</span>: <?php esc_html_e('Your website URL', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%blog_description%%</span>: <?php esc_html_e('Your website description', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_title%%</span>: <?php esc_html_e('Event title', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_description%%</span>: <?php esc_html_e('Event Description', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_tags%%</span>: <?php esc_html_e('Event Tags', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_labels%%</span>: <?php esc_html_e('Event Labels', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_categories%%</span>: <?php esc_html_e('Event Categories', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_cost%%</span>: <?php esc_html_e('Event Cost', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_link%%</span>: <?php esc_html_e('Event link', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_speaker_name%%</span>: <?php esc_html_e('Speaker name of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_organizer_name%%</span>: <?php esc_html_e('Organizer name of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_organizer_tel%%</span>: <?php esc_html_e('Organizer tel of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_organizer_email%%</span>: <?php esc_html_e('Organizer email of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_organizer_url%%</span>: <?php esc_html_e('Organizer url of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_other_organizers_name%%</span>: <?php esc_html_e('Additional organizers name of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_other_organizers_tel%%</span>: <?php esc_html_e('Additional organizers tel of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_other_organizers_email%%</span>: <?php esc_html_e('Additional organizers email of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_location_name%%</span>: <?php esc_html_e('Location name of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_location_address%%</span>: <?php esc_html_e('Location address of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_other_locations_name%%</span>: <?php esc_html_e('Additional locations name of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_other_locations_address%%</span>: <?php esc_html_e('Additional locations address of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_featured_image%%</span>: <?php esc_html_e('Featured image of booked event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_more_info%%</span>: <?php esc_html_e('Event link', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_other_info%%</span>: <?php esc_html_e('Event more info link', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%online_link%%</span>: <?php esc_html_e('Event online link', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%attendees_full_info%%</span>: <?php esc_html_e('Full Attendee info such as booking form data, name, email etc.', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%booking_id%%</span>: <?php esc_html_e('Booking ID', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%booking_transaction_id%%</span>: <?php esc_html_e('Transaction ID of Booking', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%admin_link%%</span>: <?php esc_html_e('Admin booking management link.', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%total_attendees%%</span>: <?php esc_html_e('Total attendees of current booking', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%amount_tickets%%</span>: <?php esc_html_e('Amount of Booked Tickets (Total attendees of all bookings)', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%ticket_name%%</span>: <?php esc_html_e('Ticket name', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%ticket_time%%</span>: <?php esc_html_e('Ticket time', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%ticket_name_time%%</span>: <?php esc_html_e('Ticket name & time', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%ticket_private_description%%</span>: <?php esc_html_e('Ticket private description', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%payment_gateway%%</span>: <?php esc_html_e('Payment Gateway', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%dl_file%%</span>: <?php esc_html_e('Link to the downloadable file', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%google_calendar_link%%</span>: <?php esc_html_e('Add to Google Calendar', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%google_calendar_link_next_occurrences%%</span>: <?php esc_html_e('Add to Google Calendar Links for next 20 occurrences', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_start_date%%</span>: <?php esc_html_e('Event Start Date', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_end_date%%</span>: <?php esc_html_e('Event End Date', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_start_time%%</span>: <?php esc_html_e('Event Start Time', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_end_time%%</span>: <?php esc_html_e('Event End Time', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_timezone%%</span>: <?php esc_html_e('Event Timezone', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_start_date_local%%</span>: <?php esc_html_e('Event Local Start Date', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_end_date_local%%</span>: <?php esc_html_e('Event Local End Date', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_start_time_local%%</span>: <?php esc_html_e('Event Local Start Time', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_end_time_local%%</span>: <?php esc_html_e('Event Local End Time', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_status%%</span>: <?php esc_html_e('Status of event', 'modern-events-calendar-lite' ); ?></li>
            <li><span>%%event_note%%</span>: <?php esc_html_e('Event Note', 'modern-events-calendar-lite' ); ?></li>
            <?php do_action('mec_extra_field_notifications'); ?>
        </ul>
        <?php
    }
}