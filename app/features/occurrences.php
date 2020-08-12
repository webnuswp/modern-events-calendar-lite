<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Occurrences class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_occurrences extends MEC_base
{
    public $factory;
    public $main;
    public $settings;
    public $db;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();

        // Import MEC Main
        $this->main = $this->getMain();
        
        // MEC Settings
        $this->settings = $this->main->get_settings();

        // Import MEC DB
        $this->db = $this->getDB();
    }
    
    /**
     * Initialize locations feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Occurrences Status
        $occurrences_status = (isset($this->settings['per_occurrences_status']) and $this->settings['per_occurrences_status']);

        // Feature is not enabled
        if(!$occurrences_status) return;

        // Tab
        $this->factory->filter('mec-single-event-meta-title', array($this, 'tab'), 10, 3);

        // Metabox
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_occurrences'), 18);

        // Occurrences for FES
        if(!isset($this->settings['fes_section_occurrences']) or (isset($this->settings['fes_section_occurrences']) and $this->settings['fes_section_occurrences'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_occurrences'), 18);

        // AJAX
        $this->factory->action('wp_ajax_mec_occurrences_dropdown', array($this, 'dropdown'));
        $this->factory->action('wp_ajax_mec_occurrences_add', array($this, 'add'));
        $this->factory->action('wp_ajax_mec_occurrences_delete', array($this, 'delete'));

        // Save Data
        $this->factory->action('mec_save_event_data', array($this, 'save'), 10, 2);
    }

    public function tab($tabs, $activated, $post)
    {
        $draft = (isset($post->post_status) and $post->post_status != 'auto-draft') ? false : true;
        $repeat_status = get_post_meta($post->ID, 'mec_repeat_status', true);

        if($draft or !$repeat_status) return $tabs;

        $tabs[__('Occurrences', 'modern-events-calendar-lite')] = 'mec-occurrences';
        return $tabs;
    }

    /**
     * Show occurrences of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_occurrences($post)
    {
        $draft = (isset($post->post_status) and $post->post_status != 'auto-draft') ? false : true;
        $repeat_status = get_post_meta($post->ID, 'mec_repeat_status', true);

        if($draft or !$repeat_status) return;

        $limit = 100;
        $now = current_time('timestamp', 0);
        $_6months_ago = strtotime('-6 Months', $now);

        $occurrences = $this->get_dates($post->ID, $now, $limit);

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $datetime_format = $date_format.' '.$time_format;

        $all_occurrences = $this->get_all_occurrences($post->ID, strtotime('-1 Month'));
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-occurrences">
            <h4><?php _e('Occurrences', 'modern-events-calendar-lite'); ?></h4>
            <div class="mec-occurrences-wrapper">
                <div>
                    <select id="mec_occurrences_dropdown" title="<?php esc_attr_e('Occurrence', 'modern-events-calendar-lite'); ?>">
                        <option class="mec-load-occurrences" value="<?php echo $_6months_ago.':'.$_6months_ago; ?>"><?php esc_html_e('Previous Occurrences', 'modern-events-calendar-lite'); ?></option>
                        <?php $i = 1; foreach($occurrences as $occurrence): ?>
                        <option value="<?php echo $occurrence->tstart.':'.$occurrence->tend; ?>" <?php echo ($i === 1 ? 'selected="selected"' : ''); ?>><?php echo (date_i18n($datetime_format, $occurrence->tstart)); ?></option>
                        <?php $i++; endforeach; ?>
                        <?php if(count($occurrences) >= $limit and isset($occurrence)): ?>
                        <option class="mec-load-occurrences" value="<?php echo $occurrence->tstart.':'.$occurrence->tend; ?>"><?php esc_html_e('Next Occurrences', 'modern-events-calendar-lite'); ?></option>
                        <?php endif; ?>
                    </select>
                    <button id="mec_occurrences_add" type="button" class="button mec-button-new"><?php esc_attr_e('Add', 'modern-events-calendar-lite'); ?></button>
                </div>
                <ul class="mec-occurrences-list">
                    <?php foreach($all_occurrences as $all_occurrence) echo $this->get_occurrence_form($all_occurrence['id']); ?>
                </ul>
            </div>
        </div>
        <script>
        jQuery(document).ready(function()
        {
            mec_trigger_load_dates();
            mec_trigger_add_occurrence();
            mec_trigger_delete_occurrence();
        });

        function mec_trigger_load_dates()
        {
            jQuery('#mec_occurrences_dropdown').off('change').on('change', function()
            {
                var $dropdown = jQuery(this);
                var value = $dropdown.val();

                if(!$dropdown.find(jQuery('option[value="'+value+'"]')).hasClass('mec-load-occurrences')) return;

                var $button = jQuery('#mec_occurrences_add');

                // Disable the Form
                $dropdown.attr('disabled', 'disabled');
                $button.attr('disabled', 'disabled');

                jQuery.ajax(
                {
                    url: "<?php echo admin_url('admin-ajax.php', NULL); ?>",
                    type: "POST",
                    data: "action=mec_occurrences_dropdown&id=<?php echo $post->ID; ?>&_wpnonce=<?php echo wp_create_nonce('mec_occurrences_dropdown'); ?>&date="+value,
                    dataType: "json"
                })
                .done(function(response)
                {
                    if(response.success) $dropdown.html(response.html);

                    // New Trigger
                    mec_trigger_load_dates();

                    // Enable the Form
                    $dropdown.removeAttr('disabled');
                    $button.removeAttr('disabled');
                });
            });
        }

        function mec_trigger_add_occurrence()
        {
            jQuery('#mec_occurrences_add').off('click').on('click', function()
            {
                var $dropdown = jQuery('#mec_occurrences_dropdown');
                var $button = jQuery(this);
                var $list = jQuery('.mec-occurrences-list');

                var value = $dropdown.val();

                // Disable the Form
                $dropdown.attr('disabled', 'disabled');
                $button.attr('disabled', 'disabled');

                jQuery.ajax(
                {
                    url: "<?php echo admin_url('admin-ajax.php', NULL); ?>",
                    type: "POST",
                    data: "action=mec_occurrences_add&id=<?php echo $post->ID; ?>&_wpnonce=<?php echo wp_create_nonce('mec_occurrences_add'); ?>&date="+value,
                    dataType: "json"
                })
                .done(function(response)
                {
                    if(response.success)
                    {
                        // Prepend
                        $list.prepend(response.html);

                        mec_trigger_delete_occurrence();
                    }

                    // Enable the Form
                    $dropdown.removeAttr('disabled');
                    $button.removeAttr('disabled');
                });
            });
        }

        function mec_trigger_delete_occurrence()
        {
            jQuery('.mec-occurrences-delete-button').off('click').on('click', function()
            {
                var $button = jQuery(this);
                var id = $button.data('id');

                var $occurrence = jQuery('#mec_occurrences_'+id);

                // Loading Style
                $occurrence.addClass('mec-loading');

                jQuery.ajax(
                {
                    url: "<?php echo admin_url('admin-ajax.php', NULL); ?>",
                    type: "POST",
                    data: "action=mec_occurrences_delete&id="+id+"&_wpnonce=<?php echo wp_create_nonce('mec_occurrences_delete'); ?>",
                    dataType: "json"
                })
                .done(function(response)
                {
                    if(response.success)
                    {
                        // Remove the item
                        $occurrence.remove();
                    }
                    else
                    {
                        // Loading Style
                        $occurrence.removeClass('mec-loading');
                    }
                });
            });
        }
        </script>
        <?php
    }

    public function dropdown()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_occurrences_dropdown')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $date = isset($_POST['date']) ? $_POST['date'] : '';
        $id = isset($_POST['id']) ? $_POST['id'] : '';

        // Date is invalid!
        if(!trim($date) or !trim($id)) $this->main->response(array('success'=>0, 'code'=>'DATE_OR_ID_IS_INVALID'));

        $dates = explode(':', $date);

        $limit = 100;
        $now = $dates[0];
        $_6months_ago = strtotime('-6 Months', $now);

        $occurrences = $this->get_dates($id, $now, $limit);

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $datetime_format = $date_format.' '.$time_format;

        $success = 0;
        $html = '<option class="mec-load-occurrences" value="'.$_6months_ago.':'.$_6months_ago.'">'.__('Previous Occurrences', 'modern-events-calendar-lite').'</option>';

        $i = 1;
        foreach($occurrences as $occurrence)
        {
            $success  = 1;
            $html .= '<option value="'.$occurrence->tstart.':'.$occurrence->tend.'" '.($i === 1 ? 'selected="selected"' : '').'>'.(date_i18n($datetime_format, $occurrence->tstart)).'</option>';
            $i++;
        }

        if(count($occurrences) >= $limit and isset($occurrence)) $html .= '<option class="mec-load-occurrences" value="'.$occurrence->tstart.':'.$occurrence->tend.'">'.__('Next Occurrences', 'modern-events-calendar-lite').'</option>';

        $this->main->response(array('success'=>$success, 'html'=>$html));
    }

    public function delete()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_occurrences_delete')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $occurrence_id = isset($_POST['id']) ? $_POST['id'] : '';

        // Request is invalid!
        if(!trim($occurrence_id)) $this->main->response(array('success'=>0, 'code'=>'ID_IS_INVALID'));

        $this->db->q("DELETE FROM `#__mec_occurrences` WHERE `id`='".$this->db->escape($occurrence_id)."'");

        $this->main->response(array('success'=>1));
    }

    public function add()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_occurrences_add')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $date = isset($_POST['date']) ? $_POST['date'] : '';
        $id = isset($_POST['id']) ? $_POST['id'] : '';

        // Date is invalid!
        if(!trim($date) or !trim($id)) $this->main->response(array('success'=>0, 'code'=>'DATE_OR_ID_IS_INVALID'));

        $dates = explode(':', $date);

        // Add Occurrence
        $occurrence_id = $this->db->q("INSERT INTO `#__mec_occurrences` (`post_id`,`occurrence`,`params`) VALUES ('".$id."','".$dates[0]."','".json_encode(array())."')", 'insert');

        $success = 1;

        ob_start();
        $this->get_occurrence_form($occurrence_id);
        $html = ob_get_clean();

        $this->main->response(array('success'=>$success, 'html'=>$html));
    }

    public function get_occurrence_form($occurrence_id)
    {
        $params = $this->get($occurrence_id);
        $data = $this->get_data($occurrence_id);

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $datetime_format = $date_format.' '.$time_format;
        ?>
        <li id="mec_occurrences_<?php echo $occurrence_id; ?>">
            <h3><span class="mec-occurrences-delete-button" data-id="<?php echo $occurrence_id; ?>"><?php esc_html_e('Delete', 'modern-events-calendar-lite'); ?></span><?php echo date_i18n($datetime_format, $data['occurrence']); ?></h3>
            <input type="hidden" name="mec[occurrences][<?php echo $occurrence_id; ?>][id]" value="<?php esc_attr_e($occurrence_id); ?>">
            <div class="mec-form-row">
                <div class="mec-col-3"><label for="mec_occurrences_<?php echo $occurrence_id; ?>_bookings_limit"><?php esc_attr_e('Total Booking Limit', 'modern-events-calendar-lite'); ?></label></div>
                <div class="mec-col-9"><input id="mec_occurrences_<?php echo $occurrence_id; ?>_bookings_limit" name="mec[occurrences][<?php echo $occurrence_id; ?>][bookings_limit]" type="number" value="<?php echo (isset($params['bookings_limit']) ? esc_attr($params['bookings_limit']) : ''); ?>"></div>
            </div>
        </li>
        <?php
    }

    public function save($post_id, $data)
    {
        if(!isset($data['occurrences']) or (isset($data['occurrences']) and !is_array($data['occurrences']))) return;

        $occurrences = $data['occurrences'];
        foreach($occurrences as $occurrence)
        {
            $this->db->q("UPDATE `#__mec_occurrences` SET `params`='".json_encode($occurrence)."' WHERE `id`='".$this->db->escape($occurrence['id'])."'");
        }
    }

    public function get_dates($post_id, $start, $limit = 100)
    {
        return $this->db->select("SELECT `tstart`, `tend` FROM `#__mec_dates` WHERE `post_id`='".$this->db->escape($post_id)."' AND `tstart`>='".$this->db->escape($start)."' ORDER BY `tstart` ASC LIMIT ".$this->db->escape($limit));
    }

    public function get($occurrence_id)
    {
        $JSON = $this->db->select("SELECT `params` FROM `#__mec_occurrences` WHERE `id`='".$this->db->escape($occurrence_id)."'", 'loadResult');

        if(!trim($JSON)) return array();
        else
        {
            $params = json_decode($JSON, true);

            if(!is_array($params)) return array();
            else return $params;
        }
    }

    public function get_data($occurrence_id)
    {
        return $this->db->select("SELECT * FROM `#__mec_occurrences` WHERE `id`='".$this->db->escape($occurrence_id)."'", 'loadAssoc');
    }

    public function get_all_occurrences($post_id, $start = NULL)
    {
        return $this->db->select("SELECT * FROM `#__mec_occurrences` WHERE `post_id`='".$this->db->escape($post_id)."' ".($start ? "AND `occurrence`>='".$this->db->escape($start)."'" : '')." ORDER BY `occurrence` DESC LIMIT 200", 'loadAssocList');
    }

    public static function param($post_id, $timestamp, $key, $default = NULL)
    {
        $o = new MEC_feature_occurrences();
        $db = $o->getDB();

        $JSON = $db->select("SELECT `params` FROM `#__mec_occurrences` WHERE `post_id`='".$db->escape($post_id)."' AND `occurrence`='".$db->escape($timestamp)."' ORDER BY `id` DESC LIMIT 1", 'loadResult');

        if(!trim($JSON)) $params = array();
        else
        {
            $params = json_decode($JSON, true);

            if(!is_array($params)) $params = array();
        }

        if(isset($params[$key]) and trim($params[$key]) != '') return $params[$key];
        else return $default;
    }
}