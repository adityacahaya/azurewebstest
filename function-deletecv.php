<?php

require_once "vendor/autoload.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

// //------insert.php------ PRODUCTION
$servername = "azurewebstest3.mysql.database.azure.com";
$username = "adityacahaya@azurewebstest3";
$password = "040194aditya!";
$dbname = "azurewebstest";

//------insert.php------ DEVELOPMENT
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "azurewebstest";

$conn = mysqli_connect($servername, $username, $password, $dbname);
$email=$_POST['email'];
$linkfile=$_POST['linkfile'];

$sql="delete from fileupload where email = '".$email."' and linkfile = '".$linkfile."'";
$canuploadfile = false;
$errors = [];
if (mysqli_query($conn,$sql)) {
  try {
    $link_array = explode('/',$linkfile);
    $file_name = end($link_array);
    $fileToUpload = $file_name;
    // set container
    $containerName = "azurewebtest";
    $connectionString = "DefaultEndpointsProtocol=https;AccountName=azurewebstest;AccountKey=YtrIKIOVXYNt2Yp2tJ1MNPeNz0CZgzM+SFST/D7Bv2yNw3DqEmty5jN1T9YwdO+sr+7s6h4tWXbByuhviQdjOQ==;EndpointSuffix=core.windows.net";
    $blobClient = BlobRestProxy::createBlobService($connectionString);
    // List blobs.
    $listBlobsOptions = new ListBlobsOptions();
    $listBlobsOptions->setPrefix($fileToUpload);
    $bloburl = "";
    do{
      $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
      foreach ($result->getBlobs() as $blob)
      {
        $bloburl = $blob->getUrl();
        $blobname = $blob->getName();
        if($linkfile == $bloburl){
          $blobClient->deleteBlob($containerName, $blobname);
        }
      }
      $listBlobsOptions->setContinuationToken($result->getContinuationToken());
    } while($result->getContinuationToken());
  }
  catch(ServiceException $e){
    $code = $e->getCode();
    $error_message = $e->getMessage();
    $errors[] = $code.": ".$error_message."";
  }
  catch(InvalidArgumentTypeException $e){
    $code = $e->getCode();
    $error_message = $e->getMessage();
    $errors[] = $code.": ".$error_message."";
  }
  catch(Exception $e){
    $code = $e->getCode();
    $error_message = $e->getMessage();
    $errors[] = $code.": ".$error_message."";
  }
}else{
  $errors[] = "user not not register yet";
}

if ($errors){
  echo json_encode(array("statusCode"=>201,"errormessage"=>$errors));
}else{
  echo json_encode(array("statusCode"=>200,"errormessage"=>$errors));
}
?>
