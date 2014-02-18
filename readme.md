Garmin Uploader
===========

Simple tool for uploading fit files to Garmin Connect platform. Unfortunately Garmin Connect doesn't support uploading fit files via API and that's why it is little bit tricky…

Example of usage:

```php
$uploader = new Uploader(new CurlConnector); // or new DirectConnector

$uploader->setUserCredentials('user', 'password');
$response = $uploader->uploadFit(file_get_contents(__DIR__ . '/workout.fit'));

print_r($response);
```

License
-------

MIT

Copyright (c) 2014 Vojtěch Kohout (aka Tharos)