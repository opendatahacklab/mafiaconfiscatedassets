<?php
/**
 * This class allows to represent, using the LOCN and WSG84 vocabularies,
 * a set of objects and their locations in terms of addresses.  The main class is RDFLocationGenerator.
 * Create an instance of ConfiscatedAssetsConverter and parse each asset one by one, after you have
 * sent on the three streams the ontologies preambles.
 * 
 * @author Cristiano Longo
 */

/**
 * Handle the conversion of multiple confiscated assets.
 *
 * Works on three file handles:
 * - the former for all the assets
 * - next the one of addresses
 * - then those containing latitude and longitude
 * - one file with the whole ontology
 * - finally the one containing logs
 *
 * @author Cristiano Longo
 *
 * NOTE: we assume that you imported in the final ontologies (where the converted
 * data will be placed) the confiscated asset vocabolary with prefix ca.
 *
 */
class ConfiscatedAssetsConverter{
	private $assetsHandle;
	private $locationsHandle;
	private $coordinatesHandle;
	private $allHandle;
	private $logsHandle;

	//this array will be used to avoid duplicate locations
	private $locations=array();

	/**
	 * Create a converter which will use the specified handles to output rdf/xml and logs.
	 */
	public function __construct($assetsHandle, $locationsHandle, $coordinatesHandle, $allHandle, $logsHandle){
		$this->assetsHandle=$assetsHandle;
		$this->locationsHandle=$locationsHandle;
		$this->coordinatesHandle=$coordinatesHandle;
		$this->allHandle=$allHandle;
		$this->logsHandle=$logsHandle;
	}

	/*
	 * Convert a specified asset.
	 *
	 * @param rowId identifier of the row in the source file, used for logging purposes
	 * @param uri the URI which will be assigned to the asset representing the individual
	 * @param name asset label
	 * @street street where the asset is located
	 * @number civic number of the asset address
	 * @municipality municipality where the asset is located
	 * @countryCode country code
	 */
	public function convert($rowId, $uri, $name, $street, $number, $municipality, $countryCode='IT'){

		$location=new AddressRDFConverter($street, $number, $municipality, $countryCode);
		$locationUri = $location->id;

		//write the asset
		$this->writeToAssets("<ca:ConfiscatedAsset rdf:about=\"$uri\">\n");
		$this->writeToAssets("\t<rdfs:label>$name</rdfs:label>\n");
		$this->writeToAssets("\t<locn:location rdf:resource=\"$locationUri\" />\n");
		$this->writeToAssets("</ca:ConfiscatedAsset>\n");

		//put an asset in a location, check whether the location already exists in the ontologies,
		//create it if not.
		if (array_key_exists($locationUri, $this->locations))
			return;
		$locations[$locationUri]=true;

		$this->writeToLocations("<locn:Location rdf:about=\"$locationUri\">\n");

		//at first the address
		$this->writeToLocations("\t<locn:address>\n");
		$this->writeToLocations($location->getLOCNAddress($locationUri.'_address'));
		$this->writeToLocations("\t</locn:address>\n");

		//geocoding is attempted just if the address is complete
		if ($number==null || strlen($number)==0){
			fwrite($this->logsHandle, $rowId.": WARNING civic number missing\n");
			$this->writeToLocations("</locn:Location>\n");
			return;
		}

		if ($street==null || strlen($street)==0){
			fwrite($this->logsHandle, $rowId.": ERROR street missing\n");
			$this->writeToLocations("</locn:Location>\n");
			return;
		}

		//if successful, the rdf representation to the point is stored and linked to the location
		$pointUri=$locationUri.'_geometry';
		$point=$location->getPointGoogleGeocoder($pointUri, $this->logsHandle, $rowId);
		if ($point!=null){
			$this->writeToLocations("\t<locn:geometry rdf:resource=\"$pointUri\" />\n");
			$this->writeToLocations("</locn:Location>\n");
			$this->writeToCoordinates($point);
		} else
			$this->writeToLocations("</locn:Location>\n");
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
	private function writeToCoordinates($str){
		fwrite($this->coordinatesHandle, $str);
		fwrite($this->allHandle, $str);
	}
}

/**
 * Handle an address.
 */
class AddressRDFConverter{
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
		$r.="\t\t\t<locn:thoroughfare>$this->street</locn:thoroughfare>\n";
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
