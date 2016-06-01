# PodTube
Makes an RSS podcast feed from given YouTube URLs

## Configuration
Download this repository and then edit `youtube.php` to add your [Google API server key.](https://console.developers.google.com/apis/credentials)
Set hostname to your public ip or domain and subdirectory.
In your `php.ini` you must set `output_buffer=Off` for the progress bar to work. (Everything else works fine even without this change)

## Usage
Open your web browser and simply enter in a YouTube link or ID into the textbox at the top of the page. Click 'Add Video To Feed'.
In any podcatcher, simply add the url for the generated `rss.xml` file.
