=== All-Content HTML Sitemap Generator ===
Contributors: yashvirpal  
Tags: html sitemap, seo, pages, posts, custom post types  
Requires at least: 5.0  
Tested up to: 6.8
Requires PHP: 7.4  
Stable tag: 1.3
License: GPL-2.0+  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Display an HTML sitemap including pages, posts, authors, and custom post types with pagination support.

== Description ==

The **All-Content HTML Sitemap Generator** plugin lets you display a **user-friendly, paginated sitemap** of your website, including:
- Pages  
- Blog posts  
- Custom post types  
- Authors  
- Taxonomies  

You can customize the sitemap display via the **All-Content HTML Sitemap Generator** menu.

== Installation ==

1. Download the plugin `.zip` file.
2. Upload the extracted folder to `/wp-content/plugins/`.
3. Activate the plugin in the **Plugins** menu in WordPress.
4. Add the `[html_sitemap]` shortcode to any page where you want the sitemap to appear.

== Frequently Asked Questions ==

= How do I use the shortcode? =  
Simply add `[html_sitemap]` to a page or post.

= Can I exclude certain post types? =  
Currently, all post types except system ones (`attachment`, `revision`, etc.) are included.

= Does this support pagination? =  
Yes, you can set the number of items per page in **Settings → HTML Sitemap**.

== Screenshots ==

1. **Example of the sitemap output.**  
2. **Admin settings page to configure the sitemap.**  

== Changelog ==

= 1.2 =  
* Added settings for pagination.  
* Improved security with nonce verification and sanitization.  
* Fixed styling issues for the frontend sitemap.  

= 1.1 =  
* Added support for custom post types.  
* Improved shortcode handling.  

= 1.0 =  
* Initial release.  

== Upgrade Notice ==

= 1.2 =  
Recommended upgrade for better pagination and security improvements.

== License ==

This plugin is released under the **GPL-2.0+** license.  
