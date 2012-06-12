Façade
======

Facade is an [open-source][1] library for memory-effecient consumption of stream-based protocols like HTTP.

Included is a streaming HTTP client and a streaming [AWS S3 client][2]. 99designs uses these to stream large designs
from AWS to upstream clients without buffering the entire file in memory or on disk at any one time. 

Examples
========

Streaming a file from disk to S3:

```php
<?php

$s3 = new Facade_S3(getenv('AWS_ACCESS_KEY_ID'), getenv('AWS_SECRET_ACCESS_KEY'));
$file = '/uploads/largeimage.jpg';

$response = $s3
	->put("/llamas/largeimage.jpg")
	->setStream(Facade_Stream::fromFile($file))
	->setContentType('image/jpeg')
	->setHeader('Content-MD5: '.base64_encode(md5_file($file, true)))
	->send();

```

Streaming an S3 file to a client:

```php
<?php

$s3 = new Facade_S3(getenv('AWS_ACCESS_KEY_ID'), getenv('AWS_SECRET_ACCESS_KEY'));

$response = $s3
  ->get('/llamas/largeimage.jpg')
  ->send();

stream_copy_to_stream($response->getStream(), STDOUT);

```

  [1]: http://www.opensource.org/licenses/mit-license.php
  [3]: http://aws.amazon.com/s3/
