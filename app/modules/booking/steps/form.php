<?php
/** no direct access **/
defined('MECEXEC') or die();

$event_id = $event->ID;
$reg_fields = $this->main->get_reg_fields($event_id);

$date_ex = explode(':', $date);
$occurrence = $date_ex[0];

$ticket_variations = $this->main->ticket_variations($event_id);
$event_tickets = isset($event->data->tickets) ? $event->data->tickets : array();

$current_user = wp_get_current_user();
$first_for_all = (!isset($this->settings['booking_first_for_all']) or (isset($this->settings['booking_first_for_all']) and $this->settings['booking_first_for_all'] == 1)) ? true : false;

$mec_email = false;
$mec_name = false;
foreach($reg_fields as $field)
{
	if(isset($field['type']))
	{
		if($field['type'] == 'mec_email') $mec_email = true;
		if($field['type'] == 'name') $mec_name = true;
	}
	else
	{
		break;
	}
}

if(!$mec_name)
{
    $reg_fields[] = array(
        'mandatory' => '0',
		'type'      => 'name',
		'label'     => esc_html__( 'Name', 'modern-events-calendar-lite' ),
    );
}

if(!$mec_email)
{
    $reg_fields[] = array(
        'mandatory' => '0',
        'type'      => 'mec_email',
        'label'     => esc_html__( 'Email', 'modern-events-calendar-lite' ),
    );
}
?>

<form id="mec_book_form<?php echo $uniqueid; ?>" class="mec-booking-form-container row" novalidate="novalidate" enctype="multipart/form-data" method="post">
    <h4><?php echo apply_filters('mec-attendees-title', __('Attendees Form', 'modern-events-calendar-lite')) ?></h4>
    <ul class="mec-book-tickets-container">

        <?php $j = 0; foreach($tickets as $ticket_id=>$count): if(!$count) continue; $ticket = $event_tickets[$ticket_id]; for($i = 1; $i <= $count; $i++): $j++; ?>
        <li class="mec-book-ticket-container <?php echo (($j > 1 and $first_for_all) ? 'mec-util-hidden' : ''); ?>">
            <?php if (!empty($ticket['name']) || !empty($this->book->get_ticket_price_label($ticket, current_time('Y-m-d')))) : ?>
            <h4 class="col-md-12">
                <span class="mec-ticket-name"><?php echo $ticket['name']; ?></span>
                <span class="mec-ticket-price"><?php echo $this->book->get_ticket_price_label($ticket, current_time('Y-m-d')); ?></span>
            </h4>
            <?php endif; ?>

            <!-- Custom fields -->
            <?php if(count($reg_fields)): foreach($reg_fields as $reg_field_id=>$reg_field): if(!is_numeric($reg_field_id) or !isset($reg_field['type'])) continue; ?>


            <?php $reg_field_name = strtolower( str_replace([' ',',',':','"',"'"], '_', $reg_field['label']) ); ?>

            <div class="mec-book-reg-field-<?php echo $reg_field['type']; ?> <?php echo ((isset($reg_field['mandatory']) and $reg_field['mandatory']) ? 'mec-reg-mandatory' : ''); ?><?php
            if ( isset($reg_field['inline']) && $reg_field['inline'] == 'enable') {
                echo ' col-md-6'; } else { echo ' col-md-12'; }
        ?>" data-ticket-id="<?php echo $j; ?>" data-field-id="<?php echo $reg_field_id; ?>">
                <?php if(isset($reg_field['label']) and $reg_field['type'] != 'agreement' &&  $reg_field['type'] != 'name' && $reg_field['type'] != 'mec_email' ): ?><label for="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>"><?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?><?php echo ((isset($reg_field['mandatory']) and $reg_field['mandatory']) ? '<span class="wbmec-mandatory">*</span>' : ''); ?></label><?php endif; ?>

                <?php /** Name **/ if($reg_field['type'] == 'name'): ?>
                <?php $reg_field['label'] = ($reg_field['label']) ? $reg_field['label'] : 'Name'; ?>
                <label for="mec_book_reg_field_name<?php echo $reg_field_id; ?>"><?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?><span class="wbmec-mandatory">*</span></label>
                <input id="mec_book_reg_field_name<?php echo $reg_field_id; ?>" type="text" name="book[tickets][<?php echo $j; ?>][name]" value="<?php echo trim((isset($current_user->user_firstname) ? $current_user->user_firstname : '').' '.(isset($current_user->user_lastname) ? $current_user->user_lastname : '')); ?>" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" required />

                <?php /** MEC Email **/ elseif($reg_field['type'] == 'mec_email'): ?>
                <?php $reg_field['label'] = ($reg_field['label']) ? $reg_field['label'] : 'Email'; ?>
                <label for="mec_book_reg_field_email<?php echo $reg_field_id; ?>"><?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?><span class="wbmec-mandatory">*</span></label>
                <input id="mec_book_reg_field_email<?php echo $reg_field_id; ?>" type="email" name="book[tickets][<?php echo $j; ?>][email]" value="<?php echo isset($current_user->user_email) ? $current_user->user_email : ''; ?>" placeholder="<?php _e('Email', 'modern-events-calendar-lite'); ?>" required />

                <?php /** Text **/ elseif($reg_field['type'] == 'text'): ?>
                <input id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" type="text" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" value="" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" <?php if(isset($reg_field['mandatory']) and $reg_field['mandatory']) echo 'required'; ?> />

                <?php /** Date **/ elseif($reg_field['type'] == 'date'): ?>
                <input id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" type="date" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" value="" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" <?php if(isset($reg_field['mandatory']) and $reg_field['mandatory']) echo 'required'; ?> min="1970-01-01" max="2099-12-31" />

                <?php /** File **/ elseif($reg_field['type'] == 'file'): ?>
                <input id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" type="file" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" value="" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" <?php if(isset($reg_field['mandatory']) and $reg_field['mandatory']) echo 'required'; ?> />

                <?php /** Email **/ elseif($reg_field['type'] == 'email'): ?>
                <input id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" type="email" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" value="" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" <?php if(isset($reg_field['mandatory']) and $reg_field['mandatory']) echo 'required'; ?> />

                <?php /** Tel **/ elseif($reg_field['type'] == 'tel'): ?>
                <input id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" oninput="this.value=this.value.replace(/(?![0-9])./gmi,'')" type="tel" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" value="" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" <?php if(isset($reg_field['mandatory']) and $reg_field['mandatory']) echo 'required'; ?> />

                <?php /** Textarea **/ elseif($reg_field['type'] == 'textarea'): ?>
                <textarea id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" <?php if(isset($reg_field['mandatory']) and $reg_field['mandatory']) echo 'required'; ?>></textarea>

                <?php /** Dropdown **/ elseif($reg_field['type'] == 'select'): ?>
                <select id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" placeholder="<?php _e($reg_field['label'], 'modern-events-calendar-lite'); ?>" <?php if(isset($reg_field['mandatory']) and $reg_field['mandatory']) echo 'required'; ?>>
                    <?php foreach($reg_field['options'] as $reg_field_option): ?>
                    <option value="<?php esc_attr_e($reg_field_option['label'], 'modern-events-calendar-lite'); ?>"><?php _e($reg_field_option['label'], 'modern-events-calendar-lite'); ?></option>
                    <?php endforeach; ?>
                </select>

                <?php /** Radio **/ elseif($reg_field['type'] == 'radio'): ?>
                <?php foreach($reg_field['options'] as $reg_field_option): ?>
                <label for="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id.'_'.strtolower(str_replace(' ', '_', $reg_field_option['label'])); ?>">
                    <input type="radio" id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id.'_'.strtolower(str_replace(' ', '_', $reg_field_option['label'])); ?>" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" value="<?php _e($reg_field_option['label'], 'modern-events-calendar-lite'); ?>" />
                    <?php _e($reg_field_option['label'], 'modern-events-calendar-lite'); ?>
                </label>
                <?php endforeach; ?>

                <?php /** Checkbox **/ elseif($reg_field['type'] == 'checkbox'): ?>
                <?php foreach($reg_field['options'] as $reg_field_option): ?>
                <label for="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id.'_'.strtolower(str_replace(' ', '_', $reg_field_option['label'])); ?>">
                    <input type="checkbox" id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id.'_'.strtolower(str_replace(' ', '_', $reg_field_option['label'])); ?>" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>][]" value="<?php _e($reg_field_option['label'], 'modern-events-calendar-lite'); ?>" />
                    <?php _e($reg_field_option['label'], 'modern-events-calendar-lite'); ?>
                </label>
                <?php endforeach; ?>

                <?php /** Agreement **/ elseif($reg_field['type'] == 'agreement'): ?>
                <label for="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>">
                    <input type="checkbox" id="mec_book_reg_field_reg<?php echo $j.'_'.$reg_field_id; ?>" name="book[tickets][<?php echo $j; ?>][reg][<?php echo $reg_field_id; ?>]" value="1" <?php echo (!isset($reg_field['status']) or (isset($reg_field['status']) and $reg_field['status'] == 'checked')) ? 'checked="checked"' : ''; ?> />
                    <?php echo ((isset($reg_field['mandatory']) and $reg_field['mandatory']) ? '<span class="wbmec-mandatory">*</span>' : ''); ?>
                    <?php echo sprintf(__(stripslashes($reg_field['label']), 'modern-events-calendar-lite'), '<a href="'.get_the_permalink($reg_field['page']).'" target="_blank">'.get_the_title($reg_field['page']).'</a>'); ?>
                </label>

                <?php /** Paragraph **/ elseif($reg_field['type'] == 'p'): ?>
                <?php $firstCharacter = substr(stripslashes($reg_field['content']), 0, 1);
                if ($firstCharacter == '[') : ?>
                <?php echo do_shortcode(stripslashes($reg_field['content'])); ?>
                <?php else : ?>
                <p><?php _e(stripslashes($reg_field['content']), 'modern-events-calendar-lite'); ?></p>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; endif; ?>

            <!-- Ticket Variations -->
            <?php if(isset($this->settings['ticket_variations_status']) and $this->settings['ticket_variations_status'] and count($ticket_variations)): foreach($ticket_variations as $ticket_variation_id=>$ticket_variation): if(!is_numeric($ticket_variation_id) or !isset($ticket_variation['title']) or (isset($ticket_variation['title']) and !trim($ticket_variation['title']))) continue; ?>
            <div class="col-md-12">
                <div class="mec-book-ticket-variation" data-ticket-id="<?php echo $j; ?>" data-ticket-variation-id="<?php echo $ticket_variation_id; ?>">
                    <h5><span class="mec-ticket-variation-name"><?php echo $ticket_variation['title']; ?></span><span class="mec-ticket-variation-price"><?php echo $this->main->render_price($ticket_variation['price']); ?></span></h5>
                    <input type="number" min="0" max="<?php echo ((is_numeric($ticket_variation['max']) and $ticket_variation['max']) ? $ticket_variation['max'] : 1); ?>" name="book[tickets][<?php echo $j; ?>][variations][<?php echo $ticket_variation_id; ?>]" onchange="mec_check_variation_min_max<?php echo $uniqueid; ?>(this);">
                </div>
            </div>
            <?php endforeach; endif; ?>

            <input type="hidden" name="book[tickets][<?php echo $j; ?>][id]" value="<?php echo $ticket_id; ?>" />
            <input type="hidden" name="book[tickets][<?php echo $j; ?>][count]" value="1" />
        </li>
        <?php endfor; endforeach; ?>

        <?php if($j > 1 and $first_for_all): ?>
        <li class="mec-first-for-all-wrapper">
            <label class="mec-fill-attendees">
                <input type="hidden" name="book[first_for_all]" value="0" />
                <input type="checkbox" name="book[first_for_all]" value="1" checked="checked" class="mec_book_first_for_all" id="mec_book_first_for_all<?php echo $uniqueid; ?>" onchange="mec_toggle_first_for_all<?php echo $uniqueid; ?>();" />
                <label for="pages1" onclick="mec_label_first_for_all<?php echo $uniqueid; ?>();" class="wn-checkbox-label"></label>
                <?php _e("Fill other attendees information like the first form.", 'modern-events-calendar-lite'); ?>
            </label>
        </li>
        <?php endif; ?>

    </ul>
    <input type="hidden" name="book[date]" value="<?php echo $date; ?>" />
    <input type="hidden" name="book[event_id]" value="<?php echo $event_id; ?>" />
    <input type="hidden" name="action" value="mec_book_form" />
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
    <input type="hidden" name="uniqueid" value="<?php echo $uniqueid; ?>" />
    <input type="hidden" name="step" value="2" />
    <?php wp_nonce_field('mec_book_form_'.$event_id); ?>
    <button type="submit"><?php _e('Next', 'modern-events-calendar-lite'); ?></button>
</form>
