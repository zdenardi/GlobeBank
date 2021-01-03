<?php
//This guide demonstrates the 5 steps of database interaction with php

$dbhost = 'localhost';
$dbuser = 'webuser';
$dbpass = 'secretpassword';
$dbname ='globe_bank';
// 1. Create a database connection
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

//Test to see if connection is OK
if(mysqli_connect_error()){
    $msg = "DB connection failed : ";
    $msg .= mysqli_connect_error();
    $msg .="( ".mysqli_connect_errno(). ")";
    exit($msg);
}
// 2. Perform Query
$query="SELECT * FROM subjects";
$result_set = mysqli_query($connection, $query);

//check to see if query is OK

if(!result_set){
    echo "Database Query Failed";
}

// 3. Use returned data is avail
while($subject = mysql_fetch_assoc($result_set)){
    echo $subject["menu_name"] . "<br />";
}
// 4. Release Data
$mysqli_free_result($result_set);

// 5. Close DB connection
mysqli_close($connection);
