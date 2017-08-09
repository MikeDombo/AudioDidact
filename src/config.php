<?php
/** Add your configuration details only in this file */

// Global Constants

/**  Change to your hostname. End with trailing slash */
define("LOCAL_URL", "https://localhost/audiodidact/");
/**  Change to subdirectory if any. End with trailing slash. If no subdirectory, enter "" */
define("SUBDIR", "audiodidact/");
/** Add server key here */
define("GOOGLE_API_KEY", "****");

/** Set the directory where you want to download the thumbnails, videos, and audio. This must be publicly accessible */
define("DOWNLOAD_PATH", "temp");
/** Set to true if your server is setup for HTTPS (highly recommended); */
define("SESSION_COOKIE_SECURE", true);
/** Set the from and reply-to field of all emails sent from AudioDidact.
 * EX: "\"AudioDidact Administrator\"<michael@mikedombrowski.com>"
 */
define("EMAIL_FROM", "\"AudioDidact Administrator\"<michael@mikedombrowski.com>");

// Database constants
/** Choose your DAL and Constants */

//MySQL Database Usage

	define("ChosenDAL", "\\AudioDidact\\MySQLDAL");
	define("DB_HOST", "localhost");
	// Even if database, user, and password are not used, they have to be set to something
	define("DB_DATABASE", "audiodidact");
	define("DB_USER", "root");
	define("DB_PASSWORD", "");
	define("PDO_STR", 'mysql:host='.DB_HOST.';dbname='.DB_DATABASE.';charset=utf8');


// SQLite Database Usage
/*
	define("ChosenDAL", "\\AudioDidact\\SQLite");
	// Path to SQLite database file
	define("DB_FILE", "database.sqlite");
	define("PDO_STR", 'sqlite:'.DB_FILE);
*/


// MongoDB Database Usage
/*
	define("ChosenDAL", "\\AudioDidact\\MongoDBDAL");
	define("PDO_STR", "mongodb://localhost:27017");
	define("DB_DATABASE", "audiodidact");
*/

//
//
// Do not manually modify below this line
//
//
/** Defines if a database validation is necessary */
define("CHECK_REQUIRED", true);
