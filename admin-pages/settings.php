<?php

defined('ABSPATH') or die("Zugriff verweigert.");

class Wiki_Admin_Page_Settings {
	function __construct() {
		$this->maybe_save_settings();
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}

	/**
	 * Fügt die Admin-Menüs hinzu
	 *
	 * @see		http://codex.wordpress.org/Adding_Administration_Menus
	 */
	function admin_menu() {
		$page = add_submenu_page('edit.php?post_type=psource_wiki', __('Wiki-Einstellungen', 'ps-wiki'), __('Wiki-Einstellungen', 'ps-wiki'), 'manage_options', 'psource_wiki', array(&$this, 'display_settings'));
	}

	function display_settings() {
		global $wiki;

		if ( ! current_user_can('manage_options') )
			wp_die(__('Du hast keine Berechtigung, auf diese Seite zuzugreifen', 'ps-wiki'));	//Bei ordnungsgemäßem Zugriff wird diese Meldung nicht angezeigt.

		if ( isset($_GET['psource_wiki_settings_saved']) && $_GET['psource_wiki_settings_saved'] == 1 )
			echo '<div class="updated fade"><p>'.__('Einstellungen gespeichert.', 'ps-wiki').'</p></div>';
		?>

		<div class="wrap">
			<h2><?php _e('Wiki-Einstellungen', 'ps-wiki'); ?></h2>
			<form method="post" action="edit.php?post_type=psource_wiki&amp;page=psource_wiki">

			<?php wp_nonce_field('wiki_save_settings', 'wiki_settings_nonce'); ?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="psource_wiki-toc_auto"><?php _e('Inhaltsverzeichnis automatisch einfügen', 'ps-wiki'); ?></label></th>
					<td>
						<input type="checkbox" id="psource_wiki-toc_auto" name="wiki[toc_auto]" value="1" <?php checked($wiki->get_setting('toc_auto', 0), 1); ?> />
						<span class="description"><?php _e('Fügt am Anfang jeder Wiki-Seite automatisch ein Inhaltsverzeichnis (H2/H3) ein. Alternativ kannst du den Shortcode [ps_wiki_toc] an beliebiger Stelle nutzen.', 'ps-wiki'); ?></span>
					</td>
				</tr>
					<th scope="row"><label for="psource_wiki-autolink_enabled"><?php _e('Automatische Wiki-Verlinkung', 'ps-wiki'); ?></label></th>
					<td>
						<input type="checkbox" id="psource_wiki-autolink_enabled" name="wiki[autolink_enabled]" value="1" <?php checked($wiki->get_setting('autolink_enabled', 1), 1); ?> />
						<span class="description"><?php _e('Verwende [[Seitentitel]] im Text, um automatisch auf andere Wiki-Seiten zu verlinken. Beispiel: [[Wiki 1]] verlinkt auf die Seite "Wiki 1".', 'ps-wiki'); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th><label for="psource_wiki-slug"><?php _e('Wiki Slug', 'ps-wiki'); ?></label> </th>
					<td> /<input type="text" size="20" id="psource_wiki-slug" name="wiki[slug]" value="<?php echo $wiki->get_setting('slug'); ?>" /></td>
				</tr>
				   <tr valign="top">
					   <th><label for="psource_wiki-display_mode"><?php _e('Darstellung', 'ps-wiki'); ?></label></th>
					   <td>
						   <select id="psource_wiki-display_mode" name="wiki[display_mode]">
							   <option value="list" <?php selected($wiki->get_setting('display_mode'), 'list'); ?>><?php _e('Liste', 'ps-wiki'); ?></option>
							   <option value="grid" <?php selected($wiki->get_setting('display_mode'), 'grid'); ?>><?php _e('Grid', 'ps-wiki'); ?></option>
						   </select>
					   </td>
				   </tr>
				   <tr valign="top">
					   <th><label for="psource_wiki-excerpt_type"><?php _e('Auszugslänge', 'ps-wiki'); ?></label></th>
					   <td>
						   <input type="number" min="5" max="500" id="psource_wiki-excerpt_length" name="wiki[excerpt_length]" value="<?php echo esc_attr($wiki->get_setting('excerpt_length', 30)); ?>" style="width:60px;" />
						   <select id="psource_wiki-excerpt_type" name="wiki[excerpt_type]">
							   <option value="words" <?php selected($wiki->get_setting('excerpt_type', 'words'), 'words'); ?>><?php _e('Wörter', 'ps-wiki'); ?></option>
							   <option value="chars" <?php selected($wiki->get_setting('excerpt_type', 'words'), 'chars'); ?>><?php _e('Zeichen', 'ps-wiki'); ?></option>
						   </select>
						   <span class="description"><?php _e('Wie lang sollen die Auszüge in Listen und Grid sein?', 'ps-wiki'); ?></span>
					   </td>
				   </tr>
				<?php
				if ( class_exists('Wiki_Premium') ) {
					Wiki_Premium::get_instance()->admin_page_settings();
				} ?>
			</table>

			<?php
			if ( ! class_exists('Wiki_Premium') ) : ?>
			<h3><?php _e('<a target="_blank" href="https://cp-psource.github.io/ps-wiki/">Upgrade jetzt</a> um neue Features zu erhalten!', 'ps-wiki'); ?></h3>

			<ul>
				<li><?php _e('Gib die Anzahl der Breadcrumbs an, die dem Titel hinzugefügt werden sollen', 'ps-wiki'); ?></li>
				<li><?php _e('Gib einen benutzerdefinierten Namen für Wikis an', 'ps-wiki'); ?></li>
				<li><?php _e('Sub-Wikis hinzufügen', 'ps-wiki'); ?></li>
				<li><?php _e('Gib an wie Sub-Wikis bestellt werden sollen', 'ps-wiki'); ?></li>
				<li><?php _e('Ermögliche anderen Benutzern als dem Administrator, Wikis zu bearbeiten', 'ps-wiki'); ?></li>
			</ul>
			<?php
			endif; ?>
			<p class="submit">
			<input type="submit" class="button-primary" name="submit_settings" value="<?php _e('Änderungen speichern', 'ps-wiki') ?>" />
			</p>
		</form>
		<?php
	}

	function maybe_save_settings() {
		global $wiki;

		if ( isset($_POST['wiki_settings_nonce']) ) {
			check_admin_referer('wiki_save_settings', 'wiki_settings_nonce');

			$new_slug = untrailingslashit($_POST['wiki']['slug']);

			if ( $wiki->get_setting('slug') != $new_slug )
				update_option('wiki_flush_rewrites', 1);

					$wiki->settings['slug'] = $new_slug;
					// Darstellung speichern

						$wiki->settings['display_mode'] = isset($_POST['wiki']['display_mode']) && in_array($_POST['wiki']['display_mode'], array('list','grid')) ? $_POST['wiki']['display_mode'] : 'list';
						$wiki->settings['excerpt_length'] = isset($_POST['wiki']['excerpt_length']) ? max(5, intval($_POST['wiki']['excerpt_length'])) : 30;
						$wiki->settings['excerpt_type'] = isset($_POST['wiki']['excerpt_type']) && in_array($_POST['wiki']['excerpt_type'], array('words','chars')) ? $_POST['wiki']['excerpt_type'] : 'words';
						$wiki->settings['autolink_enabled'] = isset($_POST['wiki']['autolink_enabled']) ? 1 : 0;
						$wiki->settings['toc_auto'] = isset($_POST['wiki']['toc_auto']) ? 1 : 0;
						$wiki->settings = apply_filters('wiki_save_settings', $wiki->settings, $_POST['wiki']);

			update_option('wiki_settings', $wiki->settings);

			if ( !function_exists('get_editable_roles') )
				require_once ABSPATH . 'wp-admin/includes/user.php';
			$roles = get_editable_roles();

			foreach ( $roles as $role_key => $role ) {
				$role_obj = get_role($role_key);

				if ( isset($_POST['edit_wiki_privileges'][$role_key]) )
					$role_obj->add_cap('edit_wiki_privileges');
				else
					$role_obj->remove_cap('edit_wiki_privileges');
			}
			
			wp_redirect('edit.php?post_type=psource_wiki&page=psource_wiki&psource_wiki_settings_saved=1');
			exit;
		}
	}
}

new Wiki_Admin_Page_Settings();