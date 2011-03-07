Author: Greg Froese
Email: greg.froese at gmail.com

Simple script to build a database of all the files in an Amazon S3 bucket.
Intentions are to build a basic image gallery that uses S3 as its primary (and only) source of images including thumbnails.

Installation:
I have included Donovan Schönknecht's PHP S3 class as part of this project.
Please see the README.txt in lib/S3 for the requierments to use this class.
Visit his site here: http://undesigned.org.za/

Usage:
Create a database with the schema in schema.sql.
Copy config.inc.php to config.php and alter the values accordingly.

Scanning your bucket:
This step is necessary to run each time you add files to your S3 bucket.
It will create records in the database so the structure and filenames can be replciated for the gallery without having any local image files and without the need to get directory listings remotely.
This is a command line tool, so it's simply:
php scan.php

TODO:
1. Optionally compare the hash of each image to identify changed files on S3 and update the database accordingly
2. Have the scan.php generate thumbnails
3. Create the actual image gallery views
