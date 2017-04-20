<?php

class Armat_Armatimport_Model_Connection extends Mage_Core_Model_Abstract
{
    
	public static $ftp = ''; 
	
	private $_host = 'ftp.aaaaaaaa.com';
	private $_user = 'username';
	private $_pass = '**********';
	
	private $_filename = '';
	
	private $_XML = '';
	
    public function _construct()
    {
		self::$ftp = new Varien_Io_Sftp();
		self::$ftp->open(
			array(
					'host'      => $this->_host,
					'username'  => $this->_user,
					'password'  => $this->_pass,
				)
			);		
	}
	
	public function getXMLPath(){
		return $this->_XML;
	}
	
	public function setXMLPath($filename){
		$this->_filename = $filename;
		$this->_XML = Mage::getBaseDir().'/armatproduct/'.$filename;
	}
	
	public function getFilename(){
		
		return $this->_filename;
	}
	
	public function initProcess()
	{
		if(!file_exists($this->getXMLPath()))
			self::$ftp->read($this->getFilename(),$this->getXMLPath());		
	}
		
}