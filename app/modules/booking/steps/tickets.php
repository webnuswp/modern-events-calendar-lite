<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var $this MEC_main **/

$event_id = $event->ID;
$tickets = isset($event->data->tickets) ? $event->data->tickets : array();
$dates = isset($event->dates) ? $event->dates : array($event->date);

$occurrence_time = isset($dates[0]['start']['timestamp']) ? $dates[0]['start']['timestamp'] : strtotime($dates[0]['start']['date']);

$default_ticket_number = 0;
if(count($tickets) == 1) $default_ticket_number = 1;

$book = $this->getBook();
$availability = $book->get_tickets_availability($event_id, $occurrence_time);

$date_format = (isset($settings['booking_date_format1']) and trim($settings['booking_date_format1'])) ? $settings['booking_date_format1'] : 'Y-m-d';
if(isset($event->data->meta['mec_repeat_type']) and $event->data->meta['mec_repeat_type'] === 'custom_days') $date_format .= ' '.get_option('time_format');

$midnight_event = $this->is_midnight_event($event);

$book_all_occurrences = 0;
if(isset($event->data) and isset($event->data->meta) and isset($event->data->meta['mec_booking']) and isset($event->data->meta['mec_booking']['bookings_all_occurrences'])) $book_all_occurrences = (int) $event->data->meta['mec_booking']['bookings_all_occurrences'];

// User Booking Limits
list($user_ticket_limit, $user_ticket_unlimited) = $book->get_user_booking_limit($event_id);
?>

<form id="mec_book_form<?php echo $uniqueid; ?>" onsubmit="mec_book_form_submit(event, <?php echo $uniqueid; ?>);">
    <h4><?php _e('Book Event', 'modern-events-calendar-lite'); ?></h4>

    <?php if(!$book_all_occurrences and count($dates) > 1): ?>
    <div class="mec-book-first">
        <label for="mec_book_form_date<?php echo $uniqueid; ?>"><?php _e('Date', 'modern-events-calendar-lite'); ?>: </label>
        <select name="book[date]" id="mec_book_form_date<?php echo $uniqueid; ?>" onchange="mec_get_tickets_availability<?php echo $uniqueid; ?>(<?php echo $event_id; ?>, this.value);">
            <?php foreach($dates as $date): ?>
            <option value="<?php echo $book->timestamp($date['start'], $date['end']); ?>">
                <?php echo strip_tags($this->date_label($date['start'], $date['end'], $date_format)); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php else: ?>
    <input type="hidden" name="book[date]" value="<?php echo $book->timestamp($dates[0]['start'], $dates[0]['end']); ?>">
    <?php endif; ?>
    
    <div class="mec-event-tickets-list" id="mec_book_form_tickets_container<?php echo $uniqueid; ?>" data-total-booking-limit="<?php echo isset($availability['total']) ? $availability['total'] : '-1'; ?>">
        <?php foreach($tickets as $ticket_id=>$ticket): $stop_selling = isset($availability['stop_selling_'.$ticket_id]) ? $availability['stop_selling_'.$ticket_id] : false; $ticket_limit = isset($availability[$ticket_id]) ? $availability[$ticket_id] : -1; if($ticket_limit === '0' and count($dates) <= 1) continue; ?>
        <div class="mec-event-ticket mec-event-ticket<?php echo $ticket_limit; ?>" id="mec_event_ticket<?php echo $ticket_id; ?>">
            <div class="mec-ticket-available-spots <?php echo ($ticket_limit == '0' ? 'mec-util-hidden' : ''); ?>">
                <span class="mec-event-ticket-name"><?php echo (isset($ticket['name']) ? __($ticket['name'], 'modern-events-calendar-lite') : ''); ?></span>
                <span class="mec-event-ticket-price"><?php echo (isset($ticket['price_label']) ? $book->get_ticket_price_label($ticket, current_time('Y-m-d')) : ''); ?></span>
                <?php if(isset($ticket['description']) and trim($ticket['description'])): ?><p class="mec-event-ticket-description"><?php echo __($ticket['description'], 'modern-events-calendar-lite'); ?></p><?php endif; ?>

                <?php if(!$user_ticket_unlimited and $user_ticket_limit == 1 and count($tickets) == 1): ?>
                    <input type="hidden" name="book[tickets][<?php echo $ticket_id; ?>]" value="1" />
                    <p><?php _e('1 Ticket selected.', 'modern-events-calendar-lite'); ?></p>
                <?php else: ?>
                <div>
                    <input type="number" class="mec-book-ticket-limit" name="book[tickets][<?php echo $ticket_id; ?>]" title="<?php esc_attr_e('Count', 'modern-events-calendar-lite'); ?>" placeholder="<?php esc_attr_e('Count', 'modern-events-calendar-lite'); ?>" value="<?php echo $default_ticket_number; ?>" min="0" max="<?php echo ($ticket_limit != '-1' ? $ticket_limit : ''); ?>" onchange="mec_check_tickets_availability<?php echo $uniqueid; ?>(<?php echo $ticket_id; ?>, this.value);" />
                </div>
                <span class="mec-event-ticket-available"><?php echo sprintf(__('Available %s: <span>%s</span>', 'modern-events-calendar-lite'), $this->m('tickets', __('Tickets', 'modern-events-calendar-lite')), ($ticket['unlimited'] ? __('Unlimited', 'modern-events-calendar-lite') : ($ticket_limit != '-1' ? $ticket_limit : __('Unlimited', 'modern-events-calendar-lite')))); ?></span>
                <?php endif; ?>
            </div>
            <?php
                $str_replace = isset($ticket['name']) ? '<strong>'.$ticket['name'].'</strong>' : '';
                $ticket_message_sales =  sprintf(__('The %s ticket sales has stopped!', 'modern-events-calendar-lite'), $str_replace);
                $ticket_message_sold_out =  sprintf(__('The %s ticket is sold out. You can try another ticket or another date.', 'modern-events-calendar-lite'), $str_replace);
            ?>
            <?php if(isset($stop_selling) and $stop_selling): ?>
            <div id="mec-ticket-message-<?php echo $ticket_id; ?>" class="mec-ticket-unavailable-spots mec-error <?php echo ($ticket_limit == '0' ? '' : 'mec-util-hidden'); ?>">
                <div>
                    <?php echo $ticket_message_sales; ?>
                </div>
                <input type="hidden" id="mec-ticket-message-sales-<?php echo $ticket_id; ?>" value="<?php echo $ticket_message_sales; ?>" />
                <input type="hidden" id="mec-ticket-message-sold-out-<?php echo $ticket_id; ?>" value="<?php echo $ticket_message_sold_out; ?>" />
            </div>
            <?php else: ?>
            <div id="mec-ticket-message-<?php echo $ticket_id; ?>" class="mec-ticket-unavailable-spots info-msg <?php echo ($ticket_limit == '0' ? '' : 'mec-util-hidden'); ?>">
                <div>
                    <?php echo $ticket_message_sold_out; ?>
                </div>
                <input type="hidden" id="mec-ticket-message-sales-<?php echo $ticket_id; ?>" value="<?php echo $ticket_message_sales; ?>" />
                <input type="hidden" id="mec-ticket-message-sold-out-<?php echo $ticket_id; ?>" value="<?php echo $ticket_message_sold_out; ?>" />
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if($this->get_recaptcha_status('booking')): ?><div class="mec-google-recaptcha"><div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $settings['google_recaptcha_sitekey']; ?>"></div></div><?php endif; ?>
    <input type="hidden" name="action" value="mec_book_form" />
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
    <input type="hidden" name="uniqueid" value="<?php echo $uniqueid; ?>" />
    <input type="hidden" name="step" value="1" />
    <?php wp_nonce_field('mec_book_form_'.$event_id); ?>
    <button id="mec-book-form-btn-step-1" type="submit" onclick="mec_book_form_back_btn_cache(this, <?php echo $uniqueid; ?>);"><?php _e('Next', 'modern-events-calendar-lite'); ?></button>
</form>