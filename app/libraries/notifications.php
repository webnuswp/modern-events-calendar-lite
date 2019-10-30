<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC notifications class
 * @author Webnus <info@webnus.biz>
 */
class MEC_notifications extends MEC_base
{
    public $main;
    public $PT;
    public $notif_settings;
    public $settings;
    public $book;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();
        
        // MEC Book Post Type Name
        $this->PT = $this->main->get_book_post_type();
        
        // MEC Notification Settings
        $this->notif_settings = $this->main->get_notifications();
        
        // MEC Settings
        $this->settings = $this->main->get_settings();

        // MEC Book
        $this->book = $this->getBook();
    }
    
    /**
     * Send email verification notification
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function email_verification($book_id)
    {
        $booker_id = get_post_field('post_author', $book_id);
        $booker = get_userdata($booker_id);
        
        if(!isset($booker->user_email)) return false;
        
        $price = get_post_meta($book_id, 'mec_price', true);
        
        // Auto verification for free bookings is enabled so don't send the verification email
        if($price <= 0 and isset($this->settings['booking_auto_verify_free']) and $this->settings['booking_auto_verify_free'] == 1) return false;
        
        // Auto verification for paid bookings is enabled so don't send the verification email
        if($price > 0 and isset($this->settings['booking_auto_verify_paid']) and $this->settings['booking_auto_verify_paid'] == 1) return false;
        
        $subject = isset($this->notif_settings['email_verification']['subject']) ? $this->content(__($this->notif_settings['email_verification']['subject'], 'modern-events-calendar-lite'), $book_id) : __('Please verify your email.', 'modern-events-calendar-lite');
        $headers = array();
        
        $recipients_str = isset($this->notif_settings['email_verification']['recipients']) ? $this->notif_settings['email_verification']['recipients'] : '';
        $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();
        
        foreach($recipients as $recipient)
        {
            // Skip if it's not a valid email
            if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
            
            $headers[] = 'BCC: '.$recipient;
        }
        
        // Attendees
        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($book_id, 'mec_attendee', true));
         
        // Do not send email twice!
        $done_emails = array();
 
        // Book Data
        $key = get_post_meta($book_id, 'mec_verification_key', true);
        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $link = trim(get_permalink($event_id), '/').'/verify/'.$key.'/';

        // Set Email Type to HTML
        add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
         
        // Send the emails
        foreach($attendees as $attendee)
        {
            $to = isset($attendee['email']) ? $attendee['email'] : '';
            if(!trim($to) or in_array($to, $done_emails) or !filter_var($to, FILTER_VALIDATE_EMAIL)) continue;

            $message = isset($this->notif_settings['email_verification']['content']) ? $this->content($this->notif_settings['email_verification']['content'], $book_id, $attendee) : '';
            $message = str_replace('%%verification_link%%', $link, $message);
            $message = str_replace('%%link%%', $link, $message);

            // Filter the email
            $mail_arg = array(
                'to'            => $to,
                'subject'       => $subject,
                'message'       => $message,
                'headers'       => $headers,
                'attachments'   => array(),
            );

            $mail_arg = apply_filters('mec_before_send_email_verification', $mail_arg, $book_id, 'email_verification');

            // Send the mail
            wp_mail($mail_arg['to'], html_entity_decode(stripslashes($mail_arg['subject']), ENT_HTML5), wpautop(stripslashes($mail_arg['message'])), $mail_arg['headers'], $mail_arg['attachments']);
        
            // For prevention of email repeat send
            $done_emails[] = $to;
        }
        
        // Remove the HTML Email filter
        remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));

        return true;
    }
    
    /**
     * Send booking notification
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function booking_notification($book_id)
    {
        $booker_id = get_post_field('post_author', $book_id);
        $booker = get_userdata($booker_id);
        
        if(!isset($booker->user_email)) return false;

        // Booking Notification is disabled
        if(isset($this->notif_settings['booking_notification']['status']) and !$this->notif_settings['booking_notification']['status']) return false;
        
        $subject = isset($this->notif_settings['booking_notification']['subject']) ? $this->content(__($this->notif_settings['booking_notification']['subject'], 'modern-events-calendar-lite'), $book_id) : __('Your booking is received.', 'modern-events-calendar-lite');
        $headers = array();

        $recipients_str = isset($this->notif_settings['booking_notification']['recipients']) ? $this->notif_settings['booking_notification']['recipients'] : '';
        $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();
        
        foreach($recipients as $recipient)
        {
            // Skip if it's not a valid email
            if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
            
            $headers[] = 'BCC: '.$recipient;
        }

        // Send the notification to event organizer
        if(isset($this->notif_settings['booking_notification']['send_to_organizer']) and $this->notif_settings['booking_notification']['send_to_organizer'] == 1)
        {
            $organizer_email = $this->get_booking_organizer_email($book_id);
            if($organizer_email !== false) $headers[] = 'BCC: '.trim($organizer_email);
        }
        
        // Attendees
        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($book_id, 'mec_attendee', true));

        // Do not send email twice!
        $done_emails = array();

        // Set Email Type to HTML
        add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
        
        // Send the emails
        foreach($attendees as $attendee)
        {       
            $to = isset($attendee['email']) ? $attendee['email'] : '';
            if(!trim($to) or in_array($to, $done_emails) or !filter_var($to, FILTER_VALIDATE_EMAIL)) continue;

            $message = isset($this->notif_settings['booking_notification']['content']) ? $this->content($this->notif_settings['booking_notification']['content'], $book_id, $attendee) : '';
            
            // Attendee Full Information
            if(strpos($message, '%%attendee_full_info%%') !== false or strpos($message, '%%attendees_full_info%%') !== false)
            {
                $attendees_full_info = $this->get_full_attendees_info($book_id);

                $message = str_replace('%%attendee_full_info%%', $attendees_full_info, $message);
                $message = str_replace('%%attendees_full_info%%', $attendees_full_info, $message);
            }

            // Filter the email
            $mail_arg = array(
                'to'            => $to,
                'subject'       => $subject,
                'message'       => $message,
                'headers'       => $headers,
                'attachments'   => array(),
            );

            $mail_arg = apply_filters( 'mec_before_send_booking_notification', $mail_arg, $book_id, 'booking_notification');

            // Send the mail
            wp_mail($mail_arg['to'], html_entity_decode(stripslashes($mail_arg['subject']), ENT_HTML5), wpautop(stripslashes($mail_arg['message'])), $mail_arg['headers'], $mail_arg['attachments']);
            
            // For prevention of email repeat send
            $done_emails[] = $to;
        }
        
        // Remove the HTML Email filter
        remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
        
        return true;
    }
    
    /**
     * Send booking confirmation notification
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function booking_confirmation($book_id)
    {
        $booker_id = get_post_field('post_author', $book_id);
        $booker = get_userdata($booker_id);
        
        if(!isset($booker->user_email)) return false;
        
        $subject = isset($this->notif_settings['booking_confirmation']['subject']) ? $this->content(__($this->notif_settings['booking_confirmation']['subject'], 'modern-events-calendar-lite'), $book_id) : __('Your booking is confirmed.', 'modern-events-calendar-lite');
        $headers = array();
        
        $recipients_str = isset($this->notif_settings['booking_confirmation']['recipients']) ? $this->notif_settings['booking_confirmation']['recipients'] : '';
        $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();
        
        foreach($recipients as $recipient)
        {
            // Skip if it's not a valid email
            if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
            
            $headers[] = 'BCC: '.$recipient;
        }
        
        // Attendees
        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($book_id, 'mec_attendee', true));

        // Do not send email twice!
        $done_emails = array();

        // Set Email Type to HTML
        add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));

        // Send the emails
        foreach($attendees as $attendee)
        {
            $to = isset($attendee['email']) ? $attendee['email'] : '';
            if(!trim($to) or in_array($to, $done_emails) or !filter_var($to, FILTER_VALIDATE_EMAIL)) continue;

            $message = isset($this->notif_settings['booking_confirmation']['content']) ? $this->content($this->notif_settings['booking_confirmation']['content'], $book_id, $attendee) : '';

            // Filter the email
            $mail_arg = array(
                'to'            => $to,
                'subject'       => $subject,
                'message'       => $message,
                'headers'       => $headers,
                'attachments'   => array(),
            );

            $mail_arg = apply_filters( 'mec_before_send_booking_confirmation', $mail_arg, $book_id, 'booking_confirmation');

            // Send the mail
            wp_mail($mail_arg['to'], html_entity_decode(stripslashes($mail_arg['subject']), ENT_HTML5), wpautop(stripslashes($mail_arg['message'])), $mail_arg['headers'], $mail_arg['attachments']);
        
            // For prevention of email repeat send
            $done_emails[] = $to;
        }
        
        // Remove the HTML Email filter
        remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));

        return true;
    }
    
      /**
     * Send booking cancellation
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return void
     */
    public function booking_cancellation($book_id)
    {
        $booker_id = get_post_field('post_author', $book_id);
        $booker = get_userdata($booker_id);
        
        // Cancelling Notification is disabled
        if(!isset($this->notif_settings['cancellation_notification']['status']) or isset($this->notif_settings['cancellation_notification']['status']) and !$this->notif_settings['cancellation_notification']['status']) return;

        $tos = array();

        // Send the notification to admin
        if(isset($this->notif_settings['cancellation_notification']['send_to_admin']) and $this->notif_settings['cancellation_notification']['send_to_admin'] == 1)
        {
            $tos[] = get_bloginfo('admin_email');
        }

        // Send the notification to event organizer
        if(isset($this->notif_settings['cancellation_notification']['send_to_organizer']) and $this->notif_settings['cancellation_notification']['send_to_organizer'] == 1)
        {
            $organizer_email = $this->get_booking_organizer_email($book_id);
            if($organizer_email !== false) $tos[] = trim($organizer_email);
        }

        // Send the notification to event user
        if(isset($this->notif_settings['cancellation_notification']['send_to_user']) and $this->notif_settings['cancellation_notification']['send_to_user'] == 1)
        {
            if(isset($booker->user_email) and $booker->user_email)
            {
                // Attendees
                $attendees = get_post_meta($book_id, 'mec_attendees', true);
                if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($book_id, 'mec_attendee', true));

                // For When sended email time, And  prevention of email repeat send
                $done_emails = array();

                // Send the emails
                foreach($attendees as $attendee)
                {
                    if(isset($attendee['email']) and !in_array($attendee['email'], $done_emails))
                    {
                        $tos[] = $attendee;
                        $done_emails[] = $attendee['email'];
                    }
                }
            }
        }

        // No Recipient
        if(!count($tos)) return;

        $headers = array();
        
        $recipients_str = isset($this->notif_settings['cancellation_notification']['recipients']) ? $this->notif_settings['cancellation_notification']['recipients'] : '';
        $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();
        
        foreach($recipients as $recipient)
        {
            // Skip if it's not a valid email
            if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
            
            $headers[] = 'BCC: '.$recipient;
        }

        $subject = isset($this->notif_settings['cancellation_notification']['subject']) ? $this->content(__($this->notif_settings['cancellation_notification']['subject'], 'modern-events-calendar-lite'), $book_id) : __('booking canceled.', 'modern-events-calendar-lite');

        // Set Email Type to HTML
        add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
        
        // Send the mail
        $i = 1;
        foreach($tos as $to)
        {
            $mailto = (is_array($to) and isset($to['email'])) ? $to['email'] : $to;

            if(!trim($mailto) or !filter_var($mailto, FILTER_VALIDATE_EMAIL)) continue;
            if($i > 1) $headers = array();

            $message = isset($this->notif_settings['cancellation_notification']['content']) ? $this->content($this->notif_settings['cancellation_notification']['content'], $book_id, (is_array($to) ? $to : NULL)) : '';
        
            // Book Data
            $message = str_replace('%%admin_link%%', $this->link(array('post_type'=>$this->main->get_book_post_type()), $this->main->URL('admin').'edit.php'), $message);

            // Attendee Full Information
            if(strpos($message, '%%attendee_full_info%%') !== false or strpos($message, '%%attendees_full_info%%') !== false)
            {
                $attendees_full_info = $this->get_full_attendees_info($book_id);

                $message = str_replace('%%attendee_full_info%%', $attendees_full_info, $message);
                $message = str_replace('%%attendees_full_info%%', $attendees_full_info, $message);
            }

            // Filter the email
            $mail_arg = array(
                'to'            => $mailto,
                'subject'       => $subject,
                'message'       => $message,
                'headers'       => $headers,
                'attachments'   => array(),
            );

            $mail_arg = apply_filters( 'mec_before_send_booking_cancellation', $mail_arg, $book_id, 'booking_cancellation');
            
            // Send the mail
            wp_mail($mail_arg['to'], html_entity_decode(stripslashes($mail_arg['subject']), ENT_HTML5), wpautop(stripslashes($mail_arg['message'])), $mail_arg['headers'], $mail_arg['attachments']);

            $i++;
        }

        // Remove the HTML Email filter
        remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
    }

    /**
     * Send admin notification
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return void
     */
    public function admin_notification($book_id)
    {
        // Admin Notification is disabled
        if(isset($this->notif_settings['admin_notification']['status']) and !$this->notif_settings['admin_notification']['status']) return;

        $to = get_bloginfo('admin_email');
        $subject = isset($this->notif_settings['admin_notification']['subject']) ? $this->content(__($this->notif_settings['admin_notification']['subject'], 'modern-events-calendar-lite'), $book_id) : __('A new booking is received.', 'modern-events-calendar-lite');
        $headers = array();
        
        $recipients_str = isset($this->notif_settings['admin_notification']['recipients']) ? $this->notif_settings['admin_notification']['recipients'] : '';
        $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();
        
        foreach($recipients as $recipient)
        {
            // Skip if it's not a valid email
            if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
            
            $headers[] = 'CC: '.$recipient;
        }
        
        // Send the notification to event organizer
        if(isset($this->notif_settings['admin_notification']['send_to_organizer']) and $this->notif_settings['admin_notification']['send_to_organizer'] == 1)
        {
            $organizer_email = $this->get_booking_organizer_email($book_id);
            if($organizer_email !== false) $headers[] = 'CC: '.trim($organizer_email);
        }
        
        $message = isset($this->notif_settings['admin_notification']['content']) ? $this->content($this->notif_settings['admin_notification']['content'], $book_id) : '';
        
        // Book Data
        $message = str_replace('%%admin_link%%', $this->link(array('post_type'=>$this->main->get_book_post_type()), $this->main->URL('admin').'edit.php'), $message);

        // Attendee Full Information
        if(strpos($message, '%%attendee_full_info%%') !== false or strpos($message, '%%attendees_full_info%%') !== false)
        {
            $attendees_full_info = $this->get_full_attendees_info($book_id);

            $message = str_replace('%%attendee_full_info%%', $attendees_full_info, $message);
            $message = str_replace('%%attendees_full_info%%', $attendees_full_info, $message);
        }

        // Set Email Type to HTML
        add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
        
        // Filter the email
        $mail_arg = array(
            'to'            => $to,
            'subject'       => $subject,
            'message'       => $message,
            'headers'       => $headers,
            'attachments'   => array(),
        );
        $mail_arg = apply_filters( 'mec_before_send_admin_notification', $mail_arg, $book_id, 'admin_notification');

        // Send the mail
        wp_mail($mail_arg['to'], html_entity_decode(stripslashes($mail_arg['subject']), ENT_HTML5), wpautop(stripslashes($mail_arg['message'])), $mail_arg['headers'], $mail_arg['attachments']);
        
        // Remove the HTML Email filter
        remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
    }

    /**
     * Send booking reminder notification
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return boolean
     */
    public function booking_reminder($book_id)
    {
        $booker_id = get_post_field('post_author', $book_id);
        $booker = get_userdata($booker_id);

        if(!isset($booker->user_email)) return false;

        $subject = isset($this->notif_settings['booking_reminder']['subject']) ? $this->content(__($this->notif_settings['booking_reminder']['subject'], 'modern-events-calendar-lite'), $book_id) : __('Booking Reminder', 'modern-events-calendar-lite');
        $headers = array();

        $recipients_str = isset($this->notif_settings['booking_reminder']['recipients']) ? $this->notif_settings['booking_reminder']['recipients'] : '';
        $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();

        foreach($recipients as $recipient)
        {
            // Skip if it's not a valid email
            if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;

            $headers[] = 'BCC: '.$recipient;
        }

        // Attendees
        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($book_id, 'mec_attendee', true));

        // Set Email Type to HTML
        add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));

        // Send the emails
        foreach($attendees as $attendee)
        {
            if(isset($attendee[0]['MEC_TYPE_OF_DATA'])) continue;

            $to = $attendee['email'];
            $message = isset($this->notif_settings['booking_reminder']['content']) ? $this->content($this->notif_settings['booking_reminder']['content'], $book_id, $attendee) : '';

            if(!trim($to)) continue;

            // Filter the email
            $mail_arg = array(
                'to'            => $to,
                'subject'       => $subject,
                'message'       => $message,
                'headers'       => $headers,
                'attachments'   => array(),
            );

            $mail_arg = apply_filters('mec_before_send_booking_reminder', $mail_arg, $book_id, 'booking_reminder');

            // Send the mail
            wp_mail($mail_arg['to'], html_entity_decode(stripslashes($mail_arg['subject']), ENT_HTML5), wpautop(stripslashes($mail_arg['message'])), $mail_arg['headers'], $mail_arg['attachments']);

        }

        // Remove the HTML Email filter
        remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));

        return true;
    }
    
    /**
     * Send new event notification
     * @author Webnus <info@webnus.biz>
     * @param int $event_id
     * @param boolean $update
     * @return boolean
     */
    public function new_event($event_id, $update = false)
    {
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) return false;
        
        // MEC Event Post Type
        $event_PT = $this->main->get_main_post_type();
        
        // If it's not a MEC Event
        if(get_post_type($event_id) != $event_PT) return false;
        
        // If it's an update request, then don't send any notification
        if($update) return false;
        
        // New event notification is disabled
        if(!isset($this->notif_settings['new_event']['status']) or (isset($this->notif_settings['new_event']['status']) and !$this->notif_settings['new_event']['status'])) return false;
        
        $status = get_post_status($event_id);

        // Don't send the email if it's auto draft post
        if($status == 'auto-draft') return false;
        
        $to = get_bloginfo('admin_email');
        $subject = (isset($this->notif_settings['new_event']['subject']) and trim($this->notif_settings['new_event']['subject'])) ? __($this->notif_settings['new_event']['subject'], 'modern-events-calendar-lite') : __('A new event is added.', 'modern-events-calendar-lite');
        $headers = array();
        
        $recipients_str = isset($this->notif_settings['new_event']['recipients']) ? $this->notif_settings['new_event']['recipients'] : '';
        $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();
        
        foreach($recipients as $recipient)
        {
            // Skip if it's not a valid email
            if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
            
            $headers[] = 'CC: '.$recipient;
        }
        
        $message = (isset($this->notif_settings['new_event']['content']) and trim($this->notif_settings['new_event']['content'])) ? $this->notif_settings['new_event']['content'] : '';
        
        // Site Data
        $message = str_replace('%%blog_name%%', get_bloginfo('name'), $message);
        $message = str_replace('%%blog_url%%', get_bloginfo('url'), $message);
        $message = str_replace('%%blog_description%%', get_bloginfo('description'), $message);
        
        // Event Data
        $message = str_replace('%%admin_link%%', $this->link(array('post_type'=>$event_PT), $this->main->URL('admin').'edit.php'), $message);
        $message = str_replace('%%event_title%%', get_the_title($event_id), $message);
        $message = str_replace('%%event_link%%', get_post_permalink($event_id), $message);
        $message = str_replace('%%event_status%%', $status, $message);
        $message = str_replace('%%event_note%%', get_post_meta($event_id, 'mec_note', true), $message);
        
        // Notification Subject
        $subject = str_replace('%%event_title%%', get_the_title($event_id), $subject);
        
        // Set Email Type to HTML
        add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
        
        // Send the mail
        wp_mail($to, html_entity_decode(stripslashes($subject), ENT_HTML5), wpautop(stripslashes($message)), $headers);
        
        // Remove the HTML Email filter
        remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));

        return true;
    }
    
    /**
    * Send new event published notification
    * @author Webnus <info@webnus.biz>
    * @param string $new
    * @param string $old
    * @param object $post
    * @return void
    */
    public function user_event_publishing($new, $old, $post)
    {
        // MEC Event Post Type
        $event_PT = $this->main->get_main_post_type();
        
        // User event publishing notification is disabled
        if(!isset($this->notif_settings['user_event_publishing']['status']) or (isset($this->notif_settings['user_event_publishing']['status']) and !$this->notif_settings['user_event_publishing']['status'])) return false;
        
        if(($new == 'publish') and ($old != 'publish') and ($post->post_type == $event_PT))
        {
            $guest_email = get_post_meta($post->ID, 'fes_guest_email', true);

            // Not Set Guest User Email
            if(!trim($guest_email) or !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) return;

            $guest_name = get_post_meta($post->ID, 'fes_guest_name', true);
            $status = get_post_status($post->ID);
            
            $to = $guest_email;
            $subject = (isset($this->notif_settings['user_event_publishing']['subject']) and trim($this->notif_settings['user_event_publishing']['subject'])) ? __($this->notif_settings['user_event_publishing']['subject'], 'modern-events-calendar-lite') : __('Your event is published.', 'modern-events-calendar-lite');
            $headers = array();
            
            $recipients_str = isset($this->notif_settings['user_event_publishing']['recipients']) ? $this->notif_settings['user_event_publishing']['recipients'] : '';
            $recipients = trim($recipients_str) ? explode(',', $recipients_str) : array();
            
            foreach($recipients as $recipient)
            {
                // Skip if it's not a valid email
                if(trim($recipient) == '' or !filter_var($recipient, FILTER_VALIDATE_EMAIL)) continue;
                
                $headers[] = 'CC: '.$recipient;
            }
            
            $message = (isset($this->notif_settings['user_event_publishing']['content']) and trim($this->notif_settings['user_event_publishing']['content'])) ? $this->notif_settings['user_event_publishing']['content'] : '';
            
            // User Data
            $message = str_replace('%%name%%', $guest_name, $message);

            // Site Data
            $message = str_replace('%%blog_name%%', get_bloginfo('name'), $message);
            $message = str_replace('%%blog_url%%', get_bloginfo('url'), $message);
            $message = str_replace('%%blog_description%%', get_bloginfo('description'), $message);
            
            // Event Data
            $message = str_replace('%%admin_link%%', $this->link(array('post_type'=>$event_PT), $this->main->URL('admin').'edit.php'), $message);
            $message = str_replace('%%event_title%%', get_the_title($post->ID), $message);
            $message = str_replace('%%event_link%%', get_post_permalink($post->ID), $message);
            $message = str_replace('%%event_status%%', $status, $message);
            $message = str_replace('%%event_note%%', get_post_meta($post->ID, 'mec_note', true), $message);
            
            // Notification Subject
            $subject = str_replace('%%event_title%%', get_the_title($post->ID), $subject);
            
            // Set Email Type to HTML
            add_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
            
            // Send the mail
            wp_mail($to, html_entity_decode(stripslashes($subject), ENT_HTML5), wpautop(stripslashes($message)), $headers);
            
            // Remove the HTML Email filter
            remove_filter('wp_mail_content_type', array($this->main, 'html_email_type'));
        }
    }

    /**
     * Generate a link based on parameters
     * @author Webnus <info@webnus.biz>
     * @param array $vars
     * @param string $url
     * @return string
     */
    public function link($vars = array(), $url = NULL)
    {
        if(!trim($url)) $url = $this->main->URL('site').$this->main->get_main_slug().'/';
        foreach($vars as $key=>$value) $url = $this->main->add_qs_var($key, $value, $url);
        
        return $url;
    }
    
    /**
     * Generate content of email
     * @author Webnus <info@webnus.biz>
     * @param string $message
     * @param int $book_id
     * @param array $attendee
     * @return string
     */
    public function content($message, $book_id, $attendee = array())
    {
        $booker_id = get_post_field('post_author', $book_id);
        $booker = get_userdata($booker_id);

        $event_id = get_post_meta($book_id, 'mec_event_id', true);

        $first_name = (isset($booker->first_name) ? $booker->first_name : '');
        $last_name = (isset($booker->last_name) ? $booker->last_name : '');
        $name = (isset($booker->first_name) ? trim($booker->first_name.' '.(isset($booker->last_name) ? $booker->last_name : '')) : '');
        $email = (isset($booker->user_email) ? $booker->user_email : '');

        /**
         * Get the data from Attendee instead of main booker user
         */
        if(isset($attendee['name']) and trim($attendee['name']))
        {
            $name = $attendee['name'];
            $attendee_ex_name = explode(' ', $name);

            $first_name = isset($attendee_ex_name[0]) ? $attendee_ex_name[0] : '';
            $last_name = isset($attendee_ex_name[1]) ? $attendee_ex_name[1] : '';
            $email = isset($attendee['email']) ? $attendee['email'] : $email;
        }

        // Booker Data
        $message = str_replace('%%first_name%%', $first_name, $message);
        $message = str_replace('%%last_name%%', $last_name, $message);
        $message = str_replace('%%name%%', $name, $message);
        $message = str_replace('%%user_email%%', $email, $message);
        $message = str_replace('%%user_id%%', (isset($booker->ID) ? $booker->ID : ''), $message);
        
        // Site Data
        $message = str_replace('%%blog_name%%', get_bloginfo('name'), $message);
        $message = str_replace('%%blog_url%%', get_bloginfo('url'), $message);
        $message = str_replace('%%blog_description%%', get_bloginfo('description'), $message);
        
        // Book Data
        $transaction_id = get_post_meta($book_id, 'mec_transaction_id', true);

        $message = str_replace('%%book_date%%', get_the_date('', $book_id), $message);

        // Book Time
        $start_seconds = get_post_meta($event_id, 'mec_start_day_seconds', true);
        $event_start_time = get_post_meta($event_id, 'mec_allday', true) ? __('All of the day', 'modern-events-calendar-lite') : $this->main->get_time($start_seconds);

        // Condition for check some parameter simple hide event time
        if(!get_post_meta( $event_id, 'mec_hide_time', true )) $message = str_replace('%%book_time%%', $event_start_time, $message);
        else $message = str_replace('%%book_time%%', '', $message);

        $message = str_replace('%%invoice_link%%', $this->book->get_invoice_link($transaction_id), $message);

        $cancellation_key = get_post_meta($book_id, 'mec_cancellation_key', true);
        $cancellation_link = trim(get_permalink($event_id), '/').'/cancel/'.$cancellation_key.'/';

        $message = str_replace('%%cancellation_link%%', $cancellation_link, $message);

        // Booking Price
        $price = get_post_meta($book_id, 'mec_price', true);
        $message = str_replace('%%book_price%%', $this->main->render_price(($price ? $price : 0)), $message);
        $message = str_replace('%%total_attendees%%', $this->book->get_total_attendees($book_id), $message);

        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $mec_date = explode(':', get_post_meta($book_id, 'mec_date', true));

        if(count($mec_date) == 2 and isset($mec_date[0]))
        $message = str_replace('%%amount_tickets%%', $this->book->get_tickets_availability($event_id, current($mec_date), 'reservation'), $message);

        // Event Data
        $organizer_id = get_post_meta($event_id, 'mec_organizer_id', true);
        $location_id = get_post_meta($event_id, 'mec_location_id', true);
        $speaker_id = wp_get_post_terms( $event_id, 'mec_speaker', '');

        $organizer = get_term($organizer_id, 'mec_organizer');
        $location = get_term($location_id, 'mec_location');
        
        $message = str_replace('%%event_title%%', get_the_title($event_id), $message);
        $message = str_replace('%%event_link%%', get_post_permalink($event_id), $message);
        
        $message = str_replace('%%event_organizer_name%%', (isset($organizer->name) ? $organizer->name : ''), $message);
        $message = str_replace('%%event_organizer_tel%%', get_term_meta($organizer_id, 'tel', true), $message);
        $message = str_replace('%%event_organizer_email%%', get_term_meta($organizer_id, 'email', true), $message);

        $speaker_name = array();
        foreach($speaker_id as $speaker) $speaker_name[] = isset($speaker->name) ? $speaker->name : null;

        $message = str_replace('%%event_speaker_name%%', (isset($speaker_name) ? implode(', ', $speaker_name): ''), $message);
        $message = str_replace('%%event_location_name%%', (isset($location->name) ? $location->name : ''), $message);
        $message = str_replace('%%event_location_address%%', get_term_meta($location_id, 'address', true), $message);

        $ticket_name = $ticket_start_hour = $ticket_start_minute = $ticket_end_hour = $ticket_end_minute = $ticket_start_ampm = $ticket_end_ampm = '';

        $ticket_ids_str = get_post_meta($book_id, 'mec_ticket_id', true);
        $tickets = get_post_meta($event_id, 'mec_tickets', true);

        $ticket_ids = explode(',', $ticket_ids_str);
        $ticket_ids = array_filter($ticket_ids);

        if(!is_array($ticket_ids)) $ticket_ids = array();
        if(!is_array($tickets)) $tickets = array();

        foreach($ticket_ids as $get_ticket_id=>$value)
        {
            foreach($tickets as $ticket=>$ticket_info)
            {
                if($ticket != $value) continue;

                $ticket_name = $ticket_info['name'];
                $ticket_start_hour = $ticket_info['ticket_start_time_hour'];
                $ticket_start_minute = $ticket_info['ticket_start_time_minute'];
                $ticket_start_ampm = $ticket_info['ticket_start_time_ampm'];
                $ticket_end_hour = $ticket_info['ticket_end_time_hour'];
                $ticket_end_minute = $ticket_info['ticket_end_time_minute'];
                $ticket_end_ampm = $ticket_info['ticket_end_time_ampm'];
            }
        }

        $ticket_start_minute_s = $ticket_start_minute;
        $ticket_end_minute_s = $ticket_end_minute;

        if($ticket_start_minute == '0') $ticket_start_minute_s = '00';
        if($ticket_start_minute == '5') $ticket_start_minute_s = '05';
        if($ticket_end_minute == '0') $ticket_end_minute_s = '00';
        if($ticket_end_minute == '5') $ticket_end_minute_s = '05';

        $ticket_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($ticket_start_hour, $ticket_start_ampm), $ticket_start_minute_s);
        $ticket_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($ticket_end_hour, $ticket_end_ampm), $ticket_end_minute_s);

        $ticket_time = $this->main->get_time($ticket_start_seconds).' ' . esc_html__('to' , 'modern-events-calendar-lite') . ' ' .$this->main->get_time($ticket_end_seconds);

        $message = str_replace('%%ticket_name%%', $ticket_name, $message);
        $message = str_replace('%%ticket_time%%', $ticket_time, $message);
        
        $start_time = strtotime(get_the_date('Y-m-d', $book_id).' '.sprintf("%02d", $ticket_start_hour).':'.sprintf("%02d", $ticket_start_minute).' '.$ticket_start_ampm);
        $end_time = strtotime(get_the_date('Y-m-d', $book_id).' '.sprintf("%02d", $ticket_end_hour).':'.sprintf("%02d", $ticket_end_minute).' '.$ticket_end_ampm);
        
        $gmt_offset_seconds = $this->main->get_gmt_offset_seconds($start_time);
        $event_title = get_the_title($event_id);
        $event_info = get_post($event_id);
        $event_content = trim($event_info->post_content) ? strip_shortcodes(strip_tags($event_info->post_content)) : $event_title;

        $google_caneldar_link = '<a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color" href="https://www.google.com/calendar/event?action=TEMPLATE&text= ' . $event_title . '&dates='. gmdate('Ymd\\THi00\\Z', ($start_time - $gmt_offset_seconds)) . '/' . gmdate('Ymd\\THi00\\Z', ($end_time - $gmt_offset_seconds)) . '&details=' . urlencode($event_content) . '&location=' . get_term_meta($location_id, 'address', true) . '" target="_blank">' . __('+ Add to Google Calendar', 'modern-events-calendar-lite') . '</a>';
        $ical_export_link  = '<a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color" href="' . $this->main->ical_URL_email($event_id, $book_id, get_the_date('Y-m-d', $book_id)) . '">'. __('+ iCal export', 'modern-events-calendar-lite') . '</a>';
        
        $message = str_replace('%%google_calendar_link%%', $google_caneldar_link, $message);
        $message = str_replace('%%ics_link%%', $ical_export_link, $message);
        
        return $message;
    }
    
    /**
     * Get Booking Organizer Email by Book ID
     * @author Webnus <info@webnus.biz>
     * @param int $book_id
     * @return string
     */
    public function get_booking_organizer_email($book_id)
    {
        $event_id = get_post_meta($book_id, 'mec_event_id', true);
        $organizer_id = get_post_meta($event_id, 'mec_organizer_id', true);
        $email = get_term_meta($organizer_id, 'email', true);
        
        return trim($email) ? $email : false;
    }

    /**
     * Get full attendees info
     * @param $book_id
     * @return string
     */
    public function get_full_attendees_info($book_id)
    {
        $attendees_full_info = '';

        $attendees = get_post_meta($book_id, 'mec_attendees', true);
        if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($book_id, 'mec_attendee', true));

        $event_id = get_post_meta($book_id, 'mec_event_id', true);

        $reg_fields = $this->main->get_reg_fields($event_id);
        foreach($attendees as $key=>$attendee)
        {
            if(isset($attendee[0]['MEC_TYPE_OF_DATA'])) continue;
            if($key === 'attachments') continue;

            $reg_form = isset($attendee['reg']) ? $attendee['reg'] : array();

            $attendees_full_info .= __('Name', 'modern-events-calendar-lite').': '.((isset($attendee['name']) and trim($attendee['name'])) ? $attendee['name'] : '---')."\r\n";
            $attendees_full_info .= __('Email', 'modern-events-calendar-lite').': '.((isset($attendee['email']) and trim($attendee['email'])) ? $attendee['email'] : '---')."\r\n";

            if(count($reg_form))
            {
                foreach($reg_form as $field_id=>$value)
                {
                    // Placeholder Keys
                    if(!is_numeric($field_id)) continue;

                    $type = $reg_fields[$field_id]['type'];

                    $label = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['label'] : '';
                    if(trim($label) == '') continue;

                    if($type == 'agreement')
                    {
                        $label = sprintf(__($label, 'modern-events-calendar-lite'), '<a href="'.get_the_permalink($reg_fields[$field_id]['page']).'">'.get_the_title($reg_fields[$field_id]['page']).'</a>');
                        $attendees_full_info .= $label.': '.($value == '1' ? __('Yes', 'modern-events-calendar-lite') : __('No', 'modern-events-calendar-lite'))."\r\n";
                    }
                    else
                    {
                        $attendees_full_info .= __($label, 'modern-events-calendar-lite').': '.(is_string($value) ? $value : (is_array($value) ? implode(', ', $value) : '---'))."\r\n";
                    }
                }
            }

            $attendees_full_info .= "\r\n";
        }

        return $attendees_full_info;
    }
}