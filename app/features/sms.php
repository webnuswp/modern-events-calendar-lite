<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC SMS feature.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_sms extends MEC_base
{
    public $factory;
    public $main;
    public $settings;
    public $notifications;

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

        // MEC Notification Library
        $this->notifications = $this->main->getNotifications();
    }

    /**
     * Initialize locations feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Pro Only
        if(!$this->getPRO()) return;

        // SMS Status
        $sms_status = (isset($this->settings['sms_status']) and $this->settings['sms_status']);

        // SMS is not enabled
        if(!$sms_status) return;

        $sid = $this->settings['sms_twilio_account_sid'] ?? '';
        $auth_token = $this->settings['sms_twilio_auth_token'] ?? '';
        $from = $this->settings['sms_twilio_sender_number'] ?? '';

        // Insufficient Credentials
        if(!trim($sid) or !trim($auth_token) or !trim($from)) return;

        // Admin Notification
        $this->factory->action('mec_booking_completed', array($this, 'admin_notification'), 12);
    }

    /**
     * Send admin notification
     *
     * @param int $book_id
     * @return bool
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     * @author Webnus <info@webnus.net>
     */
    public function admin_notification($book_id)
    {
        if(!$book_id) return false;

        // Admin Notification is disabled
        if(isset($this->settings['sms_notif_admin_status']) and !$this->settings['sms_notif_admin_status']) return false;

        $recipients_str = $this->settings['sms_notif_admin_recipients'] ?? '';

        // No recipients
        if(trim($recipients_str) === '') return false;

        $recipients = explode(',', $recipients_str);

        // Unique Recipients
        $recipients = array_map('trim', $recipients);
        $recipients = array_unique($recipients);

        $message = $this->settings['sms_notif_admin_text'] ?? '';
        $message = $this->notifications->content($message, $book_id);

        // Remove remained placeholders
        $message = preg_replace('/%%.*%%/', '', $message);

        // Strip HTML Tags
        $message = strip_tags($message);

        try
        {
            // Twilio Client
            $client = $this->get_twilio_client();

            // From Number
            $from = $this->settings['sms_twilio_sender_number'] ?? '';

            // Send Text Messages
            foreach ($recipients as $recipient)
            {
                $client->messages->create(
                    $recipient,
                    [
                        'from' => $from,
                        'body' => $message
                    ]
                );
            }
        }
        catch(Exception $e)
        {
            // Store the Error
            update_option('mec_sms_twilio_error', $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function get_twilio_client()
    {
        require __DIR__ . '/../api/Twilio/autoload.php';

        $sid = $this->settings['sms_twilio_account_sid'] ?? '';
        $auth_token = $this->settings['sms_twilio_auth_token'] ?? '';

        return new Twilio\Rest\Client($sid, $auth_token);
    }
}