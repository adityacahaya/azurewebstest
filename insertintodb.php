<?php
//------insert.php------
$servername = "azurewebstest3.mysql.database.azure.com";
$username = "adityacahaya@azurewebstest3";
$password = "040194aditya!";
$dbname = "azurewebstest";

$conn = mysqli_connect($servername, $username, $password, $dbname);
$name=$_POST['name'];
$pass=$_POST['password'];
$email=$_POST['email'];
$sql="insert into account(name,password,email) values ('".$name."','".$pass."','".$email."')";

if (mysqli_query($conn,$sql)) {
	echo json_encode(array("statusCode"=>200));
}else {
	echo json_encode(array("statusCode"=>201));
}

?>
