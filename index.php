<?php
require_once("config.php");
require_once("lib/S3/S3.php");

$s3 = new s3gallery();
$s3->index();

class s3gallery {

	public function db_connect() {
		//setup the database
		$conn = mysql_connect(config::$dbhost, config::$dbuser, config::$dbpass);
		mysql_select_db(config::$dbname, $conn);
		return $conn;
	}

	public function index() {
		$this->loadHeader();
		$this->db_connect();
		$level = $this->get_param("parent_id", config::$rootParentID, $_GET);
		$this->list_dirs($level);
		$this->show_images($level);
	}

	public function get_param($key, $default, $array) {
		if(isset($array[$key])) {
			return $array[$key];
		} else {
			return $default;
		}
	}

	public function show_images($dir_id) {
		?><div id="maincol"><?php
			//get all images with thumbnails
			$sql = "SELECT i.*, t.name as thumb FROM " . config::$thumbsTable . " as t
					INNER JOIN " . config::$imagesTable . " as i ON i.id = t.image_id
					WHERE i.dir_id = $dir_id
					ORDER BY i.name ASC";
			$thumbs = $this->get_rows($sql);
			echo "found ".count($thumbs)." pics with thumbnails<br />";
//			$sql = "SELECT * FROM " . config::$imagesTable . " WHERE dir_id = $dir_id ORDER BY name ASC";
			$sql = "SELECT i.* as thumb FROM " . config::$imagesTable . " as i
					LEFT JOIN " . config::$thumbsTable . " as t ON i.id = t.image_id
					WHERE t.id is null AND i.dir_id = $dir_id";
//			echo "sql: $sql<br />";
			$rows = $this->get_rows($sql);
			if($rows) {
				echo "There are ".count($rows)." images in this directory without thumbnails.  Please run 'php buildThumbs.php'<br />";
			}
			$count = 0;
			$imagesPerRow = config::$imagesPerRow;
			?><table id="imageTable"><?php
				foreach($thumbs as $row) {
					$extension = substr($row['name'],-3);
					if(in_array($extension, config::$valid_extensions)) { //don't show non-images, specifically sub directories
						if($count % $imagesPerRow == 0) {
							if($count > 0) {
								?></tr><tr><?php
							} else {
								?><tr><?php
							}
						}
						$count++;
						if(config::$useLocalThumbs) {
							$thumbPath = "";
						} else {
							$thumbPath = config::$awsURL;
						}
						?><td><a href="<?=config::$awsURL.$row["name"]?>">
							<img src="<?=$thumbPath.$row["thumb"]?>">
						</a></td><?php
					}
				}
			?></tr></table></div><?php
	}

	public function list_dirs($level) {
		$dirs = $this->get_dirs($level);
?>
		<div id="leftcol">
			<ul class="menu">
				<li><a href="/index.php">Home</a></li>
<?php
			foreach($dirs as $dir) {
				?><li>
					<a href="/index.php?parent_id=<?=$dir["id"]?>"><?=$dir["dirname"]?></a></li>
				<?php
			}
			?></ul>
		</div><?php
	}

	public function get_dirs($level) {
		$sql = "SELECT * FROM " . config::$dirsTable . " WHERE parent_id = $level";
		$results = $this->get_rows($sql);
		return $results;
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

	public function get_rows($sql) {
		$result = mysql_query($sql);
		$rows = array();
		while($row = mysql_fetch_assoc($result)) {
			$rows[] = $row;
		}
		return $rows;
	}

	public function loadHeader() {
		?>
			<head>
				<title>S3Gallery</title>
				<link href="s3gallery.css" rel="stylesheet" type="text/css" />
			</head>
		<?php
	}
}
