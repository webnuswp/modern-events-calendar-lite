<?php
/** no direct access **/
defined('MECEXEC') or die();

$event_id = $event->ID;
?>
<?php if(isset($message)): ?>
<div class="mec-event-book-message mec-gateway-message mec-success">
    <div class="<?php echo (isset($message_class) ? $message_class : ''); ?>">
        <?php echo stripslashes($message); ?>

        <?php if(isset($response_data) and isset($response_data['invoice_link'])): ?>
         <a class="mec-invoice-download" href="<?php echo $response_data['invoice_link']; ?>"><?php echo __('Download Invoice', 'modern-events-calendar-lite'); ?></a>
        <?php unset($response_data['invoice_link']); endif; ?>
    </div>
</div>
<?php endif;