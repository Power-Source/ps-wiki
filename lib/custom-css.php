<?php
// Bindet benutzerdefiniertes Wiki-CSS im Frontend ein
add_action('wp_head', function() {
    if (is_admin()) return;
    if (get_query_var('post_type') === 'psource_wiki' || is_singular('psource_wiki') || is_tax('psource_wiki_category')) {
        $settings = get_option('wiki_settings');
        if (!empty($settings['custom_css'])) {
            echo '<style id="psource-wiki-custom-css">' . $settings['custom_css'] . '</style>';
        }
    }
});
