<?php
/** no direct access **/

use MEC\FES\FormBuilder;

defined('MECEXEC') or die();

FormBuilder::enqueue();

?>
<div class="mec-fes-form">

    <?php
        FormBuilder::actions(
            $post,
            array(
                'url' => $this->link_list_events(),
            )
        );
    ?>


    <div class="mec-util-hidden" id="mec_fes_form_message"></div>
    <form id="mec_fes_form" enctype="multipart/form-data">
        <?php

        ?>

        <div class="mec-fes-form-cntt">

            <?php
                FormBuilder::title( $post );
                FormBuilder::editor( $post );

                if(isset($this->settings['fes_section_excerpt']) && $this->settings['fes_section_excerpt']){

                    FormBuilder::excerpt(
                        $post,
                        array(
                            'required' => (isset($this->settings['fes_required_excerpt']) and $this->settings['fes_required_excerpt']) ? true : false,
                        )
                    );
                }

                FormBuilder::info( $post );

                FormBuilder::datetime(
                    $post,
                    array(
                        'time_format' => isset($this->settings['time_format']) ? $this->settings['time_format'] : 12,
                        'datepicker_format' => (isset($this->settings['datepicker_format']) and trim($this->settings['datepicker_format'])) ? $this->settings['datepicker_format'] : 'Y-m-d',
                    )
                );

                if(isset($this->settings['tz_per_event']) and $this->settings['tz_per_event']){

                    FormBuilder::timezone( $post, array() );
                }

                if(isset($this->settings['countdown_status']) and $this->settings['countdown_status'] and (!isset($this->settings['fes_section_countdown_method']) or (isset($this->settings['fes_section_countdown_method']) and $this->settings['fes_section_countdown_method']))){

                    FormBuilder::countdown_status( $post, array() );
                }

                if(!isset($this->settings['fes_section_shortcode_visibility']) or (isset($this->settings['fes_section_shortcode_visibility']) and $this->settings['fes_section_shortcode_visibility'])){

                    FormBuilder::visibility( $post, array() );
                }

                FormBuilder::other_fields( $post, array() );

                FormBuilder::note( $post, array() );
            ?>
        </div>

        <div class="mec-fes-form-sdbr">
            <?php

                if(isset($this->settings['fes_guest_name_email']) and $this->settings['fes_guest_name_email']){

                    FormBuilder::guest( $post, array() );
                }

                if(!isset($this->settings['fes_section_event_links']) or (isset($this->settings['fes_section_event_links']) and $this->settings['fes_section_event_links'])){

                    FormBuilder::event_links(
                        $post,
                        array(
                            'event_link_required' => (isset($this->settings['fes_required_event_link']) and $this->settings['fes_required_event_link']),
                            'more_info_required' => (isset($this->settings['fes_required_more_info_link']) and $this->settings['fes_required_more_info_link']),
                        )
                    );
                }

                if(!isset($this->settings['fes_section_cost']) or (isset($this->settings['fes_section_cost']) and $this->settings['fes_section_cost'])){

                    FormBuilder::cost(
                        $post,
                        array(
                            'required' => ((isset($this->settings['fes_required_cost']) and $this->settings['fes_required_cost'])),
                        )
                    );
                }

                if(!isset($this->settings['fes_section_featured_image']) or (isset($this->settings['fes_section_featured_image']) and $this->settings['fes_section_featured_image'])){

                    FormBuilder::thumbnail(
                        $post,
                        array(
                            'required' => (isset($this->settings['fes_required_featured_image']) and $this->settings['fes_required_featured_image']),
                            'featured_image_caption' => (isset($this->settings['featured_image_caption']) and $this->settings['featured_image_caption']),
                        )
                    );
                }

                if(!isset($this->settings['fes_section_categories']) or (isset($this->settings['fes_section_categories']) and $this->settings['fes_section_categories'])){

                    FormBuilder::categories(
                        $post,
                        array(
                            'required' => (isset($this->settings['fes_required_category']) and $this->settings['fes_required_category']),
                        )
                    );
                }

                if(!isset($this->settings['fes_section_labels']) or (isset($this->settings['fes_section_labels']) and $this->settings['fes_section_labels'])){

                    FormBuilder::labels(
                        $post,
                        array(
                            'required' => (isset($this->settings['fes_required_label']) and $this->settings['fes_required_label']),
                        )
                    );
                }

                if(!isset($this->settings['fes_section_event_color']) or (isset($this->settings['fes_section_event_color']) and $this->settings['fes_section_event_color'])){

                    FormBuilder::color( $post, array() );
                }

                if(!isset($this->settings['fes_section_tags']) or (isset($this->settings['fes_section_tags']) and $this->settings['fes_section_tags'])){

                    FormBuilder::tags( $post, array() );
                }

                if((isset($this->settings['speakers_status']) and $this->settings['speakers_status']) and isset($this->settings['fes_section_speaker']) and $this->settings['fes_section_speaker']){

                    FormBuilder::speakers( $post, array() );
                }

                if( $this->getPRO() && isset($this->settings['sponsors_status']) and $this->settings['sponsors_status'] and isset($this->settings['fes_section_sponsor']) and $this->settings['fes_section_sponsor'] ){

                    FormBuilder::sponsors( $post, array() );
                }


                if( isset($this->settings['fes_section_virtual_events']) && $this->settings['fes_section_virtual_events'] ){

                    FormBuilder::virtual( $post, array() );
                }

                if( isset($this->settings['fes_section_zoom_integration']) && $this->settings['fes_section_zoom_integration'] ){

                    FormBuilder::zoom( $post, array() );
                }

            ?>

        </div>
        <div class="mec-form-row mec-fes-submit-wide">

            <?php
                if(isset($this->settings['fes_agreement']) and $this->settings['fes_agreement']){

                    FormBuilder::agreement(
                        $post,
                        array(
                            'agreement_page' => isset($this->settings['fes_agreement_page']) and $this->settings['fes_agreement_page'] ? $this->settings['fes_agreement_page'] : false,
                            'checked' => isset($this->settings['fes_agreement_checked']) and $this->settings['fes_agreement_checked'] ? true : false,
                        )
                    );
                }

                FormBuilder::recaptcha( $post, array() );

                FormBuilder::submit_button( $post, array() );
            ?>
        </div>
    </form>
</div>

<?php do_action('mec_fes_form_footer', $post); ?>