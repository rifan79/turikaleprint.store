<?php

header("Content-Type: application/json");

require '../../config/database.php';

$province_id = isset($_GET['province_id']) ? (int)$_GET['province_id'] : 0;

if($province_id === 0){
    echo json_encode([]);
    exit;
}

$query = mysqli_query($conn,"SELECT id,name FROM cities WHERE province_id=$province_id ORDER BY name ASC");

$data=[];

while($row=mysqli_fetch_assoc($query)){
    $data[]=$row;
}

echo json_encode($data);