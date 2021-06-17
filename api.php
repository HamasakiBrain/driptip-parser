<?php
    require 'index.php';
    header('Content-Type: application/json');

    if ($_GET['page']){
        echo json_encode(run(trim($_GET['page'])));
    }
