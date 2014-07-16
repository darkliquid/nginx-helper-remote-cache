=== nginx-helper-remote-cache ===
Contributors: darkliquid
Tags: nginx, cache, purge, remote cache
Requires at least: 3.0.1
Tested up to: 3.9
Stable tag: 0.1.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds in some additional features to nginx-helper for allowing purging of the nginx cache when it 
is stored on a remote machine.

== Description ==

Provides a new admin menu dropdown and hooks onto post saves in order to purge nginx fastcgi caches 
via purge URL requests, instead of trying to delete a local cache directory. This grants you the 
ability to clear the cache of any page by going to it and selecting the **Clear cache for current URL**
option, or to clear the whole cache by selecting the **Clear cache for all pages** option

== Installation ==

1. Extract the zip file.
2. Upload the contents to the `/wp-content/plugins/` directory of your WordPress installation.
3. Then activate the plugin from Plugins page.

== Frequently Asked Questions ==

= Do I need anything else to make this work? =

Yes. You need to have installed and configured the [nginx-helper plugin](http://wordpress.org/extend/plugins/nginx-helper/)

== Changelog ==

= 0.1.0 =

* Initial release
