# PodTube
Makes an RSS podcast feed from given YouTube URLs

## Configuration
Download this repository and then edit `config.php` to add your [Google API server key.](https://console.developers.google.com/apis/credentials)
Set hostname to your public ip or domain and subdirectory.

![config.php](https://raw.githubusercontent.com/md100play/PodTube/master/README-images/config-php.PNG)

Set database configuration also in `config.php`. Execute the SQL script in the Schema directory or manually replicate the same structure. In future versions the software will be able to make its own database schema, but for now you must do it.

## Usage
Open your web browser to the server that you have set up. Make a new account using the sign up link on the home page. Copy in a YouTube video's URL on the Add a Video page and click Add Video to Feed.

Subscribe to the generated feed using the URL shown on the Add a Video page to receive updates as they come.

## Documentation
PHP API documentation is available [here](https://md100play.github.io/PodTube/html/index.xhtml).

## Future Tasks
- Customize feed options including Title, Author, image, etc.

## Completed Tasks
- Better session handling (session cookie is now stored for a long time and refreshed often)
- Create and verify databases
- Account management added
