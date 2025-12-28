<?php
/**
 * Autolink-Funktion für PS Wiki
 *
 * @param string $content
 * @return string
 */
function ps_wiki_autolink_titles($content) {
    if (!is_singular('psource_wiki')) return $content;
    static $wiki_titles = null;
    if ($wiki_titles === null) {
        $wiki_titles = array();
        $posts = get_posts(array(
            'post_type' => 'psource_wiki',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => array('ID', 'post_title')
        ));
        foreach ($posts as $post) {
            $wiki_titles[mb_strtolower($post->post_title)] = $post->ID;
        }
    }
    return preg_replace_callback('/\[\[([^\]|]+)(\|([^\]]+))?\]\]/', function($matches) use ($wiki_titles) {
        $title = trim($matches[1]);
        $label = isset($matches[3]) && $matches[3] !== '' ? $matches[3] : $title;
        $key = mb_strtolower($title);
        if (isset($wiki_titles[$key])) {
            $url = get_permalink($wiki_titles[$key]);
            return '<a href="' . esc_url($url) . '" class="ps-wiki-autolink">' . esc_html($label) . '</a>';
        } else {
            return '<span class="ps-wiki-autolink-missing">' . esc_html($label) . '</span>';
        }
    }, $content);
}

/**
 * Generiert ein Inhaltsverzeichnis (TOC) aus H2/H3-Überschriften
 * @param string $content
 * @return string TOC-HTML
 */
function ps_wiki_generate_toc($content) {
    $matches = array();
    preg_match_all('/<h([23])[^>]*>(.*?)<\/h[23]>/', $content, $matches, PREG_SET_ORDER);
    if (empty($matches)) return '';
    $toc = '<div class="ps-wiki-toc"><strong>' . __('Inhaltsverzeichnis', 'ps-wiki') . '</strong><ul>';
    $ids = array();
    foreach ($matches as $m) {
        $level = $m[1];
        $title = strip_tags($m[2]);
        $id = sanitize_title($title);
        // Eindeutige IDs erzwingen
        $orig_id = $id;
        $i = 2;
        while (in_array($id, $ids)) {
            $id = $orig_id . '-' . $i;
            $i++;
        }
        $ids[] = $id;
        // Anker im Content ersetzen
        $content = preg_replace('/' . preg_quote($m[0], '/') . '/', '<h' . $level . ' id="' . $id . '">' . $m[2] . '</h' . $level . '>', $content, 1);
        $toc .= '<li class="toc-level-' . $level . '"><a href="#' . $id . '">' . esc_html($title) . '</a></li>';
    }
    $toc .= '</ul></div>';
    return array('toc' => $toc, 'content' => $content);
}

/**
 * Shortcode für Inhaltsverzeichnis
 */
function ps_wiki_toc_shortcode($atts, $content = null) {
    global $post;
    if (!$post) return '';
    $data = ps_wiki_generate_toc($post->post_content); // KEIN apply_filters mehr!
    return $data['toc'];
}
add_shortcode('ps_wiki_toc', 'ps_wiki_toc_shortcode');
