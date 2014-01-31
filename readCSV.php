<?php

    // open csf file in read mode
    $csvFile = fopen("ontario_charities_list.csv", "r");

    $charity_names = array(); // array of possible charity names to look up
    
    // process file and only read 2nd column
    while(($row = fgetcsv($csvFile)) !== FALSE) {
        array_push($charity_names, $row[1]); // push found name from csv to array
    }
    
    fclose($csvFile); // done with the file

?>