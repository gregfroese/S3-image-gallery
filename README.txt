Author: Greg Froese
Email: greg.froese at gmail.com

Simple script to build a database of all the files in an Amazon S3 bucket.
Intentions are to build a basic image gallery that uses S3 as its primary (and only) source of images including thumbnails.

Installation:
I have included Donovan Schönknecht's PHP S3 class as part of this project.
Please see the README.txt in lib/S3 for the requierments to use this class.
Visit his site here: http://undesigned.org.za/

IMPORTANT:
You must make sure you have your AWS S3 security setup in a way that your image files are viewable from the web.
Your best bet is probably a bucket policy - see here for more info: http://docs.amazonwebservices.com/AmazonS3/latest/dev/index.html?AccessPolicyLanguage_UseCases_s3_a.html

Usage:
Create a database with the schema in schema.sql.
Copy config.inc.php to config.php and alter the values accordingly.

Scanning your bucket:
This step is necessary to run each time you add files to your S3 bucket.
It will create records in the database so the structure and filenames can be replciated for the gallery without having any local image files and without the need to get directory listings remotely.
This is a command line tool, so it's simply:
php scan.php

Generating thumbnails:
This is a command line task as well as it could take a long time to run.
php buildThumbs.php
This will go through all the images in your database that don't have a corresonding thumbnail record and check for the existance of a thumbnail image (currently locally only). If none is found, it will create one and add a record to the database.  If one is found but there is no record in the database, a record is added.

TODO:
1. Optionally compare the hash of each image to identify changed files on S3 and update the database accordingly
2. Generate thumbnails
	a. DONE: generate thumbnails by finding files in the database and retrieving them to generate the thumbnail
	b. Optionally push the thumbnails back up to S3
3. Create the actual image gallery views
	a. Optionally use local thumbnails and images for all links

