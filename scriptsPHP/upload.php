<?php


    // //Create Upload Dir
    // $dir = 'uploadZ';
    // // create new directory with 744 permissions if it does not exist yet
    // // owner will be the user/group the PHP script is run under
    // if ( !file_exists($dir) ) 
    // {
    //     if (mkdir ($dir, 0777))
    //         echo 'dir created ';
    //     else 
    //         echo 'error creating dir ';
    // }

    // if (file_put_contents ($dir.'/test.txt', 'Hello File'))
    // {
    //     echo ' test file written ';
    // }








    if ( 0 < $_FILES['file']['error'] ) {
        echo 'Error: ' . $_FILES['file']['error'] ;
    }
    else {

        if (is_uploaded_file($_FILES['file']['tmp_name']))
            echo 'file Uploaded! ';

        if (move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']))
            echo "file Moved successfully ".$_FILES['file']['name'];
        else 
            echo "File not moved! ".$_FILES['file']['name'];
    }

    $newName = $_POST['newName'];
    $newDescription = $_POST['newDescription'];
    $newLandingUrl = $_POST['newLandingUrl'];
    $newHotspots = $_POST['newHotspots'];

    echo " ".$newName." ".$newDescription." ".$newLandingUrl." ".$newHotspots;

?>