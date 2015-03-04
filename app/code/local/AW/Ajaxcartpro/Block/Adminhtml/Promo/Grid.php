<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcartpro
 * @version    3.2.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ajaxcartpro_Block_Adminhtml_Promo_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ajaxcartproPromoGrid');
        $this->setDefaultSort('id', 'desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ajaxcartpro/promo')->getResourceCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                 'header' => $this->__('ID'),
                 'index'  => 'rule_id',
                 'type'   => 'number',
                 'width'  => 10
            )
        );

        $this->addColumn(
            'name',
            array(
                 'header' => $this->__('Rule Name'),
                 'align'  => 'left',
                 'index'  => 'name',
                 'width'  => 1000,
            )
        );

        $this->addColumn(
            'type',
            array(
                 'header'  => $this->__('Action'),
                 'width'   => 100,
                 'align'   => 'left',
                 'index'   => 'type',
                 'type'    => 'options',
                 'options' => Mage::getModel('ajaxcartpro/source_promo_rule_type')->toOptionArray(),
            )
        );

        $this->addColumn(
            'is_active',
            array(
                 'header'  => $this->__('Status'),
                 'align'   => 'left',
                 'width'   => '80px',
                 'index'   => 'is_active',
                 'type'    => 'options',
                 'options' => array(
                     1 => $this->__('Active'),
                     0 => $this->__('Inactive')
                 ),
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_ids',
                array(
                     'header'                    => $this->__('Store View'),
                     'index'                     => 'store_ids',
                     'type'                      => 'store',
                     'width'                     => '100px',
                     'store_all'                 => true,
                     'store_view'                => true,
                     'sortable'                  => false,
                     'renderer'                  => 'ajaxcartpro/adminhtml_promo_grid_renderer_multiStores',
                     'filter_condition_callback' => array($this, 'filterStore'),
                )
            );
        }

        $this->addColumn(
            'from_date',
            array(
                 'header' => $this->__('Date Start'),
                 'align'  => 'left',
                 'width'  => '120px',
                 'type'   => 'date',
                 'index'  => 'from_date',
            )
        );

        $this->addColumn(
            'to_date',
            array(
                 'header'  => $this->__('Date Expire'),
                 'align'   => 'left',
                 'width'   => '120px',
                 'type'    => 'date',
                 'default' => '--',
                 'index'   => 'to_date',
            )
        );

        $this->addColumn(
            'priority',
            array(
                 'header' => $this->__('Priority'),
                 'align'  => 'right',
                 'width'  => '50px',
                 'index'  => 'priority',
            )
        );
    }

    protected function filterStore($collection, $column)
    {
        $collection->addStoreFilter($column->getFilter()->getValue());
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
    }
}