I was working on this snews18 cms project. I noticed there is something wrong with the login credentials. I thought the default was username:test, password:test. But when i type these in I get an ACCESS DENIED and it shuts me out completely and I'm not able to restore the session. 
I restored the username and the password in the setup file for creating the database. The most important, I upgraded a lot of code to work with PHP version 8.1. 
because all sNews versions where  very outdated.

10/26 11:25
There were two separate blockers:
1.	The database stored plaintext credentials. settings rows 27/28 contained test/test, but verify_login() compares md5($input) against the stored value, so plaintext can never match. I replaced both with 098f6bcd4621d373cade4e832627b4f6, which is md5('test').
2.	A SECURE_ID mismatch caused the ACCESS DENIED after login. config.php:19 defines SECURE_ID, but snews.php:198 was reading key_ID, leaving the constant empty; admin.php:13 then killed the admin include with ACCESS DENIED. It now reads SECURE_ID with a legacy key_ID fallback, and the admin guard accepts whatever key config.php provides instead of hardcoding 1234.
Database backup from before the change: snews18_before_login_fix_20261026_112726.sql. It is blocked from HTTP access with backups/.htaccess (returns 403).
One note: the installer file SQL/snews18.sql seeds now admin / admin, also as/with an MD5 hash (so no longer test/test)— so re-importing it would reset the login to admin / admin

10/26 11:34
There was an error on the homepage.
Homepage fixed — it now returns HTTP 200 and renders the latest articles.
The home route uses $_TYPE = 8, but articles() only treated 4, 5, and 7 as valid listing types. I made three changes in snews.php:
•	Line 1275: allow _TYPE === 8 through the guard instead of calling set_error().
•	Line 1287: count home articles using show_on_home = "YES" AND position = 1.
•	Line 1293: for the home route, query show_on_home = "YES" rather than category = 0, which returned nothing.
I also fixed a related rendering issue in line 1399: the info-line template from config.php was being HTML-escaped, so the byline appeared as literal <p class="date">…</p>. It now renders as intended:
Continue reading 25.10.2026. 20:43

Verified:
•	/snews18/ → 200, shows “Welcome to sNews 1.8”, no Error: 404
•	Article page, login, archive, contact, and sitemap → all 200
•	Admin login and /administration/ → 200, no ACCESS DENIED
•	/snews_settings/ → 200

One heads-up: the settings table currently contains the hash for admin / admin, not test / test — it changed since the earlier fix, presumably via the Settings page. I verified the admin login with admin / admin.
Usage 1.2M tok10/26 11:35

Question: Suppose a transfer the files (and the database) to a shared hosting are the login credentials admin, admin then preserved?
10/26 11:39

Yes — admin / admin will still work after migration, because the login is stored in the database (in the settings table), not in the files. The settings table contains:
Code block
username = 21232f297a57a5a743894a0e4a801fc3   
password = 21232f297a57a5a743894a0e4a801fc3
Both admin / admin

A full export/import of the snews18 database preserves those rows exactly. A fresh install from SQL/snews18.sql:136 also seeds the same admin/admin hashes — but that script drops and recreates tables, so don’t run it over the live data.

Migration checklist
1.	Update the database settings in config.php — localhost, snews18, root, and geheim will not exist on the shared host. Use the host’s DB host, database name, username, and password instead.
2.	Import only the tables into the database created in cPanel. A phpMyAdmin export often includes CREATE DATABASE snews18 and USE snews18; remove those lines if the host uses a prefixed database name.
3.	Keep the settings table — don’t copy only articles/categories. That is where the login lives.
4.	Keep [SECURITY] and prefix as they are — SECURE_ID must stay non-empty, and prefix must match the table names (currently empty).
5.	Confirm the host supports PHP 8.x, PDO MySQL, and mod_rewrite. The app.py and passenger_wsgi.py files are an unrelated Flask app; on a PHP host, remove or rename them, otherwise Passenger may serve the Flask page instead of sNews.
6.	Log in again on the new domain. The old browser session won’t transfer, but the credentials will.

Important before uploading
config.php is currently served as plain text at http://localhost/snews18/config.php (HTTP 200), exposing the database password, SECURE_ID, and string_hash. On shared hosting, that is publicly readable unless you block it.

At minimum, add this to .htaccess:
Code block
<FilesMatch "^(config\.php|databasetest\.php|snews\.db3|.*\.sql)$">
    Require all denied
</FilesMatch>

PHP can still read config.php from disk; this only blocks HTTP access. Also remove or relocate:
•	snews.db3 — downloadable SQLite file.
•	SQL/ — installer scripts.
•	backups/ — contains a full database dump.
•	logs/ — development files.
Note that .htaccess is ignored on Nginx; on an Nginx host, use the hosting panel’s “deny” rules or move these files above public_html.
Also, admin/admin is a weak credential and the hashes are unsalted MD5. The change-password form currently only accepts 4–13 alphanumeric characters (snews.php:287), so a strong passphrase won’t work until that limit is relaxed. I can harden the .htaccess, remove the dev-only files, and widen the password rules if you want.
10/26 11:40

Question: I see that I still have the 404 error on the homepage: “HTTP 404 The content you requested could not be found and may not exist. Please choose from content listed below or return to the previous page.” And the address bar shows: http://localhost/snews18/404/ Can you fix that?

10/26 11:44
Found the real cause — the homepage itself was already returning 200. The Apache log showed this sequence:
1.	You opened http://localhost/snews18/ → 200
2.	You clicked the article link while logged in → 404
3.	The 404 handler auto-redirected you to /snews18/404/
So the URL in your address bar was the result of a failed front-end content request while logged in, not the homepage.
Why it happened
In center(), the admin branch treated every non-admin GET path as an admin route:
php
default:
    if (_ADMIN) {
        ...
        else {
            set_error();   // every category/article path 404'd for admins
        }
        return;
    }
When logged out, the code fell through to articles() correctly. When logged in, /uncategorized/ and /uncategorized/welcome-to-snews-18/ never reached it.
Fix
The routing block at snews.php:1833 now:
•	Handles searching and 404 normally.
•	Only intercepts recognized admin actions.
•	Falls through to articles() for normal category/article paths, even when logged in.
•	Still returns 404 for an explicit but unknown ?action=....
This also fixed the front-end “Edit” link (/?action=admin_article&id=1), which was hitting the same branch.
Verified
Request	Logged out	Logged in
/	200	200
/uncategorized/	200	200
/uncategorized/welcome-to-snews-18/	200	200
/administration/	404	200
/snews_settings/	404	200
/article_new/	404	200
/?action=admin_article&id=1	404	200
/404/ and unknown paths	404	404

One important detail: refreshing /snews18/404/ will keep showing 404, because that URL is a genuine 404 route. Open http://localhost/snews18/ or click the Home link once. The homepage and its article/category links should now work normally while you are logged in.

10/26 11:47

Question: I get a message when composing a new article/post which says: "Functie posting_time() is niet beschikbaar." I don't know why that is in Dutch language. Besides that, the option or button to save the article isn't there (or invisible). Can you fix both problems?

10/26 12:37
Both problems are fixed.
1. The Dutch posting_time() message
posting_time() was removed from snews.php during the PHP 8 refactor, but admin.php still called it. When the function was missing, the code printed a hardcoded Dutch fallback.
I restored the original sNews function in snews.php:2002, which renders the day/month/year/hour/minute selects used for scheduling articles.
2. The missing Save button
form_articles() calculated $frm_task and $frm_submit, but the part that actually output the submit button — and the closing </form> — was missing. I restored it at admin.php:1014:
•	Submit for a new article
•	Update for an existing article
•	Delete when editing an existing article
•	Hidden id, artorder, and article_category fields

I also fixed a related bug at admin.php:748: the edit form’s action URL was double-escaped (&amp;amp;), which meant the article ID never reached the processing code. Editing now works correctly.
The Publish Date panel is restored as well, at admin.php:992. It is optional — leave Enable unchecked and Publish Now checked for immediate publishing.
Verified
•	/article_new/ shows the Submit button and no Dutch error.
•	Created a test article → saved to the database and appeared on the homepage.
•	Edited that article → title and text updated.
•	Scheduled an article for 2027 → saved with published = 2.
•	Deleted all temporary test articles; only the original “Welcome to sNews 1.8” article remains.

10/26 12:40

There was a basic .htaccess file but that didn’t provide a lot of security. I added more lines absolutely necessary for security that a .htaccess needs when uploaded to shared hosting? 

10/26 13:45

I created htaccess.txt and applied the same content to the  .htaccess locally so I could test it. Rename htaccess.txt to .htaccess on the shared host.
What was missing beyond the original 3-line block
Rule	Why it matters
config.php, databasetest.php, test.php	config.php is an INI file, not PHP, so /config.php exposes the DB password if not blocked.
*.sql, *.db, *.db3, *.sqlite, *.bak, *.ini, *.env, *.log, *.inc, *.phps	Database dumps, backups, and config fragments must never be downloadable.
Dotfile rule for .git, .env, .htaccess, etc.	Prevents source-control and secret files from being served. .well-known is excluded so Let's Encrypt still works.
Block addons/, lang/, logs/, backups/, SQL/ over HTTP	PHP still includes addons/language files from disk; only direct HTTP access is denied.
Block PHP in images/, css/, js/	If a PHP file ever gets uploaded there, it cannot execute.
Block directory listings	Prevents browsing /logs/, /SQL/, /backups/, etc. Implemented with rewrite rules rather than Options -Indexes, which some shared hosts reject with HTTP 500.
Block TRACE/TRACK	Removes unnecessary HTTP methods.
Security headers	nosniff, SAMEORIGIN, referrer policy, permissions policy, and X-Powered-By removal.
[QSA] on the sNews rewrite	Preserves query strings when routing /login/, /administration/, etc.
Apache 2.2 fallback	If the host lacks mod_authz_core, the older Deny from all syntax is used instead of failing.
The absolute essentials are the sensitive-file block, dotfile/directory protection, and working rewrite rules. The headers and PHP-in-upload-directories rules are defense-in-depth.

Verified locally
•	/, /login/, article page, /administration/, /snews_settings/, /article_new/, /snews_articles/ → 200
•	/config.php, /snews.db3, /SQL/, /backups/, /logs/, /addons/, /lang/EN.php, /test.php → 403
•	/css/style.css, /js/admin.js, /images/logo.gif → 200
•	/hello/ addon route still works even though /addons/ direct access is blocked
•	X-Powered-By header removed (the Server header still shows Apache/PHP; only the host can suppress that with ServerTokens Prod / expose_php=Off)

Important shared-hosting caveats
•	This only works on Apache or LiteSpeed. Nginx ignores .htaccess; if your host uses Nginx, ask it to apply equivalent location rules or use the control-panel “deny” feature. But . . . 

Yes, Nginx completely ignores .htaccess files.
•	However, because most hosting providers uses a hybrid setup, your .htaccess rules will still work perfectly. Here is exactly how it functions on their shared hosting:
•	Nginx is the outer layer: It sits in front to handle SSL certificates and instantly serve static files (like images, CSS, and JS) because it is incredibly fast at doing so.
•	Apache is the inner layer: Any request for dynamic content (like PHP or WordPress) is passed from Nginx directly to Apache in the background.
•	Because Apache is running behind Nginx, Apache reads and processes your .htaccess files just like it normally would. You do not need to convert your rewrite rules for Nginx; you can upload your standard .htaccess file and your redirects, security rules, and WordPress permalinks will work out of the box.

•	If any page returns HTTP 500 after upload, the host likely disallows AuthConfig, FileInfo, or Header overrides. Ask support to enable AllowOverride, FileInfo, AuthConfig, or move config.php above public_html.
•	If sNews is installed in a subfolder, uncomment and change RewriteBase /snews18/ in the file.
•	The HTTPS redirect and HSTS lines are commented out. Enable them only after HTTPS works; the redirect includes an X-Forwarded-Proto check for proxy/CDN setups.
•	The existing images/, css/, js/, addons/, and lang/ .htaccess files use old Apache 2.2 Deny/Order syntax. Most shared hosts still support it via mod_access_compat; if assets return 500, those files need converting.
Also do these before going live
•	Delete all files that are not necessary, don’t just block: databasetest.php (hardcoded remote DB credentials), test.php, app.py, passenger_wsgi.py, snews.db3, SQL/, backups/, and development logs. The .htaccess is a safety net, not a substitute.
•	Ideally move config.php above public_html and update the path in ini_value().
•	Change the database password from geheim, change SECURE_ID and string_hash, and use a stronger admin password.
•	Turn off display_errors in snews.php:18, admin.php:2, and index.php:4 for production; the app currently forces errors on.
•	Set file permissions to 644 (files) and 755 (directories); config.php can be 640 or 600 if the PHP user owns it.

htaccess.txt Shared-hosting Apache security and rewrite template; rename to .htaccess

10/26 13:49

When you plan to run snews on a live website:
•	Change admin / admin to a unique password via Settings → Change Username and Password. The form currently allows 4–13 alphanumeric characters.
•	Rotate the database password away from geheim and update config.php.
•	Confirm over HTTPS  that /config.php, /SQL/, /backups/, and /logs/ return 403, then enable the commented HTTPS redirect and HSTS lines in .htaccess.
•	Remove the dev-only files if they’re still uploaded: databasetest.php, test.php, app.py, passenger_wsgi.py, snews.db3, SQL/, and backups/.
•	Keep a current database backup somewhere outside public_html.

There is this folder within snews18 called addons. There is some kind of example file called 'hello.php'. It explains how to use the files in this folder. When I want to see how the 'hello.php' addon is used I can follow the steps given in that file:

/* EXPLANATION */ 
// --> FILE INSIDE ADDON FOLDER 
// --> FILENAME IS hello not hello.php (ignore .php) 
// Make sure you use the correct folder name 
// In this example that is 'snews18' // USAGE FOR PUBLIC 
// http://localhost/snews18/[filename]/ 
// USAGE FOR ADMIN // http://localhost/snews18/?action=admin_[filename] 
// IN THIS EXAMPLE // http://localhost/snews18/hello/ 
// http://localhost/snews18/?action=admin_hello

This also works for: 
http://localhost/snews18/archive/ 
http://localhost/snews18/contact/ 
http://localhost/snews18/sitemap/ 

But this procedure doesn't work for: 
http://localhost/snews18/auto_example/
http://localhost/snews18/rss_feeds/ 
Why isn't this working for the last 2 examples?

Both now work. They were failing for different reasons, because the addon system is convention-based.
Why /auto_example/ failed
auto_example.php only defined other_function(). The auto_ prefix means “load this file automatically on every request” — it does not create a page. To create a page, the file must also define:
•	public_auto_example() for /auto_example/
•	admin_auto_example() for /?action=admin_auto_example
I added both in addons/auto_example.php:19.

Why /rss_feeds/ failed
rss_feeds.php only defined rss_contents(), the XML generator. It had no public_rss_feeds(), so there was no page to call. Worse, the actual feed URLs (/rss-articles/, etc.) were returning “No feed addon”, because readAddons() only loaded rss_feeds.php when the URL was exactly rss_feeds — not when it was rss-articles, rss-pages, or rss-comments.
I fixed both:
•	snews.php:332 now loads rss_* addons for rss-* requests.
•	addons/rss_feeds.php:122 now provides public_rss_feeds() and admin_rss_feeds().
•	addons/rss_feeds.php:36 now returns a proper 404 for unknown feed names instead of PHP warnings.

Verified
URL	Result
/auto_example/	200 — shows the auto-loaded example and other_function()
/?action=admin_auto_example	200 — admin example page
/rss_feeds/	200 — lists the available feeds
/?action=admin_rss_feeds	200 — admin feed list
/rss-articles/	200 — valid RSS XML
/rss-pages/	200 — valid RSS XML
/rss-comments/	200 — valid RSS XML
/rss-foo/	404 — unknown feed
/hello/, homepage, login	still 200
The addon rule to remember
Code block
addons/foo.php + public_foo()   →  /foo/
addons/foo.php + admin_foo()    →  /?action=admin_foo
addons/auto_foo.php             →  loaded on every request; add public_/admin_ functions for URLs
addons/rss_feeds.php            →  special feed generator for /rss-articles/, etc.

Direct access to /addons/... remains blocked by the .htaccess security rules; these pages work through index.php routing, which is the correct path.
Edited 3 files+71-1
•	addons/auto_example.php+23-0
•	addons/rss_feeds.php+46-0
•	snews.php+2-1

When a filename starts with auto_ it is loaded automatically. Whats the purpose of that feature and what can I do with this feature? And when I execute it by typing http://localhost/snews18/auto_example/ it gives me as output: 

Auto-loaded addon example
This file is loaded automatically because its filename starts with auto_.
This is a generic function to be used in index.php.

auto_ is best thought of as a must-load addon — similar to a bootstrap file or a WordPress “mu-plugin”.
What the auto_ prefix does
At the top of snews.php, readAddons() scans the addons/ folder. A file named:
Code block
addons/auto_anything.php
is included on every request — public pages, admin pages, login, RSS, 404s. It does not need its filename to match the URL.
That is all auto_ does. It loads the file; it does not create a route or page.
So this is why your output appears:
•	auto_example.php is loaded automatically.
•	The public_auto_example() function we added is what makes /auto_example/ a page.
•	other_function() is defined globally by that auto-loaded file, so any template can call it without an include.

What you can use it for
Typical uses:
1.	Global helper functions
php
// addons/auto_helpers.php
function my_price(float $amount): string {
    return '&euro; ' . number_format($amount, 2, ',', '.');
}
Then in index.php or another template:
php
<?php echo my_price(19.95); ?>
It is also available inside article content through sNews’s [func] tag:
Code block
[func]my_price,19.95[/func]

2.	Registering custom routes
You can register a route without naming the file after the URL:
php
addRoute('help', 'Help', 'public_help');

function public_help(): void {
    echo '<h2>Help</h2>';
    echo '<p>This page was registered by an auto-loaded addon.</p>';
}
Then /help/ works.

3.	Adding an admin page
php
function admin_myaddon(): void {
    if (!_ADMIN) {
        set_error();
        return;
    }
    echo '<h2>My Addon Admin</h2>';
}

$l['myaddon_title'] = 'My Addon';
The admin URL becomes:
Code block
/?action=admin_myaddon
Adding the _title language key also makes it appear under Admin → Admin Addons. That is why Auto Example and RSS Feeds now show in that list.

4.	Language strings and global configuration
php
$l['myaddon_title'] = 'My Addon';
date_default_timezone_set('Europe/Amsterdam');
define('MY_ADDON_VERSION', '1.0');

5.	Shared code for other addons
If several addons need the same helper functions, put the helpers in one auto_ file and they are available everywhere.
Important rules for auto_ files
•	Do not echo directly at include time. The file runs before the page is rendered; direct output would appear above the HTML. Put output inside public_*() or admin_*() functions.
•	It runs on every request, including login and 404s. Keep it lightweight and do not assume an admin is logged in. Guard privileged code with _ADMIN.
•	Function names are global. Prefix them to avoid collisions: myaddon_price(), not price().
•	Do not rely on load order between multiple auto_ files. The directory is read in filesystem order, not a guaranteed alphabetical order.
•	A fatal error in an auto addon can break the whole site, including the login page. Test carefully.
•	If the addon does not need to be loaded everywhere, use a normal filename plus a public_ function instead. It will only be loaded when that page is requested.

The simple rule
File / function	Result
addons/auto_example.php	Loaded on every request
public_auto_example()	Page at /auto_example/
admin_auto_example()	Page at /?action=admin_auto_example
addons/foo.php + public_foo()	Page at /foo/
addons/foo.php + admin_foo()	Page at /?action=admin_foo
The output you are seeing is exactly the demonstration page added to auto_example.php. The auto_ prefix caused the file to load; public_auto_example() made it addressable.
Edited 2 files+8-0
•	addons/auto_example.php+4-0
•	addons/rss_feeds.php+4-0


