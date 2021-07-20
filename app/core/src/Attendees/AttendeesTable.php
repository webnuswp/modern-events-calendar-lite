<?php

namespace MEC\Attendees;

class AttendeesTable{

    public static $tbl = 'mec_attendees';

    public static function create_table(){

        if ( !function_exists( 'dbDelta' ) ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

        $tbl = self::$tbl;
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}{$tbl}` (
            `attendee_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) NOT NULL,
            `event_id` bigint(20) NOT NULL,
            `occurrence` int(11) NOT NULL,
            `name` varchar(50) NOT NULL,
            `email` varchar(50) NOT NULL,
            `data` text NULL,
            `count` int(11) DEFAULT 1,
            PRIMARY KEY (`attendee_id`)
        ){$charset}";

        dbDelta( $sql );

    }
}