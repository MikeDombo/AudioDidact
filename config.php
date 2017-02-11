<?php
/** Add your configuration details only in this file */

// Global Constants

/**  Change to your hostname. End with trailing slash */
define("LOCAL_URL", "https://localhost/podtube/");
/**  Change to subdirectory if any. End with trailing slash. If no subdirectory, enter "" */
define("SUBDIR", "podtube/");
/** Add server key here */
define("GOOGLE_API_KEY", "****");
/** Set the directory where you want to download the thumbnails, videos, and audio. This must be publicly accessible */
define("DOWNLOAD_PATH", "temp");
/** Set to true if your server is setup for HTTPS (highly recommended); */
define("SessionCookieSecure", true);
/** Set the from and reply-to field of all emails sent from AudioDidact.
 * EX: "\"AudioDidact Administrator\"<michael@mikedombrowski.com>"
 */
define("EMAIL_FROM", "\"AudioDidact Administrator\"<michael@mikedombrowski.com>");

// Database constants
/** Choose your DAL and Constants */
define("ChosenDAL", "MySQLDAL");
define("DB_HOST", "localhost");
// Even if database, user, and password are not used, they have to be set to something
define("DB_DATABASE", "podtube");
define("DB_USER", "****");
define("DB_PASSWORD", "****");

// SQLite Database Usage
//define("ChosenDAL", "SQLite");
// Path to SQLite database file
//define("DB_HOST", "database.sqlite");


//
//
// Do not manually modify below this line
//
//
/** Defines if a database validation is necessary */
define("CHECK_REQUIRED", true);
