<?php
/*
  Plugin Name: Nginx Helper Remote Cache Extensions
  Description: An extension to nginx helper for working with remote caches.
  Version: 0.1.0
  Author: Andrew Montgomery-Hurrell
  Author URI: http://multiplay.com
  Requires at least: 3.0
  Tested up to: 3.5
*/

/**
 * PHP version 5
 *
 * @link http://github.com/multiplay/nginx-helper-remote-cache
 * @license Proprietary
 * @author Andrew Montgomery-Hurrell
 * @category Wordpress
 * @package NginxHelperRemoteCache
 * @version 0.1.0
 *
 */

/**
 * Add manual cache purge options into the admin bar
 *
 * @param bool $meta
 */
function nginx_helper_remote_cache_adminbar_menu($meta = true)
{
    global $wp_admin_bar;
    global $wp;

    // No adminbar for logged out users
    if (!is_user_logged_in()) {
        return;
    }

    // No admin bar unless you are an admin and the bar is meant to be shown
    if (!is_super_admin() || !is_admin_bar_showing()) {
        return;
    }

    // There is a bar to modify, so get to it!
    $wp_admin_bar->add_menu(
        array(
            'id' => 'nginx_helper_remote_cache_menu',
            'title' => __('Nginx Remote Cache')
        )
    );

    $purge_url = add_query_arg(array('nginx_helper_remote_cache_action' => 'purge_all'));
    $nonced_url = wp_nonce_url($purge_url, 'nginx_helper_remote_cache');

    $wp_admin_bar->add_menu(
        array(
            'parent' => 'nginx_helper_remote_cache_menu',
            'id'     => 'nginx_helper_remote_cache_clear_all',
            'title' => __('Clear cache for all pages'),
            'href' => $nonced_url
        )
    );

    $purge_url = add_query_arg(
        array(
            'nginx_helper_remote_cache_action' => 'purge_url',
            'nginx_helper_remote_cache_purge_url' => site_url($_SERVER['REQUEST_URI'])
        )
    );

    $nonced_url = wp_nonce_url($purge_url, 'nginx_helper_remote_cache');

    $wp_admin_bar->add_menu(
        array(
            'parent' => 'nginx_helper_remote_cache_menu',
            'id'     => 'nginx_helper_remote_cache_clear_current_url',
            'title' => __('Clear cache for current URL'),
            'href' => $nonced_url
        )
    );
}

/**
 * Purge EVERYTHING. Assumes custom post types are purgable using standard post
 * permalinks
 *
 * @global bool $rt_wp_nginx_purger
 * @global WPDB $wpdb
 */
function nginx_helper_remote_cache_purge_all()
{
    global $rt_wp_nginx_purger;
    if (isset($rt_wp_nginx_purger)) {
        global $wpdb;

        $posts = $wpdb->get_results(
            "SELECT ID,post_type
            FROM {$wpdb->posts}
            WHERE post_status='publish' AND post_type NOT IN ('revision','nav_menu_item')"
        );

        foreach ($posts as $post) {
            switch ($post->post_type) {
                case 'page':
                    $permalink = get_page_link($post->ID);
                    break;
                case 'post':
                    $permalink = get_permalink($post->ID);
                    break;
                case 'attachment':
                    $permalink = get_attachment_link($post->ID);
                    break;
                default:
                    $permalink = get_post_permalink($post->ID);
                    break;
            }
            $rt_wp_nginx_purger->purgeUrl($permalink);
        }
    } else {
        return 'NGINX HELPER PLUGIN PURGER CLASS NOT LOADED';
    }
    wp_redirect(home_url());
    exit;
}

/**
 * Purge the passed in URL
 *
 * @param $url string URL to purge
 * @global bool $rt_wp_nginx_purger
 */
function nginx_helper_remote_cache_purge_url($url)
{
    global $rt_wp_nginx_purger;
    if (isset($rt_wp_nginx_purger)) {
        //error_log('purging url: ' . $url);
        $rt_wp_nginx_purger->purgeUrl($url);
    } else {
        return 'NGINX HELPER PLUGIN PURGER CLASS NOT LOADED';
    }
    wp_redirect($url);
    exit;
}

/**
 * Purge the passed in Post by ID
 *
 * @param $post_id int ID of post to purge
 * @global bool $rt_wp_nginx_purger
 */
function nginx_helper_remote_cache_purge_post($post_id)
{
    global $rt_wp_nginx_purger;
    if (isset($rt_wp_nginx_purger)) {
        $url = get_permalink($post_id);
        //error_log('purging post: '.$post_id.' with  url: ' . $url);
        $rt_wp_nginx_purger->purgeUrl($url);
    }
}

/**
 * Add special handlers to the query parsing so we can get commands called 
 * from links, etc
 *
 * @param $wp WP_Query wordpress query object
 */
function nginx_helper_remote_cache_action_handler($wp)
{
    if (array_key_exists('nginx_helper_remote_cache_action', $wp->query_vars) && check_admin_referer('nginx_helper_remote_cache')) {
        switch ($wp->query_vars['nginx_helper_remote_cache_action']) {
            case 'purge_all':
                $error = nginx_helper_remote_cache_purge_all();
                break;
            case 'purge_url':
                $error = nginx_helper_remote_cache_purge_url($wp->query_vars['nginx_helper_remote_cache_purge_url']);
                break;
        }
        // process the request.
        // For now, we'll just call wp_die, so we know it got processed
        wp_die("nginx_helper_remote_cache: $error");
    }
}

/**
 * Register the query vars we will be hooking into
 *
 * @param $vars array Array of variables to look for on the query
 */
function nginx_helper_remote_cache_query_vars($vars)
{
    $vars[] = 'nginx_helper_remote_cache_action';
    $vars[] = 'nginx_helper_remote_cache_purge_url';

    return $vars;
}

/**
 * Initialise plugin hooks
 *
 */
function nginx_helper_remote_cache_init()
{
    add_filter('query_vars', 'nginx_helper_remote_cache_query_vars');
    add_action('parse_request', 'nginx_helper_remote_cache_action_handler');
    add_action('admin_bar_menu', 'nginx_helper_remote_cache_adminbar_menu', 150);
    add_action('save_post', 'nginx_helper_remote_cache_purge_post');
}

// Init plugin
add_action('init', 'nginx_helper_remote_cache_init');
