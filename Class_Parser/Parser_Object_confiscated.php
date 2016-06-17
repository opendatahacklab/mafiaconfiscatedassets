<?php 
/**
 * @author Giorgio Oliva
 * La Classe Parser_Object_confiscated intende creare una struttura che immagazzini le informazioni fondamentali 
 * relative ai beni confiscati come Latitudine, Longitudine, ID_Bene, Descrizione, Assegnato,Indirizzo
 */

class Parser_Object_confiscated
{
	public $latitude;
	public $longitude;
	public $ID_Bene;
	public $Descrizione;
	public $Assegnato;
	public $Indirizzo;
    public $Name_file_csv;
    public function getName_file_csv() 
    {
        echo $Name_file_csv;
        return $this->Name_file_csv;
    }
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
    public function setName_file_csv($Name_file_csv) 
    {
       $this->Name_file_csv=$Name_file_csv;
       return $this;
    }

}
?>