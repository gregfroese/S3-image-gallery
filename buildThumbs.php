<?php
require_once("lib/S3/S3.php");
require_once("config.php");

$s3 = new s3gallery();
//$s3->test();
$s3->buildThumbs();

class s3gallery {

	public function db_connect() {
		$conn = mysql_connect(config::$dbhost, config::$dbuser, config::$dbpass);
		mysql_select_db(config::$dbname, $conn);
		return $conn;
	}

	/**
	 * this is where the magic starts
	 */
	public function buildThumbs() {
		$db = $this->db_connect();

//		$file = "IMG_04773312889087373588760.jpg";
//		$exif = exif_read_data($file);
//		$o = $exif["Orientation"];
//		echo "orientation: ($o)\n";
//		$this->createThumb($file,0);
//		die;

		$s3 = new S3(config::$awsAccessKey, config::$awsSecretKey);
		
		//get all images that don't have thumbnails from the local db
		$sql = "SELECT i.* FROM " . config::$imagesTable . " as i
					INNER JOIN " . config::$dirsTable . " as d ON d.id = i.dir_id
					LEFT JOIN " . config::$thumbsTable . " as t ON i.id = t.image_id
					WHERE t.id is null";
		$images = $this->get_rows($sql);
		$count = 0;
		foreach($images as $image) {
			$extension = substr($image['name'],-3);
			if(in_array($extension, config::$valid_extensions)) { //don't use non-images, specifically sub directories
				$count++;
				echo "$count: $image[name]\n";
				//get the image file from s3
				$pathinfo = pathinfo($image["name"]);
//				var_dump($pathinfo);
				$savefile = "thumbs/".$image["name"];
				//double check to make sure we don't have this thumbnail already
				if(!file_exists($savefile)) {
					$saveinfo = pathinfo($savefile);
					if(!file_exists($saveinfo["dirname"])) {
						echo "Creating $saveinfo[dirname]....\n";
						mkdir($saveinfo["dirname"],0777,true);
					}
					echo "Retrieving $image[name] from ".config::$bucket."->$savefile..";
					$s3->getObject(config::$bucket, $image["name"], fopen($savefile, 'wb'));
					echo "done.\n";
				}
				//this is outside the previous if() in case you have your files locally already but no thumbs
				$this->createThumb($savefile, $image["id"]);
				if(!config::$keepOriginal) {
					echo "Deleting original file $savefile\n";
					unlink($savefile);
				}
			
				if($count>200) die;
			}				
		}
		return;
	}

	public function createThumb($image, $image_id) {
		$parts = pathinfo($image);
		$thumbFile = $parts["dirname"]."/thumb.".$parts["filename"].".".$parts["extension"];
		if(file_exists($thumbFile)) {
			echo "Thumbnail for $image already exists, updating the database only..";
			$sql = "INSERT INTO " . config::$thumbsTable . " (name, image_id, created, updated)
				VALUES ('$thumbFile', $image_id, now(), now())";
			$result = $this->insert_row($sql);
			echo "done.\n";
		} else {
			echo "Creating thumbnail for {$image} to {$thumbFile}..";
			// load image and get image size
			$img = imagecreatefromjpeg($image);
			$width = imagesx( $img );
			$height = imagesy( $img );
	
			// calculate thumbnail size
			$new_width = config::$thumbWidth;
			$new_height = floor( $height * ( config::$thumbWidth / $width ) );
		
			// create a new temporary image
			$tmp_img = imagecreatetruecolor( $new_width, $new_height );
		
			// copy and resize old image into new image 
			imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		
			//need rotation? http://www.php.net/manual/en/function.exif-read-data.php#76964
			$exif = exif_read_data($image);
			if(isset($exif['Orientation'])) {
				$ort = $exif['Orientation'];
				echo "{$ort}\n";
				switch($ort)
				{
					case 1: // nothing
					break;
		
					case 2: // horizontal flip
					break;
						
					case 3: // 180 rotate left
						echo "rotating 180 degrees..";
						$tmp_img = imagerotate($tmp_img,180,0);
					break;
				    
					case 4: // vertical flip
					break;
				
					case 5: // vertical flip + 90 rotate right
					break;
					

					case 6: // 90 rotate right
						echo "rotating 90 degrees right..";
						$tmp_img = imagerotate($tmp_img,270,0);
					break;
				
					case 7: // horizontal flip + 90 rotate right
					break;
				
					case 8:    // 90 rotate left
						echo "rotating 90 degrees left..";
						$tmp_img = imagerotate($tmp_img,90,0);
					break;
				}
			}

			// save thumbnail into a file
			imagejpeg( $tmp_img, $thumbFile );
			$sql = "INSERT INTO " . config::$thumbsTable . " (name, image_id, created, updated)
				VALUES ('$thumbFile', $image_id, now(), now())";
			$result = $this->insert_row($sql);
		
			echo "done.\n";
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
	
	public function get_rows($sql) {
		$result = mysql_query($sql);
		$rows = array();
		while($row = mysql_fetch_assoc($result)) {
			$rows[] = $row;
		}
		return $rows;
	}	

	
	/** got this from http://php.net/manual/en/function.imagerotate.php **/
	public function rotateImage ($image, $angle)
	{
		if ( ($angle < 0) || ($angle > 360) )
		{
			exit ("Error, angle passed out of range: [0,360]");
		}

		$width    = imagesx ($image);
		$height    = imagesy ($image);

		$dstImage = imagecreatetruecolor ($width, $height);

		if ( ($angle == 0) || ($angle == 360) )
		{
			// Just copy image to output:
			imagecopy ($dstImage, $image, 0, 0, 0, 0, $width, $height);
		}
		else
		{
			$centerX = floor ($width / 2);
			$centerY = floor ($height / 2);

			// Run on all pixels of the destination image and fill them:
			for ($dstImageX = 0; $dstImageX < $width; $dstImageX++)
			{
				for ($dstImageY = 0; $dstImageY < $height; $dstImageY++)
				{
					// Calculate pixel coordinate in coordinate system centered at the image center:
					$x = $dstImageX - $centerX;
					$y = $centerY - $dstImageY;

					if ( ($x == 0) && ($y == 0) )
					{
						// We are in the image center, this pixel should be copied as is:
						$srcImageX = $x;
						$srcImageY = $y;
					}
					else
					{
						$r = sqrt ($x * $x + $y * $y); // radius - absolute distance of the current point from image center

						$curAngle = asin ($y / $r); // angle of the current point [rad]

						if ($x < 0)
						{
							$curAngle = pi () - $curAngle;
						}

						$newAngle = $curAngle + $angle * pi () / 180; // new angle [rad]

						// Calculate new point coordinates (after rotation) in coordinate system at image center
						$newXRel = floor ($r * cos ($newAngle));
						$newYRel = floor ($r * sin ($newAngle));

						// Convert to image absolute coordinates
						$srcImageX = $newXRel + $centerX;
						$srcImageY = $centerY - $newYRel;
					}

					$pixelColor = imagecolorat  ($image, $srcImageX, $srcImageY); // get source pixel color

					imagesetpixel ($dstImage, $dstImageX, $dstImageY, $pixelColor); // write destination pixel
				}
			}
		}

		return $dstImage;
	}

}
