=== Frontend Dashboard Custom Post and Taxonomies ===
Contributors: vinoth06, buffercode
Tags: frontend dashboard, custom post type, cpt, taxonomies, frontend submission
Donate link: https://www.paypal.com/paypalme2/buffercode
Requires at least: 6.1
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend Dashboard Custom Post is an add-on to add, manage, and customize custom post types and custom taxonomies directly inside Frontend Dashboard.

== Description ==

> #### Notice
> This is a free add-on plugin for [Frontend Dashboard](https://buffercode.com/plugin/frontend-dashboard). Please install and activate Frontend Dashboard (v3.0.0+) to use this plugin.

**Frontend Dashboard Custom Post and Taxonomies** enables seamless creation and frontend submission workflows for WordPress custom post types, categories, and tags.

### Key Features
* **Post Status Control**: Allow frontend users to submit posts directly to Published or Pending Review status based on user roles.
* **Custom Post Type Builder**: Create, edit, and configure custom post types with custom icons, menu order, and supported attributes.
* **Custom Taxonomy Builder**: Create custom hierarchical (categories) and non-hierarchical (tags) taxonomies attached to any post type.
* **Role-Based Access**: Restrict post submission, editing, and taxonomy assignments based on specific user roles.
* **Disable Default Attributes**: Selectively disable built-in post attributes (featured images, content editor, excerpt, comments) per post type.
* **Frontend Post Management**: Full frontend CRUD interface for users to add, edit, preview, and delete their own posts.

== Installation ==

1. Upload the `frontend-dashboard-custom-post` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure **Frontend Dashboard** is installed and active.
4. Navigate to **Frontend Dashboard > Custom Post** or **Custom Taxonomies** to start configuring.
5. Save settings.

== Changelog ==

= 3.0.0 =
* Major Release: Full compatibility and deep integration with Frontend Dashboard 3.0.0 App Shell.
* Modernized admin settings and field builders.
* Improved frontend submission forms, file attachment handling, and taxonomy selection.
* Enhanced role permissions, soft-delete capabilities, and post listing pagination.
* Security: Enhanced nonce verification and capability checks on all submission actions.
* Fully tested with WordPress 6.7 and PHP 8.0 / 8.1 / 8.2 / 8.3.

= 1.5.10 =
* Added filter hooks [fed_cp_list_details].

= 1.5.9 =
* Pagination updated and added a filter hook to show all posts or single post to admin.

= 1.5.8 =
* Added new features and styles to Post and Custom Post.

= 1.5.7 =
* Bug fixes.

= 1.5.6 =
* Frontend Dashboard Custom Post new features added and bug fixes.

= 1.5.5 =
* Bug fixes.

= 1.5.3 =
* Post and Custom Post soft delete support.

= 1.5.2 =
* Bug fixes.

= 1.5.1 =
* Frontend Dashboard Post Ordering Fixed and translation updates.

= 1.5 =
* Support Frontend Dashboard 1.5.

= 1.4.10 =
* Bug Fixes: Custom post taxonomy update for specific roles.

= 1.4.9 =
* Bug Fixes: Custom post delete and save.

= 1.4.8 =
* Bug Fixes.

= 1.4.7 =
* Bug Fixes: Post/Custom post show post content.

= 1.0 =
* Public release.

== Upgrade Notice ==

= 3.0.0 =
Major release: Modernized CPT/Taxonomy management and complete compatibility with Frontend Dashboard 3.0.0.

== Screenshots ==
1. Dashboard Settings
2. Settings
3. Add Taxonomy - Settings
4. Add Taxonomy - Label Settings
5. Add Taxonomy - Basic Settings
6. Add Custom Post - Built-in Taxonomies
7. Add Custom Post - Supports
8. Add Custom Post - Label Settings
9. Add Custom Post - Basic Settings
10. Admin Post/Custom Post Taxonomies
11. Admin Post/Custom Post Menu
12. Admin Post/Custom Allow user role to Add/Edit/Delete Post
