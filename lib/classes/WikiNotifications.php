<?php
/**
 * Klasse für Wiki-Benachrichtigungen und Abonnements
 */
class WikiNotifications {
    private $wiki;

    public function __construct($wiki_instance) {
        $this->wiki = $wiki_instance;
    }

    public function is_subscribed() {
        global $wpdb, $current_user, $post, $blog_id;
        if (is_user_logged_in()) {
            return $wpdb->get_var("SELECT COUNT(ID) FROM {$this->wiki->db_prefix}wiki_subscriptions WHERE blog_id = {$blog_id} AND wiki_id = {$post->ID} AND user_id = {$current_user->ID}");
        }
        if (isset($_COOKIE['psource_wiki_email'])) {
            return (bool) $wpdb->get_var("SELECT COUNT(ID) FROM {$this->wiki->db_prefix}wiki_subscriptions WHERE blog_id = {$blog_id} AND wiki_id = {$post->ID} AND email = '{$_COOKIE['psource_wiki_email']}'");
        }
        return false;
    }

    public function notifications_meta_box($post, $echo = true) {
        $settings = get_option('psource_wiki_settings');
        $email_notify = get_post_meta($post->ID, 'psource_wiki_email_notification', true);
        if (false === $email_notify) {
            $email_notify = 'enabled';
        }
        $content = '';
        $content .= '<input type="hidden" name="psource_wiki_notifications_meta" value="1" />';
        $content .= '<div class="alignleft">';
        $content .= '<label><input type="checkbox" name="psource_wiki_email_notification" value="enabled" ' . checked('enabled', $email_notify, false) .' /> '.__('Aktiviere E-Mail-Benachrichtigungen', 'ps-wiki').'</label>';
        $content .= '</div>';
        $content .= '<div class="clear"></div>';
        if ($echo) {
            echo $content;
        }
        return $content;
    }

    public function send_notifications($post_id) {
        global $wpdb;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!$post = get_post($post_id, ARRAY_A)) return;
        if ($post['post_type'] != 'psource_wiki' || !post_type_supports($post['post_type'], 'revisions')) return;
        $revisions = wp_get_post_revisions($post_id, array('order' => 'ASC'));
        $revision = array_pop($revisions);
        $post = get_post($post_id);
        $cancel_url = get_option('siteurl') . '?action=cancel-wiki-subscription&sid=';
        $admin_email = get_option('admin_email');
        $post_title = strip_tags($post->post_title);
        $post_content = strip_tags($post->post_content);
        $post_url = get_permalink($post_id);
        $post_excerpt = $post_content;
        if (strlen($post_excerpt) > 255) {
            $post_excerpt = substr($post_excerpt,0,252) . 'Weiterlesen...';
        }
        if ($revision) {
            $revert_url = wp_nonce_url(add_query_arg(array('revision' => $revision->ID), admin_url('revision.php')), "restore-post_$post->ID|$revision->ID" );
        } else {
            $revert_url = "";
        }
        $blog_name = get_option('blogname');
        $wiki_notification_content = array();
        $wiki_notification_content['user'] = sprintf(__("Sehr geehrter Abonnent,\n\n%s wurde geändert\n\nDu kannst die Wiki-Seite hier vollständig lesen: %s\n\n%s\n\nDanke,\nBLOGNAME\n\nAbonnement kündigen: CANCEL_URL", 'POST TITLE', 'ps-wiki'), 'POST_URL', 'EXCERPT', 'BLOGNAME');
        if ($revision) {
            $wiki_notification_content['author'] = sprintf(__("Lieber Autor,\n\n%s wurde verändert\n\nDu kannst die Wiki-Seite hier vollständig lesen: %s\n\nDu kannst die Änderungen rückgängig machen: %s\n\n%s\n\nDanke,\n%s\n\nAbonnement kündigen: %s", 'ps-wiki'), 'POST_TITLE', 'POST_URL', 'REVERT_URL', 'EXCERPT', 'BLOGNAME', 'CANCEL_URL');
        } else {
            $wiki_notification_content['author'] = sprintf(__("Lieber Autor,\n\n%s wurde verändert\n\nDu kannst die Wiki-Seite hier vollständig lesen: %s\n\n%s\n\nDanke,\n\n%s\n\nAbonnement kündigen: %s", 'ps-wiki'), 'POST_TITLE', 'POST_URL', 'EXCERPT', 'BLOGNAME', 'CANCEL_URL');
        }
        foreach ($wiki_notification_content as $key => $content) {
            $wiki_notification_content[$key] = str_replace("BLOGNAME",$blog_name,$wiki_notification_content[$key]);
            $wiki_notification_content[$key] = str_replace("POST_TITLE",$post_title,$wiki_notification_content[$key]);
            $wiki_notification_content[$key] = str_replace("EXCERPT",$post_excerpt,$wiki_notification_content[$key]);
            $wiki_notification_content[$key] = str_replace("POST_URL",$post_url,$wiki_notification_content[$key]);
            $wiki_notification_content[$key] = str_replace("REVERT_URL",$revert_url,$wiki_notification_content[$key]);
            $wiki_notification_content[$key] = str_replace("\'","'",$wiki_notification_content[$key]);
        }
        global $blog_id;
        $query = "SELECT * FROM " . $this->wiki->db_prefix . "wiki_subscriptions WHERE blog_id = {$blog_id} AND wiki_id = {$post->ID}";
        $subscription_emails = $wpdb->get_results($query, ARRAY_A);
        if (count($subscription_emails) > 0){
            foreach ($subscription_emails as $subscription_email){
                $loop_notification_content = $wiki_notification_content['user'];
                if ($subscription_email['user_id'] > 0) {
                    if ($subscription_email['user_id'] == $post->post_author) {
                        $loop_notification_content = $wiki_notification_content['author'];
                    }
                    $user = get_userdata($subscription_email['user_id']);
                    $subscription_to = $user->user_email;
                } else {
                    $subscription_to = $subscription_email['email'];
                }
                $loop_notification_content = str_replace("CANCEL_URL",$cancel_url . $subscription_email['ID'],$loop_notification_content);
                $subject_content = $blog_name . ': ' . __('Änderungen an der Wiki-Seite', 'ps-wiki');
                $from_email = $admin_email;
                $message_headers = "MIME-Version: 1.0\n" . "From: " . $blog_name . " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
                wp_mail($subscription_to, $subject_content, $loop_notification_content, $message_headers);
            }
        }
    }
}
