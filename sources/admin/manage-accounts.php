<?php
if(basename($_SERVER["PHP_SELF"]) == "manage-accounts.php"){
    die("403 - Access Forbidden");
}
if(isset($_SESSION['id'])){
	if(isset($_SESSION['admin'])){
		if(empty($_GET['action'])){
			echo "<h2 class=\"text-left\">Manage Accounts</h2><hr/>
				<table class=\"table\">
				  <thead>
					<tr>
					  <th>Username</th>
					  <th>Email</th>
					  <th>GM Level</th>
					  <th>NX</th>
					  <th>Vote Points</th>
					  <th>Status</th>
					</tr>
				  </thead>
				  <tbody>";
			$per_page = 15;
			$pages_query = $mysqli->query("SELECT id FROM `accounts`")->num_rows;
			$pages = ceil($pages_query/$per_page);
			if(isset($_GET['p']) && is_numeric($_GET['p']) && $_GET['p']>0){
				$page = $mysqli->real_escape_string($_GET['p']);
			}
			else {
				redirect("?base=admin&page=manageaccounts&p=1");
			}
			$start = ($page - 1) * $per_page;

			$query = $mysqli->query("SELECT * FROM accounts ORDER BY name ASC LIMIT $start, $per_page");
			//if (array_key_exists($colnx, $query)) {
				while ($row = $query->fetch_assoc()) {
				if($row['loggedin'] > 0) {
					$status = "<span class=\"label label-success\">Online</span>";
				}
				elseif($row['loggedin'] == 0 && $row['banned'] > 0){
					$status = "<span class=\"label label-danger\">Banned</span>";
				}
				elseif($row['loggedin'] == 0){
					$status = "<span class=\"label label-default\">Ofline</span>";
				}
				else {
					$status = "<span class=\"label label-warning\">Unknown</span>";
				}
					echo "<tr>
						<td><a href=\"?base=admin&amp;page=manageaccounts&amp;action=view&amp;user=".$row['name']."\">".$row['name']."</td>
						<td>".$row['email']."</td>
						<td>".$row['gm']."</td>
						<td>".$row[$colnx]."</td>
						<td>".$row[$colvp]."</td>
						<td>".$status."</td>
						</tr>";
				}
			//} else {
			//	echo "<div class=\"alert alert-danger\">Vote Configuration is incorrect. <a href=\"?base=admin&page=voteconfig\" class=\"alert-link\">Resolve &raquo;</a></div>";
			// 	}
			$minus1 = $page - 1;
			echo "</tbody>
			</table>
			<div class=\"text-center\">
			<ul class=\"pagination\">";
			if ($pages >=1 && $page <=$pages) {
			if($page-5 <= 0){
				$x = 1;
			}
			else {
				$x = $page-5;
			}
			  for ($x; $x<=$page+5; $x++) {
				if($x == $page){
					echo "<li class=\"active\">";
				} else{
					echo "<li>";
				}
				echo "<a href=\"?base=admin&amp;page=manageaccounts&amp;p=".$x."\">".$x."</a></li>";
			  }
			}
			echo "</ul>
			</div>";
		}
		elseif($_GET['action']=="view"){
			if(isset($_GET['user'])){
				$user = $mysqli->real_escape_string(preg_replace("/[^A-Za-z0-9 ]/", '', $_GET['user']));
				$count = $mysqli->query("SELECT * FROM accounts WHERE name = '".$user."'");
				if($count->num_rows == 1) {
					$row = $count->fetch_assoc();
					if($row['loggedin'] > 0) {
						$status = "<span class=\"label label-success\">Online</span>";
					}
					elseif($row['loggedin'] == 0 && $row['banned'] > 0){
						$status = "<span class=\"label label-danger\">Banned</span>";
					}
					elseif($row['loggedin'] == 0){
						$status = "<span class=\"label label-default\">Ofline</span>";
					}
					else {
						$status = "<span class=\"label label-warning\">Unknown</span>";
					}
					
					if($row['webadmin'] == 1) {
						$webchecked = "checked";
					} else {
						$webchecked = "";
					}
					if($row['mute'] > 0) {
						$mutechecked = "checked";
					} else {
						$mutechecked = "";
					}
					echo "<h2 class=\"text-left\">Viewing ".$user."</h2><hr/>";
					if(!isset($_POST['submit'])) {
						echo "<form role=\"form\" method=\"POST\">
							<div class=\"form-group\">
								<label for=\"username\">Username:</label>
								".$row['name']."
							</div>
							<div class=\"form-group\">
								<label for=\"inputEmail\">Email:</label>
								 <input type=\"email\" name=\"email\" class=\"form-control\" id=\"inputEmail\" value=\"".$row['email']."\" placeholder=\"Email\"\">
							</div>
							<div class=\"form-group\">
								<label for=\"password\">New Password:</label><small>&nbsp;Leave empty to keep the old password</small>
								 <input type=\"password\" name=\"password\" class=\"form-control\" id=\"password\" placeholder=\"Password\">
							</div>
							<div class=\"form-group\">
								<label for=\"inputNX\">NX Amount:</label>
								 <input type=\"text\" name=\"nx\" class=\"form-control\" id=\"inputNX\" placeholder=\"NX Amount\" value=\"".$row[$colnx]."\">
							</div>
							<div class=\"form-group\">
								<label for=\"inputVP\">VP Amount:</label>
								 <input type=\"text\" name=\"vp\" class=\"form-control\" id=\"inputVP\" placeholder=\"VP Amount\" value=\"".$row[$colvp]."\">
							</div>
							<div class=\"form-group\">
								<label for=\"gmLevel\">GM Level:</label>
								 <input type=\"text\" name=\"gm\" class=\"form-control\" id=\"gmLevel\" placeholder=\"GM Level\" value=\"".$row['gm']."\">
							</div>
							<div class=\"form-group\">
								<div class=\"checkbox\">
									<label>
										<input type=\"checkbox\" name=\"webadmin\" ".$webchecked."> Web Administrator
									</label>
								</div>
							</div>
							<div class=\"form-group\">
								<div class=\"checkbox\">
									<label>
										<input type=\"checkbox\" name=\"mute\" ".$mutechecked."> Muted
									</label>
								</div>
							</div>
							<button class=\"btn btn-primary\" name=\"submit\" type=\"submit\">Edit User &raquo;</button>
						</form>";
					} else {
						if(empty($_POST["email"])) {
							echo "<div class=\"alert alert-danger\">You must enter an email!</div>";
							$err = 1;
						}
						else {
							$email = $mysqli->real_escape_string(strip_tags($_POST["email"]));
						}
						if(empty($_POST["nx"]) || !is_numeric($_POST["nx"])) {
							echo "<div class=\"alert alert-danger\">You must enter an integer for NX!</div>";
							$err = 1;
						}
						else {
							$nx = $mysqli->real_escape_string(strip_tags($_POST["nx"]));
						}
						if(empty($_POST["vp"]) || !is_numeric($_POST["vp"])) {
							echo "<div class=\"alert alert-danger\">You must enter an integer for Vote Points!</div>";
							$err = 1;
						}
						else {
							$vp = $mysqli->real_escape_string(strip_tags($_POST["vp"]));
						}
						if(!is_numeric($_POST["gm"])) {
							echo "<div class=\"alert alert-danger\">You must enter an integer for the GM Level</div>";
							$err = 1;
						}
						else {
							$gm = $mysqli->real_escape_string(strip_tags($_POST["gm"]));
						}
						$password = $mysqli->real_escape_string(sha1($_POST["password"]));
						if(isset($_POST['webadmin'])){
							$webadmin = 1;
						}
						else {
							$webadmin = 0;
						}
						if(isset($_POST['mute'])){
							$muted = 1;
						}
						else {
							$muted = 0;
						}
						if(isset($err)){
							echo "<hr/><button onclick=\"goBack()\" class=\"btn btn-primary\">&laquo; Go Back</button>";
						}
						if($_POST['password'] == "" && !isset($err)) {
							$mysqli->query("UPDATE accounts SET email = '".$email."', ".$colnx." = '".$nx."', ".$colvp." = '".$vp."', gm = '".$gm."', webadmin = '".$webadmin."', mute = '".$muted."' WHERE name = '".$user."'");
							echo "<div class=\"alert alert-success\">".$user." successfully edited</div>";
							redirect_wait5("?base=admin&page=manageaccounts&action=view&user=".$user."");
						}
						elseif(!isset($err)) {
							$mysqli->query("UPDATE accounts SET password = '".$password."', email = '".$email."', ".$colnx." = '".$nx."', ".$colvp." = '".$vp."', gm = '".$gm."', webadmin = '".$webadmin."', mute = '".$muted."' WHERE name = '".$user."'");
							echo "<div class=\"alert alert-success\">".$user." successfully edited</div>";
							redirect_wait5("?base=admin&page=manageaccounts&action=view&user=".$user."");
						}					
					}
				}
				else {
					echo "
					<h2 class=\"text-left\">Error</h2><hr/>
					<div class=\"alert alert-danger\">This user doesn't exist!</div>";
					redirect_wait5("?base=admin&page=manageaccounts");
				}
			}
		} else {
			redirect("?base=admin");
		}
	}
}else{
	redirect("?base=main");
}
?>