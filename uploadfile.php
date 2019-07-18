<?php

require_once "vendor/autoload.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

  $sql="select * from account where email = '".$email."'";
  $result = $conn->query($sql);
  $canuploadfile = false;
  if ($result->num_rows > 0) {
    $canuploadfile = true;
  }

  if (isset($_FILES['files']) && $canuploadfile) {
    $errors = [];
    $path = 'send_to_storage/';
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'txt', 'doc', 'docx'];

    $all_files = count($_FILES['files']['tmp_name']);

    for ($i = 0; $i < $all_files; $i++) {
      $file_name = $_FILES['files']['name'][$i];
      $file_tmp = $_FILES['files']['tmp_name'][$i];
      $file_type = $_FILES['files']['type'][$i];
      $file_size = $_FILES['files']['size'][$i];
      $file_ext = strtolower(end(explode('.', $file_name)));

      $fileToUpload = $file_name;
      $content = file_get_contents($_FILES['files']["tmp_name"][$i]);

      $file = $path . $file_name;

      if (!in_array($file_ext, $extensions)) {
        $errors[] = 'Extension not allowed: ' . $file_name . ' ' . $file_type;
      }else if ($file_size > 2097152) {
        $errors[] = 'File size exceeds limit: ' . $file_name . ' ' . $file_type;
      }else{
        try {
          // set container
          $containerName = "azurewebtest";
          $connectionString = "DefaultEndpointsProtocol=https;AccountName=azurewebstest;AccountKey=YtrIKIOVXYNt2Yp2tJ1MNPeNz0CZgzM+SFST/D7Bv2yNw3DqEmty5jN1T9YwdO+sr+7s6h4tWXbByuhviQdjOQ==;EndpointSuffix=core.windows.net";
          $blobClient = BlobRestProxy::createBlobService($connectionString);

          //Upload blob
          $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

          $blob = $blobClient->getBlob($containerName, $fileToUpload);

          // List blobs.
          $listBlobsOptions = new ListBlobsOptions();
          $listBlobsOptions->setPrefix($fileToUpload);
          $bloburl = "";
          do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
              $bloburl = $blob->getUrl();
            }

            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
          } while($result->getContinuationToken());

          $sql="insert into fileupload(email,linkfile) values ('".$email."','".$bloburl."')";

          if (!mysqli_query($conn,$sql)) {
            $errors[] = "problem when insert to db";
          }
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
      }
    }
  }else{
    $errors[] = "user not not register yet";
  }
}

if ($errors){
  echo json_encode(array("statusCode"=>201,"errormessage"=>$errors));
} else{
  echo json_encode(array("statusCode"=>200,"errormessage"=>$errors));
}
?>
