=== Plugin Name ===
Contributors: Mike_Cowobo
Donate link: http://trenvo.com/
Tags: editor, front-end, wysiwyg, live, live admin, raptor
Requires at least: 3.5
Tested up to: 3.5
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Front-end editing with back-end flexibility

== Description ==

With Front End Editor you can edit your posts where they appear -on your website-, but with all the flexibility and options you would expect from back-end editing. And more. The only real What You See Is What You Press editor.

Live Editor is unobtrusive and appears as a tab next to "Visual" and "Text" in the Post editor. Clicking on it takes you to the Live Editing interface, based on WordPress' own Theme Customizer, showing all metaboxes you would normally expect from your posting page.

You can also set Live Editor as the default editor of choice in the user settings ( Users->Your Profile ).

Live Editor uses [WP Raptor](http://wordpress.org/extend/plugins/wp-raptor) as WYSIWYG editor and bundles a known compatible version. If you have your own version of the plugin installed Live Editor will use that one.

*This plugin is only freshly released, so use with care. Please leave any comments, bugs or suggestion in the Support section of the plugin page!*

*If you want to help develop this plugin, visit the [GitHub repo](https://github.com/mgmartel/WP-Live-Editor).*

= Features =
* What You See Is What You Press
* Access to *all* metaboxes available in the Post Editor\*
* Live previews of options, like Page templates
* Unobtrusive - defaults to appearing as an editor tab
* API for plugin and theme developers, to add custom transports for their options (currently only supports refresh)
* Uses Theme Customizer native styles for seamless integration with WordPress

\* The current version of Live Editor uses the screen options from the normal Editor to show or hide meta boxes

*Live Editor is part of [Live Admin](http://trenvo.com) and works great with [Live Dashboard](http://wordpress.org/extend/plugins/live-dashboard/), [Live Theme Preview](http://wordpress.org/extend/plugins/live-theme-preview/) and [WP Getting Started](http://wordpress.org/extend/plugins/wp-getting-started/)*

== Installation ==

Install and activate.

Live Editor will now appear as a tab next to Visual and HTML in your post editor.

To set Live Editor as your default editor, go to your profile (Users->Your Profile), tick the box and save.

== Frequently Asked Questions ==

= When I add a new post, the Live Editor tab doesn't show up! =

The only way to add new posts using Live Editor is by setting it as your default editor in your user profile. Alternatively, save your post as a draft and the tab will show up.

= The 'Live' tab doesn't show up at all =

When the Visual Editor is disabled, there are no tabs above the editor. Instead, a button 'Use Live Editor' appears above the Publish metabox on the right.

== Screenshots ==

1. Live Editor in action

== Changelog ==

= 0.1 =
* Initial release

== Other Notes ==

= For Developers =

If your plugin or theme adds metaboxes to the post screen, or has custom fields that influence the way a post is displayed (eg. by adding a map), you can set Live Editor to automatically refresh the preview pane each time that option is updated.

To add a transport, use `$live_editor->add_metabox_transport( $metabox_slug, $transport );` (NOTE: only 'refresh' is now supported as transport mode)

The right moment to do this would be during the "live_editor_add_metabox_transports" action hook. For example:

`
function my_plugin_metabox_transport( $live_editor ) {
    $live_editor->add_metabox_transport( 'my_metabox_slug', 'refresh' );
}
add_action ( 'live_editor_add_metabox_transports', 'my_plugin_metabox_transport');
`

That's it! Live Editor will now automatically refresh when your metabox's content changes.

Then, you'll want to do something with your changes. For this, you can hook into live_editor_transports-{your_metabox_slug}. You can find the contents of your metabox either in the first param to the action, or later on in the `$live_editor` global:

`$live_editor->transport_params['your_metabox_slug']`

In summary and for example:

`
function live_editor_my_metabox_slug_transport ( $metabox_contents ) {
    // Do something with $metabox_contents
    // or add your actions and filters
    add_filter('the_content','my_metabox_content_filter');
}
add_action ( 'live_editor_transports-my_metabox_slug', 'live_editor_my_metabox_slug_transport' );

function my_metabox_content_filter( $content ) {
    global $live_editor;
    $live_editor_metabox_contents = $live_editor->transport_params['my_metabox_slug'];
    // Do something!

    return $content;
}
`

You can also check meta-box-transports.php in the lib folder of the plugin for examples.