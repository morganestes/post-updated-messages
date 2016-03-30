=== Post Updated Messages ===
Contributors: morganestes
Tags: post messages, custom post types, admin
Requires at least: 3.7.0
Tested up to: 4.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tailored updated messages for custom post types.

== Description ==

Changes the default "Post updated" message to reflect the actual post type. It uses the labels set when a
post type is registered to display "My Post Type updated".

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/post-updated-messages` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.

== Frequently Asked Questions ==

= What post types use the custom messages? =

By default, all custom post types will use the custom messages. Posts and pages will use their default messages.

= Can I exclude a post type from custom messages?  =

Sure. You can add your post type slug to the `pum_post_types_nofilter` filter to use the default 'post' messages.

= Can I change a message? =

Yep. Use the `pum_post_messages` filter to change the action words (e.g. "updated", "published"), or `pum_post_bulk_messages` filter for the bulk actions (e.g "permanently deleted", "moved to the Trash" ).

= Can I change the post type name? =

C'mon, you gotta do *some* work yourself! (hint: read the inline docs)

== Changelog ==

= 1.0.0 =
* Initial release.

== Cow Picture ==

 ______________________________
< Post Updated Messages Rocks! >
 ------------------------------
        \   ^__^
         \  (oo)\_______
            (__)\       )\/\
                ||----w |
                ||     ||
