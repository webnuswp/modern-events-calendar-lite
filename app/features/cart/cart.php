<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_cart $this */

$cart_id = $this->cart->get_cart_id();
$cart = $this->cart->get_cart($cart_id);

// Javascript Code
$javascript = '<script>
jQuery(document).ready(function()
{
    jQuery(".mec-cart").mecCart(
    {
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
    });
});
</script>';

// Include javascript code into the footer
$this->factory->params('footer', $javascript);

// Events Archive
$archive_url = $this->main->get_archive_url();
?>
<div class="mec-wrap mec-cart">

    <div class="mec-cart-empty-wrapper <?php echo (count($cart) ? 'mec-util-hidden' : ''); ?>">
        <p><?php esc_html_e('Cart is empty!', 'modern-events-calendar-lite'); ?></p>
        <?php if($archive_url): ?>
        <div>
            <a class="mec-bg-color button" href="<?php echo esc_url($archive_url); ?>"><?php esc_html_e('Go to events page', 'modern-events-calendar-lite'); ?></a>
        </div>
        <?php endif; ?>
    </div>

    <?php if(count($cart)): ?>
    <table id="mec_cart_transactions_table">
        <thead>
            <tr>
                <th></th>
                <th><?php esc_html_e('Transaction ID', 'modern-events-calendar-lite'); ?></th>
                <th><?php esc_html_e('Event', 'modern-events-calendar-lite'); ?></th>
                <th><?php esc_html_e('Tickets', 'modern-events-calendar-lite'); ?></th>
                <th><?php esc_html_e('Dates', 'modern-events-calendar-lite'); ?></th>
                <th><?php esc_html_e('Payable', 'modern-events-calendar-lite'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cart as $transaction_id): $TO = $this->book->get_TO($transaction_id); ?>
            <tr id="mec_cart_transactions_<?php echo esc_attr($transaction_id); ?>">
                <td><span class="mec-cart-remove-transactions" data-transaction-id="<?php echo esc_attr($transaction_id); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="9" height="8.999" viewBox="0 0 9 8.999"><path id="close" d="M6.079,5.647l4.067,4.067-.433.433L5.646,6.079,1.579,10.146l-.433-.433L5.214,5.647,1.146,1.58l.433-.433L5.646,5.214,9.713,1.147l.433.433Z" transform="translate(-1.146 -1.147)" fill="#949596"></path></svg></span></td>
                <td>
                    <?php echo esc_html($transaction_id); ?>
                    <?php echo ($TO->get_coupon() ? '<br><code class="mec-cart-coupon-code" title="'.esc_attr__('Applied Coupon', 'modern-events-calendar-lite').'">'.esc_html($TO->get_coupon()).'</code>' : ''); ?>
                </td>
                <td class="mec-cart-event-info"><?php echo MEC_kses::element($TO->get_event_featured_image()); ?><?php echo MEC_kses::element($TO->get_event_link()); ?></td>
                <td><?php echo MEC_kses::element($TO->get_tickets_html()); ?></td>
                <td><?php echo MEC_kses::element($TO->get_dates_html()); ?></td>
                <td><?php echo MEC_kses::element($TO->get_price_html()); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5"></th>
                <th id="mec_cart_total_payable"><?php echo MEC_kses::element($this->main->render_price($this->cart->get_payable($cart))); ?></th>
            </tr>
        </tfoot>
    </table>
    <div class="mec-cart-coupon-checkout-action">

        <?php if(isset($this->settings['coupons_status']) and $this->settings['coupons_status']): ?>
        <div class="mec-cart-coupon">
            <form id="mec_cart_coupon_form">
                <input type="text" id="mec_cart_coupon_input" placeholder="<?php esc_attr_e('Coupon Code'); ?>" title="<?php esc_attr_e('Coupon Code'); ?>">
                <button type="submit"><?php esc_html_e('Apply Coupon'); ?></button>
            </form>
            <div id="mec_cart_message"></div>
        </div>
        <?php endif; ?>

        <?php if(isset($this->settings['checkout_page']) and $this->settings['checkout_page']): ?>
        <div class="mec-cart-checkout-button">
            <a class="mec-cart-checkout-link mec-bg-color button" href="<?php echo esc_url(get_permalink($this->settings['checkout_page'])); ?>"><?php esc_html_e('Proceed to checkout', 'modern-events-calendar-lite'); ?></a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>