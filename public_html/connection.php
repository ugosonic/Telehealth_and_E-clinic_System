<?php
$database= new mysqli("localhost","u398331630_myclinic","kingsley55A","u398331630_administrator");
    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }
    ?>