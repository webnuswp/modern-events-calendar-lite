<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC LearnDash addon class
 * @author Webnus <info@webnus.net>
 */
class MEC_addon_learndash extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_main
     */
    public $main;
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // MEC Factory class
        $this->factory = $this->getFactory();
        
        // MEC Main class
        $this->main = $this->getMain();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize the LD addon
     * @author Webnus <info@webnus.net>
     * @return boolean
     */
    public function init()
    {
        // Module is not enabled
        if(!isset($this->settings['ld_status']) or (isset($this->settings['ld_status']) and !$this->settings['ld_status'])) return false;

        // Tickets
        add_action('custom_field_ticket', array($this, 'add_courses_dropdown_to_tickets'), 10, 2);
        add_action('custom_field_dynamic_ticket', array($this, 'add_courses_dropdown_to_raw_tickets'));

        // Enrollment Method
        $enroll_method = (isset($this->settings['ld_enrollment_method']) and trim($this->settings['ld_enrollment_method'])) ? $this->settings['ld_enrollment_method'] : 'booking';

        // Enroll
        if($enroll_method === 'booking') add_action('mec_booking_completed', array($this, 'assign'), 10, 1);
        elseif($enroll_method === 'confirm') add_action('mec_booking_confirmed', array($this, 'assign'), 10, 1);
        elseif($enroll_method === 'verification') add_action('mec_booking_verified', array($this, 'assign'), 10, 1);
        elseif($enroll_method === 'confirm_verification')
        {
            add_action('mec_booking_confirmed', array($this, 'pre_enroll'), 10, 1);
            add_action('mec_booking_verified', array($this, 'pre_enroll'), 10, 1);
        }

        return true;
    }

    public function add_courses_dropdown_to_tickets($ticket, $key)
    {
        // LearnDash is not installed
        if(!defined('LEARNDASH_VERSION')) return;

        $courses = $this->get_courses();
        if(!count($courses)) return;
        ?>
        <div class="mec-form-row">
            <label for="mec_tickets_<?php echo esc_attr($key); ?>_ld_course"><?php esc_html_e('LearnDash Course', 'modern-events-calendar-lite'); ?></label>
            <select name="mec[tickets][<?php echo esc_attr($key); ?>][ld_course]" id="mec_tickets_<?php echo esc_attr($key); ?>_ld_course">
                <option>-----</option>
                <?php foreach($courses as $course_id => $course_name): ?>
                <option value="<?php echo esc_attr($course_id); ?>"<?php echo ((isset($ticket['ld_course']) and $course_id == $ticket['ld_course']) ? 'selected="selected"' : ''); ?>><?php echo esc_html($course_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    public function add_courses_dropdown_to_raw_tickets()
    {
        // LearnDash is not installed
        if(!defined('LEARNDASH_VERSION')) return;

        $this->add_courses_dropdown_to_tickets(array(), ':i:');
    }

    public function get_courses()
    {
        $courses = [];

        $args = ['post_type' => 'sfwd-courses', 'posts_per_page' => -1];
        if(!current_user_can('manage_options') and isset($this->settings['ld_course_access']) and $this->settings['ld_course_access'] === 'user') $args['author'] = get_current_user_id();

        $posts = get_posts($args);
        if($posts) foreach($posts as $post) $courses[$post->ID] = $post->post_title;

        return $courses;
    }

    public function assign($book_id)
    {
        // LearnDash is not installed
        if(!defined('LEARNDASH_VERSION')) return;

        // MEC User
        $u = $this->getUser();

        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $ticket_ids = explode(',', get_post_meta($book_id, 'mec_ticket_id', true));

        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        if(!is_array($attendees)) $attendees = array();

        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        foreach($attendees as $key => $attendee)
        {
            if($key === 'attachments') continue;
            if(!isset($attendee['id'])) continue;

            $ticket_id = $attendee['id'];

            if(!is_numeric($ticket_id)) continue;
            if(!in_array($ticket_id, $ticket_ids)) continue;
            if(!isset($tickets[$ticket_id])) continue;

            $ticket = $tickets[$ticket_id];

            // Course ID
            $course_id = $ticket['ld_course'];

            // User ID
            $user_id = $u->register($attendee, [
                'event_id' => $event_id,
            ]);

            // Associate Course
            ld_update_course_access($user_id, $course_id, false);
        }
    }

    public function pre_enroll($booking_id)
    {
        $confirmed = get_post_meta($booking_id, 'mec_confirmed', true);
        $verified = get_post_meta($booking_id, 'mec_verified', true);

        if($confirmed == 1 and $verified == 1) $this->assign($booking_id);
    }
}