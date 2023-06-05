<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_mec $this */

$multilingual = $this->main->is_multilingual();
$locale = $this->main->get_backend_active_locale();

$settings = $this->main->get_settings();
$ml_settings = $this->main->get_ml_settings(NULL, $locale);

$fees = isset($settings['fees']) ? $settings['fees'] : array();
$ticket_variations = isset($settings['ticket_variations']) ? $settings['ticket_variations'] : array();

// WordPress Pages
$pages = get_pages();

// User Roles
$roles = array_reverse(wp_roles()->roles);

$bfixed_fields = $this->main->get_bfixed_fields();
if(!is_array($bfixed_fields)) $bfixed_fields = array();

// Booking form
$mec_email  = false;
$mec_name   = false;

$reg_fields = $this->main->get_reg_fields();
if(!is_array($reg_fields)) $reg_fields = array();

foreach($reg_fields as $field)
{
	if(isset($field['type']))
	{
		if($field['type'] == 'name') $mec_name = true;
		if($field['type'] == 'mec_email') $mec_email = true;
	}
	else break;
}

if(!$mec_name)
{
	array_unshift(
		$reg_fields,
		array(
			'mandatory' => '0',
			'type'      => 'name',
			'label'     => esc_html__('Name', 'modern-events-calendar-lite' ),
        )
	);
}

if(!$mec_email)
{
	array_unshift(
		$reg_fields,
		array(
			'mandatory' => '0',
			'type'      => 'mec_email',
			'label'     => esc_html__('Email', 'modern-events-calendar-lite' ),
        )
	);
}

// Payment Gateways
$gateways = $this->main->get_gateways();
$gateways_options = $this->main->get_gateways_options();
?>
<div class="wns-be-container wns-be-container-sticky">
    <div id="wns-be-infobar">
        <div class="mec-search-settings-wrap">
            <i class="mec-sl-magnifier"></i>
            <input id="mec-search-settings" type="text" placeholder="<?php esc_html_e('Search...', 'modern-events-calendar-lite' ); ?>">
        </div>
        <a id="" class="dpr-btn dpr-save-btn"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite' ); ?></a>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('booking'); ?>
    </div>

    <div class="wns-be-main">
        <div id="wns-be-notification"></div>
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">

                    <form id="mec_booking_form">

                        <div id="booking_option" class="mec-options-fields active">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Booking', 'modern-events-calendar-lite' ); ?></h4>

                            <?php if(!$this->main->getPRO()): ?>
                            <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite' ), '<a href="'.esc_url($this->main->get_pro_link()).'" target="_blank">'.esc_html__('Pro version of Modern Events Calendar', 'modern-events-calendar-lite' ).'</a>'); ?></div>
                            <?php else: ?>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][booking_status]" value="0" />
                                    <input onchange="jQuery('#mec_booking_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][booking_status]" <?php if(isset($settings['booking_status']) and $settings['booking_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable booking module', 'modern-events-calendar-lite' ); ?>
                                    <p><?php esc_attr_e("After enabling and saving the settings, reloading the page will add 'payment Gateways' to the settings and a new menu item on the Dashboard", 'modern-events-calendar-lite' ); ?></p>
                                </label>
                            </div>
                            <div id="mec_booking_container_toggle" class="<?php if((isset($settings['booking_status']) and !$settings['booking_status']) or !isset($settings['booking_status'])) echo 'mec-util-hidden'; ?>">
                                <h5 class="title"><?php esc_html_e('Date Options', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_date_format1"><?php esc_html_e('Date Format', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <input type="text" id="mec_settings_booking_date_format1" name="mec[settings][booking_date_format1]" value="<?php echo ((isset($ml_settings['booking_date_format1']) and trim($ml_settings['booking_date_format1']) != '') ? $ml_settings['booking_date_format1'] : 'Y-m-d'); ?>" />
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Date Format', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("Specify the date format of the event's date on the booking module.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_maximum_dates"><?php esc_html_e('Maximum Dates', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <input type="number" id="mec_settings_booking_maximum_dates" name="mec[settings][booking_maximum_dates]" value="<?php echo ((isset($settings['booking_maximum_dates']) and trim($settings['booking_maximum_dates']) != '') ? $settings['booking_maximum_dates'] : '6'); ?>" placeholder="<?php esc_attr_e('Default is 6', 'modern-events-calendar-lite' ); ?>" min="1" max="100" step="1" />
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Maximum Dates', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("Specify the number of dates available in the date selection dropdown menu for recurring events.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="title"><?php esc_html_e('Date Selection Options', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_date_selection"><?php esc_html_e('Date Selection', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_date_selection" name="mec[settings][booking_date_selection]">
                                            <option value="dropdown" <?php echo ((!isset($settings['booking_date_selection']) or (isset($settings['booking_date_selection']) and $settings['booking_date_selection'] == 'dropdown')) ? 'selected' : ''); ?>><?php esc_html_e('Dropdown', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="calendar" <?php echo ((isset($settings['booking_date_selection']) and $settings['booking_date_selection'] == 'calendar') ? 'selected' : ''); ?>><?php esc_html_e('Calendar', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="checkboxes" <?php echo ((isset($settings['booking_date_selection']) and $settings['booking_date_selection'] == 'checkboxes') ? 'selected' : ''); ?>><?php esc_html_e('Checkboxes', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="express-calendar" <?php echo ((isset($settings['booking_date_selection']) and $settings['booking_date_selection'] == 'express-calendar') ? 'selected' : ''); ?>><?php esc_html_e('Express Calendar', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Date Selection', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("Specify the type of date selection field in the booking module.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_date_selection_per_event"><?php esc_html_e('Date Selection Per Event', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_date_selection_per_event" name="mec[settings][booking_date_selection_per_event]">
                                            <option value="0" <?php echo ((!isset($settings['booking_date_selection_per_event']) or (isset($settings['booking_date_selection_per_event']) and $settings['booking_date_selection_per_event'] == 0)) ? 'selected' : ''); ?>><?php esc_html_e('No', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="1" <?php echo ((isset($settings['booking_date_selection_per_event']) and $settings['booking_date_selection_per_event'] == 1) ? 'selected' : ''); ?>><?php esc_html_e('Yes', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Date Selection Per Event', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("If enabled, you can change the date selection method per event.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="title"><?php esc_html_e('Ticket Options', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label for="mec_settings_booking_family_ticket">
                                        <input type="hidden" name="mec[settings][booking_family_ticket]" value="0" />
                                        <input type="checkbox" name="mec[settings][booking_family_ticket]" id="mec_settings_booking_family_ticket" <?php echo ((isset($settings['booking_family_ticket']) and $settings['booking_family_ticket'] == '1') ? 'checked="checked"' : ''); ?> value="1" />
                                        <?php esc_html_e('Family Tickets', 'modern-events-calendar-lite' ); ?>
                                    </label>
                                    <span class="mec-tooltip">
                                        <div class="box right">
                                            <h5 class="title"><?php esc_html_e('Family Tickets', 'modern-events-calendar-lite' ); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("By default all tickets are for selling one seat. By enabling family tickets you can create tickets with higher seats.", 'modern-events-calendar-lite' ); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <h5 class="title"><?php esc_html_e('Interval Options', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_show_booking_form_interval"><?php esc_html_e('Show Booking Form Interval', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <input type="number" id="mec_settings_show_booking_form_interval" name="mec[settings][show_booking_form_interval]" value="<?php echo ((isset($settings['show_booking_form_interval']) and trim($settings['show_booking_form_interval']) != '0') ? $settings['show_booking_form_interval'] : '0'); ?>" placeholder="<?php esc_attr_e('Minutes (e.g 5)', 'modern-events-calendar-lite' ); ?>" />
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Show Booking Form Interval', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("You can show the booking form only at certain times before the event starts. If you set this option to 30 then the booking form will open only 30 minutes before starting the event! One day is 1440 minutes.", 'modern-events-calendar-lite' ); ?></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Booking Cancellation', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_cancellation_period_from"><?php esc_html_e('Cancellation Period', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <div class="cancellation-period-box">
                                            <input type="number" id="mec_settings_cancellation_period_from" name="mec[settings][cancellation_period_from]" value="<?php echo ((isset($settings['cancellation_period_from']) and trim($settings['cancellation_period_from']) != '') ? $settings['cancellation_period_from'] : ''); ?>" placeholder="<?php esc_attr_e('From e.g 48', 'modern-events-calendar-lite' ); ?>" />
                                            <input type="number" id="mec_settings_cancellation_period_time" name="mec[settings][cancellation_period_time]" value="<?php echo ((isset($settings['cancellation_period_time']) and trim($settings['cancellation_period_time']) != '') ? $settings['cancellation_period_time'] : ''); ?>" placeholder="<?php esc_attr_e('To e.g 24', 'modern-events-calendar-lite' ); ?>" />
                                        </div>
                                        <select name="mec[settings][cancellation_period_p]" title="<?php esc_attr_e('Period', 'modern-events-calendar-lite' ); ?>">
                                            <option value="hour" <?php echo (isset($settings['cancellation_period_p']) and $settings['cancellation_period_p'] == 'hour') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Hour(s)', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="day" <?php echo (isset($settings['cancellation_period_p']) and $settings['cancellation_period_p'] == 'day') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Day(s)', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <select name="mec[settings][cancellation_period_type]" title="<?php esc_attr_e('Type', 'modern-events-calendar-lite' ); ?>">
                                            <option value="before" <?php echo (isset($settings['cancellation_period_type']) and $settings['cancellation_period_type'] == 'before') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Before', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="after" <?php echo (isset($settings['cancellation_period_type']) and $settings['cancellation_period_type'] == 'after') ? 'selected="selected"' : ''; ?>><?php esc_html_e('After', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <div>
                                            <?php esc_html_e('Event Start', 'modern-events-calendar-lite' ); ?>
                                            <span class="mec-tooltip">
                                                <div class="box left">
                                                    <h5 class="title"><?php esc_html_e('Cancellation Period', 'modern-events-calendar-lite' ); ?></h5>
                                                    <div class="content"><p><?php esc_attr_e("You can restrict the option to cancel bookings. Leave empty for cancellation at any time. For example if you insert 48 to 24 hours before the event starts then bookers are able to cancel their booking only on this time period.", 'modern-events-calendar-lite' ); ?></p></div>
                                                </div>
                                                <i title="" class="dashicons-before dashicons-editor-help"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_cancel_page"><?php esc_html_e('Cancellation Page', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_cancel_page" name="mec[settings][booking_cancel_page]">
                                            <option value="">----</option>
                                            <?php foreach($pages as $page): ?>
                                            <option <?php echo ((isset($settings['booking_cancel_page']) and $settings['booking_cancel_page'] == $page->ID) ? 'selected="selected"' : ''); ?> value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Cancellation Page', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("Users will be redirected to this page after the booking cancellation. Leave it empty if you are not intrested.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_cancel_page_time"><?php esc_html_e('Cancellation Page Time Interval', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <input type="number" id="mec_settings_booking_cancel_page_time" name="mec[settings][booking_cancel_page_time]" value="<?php echo ((isset($settings['booking_cancel_page_time']) and trim($settings['booking_cancel_page_time']) != '0') ? $settings['booking_cancel_page_time'] : '2000'); ?>" placeholder="<?php esc_attr_e('2000 means 2 seconds', 'modern-events-calendar-lite' ); ?>" />
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Cancellation Page Time Interval', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("Specify the amount of delay before being redirected to the cancellation page. (in milliseconds)", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="title"><?php esc_html_e('User Registration', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_registration"><?php esc_html_e('Registration', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_registration" name="mec[settings][booking_registration]" onchange="jQuery('#mec_settings_booking_registration_wrapper').toggleClass('w-hidden');">
                                            <option <?php echo ((isset($settings['booking_registration']) and $settings['booking_registration'] == '1') ? 'selected="selected"' : ''); ?> value="1"><?php echo esc_html__('Enabled', 'modern-events-calendar-lite' ); ?></option>
                                            <option <?php echo ((isset($settings['booking_registration']) and $settings['booking_registration'] == '0') ? 'selected="selected"' : ''); ?> value="0"><?php echo esc_html__('Disabled', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Registration', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("By enabaling this option MEC will create a WordPress User for the main attendee. It's recommended to keep it enabled.", 'modern-events-calendar-lite' ); ?></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div id="mec_settings_booking_registration_wrapper" class="<?php echo (!isset($settings['booking_registration']) or (isset($settings['booking_registration']) and $settings['booking_registration'])) ? "" : "w-hidden"; ?>">
                                    <div class="mec-form-row">
                                        <label class="mec-col-3" for="mec_settings_booking_user_role"><?php esc_html_e('User Role', 'modern-events-calendar-lite' ); ?></label>
                                        <div class="mec-col-9">
                                            <select id="mec_settings_booking_user_role" name="mec[settings][booking_user_role]">
                                                <option value="">----</option>
                                                <?php foreach($roles as $role => $r): ?>
                                                    <option <?php echo ((isset($settings['booking_user_role']) and $settings['booking_user_role'] == $role) ? 'selected="selected"' : ''); ?> value="<?php echo esc_attr($role); ?>"><?php echo esc_html($r['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="mec-tooltip">
                                                <div class="box left">
                                                    <h5 class="title"><?php esc_html_e('User Role', 'modern-events-calendar-lite' ); ?></h5>
                                                    <div class="content"><p><?php esc_attr_e("MEC creates a user for the main attendee after each booking. The default role of the user is subscriber but you can change it if needed.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                </div>
                                                <i title="" class="dashicons-before dashicons-editor-help"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mec-form-row">
                                        <label class="mec-col-3" for="mec_settings_booking_userpass"><?php esc_html_e('Username & Password', 'modern-events-calendar-lite' ); ?></label>
                                        <div class="mec-col-9">
                                            <select id="mec_settings_booking_userpass" name="mec[settings][booking_userpass]">
                                                <option value="auto" <?php echo ((isset($settings['booking_userpass']) and trim($settings['booking_userpass']) == 'auto') ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Auto', 'modern-events-calendar-lite' ); ?></option>
                                                <option value="manual" <?php echo ((isset($settings['booking_userpass']) and trim($settings['booking_userpass']) == 'manual') ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Manual', 'modern-events-calendar-lite' ); ?></option>
                                            </select>
                                            <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Username & Password', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("If you set it to the manual option, users can insert a username and password during the booking for registration; otherwise, MEC will use their email and an auto-generated password.", 'modern-events-calendar-lite' ); ?></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mec-form-subtitle"><?php esc_html_e('Limitation', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_limit"><?php esc_html_e('Limit', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <input type="number" id="mec_settings_booking_limit" name="mec[settings][booking_limit]" value="<?php echo ((isset($settings['booking_limit']) and trim($settings['booking_limit']) != '') ? $settings['booking_limit'] : ''); ?>" placeholder="<?php esc_attr_e('Default is empty', 'modern-events-calendar-lite' ); ?>" />
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Booking Limit', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("The total number of tickets that a user can book. It is useful if you're providing free tickets. Leave it empty for an unlimited booking.", 'modern-events-calendar-lite' ); ?></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_ip_restriction"><?php esc_html_e('IP restriction', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_ip_restriction" name="mec[settings][booking_ip_restriction]">
                                            <option value="1" <?php echo ((isset($settings['booking_ip_restriction']) and trim($settings['booking_ip_restriction']) == 1) ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Enabled', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="0" <?php echo ((isset($settings['booking_ip_restriction']) and trim($settings['booking_ip_restriction']) == 0) ? 'selected="selected"' : ''); ?>><?php echo esc_html__('Disabled', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('IP restriction', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("If you set a limit for the total tickets that users can book, MEC will use the IP and email to prevent users to book high tickets. You can disable the IP restriction if you don't need it.", 'modern-events-calendar-lite' ); ?></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_lock_prefilled"><?php esc_html_e('Lock Pre-filled Fields', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_lock_prefilled" name="mec[settings][booking_lock_prefilled]">
                                            <option value="0" <?php echo (isset($settings['booking_lock_prefilled']) and $settings['booking_lock_prefilled'] == '0') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Disabled', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="1" <?php echo (isset($settings['booking_lock_prefilled']) and $settings['booking_lock_prefilled'] == '1') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Enabled', 'modern-events-calendar-lite' ); ?></option>
                                            <option value="2" <?php echo (isset($settings['booking_lock_prefilled']) and $settings['booking_lock_prefilled'] == '2') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Enabled Only for Main Attendee', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Lock Pre-filled Fields', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("When users are logged in, the name and email fields will be pre-filled but users can change them. If you enable the lock, logged-in users cannot change the pre-filled fields.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Last Few Tickets Flag', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_last_few_tickets_percentage"><?php esc_html_e('Percentage', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <input type="number" id="mec_settings_booking_last_few_tickets_percentage" name="mec[settings][booking_last_few_tickets_percentage]" value="<?php echo ((isset($settings['booking_last_few_tickets_percentage']) and trim($settings['booking_last_few_tickets_percentage']) != '') ? max($settings['booking_last_few_tickets_percentage'], 1) : '15'); ?>" placeholder="<?php esc_attr_e('Default is 15', 'modern-events-calendar-lite' ); ?>" min="1" max="100" step="1" />
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Last Few Tickets Percentage', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("MEC will show the \"Last Few Ticket\" flags on events when the remaning tickets are less than this percentage.", 'modern-events-calendar-lite' ); ?></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Thank You Page', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_thankyou_page"><?php esc_html_e('Thank You Page', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_thankyou_page" name="mec[settings][booking_thankyou_page]">
                                            <option value="">----</option>
                                            <?php foreach($pages as $page): ?>
                                                <option <?php echo ((isset($settings['booking_thankyou_page']) and $settings['booking_thankyou_page'] == $page->ID) ? 'selected="selected"' : ''); ?> value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Thank You Page', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("The user will be redirected to this page after a successfull booking. Leave it empty if you are not intrested.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_thankyou_page_time"><?php esc_html_e('Thank You Page Time Interval', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <input type="number" id="mec_settings_booking_thankyou_page_time" name="mec[settings][booking_thankyou_page_time]" value="<?php echo ((isset($settings['booking_thankyou_page_time']) and trim($settings['booking_thankyou_page_time']) != '0') ? $settings['booking_thankyou_page_time'] : '2000'); ?>" placeholder="<?php esc_attr_e('2000 mean 2 seconds', 'modern-events-calendar-lite' ); ?>" />
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Thank You Page Time Interval', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("Specify the amount of delay before being redirected to the thank you page. (in milliseconds)", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Transaction ID', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_tid_generation_method"><?php esc_html_e('Generation Method', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_tid_generation_method" name="mec[settings][booking_tid_gen_method]" onchange="jQuery('#mec_settings_booking_tid_ordered_generation').toggleClass('mec-util-hidden');">
                                            <option <?php echo ((isset($settings['booking_tid_gen_method']) and $settings['booking_tid_gen_method'] == 'random') ? 'selected="selected"' : ''); ?> value="random"><?php echo esc_html__('Random', 'modern-events-calendar-lite' ); ?></option>
                                            <option <?php echo ((isset($settings['booking_tid_gen_method']) and $settings['booking_tid_gen_method'] == 'ordered') ? 'selected="selected"' : ''); ?> value="ordered"><?php echo esc_html__('Ordered Numbers', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div id="mec_settings_booking_tid_ordered_generation" class="<?php echo (!isset($settings['booking_tid_gen_method']) or (isset($settings['booking_tid_gen_method']) and $settings['booking_tid_gen_method'] == 'random')) ? 'mec-util-hidden' : ''; ?>">
                                    <div class="mec-form-row">
                                        <label class="mec-col-3" for="mec_settings_booking_tid_start_from"><?php esc_html_e('Start From', 'modern-events-calendar-lite' ); ?></label>
                                        <div class="mec-col-9">
                                            <input type="number" id="mec_settings_booking_tid_start_from" name="mec[settings][booking_tid_start_from]" value="<?php echo (isset($settings['booking_tid_start_from']) ? esc_attr($settings['booking_tid_start_from']) : 10000); ?>" min="1" step="1">
                                        </div>
                                    </div>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Simplify Booking Form', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_booking_skip_step1"><?php esc_html_e('Skip Step 1', 'modern-events-calendar-lite' ); ?></label>
                                    <div class="mec-col-9">
                                        <select id="mec_settings_booking_skip_step1" name="mec[settings][booking_skip_step1]">
                                            <option <?php echo ((isset($settings['booking_skip_step1']) and $settings['booking_skip_step1'] == '0') ? 'selected="selected"' : ''); ?> value="0"><?php echo esc_html__('Disabled', 'modern-events-calendar-lite' ); ?></option>
                                            <option <?php echo ((isset($settings['booking_skip_step1']) and $settings['booking_skip_step1'] == '1') ? 'selected="selected"' : ''); ?> value="1"><?php echo esc_html__('If Possible', 'modern-events-calendar-lite' ); ?></option>
                                        </select>
                                        <span class="mec-tooltip">
                                            <div class="box left">
                                                <h5 class="title"><?php esc_html_e('Skip Step 1', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("If there is no choice for users in the first step of the booking module, this step will be skipped. In other words, if the event has only one date and the total user booking limit option is set to one, this step will be skipped.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Who can book?', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <label for="mec_settings_booking_wcb_all">
                                        <input type="hidden" name="mec[settings][booking_wcb_all]" value="0" />
                                        <input type="checkbox" name="mec[settings][booking_wcb_all]" id="mec_settings_booking_wcb_all" <?php echo ((!isset($settings['booking_wcb_all']) or (isset($settings['booking_wcb_all']) and $settings['booking_wcb_all'] == '1')) ? 'checked="checked"' : ''); ?> value="1" onchange="jQuery('#mec_settings_booking_booking_wcb_options').toggleClass('mec-util-hidden');" />
                                        <?php esc_html_e('All Users', 'modern-events-calendar-lite' ); ?>
                                    </label>
                                </div>
                                <div id="mec_settings_booking_booking_wcb_options" class="<?php echo (!isset($settings['booking_wcb_all']) or (isset($settings['booking_wcb_all']) and $settings['booking_wcb_all'] == '1')) ? 'mec-util-hidden' : ''; ?>" style="margin: 0 0 40px 0; padding: 20px 20px 4px; border: 1px solid #ddd;">
                                    <?php foreach($roles as $role_key => $role): $wcb_value = isset($settings['booking_wcb_'.$role_key]) ? $settings['booking_wcb_'.$role_key] : 1; ?>
                                        <div class="mec-form-row">
                                            <div class="mec-col-12">
                                                <label for="mec_settings_booking_wcb_<?php echo esc_attr($role_key); ?>">
                                                    <input type="hidden" name="mec[settings][booking_wcb_<?php echo esc_attr($role_key); ?>]" value="0" />
                                                    <input type="checkbox" name="mec[settings][booking_wcb_<?php echo esc_attr($role_key); ?>]" id="mec_settings_booking_wcb_<?php echo esc_attr($role_key); ?>" <?php echo ((!isset($settings['booking_wcb_'.$role_key]) or (isset($settings['booking_wcb_'.$role_key]) and $settings['booking_wcb_'.$role_key] == '1')) ? 'checked="checked"' : ''); ?> value="1" />
                                                    <?php echo esc_html($role['name']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Booking Elements', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_first_for_all">
                                            <input type="hidden" name="mec[settings][booking_first_for_all]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_first_for_all]" id="mec_settings_booking_first_for_all" <?php echo ((!isset($settings['booking_first_for_all']) or (isset($settings['booking_first_for_all']) and $settings['booking_first_for_all'] == '1')) ? 'checked="checked"' : ''); ?> value="1" />
                                            <?php esc_html_e('Enable Express Attendees Form', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                        <span class="mec-tooltip">
                                            <div class="box">
                                                <h5 class="title"><?php esc_html_e('Enable Express Attendees Form', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("Apply the info from the first attendee to all the purchased tickets by that user. Uncheck if you want every ticket to have its own attendee’s info.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_attendee_counter">
                                            <input type="hidden" name="mec[settings][attendee_counter]" value="0" />
                                            <input type="checkbox" name="mec[settings][attendee_counter]" id="mec_settings_attendee_counter"
                                                <?php echo ((isset($settings['attendee_counter']) and $settings['attendee_counter'] == '1') ? 'checked="checked"' : ''); ?>
                                                value="1" />
                                            <?php esc_html_e('Attendee Counter', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_display_total_tickets">
                                            <input type="hidden" name="mec[settings][booking_display_total_tickets]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_display_total_tickets]" id="mec_settings_booking_display_total_tickets"
                                                <?php echo ((isset($settings['booking_display_total_tickets']) and $settings['booking_display_total_tickets'] == '1') ? 'checked="checked"' : ''); ?>
                                                   value="1" />
                                            <?php esc_html_e('Display Total Tickets', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                        <span class="mec-tooltip">
                                            <div class="box">
                                                <h5 class="title"><?php esc_html_e('Display Total Tickets', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("If enabled, the total number of selected tickets will be displayed next to the booking button in the  first step of the booking.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_display_progress_bar">
                                            <input type="hidden" name="mec[settings][booking_display_progress_bar]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_display_progress_bar]" id="mec_settings_booking_display_progress_bar"
                                                <?php echo ((isset($settings['booking_display_progress_bar']) and $settings['booking_display_progress_bar'] == '1') ? 'checked="checked"' : ''); ?>
                                                   value="1" />
                                            <?php esc_html_e('Display Progress Bar', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                        <span class="mec-tooltip">
                                            <div class="box">
                                                <h5 class="title"><?php esc_html_e('Display Progress Bar', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("If enabled, a progress bar will be added to the booking module indicating the current step and the next steps. It won't get displayed if there is only 1 step.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_invoice">
                                            <input type="hidden" name="mec[settings][booking_invoice]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_invoice]" id="mec_settings_booking_invoice"
                                                <?php echo ((!isset($settings['booking_invoice']) or (isset($settings['booking_invoice']) and $settings['booking_invoice'] == '1')) ? 'checked="checked"' : ''); ?>
                                                value="1" />
                                            <?php esc_html_e('Enable Invoice', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_ongoing">
                                            <input type="hidden" name="mec[settings][booking_ongoing]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_ongoing]" id="mec_settings_booking_ongoing"
                                                <?php echo ((isset($settings['booking_ongoing']) and $settings['booking_ongoing'] == '1') ? 'checked="checked"' : ''); ?>
                                                value="1" />
                                            <?php esc_html_e('Enable Booking for Ongoing Events', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_downloadable_file_status">
                                            <input type="hidden" name="mec[settings][downloadable_file_status]" value="0" />
                                            <input type="checkbox" name="mec[settings][downloadable_file_status]" id="mec_settings_booking_downloadable_file_status"
                                                <?php echo ((isset($settings['downloadable_file_status']) and $settings['downloadable_file_status'] == '1') ? 'checked="checked"' : ''); ?>
                                                   value="1" />
                                            <?php esc_html_e('Enable Downloadable File', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                        <span class="mec-tooltip">
                                            <div class="box">
                                                <h5 class="title"><?php esc_html_e('Downloadable File', 'modern-events-calendar-lite' ); ?></h5>
                                                <div class="content"><p><?php esc_attr_e("By enabling this feature, You can upload a file for each event and the attendees will be able to download it after booking.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                            </div>
                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_disable_ticket_times">
                                            <input type="hidden" name="mec[settings][disable_ticket_times]" value="0" />
                                            <input type="checkbox" name="mec[settings][disable_ticket_times]" id="mec_settings_booking_disable_ticket_times"
                                                <?php echo ((isset($settings['disable_ticket_times']) and $settings['disable_ticket_times'] == '1') ? 'checked="checked"' : ''); ?>
                                                   value="1" />
                                            <?php esc_html_e('Disable Ticket Times', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php do_action('add_booking_variables', $settings); ?>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Email verification', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_auto_verify_free">
                                            <input type="hidden" name="mec[settings][booking_auto_verify_free]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_auto_verify_free]" id="mec_settings_booking_auto_verify_free" <?php echo ((isset($settings['booking_auto_verify_free']) and $settings['booking_auto_verify_free'] == '1') ? 'checked="checked"' : ''); ?> value="1" />
                                            <?php esc_html_e('Auto verification for free bookings', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_auto_verify_paid">
                                            <input type="hidden" name="mec[settings][booking_auto_verify_paid]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_auto_verify_paid]" id="mec_settings_booking_auto_verify_paid" <?php echo ((isset($settings['booking_auto_verify_paid']) and $settings['booking_auto_verify_paid'] == '1') ? 'checked="checked"' : ''); ?> value="1" />
                                            <?php esc_html_e('Auto verification for paid bookings', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Booking Confirmation', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_auto_confirm_free">
                                            <input type="hidden" name="mec[settings][booking_auto_confirm_free]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_auto_confirm_free]" id="mec_settings_booking_auto_confirm_free" <?php echo ((isset($settings['booking_auto_confirm_free']) and $settings['booking_auto_confirm_free'] == '1') ? 'checked="checked"' : ''); ?> value="1" />
                                            <?php esc_html_e('Auto confirmation for free bookings', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_auto_confirm_paid">
                                            <input type="hidden" name="mec[settings][booking_auto_confirm_paid]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_auto_confirm_paid]" id="mec_settings_booking_auto_confirm_paid" <?php echo ((isset($settings['booking_auto_confirm_paid']) and $settings['booking_auto_confirm_paid'] == '1') ? 'checked="checked"' : ''); ?> value="1" />
                                            <?php esc_html_e('Auto confirmation for paid bookings', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <div class="mec-col-12">
                                        <label for="mec_settings_booking_auto_confirm_send_email">
                                            <input type="hidden" name="mec[settings][booking_auto_confirm_send_email]" value="0" />
                                            <input type="checkbox" name="mec[settings][booking_auto_confirm_send_email]" id="mec_settings_booking_auto_confirm_send_email" <?php echo ((isset($settings['booking_auto_confirm_send_email']) and $settings['booking_auto_confirm_send_email'] == '1') ? 'checked="checked"' : ''); ?> value="1" />
                                            <?php esc_html_e('Send confirmation email in auto confirmation mode', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php do_action( 'mec_booking_settings_end',$this->settings ) ?>
                            <?php endif; ?>
                        </div>

                        <?php if(isset($this->settings['booking_status']) and $this->settings['booking_status']): ?>

                        <?php do_action('mec_reg_menu_start', $this->main, $this->settings); ?>

                        <div id="booking_shortcode" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Booking Shortcode', 'modern-events-calendar-lite' ); ?></h4>

                            <?php if(!$this->main->getPRO()): ?>
                            <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite' ), '<a href="'.esc_url($this->main->get_pro_link()).'" target="_blank">'.esc_html__('Pro version of Modern Events Calendar', 'modern-events-calendar-lite' ).'</a>'); ?></div>
                            <?php else: ?>

                            <div class="mec-form-row">
                                <div class="mec-col-12">
                                    <p><?php echo sprintf(esc_html__("Booking module is available in the event details page but if you like to embed booking module of certain event into a custom WP page or post or any shortcode compatible widgets, all you need to do is to insert %s shortcode into the page content and place the event id instead of 1.", 'modern-events-calendar-lite' ), '<code>[mec-booking event-id="1"]</code>'); ?></p>
                                    <p><?php echo sprintf(esc_html__('Also, you can insert %s if you like to show only one of the available tickets in booking module. Instead of 1 you should insert the ticket ID. This parameter is optional.', 'modern-events-calendar-lite' ), '<strong>ticket-id="1"</strong>'); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div id="coupon_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Coupons', 'modern-events-calendar-lite' ); ?></h4>

                            <?php if(!$this->main->getPRO()): ?>
                            <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite' ), '<a href="'.esc_url($this->main->get_pro_link()).'" target="_blank">'.esc_html__('Pro version of Modern Events Calendar', 'modern-events-calendar-lite' ).'</a>'); ?></div>
                            <?php else: ?>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][coupons_status]" value="0" />
                                    <input onchange="jQuery('#mec_coupons_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][coupons_status]" <?php if(isset($settings['coupons_status']) and $settings['coupons_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable coupons module', 'modern-events-calendar-lite' ); ?>
                                </label>
                                <p><?php esc_attr_e("After enabling and saving the settings, you should reload the page to see a new menu on the Dashboard > Booking", 'modern-events-calendar-lite' ); ?></p>
                            </div>
                            <div id="mec_coupons_container_toggle" class="<?php if((isset($settings['coupons_status']) and !$settings['coupons_status']) or !isset($settings['coupons_status'])) echo 'mec-util-hidden'; ?>">
                                <div class="mec-form-row">
                                    <label>
                                        <input type="hidden" name="mec[settings][coupons_apply_100percent_to_all]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][coupons_apply_100percent_to_all]" <?php if(isset($settings['coupons_apply_100percent_to_all']) and $settings['coupons_apply_100percent_to_all']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Apply 100 percent coupons to all fees', 'modern-events-calendar-lite' ); ?>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div id="taxes_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Taxes / Fees', 'modern-events-calendar-lite' ); ?></h4>

                            <?php if(!$this->main->getPRO()): ?>
                            <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite' ), '<a href="'.esc_url($this->main->get_pro_link()).'" target="_blank">'.esc_html__('Pro version of Modern Events Calendar', 'modern-events-calendar-lite' ).'</a>'); ?></div>
                            <?php else: ?>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][taxes_fees_status]" value="0" />
                                    <input onchange="jQuery('#mec_taxes_fees_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][taxes_fees_status]" <?php if(isset($settings['taxes_fees_status']) and $settings['taxes_fees_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable taxes / fees module', 'modern-events-calendar-lite' ); ?>
                                </label>
                            </div>
                            <div id="mec_taxes_fees_container_toggle" class="<?php if((isset($settings['taxes_fees_status']) and !$settings['taxes_fees_status']) or !isset($settings['taxes_fees_status'])) echo 'mec-util-hidden'; ?>">
                                <div class="mec-form-row">
                                    <button class="button" type="button" id="mec_add_fee_button"><?php esc_html_e('Add Fee', 'modern-events-calendar-lite' ); ?></button>
                                </div>
                                <div class="mec-form-row" id="mec_fees_list">
                                    <?php $i = 0; foreach($fees as $key=>$fee): if(!is_numeric($key)) continue; $i = max($i, $key); ?>
                                    <div class="mec-box" id="mec_fee_row<?php echo esc_attr($i); ?>">
                                        <div class="mec-form-row">
                                            <input class="mec-col-12" type="text" name="mec[settings][fees][<?php echo esc_attr($i); ?>][title]" placeholder="<?php esc_attr_e('Fee Title', 'modern-events-calendar-lite' ); ?>" value="<?php echo (isset($fee['title']) ? esc_attr($fee['title']) : ''); ?>" />
                                        </div>
                                        <div class="mec-form-row">
                                            <span class="mec-col-4">
                                                <input type="text" name="mec[settings][fees][<?php echo esc_attr($i); ?>][amount]" placeholder="<?php esc_attr_e('Amount', 'modern-events-calendar-lite' ); ?>" value="<?php echo (isset($fee['amount']) ? esc_attr($fee['amount']) : ''); ?>" />
                                                <span class="mec-tooltip">
                                                    <div class="box top">
                                                        <h5 class="title"><?php esc_html_e('Amount', 'modern-events-calendar-lite' ); ?></h5>
                                                        <div class="content"><p><?php esc_attr_e("Fee amount, considered as fixed amount if you set the type to amount otherwise considered as percentage", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/taxes-or-fees/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                    </div>
                                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                </span>
                                            </span>
                                            <span class="mec-col-4">
                                                <select name="mec[settings][fees][<?php echo esc_attr($i); ?>][type]">
                                                    <option value="percent" <?php echo ((isset($fee['type']) and $fee['type'] == 'percent') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Percent', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="amount" <?php echo ((isset($fee['type']) and $fee['type'] == 'amount') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Amount (Per Ticket)', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="amount_per_date" <?php echo ((isset($fee['type']) and $fee['type'] == 'amount_per_date') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Amount (Per Date)', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="amount_per_booking" <?php echo ((isset($fee['type']) and $fee['type'] == 'amount_per_booking') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Amount (Per Booking)', 'modern-events-calendar-lite' ); ?></option>
                                                </select>
                                            </span>
                                            <button class="button" type="button" id="mec_remove_fee_button<?php echo esc_attr($i); ?>" onclick="mec_remove_fee(<?php echo esc_attr($i); ?>);"><?php esc_html_e('Remove', 'modern-events-calendar-lite' ); ?></button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" id="mec_new_fee_key" value="<?php echo ($i+1); ?>" />
                                <div class="mec-util-hidden" id="mec_new_fee_raw">
                                    <div class="mec-box" id="mec_fee_row:i:">
                                        <div class="mec-form-row">
                                            <input class="mec-col-12" type="text" name="mec[settings][fees][:i:][title]" placeholder="<?php esc_attr_e('Fee Title', 'modern-events-calendar-lite' ); ?>" />
                                        </div>
                                        <div class="mec-form-row">
                                            <span class="mec-col-4">
                                                <input type="text" name="mec[settings][fees][:i:][amount]" placeholder="<?php esc_attr_e('Amount', 'modern-events-calendar-lite' ); ?>" />
                                                <span class="mec-tooltip">
                                                    <div class="box top">
                                                        <h5 class="title"><?php esc_html_e('Amount', 'modern-events-calendar-lite' ); ?></h5>
                                                        <div class="content"><p><?php esc_attr_e("Fee amount, considered as fixed amount if you set the type to amount otherwise considered as percentage", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/taxes-or-fees/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                    </div>
                                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                </span>
                                            </span>
                                            <span class="mec-col-4">
                                                <select name="mec[settings][fees][:i:][type]">
                                                    <option value="percent"><?php esc_html_e('Percent', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="amount"><?php esc_html_e('Amount (Per Ticket)', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="amount_per_date"><?php esc_html_e('Amount (Per Date)', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="amount_per_booking"><?php esc_html_e('Amount (Per Booking)', 'modern-events-calendar-lite' ); ?></option>
                                                </select>
                                            </span>
                                            <button class="button" type="button" id="mec_remove_fee_button:i:" onclick="mec_remove_fee(:i:);"><?php esc_html_e('Remove', 'modern-events-calendar-lite' ); ?></button>
                                        </div>
                                    </div>
                                </div>

                                <?php if(!isset($settings['wc_status']) or (isset($settings['wc_status']) and !$settings['wc_status'])): ?>
                                <div class="mec-form-row">
                                    <h4><?php echo esc_html__('Disable Fees per Gateways', 'modern-events-calendar-lite' ); ?></h4>
                                        <?php foreach($gateways as $gateway): ?>
                                        <div class="mec-form-row">
                                            <span class="mec-col-12">
                                                <label>
                                                    <input type="hidden" name="mec[settings][fees_disabled_gateways][<?php echo esc_attr($gateway->id()); ?>]" value="0">
                                                    <input type="checkbox" name="mec[settings][fees_disabled_gateways][<?php echo esc_attr($gateway->id()); ?>]" value="1" <?php echo ((isset($settings['fees_disabled_gateways']) and isset($settings['fees_disabled_gateways'][$gateway->id()]) and $settings['fees_disabled_gateways'][$gateway->id()]) ? 'checked="checked"' : ''); ?>>
                                                    <?php echo esc_html($gateway->title()); ?>
                                                </label>
                                            </span>
                                        </div>
                                        <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div id="ticket_variations_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Ticket Variations & Options', 'modern-events-calendar-lite' ); ?></h4>

                            <?php if(!$this->main->getPRO()): ?>
                                <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite' ), '<a href="'.esc_url($this->main->get_pro_link()).'" target="_blank">'.esc_html__('Pro version of Modern Events Calendar', 'modern-events-calendar-lite' ).'</a>'); ?></div>
                            <?php else: ?>
                                <div class="mec-form-row">
                                    <label>
                                        <input type="hidden" name="mec[settings][ticket_variations_status]" value="0" />
                                        <input onchange="jQuery('#mec_ticket_variations_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][ticket_variations_status]" <?php if(isset($settings['ticket_variations_status']) and $settings['ticket_variations_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable ticket variations module', 'modern-events-calendar-lite' ); ?>
                                    </label>
                                </div>
                                <div id="mec_ticket_variations_container_toggle" class="<?php if((isset($settings['ticket_variations_status']) and !$settings['ticket_variations_status']) or !isset($settings['ticket_variations_status'])) echo 'mec-util-hidden'; ?>">
                                    <div class="mec-form-row">
                                        <button class="button" type="button" id="mec_add_ticket_variation_button"><?php esc_html_e('Add Variation / Option', 'modern-events-calendar-lite' ); ?></button>
                                    </div>
                                    <div class="mec-form-row" id="mec_ticket_variations_list">
                                        <?php $i = 0; foreach($ticket_variations as $key=>$ticket_variation): if(!is_numeric($key)) continue; $i = max($i, $key); ?>
                                            <div class="mec-box mec_ticket_variation_row" id="mec_ticket_variation_row<?php echo esc_attr($i); ?>">
                                                <div class="mec-form-row">
                                                    <input class="mec-col-12" type="text" name="mec[settings][ticket_variations][<?php echo esc_attr($i); ?>][title]" placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite' ); ?>" value="<?php echo (isset($ticket_variation['title']) ? esc_attr($ticket_variation['title']) : ''); ?>" />
                                                </div>
                                                <div class="mec-form-row">
                                                    <span class="mec-col-4">
                                                        <input type="text" name="mec[settings][ticket_variations][<?php echo esc_attr($i); ?>][price]" placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite' ); ?>" value="<?php echo (isset($ticket_variation['price']) ? esc_attr($ticket_variation['price']) : ''); ?>" />
                                                        <span class="mec-tooltip">
                                                            <div class="box top">
                                                                <h5 class="title"><?php esc_html_e('Price', 'modern-events-calendar-lite' ); ?></h5>
                                                                <div class="content"><p><?php esc_attr_e("Option Price", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/ticket-variations/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                            </div>
                                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                        </span>
                                                    </span>
                                                    <span class="mec-col-4">
                                                        <input type="number" min="0" name="mec[settings][ticket_variations][<?php echo esc_attr($i); ?>][max]" placeholder="<?php esc_attr_e('Maximum Per Ticket', 'modern-events-calendar-lite' ); ?>" value="<?php echo (isset($ticket_variation['max']) ? esc_attr($ticket_variation['max']) : ''); ?>" />
                                                        <span class="mec-tooltip">
                                                            <div class="box top">
                                                                <h5 class="title"><?php esc_html_e('Maximum Per Ticket', 'modern-events-calendar-lite' ); ?></h5>
                                                                <div class="content"><p><?php esc_attr_e("Maximum Per Ticket. Leave blank for unlimited.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/ticket-variations/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                            </div>
                                                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                        </span>
                                                    </span>
                                                    <button class="button mec_remove_ticket_variation_button" type="button" id="mec_remove_ticket_variation_button<?php echo esc_attr($i); ?>" onclick="mec_remove_ticket_variation(<?php echo esc_attr($i); ?>, 'ticket_variation');"><?php esc_html_e('Remove', 'modern-events-calendar-lite' ); ?></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" id="mec_new_ticket_variation_key" value="<?php echo ($i+1); ?>" />
                                    <div class="mec-util-hidden" id="mec_new_ticket_variation_raw">
                                        <div class="mec-box mec_ticket_variation_row" id="mec_ticket_variation_row:i:">
                                            <div class="mec-form-row">
                                                <input class="mec-col-12" type="text" name="mec[settings][ticket_variations][:i:][title]" placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite' ); ?>" />
                                            </div>
                                            <div class="mec-form-row">
                                                <span class="mec-col-4">
                                                    <input type="text" name="mec[settings][ticket_variations][:i:][price]" placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite' ); ?>" />
                                                    <span class="mec-tooltip">
                                                        <div class="box top">
                                                            <h5 class="title"><?php esc_html_e('Price', 'modern-events-calendar-lite' ); ?></h5>
                                                            <div class="content"><p><?php esc_attr_e("Option Price", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/ticket-variations/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                        </div>
                                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                    </span>
                                                </span>
                                                <span class="mec-col-4">
                                                    <input type="number" min="0" name="mec[settings][ticket_variations][:i:][max]" placeholder="<?php esc_attr_e('Maximum Per Ticket', 'modern-events-calendar-lite' ); ?>" value="1" />
                                                    <span class="mec-tooltip">
                                                        <div class="box top">
                                                            <h5 class="title"><?php esc_html_e('Maximum Per Ticket', 'modern-events-calendar-lite' ); ?></h5>
                                                            <div class="content"><p><?php esc_attr_e("Maximum Per Ticket. Leave blank for unlimited.", 'modern-events-calendar-lite' ); ?><a href="https://webnus.net/dox/modern-events-calendar/ticket-variations/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                        </div>
                                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                    </span>
                                                </span>
                                                <button class="button mec_remove_ticket_variation_button" type="button" id="mec_remove_ticket_variation_button:i:" onclick="mec_remove_ticket_variation(:i:, 'ticket_variation');"><?php esc_html_e('Remove', 'modern-events-calendar-lite' ); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <label>
                                        <input type="hidden" name="mec[settings][ticket_variations_per_ticket]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][ticket_variations_per_ticket]" <?php if(isset($settings['ticket_variations_per_ticket']) and $settings['ticket_variations_per_ticket']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable variations per ticket', 'modern-events-calendar-lite' ); ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="booking_form_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Booking Form', 'modern-events-calendar-lite' ); ?></h4>
                            <div class="mec-booking-per-attendee-fields">
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Per Attendee Fields', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-container">
                                    <?php do_action('before_mec_reg_fields_form'); ?>
                                    <?php do_action('mec_reg_fields_form_start'); ?>
                                    <div class="mec-form-row" id="mec_reg_form_container">
                                        <?php /** Don't remove this hidden field **/ ?>
                                        <input type="hidden" name="mec[reg_fields]" value="" />

                                        <ul id="mec_reg_form_fields">
                                            <?php
                                            $i = 0;
                                            foreach($reg_fields as $key => $reg_field)
                                            {
                                                if(!is_numeric($key)) continue;
                                                $i = max( $i, $key );

                                                if($reg_field['type'] == 'text') echo MEC_kses::form($this->main->field_text($key, $reg_field));
                                                elseif($reg_field['type'] == 'name') echo MEC_kses::form($this->main->field_name($key, $reg_field));
                                                elseif($reg_field['type'] == 'mec_email') echo MEC_kses::form($this->main->field_mec_email($key, $reg_field));
                                                elseif($reg_field['type'] == 'email') echo MEC_kses::form($this->main->field_email($key, $reg_field));
                                                elseif($reg_field['type'] == 'date') echo MEC_kses::form($this->main->field_date($key, $reg_field));
                                                elseif($reg_field['type'] == 'file') echo MEC_kses::form($this->main->field_file($key, $reg_field));
                                                elseif($reg_field['type'] == 'tel') echo MEC_kses::form($this->main->field_tel($key, $reg_field));
                                                elseif($reg_field['type'] == 'textarea') echo MEC_kses::form($this->main->field_textarea($key, $reg_field));
                                                elseif($reg_field['type'] == 'p') echo MEC_kses::form($this->main->field_p($key, $reg_field));
                                                elseif($reg_field['type'] == 'checkbox') echo MEC_kses::form($this->main->field_checkbox($key, $reg_field));
                                                elseif($reg_field['type'] == 'radio') echo MEC_kses::form($this->main->field_radio($key, $reg_field));
                                                elseif($reg_field['type'] == 'select') echo MEC_kses::form($this->main->field_select($key, $reg_field));
                                                elseif($reg_field['type'] == 'agreement') echo MEC_kses::form($this->main->field_agreement($key, $reg_field));
                                            }
                                            ?>
                                        </ul>
                                        <div id="mec_reg_form_field_types">
                                            <button type="button" class="button red" data-type="name"><?php esc_html_e( 'MEC Name', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button red" data-type="mec_email"><?php esc_html_e( 'MEC Email', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="text"><?php esc_html_e( 'Text', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="email"><?php esc_html_e( 'Email', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="date"><?php esc_html_e( 'Date', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="tel"><?php esc_html_e( 'Tel', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="file"><?php esc_html_e( 'File', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="textarea"><?php esc_html_e( 'Textarea', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="checkbox"><?php esc_html_e( 'Checkboxes', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="radio"><?php esc_html_e( 'Radio Buttons', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="select"><?php esc_html_e( 'Dropdown', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="agreement"><?php esc_html_e( 'Agreement', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="p"><?php esc_html_e( 'Paragraph', 'modern-events-calendar-lite' ); ?></button>
                                        </div>
                                        <?php do_action('mec_reg_fields_form_end'); ?>
                                    </div>
                                    <?php do_action('after_mec_reg_fields_form'); ?>
                                </div>
                                <input type="hidden" id="mec_new_reg_field_key" value="<?php echo ($i + 1); ?>" />
                                <div class="mec-util-hidden">
                                    <div id="mec_reg_field_text">
                                        <?php echo MEC_kses::form($this->main->field_text(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_email">
                                        <?php echo MEC_kses::form($this->main->field_email(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_mec_email">
                                        <?php echo MEC_kses::form($this->main->field_mec_email(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_name">
                                        <?php echo MEC_kses::form($this->main->field_name(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_tel">
                                        <?php echo MEC_kses::form($this->main->field_tel(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_date">
                                        <?php echo MEC_kses::form($this->main->field_date(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_file">
                                        <?php echo MEC_kses::form($this->main->field_file(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_textarea">
                                        <?php echo MEC_kses::form($this->main->field_textarea(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_checkbox">
                                        <?php echo MEC_kses::form($this->main->field_checkbox(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_radio">
                                        <?php echo MEC_kses::form($this->main->field_radio(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_select">
                                        <?php echo MEC_kses::form($this->main->field_select(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_agreement">
                                        <?php echo MEC_kses::form($this->main->field_agreement(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_p">
                                        <?php echo MEC_kses::form($this->main->field_p(':i:')); ?>
                                    </div>
                                    <div id="mec_reg_field_option">
                                        <?php echo MEC_kses::form($this->main->field_option(':fi:', ':i:')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mec-booking-fixed-fields">
                                <h5 class="mec-form-subtitle"><?php esc_html_e('Fixed Fields', 'modern-events-calendar-lite' ); ?></h5>
                                <div class="mec-container">
                                    <?php do_action('before_mec_bfixed_fields_form'); ?>
                                    <?php do_action('mec_bfixed_fields_form_start'); ?>
                                    <div class="mec-form-row" id="mec_bfixed_form_container">
                                        <?php /** Don't remove this hidden field **/ ?>
                                        <input type="hidden" name="mec[bfixed_fields]" value="" />

                                        <ul id="mec_bfixed_form_fields">
                                            <?php
                                            $b = 0;
                                            foreach($bfixed_fields as $key => $bfixed_field)
                                            {
                                                if(!is_numeric($key)) continue;
                                                $b = max($b, $key);

                                                if( !isset($bfixed_field['type']) ) continue;

                                                if($bfixed_field['type'] == 'text') echo MEC_kses::form($this->main->field_text($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'name') echo MEC_kses::form($this->main->field_name($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'mec_email') echo MEC_kses::form($this->main->field_mec_email($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'email') echo MEC_kses::form($this->main->field_email($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'date') echo MEC_kses::form($this->main->field_date($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'file') echo MEC_kses::form($this->main->field_file($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'tel') echo MEC_kses::form($this->main->field_tel($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'textarea') echo MEC_kses::form($this->main->field_textarea($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'p') echo MEC_kses::form($this->main->field_p($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'checkbox') echo MEC_kses::form($this->main->field_checkbox($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'radio') echo MEC_kses::form($this->main->field_radio($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'select') echo MEC_kses::form($this->main->field_select($key, $bfixed_field, 'bfixed'));
                                                elseif($bfixed_field['type'] == 'agreement') echo MEC_kses::form($this->main->field_agreement($key, $bfixed_field, 'bfixed'));
                                            }
                                            ?>
                                        </ul>
                                        <div id="mec_bfixed_form_field_types">
                                            <button type="button" class="button" data-type="text"><?php esc_html_e( 'Text', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="email"><?php esc_html_e( 'Email', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="date"><?php esc_html_e( 'Date', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="tel"><?php esc_html_e( 'Tel', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="textarea"><?php esc_html_e( 'Textarea', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="checkbox"><?php esc_html_e( 'Checkboxes', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="radio"><?php esc_html_e( 'Radio Buttons', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="select"><?php esc_html_e( 'Dropdown', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="agreement"><?php esc_html_e( 'Agreement', 'modern-events-calendar-lite' ); ?></button>
                                            <button type="button" class="button" data-type="p"><?php esc_html_e( 'Paragraph', 'modern-events-calendar-lite' ); ?></button>
                                        </div>
                                        <?php do_action('mec_bfixed_fields_form_end'); ?>
                                    </div>
                                    <div class="mec-form-row">
                                        <?php wp_nonce_field('mec_options_form'); ?>
                                        <button  style="display: none;" id="mec_reg_fields_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e( 'Save Changes', 'modern-events-calendar-lite' ); ?></button>
                                    </div>
                                    <?php do_action('after_mec_bfixed_fields_form'); ?>
                                </div>
                                <input type="hidden" id="mec_new_bfixed_field_key" value="<?php echo ($b + 1); ?>" />
                                <div class="mec-util-hidden">
                                    <div id="mec_bfixed_field_text">
                                        <?php echo MEC_kses::form($this->main->field_text(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_email">
                                        <?php echo MEC_kses::form($this->main->field_email(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_tel">
                                        <?php echo MEC_kses::form($this->main->field_tel(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_date">
                                        <?php echo MEC_kses::form($this->main->field_date(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_textarea">
                                        <?php echo MEC_kses::form($this->main->field_textarea(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_checkbox">
                                        <?php echo MEC_kses::form($this->main->field_checkbox(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_radio">
                                        <?php echo MEC_kses::form($this->main->field_radio(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_select">
                                        <?php echo MEC_kses::form($this->main->field_select(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_agreement">
                                        <?php echo MEC_kses::form($this->main->field_agreement(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_p">
                                        <?php echo MEC_kses::form($this->main->field_p(':i:', array(), 'bfixed')); ?>
                                    </div>
                                    <div id="mec_bfixed_field_option">
                                        <?php echo MEC_kses::form($this->main->field_option(':fi:', ':i:', array(), 'bfixed')); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="uploadfield_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Upload Field', 'modern-events-calendar-lite' ); ?></h4>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_booking_form_upload_field_mime_types"><?php esc_html_e('Mime types', 'modern-events-calendar-lite' ); ?></label>
                                <div class="mec-col-9">
                                    <input type="text" id="mec_booking_form_upload_field_mime_types" name="mec[settings][upload_field_mime_types]" placeholder="jpeg,jpg,png,pdf" value="<?php echo ((isset($settings['upload_field_mime_types']) and trim($settings['upload_field_mime_types']) != '') ? $settings['upload_field_mime_types'] : ''); ?>" />
                                </div>
                                <p class="description"><?php echo esc_html__('Split mime types with ",".', 'modern-events-calendar-lite' ); ?> <br /> <?php esc_attr_e("Default: jpeg,jpg,png,pdf", 'modern-events-calendar-lite' ); ?></p>
                            </div>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_booking_form_upload_field_max_upload_size"><?php esc_html_e('Maximum file size', 'modern-events-calendar-lite' ); ?></label>
                                <div class="mec-col-9">
                                    <input type="number" id="mec_booking_form_upload_field_max_upload_size" name="mec[settings][upload_field_max_upload_size]" value="<?php echo ((isset($settings['upload_field_max_upload_size']) and trim($settings['upload_field_max_upload_size']) != '') ? $settings['upload_field_max_upload_size'] : ''); ?>" />
                                </div>
                                <p class="description"><?php echo esc_html__('The unit is Megabyte "MB"', 'modern-events-calendar-lite' ); ?></p>
                            </div>
                        </div>

                        <div id="payment_gateways_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Payment Gateways', 'modern-events-calendar-lite' ); ?></h4>
                            <div class="mec-container">

                                <?php if(class_exists('WooCommerce')): ?>
                                <div class="mec-form-row" style="margin-bottom: 30px;">
                                    <div class="mec-col-12">
                                        <label>
                                            <input type="hidden" name="mec[settings][wc_status]" value="0" />
                                            <input id="mec_gateways_wc_status" onchange="jQuery('#mec_payment_options_wrapper, #mec_gateways_wc_status_guide').toggleClass('w-hidden');" value="1" type="checkbox" name="mec[settings][wc_status]" <?php if(isset($settings['wc_status']) and $settings['wc_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Use WooCommerce as Payment System', 'modern-events-calendar-lite' ); ?>
                                        </label>
                                        <p><?php esc_html_e("By enabling this feature, tickets will be added to WooCommerce cart and all payment process would be done by WooCommerce so all of MEC payment related modules will be disabled. To configure your desired gateways and booking fields etc, you need to configure WooCommerce on your website.", 'modern-events-calendar-lite' ); ?></p>
                                        <div id="mec_gateways_wc_status_guide" class="<?php if(!isset($settings['wc_status']) or (isset($settings['wc_status']) and !$settings['wc_status'])) echo 'w-hidden'; ?>">

                                            <?php if(isset($settings['mec_cart_status']) and $settings['mec_cart_status']): ?>
                                            <p class="mec-error" id="mec_wc_status_mec_cart_error"><?php esc_html_e("Please disable MEC Cart first otherwise you're not able to use WooCommerce feature.", 'modern-events-calendar-lite' ); ?></p>
                                            <?php endif; ?>

                                            <p><?php esc_html_e("You cannot use following MEC features so you should use WooCommerc and its add-ons if you need them.", 'modern-events-calendar-lite' ); ?></p>
                                            <ul>
                                                <li><?php esc_html_e('Payment Gateways', 'modern-events-calendar-lite' ); ?></li>
                                                <li><?php esc_html_e('Price per Dates of Tickets', 'modern-events-calendar-lite' ); ?></li>
                                                <li><?php esc_html_e('Coupons', 'modern-events-calendar-lite' ); ?></li>
                                                <li><?php esc_html_e('Ticket Variations', 'modern-events-calendar-lite' ); ?></li>
                                                <li><?php esc_html_e('Taxes / Fees', 'modern-events-calendar-lite' ); ?></li>
                                                <li><?php esc_html_e('Discount Per Roles', 'modern-events-calendar-lite' ); ?></li>
                                            </ul>

                                            <div class="mec-form-row" style="margin-top: 40px;">
                                                <label class="mec-col-3" for="mec_gateways_wc_autoorder_complete"><?php esc_html_e('Automatically complete WooCommerce orders', 'modern-events-calendar-lite' ); ?></label>
                                                <div class="mec-col-9">
                                                    <select id="mec_gateways_wc_autoorder_complete" name="mec[settings][wc_autoorder_complete]">
                                                        <option value="1" <?php echo((isset($settings['wc_autoorder_complete']) and $settings['wc_autoorder_complete'] == '1') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Enabled', 'modern-events-calendar-lite' ); ?></option>
                                                        <option value="0" <?php echo((isset($settings['wc_autoorder_complete']) and $settings['wc_autoorder_complete'] == '0') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Disabled', 'modern-events-calendar-lite' ); ?></option>
                                                    </select>
                                                    <span class="mec-tooltip">
                                                        <div class="box left">
                                                            <h5 class="title"><?php esc_html_e('Auto WooCommerce orders', 'modern-events-calendar-lite' ); ?></h5>
                                                            <div class="content"><p><?php esc_attr_e('It applies only to the orders that are related to MEC.', 'modern-events-calendar-lite' ); ?>
                                                            <a href="https://webnus.net/dox/modern-events-calendar/woocommerce/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                        </div>
                                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mec-form-row">
                                                <label class="mec-col-3" for="mec_gateways_wc_after_add"><?php esc_html_e('After Add to Cart', 'modern-events-calendar-lite' ); ?></label>
                                                <div class="mec-col-9">
                                                    <select id="mec_gateways_wc_after_add" name="mec[settings][wc_after_add]">
                                                        <option value="cart" <?php echo((isset($settings['wc_after_add']) and $settings['wc_after_add'] == 'cart') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Redirect to Cart', 'modern-events-calendar-lite' ); ?></option>
                                                        <option value="checkout" <?php echo((isset($settings['wc_after_add']) and $settings['wc_after_add'] == 'checkout') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Redirect to Checkout', 'modern-events-calendar-lite' ); ?></option>
                                                        <option value="optional_cart" <?php echo((isset($settings['wc_after_add']) and $settings['wc_after_add'] == 'optional_cart') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Optional View Cart Button', 'modern-events-calendar-lite' ); ?></option>
                                                        <option value="optional_chckout" <?php echo((isset($settings['wc_after_add']) and $settings['wc_after_add'] == 'optional_chckout') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Optional Checkout Button', 'modern-events-calendar-lite' ); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mec-form-row">
                                                <label class="mec-col-3" for="mec_gateways_wc_booking_form"><?php esc_html_e('MEC Booking Form', 'modern-events-calendar-lite' ); ?></label>
                                                <div class="mec-col-9">
                                                    <select id="mec_gateways_wc_booking_form" name="mec[settings][wc_booking_form]">
                                                        <option value="0" <?php echo((isset($settings['wc_booking_form']) and $settings['wc_booking_form'] == '0') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Disabled', 'modern-events-calendar-lite' ); ?></option>
                                                        <option value="1" <?php echo((isset($settings['wc_booking_form']) and $settings['wc_booking_form'] == '1') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Enabled', 'modern-events-calendar-lite' ); ?></option>
                                                    </select>
                                                    <span class="mec-tooltip">
                                                        <div class="box left">
                                                            <h5 class="title"><?php esc_html_e('Booking Form', 'modern-events-calendar-lite' ); ?></h5>
                                                            <div class="content"><p><?php esc_attr_e('If enabled then users should fill the booking form in MEC and then they will be redirected to checkout.', 'modern-events-calendar-lite' ); ?>
                                                            <a href="https://webnus.net/dox/modern-events-calendar/woocommerce/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite' ); ?></a></p></div>
                                                        </div>
                                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div id="mec_payment_options_wrapper" class="<?php if(isset($settings['wc_status']) and $settings['wc_status'] and class_exists('WooCommerce')) echo 'w-hidden'; ?>">
                                    <div class="mec-form-row" id="mec_gateways_form_container">
                                        <ul>
                                            <?php foreach($gateways as $gateway): ?>
                                            <li id="mec_gateway_id<?php echo esc_attr($gateway->id()); ?>">
                                                <?php $gateway->options_form(); ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="mec-form-row" style="margin-top: 30px;">
                                        <div class="mec-col-12">
                                            <label>
                                                <input type="hidden" name="mec[gateways][op_status]" value="0" />
                                                <input id="mec_gateways_op_status" value="1" type="checkbox" name="mec[gateways][op_status]" <?php if(isset($gateways_options['op_status']) and $gateways_options['op_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable Organizer Payment Module', 'modern-events-calendar-lite' ); ?>
                                            </label>
                                            <span class="mec-tooltip">
                                                <div class="box">
                                                    <h5 class="title"><?php esc_html_e('Organizer Payment', 'modern-events-calendar-lite' ); ?></h5>
                                                    <div class="content"><p><?php esc_attr_e("By enabling this module, organizers can insert their payment credentials to receive the payments directly. This feature needs the Stripe connect payment gateway to work.", 'modern-events-calendar-lite' ); ?></p></div>
                                                </div>
                                                <i title="" class="dashicons-before dashicons-editor-help"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mec-form-row">
                                        <div class="mec-col-12">
                                            <label>
                                                <input type="hidden" name="mec[gateways][gateways_per_event]" value="0" />
                                                <input id="mec_gateways_gateways_per_event" value="1" type="checkbox" name="mec[gateways][gateways_per_event]" <?php if(isset($gateways_options['gateways_per_event']) and $gateways_options['gateways_per_event']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Disable / Enable payment gateways per event', 'modern-events-calendar-lite' ); ?>
                                            </label>
                                            <span class="mec-tooltip">
                                                <div class="box">
                                                    <h5 class="title"><?php esc_html_e('Payment Gateways Per Event', 'modern-events-calendar-lite' ); ?></h5>
                                                    <div class="content"><p><?php esc_attr_e("By enabling this module, event submitters will be able to disable/enable payment gateways per event on the FES Form and on the event add/edit page.", 'modern-events-calendar-lite' ); ?></p></div>
                                                </div>
                                                <i title="" class="dashicons-before dashicons-editor-help"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mec-form-row">
                                        <div class="mec-col-12">
                                            <label>
                                                <input type="hidden" name="mec[settings][skip_payment_step_for_free_bookings]" value="0" />
                                                <input id="mec_gateways_gateways_per_event" value="1" type="checkbox" name="mec[settings][skip_payment_step_for_free_bookings]" <?php if(!isset($settings['skip_payment_step_for_free_bookings']) or (isset($settings['skip_payment_step_for_free_bookings']) and $settings['skip_payment_step_for_free_bookings'])) echo 'checked="checked"'; ?> /> <?php esc_html_e('Skip payment step for free bookings', 'modern-events-calendar-lite' ); ?>
                                            </label>
                                            <span class="mec-tooltip">
                                                <div class="box">
                                                    <h5 class="title"><?php esc_html_e('Skip Payment Step', 'modern-events-calendar-lite' ); ?></h5>
                                                    <div class="content"><p><?php esc_attr_e("If enabled, it will skip payment step for free bookings. You can disable it if you're required by law.", 'modern-events-calendar-lite' ); ?></p></div>
                                                </div>
                                                <i title="" class="dashicons-before dashicons-editor-help"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mec-form-row">
                                        <div class="mec-col-12">
                                            <label>
                                                <input type="hidden" name="mec[settings][booking_auto_refund]" value="0" />
                                                <input id="mec_gateways_auto_refund" value="1" type="checkbox" name="mec[settings][booking_auto_refund]" <?php if(isset($settings['booking_auto_refund']) and $settings['booking_auto_refund']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Automatically refund the payment', 'modern-events-calendar-lite' ); ?>
                                            </label>
                                            <span class="mec-tooltip">
                                                <div class="box">
                                                    <h5 class="title"><?php esc_html_e('Auto Refund', 'modern-events-calendar-lite' ); ?></h5>
                                                    <div class="content"><p><?php esc_attr_e("Automatically refund the payment when a booking paid by gateways like Stripe gets canceled.", 'modern-events-calendar-lite' ); ?></p></div>
                                                </div>
                                                <i title="" class="dashicons-before dashicons-editor-help"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mec-form-row" style="margin-top: 30px;">
                                        <div class="mec-col-12">
                                            <label>
                                                <input type="hidden" name="mec[settings][booking_partial_payment]" value="0" />
                                                <input id="mec_booking_partial_payment" onchange="jQuery('#mec_booking_partial_payment_options').toggleClass('w-hidden');" value="1" type="checkbox" name="mec[settings][booking_partial_payment]" <?php if(isset($settings['booking_partial_payment']) and $settings['booking_partial_payment']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Accept partial payment', 'modern-events-calendar-lite' ); ?>
                                            </label>
                                            <span class="mec-tooltip">
                                                <div class="box">
                                                    <h5 class="title"><?php esc_html_e('Partial Payment', 'modern-events-calendar-lite' ); ?></h5>
                                                    <div class="content"><p><?php esc_attr_e("You can accept a partial of payment upon booking and manage the rest of payment on your own. For example receive 20% of booking amount online and manage the rest in cash or other payment systems.", 'modern-events-calendar-lite' ); ?></p></div>
                                                </div>
                                                <i title="" class="dashicons-before dashicons-editor-help"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div id="mec_booking_partial_payment_options" class="<?php if(!isset($settings['booking_partial_payment']) or (isset($settings['booking_partial_payment']) and !$settings['booking_partial_payment'])) echo 'w-hidden'; ?>">
                                        <div class="mec-form-row">
                                            <label class="mec-col-3" for="mec_booking_payable"><?php esc_html_e('Payable', 'modern-events-calendar-lite' ); ?></label>
                                            <div class="mec-col-9">
                                                <input type="number" min="1" id="mec_booking_payable" name="mec[settings][booking_payable]" value="<?php echo ((isset($settings['booking_payable']) and trim($settings['booking_payable']) != '') ? $settings['booking_payable'] : '100'); ?>" />
                                                <select id="mec_booking_payable_type" name="mec[settings][booking_payable_type]" title="<?php esc_attr_e('Payable Type', 'modern-events-calendar-lite' ); ?>">
                                                    <option value="percent" <?php echo ((isset($settings['booking_payable_type']) and $settings['booking_payable_type'] === 'percent') ? 'selected' : ''); ?>><?php esc_attr_e('Percent (%)', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="amount" <?php echo ((isset($settings['booking_payable_type']) and $settings['booking_payable_type'] === 'amount') ? 'selected' : ''); ?>><?php esc_attr_e('Amount ($)', 'modern-events-calendar-lite' ); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mec-form-row">
                                            <div class="mec-col-12">
                                                <label>
                                                    <input type="hidden" name="mec[settings][booking_payable_per_event]" value="0" />
                                                    <input id="mec_booking_payable_per_event" value="1" type="checkbox" name="mec[settings][booking_payable_per_event]" <?php if(isset($settings['booking_payable_per_event']) and $settings['booking_payable_per_event']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Ability to edit payable options per event', 'modern-events-calendar-lite' ); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mec-form-row" style="margin-top: 30px;">
                                        <div class="mec-col-12">
                                            <label class="mec-col-3" for="mec_settings_gateways_debug_email"><?php esc_html_e('Debug Paypal Standard Gateway', 'modern-events-calendar-lite' ); ?></label>
                                            <div class="mec-col-9">
                                                <input type="text" id="mec_settings_gateways_debug_email" name="mec[settings][gateways_debug_email]" value="<?php echo ((isset($settings['gateways_debug_email']) and trim($settings['gateways_debug_email']) != '') ? $settings['gateways_debug_email'] : ''); ?>" />
                                                <p style="color: red;"><?php echo esc_html__("If you are encountering problems with the PayPal Standard gateway, you can enter your email here to receive a debug email that contains information received from the PayPal API. Please be aware that this email may contain sensitive information, so proceed with caution.", 'modern-events-calendar-lite' ); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mec-form-row">
                                    <?php wp_nonce_field('mec_options_form'); ?>
                                    <button style="display: none;" id="mec_gateways_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite' ); ?></button>
                                </div>
                            </div>
                        </div>

                        <div id="cart_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('MEC Cart', 'modern-events-calendar-lite' ); ?></h4>
                            <div class="mec-form-row">
                                <div class="mec-col-12">
                                    <label>
                                        <input type="hidden" name="mec[settings][mec_cart_status]" value="0" />
                                        <input id="mec_gateways_mec_cart_status" onchange="jQuery('#mec_gateways_mec_cart_status_guide, #mec_wc_status_mec_cart_error').toggleClass('w-hidden');" value="1" type="checkbox" name="mec[settings][mec_cart_status]" <?php if(isset($settings['mec_cart_status']) and $settings['mec_cart_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Use MEC Cart System', 'modern-events-calendar-lite' ); ?>
                                    </label>
                                    <p><?php esc_html_e("If you don't want to use WooCommerce for any reason you can use MEC Cart for adding a simple cart and checkout system to your website.", 'modern-events-calendar-lite' ); ?></p>
                                    <div id="mec_gateways_mec_cart_status_guide" class="<?php if(!isset($settings['mec_cart_status']) or (isset($settings['mec_cart_status']) and !$settings['mec_cart_status'])) echo 'w-hidden'; ?>">
                                        <p style="margin-top: 20px;"><?php esc_html_e("You cannot use following MEC features while using MEC Cart.", 'modern-events-calendar-lite' ); ?></p>
                                        <ul>
                                            <li><?php esc_html_e('WooCommerce as Payment Gateway', 'modern-events-calendar-lite' ); ?></li>
                                            <li><?php esc_html_e('Currency Per Event', 'modern-events-calendar-lite' ); ?></li>
                                            <li><?php esc_html_e('Disable Gateways Per Event', 'modern-events-calendar-lite' ); ?></li>
                                            <li><?php esc_html_e('Thank You Page Per Event', 'modern-events-calendar-lite' ); ?></li>
                                            <li><?php esc_html_e('Stripe Connect Gateway', 'modern-events-calendar-lite' ); ?></li>
                                            <li><?php esc_html_e('Pay By WooCommerce Gateway', 'modern-events-calendar-lite' ); ?></li>
                                            <li><?php esc_html_e('Organizer Payment Module', 'modern-events-calendar-lite' ); ?></li>
                                        </ul>

                                        <div class="mec-form-row" style="margin-top: 40px;">
                                            <label class="mec-col-3" for="mec_settings_cart_page"><?php esc_html_e('Cart Page', 'modern-events-calendar-lite' ); ?></label>
                                            <div class="mec-col-9">
                                                <select id="mec_settings_cart_page" name="mec[settings][cart_page]">
                                                    <option value="">----</option>
                                                    <?php foreach($pages as $page): ?>
                                                        <option <?php echo ((isset($settings['cart_page']) and $settings['cart_page'] == $page->ID) ? 'selected="selected"' : ''); ?> value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <p class="description"><?php echo sprintf(esc_html__('Put %s shortcode into the page.', 'modern-events-calendar-lite' ), '<code>[mec-cart]</code>'); ?></p>
                                            </div>
                                        </div>
                                        <div class="mec-form-row">
                                            <label class="mec-col-3" for="mec_settings_checkout_page"><?php esc_html_e('Checkout Page', 'modern-events-calendar-lite' ); ?></label>
                                            <div class="mec-col-9">
                                                <select id="mec_settings_checkout_page" name="mec[settings][checkout_page]">
                                                    <option value="">----</option>
                                                    <?php foreach($pages as $page): ?>
                                                        <option <?php echo ((isset($settings['checkout_page']) and $settings['checkout_page'] == $page->ID) ? 'selected="selected"' : ''); ?> value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <p class="description"><?php echo sprintf(esc_html__('Put %s shortcode into the page.', 'modern-events-calendar-lite' ), '<code>[mec-checkout]</code>'); ?></p>
                                            </div>
                                        </div>
                                        <div class="mec-form-row">
                                            <label class="mec-col-3" for="mec_settings_cart_after_add"><?php esc_html_e('After Add to Cart', 'modern-events-calendar-lite' ); ?></label>
                                            <div class="mec-col-9">
                                                <select id="mec_settings_cart_after_add" name="mec[settings][cart_after_add]">
                                                    <option value="cart" <?php echo((isset($settings['cart_after_add']) and $settings['cart_after_add'] == 'cart') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Redirect to Cart', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="checkout" <?php echo((isset($settings['cart_after_add']) and $settings['cart_after_add'] == 'checkout') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Redirect to Checkout', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="optional_cart" <?php echo((isset($settings['cart_after_add']) and $settings['cart_after_add'] == 'optional_cart') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Optional View Cart Button', 'modern-events-calendar-lite' ); ?></option>
                                                    <option value="optional_chckout" <?php echo((isset($settings['cart_after_add']) and $settings['cart_after_add'] == 'optional_chckout') ? 'selected="selected"' : ''); ?>><?php esc_html_e('Optional Checkout Button', 'modern-events-calendar-lite' ); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mec-form-row">
                                            <div class="mec-col-12">
                                                <label>
                                                    <input type="hidden" name="mec[settings][mec_cart_invoice]" value="0" />
                                                    <input id="mec_gateways_mec_cart_invoice" value="1" type="checkbox" name="mec[settings][mec_cart_invoice]" <?php if(isset($settings['mec_cart_invoice']) and $settings['mec_cart_invoice']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable Cart Invoice', 'modern-events-calendar-lite' ); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="webhooks_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php esc_html_e('Webhooks', 'modern-events-calendar-lite' ); ?></h4>

                            <?php if(!$this->main->getPRO()): ?>
                                <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite' ), '<a href="'.esc_url($this->main->get_pro_link()).'" target="_blank">'.esc_html__('Pro version of Modern Events Calendar', 'modern-events-calendar-lite' ).'</a>'); ?></div>
                            <?php else: ?>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][webhooks_status]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][webhooks_status]" <?php if(isset($settings['webhooks_status']) and $settings['webhooks_status']) echo 'checked="checked"'; ?> /> <?php esc_html_e('Enable webhooks module', 'modern-events-calendar-lite' ); ?>
                                </label>
                                <p><?php esc_attr_e("After enabling and saving the settings, you should reload the page to see a new menu on the Dashboard > Webhooks", 'modern-events-calendar-lite' ); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php endif; // If Booking Enabled ?>

                        <div class="mec-options-fields">
                            <?php wp_nonce_field('mec_options_form'); ?>
                            <?php if($multilingual): ?>
                            <input name="mec_locale" type="hidden" value="<?php echo esc_attr($locale); ?>" />
                            <?php endif; ?>
                            <button style="display: none;" id="mec_booking_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite' ); ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="wns-be-footer">
        <a id="" class="dpr-btn dpr-save-btn"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite' ); ?></a>
    </div>

</div>

<?php $this->factory->params('footer', '<script>
jQuery(document).ready(function()
{
    jQuery(".dpr-save-btn").on("click", function(event)
    {
        event.preventDefault();
        jQuery("#mec_booking_form_button").trigger("click");
    });
});

jQuery("#mec_gateways_form_container .mec-required").on("change", function()
{
    var val = jQuery(this).val();
    if(val)
    {
        // Remove Focus Style
        jQuery(this).removeClass("mec-mandatory");
    }
});

jQuery("#mec_booking_form").on("submit", function(event)
{
    event.preventDefault();

    var validated = true;
    var first_field;

    jQuery("#mec_gateways_form_container").find(".mec-required").each(function()
    {
        // Remove Focus Style
        jQuery(this).removeClass("mec-mandatory");

        var val = jQuery(this).val();
        if(jQuery(this).is(":visible") && !val)
        {
            // Add Focus Style
            jQuery(this).addClass("mec-mandatory");

            validated = false;
            if(!first_field) first_field = this;
        }
    });

    if(!validated && first_field)
    {
        jQuery(first_field).focus();
        jQuery("html, body").animate(
        {
            scrollTop: (jQuery(first_field).offset().top - 200)
        }, 500);

        return false;
    }

    // Add loading Class to the button
    jQuery(".dpr-save-btn").addClass("loading").text("'.esc_js(esc_attr__('Saved', 'modern-events-calendar-lite' )).'");
    jQuery("<div class=\"wns-saved-settings\">'.esc_js(esc_attr__('Settings Saved!', 'modern-events-calendar-lite' )).'</div>").insertBefore("#wns-be-content");

    if(jQuery(".mec-purchase-verify").text() != "'.esc_js(esc_attr__('Verified', 'modern-events-calendar-lite' )).'")
    {
        jQuery(".mec-purchase-verify").text("'.esc_js(esc_attr__('Checking ...', 'modern-events-calendar-lite' )).'");
    }

    var settings = jQuery("#mec_booking_form").serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=mec_save_settings&"+settings,
        beforeSend: function () {
            jQuery(".wns-be-main").append("<div class=\"mec-loarder-wrap mec-settings-loader\"><div class=\"mec-loarder\"><div></div><div></div><div></div></div></div>");
        },
        success: function(data)
        {
            // Remove the loading Class to the button
            setTimeout(function()
            {
                jQuery(".dpr-save-btn").removeClass("loading").text("'.esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite' )).'");
                jQuery(".wns-saved-settings").remove();
                jQuery(".mec-loarder-wrap").remove();
                if(jQuery(".mec-purchase-verify").text() != "'.esc_js(esc_attr__('Verified', 'modern-events-calendar-lite' )).'")
                {
                    jQuery(".mec-purchase-verify").text("'.esc_js(esc_attr__('Please Refresh Page', 'modern-events-calendar-lite' )).'");
                }
            }, 1000);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            // Remove the loading Class to the button
            setTimeout(function()
            {
                jQuery(".dpr-save-btn").removeClass("loading").text("'.esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite' )).'");
                jQuery(".wns-saved-settings").remove();
                jQuery(".mec-loarder-wrap").remove();
            }, 1000);
        }
    });
});
</script>');