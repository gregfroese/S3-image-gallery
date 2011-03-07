<?php
class config {
        public static $dbhost = "localhost";
        public static $dbname = "s3gallery";
        public static $dbuser = "s3";
        public static $dbpass = "gallery";
        public static $imagesTable = "images";
        public static $dirsTable = "dirs";
        public static $bucket = "your_bucket_name";
        public static $awsAccessKey = "replace me";
        public static $awsSecretKey = "replace me";
	public static $rootParentID = 1; //change this if you want/need to adjust the root of your directory structure
	public static $imagesPerRow = 6;
	public static $awlURL = "http://whatever"; //change this to the CNAME you have setup for your AWS bucket
}
