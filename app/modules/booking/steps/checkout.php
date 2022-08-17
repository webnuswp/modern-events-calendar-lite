<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_books $this */
/** @var array $raw_tickets */
/** @var array $price_details */
/** @var string $transaction_id */

$event_id = $event->ID;
$gateways = $this->main->get_gateways();

$date_ex = explode(':', $date);
$occurrence = $date_ex[0];

$booking_options = get_post_meta($event_id, 'mec_booking', true);
if(!is_array($booking_options)) $booking_options = array();

$gateway_settings = $this->main->get_gateways_options();

$active_gateways = array();
foreach($gateways as $gateway)
{
    if(!$gateway->enabled()) continue;

    // Gateway is disabled per event
    if(isset($gateway_settings['gateways_per_event']) and $gateway_settings['gateways_per_event'])
    {
        if(isset($booking_options['gateways_'.$gateway->id().'_disabled']) and $booking_options['gateways_'.$gateway->id().'_disabled']) continue;
    }

    $active_gateways[] = $gateway;

    // When Stripe Connect is enabled and organizer is connected then skip other gateways
    if($gateway->id() == 7 and get_user_meta(get_post_field('post_author', $event_id), 'mec_stripe_id', true)) // Stripe Connect
    {
        $active_gateways = array($gateway);
        break;
    }
}

$mecFluentEnable = class_exists('MEC_Fluent\Core\pluginBase\MecFluent') && (isset($this->settings['single_single_style']) and $this->settings['single_single_style'] == 'fluent') ? true : false;
if($mecFluentEnable)
{
    $ticketsDetails = [];
    foreach($raw_tickets as $ticket_id => $count)
    {
        if(!isset($event_tickets[$ticket_id])) continue;

        $ticketPrice = isset($event_tickets[$ticket_id]['price']) ? $this->book->get_ticket_price($event_tickets[$ticket_id], current_time('Y-m-d'), $event_id, $occurrence) : 0;
        $ticketsDetails[$ticket_id]['name'] = $event_tickets[$ticket_id]['name'];
        $ticketsDetails[$ticket_id]['count'] = $count;
        $ticketsDetails[$ticket_id]['price'] = ($ticketPrice*$count);
    }
}
?>
<div id="mec_book_payment_form" class="mec-booking-form-container mec-wrap-checkout row">
    <?php if($display_progress_bar): ?>
        <ul class="mec-booking-progress-bar">
            <li class="mec-booking-progress-bar-date-and-ticket mec-active"><?php esc_html_e('Date & Ticket', 'modern-events-calendar-lite'); ?></li>
            <li class="mec-booking-progress-bar-attendee-info mec-active"><?php esc_html_e('Attendee Info', 'modern-events-calendar-lite'); ?></li>
            <li class="mec-booking-progress-bar-payment mec-active"><?php esc_html_e('Payment', 'modern-events-calendar-lite'); ?></li>
            <li class="mec-booking-progress-bar-complete"><?php esc_html_e('Complete', 'modern-events-calendar-lite'); ?></li>
        </ul>
    <?php else: ?>
        <h4><?php esc_html_e('Checkout', 'modern-events-calendar-lite'); ?></h4>
    <?php endif; ?>

    <div class="mec-book-form-price">
    <?php if ($mecFluentEnable) { ?>
            <?php if ($ticketsDetails) { ?>
                <div class="mec-book-available-tickets-details">
                    <div class="mec-book-available-tickets-details-header">
                        <span><?php esc_html_e('Ticket(s) Name', 'modern-events-calendar-lite'); ?></span>
                        <span><?php esc_html_e('Qty', 'modern-events-calendar-lite'); ?></span>
                        <span><?php esc_html_e('Price', 'modern-events-calendar-lite'); ?></span>
                    </div>
                    <div class="mec-book-available-tickets-details-body">
                        <?php foreach ($ticketsDetails as $ticket) { ?>
                            <div class="mec-book-available-tickets-details-item">
                                <span><?php echo esc_html($ticket['name']); ?></span>
                                <span><?php echo esc_html($ticket['count']); ?></span>
                                <span><?php echo esc_html($ticket['price']); ?></span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <?php if(isset($price_details['details']) and is_array($price_details['details']) and count($price_details['details'])): ?>
                <div class="mec-book-price-details">
                    <?php foreach($price_details['details'] as $detail): ?>
                        <div class="mec-book-price-detail mec-book-price-detail-type<?php echo esc_attr($detail['type']); ?>">
                            <span></span>
                            <span class="mec-book-price-detail-description"><?php echo MEC_kses::element($detail['description']); ?></span>
                            <span class="mec-book-price-detail-amount"><?php echo MEC_kses::element($this->main->render_price($detail['amount'], $event_id)); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="mec-book-price-total">
                <span class="mec-book-price-total-description"><?php esc_html_e('Total Due', 'modern-events-calendar-lite'); ?></span>
                <span class="mec-book-price-total-amount"><?php echo MEC_kses::element($this->main->render_price($price_details['total'], $event_id)); ?></span>
            </div>
            <div style="clear:both"></div>
        <?php } else { ?>
            <?php if(isset($price_details['details']) and is_array($price_details['details']) and count($price_details['details'])): ?>
            <ul class="mec-book-price-details">
                <?php foreach($price_details['details'] as $detail): ?>
                <li class="mec-book-price-detail mec-book-price-detail-type<?php echo esc_attr($detail['type']); ?>">
                    <span class="mec-book-price-detail-description"><?php echo MEC_kses::element($detail['description']); ?></span>
                    <span class="mec-book-price-detail-amount"><?php echo MEC_kses::element($this->main->render_price($detail['amount'], $event_id)); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <span class="mec-book-price-total"><?php echo MEC_kses::element($this->main->render_price($price_details['total'], $event_id)); ?></span>
        <?php } ?>
    </div>
    <?php if(isset($this->settings['coupons_status']) and $this->settings['coupons_status']): ?>
    <div class="mec-book-form-coupon">
        <form id="mec_book_form_coupon<?php echo esc_attr($uniqueid); ?>" onsubmit="mec_book_apply_coupon<?php echo esc_attr($uniqueid); ?>(); return false;">
            <input type="text" name="coupon" title="<?php esc_attr_e('Discount Coupon', 'modern-events-calendar-lite'); ?>" placeholder="<?php esc_attr_e('Discount Coupon', 'modern-events-calendar-lite'); ?>" />
            <input type="hidden" name="transaction_id" value="<?php echo esc_attr($transaction_id); ?>" />
            <input type="hidden" name="action" value="mec_apply_coupon" />
            <?php wp_nonce_field('mec_apply_coupon_'.$transaction_id); ?>
            <button type="submit"><?php esc_html_e('Apply Coupon', 'modern-events-calendar-lite'); ?></button>
        </form>
        <div class="mec-coupon-message mec-util-hidden"></div>
    </div>
    <?php endif; ?>
    <?php do_action('mec-booking-after-coupon-form', $transaction_id, $uniqueid); ?>
    <div class="mec-book-form-gateways">
        <?php $first_gateway_id = NULL; foreach($active_gateways as $gateway): if(is_null($first_gateway_id)) $first_gateway_id = $gateway->id(); ?>
        <div class="mec-book-form-gateway-label">
            <label>
                <?php if(count($active_gateways) > 1): ?>
                <input type="radio" name="book[gateway]" onchange="mec_gateway_selected(this.value); mec_adjust_booking_fees<?php echo esc_attr($uniqueid); ?>(this.value, '<?php echo esc_attr($transaction_id); ?>');" value="<?php echo esc_attr($gateway->id()); ?>" />
                <?php endif; ?>
                <?php echo MEC_kses::element($gateway->title()); ?>
            </label>
        </div>
        <?php endforeach; ?>

        <?php foreach($active_gateways as $gateway): ?>
        <div class="mec-book-form-gateway-checkout <?php echo (count($active_gateways) == 1 ? '' : 'mec-util-hidden'); ?>" id="mec_book_form_gateway_checkout<?php echo esc_attr($gateway->id()); ?>">
            <?php echo MEC_kses::element($gateway->comment()); ?>
            <?php $gateway->checkout_form($transaction_id); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <button id="mec-book-form-back-btn-step-3" class="mec-book-form-back-button" type="button" onclick="mec_book_form_back_btn_click(this);"><?php esc_html_e('Back', 'modern-events-calendar-lite'); ?></button>
    <form id="mec_book_form_free_booking<?php echo esc_attr($uniqueid); ?>" class="mec-util-hidden mec-click-next" onsubmit="mec_book_free<?php echo esc_attr($uniqueid); ?>(); return false;">
        <div class="mec-form-row">
            <input type="hidden" name="action" value="mec_do_transaction_free" />
            <input type="hidden" name="transaction_id" value="<?php echo esc_attr($transaction_id); ?>" />
            <input type="hidden" name="gateway_id" value="4" />
            <input type="hidden" name="uniqueid" value="<?php echo esc_attr($uniqueid); ?>" />
            <?php wp_nonce_field('mec_transaction_form_'.$transaction_id); ?>
            <button class="mec-book-form-next-button" type="submit"><?php echo sprintf(esc_html__('Free %s', 'modern-events-calendar-lite'), $this->main->m('booking', esc_html__('Booking', 'modern-events-calendar-lite'))); ?></button>
        </div>
    </form>

    <?php if($first_gateway_id): ?>
    <script>
    jQuery(document).ready(function()
    {
        mec_adjust_booking_fees<?php echo esc_js($uniqueid); ?>(<?php echo esc_js($first_gateway_id); ?>, '<?php echo esc_js($transaction_id); ?>');
    });
    </script>
    <?php endif; ?>
</div>