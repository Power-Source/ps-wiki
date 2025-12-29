<?php
// Grid/List Wrapper für Wiki-Archiv-Ausgabe
add_filter('the_content', function($content) {
    if (is_post_type_archive('psource_wiki') || is_tax('psource_wiki_category')) {
        $settings = get_option('wiki_settings');
        $mode = isset($settings['display_mode']) ? $settings['display_mode'] : 'list';
        if ($mode === 'grid') {
            return '<div class="psource-wiki-grid-item">' . $content . '</div>';
        } else {
            return '<div class="psource-wiki-list-item">' . $content . '</div>';
        }
    }
    return $content;
}, 30);

// Grid-Wrapper um die Beitragsliste
add_filter('loop_start', function($query) {
    if ($query->is_main_query() && (is_post_type_archive('psource_wiki') || is_tax('psource_wiki_category'))) {
        $settings = get_option('wiki_settings');
        $mode = isset($settings['display_mode']) ? $settings['display_mode'] : 'list';
        if ($mode === 'grid') {
            echo '<div class="psource-wiki-grid">';
        }
    }
});
add_filter('loop_end', function($query) {
    if ($query->is_main_query() && (is_post_type_archive('psource_wiki') || is_tax('psource_wiki_category'))) {
        $settings = get_option('wiki_settings');
        $mode = isset($settings['display_mode']) ? $settings['display_mode'] : 'list';
        if ($mode === 'grid') {
            echo '</div>';
        }
    }
});

// Grid-CSS automatisch laden, wenn Grid aktiv ist
add_action('wp_enqueue_scripts', function() {
    $settings = get_option('wiki_settings');
    $mode = isset($settings['display_mode']) ? $settings['display_mode'] : 'list';
    if ($mode === 'grid') {
        wp_enqueue_style('psource-wiki-archive-grid', plugins_url('../css/wiki-archive-grid.css', __FILE__));
    }
});
