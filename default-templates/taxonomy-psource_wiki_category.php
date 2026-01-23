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

// Die globale Query wird verwendet, damit die Paginierung des Themes greift
global $wp_query;
?>
<div id="primary" class="wiki-primary-event">
    <div id="content">
        <div class="padder">
            <div id="wiki-page-wrapper">
                <h1 class="entry-title"><?php echo esc_html($term->name); ?></h1>
                <?php
                $desc = term_description();
                if ($desc) {
                    // Entferne explizit den Shortcode [ps_wiki_toc] aus der Beschreibung
                    $desc = preg_replace('/\[ps_wiki_toc.*?\]/i', '', $desc);
                    echo '<div class="wiki-category-description">' . do_shortcode($desc) . '</div>';
                }
                ?>
                <?php if (have_posts()) : ?>
                    <?php if ($display_mode === 'grid') : ?>
                        <div class="psource-wiki-grid">
                            <?php while (have_posts()) : the_post(); ?>
                                <div class="psource-wiki-grid-item">
                                    <a href="<?php the_permalink(); ?>">
                                        <div class="psource-wiki-archive-title"><?php the_title(); ?></div>
                                    </a>
                                    <div class="psource-wiki-archive-meta">
                                        <?php echo get_the_date(); ?> | <?php the_author(); ?>
                                    </div>
                                    <div class="psource-wiki-archive-excerpt">
                                        <?php
                                        $content = get_the_content();
                                        $content = preg_replace('/\[ps_wiki_toc.*?\]/i', '', $content);
                                        echo psource_wiki_get_excerpt($content, $excerpt_length, $excerpt_type);
                                        ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else : ?>
                        <div class="psource-wiki-archive-list">
                            <?php while (have_posts()) : the_post(); ?>
                                <article class="psource-wiki-archive-entry">
                                    <header>
                                        <h2 class="psource-wiki-archive-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h2>
                                        <div class="psource-wiki-archive-meta">
                                            <?php echo get_the_date(); ?> | <?php the_author(); ?>
                                        </div>
                                    </header>
                                    <div class="psource-wiki-archive-excerpt">
                                        <?php
                                        $content = get_the_content();
                                        $content = preg_replace('/\[ps_wiki_toc.*?\]/i', '', $content);
                                        echo esc_html(wp_trim_words(strip_tags($content), 30, '...'));
                                        ?>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                    <?php the_posts_pagination(); ?>
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
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 2em;
    padding: 1.5em 2em;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.psource-wiki-archive-title a:hover {
    text-decoration: underline;
}

.psource-wiki-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5em;
    margin: 1em 0 2em 0;
}

@media (min-width: 600px) {
    .psource-wiki-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (min-width: 900px) {
    .psource-wiki-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (min-width: 1200px) {
    .psource-wiki-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.psource-wiki-grid-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.2em 1.2em 1.5em 1.2em;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    text-align: left;
    transition: box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    width: 100%;
    min-width: 0;
}
.psource-wiki-grid-item:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    background: #f0f6ff;
}
.psource-wiki-grid-item a {
    text-decoration: none;
    margin-bottom: 0.2em;
}
.psource-wiki-grid-item .psource-wiki-archive-title {
    margin-bottom: 0.1em;
}
.psource-wiki-grid-item .psource-wiki-archive-meta {
    margin-bottom: 0.5em;
}
.psource-wiki-grid-item .psource-wiki-archive-excerpt {
    margin-top: 0.2em;
}
</style>


