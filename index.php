<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <title>Sample video editing</title>
 <meta name="keyword" content="video to gif, video shortener">
 <meta name="description" content="Convert video to gif or cut it out to shorter length">
 <link rel="stylesheet" type="text/css" href="style.css">

</head>
<body>

<div id="main-area">
	<div id="header">
		<h1>Upload sample video to edit</h1>
		<p>Make sure that your uploaded file name is not containing any special characters and or white spaces!</p>
	</div>

		<form method="post" action="" enctype="multipart/form-data">
			<div id="form-contents">

				<label for="video_file">Video to Edit:</label> </br>
				<input id="video_file" type="file" name="user_video" value=""/></br>
				
				<label for="extension">Convert to:</label></br>
				<select name="extension" id="extension">
					<option value="none">Default</option>
					<option value="gif">gif</option>
					<option value="mp4">mp4</option>
				</select></br>
				<label for="start_from">Start From:</label></br>
				<input type="text" name="start_from" id="start_from" value="" placeholder="example: 00:02:21"/>
				</br>
				<label for="length">Length:</label></br>
				<input type="text" name="length" id="length" value="" placeholder="example: 10"/> seconds
				</br>
				<input type="submit" name="submit" value="Edit">
			</div>				
		</form>
        <?php	
	$input_dir = dirname(__FILE__). "/input/";
	$output_dir = dirname(__FILE__). "/output/";
	
	if(isset($_POST["submit"])) {		
		if(file_exists($_FILES["user_video"]["tmp_name"])){
			$temp_file = $_FILES["user_video"]["tmp_name"];
			
			$fileType = mime_content_type($temp_file);		
			if(!preg_match('/video\/*/', $fileType)) {	
				echo "Please upload a video";
				return;
			}
			
			// file name with extension
			$file = $_FILES["user_video"]["name"];	
			
			// name without extension
			$filename = pathinfo($file, PATHINFO_FILENAME);
			
			// Default extension
			$default = pathinfo($file, PATHINFO_EXTENSION);
			
			// create special string from date to ensure filename is unique
			$date = date("Y-m-d H:i:s");
			$uploadtime = strtotime($date);
			
			// upload path
			$video_file = $input_dir . "/" . $uploadtime ."_". $file;
			
			// check the specified extension
			if(!isset($_POST["extension"]) || $_POST["extension"] == ""){
				echo "Please set the output extension.";
				return;
			}
			$ext = $_POST["extension"]; // output extension	
			if($ext == "none") {
				$ext = $default;
			}			
			
			// put file to input directory to make it easier to be processed with ffmpeg
			$moved = move_uploaded_file($temp_file, $video_file);
			if($moved) {
				// change php working directory to where ffmpeg binary file reside
				chdir("binaries");
				
				$start_from = "00:00:00";				
				// check the specified starting time
				if(isset($_POST["start_from"]) && $_POST["start_from"] != ""){
					$start_from = $_POST["start_from"];
				}				
				
				$length = 10;
				// check the specified duration
				if(isset($_POST["length"]) && $_POST["length"] != ""){
					$length = $_POST["length"];
				}
				
				$output = "$output_dir/$uploadtime"."_$filename.$ext";
				$process = exec("ffmpeg -t $length -ss $start_from -i $video_file -b:v 2048k $output 2>&1", $result);				
				
				// delete uploaded file from input folder to reserve disk space
				unlink($video_file);
				
				echo "<span>Edit Finished:</span>";
				
				echo "<a href='http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"."output/$uploadtime"."_$filename.$ext'>Download</a>";
			}
			
		} else {
			echo "<h3>No file was uploaded!</h3>";
		}
	}
?>
</div>
</body>
</html>