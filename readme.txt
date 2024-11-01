=== Sidebar Content ===
Contributors: stephenlgray
Tags: sidebar, page content, pages, custom post type
Requires at least: 3.0
Tested up to: 4.4.1
Stable tag: trunk

Allows creation of reusable sidebar content and easy inclusion on pages

== Description ==

Adds a new post type called 'Sidebar Content': reusable snippets with a title, content and an optional link. These are then listed on the editing screen for each page with checkboxes : simply tick the required snippet(s) and they will appear wherever the template tag is placed.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `sg-sidebar-content` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the template tag `<?php sg_sidebar_content_display(); ?>` to your template wherever you want the content to appear (usually the sidebar).

== Frequently Asked Questions ==



== Screenshots ==


== Changelog ==

= 0.2 =
* Tested with WP 4.4.1
* Heading is now also link if specified
