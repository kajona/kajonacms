#Reference: Constants

* `_admin_nr_of_rows_` module: system
Number of records per page in admin-lists
* `_admin_only_https_` module: system
If enabled, the backend-access is allowed using https only. If the backend is loaded using http, the system sends a redirect to load the page using https. The header to be checked (in order to detect https) may be configured using the config.php file:

```
   $config[’https_header’] = "HTTPS";
   $config[’https_header_value’] = "on";
```

* `_admin_skin_default_` module: system
The default backend skin
* `_admins_group_id_` module: system
SystemID of the global admin-group. Shouldn't be changed.
* `_guests_group_id_` module: system
SystemID of the guests group. All users not being logged in are treated as members of the guest group.
* `_mediamanager_default_filesrepoid_` module: mediamanager
SystemID of the default repository to select files from. Used as an entry-repo for file-selection dialogs.
* `_mediaamanager_default_imagesrepoid_` module: mediamanager
SystemID of the default repository to select images from.
v3template module: packagemanagement
Name of the templatepack currently active. Change the active pack using the packagemanagement, not by changing the setting directly.
* `_packageserver_repo_id_` moduel: packageserver
SystemID of the mediamanger repository used to store the packages. If given, the packageserver searches in this repo for packages only, otherwise in all repos.
* `_pages_cacheenabled_` module: pages
Enables or disables the pages-cache
* `_pages_defaulttemplate_` modul: pages
The default-template to be used when creating new pages. Is selected by default when creating a new page, but may be overriden by the user.
* `_pages_errorpage_` module: pages
Name of the page to be shown in the portal case of errors.
* `_pages_indexpage_` module: pages
Default page of the portal, loaded if no explicit pagename was requested
* `_pages_newdisabled_` module: pages
If active, new pages are disabled by default (and therefore not visible in the portal)
* `_pages_portaleditor_` module: pages
Enables or disables the portaleditor globally
* `_pages_templatechange_` module: pages
Allows or disallows the change of a pages' template in case there are elements on the page. If set to true (change allowed), the tempalte may be changed. Note that this may lead to inconsistencies (ghost elements), where elements are stored in the backend but not shown on the portal. In most cases this is due to differing placeholders on the templates.
* `_remoteloader_max_cachetime_` module: system
Number of seconds external sources (such as RSS feeds) queried by the remoteloader are being cached (and not queried again)
* `_stats_duration_online_` module: stats
Number of seconds a user may be idle, but still being listed as an active visitor by the stats
* `_stats_exclusionlist_` module: stats
Comma-separated list of domains (referers) to be excluded from some stats-reports (e.g. localhost)
* `_stats_nrofrecords_` module: stats
Number of records per page on stats reports
* `_system_admin_email_` module: system
Mail-address of the administrator. Will be used to send messages to in case of errors.
_` system_browser_cachebuster_` module: system
A counter which may be added to urls of js- and css files. The counter is increased on module-updates, forcing browsers to reload the files.
* `_system_changehistory_enabled_` module: system
Enables or disabled the internal change-history. Depending on the number of records per system, the changelog may generate a large volume of data.
* `_system_dbdump_amount_` module: system
Number of database-dumps to keep. Oldest dumps are deleted if the limit is reached.
* `_system_graph_type_` module: system
Charting-library to be used
* `_system_lock_maxtime_` module: system
Number of seconds a record is locked. Normally records are unlocked automatically. Otherwise, the records' lock is released after the given number of seconds.
* `_system_mod_rewrite_` module: system
Enables or disables the URL-rewriting on the system-side. If enabled, the .htaccess rules have to be enabled, too.
* `_system_portal_disable_` module: system
If enabled, the portal is disabled completely. May be used during maintenance phases.
* `_system_portal_disablepage_` module: system
Fallback page to be shown if the portal is disabled
* `_system_release_time_` module: system
Session-timeout in seconds. After an inactivity for the given number of seconds, the session is closed on the server-side.
* `_system_timezone_` module: system
The timezone to be used by the system. The syntax if the value is based on the PHP-setting, see: http://www.php.net/manual/en/timezones.php. 
* `_system_use_dbcache_` module: system
Enables / Disables the internal database-cache. Should be disabled with care!
* `_user_log_nrofrecords_` module: user
Number of records per page when rendering the user-login log
* `_user_selfedit_` module: user
Enables or disables the editing of a users' own data. Enabled by default.
 
##Runtime / Environment

Kajona provides a number of constants created during runtime or based on config-entries. 

* `_realpath_` absolute path on the filesystem pointing to the Kajona installation. Example: /var/www/html/kajona
* `_webpath_` absolute URL pointing to the current Kajona installation. Example: http://www.example.com/kajona. The path is based on the browser-request!
* `_xmlLoader_` boolean value indicating if the current request is triggered by index.php (false) or xml.php (true) 

The following constants are based on the values given in config.php:

* `_dbprefix_` Prefix to be added to table-names
* `_templatepath_` relative path to the templatepacks. Default: /templates.
* `_projectpath_` relative path to the project-files. Default: /project.
* `_filespath_` relative path to the user-defined files. Default:/files.
* `_langpath_` relative path to the language-files per package. Default: /lang.
* `_indexpath_` absolute URL to the installations index.php file. Example: https://www.kajona.de/index.php 
* `_xmlpath_`  absolute URL to the installations xml.php file. Example: https://www.kajona.de/xml.php
* `_dblog_` Enables / Disables the logging of database-queries to a logfile
* `_timedebug_` Enables / Disables the rendering of time consumed to generate the page on the server-side.
* `_dbnumber_` Enables / Disables the rendering of database-queries handled to generate the page on the server-side.
* `_templatenr_` Enables / Disables the rendering of templates processed to generate the page on the server-side.
* `_memory_` Enables / Disables the rendering of memory consumed to generate the page on the server-side.
* `_cache_` Enables / Disables the rendering of cache-hits generated by the page on the server-side.
* `_workflow_is_running _` indicates if the current request is used to trigger the workflow engine. May be helpful to fire additional checks or to skip some checks