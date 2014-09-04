=== MySQLi database layer ===
Contributors: CodeKitchen, markoheijnen
Tags: database, backend
Requires at least: 3.6
Tested up to: 3.9
Stable tag: 1.2
License: GPLv2 or later

MySQL_* functions are deprecated and this plugin creates a layer to use MySQLi instead. So you don't get notices when using PHP 5.5

== Description ==

MySQL_* functions is deprecated and this plugin creates a layer to use MySQLi instead. So you don't get notices when using PHP 5.5

Be sure to read the installation instructions, as this is **not** a traditional plugin, and on activation you do need to install our database driver by using the settings screen or move manually db.php into your content directory.


== Installation ==

1. Verify that you have MySQLi
2. Go to the settings page of this plugin and install our driver or just copy `db.php` to your WordPress content directory (`wp-content/` by default)
3. Done!

== Changelog ==

= 1.2 ( 2014-09-04 ) =
* WordPress 3.9 support what already has MySQLi support.
* Code cleanup

= 1.1 ( 2013-09-18 ) =
* Updated to all changes in core.
* Change _real_escape() method to always use mysqli_real_escape_string().

= 1.0 ( 2013-06-23 ) =
* First release