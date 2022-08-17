<?php
/** no direct access **/
defined('MECEXEC') or die();

$event_id = $event->ID;
?>
<?php if($display_progress_bar): ?>
<ul class="mec-booking-progress-bar">
    <li class="mec-booking-progress-bar-date-and-ticket mec-active"><?php esc_html_e('Date & Ticket', 'modern-events-calendar-lite'); ?></li>
    <li class="mec-booking-progress-bar-attendee-info mec-active"><?php esc_html_e('Attendee Info', 'modern-events-calendar-lite'); ?></li>
    <li class="mec-booking-progress-bar-done mec-active"><?php esc_html_e('Done', 'modern-events-calendar-lite'); ?></li>
</ul>
<?php endif; ?>
<?php if(isset($message)): ?>
<div class="mec-event-book-message mec-gateway-message mec-success">
    <div class="<?php echo (isset($message_class) ? esc_attr($message_class) : ''); ?>">
        <?php echo MEC_kses::element(stripslashes($message)); ?>
    </div>
</div>
<?php endif;