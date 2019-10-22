<?php
/** no direct access **/
defined('MECEXEC') or die();

// The Query
$query = new WP_Query(array('post_type'=>$this->PT, 'author'=>get_current_user_id(), 'posts_per_page'=>'-1', 'post_status'=>array('pending', 'draft', 'future', 'publish')));

// Generating javascript code of countdown module
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery(".mec-fes-event-remove").on("click", function(event)
    {
        var id = jQuery(this).data("id");
        var confirmed = jQuery(this).data("confirmed");
        
        if(confirmed == "0")
        {
            jQuery(this).data("confirmed", "1");
            jQuery(this).addClass("mec-fes-waiting");
            jQuery(this).text("'.esc_attr__('Click again to remove!', 'modern-events-calendar-lite').'");
            
            return false;
        }

        // Add loading class to the row
        jQuery("#mec_fes_event_"+id).addClass("loading");
        
        jQuery.ajax(
        {
            type: "POST",
            url: "'.admin_url('admin-ajax.php', NULL).'",
            data: "action=mec_fes_remove&_wpnonce='.wp_create_nonce('mec_fes_remove').'&post_id="+id,
            dataType: "JSON",
            success: function(response)
            {
                if(response.success == "1")
                {
                    // Remove the row
                    jQuery("#mec_fes_event_"+id).remove();
                }
                else
                {
                    // Remove the loading class from the row
                    jQuery("#mec_fes_event_"+id).removeClass("loading");
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Remove the loading class from the row
                jQuery("#mec_fes_event_"+id).removeClass("loading");
            }
        });
    });
});
</script>';

// Include javascript code into the footer
$this->factory->params('footer', $javascript);
?>
<div class="mec-fes-list">
    <?php if($query->have_posts()): ?>
    <div class="mec-fes-list-top-actions">
        <a href="<?php echo $this->link_add_event(); ?>"><?php echo __('Add new', 'modern-events-calendar-lite'); ?></a>
    </div>
    <?php do_action('mec_fes_list'); ?>
    <ul>
        <?php 
            while($query->have_posts()): $query->the_post();
            // Show Post Status
            global $post;
            $status = $this->main->get_event_label_status(trim($post->post_status));
        ?>
        <li id="mec_fes_event_<?php echo get_the_ID(); ?>">
            <span class="mec-event-title"><a href="<?php echo $this->link_edit_event(get_the_ID()); ?>"><?php the_title(); ?></a></span>
            <?php 
            $event_status = get_post_status(get_the_ID()); 
            if(isset($event_status) and strtolower($event_status) == 'publish'):
            ?>
            <span class="mec-fes-event-export"><a href="#mec-fes-export-wrapper-<?php echo get_the_ID(); ?>" data-lity><div class="wn-p-t-right"><div class="wn-p-t-text-content"><?php echo esc_html__('Download Attendees', 'modern-events-calendar-lite'); ?></div><i></i></div></a></span>
            <?php endif; ?>
            <span class="mec-fes-event-view"><a href="<?php the_permalink(); ?>"><div class="wn-p-t-right"><div class="wn-p-t-text-content"><?php echo esc_html__('View Event', 'modern-events-calendar-lite'); ?></div><i></i></div></a></span>
            <?php if(current_user_can('delete_post', get_the_ID())): ?>
            <span class="mec-fes-event-remove" data-confirmed="0" data-id="<?php echo get_the_ID(); ?>"><div class="wn-p-t-right"><div class="wn-p-t-text-content"><?php echo esc_html__('Remove Event', 'modern-events-calendar-lite'); ?></div><i></i></div></span>
            <?php endif; ?>
            <span class="mec-fes-event-view mec-event-status <?php echo $status['status_class']; ?>"><?php echo $status['label']; ?></span>
            <div class="mec-fes-export-wrapper mec-modal-wrap lity-hide" id="mec-fes-export-wrapper-<?php echo get_the_ID(); ?>" data-event-id="<?php echo get_the_ID(); ?>">
                <div class="mec-fes-btn-date">                    
                    <?php
                    $mec_repeat_info = get_post_meta(get_the_ID(), 'mec_repeat', true);
                    $ids_text_field = '';

                    if(isset($mec_repeat_info['status']) and $mec_repeat_info['status']):
                    ?>
                    <ul class="mec-export-list-wrapper">
                    <?php
                    $past_month = strtotime('-1month', current_time('timestamp', 0));
                    
                    $render = $this->getRender();
                    $dates = $render->dates(get_the_ID(), NULL, 15, date('Y-m-d', $past_month));

                    // Attnedees
                    $current_user_id = is_user_logged_in() ? get_current_user_id() : false;
                    $current_user_books = $this->db->select("SELECT `ID` FROM `#__posts` WHERE `post_type`='mec-books' AND `post_author`={$current_user_id}", 'loadAssocList');
                    $all_books_ids_info = array();

                    if(is_array($current_user_books) and count($current_user_books)):
                        
                        foreach($current_user_books as $current_user_book):

                            $book_ids_info = array();
                            $book_ids_info['id'] = $current_user_book['ID'];
                            $book_ids_info['info'] = array();
                            $books_meta_info = $this->db->select("SELECT `post_id`, `meta_key`, `meta_value` FROM `#__postmeta` WHERE `post_id`={$current_user_book['ID']}", 'loadAssocList');
                            
                            if(is_array($books_meta_info) and count($books_meta_info))
                            {
                                $books_data = array();
                                foreach($books_meta_info as $book_meta_info)
                                {
                                    $books_data = array_merge($books_data, array(trim($book_meta_info['meta_key']) => $book_meta_info['meta_value']));
                                }
                                $book_ids_info['info'] = $books_data;
                                array_push($all_books_ids_info, $book_ids_info);

                                if(get_the_ID() == $book_ids_info['info']['mec_event_id']) $ids_text_field .= $book_ids_info['id'] . ',';
                            }
                        endforeach;
                    endif;
                    foreach($dates as $date):
                        
                        if(isset($date['start']['date']) and isset($date['end']['date'])):

                            $attendees_count = 0;
                            $certain_date_ids = "";
                            $render_date = trim($date['start']['date']).':'.trim($date['end']['date']);
                            foreach($all_books_ids_info as $book_info)
                            {
                                if((isset($book_info['info']['mec_date']) and trim($book_info['info']['mec_date']) == $render_date) and (isset($book_info['info']['mec_event_id']) and trim($book_info['info']['mec_event_id']) == get_the_ID()))
                                {
                                    if(isset($book_info['info']['mec_attendees']))
                                    {

                                        $attendees_info = unserialize($book_info['info']['mec_attendees']);
                                        $certain_date_ids .= $book_info['id'] . ',';
                                        foreach($attendees_info as $attendees_info)
                                        {
                                            $attendees_count += $attendees_info['count'];
                                        }
                                    }
                                }
                            }
                    ?>
                        <li class="mec-export-list-item" data-ids="<?php echo $certain_date_ids; ?>"><?php echo trim($date['start']['date']); ?> <span class="mec-export-badge"><?php echo $attendees_count; ?></span></li>
                    <?php  
                        endif;
                    endforeach; 
                    ?>
                    </ul>
                    <?php else: ?>
                    <div class="date-messgae">
                        <?php _e('Event is not repeating. No dates exist for this event!', 'modern-events-calendar-lite'); ?>
                    </div>
                    <?php endif; ?>
                    <input type="hidden" class="mec-certain-user-booking-ids" value="<?php echo $ids_text_field; ?>">
                </div>
                <?php
                $mec_repeat_info = get_post_meta(get_the_ID(), 'mec_repeat', true);
                ?>
                <div class="mec-fes-btn-export">
                    <span class="mec-event-export-csv">CSV</span>
                    <span class="mec-event-export-excel">MS EXCEL</span>
                </div>
            </div>
        </li>
        <?php endwhile; wp_reset_postdata(); // Restore original Post Data ?>
    </ul>
    <?php else: ?>
    <p><?php echo sprintf(__('No events found! %s', 'modern-events-calendar-lite'), '<a href="'.$this->link_add_event().'">'.__('Add new', 'modern-events-calendar-lite').'</a>'); ?></p>
    <?php endif; ?>
</div>