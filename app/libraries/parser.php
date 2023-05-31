<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC parser class.
 * @author Webnus <info@webnus.net>
 */
class MEC_parser extends MEC_base
{
    public $main;
    public $render;
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // MEC main library
        $this->main = $this->getMain();
        
        // MEC render library
        $this->render = $this->getRender();
        
        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * A wrapper function for getting WP_Query object
     * @author Webnus <info@webnus.net>
     * @global object $wp_query
     * @return object
     */
    public function get_wp_query()
    {
        global $wp_query;
        return $wp_query;
    }
    
    /**
     * load MEC Rewrite Rules
     * @author Webnus <info@webnus.net>
     * @param WP_Rewrite $wp_rewrite
     */
    public function load_rewrites(WP_Rewrite $wp_rewrite)
    {
        // Don't add rewrite rules if archive page of MEC is disabled
        if(!$this->main->get_archive_status()) return;
        
        if(!$wp_rewrite instanceof WP_Rewrite)
        {
            global $wp_rewrite;
        }
        
        $rules = $this->get_rewrite_rules();
        $wp_rewrite->rules = $rules + $wp_rewrite->rules;
    }

    public function get_rewrite_rules()
    {
        // MEC main slug
        $slug = $this->main->get_main_slug();

        // MEC main post type name
        $PT = $this->main->get_main_post_type();

        return array(
            // '(?:'.$slug.')/(\d{4}-\d{2})/?$'=>'index.php?post_type='.$PT.'&MecDisplay=month&MecDate=$matches[1]',
            // '(?:'.$slug.')/(?:yearly)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=year',
            // '(?:'.$slug.')/(?:monthly)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=month',
            // '(?:'.$slug.')/(?:weekly)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=week',
            // '(?:'.$slug.')/(?:daily)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=day',
            // '(?:'.$slug.')/(?:timetable)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=timetable',
            // '(?:'.$slug.')/(?:map)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=map',
            // '(?:'.$slug.')/(?:list)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=list',
            // '(?:'.$slug.')/(?:grid)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=grid',
            // '(?:'.$slug.')/(?:agenda)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=agenda',
            // '(?:'.$slug.')/(?:masonry)/?$'=>'index.php?post_type='.$PT.'&MecDisplay=masonry',
            '(?:'.$slug.')/?$'=>'index.php?post_type='.$PT.'&MecDisplay=default',
            '(?:'.$slug.')/(feed|rdf|rss|rss2|atom)/?$'=>'index.php?post_type='.$PT.'&feed=$matches[1]',
        );
    }
    
    /**
     * Adds MEC query vars to the WordPress
     * @author Webnus <info@webnus.net>
     * @param array $qvars
     * @return array
     */
    public function add_query_vars($qvars)
    {
        $qvars[] = 'MecDisplay';
        $qvars[] = 'MecMethod';
        $qvars[] = 'MecDate';

        return $qvars;
    }
    
    /**
     * @author Webnus <info@webnus.net>
     * @param object $query
     */
    public function WPQ_parse($query)
    {
        // MEC Archive Page
        if($query->get('MecDisplay') != '')
        {
            $query->MEC_archive = true;
            $query->MEC_single = false;
            
            $query->set('posts_per_page', 1);
        }
    }
    
    /**
     * @author Webnus <info@webnus.net>
     * @param string $template
     * @return string
     */
    public function template($template)
    {
        // We're in an embed post
        if(is_embed()) return $template;

        $PT = $this->main->get_main_post_type();
        if(is_single() and get_post_type() == $PT)
        {
            // Block Themes
            if(function_exists('wp_is_block_theme') and wp_is_block_theme())
            {
                add_filter('the_content', array($this, 'block_theme_single_content'));

                return $template;
            }

            $template = locate_template('single-'.$PT.'.php');
            if($template == '') $template = MEC_ABSPATH.'templates'.DS.'single-mec-events.php';
        }
        elseif(is_post_type_archive($PT) && !is_search())
        {
            $template = locate_template('archive-'.$PT.'.php');
            if($template == '') $template = MEC_ABSPATH.'templates'.DS.'archive-mec-events.php';

            add_action('mec_before_main_content', function()
            {
                // MEC factory library
                $factory = $this->getFactory();

                $factory->filter('the_content', array($this, 'archive_content'));
                $factory->filter('mec_archive_title', array($this, 'archive_title'));
                $factory->filter('post_thumbnail_html', array($this, 'archive_thumbnail'));
            });
		}
        elseif(is_tax('mec_category'))
        {
            $template = locate_template('taxonomy-mec-category.php');
            if($template == '') $template = MEC_ABSPATH.'templates'.DS.'taxonomy-mec-category.php';
        }
        
        return $template;
    }
    
    /**
     * @author Webnus <info@webnus.net>
     * @param string $content
     * @return string|boolean
     */
    public function archive_content($content)
    {
        // only run it once
        remove_filter('the_content', array($this, 'archive_content'));
        
        // WP_Query
        $wp_query = $this->get_wp_query();

        if($wp_query->get('MecDisplay') == 'list') return $this->render->vlist();
        elseif($wp_query->get('MecDisplay') == 'grid') return $this->render->vgrid();
        elseif($wp_query->get('MecDisplay') == 'agenda') return $this->render->vagenda();
        elseif($wp_query->get('MecDisplay') == 'month') return $this->render->vmonth();
        elseif($wp_query->get('MecDisplay') == 'year') return $this->render->vyear();
        elseif($wp_query->get('MecDisplay') == 'week') return $this->render->vweek();
        elseif($wp_query->get('MecDisplay') == 'day') return $this->render->vday();
        elseif($wp_query->get('MecDisplay') == 'timetable') return $this->render->vtimetable();
        elseif($wp_query->get('MecDisplay') == 'masonry') return $this->render->vmasonry();
        elseif($wp_query->get('MecDisplay') == 'map') return $this->render->vmap();
        elseif($wp_query->get('MecDisplay') == 'default') return $this->render->vdefault();

        return false;
    }
    
    /**
     * @author Webnus <info@webnus.net>
     * @param string $title
     * @return string
     */
    public function archive_title($title)
    {
        // only run it once
        remove_filter('mec_archive_title', array($this, 'archive_title'));

        return $this->main->get_archive_title(false);
    }
    
    /**
     * @author Webnus <info@webnus.net>
     * @param string $html
     * @return string
     */
    public function archive_thumbnail($html)
    {
        // only run it once
        remove_filter('post_thumbnail_html', array($this, 'archive_thumbnail'));
        
        return $this->main->get_archive_thumbnail();
    }
    
    /**
     * @author Webnus <info@webnus.net>
     * @param string $content
     * @return string
     */
    public function single_content($content)
    {
        // MEC Single Page
        if(!is_singular($this->main->get_main_post_type())) return $content;
        
        $event_id = get_the_ID();
        return $this->render->vsingle(array('id'=>$event_id, 'content'=>$content));
    }

    public function archive_document_title($title)
    {
        if(is_post_type_archive($this->main->get_main_post_type()) && !is_search())
        {
            return $this->main->get_archive_title();
        }

        return $title;
    }

    public function block_theme_single_content($content)
    {
        remove_filter('the_content', array($this, 'block_theme_single_content'));
        return $this->single_content($content);
    }
}