<?php
	if(isset($_POST['token'])) {

		// $id=$_POST['id'];
		$token=$_POST['token'];
		$nik=$_POST['nik'];
		$role=$_POST['role'];
		$cdate = gmdate('Y-m-d h:i:s \G\M\T', time());
		include("connect.php");

		$status_query = "SELECT * FROM token WHERE nik='$nik'";
		$result_query = mysqli_query($con, $status_query);
		$count = mysqli_num_rows($result_query);
		if($count==1){
			$update_query = "UPDATE token SET token = '$token', created_at = '$cdate' WHERE nik='$nik'";
			$sql=mysqli_query($con, $update_query);
			if($sql){
				echo 'Token Updated..';
			}
			else{
				echo 'Failed to update token..';
			}
		}
		else{
			$query = "INSERT INTO token(nik, token, created_at,role)VALUES ( '$nik', '$token', '$cdate', '$role')";
			$sql=mysqli_query($con, $query);
			if($sql){
				echo 'Token Saved..';
			}
			else{
				echo mysqli_error($con);
			}
		}



	}

 ?>
