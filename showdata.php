<?php

require_once "vendor/autoload.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

// //------insert.php------ PRODUCTION
// $servername = "azurewebstest3.mysql.database.azure.com";
// $username = "adityacahaya@azurewebstest3";
// $password = "040194aditya!";
// $dbname = "azurewebstest";

//------insert.php------ DEVELOPMENT
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "azurewebstest";

$conn = mysqli_connect($servername, $username, $password, $dbname);
$email=$_POST['email'];

$sql="select * from fileupload where email = '".$email."'";
$result = $conn->query($sql);
$canuploadfile = false;
$resultdata = array();
$errors = [];
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $data=array("email"=>$row["email"],"linkfile"=>$row["linkfile"]);
    array_push($resultdata,$data);
  }
}else{
  $errors[] = "user not not register yet";
}

if ($errors){
  echo json_encode(array("statusCode"=>201,"errormessage"=>$errors));
}else{
  echo json_encode($resultdata);
}
?>
