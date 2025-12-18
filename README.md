# wc-tags-lowercase

=== Convert Tags to Lowercase for WooCommerce ===
Contributors: your_name
Tags: woocommerce, tags, lowercase, products, product tags
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.2
WC requires at least: 5.0
WC tested up to: 8.0
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically converts all WooCommerce product tags to lowercase.

== Description ==

This plugin automatically converts all WooCommerce product tags to lowercase. It helps maintain consistency in tag naming and prevents duplicate issues due to uppercase/lowercase differences.

= Features =

* Automatic conversion when saving products
* Bulk conversion of all existing tags
* Individual tag conversion
* Bulk actions in tags list
* User-friendly admin interface
* Compatible with WooCommerce 5.0+
* Translation ready

== Installation ==

1. Upload the `wc-tags-lowercase.zip` file to the WordPress plugins section
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Tags Lowercase to use conversion tools

== Usage ==

1. **Automatic operation**: When creating or editing a product, tags are automatically converted to lowercase.

2. **Convert all tags**: In the plugin admin page, you can convert all existing tags at once.

3. **Convert individual tags**: In the product tags list, use the "Convert to lowercase" bulk action or individual buttons.

4. **Translation**: The plugin is translation ready. Language files are located in the `/languages/` folder.

== Frequently Asked Questions ==

= Does this plugin affect SEO? =
It shouldn't negatively affect SEO. Tag slugs remain the same (only visible names change).

= Can I revert the changes? =
There's no automatic revert function. We recommend backing up before converting all tags.

= Does it work with special characters? =
Yes, it only converts letters to lowercase, respecting special characters and accents.

= How to translate the plugin? =
Copy the .pot file from the languages folder and create your own .po/.mo files using Poedit or similar tools.

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic tag conversion
* Bulk conversion tools
* Admin interface
* Translation ready

== Development Notes ==

This plugin is compatible with WordPress 5.0+ and WooCommerce 5.0+. It doesn't make database changes that cannot be manually reverted.
