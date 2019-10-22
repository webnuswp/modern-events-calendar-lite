<?php
/** no direct access **/
defined('MECEXEC') or die();

$event_id = $event->ID;
$tickets = isset($event->data->tickets) ? $event->data->tickets : array();
$dates = isset($event->dates) ? $event->dates : $event->date;

$occurrence = $dates[0]['start']['date'];

$default_ticket_number = 0;
if(count($tickets) == 1) $default_ticket_number = 1;

$book = $this->getBook();
$availability = $book->get_tickets_availability($event_id, $occurrence);

$date_format = (isset($settings['booking_date_format1']) and trim($settings['booking_date_format1'])) ? $settings['booking_date_format1'] : 'Y-m-d';
?>
<form id="mec_book_form<?php echo $uniqueid; ?>">
    <h4><?php _e('Book Event', 'modern-events-calendar-lite'); ?></h4>
    <div class="mec-book-first">
        <label for="mec_book_form_date"><?php _e('Date', 'modern-events-calendar-lite'); ?>: </label>
        <select name="book[date]" id="mec_book_form_date" onchange="mec_get_tickets_availability<?php echo $uniqueid; ?>(<?php echo $event_id; ?>, this.value);">
            <?php foreach($dates as $date): ?>
            <option value="<?php echo $date['start']['date'].':'.$date['end']['date']; ?>"><?php echo date_i18n($date_format, strtotime($date['start']['date'])).((strtotime($date['end']['date']) > strtotime($date['start']['date'])) ? ' - '.date_i18n($date_format, strtotime($date['end']['date'])) : ''); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="mec-event-tickets-list" id="mec_book_form_tickets_container<?php echo $uniqueid; ?>" data-total-booking-limit="<?php echo isset($availability['total']) ? $availability['total'] : '-1'; ?>">
        <?php foreach($tickets as $ticket_id=>$ticket): $ticket_limit = isset($availability[$ticket_id]) ? $availability[$ticket_id] : -1; if($ticket_limit === '0' and count($dates) <= 1) continue; ?>
        <div class="mec-event-ticket mec-event-ticket<?php echo $ticket_limit; ?>" id="mec_event_ticket<?php echo $ticket_id; ?>">
            <div class="mec-ticket-available-spots <?php echo ($ticket_limit == '0' ? 'mec-util-hidden' : ''); ?>">
                <span class="mec-event-ticket-name"><?php echo (isset($ticket['name']) ? $ticket['name'] : ''); ?></span>
                <span class="mec-event-ticket-price"><?php echo (isset($ticket['price_label']) ? $book->get_ticket_price_label($ticket, current_time('Y-m-d')) : ''); ?></span>
                <?php if(isset($ticket['description']) and trim($ticket['description'])): ?><p class="mec-event-ticket-description"><?php echo $ticket['description']; ?></p><?php endif; ?>
                <div>
                    <input type="number" class="mec-book-ticket-limit" name="book[tickets][<?php echo $ticket_id; ?>]" placeholder="<?php esc_attr_e('Count', 'modern-events-calendar-lite'); ?>" value="<?php echo $default_ticket_number; ?>" min="0" max="<?php echo ($ticket_limit != '-1' ? $ticket_limit : ''); ?>" onchange="mec_check_tickets_availability<?php echo $uniqueid; ?>(<?php echo $ticket_id; ?>, this.value);" />
                </div>
                <span class="mec-event-ticket-available"><?php echo sprintf(__('Available %s: <span>%s</span>', 'modern-events-calendar-lite'), $this->m('tickets', __('Tickets', 'modern-events-calendar-lite')), ($ticket['unlimited'] ? __('Unlimited', 'modern-events-calendar-lite') : ($ticket_limit != '-1' ? $ticket_limit : __('Unlimited', 'modern-events-calendar-lite')))); ?></span>
            </div>
            <div class="mec-ticket-unavailable-spots info-msg <?php echo ($ticket_limit == '0' ? '' : 'mec-util-hidden'); ?>">
                <?php echo sprintf(__('The %s ticket is sold out. You can try another ticket or another date.', 'modern-events-calendar-lite'), (isset($ticket['name']) ? '<strong>'.$ticket['name'].'</strong>' : '')); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if($this->get_recaptcha_status('booking')): ?><div class="mec-google-recaptcha"><div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $settings['google_recaptcha_sitekey']; ?>"></div></div><?php endif; ?>
    <input type="hidden" name="action" value="mec_book_form" />
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
    <input type="hidden" name="uniqueid" value="<?php echo $uniqueid; ?>" />
    <input type="hidden" name="step" value="1" />
    <?php wp_nonce_field('mec_book_form_'.$event_id); ?>
    <button type="submit"><?php _e('Next', 'modern-events-calendar-lite'); ?></button>
</form>