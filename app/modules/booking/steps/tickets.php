<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var $this MEC_main **/
/** @var $from_shortcode bool **/
/** @var $ticket_id integer **/

$event_id = $event->ID;

global $post;
if($post and $post->post_type == $this->get_main_post_type()) $translated_event_id = $post->ID;
else $translated_event_id = $event_id;

$tickets = isset($event->data->tickets) ? $event->data->tickets : array();
$dates = isset($event->dates) ? $event->dates : array($event->date);

$booking_options = get_post_meta($event_id, 'mec_booking', true);
if(!is_array($booking_options)) $booking_options = array();

// WC System
$WC_status = (isset($settings['wc_status']) and $settings['wc_status'] and class_exists('WooCommerce')) ? true : false;
$WC_booking_form = (isset($settings['wc_booking_form']) and $settings['wc_booking_form']) ? true : false;

if($ticket_id)
{
    $new_tickets = array();
    foreach($tickets as $t_id => $ticket)
    {
        if((int) $t_id === (int) $ticket_id)
        {
            $new_tickets[$t_id] = $ticket;
        }
    }

    if(count($new_tickets)) $tickets = $new_tickets;
}

$occurrence_time = isset($dates[0]['start']['timestamp']) ? $dates[0]['start']['timestamp'] : strtotime($dates[0]['start']['date']);

$default_ticket_number = 0;
if(count($tickets) == 1) $default_ticket_number = 1;

$book = $this->getBook();
$availability = $book->get_tickets_availability($event_id, $occurrence_time);

$date_format = (isset($ml_settings['booking_date_format1']) and trim($ml_settings['booking_date_format1'])) ? $ml_settings['booking_date_format1'] : 'Y-m-d';
if(isset($event->data->meta['mec_repeat_type']) and $event->data->meta['mec_repeat_type'] === 'custom_days') $date_format .= ' '.get_option('time_format');

$midnight_event = $this->is_midnight_event($event);

$book_all_occurrences = 0;
if(isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_booking']) and isset($event->data->meta['mec_booking']['bookings_all_occurrences'])) $book_all_occurrences = (int) $event->data->meta['mec_booking']['bookings_all_occurrences'];

// User Booking Limits
list($user_ticket_limit, $user_ticket_unlimited) = $book->get_user_booking_limit($event_id);

// Show Booking Form Interval
$show_booking_form_interval = (isset($settings['show_booking_form_interval'])) ? $settings['show_booking_form_interval'] : 0;
if(isset($booking_options['show_booking_form_interval']) and trim($booking_options['show_booking_form_interval']) != '') $show_booking_form_interval = $booking_options['show_booking_form_interval'];

if($show_booking_form_interval)
{
    $filtered_dates = array();
    foreach($dates as $date)
    {
        $date_diff = $this->date_diff(date('Y-m-d h:i a', current_time('timestamp', 0)), date('Y-m-d h:i a', $date['start']['timestamp']));
        if(isset($date_diff->days) and !$date_diff->invert)
        {
            $minute = $date_diff->days * 24 * 60;
            $minute += $date_diff->h * 60;
            $minute += $date_diff->i;

            if($minute > $show_booking_form_interval) continue;
        }

        $filtered_dates[] = $date;
    }

    $dates = $filtered_dates;
}

$available_spots = 0;
$total_spots = -1;
foreach($availability as $ticket_id=>$count)
{
    if(!is_numeric($ticket_id))
    {
        $total_spots = $count;
        continue;
    }

    if($count != '-1') $available_spots += $count;
    else
    {
        $available_spots = -1;
        break;
    }
}

if($total_spots > 0) $available_spots = min($available_spots, $total_spots);

// Date Selection Method
$date_selection = (isset($settings['booking_date_selection']) and trim($settings['booking_date_selection'])) ? $settings['booking_date_selection'] : 'dropdown';

// Ticket Style
$ticket_style = (isset($settings['booking_ticket_style']) and in_array($settings['booking_ticket_style'], array('default', 'row'))) ? $settings['booking_ticket_style'] : 'default';

// Modal Booking
$modal_booking = (isset($_GET['method']) and sanitize_text_field($_GET['method']) === 'mec-booking-modal');
wp_enqueue_script('mec-nice-select', $this->asset('js/jquery.nice-select.min.js'));
?>
<form id="mec_book_form<?php echo esc_attr($uniqueid); ?>" onsubmit="mec_book_form_submit(event, <?php echo esc_attr($uniqueid); ?>);">

    <?php if($display_progress_bar and (!$WC_status or ($WC_status and $WC_booking_form))): ?>
        <ul class="mec-booking-progress-bar">
            <li class="mec-booking-progress-bar-date-and-ticket mec-active"><?php esc_html_e('Date & Ticket', 'modern-events-calendar-lite'); ?></li>
            <li class="mec-booking-progress-bar-attendee-info"><?php esc_html_e('Attendee Info', 'modern-events-calendar-lite'); ?></li>
            <?php if($WC_status): ?>
                <li class="mec-booking-progress-bar-payment"><?php esc_html_e('Checkout', 'modern-events-calendar-lite'); ?></li>
            <?php else: ?>
                <li class="mec-booking-progress-bar-payment"><?php esc_html_e('Payment', 'modern-events-calendar-lite'); ?></li>
                <li class="mec-booking-progress-bar-complete"><?php esc_html_e('Complete', 'modern-events-calendar-lite'); ?></li>
            <?php endif; ?>
        </ul>
    <?php else: ?>
        <h4><?php echo ($from_shortcode ? $event->data->post->post_title : esc_html__('Book Event', 'modern-events-calendar-lite')); ?></h4>
    <?php endif; ?>

    <?php if(!$book_all_occurrences and count($dates) > 1): ?>
    <div class="mec-book-first">
        <?php if($date_selection == 'calendar'): $default_selected_datetime = $book->timestamp($dates[0]['start'], $dates[0]['end']); ?>

            <?php if(!$modal_booking): ?>
            <div class="mec-booking-calendar-wrapper" id="mec_booking_calendar_wrapper<?php echo esc_attr($uniqueid); ?>">
                <?php echo (new MEC_feature_bookingcalendar())->display_calendar($event, $uniqueid, $dates[0]['start']['date'], $default_selected_datetime); ?>
            </div>
            <input type="hidden" name="book[date]" id="mec_book_form_date<?php echo esc_attr($uniqueid); ?>" value="<?php echo esc_attr($default_selected_datetime); ?>" onchange="mec_get_tickets_availability<?php echo esc_attr($uniqueid); ?>(<?php echo esc_attr($event_id); ?>, this.value);">
            <?php else: ?>
            <div>
                <h6><?php echo MEC_kses::element($this->date_label($dates[0]['start'], $dates[0]['end'], $date_format, ' - ', false, (isset($dates[0]['allday']) ? $dates[0]['allday'] : 0))); ?></h6>
                <input type="hidden" name="book[date]" id="mec_book_form_date<?php echo esc_attr($uniqueid); ?>" value="<?php echo esc_attr($default_selected_datetime); ?>" onchange="mec_get_tickets_availability<?php echo esc_attr($uniqueid); ?>(<?php echo esc_attr($event_id); ?>, this.value);">
            </div>
            <?php endif; ?>

        <?php elseif($date_selection == 'checkboxes'): ?>
        <label><?php esc_html_e('Dates', 'modern-events-calendar-lite'); ?>: </label>
        <div class="mec-booking-dates-checkboxes">
            <?php foreach($dates as $date): ?>
            <label><input type="checkbox" name="book[date][]" value="<?php echo esc_attr($book->timestamp($date['start'], $date['end'])); ?>" onchange="mec_get_tickets_availability_multiple<?php echo esc_attr($uniqueid); ?>(<?php echo esc_attr($event_id); ?>);">&nbsp;<?php echo strip_tags($this->date_label($date['start'], $date['end'], $date_format, ' - ', false, (isset($date['allday']) ? $date['allday'] : 0))); ?></label>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <label for="mec_book_form_date<?php echo esc_attr($uniqueid); ?>"><?php esc_html_e('Date', 'modern-events-calendar-lite'); ?>: </label>
        <select class="mec-custom-nice-select" name="book[date]" id="mec_book_form_date<?php echo esc_attr($uniqueid); ?>" onchange="mec_get_tickets_availability<?php echo esc_attr($uniqueid); ?>(<?php echo esc_attr($event_id); ?>, this.value);">
            <?php foreach($dates as $date): ?>
            <option value="<?php echo esc_attr($book->timestamp($date['start'], $date['end'])); ?>">
                <?php echo strip_tags($this->date_label($date['start'], $date['end'], $date_format, ' - ', false, (isset($date['allday']) ? $date['allday'] : 0))); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </div>
    <?php elseif($book_all_occurrences): ?>
    <p class="mec-next-occ-booking-p">
        <?php esc_html_e('By booking this event you can attend all occurrences. Some of them are listed below but there might be more.', 'modern-events-calendar-lite'); ?>
        <div class="mec-next-occ-booking"><?php foreach($dates as $date) echo MEC_kses::element($this->date_label($date['start'], $date['end'], $date_format, ' - ', false))."<br>"; ?></div>
    </p>
    <input type="hidden" name="book[date]" id="mec_book_form_date<?php echo esc_attr($uniqueid); ?>" value="<?php echo esc_attr($book->timestamp($dates[0]['start'], $dates[0]['end'])); ?>">
    <?php else: ?>
        <?php if($from_shortcode) echo '<h4>'.MEC_kses::element($this->date_label($dates[0]['start'], $dates[0]['end'], $date_format, ' - ', false)).'</h4>'; ?>
        <input type="hidden" name="book[date]" id="mec_book_form_date<?php echo esc_attr($uniqueid); ?>" value="<?php echo esc_attr($book->timestamp($dates[0]['start'], $dates[0]['end'])); ?>">
    <?php endif; ?>

    <div class="mec-event-tickets-list <?php echo (!$book_all_occurrences and count($dates) > 1) ? '' : 'mec-sell-all-occurrences'; ?>" id="mec_book_form_tickets_container<?php echo esc_attr($uniqueid); ?>" data-total-booking-limit="<?php echo isset($availability['total']) ? esc_attr($availability['total']) : '-1'; ?>">
        <?php foreach($tickets as $ticket_id=>$ticket): $stop_selling = isset($availability['stop_selling_'.$ticket_id]) ? $availability['stop_selling_'.$ticket_id] : false; $ticket_limit = isset($availability[$ticket_id]) ? $availability[$ticket_id] : -1; if($ticket_limit === '0' and count($dates) <= 1) continue; ?>
        <div class="mec-event-ticket mec-event-ticket<?php echo esc_attr($ticket_limit); ?>" id="mec_event_ticket<?php echo esc_attr($ticket_id); ?>">

            <?php if($ticket_style === 'row'): ?>
            <div class="mec-ticket-style-row mec-ticket-available-spots <?php echo ($ticket_limit == '0' ? 'mec-util-hidden' : ''); ?>">
                <div class="mec-ticket-style-row-section-1">
                    <span class="mec-event-ticket-name"><?php echo (isset($ticket['name']) ? esc_html__($ticket['name'], 'modern-events-calendar-lite') : ''); ?></span>
                    <?php if(isset($ticket['description']) and trim($ticket['description'])): ?><p class="mec-event-ticket-description"><?php echo esc_html__($ticket['description'], 'modern-events-calendar-lite'); ?></p><?php endif; ?>
                </div>
                <div class="mec-ticket-style-row-section-2">
                    <?php
                        $price_label = isset($ticket['price_label']) ? $book->get_ticket_price_label($ticket, current_time('Y-m-d'), $event_id, $occurrence_time) : '';
                        $price_label = apply_filters('mec_filter_price_label', $price_label, $ticket, $event_id, $book);
                    ?>
                    <div class="mec-event-ticket-price"><?php echo MEC_kses::element($price_label); ?></div>
                    <div class="mec-event-ticket-available"><?php echo sprintf(esc_html__('Available %s: %s', 'modern-events-calendar-lite'), $this->m('tickets', esc_html__('Tickets', 'modern-events-calendar-lite')), (($ticket['unlimited'] and $ticket_limit == '-1') ? '<span>'.esc_html__('Unlimited', 'modern-events-calendar-lite').'</span>' : ($ticket_limit != '-1' ? '<span>'.$ticket_limit.'</span>' : '<span>'.esc_html__('Unlimited', 'modern-events-calendar-lite').'</span>'))); ?></div>
                </div>
                <div class="mec-ticket-style-row-section-3">
                    <?php if(!$user_ticket_unlimited and $user_ticket_limit == 1 and count($tickets) == 1): ?>
                    <input type="hidden" name="book[tickets][<?php echo esc_attr($ticket_id); ?>]" value="1" />
                    <p>
                        <?php esc_html_e('1 Ticket selected.', 'modern-events-calendar-lite'); ?>
                    </p>
                    <?php else: ?>
                    <div>
                        <input onkeydown="return event.keyCode !== 69" type="number" class="mec-book-ticket-limit" name="book[tickets][<?php echo esc_attr($ticket_id); ?>]" title="<?php esc_attr_e('Count', 'modern-events-calendar-lite'); ?>" placeholder="<?php esc_attr_e('Count', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($default_ticket_number); ?>" min="0" max="<?php echo ($ticket_limit != '-1' ? $ticket_limit : ''); ?>" onchange="mec_check_tickets_availability<?php echo esc_attr($uniqueid); ?>(<?php echo esc_attr($ticket_id); ?>, this.value);" />
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="mec-ticket-available-spots <?php echo ($ticket_limit == '0' ? 'mec-util-hidden' : ''); ?>">
                <span class="mec-event-ticket-name"><?php echo (isset($ticket['name']) ? esc_html__($ticket['name'], 'modern-events-calendar-lite') : ''); ?></span>
                <?php
                    $price_label = isset($ticket['price_label']) ? $book->get_ticket_price_label($ticket, current_time('Y-m-d'), $event_id, $occurrence_time) : '';
                    $price_label = apply_filters('mec_filter_price_label', $price_label, $ticket, $event_id, $book);
                ?>
                <span class="mec-event-ticket-price"><?php echo MEC_kses::element($price_label); ?></span>
                <?php if(isset($ticket['description']) and trim($ticket['description'])): ?><p class="mec-event-ticket-description"><?php echo esc_html__($ticket['description'], 'modern-events-calendar-lite'); ?></p><?php endif; ?>

                <?php if(!$user_ticket_unlimited and $user_ticket_limit == 1 and count($tickets) == 1): ?>
                <input type="hidden" name="book[tickets][<?php echo esc_attr($ticket_id); ?>]" value="1" />
                <p>
                    <?php esc_html_e('1 Ticket selected.', 'modern-events-calendar-lite'); ?>
                    <div class="mec-event-ticket-available"><?php echo sprintf(esc_html__('Available %s: %s', 'modern-events-calendar-lite'), $this->m('tickets', esc_html__('Tickets', 'modern-events-calendar-lite')), (($ticket['unlimited'] and $ticket_limit == '-1') ? '<span>'.esc_html__('Unlimited', 'modern-events-calendar-lite').'</span>' : ($ticket_limit != '-1' ? '<span>'.$ticket_limit.'</span>' : '<span>'.esc_html__('Unlimited', 'modern-events-calendar-lite').'</span>'))); ?></div>
                </p>
                <?php else: ?>
                <div>
                    <input onkeydown="return event.keyCode !== 69" type="number" class="mec-book-ticket-limit" name="book[tickets][<?php echo esc_attr($ticket_id); ?>]" title="<?php esc_attr_e('Count', 'modern-events-calendar-lite'); ?>" placeholder="<?php esc_attr_e('Count', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($default_ticket_number); ?>" min="0" max="<?php echo ($ticket_limit != '-1' ? $ticket_limit : ''); ?>" onchange="mec_check_tickets_availability<?php echo esc_attr($uniqueid); ?>(<?php echo esc_attr($ticket_id); ?>, this.value);" />
                </div>
                <span class="mec-event-ticket-available"><?php echo sprintf(esc_html__('Available %s: %s', 'modern-events-calendar-lite'), $this->m('tickets', esc_html__('Tickets', 'modern-events-calendar-lite')), (($ticket['unlimited'] and $ticket_limit == '-1') ? '<span>'.esc_html__('Unlimited', 'modern-events-calendar-lite').'</span>' : ($ticket_limit != '-1' ? '<span>'.$ticket_limit.'</span>' : '<span>'.esc_html__('Unlimited', 'modern-events-calendar-lite').'</span>'))); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php
                $str_replace = isset($ticket['name']) ? '<strong>'.esc_html($ticket['name']).'</strong>' : '';
                $ticket_message_sales = sprintf(esc_html__('The %s ticket sales has ended!', 'modern-events-calendar-lite'), $str_replace);
                $ticket_message_sold_out = sprintf(esc_html__('The %s ticket is sold out. You can try another ticket or another date.', 'modern-events-calendar-lite'), $str_replace);
                $ticket_message_sold_out_multiple = sprintf(esc_html__('The %s ticket is sold out for some of dates. You can try another ticket or another date.', 'modern-events-calendar-lite'), $str_replace);
            ?>
            <?php if(isset($stop_selling) and $stop_selling): ?>
            <div id="mec-ticket-message-<?php echo esc_attr($ticket_id); ?>" class="mec-ticket-unavailable-spots mec-error <?php echo (($ticket_limit != '0' or ($date_selection == 'calendar' and !$modal_booking)) ? 'mec-util-hidden' : ''); ?>">
                <div>
                    <?php echo MEC_kses::element($ticket_message_sales); ?>
                </div>
                <input type="hidden" id="mec-ticket-message-sales-<?php echo esc_attr($ticket_id); ?>" value="<?php echo esc_attr($ticket_message_sales); ?>" />
                <input type="hidden" id="mec-ticket-message-sold-out-<?php echo esc_attr($ticket_id); ?>" value="<?php echo esc_attr($ticket_message_sold_out); ?>" />
            </div>
            <?php else: ?>
            <div id="mec-ticket-message-<?php echo esc_attr($ticket_id); ?>" class="mec-ticket-unavailable-spots info-msg <?php echo ($ticket_limit == '0' ? '' : 'mec-util-hidden'); ?>">
                <div>
                    <?php echo ($date_selection == 'checkboxes' ? $ticket_message_sold_out_multiple : $ticket_message_sold_out); ?>
                </div>
                <?php if($ticket_limit == '0') do_action('mec_booking_sold_out', $event, $ticket, $ticket_id, $dates); ?>
                <input type="hidden" id="mec-ticket-message-sales-<?php echo esc_attr($ticket_id); ?>" value="<?php echo esc_attr($ticket_message_sales); ?>" />
                <input type="hidden" id="mec-ticket-message-sold-out-<?php echo esc_attr($ticket_id); ?>" value="<?php echo ($date_selection == 'checkboxes' ? esc_attr($ticket_message_sold_out_multiple) : esc_attr($ticket_message_sold_out)); ?>" />
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(($available_spots or (!$book_all_occurrences and count($dates) > 1)) and $this->get_recaptcha_status('booking')): ?><div class="mec-google-recaptcha"><div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr($settings['google_recaptcha_sitekey']); ?>"></div></div><?php endif; ?>
    <input type="hidden" name="lang" value="<?php echo esc_attr($this->get_current_lang_code()); ?>" />
    <input type="hidden" name="action" value="mec_book_form" />
    <input type="hidden" name="event_id" value="<?php echo esc_attr($event_id); ?>" />
    <input type="hidden" name="translated_event_id" value="<?php echo esc_attr($translated_event_id); ?>" />
    <input type="hidden" name="uniqueid" value="<?php echo esc_attr($uniqueid); ?>" />
    <input type="hidden" name="step" value="1" />
    <input type="hidden" name="do_skip" value="<?php echo ($do_skip ? 1 : 0); ?>" />
    <?php wp_nonce_field('mec_book_form_'.$event_id); ?>
    <button id="mec-book-form-btn-step-1" style="<?php if($available_spots == '0') echo 'display: none;'; ?>" type="submit" onclick="mec_book_form_back_btn_cache(this, <?php echo esc_attr($uniqueid); ?>);"><?php echo (($WC_status and !$WC_booking_form) ? esc_html__('Add to Cart', 'modern-events-calendar-lite') : esc_html__('Next', 'modern-events-calendar-lite')); ?></button>

    <?php if(isset($settings['booking_display_total_tickets']) and $settings['booking_display_total_tickets']): ?>
    <span id="mec_booking_quantity_wrapper_<?php echo esc_attr($uniqueid); ?>"><?php echo sprintf(esc_html__('Total: %s', 'modern-events-calendar-lite'), '<span class="mec-booking-quantity-holder">'.((!$user_ticket_unlimited and $user_ticket_limit == 1 and count($tickets) == 1) ? 1 : 0).'</span>'); ?></span>
    <?php endif; ?>
</form>
<?php if($from_shortcode): ?>
<style>.nice-select{-webkit-tap-highlight-color:transparent;background-color:#fff;border-radius:5px;border:solid 1px #e8e8e8;box-sizing:border-box;clear:both;cursor:pointer;display:block;float:left;font-family:inherit;font-size:14px;font-weight:400;height:42px;line-height:40px;outline:0;padding-left:18px;padding-right:30px;position:relative;text-align:left!important;-webkit-transition:all .2s ease-in-out;transition:all .2s ease-in-out;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;white-space:nowrap;width:auto}.nice-select:hover{border-color:#dbdbdb}.nice-select.open,.nice-select:active,.nice-select:focus{border-color:#999}.nice-select:after{border-bottom:2px solid #999;border-right:2px solid #999;content:'';display:block;height:5px;margin-top:-4px;pointer-events:none;position:absolute;right:12px;top:50%;-webkit-transform-origin:66% 66%;-ms-transform-origin:66% 66%;transform-origin:66% 66%;-webkit-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg);-webkit-transition:all .15s ease-in-out;transition:all .15s ease-in-out;width:5px}.nice-select.open:after{-webkit-transform:rotate(-135deg);-ms-transform:rotate(-135deg);transform:rotate(-135deg)}.nice-select.open .list{opacity:1;pointer-events:auto;-webkit-transform:scale(1) translateY(0);-ms-transform:scale(1) translateY(0);transform:scale(1) translateY(0)}.nice-select.disabled{border-color:#ededed;color:#999;pointer-events:none}.nice-select.disabled:after{border-color:#ccc}.nice-select.wide{width:100%}.nice-select.wide .list{left:0!important;right:0!important}.nice-select.right{float:right}.nice-select.right .list{left:auto;right:0}.nice-select.small{font-size:12px;height:36px;line-height:34px}.nice-select.small:after{height:4px;width:4px}.nice-select.small .option{line-height:34px;min-height:34px}.nice-select .list{background-color:#fff;border-radius:5px;box-shadow:0 0 0 1px rgba(68,68,68,.11);box-sizing:border-box;margin-top:4px;opacity:0;overflow:hidden;padding:0;pointer-events:none;position:absolute;top:100%;left:0;-webkit-transform-origin:50% 0;-ms-transform-origin:50% 0;transform-origin:50% 0;-webkit-transform:scale(.75) translateY(-21px);-ms-transform:scale(.75) translateY(-21px);transform:scale(.75) translateY(-21px);-webkit-transition:all .2s cubic-bezier(.5,0,0,1.25),opacity .15s ease-out;transition:all .2s cubic-bezier(.5,0,0,1.25),opacity .15s ease-out;z-index:9}.nice-select .list:hover .option:not(:hover){background-color:transparent!important}.nice-select .option{cursor:pointer;font-weight:400;line-height:40px;list-style:none;min-height:40px;outline:0;padding-left:18px;padding-right:29px;text-align:left;-webkit-transition:all .2s;transition:all .2s}.nice-select .option.focus,.nice-select .option.selected.focus,.nice-select .option:hover{background-color:#f6f6f6}.nice-select .option.selected{font-weight:700}.nice-select .option.disabled{background-color:transparent;color:#999;cursor:default}.no-csspointerevents .nice-select .list{display:none}.no-csspointerevents .nice-select.open .list{display:block}</style>
<?php wp_add_inline_script('mec-nice-select', '
jQuery(document).ready(function()
{
    if(jQuery(".mec-booking-shortcode").length < 0) return;

    // Events
    jQuery(".mec-booking-shortcode").find("select").niceSelect();
});'); ?>
<?php endif; ?>