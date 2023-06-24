<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_webhooks $this */

$hook = get_post_meta($post->ID, 'mec_hook', true);
$method = get_post_meta($post->ID, 'mec_method', true);
$url = get_post_meta($post->ID, 'mec_url', true);
?>
<div class="mec-webhook-metabox mec-details">
    <div class="mec-form-row">
        <h3><?php esc_html_e('Hook', 'modern-events-calendar-lite'); ?></h3>
        <select class="mec-col-2" name="mec[hook]" title="<?php esc_attr_e('Hook', 'modern-events-calendar-lite'); ?>">
            <option value="mec_booking_confirmed" <?php echo ($hook === 'mec_booking_confirmed' ? 'selected' : ''); ?>><?php esc_html_e('Booking Confirmed', 'modern-events-calendar-lite'); ?></option>
            <option value="mec_booking_verified" <?php echo ($hook === 'mec_booking_verified' ? 'selected' : ''); ?>><?php esc_html_e('Booking Verified', 'modern-events-calendar-lite'); ?></option>
            <option value="mec_booking_added" <?php echo ($hook === 'mec_booking_added' ? 'selected' : ''); ?>><?php esc_html_e('Booking Added', 'modern-events-calendar-lite'); ?></option>
            <option value="mec_booking_rejected" <?php echo ($hook === 'mec_booking_rejected' ? 'selected' : ''); ?>><?php esc_html_e('Booking Rejected', 'modern-events-calendar-lite'); ?></option>
            <option value="mec_booking_canceled" <?php echo ($hook === 'mec_booking_canceled' ? 'selected' : ''); ?>><?php esc_html_e('Booking Canceled', 'modern-events-calendar-lite'); ?></option>
            <option value="mec_booking_refunded" <?php echo ($hook === 'mec_booking_refunded' ? 'selected' : ''); ?>><?php esc_html_e('Booking Refunded', 'modern-events-calendar-lite'); ?></option>
        </select>
    </div>

    <div class="mec-form-row">
        <h3><?php esc_html_e('Request', 'modern-events-calendar-lite'); ?></h3>
        <select class="mec-col-2" name="mec[method]" title="<?php esc_attr_e('Method', 'modern-events-calendar-lite'); ?>">
            <option value="POST" <?php echo $method === 'POST' ? 'selected' : ''; ?>>POST</option>
            <option value="GET" <?php echo $method === 'GET' ? 'selected' : ''; ?>>GET</option>
        </select>
        <input class="mec-col-6" type="url" name="mec[url]" value="<?php echo esc_url_raw($url); ?>" title="<?php esc_attr_e('URL to send the request', 'modern-events-calendar-lite'); ?>" placeholder="<?php esc_attr_e('URL to send the request', 'modern-events-calendar-lite'); ?>">

        <p class="description"><?php esc_html_e('MEC will send the data to your target as plain form data', 'modern-events-calendar-lite'); ?></p>
    </div>

    <?php
        // Add a nonce field so we can check for it later.
        wp_nonce_field('mec_webhook_data', 'mec_webhook_nonce');
    ?>
</div>