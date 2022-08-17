<?php
// no direct access
defined('ABSPATH') or die();

/**
 * MEC Kses Class.
 *
 */
class MEC_kses extends MEC_base
{
    static $allowed_html_form = NULL;
    static $allowed_html_element = NULL;
    static $allowed_html_rich = NULL;
    static $allowed_html_embed = NULL;
    static $allowed_html_page = NULL;
    static $allowed_html_full = NULL;
    static $allowed_attrs = array(
        'data-*' => 1,
        'aria-*' => 1,
        'type' => 1,
        'value' => 1,
        'class' => 1,
        'id' => 1,
        'for' => 1,
        'style' => 1,
        'src' => 1,
        'alt' => 1,
        'title' => 1,
        'placeholder' => 1,
        'href' => 1,
        'rel' => 1,
        'target' => 1,
        'novalidate' => 1,
        'name' => 1,
        'tabindex' => 1,
        'action' => 1,
        'method' => 1,
        'width' => 1,
        'height' => 1,
        'selected' => 1,
        'checked' => 1,
        'readonly' => 1,
        'disabled' => 1,
        'required' => 1,
        'autocomplete' => 1,
        'min' => 1,
        'max' => 1,
        'step' => 1,
        'cols' => 1,
        'rows' => 1,
        'lang' => 1,
        'dir' => 1,
        'enctype' => 1,
        'multiple' => 1,
        'frameborder' => 1,
        'allow' => 1,
        'allowfullscreen' => 1,
        'label' => 1,
        'align' => 1,
        'accept-charset' => 1,
        'itemtype' => 1,
        'itemscope' => 1,
        'itemprop' => 1,
        'content' => 1,
        'onclick' => 1,
        'onsubmit' => 1,
        'onchange' => 1,
        'xmlns' => 1,
        'viewbox' => 1,
        'd' => 1,
        'transform' => 1,
        'fill' => 1,
        'enable-background' => 1,
        'version' => 1,
        'xml:space' => 1,
        'xmlns:xlink' => 1,
        'onkeydown' => 1,
    );

    public function __construct()
    {
    }

    public static function full($html)
    {
        if(is_null(self::$allowed_html_full))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_full = apply_filters('mec_kses_tags', $allowed, 'full');
        }

        if(defined('MEC_NO_JS_CSS_IN_HTML') && MEC_NO_JS_CSS_IN_HTML) return wp_kses($html, self::$allowed_html_full);
        else return $html;
    }

    public static function page($html)
    {
        if(is_null(self::$allowed_html_page))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_page = apply_filters('mec_kses_tags', $allowed, 'page');
        }

        return wp_kses($html, self::$allowed_html_page);
    }

    public static function form($html)
    {
        if(is_null(self::$allowed_html_form))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_form = apply_filters('mec_kses_tags', $allowed, 'form');
        }

        return wp_kses($html, self::$allowed_html_form);
    }

    public static function element($html)
    {
        if(is_null(self::$allowed_html_element))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_element = apply_filters('mec_kses_tags', $allowed, 'element');
        }

        return wp_kses($html, self::$allowed_html_element);
    }

    /**
     * Element + Embed
     * @param $html
     * @return string
     */
    public static function rich($html)
    {
        if(is_null(self::$allowed_html_rich))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_rich = apply_filters('mec_kses_tags', $allowed, 'rich');
        }

        return wp_kses($html, self::$allowed_html_rich);
    }

    /**
     * Only Embed
     * @param $html
     * @return string
     */
    public static function embed($html)
    {
        if(is_null(self::$allowed_html_embed))
        {
            self::$allowed_html_embed = apply_filters('mec_kses_tags', array(), 'embed');
        }

        return wp_kses($html, self::$allowed_html_embed);
    }

    public static function tags($tags, $context)
    {
        foreach(array(
            'svg',
            'path',
            'div',
            'span',
            'ul',
            'li',
            'a',
            'button',
            'dt',
            'dl',
        ) as $tag)
        {
            $tags[$tag] = self::$allowed_attrs;
        }

        if(in_array($context, array('form', 'page', 'full')))
        {
            $tags['form'] = self::$allowed_attrs;
            $tags['label'] = self::$allowed_attrs;
            $tags['input'] = self::$allowed_attrs;
            $tags['select'] = self::$allowed_attrs;
            $tags['option'] = self::$allowed_attrs;
            $tags['optgroup'] = self::$allowed_attrs;
            $tags['textarea'] = self::$allowed_attrs;
            $tags['button'] = self::$allowed_attrs;
            $tags['fieldset'] = self::$allowed_attrs;
            $tags['output'] = self::$allowed_attrs;
        }

        if(in_array($context, array('embed', 'rich', 'full')))
        {
            if(!isset($tags['iframe'])) $tags['iframe'] = self::$allowed_attrs;
            if(!isset($tags['canvas'])) $tags['canvas'] = self::$allowed_attrs;
        }

        if(in_array($context, array('full')))
        {
            if(!isset($tags['script'])) $tags['script'] = self::$allowed_attrs;
            if(!isset($tags['style'])) $tags['style'] = self::$allowed_attrs;
        }

        return $tags;
    }

    public static function styles($styles)
    {
        $styles[] = 'display';

        return $styles;
    }
}