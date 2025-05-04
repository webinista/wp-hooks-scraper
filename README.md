# Scraping WordPress actions and filters pages

1. Update the version in classes/Conf.php
2. Run download.php to download the pages linked from each URL.
3. Run parse.php to parse the pages.
4. Run a case-sensitive search for "name":"Hooks" on resulting wordpress-hooks.json file and remove the object. 