<?php

include'../includes/connection.php';

	if (!isset($_GET['do']) || $_GET['do'] != 1) {
						
    	switch ($_GET['type']) {
    		case 'user':
    			$query = 'DELETE FROM users WHERE ID = ' . $_GET['id'];
    			$result = mysqli_query($db, $query) or die(mysqli_error($db));				
            ?>
    			<script type="text/javascript">alert("Usuario eliminado con éxito.");window.location = "user.php";</script>					
            <?php
    			//break;
            }
	}
?>