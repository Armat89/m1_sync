<?php

class Armat_Armatimport_Model_Product extends Extcommerce_Altronimport_Model_Connection
{
	const ARMAT_PRODUCT_XML = 'filename.xml';
	
    
    public function _construct()
    {
		$this->setXMLPath(self::ARMAT_PRODUCT_XML);
		parent::_construct();
		
	}
	
	public function newProductImport()
	{
		$this->initProcess();
		$this->parseXML();
		
	} 
	
	public function parseXML() {
		
		$xml = $this->getXMLPath();
		$xmlObj = simplexml_load_file($xml);
		foreach($xmlObj->item as $item) {
			$this->createProduct($item);
		}
	}
	
	public function createProduct($item)
	{
			  
			/* Mapping
				LITM	Artikelposition
				EITM	GTIN
				MITM	MPN
				WTXT	Title
				DES2	Short Description
				WTX2	Description
				GWGH	Weight
				MAFT	Manufacture
				STQU	Stock
				CAT1	Headcategorie
				CAT2	Subcategorie
				CAT3	Subcategorie
				CATA	it's the final categorie where the product is in
				INPR	Price
			*/
			
			
			$productSku = $item->LITM;
			$productLoad = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
			  
			  if(!$productLoad){
				$product = Mage::getModel('catalog/product');
  
			  } else {
				  $product = Mage::getModel('catalog/product')->load($productLoad->getId());
			  }
			  
			  $product->setStoreId(1) //you can set data in store scope
						->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
						->setAttributeSetId(4) //ID of a attribute set named 'default'
						->setTypeId('simple') //product type
						->setCreatedAt(strtotime('now')) //product creation time
						->setStatus(1) //product status (1 - enabled, 2 - disabled)
						->setTaxClassId(4) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
						->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH); //catalog and search visibility
			
			  $product->setGtin($item->part_number->EITM);
			  $product->setMpn($item->part_number->MITM);
			  $product->setName($item->part_description->WTXT);
			  $product->setShortDescription($item->part_description->DES2);
			  $product->setDescription($item->part_description->WTX2);
			  $product->setWeight($item->additional_information->GWGH);
			  
			
			  $product->setManufacturer($this->retrieveOptionId('manufacturer',$item->additional_information->MAFT)); //option needs to be selected
			
			
			  $product->setStockData(array(
                       'use_config_manage_stock' => 0, //'Use config settings' checkbox
                       'manage_stock'			 => 1, //manage stock
                       'min_sale_qty'			 => 1, //Minimum Qty Allowed in Shopping Cart
                       'use_config_max_sale_qty' => 1, //Maximum Qty Allowed in Shopping Cart
                       'is_in_stock' 			 => 1, //Stock Availability
                       'qty' 					 => $item->additional_information->STQU //qty
					)
				);
				
			$product->setSku($item->LITM);
			$product->setDistributor($this->retrieveOptionId("distributor","Alltron"));
			$product->setEstimatedShippingTime("1-2 Werktage");
			$product->setQty($item->STQU);
			
			$categories[] = $this->getCategoryId($item->part_catagory->CAT1);
			$categories[] = $this->getCategoryId($item->part_catagory->CAT2);
			$categories[] = $this->getCategoryId($item->part_catagory->CAT3);
			$categories[] = $this->getCategoryId($item->part_catagory->CATA);			
			$product->setCategoryIds($categories);

			$product->save();
	}
	
	public function retrieveOptionId($attributeCode,$attributeValue) {
		$productModel = Mage::getModel('catalog/product');
		$attr = $productModel->getResource()->getAttribute($attributeCode);
		$optionId = '';	
			if ($attr->usesSource()) {
				$optionId = $attr->getSource()->getOptionId($attributeValue);
			}
		return $optionId;
	}
	
	public function getCategoryId($categoryName){
		$_category = Mage::getResourceModel('catalog/category_collection')
        ->addFieldToFilter('name', $categoryName)
        ->getFirstItem();
		
		return $categoryId = $_category->getId();
		
	}
}