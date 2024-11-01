=== Plugin Name ===
Contributors: youzu bigapp team
Donate link: http://youzu.com/
Tags: app,json,json api 
Requires at least:3.4 
Tested up to: 3.4
Stable tag: 3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provide json api and the web master could create app for phone.

== Description ==
This is a app service.This plugin is designed for mobile client customized, and provide formatted data such as articles, reviews for mobile clients . When the actual use，a client application will be used with the plugin.for the convenience of owners, we provide  automatically packaged service for the application, which is deployed on a dedicated server.the plugin will call the server and show application information such as address,application name  in admin page .

The service we named bigstation,have some functions as follows(http://bigapp.youzu.com/):
1. Set app property 
2. compiled app (including ios, android) 
3. version management
4. statistics 

For security, the bigstation should login use your phone.You should register it in first used.Then you will get app key and secret key.
Fill in the keys to plugin  admin page,the bigstation will verify and pass.


= Version 1.0.0 (25-08-2015) =
* NEW: Initial Release


== Installation ==

1. Open `wp-content/plugins` Folder
2. Put: `Folder: wp-bigapp`
3. Activate `WP-BigApp` Plugin
4. Go to `WP-Admin -> Settings -> bigapp` to configure the plugin.
5. Go to web station,and register a account.And set app key and secret_key.

= Usage =
1,open 'http://host/wordpress/index.php?yz_app=1&api_route=posts&action=get_posts'


== Screenshots ==

== Changelog ==
2016/3/16
提交了之前添加的功能，包括第三方登录，修改头像，收藏等功能。头像功能依赖其他插件(wp-user-avatar)

== Upgrade Notice ==

== Arbitrary section ==


