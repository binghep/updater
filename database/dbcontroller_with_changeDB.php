<?php
//echo 'in the file';
class DBController {
	public $conn;
	function __construct() {
		$this->conn = $this->connectDB();
		if(!empty($this->conn)) {
			$this->selectDB($this->conn);
		}
	}
	
	function connectDB() {
		require 'database.php';
		//$conn = mysqli_connect($this->host,$this->user,$this->password);
		$conn=mysqli_connect($servername,$username,$password);
		
		return $conn;
	}
	
	function selectDB($conn) {
		require 'database.php';
		mysqli_select_db($this->conn, $db);
		// mysqli_query( $conn, 'SET NAMES "utf8" COLLATE "utf8_general_ci"' );
	}
	
	function changeDB($db) {
		mysqli_select_db($this->conn, $db);
		// mysqli_query( $conn, 'SET NAMES "utf8" COLLATE "utf8_general_ci"' );
	}
	
	function runQuery($query) {
				//if (empty($this->conn)) return 'error';
		$result = mysqli_query($this->conn,$query);//Returns FALSE on failure. Returns mysqli_result object. Returns true for other successful queries.
		if (is_bool($result)) return $result;//1. mysqli_fetch_assoc can't accept true or false 
		//result is a mysqli_result object:
		$resultset=null;
		while($row=mysqli_fetch_assoc($result)) {
			$resultset[] = $row;
		}
		//var_dump($resultset);
		//if(!empty($resultset)){
		//	return $resultset;
		//}else{
			//echo '"select" query success, but resultset is empty';
			//return 'empty resultset';
		//}
		return $resultset; //it is null or an array indexed with 0  1... .
	}

	/*
	reference: 
	//		$sql="SELECT Lastname,Age FROM Persons ORDER BY Lastname";
//		$result=mysqli_query($con,$sql);
//
//		// Associative array
//		$row=mysqli_fetch_assoc($result);
//		printf ("%s (%s)\n",$row["Lastname"],$row["Age"]);
//
//		// Free result set
//		mysqli_free_result($result);
	*/
	
	function numRows($query) {
		$result  = mysqli_query($this->conn,$query);
		$rowcount = mysqli_num_rows($result);
		return $rowcount;	
	}

	function getError(){
		return mysqli_error($this->conn);
	}

	function real_escape($user_input){
		
		return mysqli_real_escape_string($this->conn, $user_input);
	}

	function setCharSet(){
		printf("Initial character set: %s\n", mysqli_character_set_name($this->conn));

		/* change character set to utf8 */
		if (!mysqli_set_charset($this->conn, "utf8")) {
		    printf("Error loading character set utf8: %s\n", mysqli_error($this->conn));
		    exit();
		} else {
		    printf("Current character set: %s\n", mysqli_character_set_name($this->conn));
		}
	}
}
?>