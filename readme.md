AutoModerator
=============

AutoModerator is a WordPress plugin that connects to Impermium's [comment tagging API](http://impermium.com/api/index.php?client=endevver&version=4.0&ref=93a3f "comment tagging API") and uses it to automatically moderate & tag wordpress comments.

Installation
------------

Upload the wp-impermium.zip file into your WordPress install's wp-content/plugins/ directory via FTP and activate the plugin in your WordPress admin or through WordPress' http uploader ("Plugins" -> "Add New" -> "Upload") & activate.

Usage
-----

Once the plugin is installed you'll find that it adds a new menu item under the "Settings" menu in the WordPress admin named "AutoModerator."

The first thing you'll need to do is add an AutoModerator API key. There will be instructions on that page on how to obtain a key if you don't already have one.

Once your API key has been entered, you'll want to modify the action to be taken when a comment is tagged with any of AutoModerator's supported tags. Your moderation efforts will now be put on auto-pilot ... all of the comments that come in on your WordPress website will be passed through Impermium's API and will be tagged appropriately based on the comment's content.

As comments start coming in and are being tagged, AutoModerator adds additional capabilities to the "Comments" page. You'll now be able to filter comments by tag, block / unblock comments and sort by tag.

Architecture
------------

### MVC ###

The code in **AutoModerator** is organized into a Model View Controller (MVC) structure. Files for all three of these can be found in the "app" directory.

1. **Models**: These classes handle the persistence and business logic of the app. You'll find most of the interactions with the WordPress database and the Impermium APIs.
2. **Controllers**: These classes handle any page routing, connecting to WordPress hooks and standalone routing (usually used for front end AJAX fragments).
3. **Views**: These are presentation layer files that contain mainly HTML & display logic.
4. **Helpers**: These classes implement presentation layer helper methods to render various elements.

### Models ###

- **ImprAccount.php**: This is where the logic to validate the Impermium API key resides.
- **ImprComment.php**: This class handles all of the interaction with the Impermium comments API for checking comments, blocking, & unblocking. This class also handles the logic behind sorting & filtering the comments for the WordPress admin "Comments" page.
- **ImprOptions.php**: This class provides an interface for all of the options that AutoModerator stores in the WordPress database.
- **ImprRemote.php**: This class implements the lower-level HTTP code for transporting API requests. This uses the WP\_Http WordPress helper methods rather than curl ... WP\_Http is the preferred (and more compatible) way to send server-side http requests.
- **ImprUtils.php**: This contains a grab bag of utility methods. These methods include user/login, password, mailing, redirection methods and more.

### Controllers ###

- **ImprAppController.php**: This is an overall method that handles setting up the admin menus in WordPress, enqueuing scripts & styles and handles the admin notices.
- **ImprCommentsController.php**: This class handles all of the WordPress hooks for passing comments to Impermium & back and all of the hooks for modifying the "Comments" page in the WordPress admin.
- **ImprOptionsController.php**: This class implements the "AutoModerator" settings page in the WordPress admin.
- **ImprUpdateController.php**: Since AutoModerator isn't available on the WordPress.org public repository, this class is necessary for interacting with Impermium's server to provide automatic updates to users.

### Views ###

- **options/info.html**: A simple HTML document containing the help information for the javascript popup on the AutoModerator settings page
- **options/ui.php**: This is the main view for the AutoModerator settings page.
- **shared/akismet.php**: The warning message displayed in the WordPress admin when a user has Akismet & AutoModerator activated at the same time
- **shared/errors.php**: A view to display form validation errors
- **shared/headline.php**: The warning message displayed in the WordPress admin when a user hasn't entered an API key yet
- **shared/request\_api\_key.php** (*deprecated*): This file contains a form that was used in Phase 1 of AutoModerator to request an API key. In phase 2 we moved to displaying a simple email address that can be used to request an API key.
- **shared/version\_not\_supported.php**: This contains a message that's displayed when the user has a version of AutoModerator that is no longer supported (AutoModerator checks to see the supported version when it tries to update in *ImprUpdateController.php*).

### Helpers ###

- **ImprAppHelper.php**: Contains application-level helpers ... currently this includes a helper to create select form elements from arrays.
- **ImprCommentsHelper.php**: Contains a helper to construct the abuse filter on the Comments page in the WordPress admin.

### Assets ###

- **css**: The folder containing css assets.
- **images**: The folder containing images.
- **js**: The folder containing javascript assets.

### Other key files ###

- **wp-impermium.php**: This is the main plugin file ... it contains the plugin header (in comments at the top), sets up all of the path constants used by the plugin, sets up the text locale (will use if/when AutoModerator is translated into other languages), auto-loads our classes, registers the uninstall hook and loads the WordPress hooks found in our controllers.
- **uninstall.php**: This contains our cleanup / uninstall script that runs when the plugin is deleted.

Unit Tests
----------

AutoModerator uses [EnhancePHP](http://www.enhance-php.com/) and [MockPress](https://github.com/Caseproof/mockpress) to implement some basic unit tests for its Models.

The unit tests themselves can be found in **test/tests/** and can be run in two different ways:

1. Via the command line: ```php ./test/run_tests.php```
2. Via the web browser by visiting ```http://mycoolsite.com/wp-content/plugins/wp-impermium/test/run_tests.php```

**Note:** When creating the zip files of this plugin the test directory should not be included because the tests can be run from the browser which could create a security issue for some users.

Internationalization
--------------------

AutoModerator is _translation ready_ (using [WordPress' built in gettext translation tools](http://codex.wordpress.org/I18n_for_WordPress_Developers))... so when the time comes to translate it a *.pot file will be generated containing all of the strings in the plugin. Then we'll be able to get translations made and included in the plugin.

All translation files will be stored and loaded from the **i18n/** directory.