<?php

			function issetor(&$var, $default = null) {
				return isset($var) ? $var : $default;
		}


		function gen_dropdown($table) {
			//generate a drobbox from a table
			global $conn;
			$sql = "SELECT ID, display FROM `" . $table . "`";
			$result = $conn->query($sql);
      $output = "";
			while($row = mysqli_fetch_array($result)){

			$output .= "<option Value='" . $row[0] . "'>" . $row[1] . "</option>";
			}

			echo $output;

		}

?>