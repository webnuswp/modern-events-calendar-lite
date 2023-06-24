<?php

namespace MEC\Events;

use MEC\Singleton;



class EventsQuery extends Singleton{

    public function parse_args($q_args){

        $default = array(
            'post_type' => 'mec-events',
            'fields' => '',
            'posts_per_page' => get_option('posts_per_page',12),
            'post_status' => array('publish','pending','draft','future','private'),
            'meta_query' => array(
                'relation' => 'AND',
            ),
            'post__in' => array(),
            'meta_query' => array(),
        );

        if(is_numeric($q_args) && $q_args > 0){

            $q_args = array(
                'event_id' => $q_args
            );
        }

        $q_args = wp_parse_args( $q_args, $default );

        return $q_args;
    }

    public function get_tax_query($q_args){

        $taxonomies = array(
            'label',
            'category',
            'location',
            'organizer',
            'speaker',
            'event_type',
            'event_type_2',
        );

        $tax_query = array(
            'relation'=>'AND'
        );

        foreach($q_args as $tax => $v){

            if(!empty($v) && in_array($tax,$taxonomies)){

                $taxonomy = 'mec_'.$tax;
                $tax_query[$taxonomy.'_term_ids'] = array(
                    'taxonomy'=> $taxonomy,
                    'field'=>'term_id',
                    'terms'=> !is_array($v) ? explode(',', trim($v, ', ')) : (array)$v,
                );
            }
        }

        $tax_query = apply_filters('mec_map_tax_query', $tax_query,$q_args);

        return $tax_query;
    }

    public function get_events($q_args){

        $tax_query = array_merge_recursive(
            isset($q_args['tax_query']) ? $q_args['tax_query'] : array(),
            $this->get_tax_query($q_args)
        );
        $q_args['tax_query'] = $tax_query;

        $q_args = $this->parse_args($q_args);

        //event_ids start
        if(array_key_exists('event_id',$q_args) && !empty($q_args['event_id'])){

            $q_args['ID'] = $q_args['event_id'];
        }

        if(array_key_exists('event_ids__in',$q_args) && is_array( $q_args['event_ids__in'] )){

            $post_in = isset( $q_args['post_in'] ) && is_array( $q_args['post_in'] ) ? $q_args['post_in'] : array();

            $q_args['post__in'] = array_merge(
                $post_in,
                $q_args['event_ids__in']
            );
        }

        if(array_key_exists('event_ids__not_in',$q_args) && is_array( $q_args['event_ids__not_in'] )){

            $post_not_in = isset( $q_args['post__not_in'] ) && is_array( $q_args['post__not_in'] ) ? $q_args['post__not_in'] : array();

            $q_args['post__not_in'] = array_merge(
                $post_not_in,
                $q_args['event_ids__not_in']
            );
        }

        //event_ids end

        return get_posts($q_args);
    }

    public function get_events_ids($q_args){

        $default = array(
            'posts_per_page' => -1,
            'fields' => 'ids',
        );

        $q_args = wp_parse_args($q_args,$default);

        return $this->get_events($q_args);
    }

    public function get_last_event($return = 'post'){

		$query_args = $this->parse_args(
			array(
				'posts_per_page' => 1,
				'order' => 'DESC',
				'orderby' => 'ID'
			)
		);
		$events = get_posts($query_args);

		if(isset($events[0]) && !empty($events[0])){
			switch($return){
				case 'event':
                    $event = new Event($events[0]);
					return $event->get_detail();

					break;
				case 'post':
				default:

					return (array)$events[0];

				break;
			}
		}

		return false;
	}
}