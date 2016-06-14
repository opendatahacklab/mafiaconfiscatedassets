<?php
/** 
 * @author Oliva Giorgio
 */
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/
class Generate_File_RDF_XML{
	private $assetsHandle;
	private $locationsHandle;
	private $coordinatesHandle;
	private $allHandle;

	public function __construct($assetsHandle, $locationsHandle, $coordinatesHandle,$allHandle)
	{
		$this->assetsHandle=$assetsHandle;
		$this->locationsHandle=$locationsHandle;
		$this->coordinatesHandle=$coordinatesHandle;
		$this->allHandle=$allHandle;
	}

	public function convert($i, $latitude, $longitude, $ID_Bene, $Indirizzo, $Assegnato, $Descrizione,$municipality,$countryCode)
	{
		$location=new AddressRDFConverter($Indirizzo,"", $municipality, $countryCode);
		$locationUri = $location->id;
		//************************************************************* write the Asset
		$this->writeToAssets("<ca:ConfiscatedAsset rdf:about=\"$i\">\n");
		$this->writeToAssets("\t<rdfs:label>$Descrizione</rdfs:label>\n");
		$this->writeToAssets("\t<locn:location rdf:resource=\"$locationUri\" />\n");
		$this->writeToAssets("</ca:ConfiscatedAsset>\n");
		//put an asset in a location, check whether the location already exists in the ontologies,
		//create it if not.
		if (array_key_exists($locationUri, $this->locations))
			return;
		$locations[$locationUri]=true;
		//*************************************************************  end write the Asset
		//************************************************************* write the Locations
		$this->writeToLocations("<locn:Location rdf:about=\"$locationUri\">\n");
		//at first the address
		$pointUri=$locationUri.'_geometry';
		$this->writeToLocations("\t<locn:address>\n");
		$this->writeToLocations($location->getLOCNAddress($locationUri.'_address'));
		$this->writeToLocations("\t</locn:address>\n");
		$this->writeToLocations("\t<locn:geometry rdf:resource=\"$pointUri\" />\n");
		$this->writeToLocations("</locn:Location>\n");
		//************************************************************* End write the Locations
		//*************************************************************  Write the Geopoints
			$this->writeToCoordinates("<geo:Point rdf:about=\"$locationUri\">\n");
			$this->writeToCoordinates("\t<geo:lat>$latitude</geo:lat>\n");
		  	$this->writeToCoordinates("\t<geo:long>$longitude</geo:long>\n");
			$this->writeToCoordinates("</geo:Point>\n");
		//*************************************************************  End Write the Geopoints

	}
	/**
	 * Write a string to the assets handle
	 * 
	 * @param String $str
	 */
	private function writeToAssets($str){
		fwrite($this->assetsHandle, $str);	
		fwrite($this->allHandle, $str);		
	}
	
	/**
	 * Write a string to the locations handle
	 *
	 * @param String $str
	 */
	private function writeToLocations($str){
		fwrite($this->locationsHandle, $str);
		fwrite($this->allHandle, $str);
	}

	/**
	 * Write a string to the coordinates handle
	 *
	 * @param String $str
	 */
	private function writeToCoordinates($str)
	{
		fwrite($this->coordinatesHandle, $str);
		fwrite($this->allHandle, $str);
	}
}
class AddressRDFConverter
{
	public $id;
	public $street;
	public $number;
	public $municipality;
	public $countryCode;
	
	public function __construct($street, $number, $municipality, $countryCode='IT'){
		$this->id=AddressRDFConverter::getID($street, $number, $municipality, $countryCode);
		$this->street=$street;
		$this->number=$number;
		$this->municipality=$municipality;
		$this->countryCode=$countryCode;
	}

	/**
	 * Generate ad identifier from an address. This may be used to
	 * generate location URIs.
	 */
	private static function getID($street, $number='NA', $municipality, $countryCode='IT'){
		$id=$street.'_'.$number.'_'.$municipality.'_'.$countryCode;
		$id_no_accent=str_replace('à','a',str_replace('é','e',str_replace('ì','i',str_replace('ò','o',str_replace('ù','u',$id)))));
		return urlencode(strtolower(str_replace(' ','_',$id_no_accent)));
	}
			
	/**
	 * Return the a string encoding this address in RDF. 
	 *
	 * @parameter uri the uri assigned to the new locn:Address invidual
	 */
	public function getLOCNAddress($uri){
		$r="\t\t<locn:Address rdf:about=\"$uri\">\n";
		if ($this->number!=null && strlen($this->number>0)){
			$r.="\t\t\t<rdfs:label>$this->number, $this->street,  $this->municipality, $this->countryCode</rdfs:label>\n";
			$r.="\t\t\t<locn:fullAddress>$this->number, $this->street,  $this->municipality, $this->countryCode</locn:fullAddress>\n";
			$r.="\t\t\t<locn:locatorDesignator>$this->number</locn:locatorDesignator>\n";
		} else {
			$r.="\t\t\t<rdfs:label>$this->street,  $this->municipality, $this->countryCode</rdfs:label>\n";
			$r.="\t\t\t<locn:fullAddress>$this->street,  $this->municipality, $this->countryCode</locn:fullAddress>\n";		
		}
		//$r.="\t\t\t<locn:thoroughfare>$this->street</locn:thoroughfare>\n";
		$r.="\t\t\t<locn:postName>$this->municipality</locn:postName>\n";
		$r.="\t\t\t<locn:adminUnitL1>$this->countryCode</locn:adminUnitL1>\n";
		$r.="\t\t</locn:Address>\n";
		return $r;
	}
	
	/**
	 * Print the rdf representation of the geo point corresponding to an address.
	 * The coordinates have been retrieved via the google geocoding service.
	 * 
	 * Note that here we assume that the address is complete (number and street are present).
	 *
	 * @parameter uri the uri assigned to the new individual
	 * @param logFileHandle is an handle where errors and warnings will be sent
	 * @param rowLogId a row identified to be prepended in the log row, indicates the csv row the
	 * 
	 * @return the string encoding the Point in the sense of WSG84 corresponding to
	 * the specified coordinates. Null if the geo-coding fails.
	 */
	function getPointGoogleGeocoder($uri, $logFileHandle, $rowLogId){
		$r="<geo:Point rdf:about=\"$uri\">\n";					
		$address=$this->number.','.htmlentities($this->street).','.
			htmlentities($this->municipality).','.$this->countryCode;
		//else
		//	$address=htmlentities($this->street).','.htmlentities($this->municipality).','.
		//		$this->countryCode;
				
		$request_url = "http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=" . urlencode($address);
		$xml = simplexml_load_file($request_url) or die("url not loading");
		if ($xml->result==null || $xml->result->geometry==null || $xml->result->geometry->location==null){
			fwrite($logFileHandle, "$rowLogId: ERROR Unable to retrieve point for $address query=$request_url");	
			return null;
		} 
		$lat = $xml->result->geometry->location->lat;
		$long = $xml->result->geometry->location->lng;
		
		if ($xml->result->geometry->location_type=="APPROXIMATE"){
			fwrite($logFileHandle, "$rowLogId: WARNING approximate geocoding for $address query=$request_url\n");	
			return null;
		} 
	  	$r.="\t<geo:lat>$lat</geo:lat>\n";
	  	$r.="\t<geo:long>$long</geo:long>\n";
		$r.="</geo:Point>\n";
		return $r;
	}	
}
?>