# AudioDidact
Makes an RSS podcast feed from given YouTube, SoundCloud, and CRTV URLs. Also supports manually uploading audio or video, setting the metadata, and uploading album art. 

# Get Started Immediately
To get started immediately, make an account [https://ytpod.mikedombrowski.com](https://ytpod.mikedombrowski.com), add content, and subscribe to the generated feed with your favorite podcast player.

# Running Your Own Server
## Configuration
Download this repository and then edit `config.php` to add your [Google API server key.](https://console.developers.google.com/apis/credentials)
Set hostname to your public ip or domain and subdirectory.

![config.php](https://raw.githubusercontent.com/md100play/PodTube/master/README-images/config-php.PNG)

- Set database configuration, also in `config.php`.
- Install pug-php using composer
- Set `batchProcess.php` to run as often as you like using the Windows Task Sheduler or cron on linux. This script is used to delete files once they are kicked out of every user's feed. If you have lots of disk space, then you may not want to run this script ever, so that the audio and thumbnails are always available.


## Usage
Open your web browser to the server that you have set up. Make a new account using the sign up link on the home page. Copy in a YouTube video's URL on the Add a Video page and click Add Video to Feed.

Subscribe to the generated feed using the URL shown on the Add a Video page to receive updates as they come.

## Documentation
PHP API documentation is available [here](https://md100play.github.io/AudioDidact/html/index.html).

## Future Tasks
- More statistics. Possibly a GitHub style punchcard.

## Completed Tasks
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
