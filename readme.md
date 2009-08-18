Facade
======

Facade is an [open-source][1] library for abstracting access to a variety of file storage mechanisms
in [PHP5][2]. Abstraction allows for composition of pluggable virtual filesystems and transparent
caching.

The backends supported will initially be:

* [Amazon S3][3]
* Filesystem

Other backends which will follow:

* [Memcache][4]
* FTP/SFTP

  [1]: http://www.opensource.org/licenses/mit-license.php
  [2]: http://www.php.net/
  [3]: http://aws.amazon.com/s3/
  [4]: http://www.danga.com/memcached/
