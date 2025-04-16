<?php
/*
Plugin Name: HTML Sitemap Generator All 
Plugin URI: https://codenskills.com
Description: Display an HTML sitemap including pages, posts, authors, and custom post types.
Version: 1.3
Author: Yashvir Pal
Author URI: https://yashvirpal.com
License: GPL2
Text Domain: html-sitemap-generator-all
*/

if (!defined('ABSPATH')) {
    exit;
}

class HTML_Sitemap_Generator
{
    public function __construct()
    {
        add_shortcode('html_sitemap', [$this, 'generate_html_sitemap']);
        add_action('admin_menu', [$this, 'html_sitemap_settings_menu']);
        add_action('admin_init', [$this, 'html_sitemap_register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    // Register settings page
    public function html_sitemap_settings_menu()
    {
        add_options_page('HTML Sitemap Settings', 'HTML Sitemap', 'manage_options', 'html-sitemap-settings', [$this, 'html_sitemap_settings_page']);
    }

    // Settings page output
    public function html_sitemap_settings_page()
    {
        ?>
        <div class="wrap">
            <h1>HTML Sitemap Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('html_sitemap_options');
                do_settings_sections('html-sitemap-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Register settings  
    public function html_sitemap_register_settings()
    {
        register_setting('html_sitemap_options', 'html_sitemap_show_posts', ['sanitize_callback' => 'sanitize_html_sitemap_show_posts']);
        register_setting('html_sitemap_options', 'html_sitemap_show_authors', ['sanitize_callback' => 'sanitize_html_sitemap_show_authors']);
        register_setting('html_sitemap_options', 'html_sitemap_show_taxonomies', ['sanitize_callback' => 'sanitize_html_sitemap_show_taxonomies']);
        register_setting('html_sitemap_options', 'html_sitemap_per_page', ['sanitize_callback' => 'sanitize_html_sitemap_per_page']);

        add_settings_section('html_sitemap_main_section', 'Sitemap Options', null, 'html-sitemap-settings');

        add_settings_field(
            'html_sitemap_show_posts',
            'Show Blog Posts',
            [$this, 'html_sitemap_checkbox'],
            'html-sitemap-settings',
            'html_sitemap_main_section',
            ['name' => 'html_sitemap_show_posts']
        );

        add_settings_field(
            'html_sitemap_show_authors',
            'Show Authors',
            [$this, 'html_sitemap_checkbox'],
            'html-sitemap-settings',
            'html_sitemap_main_section',
            ['name' => 'html_sitemap_show_authors']
        );

        add_settings_field(
            'html_sitemap_show_taxonomies',
            'Show Taxonomies',
            [$this, 'html_sitemap_checkbox'],
            'html-sitemap-settings',
            'html_sitemap_main_section',
            ['name' => 'html_sitemap_show_taxonomies']
        );

        add_settings_field(
            'html_sitemap_per_page',
            'Items Per Page',
            [$this, 'html_sitemap_number_input'],
            'html-sitemap-settings',
            'html_sitemap_main_section',
            ['name' => 'html_sitemap_per_page']
        );
    }


    // Ensure these functions exist in your class or file
    function sanitize_html_sitemap_show_posts($input)
    {
        return sanitize_text_field($input);
    }

    function sanitize_html_sitemap_show_authors($input)
    {
        return sanitize_text_field($input);
    }

    function sanitize_html_sitemap_show_taxonomies($input)
    {
        return sanitize_text_field($input);
    }

    function sanitize_html_sitemap_per_page($input)
    {
        return absint($input);
    }
    public function html_sitemap_checkbox($input)
    {
        return ($input === '1') ? '1' : '';
    }

    public function html_sitemap_number_input($input)
    {
        return absint($input);
    }









    // Generate sitemap
    public function generate_html_sitemap()
    {
        global $wpdb;

        $show_posts = get_option('html_sitemap_show_posts', 'yes') === 'yes';
        $show_authors = get_option('html_sitemap_show_authors', 'yes') === 'yes';
        $show_taxonomies = get_option('html_sitemap_show_taxonomies', 'yes') === 'yes';
        $per_page = (int) get_option('html_sitemap_per_page', 20);
        if (isset($_GET['sitemap_page']) && isset($_GET['_wpnonce'])) {
            $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce'])); // Unslash and sanitize nonce
            if (!wp_verify_nonce($nonce, 'sitemap_pagination')) {
                die('Security check failed'); // Stop execution if nonce is invalid
            }
            $paged = absint($_GET['sitemap_page']);
        } else {
            $paged = 1;
        }

        $offset = ($paged - 1) * $per_page;

        // **Check if cached data exists**
        $cache_key = 'html_sitemap_data_' . $paged;
        $results = wp_cache_get($cache_key, 'html_sitemap');

        if ($results === false) {
            $results = [];

            // **Pages**
            $pages = get_posts([
                'post_type' => 'page',
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids',
            ]);
            foreach ($pages as $page_id) {
                $results[] = (object) ['ID' => $page_id, 'title' => get_the_title($page_id), 'type' => 'Pages'];
            }

            // **Custom Post Types**
            $custom_posts = get_posts([
                'post_type' => ['custom_type_1', 'custom_type_2'], // Add your custom types
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids',
            ]);
            foreach ($custom_posts as $post_id) {
                $results[] = (object) ['ID' => $post_id, 'title' => get_the_title($post_id), 'type' => 'Custom Post'];
            }

            // **Blog Posts**
            if ($show_posts) {
                $posts = get_posts([
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'fields' => 'ids',
                ]);
                foreach ($posts as $post_id) {
                    $results[] = (object) ['ID' => $post_id, 'title' => get_the_title($post_id), 'type' => 'Blog Posts'];
                }
            }

            // **Authors**
            if ($show_authors) {
                $authors = get_users(['fields' => ['ID', 'display_name']]);
                foreach ($authors as $author) {
                    $results[] = (object) ['ID' => $author->ID, 'title' => $author->display_name, 'type' => 'Authors'];
                }
            }

            // **Taxonomies**
            if ($show_taxonomies) {
                $taxonomies = get_terms([
                    'taxonomy' => ['category', 'post_tag'], // Add your taxonomies
                    'hide_empty' => false,
                ]);
                foreach ($taxonomies as $term) {
                    $results[] = (object) ['ID' => $term->term_id, 'title' => $term->name, 'type' => 'Taxonomies'];
                }
            }

            // **Sort Results**
            usort($results, function ($a, $b) {
                return strcmp($a->type . $a->title, $b->type . $b->title);
            });

            // **Cache the results**
            wp_cache_set($cache_key, $results, 'html_sitemap', 3600); // Cache for 1 hour
        }

        // **Pagination**
        $total_results = count($results);
        $total_pages = ceil($total_results / $per_page);
        $paged_results = array_slice($results, $offset, $per_page);

        // **Output Sitemap**
        ob_start();
        echo '<div class="html-sitemap"><h2>📌 Sitemap</h2>';

        $current_type = '';
        foreach ($paged_results as $item) {
            if ($item->type !== $current_type) {
                if ($current_type !== '') {
                    echo '</ul>';
                }
                echo '<h3>' . esc_html(ucwords(str_replace('_', ' ', $item->type))) . '</h3><ul>';
                $current_type = $item->type;
            }
            $url = isset($item->ID) ? get_permalink($item->ID) : '#';
            echo '<li><a href="' . esc_url($url) . '">' . esc_html($item->title) . '</a></li>';
        }
        echo '</ul></div>';

        // **Pagination Links**
        if ($total_pages > 1) {
            echo '<div class="pagination">';
            for ($i = 1; $i <= $total_pages; $i++) {
                echo '<a href="' . esc_url(add_query_arg(['sitemap_page' => $i, '_wpnonce' => wp_create_nonce('sitemap_pagination')])) . '">' . esc_html($i) . '</a>';
            }
            echo '</div>';
        }

        return ob_get_clean();
    }





    // Enqueue styles
    public function enqueue_styles()
    {
        wp_enqueue_style(
            'html-sitemap-style',
            plugin_dir_url(__FILE__) . 'assets/style.css',
            [], // No dependencies
            '1.3' // Plugin version or use file modification time
        );
    }

}

// Initialize the plugin
new HTML_Sitemap_Generator();
