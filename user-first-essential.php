<?php
/**
 * Plugin Name: User First Essential
 * Plugin URI: https://wordpress.org/plugins/user-first-essential
 * Description: This plugin helps you set permalink structure and remove default plugins, themes, posts, and pages.
 * Author: Huzaifa Al Mesbah
 * Author URI: https://huzaifa.im
 * Text Domain: user-first-essential
 * License: GPL v3
 * Requires at least: 6.0
 * Tested up to: 6.3
 * Requires PHP: 7.0
 * Version: 1.0
 */


// Prevent direct access to this file.
if (!defined('ABSPATH')) {
    die('Direct access is not allowed.');
}

class User_First_Essential_Plugin {

    public function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_init', array($this, 'handle_form_submission'));
        add_action('admin_init', array($this, 'check_activation_redirect'));
    }

    public function load_textdomain() {
        load_plugin_textdomain('user-first-essential', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }

    public function add_menu_page() {
        add_menu_page(
            esc_html__('UFE', 'user-first-essential'),
            esc_html__('UFE', 'user-first-essential'),
            'manage_options',
            'ufe-settings',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        ?>
<div class="wrap">
    <h2><?php echo esc_html__('User First Essential Settings', 'user-first-essential'); ?></h2>
    <form method="post" action="">
        <h3><?php echo esc_html__('Permalink Settings', 'user-first-essential'); ?></h3>
        <label>
            <input type="checkbox" name="set_permalink" value="yes" />
            <?php echo esc_html__('Set Permalink Structure to Post Name', 'user-first-essential'); ?>
        </label>

        <h3><?php echo esc_html__('Remove Default Plugins and Themes', 'user-first-essential'); ?></h3>
        <label>
            <input type="checkbox" name="remove_default_plugins" value="yes" />
            <?php echo esc_html__('Remove Default Plugins (Hello Dolly and Akismet)', 'user-first-essential'); ?>
        </label>
        <label>
            <input type="checkbox" name="remove_default_themes" value="yes" />
            <?php echo esc_html__('Remove Default Themes (Twenty Twenty-One, Twenty Twenty-Two,Twenty Twenty Three)', 'user-first-essential'); ?>
        </label>

        <h3><?php echo esc_html__('Remove Default Posts and Pages', 'user-first-essential'); ?></h3>
        <label>
            <input type="checkbox" name="remove_default_posts_pages" value="yes" />
            <?php echo esc_html__('Remove Default "Hello World" Post and "Sample Page"', 'user-first-essential'); ?>
        </label>
        <h3><?php echo esc_html__('Actions', 'user-first-essential'); ?></h3>
        <input type="submit" name="ufe_remove_all" class="button button-primary"
            value="<?php echo esc_attr__('Remove All', 'user-first-essential'); ?>" />
    </form>
</div>
<?php
    }

    public function handle_form_submission() {
        if (isset($_POST['ufe_remove_all'])) {
            if (isset($_POST['set_permalink'])) {
                global $wp_rewrite;
                $wp_rewrite->set_permalink_structure('/%postname%/');
                $wp_rewrite->flush_rules();
            }

            if (isset($_POST['remove_default_plugins'])) {
                $this->remove_default_plugins();
            }

            if (isset($_POST['remove_default_themes'])) {
                $this->remove_default_themes();
            }

            if (isset($_POST['remove_default_posts_pages'])) {
                $this->remove_default_posts_pages();
            }
        }
    }

    public function remove_default_plugins() {
        if (is_plugin_active('hello-dolly/hello.php')) {
            deactivate_plugins('hello-dolly/hello.php');
            delete_plugins(array('hello-dolly/hello.php'));
        }

        if (is_plugin_active('akismet/akismet.php')) {
            deactivate_plugins('akismet/akismet.php');
            delete_plugins(array('akismet/akismet.php'));
        }
    }

    public function remove_default_themes() {
        $themes_to_remove = array(
            'twentytwentyone',
            'twentytwentytwo',
            'twentytwentythree'
        );

        foreach ($themes_to_remove as $theme) {
            if (wp_get_theme($theme)->exists()) {
                switch_theme('ufe-temp-theme');
                delete_theme($theme);
            }
        }
    }

    public function remove_default_posts_pages() {
        // Remove the default "Hello World" post
        $hello_world_post = get_page_by_title('Hello world!', OBJECT, 'post');
        if ($hello_world_post) {
            wp_delete_post($hello_world_post->ID, true);
        }

        // Remove the default "Sample Page"
        $sample_page = get_page_by_title('Sample Page', OBJECT, 'page');
        if ($sample_page) {
            wp_delete_post($sample_page->ID, true);
        }
    }

    public function check_activation_redirect() {
        if (get_option('ufe_plugin_activated')) {
            delete_option('ufe_plugin_activated');
            wp_safe_redirect(admin_url('admin.php?page=ufe-settings'));
            exit;
        }
    }
}

$User_First_Essential_plugin = new User_First_Essential_Plugin();

// Check if the plugin has been activated
register_activation_hook(__FILE__, 'ufe_set_activation_flag');
function ufe_set_activation_flag() {
    update_option('ufe_plugin_activated', true);
}