<?php
/**
 * @author Giorgio Oliva
 */
?>
<!DOCTYPE html>
<html>
<head>
	<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:600,400,300' rel='stylesheet' type='text/css'>
</head>
<body>
<div>
	<h1>Upload File csv</h1>
	<form action="Upload_Confiscated_Object.php" method="post" enctype="multipart/form-data">
		Seleziona Il file CSV del comune da Parserizzare
		<input type="file" name="fileToUpload" id="fileToUpload"><br/>
		<input type="submit" value="Carica CSV" name="submit">
	</form>
</div>
</body>
</html>
