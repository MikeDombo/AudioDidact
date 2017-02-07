# PodTube
Makes an RSS podcast feed from given YouTube, SoundCloud, and CRTV URLs

## Configuration
Download this repository and then edit `config.php` to add your [Google API server key.](https://console.developers.google.com/apis/credentials)
Set hostname to your public ip or domain and subdirectory.

![config.php](https://raw.githubusercontent.com/md100play/PodTube/master/README-images/config-php.PNG)

- Set database configuration, also in `config.php`.
- Set `batchProcess.php` to run as often as you like using the Windows Task Sheduler or cron on *nix. This script is used to delete files once they are kicked out of every user's feed. If you have lots of disk space, then you may not want to run this script ever, so that the audio and thumbnails are always available.


## Usage
Open your web browser to the server that you have set up. Make a new account using the sign up link on the home page. Copy in a YouTube video's URL on the Add a Video page and click Add Video to Feed.

Subscribe to the generated feed using the URL shown on the Add a Video page to receive updates as they come.

## Documentation
PHP API documentation is available [here](https://md100play.github.io/PodTube/html/index.html).

## Future Tasks
- Allow videos with start points in the midst of the video using YouTube's "&t=" flag to set the *in* marker
- Enable choice between audio and video download

## Completed Tasks
- Added SoundCloud to supported sites
- Audio player with playback speed control added to account page
- Added CRTV to supported sites
- Session files are now written to disk as soon as possible so that a single browser can have multiple pages open and downloading concurrently
- Better session handling (session cookie is now stored for a long time and refreshed often)
- Create and verify databases
- Account management added
- Customize feed options including Title, Author, image, etc.
- Users can choose to keep their feeds private and secured by HTTP Basic Authentication (Requires podcatcher support)
