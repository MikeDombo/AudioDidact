# AudioDidact

[![pipeline status](http://git.home.mikedombrowski.com/michael/AudioDidact/badges/master/pipeline.svg)](http://git.home.mikedombrowski.com/michael/AudioDidact/commits/master)
[![coverage report](http://git.home.mikedombrowski.com/michael/AudioDidact/badges/master/coverage.svg)](http://git.home.mikedombrowski.com/michael/AudioDidact/commits/master)

## Description

Makes an RSS podcast feed from given YouTube, Vimeo, SoundCloud, and CRTV URLs. Also supports manually uploading audio
or video, setting the metadata, and uploading album art.

## Table of Contents

- [Description](#description)
- [Get Started Immediately](#get-started-immediately)
  - [Usage](#usage)
- [Running Your Own Server](#running-your-own-server)
  - [Configuration](#configuration)
  - [Configuration With Evironment Variables](#configuration-with-evironment-variables)
- [Updating](#updating)
- [Documentation](#documentation)
- [Future Tasks](#future-tasks)
- [Completed Tasks](#completed-tasks)

## Get Started Immediately

To get started immediately, make an account [https://ytpod.mikedombrowski.com](https://ytpod.mikedombrowski.com), add content, and subscribe to the generated feed with your favorite podcast player.

### Usage

Open your web browser to the server that you have set up. Make a new account using the sign up link on the home page. Copy in a YouTube video's URL on the Add a Video page and click Add Video to Feed.

Subscribe to the generated feed using the URL shown on the Add a Video page to receive updates as they come.

## Running Your Own Server

### Configuration

Download this repository and then edit `config.yml` to add your [Google API server key.](https://console.developers.google.com/apis/credentials)
Set "force-https" to true if you want to use SSL.

- Set database configuration, also in `config.yml`.
- Install pug-php, symfony/yaml, and mongodb/mongodb (optional) using composer
- If using MongoDB, install MongoDB extension from [pecl](https://pecl.php.net/package/mongodb).
- Set `batchProcess.php` to run as often as you like using the Windows Task Sheduler or cron on linux. This script is used to delete files once they are kicked out of every user's feed. If you have lots of disk space, then you may not want to run this script ever, so that the audio and thumbnails are always available.

### Configuration With Environment Variables

Instead of editing `config.yml`, you can choose to set the options through environment variables. For this to work PHP 
must have environment variables enabled (check php.ini).

Set variables in the following format `AD_<NAME OR GROUP NAME>_<NAME IF WITHIN GROUP>`

Examples:

- download-directory = AD_DOWNLOAD_DIRECTORY
- api-keys: google = AD_API_KEYS_GOOGLE
- database: connection-string = AD_DATABASE_CONNECTION_STRING

All Available Environment Variables:
- API_KEYS_GOOGLE - string - Google API Key used for getting YouTube video title and description
- DOWNLOAD_DIRECTORY - string - Directory where podcast media is stored. Must be publicly accessible
- FORCE_HTTPS - [true|false] - True redirects all requests to https.
- EMAIL_FROM - string - Who the signup and password reset emails are from. Example: `"\"AudioDidact Administrator\"<michael@mikedombrowski.com>"`
- EMAIL_ENABLED - [true|false] - True if email verification is required for signups. When false no emails will be sent for any reason.
- DATABASE_DRIVER - [mysql|sqlite|mongodb] - Selected database storage method
- DATABASE_ALWAYS_CHECK - [true|false] - True if the database should always be verified for correctness with every request. Does not apply to mongodb.
- DATABASE_CONNECTION_STRING - string - Connection string or url for database. Examples:
  - For MySQL: `"mysql:host=HOSTNAME;dbname=DATABASE_NAME;charset=utf8"`
  - For SQLite: `"sqlite:FILE_PATH.sqlite"`
  - For MongoDB: `"mongodb://localhost:27017"`
- DATABASE_USER - string - Database connection username - only for MySQL
- DATABASE_PASSWORD - string - Database password - Only for MySQL
- DATABASE_DATABASE_NAME - string - Collection name for MongoDB
- SUPPORTED_SITES_CRTV - string - nllinktoken cookie value from CRTV. Only required when you want to download from CRTV. CRTV subscription required.

## Docker
Docker can be used to run AudioDidact with the automatically built [AudioDidact image](https://hub.docker.com/r/mikedombo/audiodidact/). An example docker-compose that uses MySQL is included in this repository.

## Updating

To update to the latest release on GitHub download or clone the repository to your computer.

1. Move all files in the src directory to your current install location, overwrite all existing files
1. Edit `config.yml` to make sure that the settings are correct
1. Load the site in your browser to force the database check/update and then you're done

## Documentation

PHP API documentation is available [here](https://MikeDombo.github.io/AudioDidact/html/index.html).

## Future Tasks

- Join sessions if the same user is logged in on multiple computers. Possibly move sessions into DB, or just keep on filesystem since that's working fine.
- More statistics. Possibly a GitHub style punchcard.

## Completed Tasks

- Dynamically find all SupportedSites so that new ones can simply be placed in the correct folder and will not require other code changes
- Moved configuration into yaml
- Add Vimeo support
- Easier updating using config-update.php
- Enable choice between audio and video download
- Add manual upload
- Switch to Pug for HTML rendering
- Add password reset
- Add email verification
- Add SoundCloud to supported sites
- Audio player with playback speed control added to account page
- Add CRTV to supported sites
- Session files are now written to disk as soon as possible so that a single browser can have multiple pages open and downloading concurrently
- Better session handling (session cookie is now stored for a long time and refreshed often)
- Create and verify databases
- Account management added
- Customize feed options including Title, Author, image, etc.
- Users can choose to keep their feeds private and secured by HTTP Basic Authentication (Requires podcatcher support)
