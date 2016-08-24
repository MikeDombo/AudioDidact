<?php
/** Add your configuration details only in this file */

// Global Constants

/**  Change to your hostname. End with trailing slash */
define("LOCAL_URL", "http://example.com/");
/**  Change to subdirectory if any. End with trailing slash. If no subdirectory, enter "" */
define("SUBDIR", "");
/** Add server key here */
define("GOOGLE_API_KEY", "****");
/** Set the directory where you want to download the thumbnails, videos, and audio. This must be publicly accessible */
define("DOWNLOAD_PATH", "temp");
/** Choose your DAL, currently the only option is MySQLDAL */
define("ChosenDAL", "MySQLDAL");
/** Set to true if your server is setup for HTTPS (highly recommended); */
define("SessionCookieSecure", true);

// Database constants
define("DB_HOST", "localhost");
define("DB_DATABASE", "podtube");
define("DB_USER", "podtube");
define("DB_PASSWORD", "podtube");


//
//
// Do not manually modify below this line
//
//
/** Defines if a database validation is necessary */
define("CHECK_REQUIRED", true);