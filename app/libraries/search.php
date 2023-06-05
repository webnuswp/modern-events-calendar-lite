<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC schedule class.
 * @author Webnus <info@webnus.net>
 */
class MEC_search extends MEC_base
{
    private $main;
    private $settings;

    public function __construct()
    {
        $this->main = $this->getMain();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }

    public function refine()
    {
        // Variables
        $sf = isset($_POST['sf']) ? $this->main->sanitize_deep_array($_POST['sf']) : array();
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $current_field = isset($_POST['last_field']) ? sanitize_text_field($_POST['last_field']) : '';
        $category_type = isset($_POST['category_type']) ? sanitize_text_field($_POST['category_type']) : 'dropdown';
        $location_type = isset($_POST['location_type']) ? sanitize_text_field($_POST['location_type']) : 'dropdown';
        $organizer_type = isset($_POST['organizer_type']) ? sanitize_text_field($_POST['organizer_type']) : 'dropdown';
        $speaker_type = isset($_POST['speaker_type']) ? sanitize_text_field($_POST['speaker_type']) : 'dropdown';
        $label_type = isset($_POST['label_type']) ? sanitize_text_field($_POST['label_type']) : 'dropdown';
        $event_ids = array();

        // Import
        self::import('app.libraries.walker');
        if(!function_exists('wp_terms_checklist')) include ABSPATH.'wp-admin/includes/template.php';

        // Taxonomy Query
        $tax_query = array(
            'relation'=>'AND'
        );

        // Add event label to filter
        if(isset($sf['label']) and trim($sf['label'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_label',
                'field'=>'term_id',
                'terms'=>explode(',', trim(sanitize_text_field($sf['label']), ', '))
            );
        }

        // Add event category to filter
        if(isset($sf['category']) and trim($sf['category'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_category',
                'field'=>'term_id',
                'terms'=>explode(',', trim(sanitize_text_field($sf['category']), ', '))
            );
        }

        // Add event location to filter
        if(isset($sf['location']) and trim($sf['location'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_location',
                'field'=>'term_id',
                'terms'=>explode(',', trim(sanitize_text_field($sf['location']), ', '))
            );
        }

        // Add event address to filter
        if(isset($sf['address']) and trim($sf['address'], ', ') != '')
        {
            $get_locations_id = $this->get_locations_id(sanitize_text_field($sf['address']));
            $tax_query[] = array(
                'taxonomy'=>'mec_location',
                'field'=>'term_id',
                'terms'=>$get_locations_id,
            );
        }

        // Add event organizer to filter
        if(isset($sf['organizer']) and trim($sf['organizer'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_organizer',
                'field'=>'term_id',
                'terms'=>explode(',', trim(sanitize_text_field($sf['organizer']), ', '))
            );
        }

        // Add event speaker to filter
        if(isset($sf['speaker']) and trim($sf['speaker'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_speaker',
                'field'=>'term_id',
                'terms'=>explode(',', trim(sanitize_text_field($sf['speaker']), ', '))
            );
        }

        // Event type
        if(isset($sf['event_type']) and trim($sf['event_type'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_event_type',
                'field'=>'term_id',
                'terms'=>explode(',', trim(sanitize_text_field($sf['event_type']), ', '))
            );
        }

        // Event Type 2
        if(isset($sf['event_type_2']) and trim($sf['event_type_2'], ', ') != '')
        {
            $tax_query[] = array(
                'taxonomy'=>'mec_event_type_2',
                'field'=>'term_id',
                'terms'=>explode(',', trim(sanitize_text_field($sf['event_type_2']), ', '))
            );
        }

        // Add event tags to filter
        if(apply_filters('mec_taxonomy_tag', '') !== 'post_tag' and isset($sf['tag']) and trim($sf['tag'], ', ') != '')
        {
            if(is_numeric($sf['tag']))
            {
                $tax_query[] = array(
                    'taxonomy'=>'mec_tag',
                    'field'=>'term_id',
                    'terms'=>explode(',', trim(sanitize_text_field($sf['tag']), ', '))
                );
            }
            else
            {
                $tax_query[] = array(
                    'taxonomy'=>'mec_tag',
                    'field'=>'name',
                    'terms'=>explode(',', trim(sanitize_text_field($sf['tag']), ', '))
                );
            }
        }

        $tax_query = apply_filters('mec_map_tax_query', $tax_query, $sf);

        // Meta Query
        $meta_query = array(
            'relation' => 'AND',
        );

        // Event Min Cost
        if(isset($sf['cost-min']) and trim($sf['cost-min']) != '')
        {
            $meta_query[] = array(
                'key'     => 'mec_cost',
                'value'   => sanitize_text_field($sf['cost-min']),
                'type'    => 'numeric',
                'compare' => '>=',
            );
        }

        // Event Max Cost
        if(isset($sf['cost-max']) and trim($sf['cost-max']) != '')
        {
            $meta_query[] = array(
                'key'     => 'mec_cost',
                'value'   => sanitize_text_field($sf['cost-max']),
                'type'    => 'numeric',
                'compare' => '<=',
            );
        }

        $meta_query = apply_filters('mec_map_meta_query', $meta_query, $sf);

        // Search Arguments
        $args = array(
            'post_type' => $this->main->get_main_post_type(),
            'post_status' => 'publish',
            'posts_per_page' => '-1',
            's' => ((isset($sf['s']) and trim($sf['s'])) ? sanitize_text_field($sf['s']) : NULL),
            'tax_query' => $tax_query,
            'meta_query' => $meta_query,
        );

        // Query
        $query = new WP_Query($args);

        // The Loop
        if($query->have_posts())
        {
            while($query->have_posts())
            {
                $query->the_post();
                $event_ids[] = get_the_ID();
            }
        }

        // Restore Original Post Data
        wp_reset_postdata();

        $categories = array();
        $locations = array();
        $organizers = array();
        $speakers = array();
        $tags = array();
        $labels = array();

        $upcoming_ids = $this->main->get_upcoming_event_ids(strtotime(current_time('Y-m-d')));
        foreach($event_ids as $event_id)
        {
            // Expired Event
            if(!in_array($event_id, $upcoming_ids)) continue;

            $e_categories = wp_get_post_terms($event_id, 'mec_category');
            if(!is_array($e_categories)) $e_categories = array();

            foreach($e_categories as $e_category) $categories[] = $e_category->term_id;

            $e_locations = wp_get_post_terms($event_id, 'mec_location');
            if(!is_array($e_locations)) $e_locations = array();

            foreach($e_locations as $e_location) $locations[] = $e_location->term_id;

            $e_organizers = wp_get_post_terms($event_id, 'mec_organizer');
            if(!is_array($e_organizers)) $e_organizers = array();

            foreach($e_organizers as $e_organizer) $organizers[] = $e_organizer->term_id;

            if((isset($this->settings['speakers_status']) and $this->settings['speakers_status']))
            {
                $e_speakers = wp_get_post_terms($event_id, 'mec_speaker');
                if(!is_array($e_speakers)) $e_speakers = array();

                foreach($e_speakers as $e_speaker) $speakers[] = $e_speaker->term_id;
            }

            $e_labels = wp_get_post_terms($event_id, 'mec_label');
            if(!is_array($e_labels)) $e_labels = array();

            foreach($e_labels as $e_label) $labels[] = $e_label->term_id;

            $e_tags = wp_get_post_terms($event_id, apply_filters('mec_taxonomy_tag', ''));
            if(!is_array($e_tags)) $e_tags = array();

            foreach($e_tags as $e_tag) $tags[] = $e_tag->term_id;
        }

        $categories = array_unique($categories);
        $locations = array_unique($locations);
        $organizers = array_unique($organizers);
        $speakers = array_unique($speakers);
        $tags = array_unique($tags);
        $labels = array_unique($labels);

        $categories_html = '';
        $locations_html = '';
        $organizers_html = '';
        $speakers_html = '';
        $tags_html = '';
        $labels_html = '';

        // Categories
        if($category_type == 'dropdown')
        {
            $label = $this->main->m('taxonomy_category', esc_html__('Category', 'modern-events-calendar-lite' ));

            $categories_html .= wp_dropdown_categories(array
            (
                'echo'=>false,
                'taxonomy'=>'mec_category',
                'name'=>' ',
                'include'=>($current_field === 'category' ? '' : array_unique($categories)),
                'id'=>'mec_sf_category_'.$id,
                'hierarchical'=>true,
                'show_option_none'=>$label,
                'option_none_value'=>'',
                'selected'=>(isset($sf['category']) ? sanitize_text_field($sf['category']) : ''),
                'orderby'=>'name',
                'order'=>'ASC',
                'show_count'=>0,
                'child_of'=>(!count($categories) ? -1 : 0),
            ));
        }
        elseif($category_type == 'checkboxes' and wp_count_terms(array('taxonomy' => 'mec_category')))
        {
            $selected = (isset($sf['category']) and trim($sf['category'], ', ')) ? explode(',', trim(sanitize_text_field($sf['category']), ', ')) : array();
            $categories_html .= wp_terms_checklist(0, array
            (
                'echo'=>false,
                'taxonomy'=>'mec_category',
                'selected_cats'=>$selected,
                'checked_ontop'=>false,
                'walker'=>(new MEC_walker(array(
                    'include'=>($current_field === 'category' ? '' : array_unique($categories)),
                    'id' => $id,
                ))),
                'descendants_and_self'=>(!count($categories) ? -1 : 0),
            ));
        }
        elseif($category_type == 'simple-checkboxes' and wp_count_terms(array('taxonomy' => 'mec_category')))
        {
            $selected = (isset($sf['category']) and trim($sf['category'], ', ')) ? explode(',', trim(sanitize_text_field($sf['category']), ', ')) : array();
            foreach($categories as $category_id)
            {
                $term = get_term($category_id);
                $categories_html .= '<li id="mec_category-'.esc_attr($term->term_id).'">
                    <label class="selectit"><input value="'.esc_attr($term->term_id).'" type="checkbox" name="tax_input[mec_category][]" id="in-mec_category-'.esc_attr($term->term_id).'" '.(in_array($term->term_id, $selected) ? 'checked' : '').'> '.esc_html($term->name).'</label>
                </li>';
            }
        }

        // Locations
        $label = $this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite' ));

        if($location_type == 'dropdown')
        {
            $locations_html .= wp_dropdown_categories(array
            (
                'echo'=>false,
                'taxonomy'=>'mec_location',
                'name'=>' ',
                'include'=>($current_field === 'location' ? '' : array_unique($locations)),
                'id'=>'mec_sf_location_'.$id,
                'hierarchical'=>true,
                'show_option_none'=>$label,
                'option_none_value'=>'',
                'selected'=>(isset($sf['location']) ? $sf['location'] : ''),
                'orderby'=>'name',
                'order'=>'ASC',
                'show_count'=>0,
                'child_of'=>(!count($locations) ? -1 : 0),
            ));
        }
        elseif($location_type == 'simple-checkboxes' and wp_count_terms(array('taxonomy' => 'mec_location')))
        {
            $selected = (isset($sf['location']) and trim($sf['location'], ', ')) ? explode(',', trim(sanitize_text_field($sf['location']), ', ')) : array();
            foreach($locations as $location_id)
            {
                $term = get_term($location_id);
                $locations_html .= '<li id="mec_location-'.esc_attr($term->term_id).'">
                    <label class="selectit"><input value="'.esc_attr($term->term_id).'" type="checkbox" name="tax_input[mec_location][]" id="in-mec_location-'.esc_attr($term->term_id).'" '.(in_array($term->term_id, $selected) ? 'checked' : '').'> '.esc_html($term->name).'</label>
                </li>';
            }
        }

        // Organizers
        $label = $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite' ));

        if($organizer_type == 'dropdown')
        {
            $organizers_html .= wp_dropdown_categories(array
            (
                'echo'=>false,
                'taxonomy'=>'mec_organizer',
                'name'=>' ',
                'include'=>($current_field === 'organizer' ? '' : array_unique($organizers)),
                'id'=>'mec_sf_organizer_'.$id,
                'hierarchical'=>true,
                'show_option_none'=>$label,
                'option_none_value'=>'',
                'selected'=>(isset($sf['organizer']) ? sanitize_text_field($sf['organizer']) : ''),
                'orderby'=>'name',
                'order'=>'ASC',
                'show_count'=>0,
                'child_of'=>(!count($organizers) ? -1 : 0),
            ));
        }
        elseif($organizer_type == 'simple-checkboxes' and wp_count_terms(array('taxonomy' => 'mec_organizer')))
        {
            $selected = (isset($sf['organizer']) and trim($sf['organizer'], ', ')) ? explode(',', trim(sanitize_text_field($sf['organizer']), ', ')) : array();
            foreach($organizers as $organizer_id)
            {
                $term = get_term($organizer_id);
                $organizers_html .= '<li id="mec_organizer-'.esc_attr($term->term_id).'">
                    <label class="selectit"><input value="'.esc_attr($term->term_id).'" type="checkbox" name="tax_input[mec_organizer][]" id="in-mec_organizer-'.esc_attr($term->term_id).'" '.(in_array($term->term_id, $selected) ? 'checked' : '').'> '.esc_html($term->name).'</label>
                </li>';
            }
        }

        // Speakers
        $label = $this->main->m('taxonomy_speaker', esc_html__('Speaker', 'modern-events-calendar-lite' ));

        if($speaker_type == 'dropdown')
        {
            $speakers_html .= wp_dropdown_categories(array
            (
                'echo'=>false,
                'taxonomy'=>'mec_speaker',
                'name'=>' ',
                'include'=>($current_field === 'speaker' ? '' : array_unique($speakers)),
                'id'=>'mec_sf_speaker_'.$id,
                'hierarchical'=>true,
                'show_option_none'=>$label,
                'option_none_value'=>'',
                'selected'=>(isset($sf['speaker']) ? sanitize_text_field($sf['speaker']) : ''),
                'orderby'=>'name',
                'order'=>'ASC',
                'show_count'=>0,
                'child_of'=>(!count($speakers) ? -1 : 0),
            ));
        }
        elseif($speaker_type == 'simple-checkboxes' and wp_count_terms(array('taxonomy' => 'mec_speaker')))
        {
            $selected = (isset($sf['speaker']) and trim($sf['speaker'], ', ')) ? explode(',', trim(sanitize_text_field($sf['speaker']), ', ')) : array();
            foreach($speakers as $speaker_id)
            {
                $term = get_term($speaker_id);
                $speakers_html .= '<li id="mec_speaker-'.esc_attr($term->term_id).'">
                    <label class="selectit"><input value="'.esc_attr($term->term_id).'" type="checkbox" name="tax_input[mec_speaker][]" id="in-mec_speaker-'.esc_attr($term->term_id).'" '.(in_array($term->term_id, $selected) ? 'checked' : '').'> '.esc_html($term->name).'</label>
                </li>';
            }
        }

        // Tags
        $label = $this->main->m('taxonomy_tag', esc_html__('Tag', 'modern-events-calendar-lite' ));

        $tags_html .= wp_dropdown_categories(array
        (
            'echo'=>false,
            'taxonomy'=>apply_filters('mec_taxonomy_tag', ''),
            'name'=>' ',
            'include'=>($current_field === 'tag' ? '' : array_unique($tags)),
            'id'=>'mec_sf_tag_'.$id,
            'hierarchical'=>true,
            'show_option_none'=>$label,
            'option_none_value'=>'',
            'selected'=>(isset($sf['tag']) ? sanitize_text_field($sf['tag']) : ''),
            'orderby'=>'name',
            'order'=>'ASC',
            'show_count'=>0,
            'child_of'=>(!count($tags) ? -1 : 0),
        ));

        // Labels
        $label = $this->main->m('taxonomy_label', esc_html__('Label', 'modern-events-calendar-lite' ));

        if($label_type == 'dropdown')
        {
            $labels_html .= wp_dropdown_categories(array
            (
                'echo'=>false,
                'taxonomy'=>'mec_label',
                'name'=>' ',
                'include'=>($current_field === 'label' ? '' : array_unique($labels)),
                'id'=>'mec_sf_label_'.$id,
                'hierarchical'=>true,
                'show_option_none'=>$label,
                'option_none_value'=>'',
                'selected'=>(isset($sf['label']) ? sanitize_text_field($sf['label']) : ''),
                'orderby'=>'name',
                'order'=>'ASC',
                'show_count'=>0,
                'child_of'=>(!count($labels) ? -1 : 0),
            ));
        }
        elseif($label_type == 'simple-checkboxes' and wp_count_terms(array('taxonomy' => 'mec_label')))
        {
            $selected = (isset($sf['label']) and trim($sf['label'], ', ')) ? explode(',', trim(sanitize_text_field($sf['label']), ', ')) : array();
            foreach($labels as $label_id)
            {
                $term = get_term($label_id);
                $labels_html .= '<li id="mec_label-'.esc_attr($term->term_id).'">
                    <label class="selectit"><input value="'.esc_attr($term->term_id).'" type="checkbox" name="tax_input[mec_label][]" id="in-mec_label-'.esc_attr($term->term_id).'" '.(in_array($term->term_id, $selected) ? 'checked' : '').'> '.esc_html($term->name).'</label>
                </li>';
            }
        }

        $output = array(
            'success' => 1,
            'categories' => (isset($sf['category']) and trim($sf['category'], ', ')) ? '' : $categories_html,
            'locations' => $current_field === 'location' ? '' : $locations_html,
            'organizers' => $current_field === 'organizer' ? '' : $organizers_html,
            'speakers' => $current_field === 'speaker' ? '' : $speakers_html,
            'tags' => $current_field === 'tag' ? '' : $tags_html,
            'labels' => $current_field === 'label' ? '' : $labels_html,
        );

        echo json_encode($output);
        exit;
    }

    public function get_locations_id($address = '')
    {
        if(!trim($address)) return array();

        $address = str_replace(' ', ',', $address);
        $locations = explode(',', $address);
        $query = "SELECT `term_id` FROM `#__termmeta` WHERE `meta_key` = 'address'";

        foreach($locations as $location) if(trim($location)) $query .= " AND `meta_value` LIKE '%" . trim($location) . "%'";

        $db = $this->getDB();
        $locations_id = $db->select($query, 'loadAssocList');
        return array_map(function($value)
        {
            return intval($value['term_id']);
        }, $locations_id);
    }
}