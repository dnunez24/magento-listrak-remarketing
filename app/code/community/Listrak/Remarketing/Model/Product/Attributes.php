<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.5
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2013 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Model_Product_Attributes
 *
 * Model to populate attributes in the attribute set map admin page
 */
class Listrak_Remarketing_Model_Product_Attributes
{
    /**
     * Returns all product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        /* @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $col */
        $col = Mage::getResourceModel('catalog/product_attribute_collection');
        $col->addVisibleFilter();

        $attributes = array();

        /* @var Mage_Catalog_Model_Resource_Eav_Attribute $value */
        foreach ($col as $value) {
            array_push(
                $attributes,
                array(
                    'code' => $value->getAttributeCode(),
                    'label' => $value->getFrontendLabel()
                )
            );
        }

        // sort the attributes by label
        usort(
            $attributes,
            function ($attrA, $attrB) {
                $valA = $attrA['label'] . ':' . $attrA['code'];
                $valB = $attrB['label'] . ':' . $attrB['code'];
                return (($valA == $valB) ? 0 : (($valA < $valB) ? -1 : 1));
            }
        );

        $final = array();
        array_push($final, array('value' => '', 'label' => 'No Selection'));
        foreach ($attributes as $attribute) {
            array_push(
                $final,
                array(
                    'value' => $attribute['code'],
                    'label' => $attribute['label'] . ' (' . $attribute['code'] . ')'
                )
            );
        }

        return $final;
    }
}

