=== FMPress Forms ===
Contributors: newsfront, nue2501
Tags: pagination, nextpage, shortcode
Requires at least: 5.7
Tested up to: 6.0
Stable tag: 1.0.0
Requires PHP: 5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A link to the next page can be displayed using a short code.

== Description ==

A link to the next page can be displayed using a short code.

== Requirements ==

- PHP version 5.3 or greater.

== Installation ==

1. Upload the entire `nextpage-link` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

After activation of the plugin, Add `[nextpage_link]LINK TEXT[/nextpage_link]` to the body of your post.

== License ==

All files and scripts are licensed under GNU General Public License.

== Frequently Asked Questions ==

= Are there any shortcode parameters? =

Support for id, type, layout, in_same_term, excluded_terms, previous, taxonomy and prefix.

Example:

`[nextpage_link type="pagination"]H2 text on the next page[/nextpage_link]
    => Link to the next page separated by <! --nextpage-->
    => Default if type is not specified

[nextpage_link id="1234"]Link Text[/nextpage_link]
    => Link post ID 1234

[nextpage_link type="nextpost"]Link Text[/nextpage_link]
    => Link to adjacent post

[nextpage_link prefix="Next page: "]Link Text[/nextpage_link]
    => Output is Next page: <a href="#">Lint Text</a>
    => Default is "Next:"`

= Can the layout be changed? =

The layout attribute can be used to specify the layout.

1. Copy `nextpage-link/template-parts` as `template-parts/nextpage-link` to your theme folder.
2. Change `layout.php` or create a new `layout-new.php`.
3. If you create a new `layout-new.php`, specify `[nextpage_link layout="new"]...`.

= Can you control adjacent articles in detail? =

It can be controlled by the following parameters.

- `in_same_term="0/1" (Default: 0)`
- `excluded_terms="1,2,3,4" (Default: empty string)`
- `previous="0/1" (Default: 0)`
- `taxonomy="post_tag" (Default: category)`

These parameters are passed to get_adjacent_post().

== Changelog ==

= 1.0.0 =
Release Date: September 12, 2022

*Plugin Released

= 0.9 =
* First version (Unreleased)
