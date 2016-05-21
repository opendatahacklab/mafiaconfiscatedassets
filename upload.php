<?php
/**
 * @author Giorgio Oliva
 */
session_start();
   if(isset($_FILES['fileToUpload'])){
      $errors= array();
      $file_name = $_FILES['fileToUpload']['name'];
      $file_size =$_FILES['fileToUpload']['size'];
      $file_tmp =$_FILES['fileToUpload']['tmp_name'];
      $file_type=$_FILES['fileToUpload']['type'];
      
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
		class Object_tmp
		{
			public $latitude;
			public $longitude;
			public $ID_Bene;
			public $Descrizione;
			public $Assegnato;
			public $Indirizzo;
			public function getLatitude() 
			{
				echo $latitude;
		        return $this->latitude;
		    }
		    public function getIndirizzo() 
			{
				echo $Indirizzo;
		        return $this->Indirizzo;
		    }
		    public function getLongitude() 
			{
		        return $this->longitude;
		    }
		    public function getID_Bene() 
			{
		        return $this->ID_Bene;
		    }
		    public function getAssegnato() 
			{
		        return $this->Assegnato;
		    }
		    public function getDescrizione() 
			{
		        return $this->Descrizione;
		    }
		    public function setLatitude($latitude) 
			{
		       $this->latitude=$latitude;
		       return $this;
		    }
		    public function setLongitude($longitude) 
			{
				$this->longitude=$longitude;
		        return $this;
		    }
		    public function setID_Bene($ID_Bene) 
			{
				$this->ID_Bene=$ID_Bene;
		        return $this;
		    }
		    public function setAssegnato($Assegnato) 
			{
				$this->Assegnato=$Assegnato;
		        return $this;
		    }
		    public function setDescrizione($Descrizione) 
			{
		       $this->Descrizione=$Descrizione;
		       return $this;
		    }
		    public function setIndirizzo($Indirizzo) 
			{
		       $this->Indirizzo=$Indirizzo;
		       return $this;
		    }

		}
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
						<h1><?php echo "Parserizzo il File CSV : ".$file_name; ?></h1>
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
					$row = 1;
					$s=0;
					$empty=" ";
					$argments = array();
					if (($handle = fopen($file_name,"r")) !== FALSE) 
					{
					    while (($data = fgetcsv($handle, 10000, $del)) !== FALSE)
					     {
					        $num = count($data);//Data è un array
					        $row++;
					        $tmp=new Object_tmp;
					        for ($c=0; $c < $num; $c++) 
					        {
				        		$_GET["latitude"] = $data[0];		   
				        		$tmp -> setLatitude($_GET["latitude"]);
				        		$_GET["longitude"] = $data[1];		   
				        		$tmp -> setLongitude($_GET["longitude"]);
				        		$_GET["ID_Bene"] = $data[2];		   
				        		$tmp -> setID_Bene($_GET["ID_Bene"]);
				        		$_GET["Descrizione"] = $data[3];		   
				        		$tmp -> setDescrizione($_GET["Descrizione"]);
				        		$_GET["Assegnato"] = $data[13];		   
				        		$tmp -> setAssegnato($_GET["Assegnato"]);
				        		$_GET["Indirizzo"] = $data[34];		   
				        		$tmp -> setIndirizzo($_GET["Indirizzo"]);

					        	if($data[$c]=="")//se non ho informazioni mi fermo
					        	{
					        		break;
					        	}
					        	$argments[$s]=$tmp;
					        }
					        $s=$s+1;
					    }
					    $num1 = count($argments);
					    for ($i = 0; $i <= $num1-1; $i++)
					    {

						    $prov=$argments[$i];
						    print("Bene N°".$i. "<br />\n");
						    print("Latitudine : ");
				        	print($prov) -> getLatitude(). "<br />\n";
				        	print("Longitudine : ");
						    print($prov) -> getLongitude(). "<br />\n";
						    print("ID_Bene : ");
						    print($prov) -> getID_Bene(). "<br />\n";
						    print("Indirizzo : ");
						    print($prov) -> getIndirizzo(). "<br />\n";
						    print("Assegnato : ");
						    print($prov) -> getAssegnato(). "<br />\n";
						    print("Descrizione : ");
						    print($prov) -> getDescrizione(). "<br />\n";
						    print("<br />\n");
						}
					}
				}
				$_SESSION['array_to_save'] = $argments;
				$argments =htmlspecialchars(serialize($argments));
				echo "-----------------------------------------<input type=\"hidden\" name=\"ArrayData\" value=\"$serialized\"/>";
				    fclose($handle);
				$del=';';
				print_r(parse_csv($file_name,$del));
				
				?>
			</td>
		</tr>
	</table>
	<form action="WriteFileOWL.php" method="post" enctype="multipart/form-data">
		Crea File OWL 
		<input type="submit" value="Carica CSV" name="submit">
	</form>
</body>
</html>		 
<?php
      }else{
         print_r($errors);
      }
   }
?>
