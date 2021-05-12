<?php
/** no direct access **/
defined('MECEXEC') or die();

$single = new MEC_skin_single();
wp_enqueue_style('mec-lity-style', $this->main->asset('packages/lity/lity.min.css'));
wp_enqueue_script('mec-lity-script', $this->main->asset('packages/lity/lity.min.js'));

$booking_options = get_post_meta(get_the_ID(), 'mec_booking', true);
if(!is_array($booking_options)) $booking_options = array();

//Compatibility with Rank Math
$rank_math_options = '';
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(is_plugin_active('schema-markup-rich-snippets/schema-markup-rich-snippets.php')) $rank_math_options = get_post_meta(get_the_ID(), 'rank_math_rich_snippet', true);

$bookings_limit_for_users = isset($booking_options['bookings_limit_for_users']) ? $booking_options['bookings_limit_for_users'] : 0;
?>
<div class="mec-wrap <?php echo $event_colorskin; ?> clearfix <?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->uniqueid; ?>">
	<?php do_action('mec_top_single_event', get_the_ID()); ?>
	<article class="row mec-single-event">

		<!-- start breadcrumbs -->
		<?php
		$breadcrumbs_settings = isset($settings['breadcrumbs']) ? $settings['breadcrumbs'] : ''; if($breadcrumbs_settings == '1'): ?>
        <div class="mec-breadcrumbs">
            <?php $single->display_breadcrumb_widget(get_the_ID()); ?>
        </div>
		<?php endif; ?>
		<!-- end breadcrumbs -->

		<div class="col-md-8">
			<div class="mec-events-event-image"><?php echo $event->data->thumbnails['full']; ?></div>
			<div class="mec-event-content">
				<?php echo $this->main->display_cancellation_reason($event, $this->display_cancellation_reason); ?>
				<h1 class="mec-single-title"><?php the_title(); ?></h1>
				<div class="mec-single-event-description mec-events-content"><?php the_content(); ?></div>
			</div>

			<?php do_action('mec_single_after_content', $event); ?>

			<!-- Custom Data Fields -->
			<?php $this->display_data_fields($event); ?>

			<div class="mec-event-info-mobile"></div>

			<!-- Export Module -->
			<?php echo $this->main->module('export.details', array('event'=>$event)); ?>

			<!-- Countdown module -->
			<?php if($this->main->can_show_countdown_module($event)): ?>
            <div class="mec-events-meta-group mec-events-meta-group-countdown">
                <?php echo $this->main->module('countdown.details', array('event'=>$this->events)); ?>
            </div>
			<?php endif; ?>

			<!-- Hourly Schedule -->
			<?php $this->display_hourly_schedules_widget($event); ?>

			<!-- Booking Module -->
			<?php if(!empty($event->date)):
			   if ( $this->main->is_sold( $event ) and count( $event->dates ) <= 1 ): ?>
				  <?php
				  $event_id        = $event->ID;
				  $dates = (isset($event->dates) ? $event->dates : array($event->date));
				  $occurrence_time = (isset($dates[0]['start']['timestamp']) ? $dates[0]['start']['timestamp'] : strtotime($dates[0]['start']['date']));
				  $tickets         = get_post_meta( $event_id, 'mec_tickets', true );
				  $book         = $this->getBook();
				  $availability = $book->get_tickets_availability( $event_id, $occurrence_time );

				  $sales_end                   = 0;
				  $ticket_sales_ended_messages = array();
				  $stop_selling                = '';
				  foreach ( $tickets as $ticket_id => $ticket ) {

					 $ticket_limit = (isset($availability[$ticket_id]) ? $availability[$ticket_id] : -1);
					 $ticket_name  = isset( $ticket['name'] ) ? '<strong>' . $ticket['name'] . '</strong>' : '';

					 $key          = 'stop_selling_' . $ticket_id;
					 if ( !isset( $availability[ $key ] ) ) {

						continue;
					 }

					 if ( true === $availability[ $key ] ) {

						$sales_end++;
						$ticket_sales_ended_messages[ $ticket_id ] = sprintf( __( 'The %s ticket sales has ended!', 'modern-events-calendar-lite' ), $ticket_name );
					 }
				  }

				  $tickets_sales_end = false;
				  if(count($tickets) === $sales_end){

					 $tickets_sales_end = true;
				  }

				  if ( !empty( $ticket_sales_ended_messages ) ):

					 foreach ( $ticket_sales_ended_messages as $ticket_id => $message ) :

						?>
						<div id="mec-ticket-message-<?php echo $ticket_id; ?>" class="mec-ticket-unavailable-spots mec-error <?php echo( $ticket_limit == '0' ? '' : 'mec-util-hidden' ); ?>"><div><?php echo $message; ?></div></div>
					 <?php
					 endforeach;
					 ?>

				  <?php else: ?>
					 <div id="mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>" class="mec-sold-tickets warning-msg"><?php _e( 'Sold out!', 'modern-events-calendar-lite' );do_action( 'mec_booking_sold_out', $event, null, null, array( $event->date ) ); ?> </div>
				  <?php endif; ?>
			<?php elseif($this->main->can_show_booking_module($event)): ?>
				<?php $data_lity_class = ''; if(isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ) $data_lity_class = 'lity-hide '; ?>
				<div id="mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>" class="<?php echo $data_lity_class; ?>mec-events-meta-group mec-events-meta-group-booking">
					<?php
					if(isset($settings['booking_user_login']) and $settings['booking_user_login'] == '1' and !is_user_logged_in() ) {
						echo do_shortcode('[MEC_login]');
					} elseif (!is_user_logged_in() and isset($booking_options['bookings_limit_for_users']) and $booking_options['bookings_limit_for_users'] == '1' ) {
						echo do_shortcode('[MEC_login]');
					} else {
						echo $this->main->module('booking.default', array('event'=>$this->events));
					}
					?>
				</div>
			<?php endif; endif; ?>

			<!-- Tags -->
			<div class="mec-events-meta-group mec-events-meta-group-tags">
                <?php echo get_the_term_list($event->ID, apply_filters('mec_taxonomy_tag', ''), __('Tags: ', 'modern-events-calendar-lite'), ', ', '<br />'); ?>
			</div>

		</div>

		<?php if(!is_active_sidebar('mec-single-sidebar')): ?>
			<div class="col-md-4">

				<div class="mec-event-info-desktop mec-event-meta mec-color-before mec-frontbox">
					<?php
					// Event Date and Time
					if(isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start']))
					{
						$midnight_event = $this->main->is_midnight_event($event);
						?>
						<div class="mec-single-event-date">
							<i class="mec-sl-calendar"></i>
							<h3 class="mec-date"><?php _e('Date', 'modern-events-calendar-lite'); ?></h3>
							<dl>
								<?php if($midnight_event): ?>
									<dd><abbr class="mec-events-abbr"><?php echo $this->main->dateify($event, $this->date_format1); ?></abbr></dd>
								<?php else: ?>
									<dd><abbr class="mec-events-abbr"><?php echo $this->main->date_label((trim($occurrence) ? array('date'=>$occurrence) : $event->date['start']), (trim($occurrence_end_date) ? array('date'=>$occurrence_end_date) : (isset($event->date['end']) ? $event->date['end'] : NULL)), $this->date_format1); ?></abbr></dd>
								<?php endif; ?>
							</dl>
							<?php echo $this->main->holding_status($event); ?>
						</div>

						<?php
						if(isset($event->data->meta['mec_hide_time']) and $event->data->meta['mec_hide_time'] == '0')
						{
							$time_comment = isset($event->data->meta['mec_comment']) ? $event->data->meta['mec_comment'] : '';
							$allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
							?>
							<div class="mec-single-event-time">
								<i class="mec-sl-clock " style=""></i>
								<h3 class="mec-time"><?php _e('Time', 'modern-events-calendar-lite'); ?></h3>
								<i class="mec-time-comment"><?php echo (isset($time_comment) ? $time_comment : ''); ?></i>
								<dl>
									<?php if($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])): ?>
										<dd><abbr class="mec-events-abbr"><?php echo $event->data->time['start']; ?><?php echo (trim($event->data->time['end']) ? ' - '.$event->data->time['end'] : ''); ?></abbr></dd>
									<?php else: ?>
										<dd><abbr class="mec-events-abbr"><?php echo $this->main->m('all_day', __('All Day' , 'modern-events-calendar-lite')); ?></abbr></dd>
									<?php endif; ?>
								</dl>
							</div>
							<?php
						}
					}
					?>

					<!-- Local Time Module -->
					<?php echo $this->main->module('local-time.details', array('event'=>$event)); ?>

					<?php
					// Event Cost
					if(isset($event->data->meta['mec_cost']) and $event->data->meta['mec_cost'] != '')
					{
						?>
						<div class="mec-event-cost">
							<i class="mec-sl-wallet"></i>
							<h3 class="mec-cost"><?php echo $this->main->m('cost', __('Cost', 'modern-events-calendar-lite')); ?></h3>
							<dl><dd class="mec-events-event-cost"><?php echo (is_numeric($event->data->meta['mec_cost']) ? $this->main->render_price($event->data->meta['mec_cost'], $event->ID) : $event->data->meta['mec_cost']); ?></dd></dl>
						</div>
						<?php
					}
					?>

					<?php do_action('mec_single_virtual_badge', $event->data ); ?>
					<?php do_action('mec_single_zoom_badge', $event->data ); ?>

					<?php
					// More Info
					if(isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://')
					{
						?>
						<div class="mec-event-more-info">
							<i class="mec-sl-info"></i>
							<h3 class="mec-cost"><?php echo $this->main->m('more_info_link', __('More Info', 'modern-events-calendar-lite')); ?></h3>
							<dl><dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php echo ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : __('Read More', 'modern-events-calendar-lite')); ?></a></dd></dl>
						</div>
						<?php
					}
					?>

					<?php
					// Event labels
					if(isset($event->data->labels) && !empty($event->data->labels))
					{
						$mec_items = count($event->data->labels);
						$mec_i = 0; ?>
						<div class="mec-single-event-label">
							<i class="mec-fa-bookmark-o"></i>
							<h3 class="mec-cost"><?php echo $this->main->m('taxonomy_labels', __('Labels', 'modern-events-calendar-lite')); ?></h3>
							<?php foreach($event->data->labels as $labels=>$label) :
								$seperator = (++$mec_i === $mec_items ) ? '' : ',';
								echo '<dl><dd style="color:' . $label['color'] . '">' . $label["name"] . $seperator . '</dd></dl>';
							endforeach; ?>
						</div>
						<?php
					}
					?>


					<?php
					// Event Location
					if(isset($event->data->meta['mec_location_id']) and isset($event->data->locations[$event->data->meta['mec_location_id']]) and !empty($event->data->locations[$event->data->meta['mec_location_id']]))
					{
						$location = $event->data->locations[$event->data->meta['mec_location_id']];
						?>
						<div class="mec-single-event-location">
							<?php if($location['thumbnail']): ?>
								<img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
							<?php endif; ?>
							<i class="mec-sl-location-pin"></i>
							<h3 class="mec-events-single-section-title mec-location"><?php echo $this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite')); ?></h3>
							<dl>
							<dd class="author fn org"><?php echo $this->get_location_html($location); ?></dd>
							<dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address></dd>

							<?php if(isset($location['url']) and trim($location['url'])): ?>
								<dd class="mec-location-url">
									<i class="mec-sl-sitemap"></i>
									<h6><?php _e('Website', 'modern-events-calendar-lite'); ?></h6>
									<span><a href="<?php echo (strpos($location['url'], 'http') === false ? 'http://'.$location['url'] : $location['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $location['url']; ?></a></span>
								</dd>
							<?php endif;
							$location_description_setting = isset( $settings['location_description'] ) ? $settings['location_description'] : ''; $location_terms = get_the_terms($event->data, 'mec_location');  if($location_description_setting == '1'): foreach($location_terms as $location_term) { if ($location_term->term_id == $location['id'] ) {  if(isset($location_term->description) && !empty($location_term->description)): ?>
								<dd class="mec-location-description">
									<p><?php echo $location_term->description;?></p>
								</dd>
							<?php endif; } } endif; ?>
							</dl>
						</div>
						<?php
						$this->show_other_locations($event); // Show Additional Locations
					}
					?>

					<?php
					// Event Categories
					if(isset($event->data->categories) and !empty($event->data->categories))
					{
						?>
						<div class="mec-single-event-category">
							<i class="mec-sl-folder"></i>
							<dt><?php echo $this->main->m('taxonomy_categories', __('Category', 'modern-events-calendar-lite')); ?></dt>
							<?php
							foreach($event->data->categories as $category)
							{
								$icon = get_metadata('term', $category['id'], 'mec_cat_icon', true);
								$icon = isset($icon) && $icon != '' ? '<i class="'.$icon.' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';
								echo '<dl><dd class="mec-events-event-categories">
                                <a href="'.get_term_link($category['id'], 'mec_category').'" class="mec-color-hover" rel="tag">'.$icon . $category['name'] .'</a></dd></dl>';
							}
							?>
						</div>
						<?php
					}
					?>
					<?php do_action('mec_single_event_under_category', $event); ?>
					<?php
					// Event Organizer
					if(isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) && !empty($event->data->organizers[$event->data->meta['mec_organizer_id']]))
					{
						$organizer = $event->data->organizers[$event->data->meta['mec_organizer_id']];
						?>
						<div class="mec-single-event-organizer">
							<?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
								<img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
							<?php endif; ?>
							<h3 class="mec-events-single-section-title"><?php echo $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite')); ?></h3>
							<dl>
							<?php if(isset($organizer['thumbnail'])): ?>
								<dd class="mec-organizer">
									<i class="mec-sl-home"></i>
									<h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
								</dd>
							<?php endif;
							if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
								<dd class="mec-organizer-tel">
									<i class="mec-sl-phone"></i>
									<h6><?php _e('Phone', 'modern-events-calendar-lite'); ?></h6>
									<a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
								</dd>
							<?php endif;
							if(isset($organizer['email']) && !empty($organizer['email'])): ?>
								<dd class="mec-organizer-email">
									<i class="mec-sl-envelope"></i>
									<h6><?php _e('Email', 'modern-events-calendar-lite'); ?></h6>
									<a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
								</dd>
							<?php endif;
							if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
								<dd class="mec-organizer-url">
									<i class="mec-sl-sitemap"></i>
									<h6><?php _e('Website', 'modern-events-calendar-lite'); ?></h6>
									<span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://'.$organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
									<?php do_action('mec_single_default_organizer', $organizer); ?>
								</dd>
							<?php endif;
							$organizer_description_setting = isset( $settings['organizer_description'] ) ? $settings['organizer_description'] : ''; $organizer_terms = get_the_terms($event->data, 'mec_organizer');  if($organizer_description_setting == '1'): foreach($organizer_terms as $organizer_term) { if ($organizer_term->term_id == $organizer['id'] ) {  if(isset($organizer_term->description) && !empty($organizer_term->description)): ?>
								<dd class="mec-organizer-description">
									<p><?php echo $organizer_term->description;?></p>
								</dd>
							<?php endif; } } endif; ?>
							</dl>
						</div>
						<?php
						$this->show_other_organizers($event); // Show Additional Organizers
					}
					?>

					<!-- Register Booking Button -->
					<?php if($this->main->can_show_booking_module($event)): ?>
						<?php $data_lity = $data_lity_class =  ''; if(isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ){ /* $data_lity = 'onclick="openBookingModal();"'; */  $data_lity_class = 'mec-booking-data-lity'; }  ?>
						<a class="mec-booking-button mec-bg-color <?php echo $data_lity_class; ?> <?php if(isset($settings['single_booking_style']) and $settings['single_booking_style'] != 'modal' ) echo 'simple-booking'; ?>" href="#mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>" <?php echo $data_lity; ?>><?php echo esc_html($this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite'))); ?></a>
					<?php elseif(isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://'): ?>
						<a class="mec-booking-button mec-bg-color" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php if(isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) echo esc_html__(trim($event->data->meta['mec_more_info_title']), 'modern-events-calendar-lite'); else echo esc_html($this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite'))); ?></a>
					<?php endif; ?>

				</div>

				<!-- Speakers Module -->
				<?php echo $this->main->module('speakers.details', array('event'=>$event)); ?>

				<!-- Attendees List Module -->
				<?php echo $this->main->module('attendees-list.details', array('event'=>$event)); ?>

				<!-- Next Previous Module -->
				<?php echo $this->main->module('next-event.details', array('event'=>$event)); ?>

				<!-- Links Module -->
				<?php echo $this->main->module('links.details', array('event'=>$event)); ?>

				<!-- Weather Module -->
				<?php echo $this->main->module('weather.details', array('event'=>$event)); ?>

				<!-- Google Maps Module -->
				<div class="mec-events-meta-group mec-events-meta-group-gmap">
					<?php echo $this->main->module('googlemap.details', array('event'=>$this->events)); ?>
				</div>

				<!-- QRCode Module -->
				<?php echo $this->main->module('qrcode.details', array('event'=>$event)); ?>

				<!-- Widgets -->
				<?php dynamic_sidebar(); ?>

			</div>
		<?php else: ?>
			<div class="col-md-4">
				<?php if($single->found_value('data_time', $settings) == 'on' || $single->found_value('local_time', $settings) == 'on' || $single->found_value('event_cost', $settings) == 'on' || $single->found_value('more_info', $settings) == 'on' || $single->found_value('event_label', $settings) == 'on' || $single->found_value('event_location', $settings) == 'on' || $single->found_value('event_categories', $settings) == 'on' || $single->found_value('event_orgnizer', $settings) == 'on' || $single->found_value('register_btn', $settings) == 'on'  ) : ?>
					<div class="mec-event-info-desktop mec-event-meta mec-color-before mec-frontbox">
						<?php
						// Event Date and Time
						if(isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start']) and $single->found_value('data_time', $settings) == 'on')
						{
							$midnight_event = $this->main->is_midnight_event($event);
							?>
							<div class="mec-single-event-date">
								<i class="mec-sl-calendar"></i>
								<h3 class="mec-date"><?php _e('Date', 'modern-events-calendar-lite'); ?></h3>
								<dl>
								<?php if($midnight_event): ?>
									<dd><abbr class="mec-events-abbr"><?php echo $this->main->dateify($event, $this->date_format1); ?></abbr></dd>
								<?php else: ?>
									<dd><abbr class="mec-events-abbr"><?php echo $this->main->date_label((trim($occurrence) ? array('date'=>$occurrence) : $event->date['start']), (trim($occurrence_end_date) ? array('date'=>$occurrence_end_date) : (isset($event->date['end']) ? $event->date['end'] : NULL)), $this->date_format1); ?></abbr></dd>
								<?php endif; ?>
								</dl>
								<?php echo $this->main->holding_status($event); ?>
							</div>

							<?php
							if(isset($event->data->meta['mec_hide_time']) and $event->data->meta['mec_hide_time'] == '0')
							{
								$time_comment = isset($event->data->meta['mec_comment']) ? $event->data->meta['mec_comment'] : '';
								$allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
								?>
								<div class="mec-single-event-time">
									<i class="mec-sl-clock " style=""></i>
									<h3 class="mec-time"><?php _e('Time', 'modern-events-calendar-lite'); ?></h3>
									<i class="mec-time-comment"><?php echo (isset($time_comment) ? $time_comment : ''); ?></i>
									<dl>
									<?php if($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])): ?>
										<dd><abbr class="mec-events-abbr"><?php echo $event->data->time['start']; ?><?php echo (trim($event->data->time['end']) ? ' - '.$event->data->time['end'] : ''); ?></abbr></dd>
									<?php else: ?>
										<dd><abbr class="mec-events-abbr"><?php echo $this->main->m('all_day', __('All Day' , 'modern-events-calendar-lite')); ?></abbr></dd>
									<?php endif; ?>
									</dl>
								</div>
								<?php
							}
						}

						// Local Time Module
						if($single->found_value('local_time', $settings) == 'on') echo $this->main->module('local-time.details', array('event'=>$event));
						?>

						<?php
						// Event Cost
						if(isset($event->data->meta['mec_cost']) and $event->data->meta['mec_cost'] != '' and $single->found_value('event_cost', $settings) == 'on')
						{
							?>
							<div class="mec-event-cost">
								<i class="mec-sl-wallet"></i>
								<h3 class="mec-cost"><?php echo $this->main->m('cost', __('Cost', 'modern-events-calendar-lite')); ?></h3>
								<dl><dd class="mec-events-event-cost"><?php echo (is_numeric($event->data->meta['mec_cost']) ? $this->main->render_price($event->data->meta['mec_cost'], $event->ID) : $event->data->meta['mec_cost']); ?></dd></dl>
							</div>
							<?php
						}
						?>

						<?php
						// More Info
						if(isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://' and $single->found_value('more_info', $settings) == 'on')
						{
							?>
							<div class="mec-event-more-info">
								<i class="mec-sl-info"></i>
								<h3 class="mec-cost"><?php echo $this->main->m('more_info_link', __('More Info', 'modern-events-calendar-lite')); ?></h3>
								<dl><dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php echo ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : __('Read More', 'modern-events-calendar-lite')); ?></a></dd></dl>
							</div>
							<?php
						}
						?>

						<?php
						// Event labels
						if(isset($event->data->labels) and !empty($event->data->labels) and $single->found_value('event_label', $settings) == 'on')
						{
							$mec_items = count($event->data->labels);
							$mec_i = 0; ?>
							<div class="mec-single-event-label">
								<i class="mec-fa-bookmark-o"></i>
								<h3 class="mec-cost"><?php echo $this->main->m('taxonomy_labels', __('Labels', 'modern-events-calendar-lite')); ?></h3>
								<?php foreach($event->data->labels as $labels=>$label) :
									$seperator = (++$mec_i === $mec_items ) ? '' : ',';
									echo '<dl><dd style="color:' . $label['color'] . '">' . $label["name"] . $seperator . '</dd></dl>';
								endforeach; ?>
							</div>
							<?php
						}
						?>

						<?php do_action('mec_single_virtual_badge', $event->data ); ?>
						<?php do_action('mec_single_zoom_badge', $event->data ); ?>

						<?php
						// Event Location
						if(isset($event->data->locations[$event->data->meta['mec_location_id']]) and !empty($event->data->locations[$event->data->meta['mec_location_id']]) and $single->found_value('event_location', $settings) == 'on')
						{
							$location = $event->data->locations[$event->data->meta['mec_location_id']];
							?>
							<div class="mec-single-event-location">
								<?php if($location['thumbnail']): ?>
									<img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
								<?php endif; ?>
								<i class="mec-sl-location-pin"></i>
								<h3 class="mec-events-single-section-title mec-location"><?php echo $this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite')); ?></h3>
								<dl>
								<dd class="author fn org"><?php echo $this->get_location_html($location); ?></dd>
								<dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address></dd>
								<?php if(isset($location['url']) and trim($location['url'])): ?>
									<dd class="mec-location-url">
										<i class="mec-sl-sitemap"></i>
										<h6><?php _e('Website', 'modern-events-calendar-lite'); ?></h6>
										<span><a href="<?php echo (strpos($location['url'], 'http') === false ? 'http://'.$location['url'] : $location['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $location['url']; ?></a></span>
									</dd>
								<?php endif;
								$location_description_setting = isset( $settings['location_description'] ) ? $settings['location_description'] : ''; $location_terms = get_the_terms($event->data, 'mec_location');  if($location_description_setting == '1'): foreach($location_terms as $location_term) { if ($location_term->term_id == $location['id'] ) {  if(isset($location_term->description) && !empty($location_term->description)): ?>
									<dd class="mec-location-description">
										<p><?php echo $location_term->description;?></p>
									</dd>
								<?php endif; } } endif; ?>
								</dl>
							</div>
							<?php
							$this->show_other_locations($event); // Show Additional Locations
						}
						?>

						<?php
						// Event Categories
						if(isset($event->data->categories) and !empty($event->data->categories) and $single->found_value('event_categories', $settings) == 'on')
						{
							?>
							<div class="mec-single-event-category">
								<i class="mec-sl-folder"></i>
								<dt><?php echo $this->main->m('taxonomy_categories', __('Category', 'modern-events-calendar-lite')); ?></dt>
								<?php
								foreach($event->data->categories as $category)
								{
									$icon = get_metadata('term', $category['id'], 'mec_cat_icon', true);
									$icon = isset($icon) && $icon != '' ? '<i class="'.$icon.' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';
									echo '<dl><dd class="mec-events-event-categories"><a href="'.get_term_link($category['id'], 'mec_category').'" class="mec-color-hover" rel="tag">'.$icon . $category['name'] .'</a></dd></dl>';
								}
								?>
							</div>
							<?php
						}
						?>
						<?php do_action('mec_single_event_under_category', $event); ?>
						<?php
						// Event Organizer
						if(isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) && !empty($event->data->organizers[$event->data->meta['mec_organizer_id']]) and $single->found_value('event_orgnizer', $settings) == 'on')
						{
							$organizer = $event->data->organizers[$event->data->meta['mec_organizer_id']];
							?>
							<div class="mec-single-event-organizer">
								<?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
									<img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
								<?php endif; ?>
								<h3 class="mec-events-single-section-title"><?php echo $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite')); ?></h3>
								<dl>
								<?php if(isset($organizer['thumbnail'])): ?>
									<dd class="mec-organizer">
										<i class="mec-sl-home"></i>
										<h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
									</dd>
								<?php endif;
								if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
									<dd class="mec-organizer-tel">
										<i class="mec-sl-phone"></i>
										<h6><?php _e('Phone', 'modern-events-calendar-lite'); ?></h6>
										<a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
									</dd>
								<?php endif;
								if(isset($organizer['email']) && !empty($organizer['email'])): ?>
									<dd class="mec-organizer-email">
										<i class="mec-sl-envelope"></i>
										<h6><?php _e('Email', 'modern-events-calendar-lite'); ?></h6>
										<a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
									</dd>
								<?php endif;
								if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
									<dd class="mec-organizer-url">
										<i class="mec-sl-sitemap"></i>
										<h6><?php _e('Website', 'modern-events-calendar-lite'); ?></h6>
										<span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://'.$organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
										<?php do_action('mec_single_default_organizer', $organizer); ?>
									</dd>
								<?php endif;
								$organizer_description_setting = isset( $settings['organizer_description'] ) ? $settings['organizer_description'] : ''; $organizer_terms = get_the_terms($event->data, 'mec_organizer');  if($organizer_description_setting == '1'): foreach($organizer_terms as $organizer_term) { if ($organizer_term->term_id == $organizer['id'] ) {  if(isset($organizer_term->description) && !empty($organizer_term->description)): ?>
									<dd class="mec-organizer-description"><p><?php echo $organizer_term->description;?></p></dd>
								<?php endif; } } endif; ?>
								</dl>
							</div>
							<?php
							$this->show_other_organizers($event); // Show Additional Organizers
						}
						?>

						<!-- Register Booking Button -->
						<?php if($this->main->can_show_booking_module($event) and $single->found_value('register_btn', $settings) == 'on'): ?>
							<?php $data_lity = $data_lity_class =  ''; if(isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ){ /* $data_lity = 'onclick="openBookingModal();"'; */  $data_lity_class = 'mec-booking-data-lity'; }  ?>
							<a class="mec-booking-button mec-bg-color <?php echo $data_lity_class; ?> <?php if(isset($settings['single_booking_style']) and $settings['single_booking_style'] != 'modal' ) echo 'simple-booking'; ?>" href="#mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>" <?php echo $data_lity; ?>><?php echo esc_html($this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite'))); ?></a>
						<?php elseif($single->found_value('register_btn', $settings) == 'on' and isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://'): ?>
							<a class="mec-booking-button mec-bg-color" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php if(isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) echo esc_html__(trim($event->data->meta['mec_more_info_title']), 'modern-events-calendar-lite'); else echo esc_html($this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')));
								?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<!-- Speakers Module -->
				<?php if($single->found_value('event_speakers', $settings) == 'on') echo $this->main->module('speakers.details', array('event'=>$event)); ?>

				<!-- Attendees List Module -->
				<?php if($single->found_value('attende_module', $settings) == 'on') echo $this->main->module('attendees-list.details', array('event'=>$event)); ?>

				<!-- Next Previous Module -->
				<?php if($single->found_value('next_module', $settings) == 'on') echo $this->main->module('next-event.details', array('event'=>$event)); ?>

				<!-- Links Module -->
				<?php if($single->found_value('links_module', $settings) == 'on') echo $this->main->module('links.details', array('event'=>$event)); ?>

				<!-- Weather Module -->
				<?php if($single->found_value('weather_module', $settings) == 'on') echo $this->main->module('weather.details', array('event'=>$event)); ?>

				<!-- Google Maps Module -->
				<?php if ($single->found_value('google_map', $settings) == 'on'): ?>
					<div class="mec-events-meta-group mec-events-meta-group-gmap">
						<?php echo $this->main->module('googlemap.details', array('event'=>$this->events)); ?>
					</div>
				<?php endif; ?>

				<!-- QRCode Module -->
				<?php if($single->found_value('qrcode_module', $settings) == 'on') echo $this->main->module('qrcode.details', array('event'=>$event)); ?>

                <!-- Custom Fields Module -->
                <?php if($single->found_value('custom_fields_module', $settings) == 'on') echo $this->display_data_fields($event, true); ?>

				<!-- Widgets -->
				<?php dynamic_sidebar('mec-single-sidebar'); ?>

			</div>
		<?php endif; ?>
	</article>

	<?php $this->display_related_posts_widget($event->ID); ?>
	<?php $this->display_next_previous_events($event); ?>

</div>
<?php
// MEC Schema
if($rank_math_options != 'event') do_action('mec_schema', $event);
?>
<script>
    // Fix modal speaker in some themes
    jQuery(".mec-speaker-avatar a").click(function(e)
    {
        e.preventDefault();
        var id =  jQuery(this).attr('href');
        lity(id);
    });

    // Fix modal booking in some themes
    jQuery(window).on('load', function()
    {
        jQuery( ".mec-booking-button.mec-booking-data-lity" ).click(function(e)
        {
            e.preventDefault();
            var book_id =  jQuery(this).attr('href');
            lity(book_id);
        });
    });
</script>