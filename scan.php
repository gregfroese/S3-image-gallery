<?php
require_once("lib/S3/S3.php");
require_once("config.php");

$s3 = new s3gallery();
//$s3->test();
$s3->scan();

class s3gallery {

	public function db_connect() {
		$conn = mysql_connect(config::$dbhost, config::$dbuser, config::$dbpass);
		mysql_select_db(config::$dbname, $conn);
		return $conn;
	}

	/**
	 * this is where the magic starts
	 */
	public function scan() {
		$db = $this->db_connect();
		if(!class_exists('S3')) {
			require_once('S3.php');
		}
		   
		//instantiate the class  
		$s3 = new S3(config::$awsAccessKey, config::$awsSecretKey);
		$bucket_contents = $s3->getBucket(config::$bucket);
		$count = 0;

		foreach( $bucket_contents as $file) {
			$count++;
			$name = $file["name"];
			$size = $file["size"];
			$time = $file["time"];
			$hash = $file["hash"];

			//check if we have this in the db already
			$sql = "SELECT * FROM " . config::$imagesTable . " WHERE name='$name'";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			echo "$count: $name -> ";
			if(!$row) {
				//find the directory this lives in
				$dir_id = $this->find_dir($name);
				//we don't have info on this image, let's add it
				$sql = "INSERT INTO " . config::$imagesTable . " (name, size, time, hash, dir_id, created, updated)
					VALUES ('$name', $size, $time, '$hash', $dir_id, now(), now())";
				$result = $this->insert_row($sql);
				if($result) {
					echo "added a record\n";
				} else {
					echo "error adding a record\n";
				}
			} else {
				echo "have this one already, added on $row[created]\n";
			}
		}
	}

	public function find_dir($filename, $build_hierarchy = TRUE) {
		$dirname = dirname($filename);
		if(in_array($dirname, array("/","."))) {
			//don't want that - get out of here
			return 0;
		}
		echo "$filename dirname: $dirname\n";
		if($build_hierarchy) {
			$this->build_dir_hierarchy($dirname);
		}
		$sql = "SELECT * FROM " . config::$dirsTable . " WHERE dirname = '$dirname'";
		$row = $this->get_row($sql);
		if(!$row) {
			echo "no row found for ($dirname): something went wrong - dieing\n"; die;
		} else {
			return $row["id"];
		}
	}

	/**
	 * make sure we have records for every level in the hierarchy for these directories
	 */
	public function build_dir_hierarchy($dirname) {
		$parts = explode("/", $dirname);
		$path = "";
		$prevPath = "";
		foreach($parts as $part) {
			if($path == "") {
				$path = $part;
			} else {
				$path .= "/$part";
			}
			//create it if it doesn't exist
			$sql = "SELECT * FROM " . config::$dirsTable . " WHERE dirname = '$path'";
			$row = $this->get_row($sql);
			if(!$row) {
				//get the id for the parent
				$parent_id = $this->find_dir($path, FALSE);
				$sql = "INSERT INTO " . config::$dirsTable . " (bucket, dirname, parent_id, created, updated) VALUES ('" . config::$bucket. "', '$path', $parent_id, now(), now())";
				$this->insert_row($sql);
			}
			$prevPath = $path;
		}
	}

	public function insert_row($sql) {
		mysql_query($sql);
		return mysql_insert_id();
	}

	public function get_row($sql) {
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		return $row;
	}

	public function test() {
		$db = $this->db_connect();
		$files = array("pictures/1980","pictures/1980/01","pictures/1980/02","pictures/2003","pictures/2003/01","pictures/2003/01/23/35","pictures/2004/12/picnic/12.jpg");
		foreach($files as $file) {
			$result = $this->find_dir($file);
		}
	}


}
