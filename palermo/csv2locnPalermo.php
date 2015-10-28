<?php
/**
 * Parse the dataset of assets of the municipality of Palermo, filtering just
 * confiscated assets. Generate an owl ontology of these. The original dataset in
 * CSV is retrieved from the open data portal of the municipality of Palermo at
 * the following URL:
 *
 * http://www.comune.palermo.it/opendata_dld.php?id=319
 *
 * It is expected on the standard input. This script will produce two files:
 * - confiscatedAssetsPalermoAssets.owl which will contain a description of
 * assets;
 * - confiscatedAssetsPalermoLocations.owl which describe where assets are located
 *  (in the sense of LOCN vocabulary);
 * - confiscatedAssetsPalermoCoordinates.owl where geometry (in the sense of
 * wsg84) of confiscated assets are stored;
 * - confiscatedAssetsPalermoAll.owl which will merge all the preceeding ontologies
 * - confiscatedAssetsPalermoLog.txt which contain warning and error logs.
 *
 * @author Cristiano Longo
 * @author Mario Alvise Di Bernardo
 */

require("../ConfiscatedAssetsConverter.php");

/******************************************************
 * Create the handles
*/
$assets=fopen('assets.owl','w+') or die('Unable to create file');
$locations=fopen('locations.owl','w+') or die('Unable to create file');
$coordinates=fopen('geometry.owl','w+') or die('Unable to create file');
$all=fopen('assetsAll.owl','w+') or die('Unable to create file');
$log=fopen('log.txt','w+') or die('Unable to create file');

/******************************************************
 * Print headers.
*/
fwrite($assets, file_get_contents('assets_preamble.owl.part'));
fwrite($locations, file_get_contents('locations_preamble.owl.part'));
fwrite($coordinates, file_get_contents('geometry_preamble.owl.part'));
fwrite($all, file_get_contents('assetsAll_preamble.owl.part'));

$timeString="Generated by csv2locnPalermo.php on ".date('d-m-Y-H-i');
fwrite($assets, "<!-- ".$timeString-"-->\n");
fwrite($locations, "<!-- ".$timeString-"-->\n");
fwrite($coordinates, "<!-- ".$timeString-"-->\n");
fwrite($all, "<!-- ".$timeString-"-->\n");
fwrite($log, $timeString."\n");

/******************************************************
 * Parsing
*/
$in=fopen('php://stdin', 'r');

//initialize converter
$converter=new ConfiscatedAssetsConverter($assets, $locations, $coordinates, $all, $log);

//ignore first line
fgets($in);

//number of confiscated assets
$n=0;

//total number of assets
$t=0;

while( $row = fgetcsv($in,1000,";") ){
	$t++;
	echo "Converting row $t\n";
	if (count($row)<2){
		fwrite($log, $t.": ERROR too few fields.\n");
		continue;
	}

	$usage=$row[0];
	$description=$row[1];

	//filter confiscated assets
	$confiscatedPattern="L. *575/65";
	if (!ereg($confiscatedPattern, $usage.$description))
		continue;

	$n++;

	if ($description!=null) 
		$descriptionUTF8=utf8_encode(ereg_replace($confiscatedPattern."[- ]*", '', $description));
	else
		$descriptionUTF8='';

	$street=$row[2];
	if ($street!=null)
		$streetUTF8=utf8_encode($street);
	else
		$streetUTF8='';
	
	$number=$row[3];
	if ($number!=null)
		$numberUTF8=utf8_encode($number);
	else 
		$numberUTF8='';

	$converter->convert($t, $n, $descriptionUTF8, $streetUTF8, $numberUTF8, "Palermo");
}


/******************************************************
 * Print footers and close files.
*/
$footer="</rdf:RDF>\n\n";
fwrite($assets, $footer);
fwrite($locations, $footer);
fwrite($coordinates, $footer);
fwrite($all, $footer);
fwrite($log,"Found $n confiscated assets on $t rows.\n");

fclose($assets);
fclose($locations);
fclose($coordinates);
fclose($all);
fclose($log);
?>