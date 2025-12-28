<?php
// Template für Wiki-Kategorie-Archive
get_header('wiki');
$term = get_queried_object();

// Plugin-Settings laden
if (function_exists('get_option')) {
    $wiki_settings = get_option('wiki_settings');
} else {
    $wiki_settings = array();
}
$display_mode = isset($wiki_settings['display_mode']) ? $wiki_settings['display_mode'] : 'list';
$excerpt_length = isset($wiki_settings['excerpt_length']) ? intval($wiki_settings['excerpt_length']) : 30;
$excerpt_type = isset($wiki_settings['excerpt_type']) ? $wiki_settings['excerpt_type'] : 'words';

function psource_wiki_get_excerpt($content, $length, $type = 'words') {
    $content = strip_tags($content);
    if ($type === 'chars') {
        if (mb_strlen($content) > $length) {
            return mb_substr($content, 0, $length) . '...';
        }
        return $content;
    } else {
        return esc_html(wp_trim_words($content, $length, '...'));
    }
}

$wikis = get_posts(array(
    'post_type' => 'psource_wiki',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'tax_query' => array(
        array(
            'taxonomy' => 'psource_wiki_category',
            'field' => 'term_id',
            'terms' => $term->term_id,
        ),
    ),
));
?>
<div id="primary" class="wiki-primary-event">
    <div id="content">
        <div class="padder">
            <div id="wiki-page-wrapper">
                <h1 class="entry-title"><?php echo esc_html($term->name); ?></h1>
                <?php if ($wikis) : ?>
                    <?php if ($display_mode === 'grid') : ?>
                        <div class="psource-wiki-grid">
                            <?php foreach ($wikis as $wiki_post) : ?>
                                <div class="psource-wiki-grid-item">
                                    <a href="<?php echo get_permalink($wiki_post->ID); ?>">
                                        <div class="psource-wiki-archive-title"><?php echo esc_html($wiki_post->post_title); ?></div>
                                    </a>
                                    <div class="psource-wiki-archive-meta">
                                        <?php echo get_the_date('', $wiki_post); ?> | <?php echo get_the_author_meta('display_name', $wiki_post->post_author); ?>
                                    </div>
                                    <div class="psource-wiki-archive-excerpt">
                                        <?php echo psource_wiki_get_excerpt($wiki_post->post_content, $excerpt_length, $excerpt_type); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="psource-wiki-archive-list">
                            <?php foreach ($wikis as $wiki_post) : ?>
                                <article class="psource-wiki-archive-entry">
                                    <header>
                                        <h2 class="psource-wiki-archive-title">
                                            <a href="<?php echo get_permalink($wiki_post->ID); ?>"><?php echo esc_html($wiki_post->post_title); ?></a>
                                        </h2>
                                        <div class="psource-wiki-archive-meta">
                                            <?php echo get_the_date('', $wiki_post); ?> | <?php echo get_the_author_meta('display_name', $wiki_post->post_author); ?>
                                        </div>
                                    </header>
                                    <div class="psource-wiki-archive-excerpt">
                                        <?php echo esc_html(wp_trim_words(strip_tags($wiki_post->post_content), 30, '...')); ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <p><?php _e('Keine Wikis in dieser Kategorie.', 'ps-wiki'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php get_sidebar('wiki'); ?>
<?php get_footer('wiki'); ?>
<style type="text/css">
.psource-wiki-archive-list {
    max-width: 700px;
    margin: 2em auto 2em auto;
    padding: 0 1em;
}
.psource-wiki-archive-entry {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 2em;
    padding: 1.5em 2em;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.psource-wiki-archive-title {
    margin: 0 0 0.2em 0;
    font-size: 1.5em;
    font-weight: 700;
}
.psource-wiki-archive-title a {
    color: #1a2a3a;
    text-decoration: none;
}
.psource-wiki-archive-title a:hover {
    text-decoration: underline;
}
.psource-wiki-archive-meta {
    color: #888;
    font-size: 0.95em;
    margin-bottom: 0.7em;
}
.psource-wiki-archive-excerpt {
    color: #222;
    font-size: 1.08em;
    margin-top: 0.5em;
}
.psource-wiki-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5em;
    margin: 1em 0 2em 0;
}
.psource-wiki-grid-item {
    background: #f8f8f8;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.2em 1.2em 1.5em 1.2em;
    min-width: 220px;
    max-width: 270px;
    flex: 1 1 220px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    text-align: left;
    transition: box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
}
.psource-wiki-grid-item:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    background: #f0f6ff;
}
.psource-wiki-grid-item a {
    text-decoration: none;
    color: #1a2a3a;
    font-weight: 600;
    font-size: 1.1em;
    margin-bottom: 0.2em;
}
.psource-wiki-grid-item .psource-wiki-archive-title {
    font-size: 1.15em;
    font-weight: 700;
    margin-bottom: 0.1em;
}
.psource-wiki-grid-item .psource-wiki-archive-meta {
    color: #888;
    font-size: 0.95em;
    margin-bottom: 0.5em;
}
.psource-wiki-grid-item .psource-wiki-archive-excerpt {
    color: #222;
    font-size: 1em;
    margin-top: 0.2em;
}
</style>


