<?php
class config {
        public static $dbhost = "localhost";
        public static $dbname = "s3gallery";
        public static $dbuser = "s3";
        public static $dbpass = "gallery";
        public static $imagesTable = "images";
        public static $thumbsTable = "thumbs";
        public static $dirsTable = "dirs";
        public static $bucket = "your_bucket_name";
        public static $awsAccessKey = "replace me";
        public static $awsSecretKey = "replace me";
		public static $rootParentID = 1; //change this if you want/need to adjust the root of your directory structure
		public static $imagesPerRow = 6;
		public static $awlURL = "http://whatever"; //change this to the CNAME you have setup for your AWS bucket
		public static $valid_extensions = array("jpg", "JPG", "jpeg", "JPEG", "CR2", "cr2", "nef", "NEF");
		public static $thumbWidth = 200;
		public static $keepOriginal = true; //keep the original image locally after downloading from s3
		public static $useLocalThumbs = true; //use thumbs on local system, if this is false, it looks on s3 for the thumbs (s3 thumbs not yet implemented)
}
