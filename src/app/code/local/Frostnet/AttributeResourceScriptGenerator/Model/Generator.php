<?php
/**
 * Frostnet
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact Frostnet for more information.
 *
 * @category    Frostnet
 * @package     mage-attribute-script.local
 * @copyright   Copyright (c) 2016 Frostnet
 * @author      Frostnet
 *
 */

class Frostnet_AttributeResourceScriptGenerator_Model_Generator extends Mage_Core_Model_Abstract
{
	/**
	 * Attribute Code
	 *
	 * @var string
	 */
	protected $_attributeCode = '';

	/**
	 * Generate a resource script for the attribute
	 *
	 * @param $attributeId int  Attribute ID
	 *
	 * @return string
	 */
	public function generateCode($attributeId)
	{
		$this->_attributeCode = Mage::getModel('eav/entity_attribute')->load($attributeId)->getAttributeCode();

		// get a map of 'real' attribute properties to properties used in setup resource array
		$realToSetupKeyLegend = $this->_getKeyMapping();

		// swap keys from above
		$data = $this->_getDefaultValues();
		$keysLegend = array_keys($realToSetupKeyLegend);
		$newData = array();

		foreach ($data as $key => $value) {
			if (in_array($key, $keysLegend)) {
				$key = $realToSetupKeyLegend[$key];
			}
			$newData[$key] = $value;
		}

		// chuck a few warnings out there for things that were a little murky
		if (isset($newData['attribute_model'])) {
			$this->warnings[] = '<warning>WARNING, value detected in attribute_model. We\'ve never seen a value ' .
			                    'there before and this script doesn\'t handle it.  Caution, etc. </warning>';
		}

		if (isset($newData['is_used_for_price_rules'])) {
			$this->warnings[] = '<error>WARNING, non false value detected in is_used_for_price_rules. ' .
			                    'The setup resource migration scripts may not support this (per 1.7.0.1)</error>';
		}

		//get text for script
		$arrayCode = var_export($newData, true);

		//generate script using simple string concatenation, making
		//a single tear fall down the cheek of a CS professor
		$script = "<?php

/*
 * startSetup() and endSetup() are intentionally omitted
 */
        
/* @var \$setup Mage_Catalog_Model_Resource_Setup */
\$setup = new Mage_Catalog_Model_Resource_Setup('core_setup');

/* 
 *  Note that apply_to can accept a string of product types, e.g. 'simple,configurable,grouped'
 */
\$data = $arrayCode;
\$setup->addAttribute('catalog_product', '" . $this->_attributeCode . "', \$data);
            ";

		$labelsScript = "
/*
 * Add different labels for multi-store setups
 * Labels should be added in [store_id => label, ...] array format
 */
// \$attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', '" . $this->_attributeCode . "');
// \$attribute->setStoreLabels(array (
// ));
// \$attribute->save();
";

		$script .= $labelsScript;

		Mage::log($script, null, 'frostnet_attributeresourcescriptgenerator.log', true);

		return $script;
	}

	/**
	 * Gets key legend for catalog product attribute
	 *
	 * @return array
	 */
	protected function _getKeyMapping()
	{
		return array(
			'frontend_input_renderer'       => 'input_renderer',
			'is_global'                     => 'global',
			'is_visible'                    => 'visible',
			'is_searchable'                 => 'searchable',
			'is_filterable'                 => 'filterable',
			'is_comparable'                 => 'comparable',
			'is_visible_on_front'           => 'visible_on_front',
			'is_wysiwyg_enabled'            => 'wysiwyg_enabled',
			'is_visible_in_advanced_search' => 'visible_in_advanced_search',
			'is_filterable_in_search'       => 'filterable_in_search',
			'is_used_for_promo_rules'       => 'used_for_promo_rules',
			'backend_model'                 => 'backend',
			'backend_type'                  => 'type',
			'backend_table'                 => 'table',
			'frontend_model'                => 'frontend',
			'frontend_input'                => 'input',
			'frontend_label'                => 'label',
			'frontend_class'                => 'frontend_class',
			'source_model'                  => 'source',
			'is_required'                   => 'required',
			'is_user_defined'               => 'user_defined',
			'default_value'                 => 'default',
			'is_unique'                     => 'unique',
			'note'                          => 'note',
			'group'                         => 'group',
		);
	}

	/**
	 * Get default attribute values
	 *
	 * @return array
	 */
	protected function _getDefaultValues()
	{
		// Catalog product default values
		$data = array(
			'backend_model'   => NULL,
			'backend_type'    => 'varchar',
			'backend_table'   => NULL,
			'frontend_model'  => NULL,
			'frontend_input'  => 'text',
			'frontend_label'  => NULL,
			'frontend_class'  => NULL,
			'source_model'    => NULL,
			'is_required'     => 0,
			'is_user_defined' => 1,
			'default_value'   => NULL,
			'is_unique'       => 0,
			'note'            => NULL,
			'label'           => ucwords(str_replace('_', ' ', $this->_attributeCode)),
			'frontend_input_renderer'       => NULL,
			'is_global'                     => \Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'is_visible'                    => 1,
			'is_searchable'                 => 0,
			'is_filterable'                 => 0,
			'is_comparable'                 => 0,
			'is_visible_on_front'           => 0,
			'is_wysiwyg_enabled'            => 0,
			'is_html_allowed_on_front'      => 0,
			'is_visible_in_advanced_search' => 0,
			'is_filterable_in_search'       => 0,
			'used_in_product_listing'       => 0,
			'used_for_sort_by'              => 0,
			'apply_to'                      => 'simple',
			'position'                      => 999,
			'is_configurable'               => 0,
			'is_used_for_promo_rules'       => 0,
			'group'                         => 'General',
		);
		return $data;
	}
}