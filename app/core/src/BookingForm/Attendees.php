<?php

namespace MEC\BookingForm;

class Attendees {

    public static function enqueue(){

        ?>
        <style>
            .nice-select {
                -webkit-tap-highlight-color: transparent;
                background-color: #fff;
                border-radius: 5px;
                border: solid 1px #e8e8e8;
                box-sizing: border-box;
                clear: both;
                cursor: pointer;
                display: block;
                float: left;
                font-family: inherit;
                font-size: 14px;
                font-weight: 400;
                height: 42px;
                line-height: 40px;
                outline: 0;
                padding-left: 18px;
                padding-right: 30px;
                position: relative;
                text-align: left !important;
                -webkit-transition: all .2s ease-in-out;
                transition: all .2s ease-in-out;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                white-space: nowrap;
                width: auto
            }

            .nice-select:hover {
                border-color: #dbdbdb
            }

            .nice-select.open,
            .nice-select:active,
            .nice-select:focus {
                border-color: #999
            }

            .nice-select:after {
                border-bottom: 2px solid #999;
                border-right: 2px solid #999;
                content: '';
                display: block;
                height: 5px;
                margin-top: -4px;
                pointer-events: none;
                position: absolute;
                right: 12px;
                top: 50%;
                -webkit-transform-origin: 66% 66%;
                -ms-transform-origin: 66% 66%;
                transform-origin: 66% 66%;
                -webkit-transform: rotate(45deg);
                -ms-transform: rotate(45deg);
                transform: rotate(45deg);
                -webkit-transition: all .15s ease-in-out;
                transition: all .15s ease-in-out;
                width: 5px
            }

            .nice-select.open:after {
                -webkit-transform: rotate(-135deg);
                -ms-transform: rotate(-135deg);
                transform: rotate(-135deg)
            }

            .nice-select.open .list {
                opacity: 1;
                pointer-events: auto;
                -webkit-transform: scale(1) translateY(0);
                -ms-transform: scale(1) translateY(0);
                transform: scale(1) translateY(0)
            }

            .nice-select.disabled {
                border-color: #ededed;
                color: #999;
                pointer-events: none
            }

            .nice-select.disabled:after {
                border-color: #ccc
            }

            .nice-select.wide {
                width: 100%
            }

            .nice-select.wide .list {
                left: 0 !important;
                right: 0 !important
            }

            .nice-select.right {
                float: right
            }

            .nice-select.right .list {
                left: auto;
                right: 0
            }

            .nice-select.small {
                font-size: 12px;
                height: 36px;
                line-height: 34px
            }

            .nice-select.small:after {
                height: 4px;
                width: 4px
            }

            .nice-select.small .option {
                line-height: 34px;
                min-height: 34px
            }

            .nice-select .list {
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 0 0 1px rgba(68, 68, 68, .11);
                box-sizing: border-box;
                margin-top: 4px;
                opacity: 0;
                overflow: hidden;
                padding: 0;
                pointer-events: none;
                position: absolute;
                top: 100%;
                left: 0;
                -webkit-transform-origin: 50% 0;
                -ms-transform-origin: 50% 0;
                transform-origin: 50% 0;
                -webkit-transform: scale(.75) translateY(-21px);
                -ms-transform: scale(.75) translateY(-21px);
                transform: scale(.75) translateY(-21px);
                -webkit-transition: all .2s cubic-bezier(.5, 0, 0, 1.25), opacity .15s ease-out;
                transition: all .2s cubic-bezier(.5, 0, 0, 1.25), opacity .15s ease-out;
                z-index: 9
            }

            .nice-select .list:hover .option:not(:hover) {
                background-color: transparent !important
            }

            .nice-select .option {
                cursor: pointer;
                font-weight: 400;
                line-height: 40px;
                list-style: none;
                min-height: 40px;
                outline: 0;
                padding-left: 18px;
                padding-right: 29px;
                text-align: left;
                -webkit-transition: all .2s;
                transition: all .2s
            }

            .nice-select .option.focus,
            .nice-select .option.selected.focus,
            .nice-select .option:hover {
                background-color: #f6f6f6
            }

            .nice-select .option.selected {
                font-weight: 700
            }

            .nice-select .option.disabled {
                background-color: transparent;
                color: #999;
                cursor: default
            }

            .no-csspointerevents .nice-select .list {
                display: none
            }

            .no-csspointerevents .nice-select.open .list {
                display: block
            }

            .mec-single-event .mec-events-meta-group-booking ul li {
				padding-left: 5px;
    			padding-right: 5px;
			}

			.mec-single-event .mec-events-meta-group-booking ul.mec-book-tickets-reg-fields-container {
				margin: 0;
			}

			.mec-single-event .mec-events-meta-group-booking ul li.mec-book-ticket-container .mec-ticket-detail{
				display: flex;
				align-items: center;
				padding: 0 0 30px 0;
				margin-bottom: 30px;
				border-bottom: 1px solid #f0f1f2;
			}

            .mec-single-event .mec-events-meta-group-booking .mec-book-tickets-container .mec-book-ticket-container .mec-ticket-subtotal-wrapper,
            .mec-single-event .mec-events-meta-group-booking .mec-book-tickets-container .mec-book-ticket-container .mec-ticket-detail {
                display: flex;
                align-items: center;
                padding: 0 0 30px 0;
                margin-bottom: 30px;
                border-bottom: 1px solid #f0f1f2;
            }
        </style>
        <?php

        wp_enqueue_script('mec-nice-select', \MEC\Base::get_main()->asset('js/jquery.nice-select.min.js'));

        wp_add_inline_script('mec-nice-select', '
            jQuery(document).ready(function(){
                if(jQuery(".mec-booking-shortcode").length < 0) return;

                // Events
                jQuery(".mec-booking-shortcode").find("select").niceSelect();
            });');
    }

    public static function output( $event, $date, $tickets, $reg_fields, $bfixed_fields, $uniqueid, $args = array() ){

        if( is_numeric( $event ) ) {

            $single_event = new \MEC_skin_single();
            $events = $single_event->get_event_mec( $event );
            $event = $events[0];
        }

        $form_title = $args['form_title'] ?? esc_html__("Attendee's Form", 'modern-events-calendar-lite');
        $date_ex = explode(':', $date);
        $occurrence = $date_ex[0];

        $mec_settings = \MEC\Settings\Settings::getInstance()->get_settings();
        $bookClass = \MEC\Base::get_main()->getBook();

        $event_id = $event->ID;
        $translated_event_id = (isset($_REQUEST['translated_event_id']) ? sanitize_text_field($_REQUEST['translated_event_id']) : 0);
        $requested_event_id = $event->requested_id ?? $event_id;

        $mec_email = false;
        $mec_name = false;
        foreach($reg_fields as $field) {

            if(isset($field['type'])) {

                if($field['type'] == 'mec_email') $mec_email = true;
                if($field['type'] == 'name') $mec_name = true;
            }
            else break;
        }

        if(!$mec_name) {

            $reg_fields[] = array(
                'mandatory' => '0',
                'type'      => 'name',
                'label'     => esc_html__('Name', 'modern-events-calendar-lite'),
            );
        }

        if(!$mec_email) {
            $reg_fields[] = array(
                'mandatory' => '0',
                'type'      => 'mec_email',
                'label'     => esc_html__('Email', 'modern-events-calendar-lite'),
            );
        }

        $fees = $bookClass->get_fees($event_id);

        $step_skipped = (isset($_REQUEST['do_skip']) ? sanitize_text_field($_REQUEST['do_skip']) : 0);

        // WC System
        $WC_status = (isset($mec_settings['wc_status']) and $mec_settings['wc_status'] and class_exists('WooCommerce'));
        if($WC_status) $fees = array();

        // MEC Card
        $cart_status = (isset($mec_settings['mec_cart_status']) and $mec_settings['mec_cart_status']);
        if($cart_status) $WC_status = false;

        $total_ticket_prices = 0;
        $check_free_tickets_booking = apply_filters('check_free_tickets_booking', 1);
        $has_fees = (bool) count($fees);

        $current_user = wp_get_current_user();
        $first_for_all = (!isset($mec_settings['booking_first_for_all']) or (isset($mec_settings['booking_first_for_all']) and $mec_settings['booking_first_for_all'] == 1));

        // Username & Password Method
        $booking_register = !((isset($mec_settings['booking_registration']) and !$mec_settings['booking_registration']));
        $booking_userpass = (isset($mec_settings['booking_userpass']) and trim($mec_settings['booking_userpass'])) ? $mec_settings['booking_userpass'] : 'auto';

        // Lock Pre-filled Fields
        $lock_prefilled = (isset($mec_settings['booking_lock_prefilled']) and trim($mec_settings['booking_lock_prefilled']) != '') ? $mec_settings['booking_lock_prefilled'] : 0;

        // Attendee Counter
        $attendee_counter = (isset($mec_settings['attendee_counter']) and $mec_settings['attendee_counter']) ? $mec_settings['attendee_counter'] : '';

        $display_progress_bar = \MEC\Base::get_main()->can_display_booking_progress_bar($mec_settings);

        $event_tickets = isset($event->data->tickets) ? $event->data->tickets : array();

        foreach ($tickets as $ticket_id => $count) {

            if (!$count) continue;
            $ticket = $event_tickets[$ticket_id];

            for($p = 1; $p <= $count; $p++) {

                $ticket_price = $bookClass->get_ticket_price($ticket, current_time('Y-m-d'), $event_id, $occurrence);
                if(is_numeric($ticket_price)) $total_ticket_prices += $ticket_price;
            }
        }

        if(isset($all_dates) and count($all_dates)) {

            $total_ticket_prices = $total_ticket_prices * count($all_dates);
        }

        static::enqueue();

        ?>
        <form id="mec_book_form<?php echo esc_attr($uniqueid); ?>" class="mec-booking-form-container row" onsubmit="mec_book_form_submit(event, <?php echo esc_attr($uniqueid); ?>);" novalidate="novalidate" enctype="multipart/form-data" method="post">

            <?php if( $display_progress_bar ): ?>
                <ul class="mec-booking-progress-bar">
                    <li class="mec-booking-progress-bar-date-and-ticket mec-active"><?php esc_html_e('Date & Ticket', 'modern-events-calendar-lite'); ?></li>
                    <li class="mec-booking-progress-bar-attendee-info mec-active"><?php esc_html_e('Attendee Info', 'modern-events-calendar-lite'); ?></li>
                    <?php if($WC_status): ?>
                        <li class="mec-booking-progress-bar-payment"><?php esc_html_e('Checkout', 'modern-events-calendar-lite'); ?></li>
                    <?php else: ?>
                        <li class="mec-booking-progress-bar-payment"><?php esc_html_e('Payment', 'modern-events-calendar-lite'); ?></li>
                        <li class="mec-booking-progress-bar-complete"><?php esc_html_e('Complete', 'modern-events-calendar-lite'); ?></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4><?php echo apply_filters('mec-booking-attendees-title', $form_title, $event_id) ?></h4>
            <?php endif; ?>

            <div class="col-md-12 mec-ticket-subtotal-wrapper">
                <div class="mec-ticket-icon-wrapper"><?php echo \MEC\Base::get_main()->svg('form/subtotal-icon'); ?></div>
                <div class="mec-ticket-name-description-wrapper">
                    <div class="mec-ticket-price-wrapper">
                        <span class="mec-ticket-price-label"><?php echo esc_html__('Subtotal', 'modern-events-calendar-lite'); ?></span>
                        <span class="mec-ticket-price"><?php echo \MEC_kses::element(\MEC\Base::get_main()->render_price($total_ticket_prices, $requested_event_id)); ?></span>
                    </div>
                </div>
            </div>

            <ul class="mec-book-bfixed-fields-container">
                <?php DisplayFields::display_fields( 'book', 'bfixed', $bfixed_fields ); ?>
            </ul>

            <ul class="mec-book-tickets-container">

                <?php $j = 0;
                foreach ($tickets as $ticket_id => $count) : if (!$count) continue;
                    $ticket = $event_tickets[$ticket_id];
                    for ($i = 1; $i <= $count; $i++) :
                        ?>
                        <li class="mec-book-ticket-container <?php echo (($j > 0 and $first_for_all) ? 'mec-util-hidden' : ''); ?>">

                            <!-- Attendee Details -->
                            <?php if(!empty($ticket['name']) || !empty($bookClass->get_ticket_price_label($ticket, current_time('Y-m-d'), $event_id, $occurrence))): ?>
                                <div class="mec-ticket-detail col-md-12">
                                    <div class="mec-ticket-icon-wrapper"><?php echo \MEC\Base::get_main()->svg('form/ticket-icon'); ?></div>
                                    <div class="mec-ticket-name-description-wrapper">
                                        <h4>
                                            <?php if($attendee_counter == 1): ?><span class="mec-ticket-attendee-counter"><?php printf(__('Attendee #%s details — ', 'modern-events-calendar-lite'), $i); ?></span><?php endif; ?>
                                            <span class="mec-ticket-name"><?php echo esc_html__($ticket['name'], 'modern-events-calendar-lite'); ?></span>
                                        </h4>
                                        <?php
                                            $ticket_price = $bookClass->get_ticket_price_label($ticket, current_time('Y-m-d'), $event_id, $occurrence);
                                            $ticket_price = apply_filters('mec_filter_ticket_price_label', $ticket_price, $ticket, $event_id, $bookClass);
                                        ?>
                                        <div class="mec-ticket-price-wrapper">
                                            <span class="mec-ticket-price-label"><?php echo esc_html__('Subtotal', 'modern-events-calendar-lite'); ?></span>
                                            <span class="mec-ticket-price"><?php echo \MEC_kses::element($ticket_price); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Custom fields -->
                            <ul class="mec-book-tickets-reg-fields-container">
                                <?php DisplayFields::display_fields( 'book', 'reg', $reg_fields, $j ); ?>
                            </ul>

                            <!-- Ticket Variations -->
                            <?php
                                $ticket_variations = \MEC\Base::get_main()->ticket_variations($event_id, $ticket_id, $translated_event_id);

                                if($WC_status) $ticket_variations = array();
                                if(\MEC\Base::get_main()->has_variations_per_ticket($event_id, $ticket_id)) $first_for_all = false;

                                $has_variations = (bool) count($ticket_variations);
                            ?>
                            <?php if(isset($mec_settings['ticket_variations_status']) and $mec_settings['ticket_variations_status'] and count($ticket_variations)): foreach($ticket_variations as $ticket_variation_id => $ticket_variation): if(!is_numeric($ticket_variation_id) or !isset($ticket_variation['title']) or (isset($ticket_variation['title']) and !trim($ticket_variation['title']))) continue; ?>
                                <div class="col-md-6 mec-book-ticket-variation-wrapper">
                                    <div class="mec-book-ticket-variation" data-ticket-id="<?php echo esc_attr($j); ?>" data-ticket-variation-id="<?php echo esc_attr($ticket_variation_id); ?>">
                                        <h5><span class="mec-ticket-variation-name"><?php echo esc_html($ticket_variation['title']); ?></span><span class="mec-ticket-variation-price"><?php echo \MEC_kses::element(\MEC\Base::get_main()->render_price($ticket_variation['price'], $requested_event_id)); ?></span></h5>
                                        <input onkeydown="return event.keyCode !== 69" type="number" min="0" max="<?php echo ((is_numeric($ticket_variation['max']) and $ticket_variation['max']) ? $ticket_variation['max'] : ''); ?>" name="book[tickets][<?php echo esc_attr($j); ?>][variations][<?php echo esc_attr($ticket_variation_id); ?>]" onchange="mec_check_variation_min_max<?php echo esc_attr($uniqueid); ?>(this);">
                                    </div>
                                </div>
                            <?php endforeach;
                            endif; ?>

                                <input type="hidden" name="book[tickets][<?php echo esc_attr($j); ?>][id]" value="<?php echo esc_attr($ticket_id); ?>" />
                                <input type="hidden" name="book[tickets][<?php echo esc_attr($j); ?>][count]" value="1" />
                        </li>
                    <?php  $j++; endfor; ?>
                <?php endforeach; ?>
            </ul>

            <?php if ($j > 1 and $first_for_all) : ?>
            <div class="mec-first-for-all-wrapper">
                <label class="mec-fill-attendees">
                    <input type="hidden" name="book[first_for_all]" value="0" />
                    <input type="checkbox" name="book[first_for_all]" value="1" checked="checked" class="mec_book_first_for_all" id="mec_book_first_for_all<?php echo esc_attr($uniqueid); ?>" onchange="mec_toggle_first_for_all<?php echo esc_attr($uniqueid); ?>(this);" />
                    <label for="pages1" onclick="mec_label_first_for_all<?php echo esc_attr($uniqueid); ?>(this);" class="wn-checkbox-label"></label>
                    <?php esc_html_e("Fill other attendees information like the first form.", 'modern-events-calendar-lite'); ?>
                </label>
            </div>
            <?php endif; ?>

            <?php if($booking_register and $booking_userpass == 'manual' and !is_user_logged_in()): ?>
            <div class="mec-book-username-password-wrapper">
                <h3><?php esc_html_e('Registration', 'modern-events-calendar-lite'); ?></h3>
                <ul class="mec-booking-registrtaion-fields">
                    <li>
                        <label for="mec_book_form_username"><?php esc_html_e('Username', 'modern-events-calendar-lite'); ?></label>
                        <input type="text" name="book[username]" id="mec_book_form_username">
                    </li>
                    <li>
                        <label for="mec_book_form_password"><?php esc_html_e('Password', 'modern-events-calendar-lite'); ?></label>
                        <input type="password" name="book[password]" id="mec_book_form_password">
                    </li>
                </ul>
            </div>
            <?php endif; ?>

            <div class="clearfix"></div>

            <?php if(isset($all_dates) and count($all_dates)): // Multiple Date ?>
                <?php foreach($all_dates as $d): ?>
                <input type="hidden" name="book[date][]" value="<?php echo esc_attr($d); ?>" />
                <?php endforeach; ?>
            <?php else: ?>
            <input type="hidden" name="book[date]" value="<?php echo esc_attr($date); ?>" />
            <?php endif; ?>
            <input type="hidden" name="book[event_id]" value="<?php echo esc_attr($event_id); ?>" />
            <input type="hidden" name="book[translated_event_id]" value="<?php echo esc_attr($translated_event_id); ?>" />
            <input type="hidden" name="lang" value="<?php echo esc_attr(\MEC\Base::get_main()->get_current_lang_code()); ?>" />
            <input type="hidden" name="action" value="mec_book_form" />
            <input type="hidden" name="event_id" value="<?php echo esc_attr($event_id); ?>" />
            <input type="hidden" name="translated_event_id" value="<?php echo esc_attr($translated_event_id); ?>" />
            <input type="hidden" name="uniqueid" value="<?php echo esc_attr($uniqueid); ?>" />
            <input type="hidden" name="step" value="2" />

            <?php do_action('mec_booking_end_form_step_2', $event_id, $tickets, (isset($all_dates) ? $all_dates : NULL), $date); ?>
            <?php wp_nonce_field('mec_book_form_' . $event_id); ?>

            <div class="mec-book-form-btn-wrap">
                <?php if(!$step_skipped): ?>
                <button id="mec-book-form-back-btn-step-2" class="mec-book-form-back-button" type="button" onclick="mec_book_form_back_btn_click(this);"><?php echo \MEC\Base::get_main()->svg('form/back-icon').' '.esc_html__('Back', 'modern-events-calendar-lite'); ?></button>
                <?php endif; ?>
                <button id="mec-book-form-btn-step-2" class="mec-book-form-next-button" type="submit" onclick="mec_book_form_back_btn_cache(this, <?php echo esc_attr($uniqueid); ?>);" <?php echo ($step_skipped ? 'style="margin-left: 0;"' : ''); ?>>
                    <?php echo (($WC_status or $cart_status) ? esc_html__('Add to Cart', 'modern-events-calendar-lite') : ((!$total_ticket_prices and !$has_fees and !$has_variations && $check_free_tickets_booking) ? esc_html__('Submit', 'modern-events-calendar-lite') : esc_html__('Next', 'modern-events-calendar-lite').' '.'<svg xmlns="http://www.w3.org/2000/svg" width="13" height="10" viewBox="0 0 13 10"><path id="next-icon" d="M92.034,76.719l-.657.675,3.832,3.857H84v.937H95.208l-3.832,3.857.657.675,4.967-5Z" transform="translate(-84.001 -76.719)" fill="#07bbe9"/></svg>')); ?>
                </button>
            </div>
        </form>
        <?php

    }
}