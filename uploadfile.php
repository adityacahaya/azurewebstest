<?php

require_once "vendor/autoload.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['files'])) {
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

                  // List blobs.
                  $listBlobsOptions = new ListBlobsOptions();
              }
              catch(ServiceException $e){
                  $code = $e->getCode();
                  $error_message = $e->getMessage();
                  $errors[] = $code.": ".$error_message."<br />";
              }
              catch(InvalidArgumentTypeException $e){
                  $code = $e->getCode();
                  $error_message = $e->getMessage();
                  $errors[] = $code.": ".$error_message."<br />";
              }
            }
        }
    }
}

if ($errors){
  echo json_encode(array("statusCode"=>201));
} else{
  echo json_encode(array("statusCode"=>200));
}
?>
