<?php
/**
 * @author Giorgio Oliva
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

require("ParserCSV.php");

   if(isset($_FILES['fileToUpload'])){
      $errors= array();
      $file_name = $_FILES['fileToUpload']['name'];
      $file_size =$_FILES['fileToUpload']['size'];
      $file_tmp =$_FILES['fileToUpload']['tmp_name'];
      $file_type=$_FILES['fileToUpload']['type'];
      $num1;
      $file_name_array=explode('.',$file_name,-1);
      $convert_file_name=$file_name_array[0];
      set_include_path(get_include_path() . PATH_SEPARATOR . $path);
      $expensions= array('text/plain','text/csv','text/tsv','text/xls');
      if(in_array($file_type,$expensions)== false){
         $errors[]="Estensione non permessa, si prega di selezionare un file CSV corretto";
      }
      
      if($file_size > 2097152){
         $errors[]='File size must be excately 2 MB';
      }
      
	if(empty($errors)==true){
		move_uploaded_file($file_tmp,"tmp/".$file_name);
	?> 
<html>
<head>
	<title>Parserizzato</title>
</head>
<body>
	
	<table>
		<thead>
			<tr>
				<td>
					<div id="intestazione">
						<h1><?php echo "Parserizzo il File CSV : ".$convert_file_name; ?></h1>
					</div>
				</td>
			</tr>
		</thead>
		<tr>
			<td>
				<div class="right">Parserizzato il : <?php echo date("d/m/Y - H:i:s"); ?> Contenente i seguenti riferimenti</div>
			</td>
		</tr>
		<tr>
			<td>
				<?php
				function parse_csv($file_name,$del)
				{
					$file_name_array=explode('.',$file_name,-1);
      				$convert_file_name=$file_name_array[0];
					$argments = array();
					$row = 1;
					$s=0;
					if (($handle = fopen($file_name,"r")) !== FALSE) 
					{
					    while (($data = fgetcsv($handle, 10000, $del)) !== FALSE)
					    {
					        $num = count($data);//Data è un array
					        $row++;
					        $tmp=new Parser_Object_confiscated;
					        for ($c=0; $c < $num; $c++) 
					        {	   
						        $tmp -> setLatitude($data[0]);		   
				        		$tmp -> setLongitude($data[1]);		   
				        		$tmp -> setID_Bene($data[2]);	   
				        		$tmp -> setDescrizione($data[3]);		   
				        		$tmp -> setAssegnato($data[13]);		   
				        		$tmp -> setIndirizzo($data[34]);
				        		$tmp -> setName_file_csv($convert_file_name);
					        	if($data[$c]=="")
					        	{
					        		break;
					        	}
					        	$argments[$s]=$tmp;
					        }
					        $s=$s+1;
					    }
					    $Parser=new ParserCSV($argments);
					    $Parser -> Stampa();
					    $Parser -> Carica_csv();
					}
				}
				fclose($handle);
				$del=';';
				print_r(parse_csv($file_name,$del));
				?>
			</td>
		</tr>
	</table>
	<!--<form method="post" action="upload.php">
		Crea File OWL 
		<input type="submit" value="caricacsv" name="caricacsv">
	</form>-->
</body>
</html>		 
<?php
      }else{
         print_r($errors);
      }
   }
?>
