<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Cart class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_cart extends MEC_base
{
    public $factory;
    public $main;
    public $cart;
    public $book;
    public $settings;

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

        // Import MEC Cart
        $this->cart = $this->getCart();

        // Import MEC Booking
        $this->book = $this->getBook();
    }
    
    /**
     * Initialize Cart Feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Cart Status
        $cart_status = (isset($this->settings['mec_cart_status']) and $this->settings['mec_cart_status']) ? true : false;

        // Feature is not enabled
        if(!$cart_status) return;

        // Shortcodes
        $this->factory->shortcode('mec-cart', array($this, 'cart'));
        $this->factory->shortcode('mec-checkout', array($this, 'checkout'));

        // Remove Transaction from Cart
        $this->factory->action('wp_ajax_mec_cart_remove_transaction', array($this, 'remove'));
        $this->factory->action('wp_ajax_nopriv_mec_cart_remove_transaction', array($this, 'remove'));

        // Apply Coupon
        $this->factory->action('wp_ajax_mec_cart_coupon', array($this, 'coupon'));
        $this->factory->action('wp_ajax_nopriv_mec_cart_coupon', array($this, 'coupon'));

        // Free Checkout
        $this->factory->action('wp_ajax_mec_do_cart_free', array($this, 'free'));
        $this->factory->action('wp_ajax_nopriv_mec_do_cart_free', array($this, 'free'));
    }

    public function cart($atts)
    {
        $path = MEC::import('app.features.cart.cart', true, true);

        ob_start();
        include $path;
        return $output = ob_get_clean();
    }

    public function checkout($atts)
    {
        $path = MEC::import('app.features.cart.checkout', true, true);

        ob_start();
        include $path;
        return $output = ob_get_clean();
    }

    public function remove()
    {
        $transaction_id = isset($_REQUEST['transaction_id']) ? sanitize_text_field($_REQUEST['transaction_id']) : NULL;

        $cart_id = $this->cart->get_cart_id();
        $cart = $this->cart->get_cart($cart_id);

        // Validation
        if(!in_array($transaction_id, $cart)) wp_send_json(array('success' => 0, 'message' => esc_html__('Transaction does not exists in cart.', 'modern-events-calendar-lite')));

        // Remove
        $this->cart->remove($transaction_id);

        // Updated Cart
        $updated_cart = $this->cart->get_cart($cart_id);

        // Total
        $total = 0;
        foreach($updated_cart as $t_id)
        {
            $TO = $this->book->get_TO($t_id);
            $total += $TO->get_payable();
        }

        // Response
        wp_send_json(array(
            'success' => 1,
            'total' => $this->main->render_price($total),
            'message' => esc_html__('Successfully removed and updated the cart.', 'modern-events-calendar-lite'),
        ));
    }

    public function coupon()
    {
        $coupon = isset($_REQUEST['coupon']) ? sanitize_text_field($_REQUEST['coupon']) : NULL;

        $cart_id = $this->cart->get_cart_id();
        $cart = $this->cart->get_cart($cart_id);

        $applied = false;
        foreach($cart as $transaction_id)
        {
            $TO = $this->book->get_TO($transaction_id);

            // Free Transaction
            if($TO->is_free()) continue;

            $validity = $this->book->coupon_check_validity($coupon, $TO->get_event_id(), $TO->get_array());
            if($validity == 1)
            {
                $applied = true;
                $this->book->coupon_apply($coupon, $transaction_id);
            }
        }

        // Response
        if($applied)
        {
            wp_send_json(array(
                'success' => 1,
                'message' => esc_html__('Coupon applied successfully. Please wait ...', 'modern-events-calendar-lite'),
            ));
        }
        else
        {
            wp_send_json(array(
                'success' => 0,
                'message' => esc_html__('Coupon was not valid or applicable.', 'modern-events-calendar-lite'),
            ));
        }
    }

    public function free()
    {
        $cart_id = isset($_POST['cart_id']) ? sanitize_text_field($_POST['cart_id']) : '';

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_cart_form_' . $cart_id))
        {
            wp_send_json(array(
                'success' => 0,
                'code' => 'NONCE_IS_INVALID',
                'message' => esc_html__('Request is invalid!', 'modern-events-calendar-lite'),
            ));
        }

        $cart = $this->cart->get_cart($cart_id);
        if(!$this->cart->is_free($cart))
        {
            wp_send_json(array(
                'success' => 0,
                'code' => 'NOT_FREE',
                'message' => esc_html__('Your cart is not free!', 'modern-events-calendar-lite'),
            ));
        }

        $free_gateway = new MEC_gateway_free();
        $results = $free_gateway->cart_do_transaction($cart_id);

        $results['output'] = '<h4>' . esc_html__('Thanks for your booking.', 'modern-events-calendar-lite') . '</h4>
        <div class="mec-event-book-message">
            <div class="' . ($results['success'] ? 'mec-success' : 'mec-error') . '">' . MEC_kses::element($results['message']) . '</div>
        </div>';

        wp_send_json($results);
    }
}