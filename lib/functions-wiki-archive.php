<?php
// PS Wiki Archiv-Logik: Grid/List, Auszugslänge, TOC-Shortcode entfernen

// Inhaltsverzeichnis-Shortcode aus Auszügen und Archiv entfernen
add_filter('get_the_excerpt', function($excerpt) {
    return preg_replace('/\[ps_wiki_toc.*?\]/i', '', $excerpt);
});
add_filter('the_excerpt', function($excerpt) {
    return preg_replace('/\[ps_wiki_toc.*?\]/i', '', $excerpt);
});

// Shortcode auch aus the_content in Archivansichten entfernen
add_filter('the_content', function($content) {
    global $wp_query;
    if (is_archive() || is_tax('psource_wiki_category') || is_post_type_archive('psource_wiki') || (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'psource_wiki')) {
        return preg_replace('/\[ps_wiki_toc.*?\]/i', '', $content);
    }
    return $content;
}, 20);

// Shortcode auch aus get_the_content entfernen
add_filter('get_the_content', function($content) {
    if (is_archive() || is_tax('psource_wiki_category') || is_post_type_archive('psource_wiki')) {
        return preg_replace('/\[ps_wiki_toc.*?\]/i', '', $content);
    }
    return $content;
});

// Shortcode auch aus manuellem Excerpt entfernen
add_filter('get_post_metadata', function($value, $object_id, $meta_key, $single) {
    if ($meta_key === 'excerpt' || $meta_key === '_excerpt' || $meta_key === 'post_excerpt') {
        if (is_archive() || is_tax('psource_wiki_category') || is_post_type_archive('psource_wiki')) {
            if (is_array($value)) {
                return array_map(function($v) {
                    return preg_replace('/\[ps_wiki_toc.*?\]/i', '', $v);
                }, $value);
            } elseif (is_string($value)) {
                return preg_replace('/\[ps_wiki_toc.*?\]/i', '', $value);
            }
        }
    }
    return $value;
}, 10, 4);

// Output-Buffer-Filter für Archive/Kategorien
function psource_wiki_toc_buffer_start() {
    if (is_archive() || is_tax('psource_wiki_category') || is_post_type_archive('psource_wiki')) {
        ob_start('psource_wiki_toc_buffer_callback');
    }
}
add_action('template_redirect', 'psource_wiki_toc_buffer_start');
function psource_wiki_toc_buffer_callback($buffer) {
    return preg_replace('/\[ps_wiki_toc.*?\]/i', '', $buffer);
}

// Auszugslänge für Wiki-Archiv (aus Option)
add_filter('excerpt_length', function($length) {
    if (is_post_type_archive('psource_wiki') || is_tax('psource_wiki_category')) {
        $settings = get_option('wiki_settings');
        return isset($settings['excerpt_length']) ? intval($settings['excerpt_length']) : 30;
    }
    return $length;
}, 20);

// Grid/List-Ansicht: Body-Klasse setzen für CSS
add_filter('body_class', function($classes) {
    if (is_post_type_archive('psource_wiki') || is_tax('psource_wiki_category')) {
        $settings = get_option('wiki_settings');
        $mode = isset($settings['display_mode']) ? $settings['display_mode'] : 'list';
        $classes[] = 'wiki-view-' . $mode;
    }
    return $classes;
});
