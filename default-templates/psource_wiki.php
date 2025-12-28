<?php
global $blog_id, $wp_query, $wiki, $post, $current_user;
get_header( 'wiki' );
?>

<div id="primary" class="wiki-primary-event">
    <div id="content">
        <div class="padder">
            <div id="wiki-page-wrapper">
                <?php
                // Kategorie-Archiv?
                if (is_tax('psource_wiki_category')) {
                    $term = get_queried_object();
                    echo '<h1 class="entry-title">' . esc_html($term->name) . '</h1>';

                    // Hole alle Wikis dieser Kategorie, sortiert nach menu_order
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
                    if ($wikis) {
                        echo '<ul class="psource-wiki-list">';
                        foreach ($wikis as $wiki_post) {
                            echo '<li><a href="' . get_permalink($wiki_post->ID) . '">' . esc_html($wiki_post->post_title) . '</a></li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>' . __('Keine Wikis in dieser Kategorie.', 'ps-wiki') . '</p>';
                    }
                } else {
                    // Einzelansicht wie gehabt
                ?>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php if ( !post_password_required() ) { 
                    $revision_id = isset($_REQUEST['revision']) ? absint($_REQUEST['revision']) : 0;
                    $left        = isset($_REQUEST['left']) ? absint($_REQUEST['left']) : 0;
                    $right       = isset($_REQUEST['right']) ? absint($_REQUEST['right']) : 0;
                    $action      = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
                ?>
                <?php if ($action !== 'view') : ?>
                    <div class="psource_wiki psource_wiki_single">
                        <div class="psource_wiki_tabs psource_wiki_tabs_top">
                            <?php echo $wiki->tabs(); ?>
                            <div class="psource_wiki_clear"></div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php
                    if ($action == 'discussion') {
                        include dirname(__FILE__) . '/wiki_comment.php';
                    } elseif ($action !== 'view') {
                        echo $wiki->decider(apply_filters('the_content', $post->post_content), $action, $revision_id, $left, $right, false);
                    } else {
                        echo apply_filters('the_content', $post->post_content);
                    }
                ?>
                <?php } ?>
                <?php } // Ende else Einzelansicht ?>
            </div>
        </div>
    </div>
</div>

<?php get_sidebar('wiki'); ?>
<?php get_footer('wiki'); ?>

<style type="text/css">
.single #primary {
	float: left;
	margin: 0 -26.4% 0 0;
}

.singular #content, .left-sidebar.singular #content {
	margin: 0 34% 0 7.6%;
    width: 58.4%;
}
.psource-wiki-list {
    margin: 1em 0 2em 0;
    padding: 0;
    list-style: disc inside;
}
.psource-wiki-list li {
    margin: 0.5em 0;
}
</style>