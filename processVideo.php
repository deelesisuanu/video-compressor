<?php

require "define.php";

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

            // check the specified starting time
            if(isset($_POST["start_from"]) && $_POST["start_from"] != ""){
                $start_from = $_POST["start_from"];
            }

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
        
    }
    else {
        echo "<h3>No file was uploaded!</h3>";
    }

}


?>