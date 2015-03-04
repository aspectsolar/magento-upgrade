<?php
/**
 * @category   Auguria
 * @package    Auguria_Video
 * @author     Auguria
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Auguria_Video_Block_Adminhtml_Video_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('video_form', array('legend'=>Mage::helper('auguria_video')->__('Item information')));

		$fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('auguria_video')->__('Title'),
          'class'     => 'required-entry input-text',
          'required'  => true,
          'name'      => 'title',
		));


        //$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
		$fieldset->addField('description', 'editor', array(
				'name' => 'description',
				'label' => Mage::helper('auguria_video')->__('Description'),
				'title' => Mage::helper('auguria_video')->__('Description'),
				'wysiwyg'   => true,
				'style'     => 'width:725px;height:460px',
				//'config'    => $wysiwygConfig
		));

		$fieldset->addField('image_identifier', 'text', array(
				'label'     => Mage::helper('auguria_video')->__('Identifier'),
				'class'     => 'required-entry input-text',
				'required'  => true,
				'name'      => 'image_identifier',
		));

		$fieldset->addField('preview', 'image', array(
          'label'     => Mage::helper('auguria_video')->__('Preview'),
		  'class'     => 'required-entry input-text',
          'required'  => true,
          'name'      => 'preview',
		  'after_element_html'   => '<p class="note"><span>Format: 425x239 pixels</span></p>',
		));

		$fieldset->addField('status', 'select',
            array(
                'name'      => 'status',
                'label'     => Mage::helper('auguria_video')->__('Status'),
                'values'    => Mage::helper('auguria_video')->statusToOptionArray(),
            )
        );

		$button = $this->_getAddProductButton();
		$fieldset->addField('product_id', 'note', array(
	          		'label' => Mage::helper('auguria_video')->__('Associated product'),
					'text' => $button,
		));

		$fieldset->addField('display_on_home_page', 'select',
            array(
                'name'      => 'display_on_home_page',
                'label'     => Mage::helper('auguria_video')->__('Display on home page'),
                'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            )
        );

		if (Mage::registry('video_data')) {
      		$form->setValues(Mage::registry('video_data')->getData());
      	}
      	return parent::_prepareForm();
  	}

  	public function _getAddProductButton()
  	{
  		$productId = "";
  		if (Mage::registry('video_data') && (int)Mage::registry('video_data')->getProductId()>0) {
  			$productId = Mage::registry('video_data')->getProductId();
  		}
  		$input = '<input type="hidden" value="'.$productId.'" name="product_id" id="auguria_video_product_id" />';
  		if ((int)$productId==0) {
  			$value = Mage::helper('auguria_video')->__('No product');
  		}
  		else {
  			$product = Mage::getModel('catalog/product')->load($productId);
  			$value = $product->getName();
  		}

  		$layout = Mage::getSingleton('core/layout');

  		$html = '<script type="text/javascript">// <![CDATA[
  		function displayProductGrid() {
  		winGrid = new Window({className:"magento",title:"'.Mage::helper('auguria_video')->__('Select product').'",width:800,height:450,minimizable:false,maximizable:false,showEffectOptions:{duration:0.4},hideEffectOptions:{duration:0.4}});
  		winGrid.setZIndex(100);
  		winGrid.showCenter(true);
  		winGrid.setContent("video_chooser_container");
  	}
  	// ]]></script>';
  		$html .= $layout->createBlock('adminhtml/widget_button')
  		->setData(array(
  				'label'     => Mage::helper('adminhtml')->__('Select product'),
  				'onclick'   => 'displayProductGrid()',
  				'class' => 'add',
  				'before_html' => $input.'<div class="main"><p id="video_name">'.$value.'</p>',
  				'after_html' => '</div>'))
  				->toHtml();

  		$productsGrid = $layout->createBlock('auguria_video/adminhtml_catalog_product_widget_chooser', '', array(
  				'id'                => 'video_chooser',
  				'use_massaction' 	=> false,
  				'product_type_id'	=> null,
  				'category_id'       => null,
  		));

  		$html  .= "<div id='video_chooser_container' style='display:none'>";
  		$html .= $productsGrid->toHtml();
  		$html  .= "</div>";
  		return $html;

  	}
}