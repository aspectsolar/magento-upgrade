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


class AW_Ajaxcartpro_Adminhtml_PromoController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/catalog')
            ->_addBreadcrumb(
                $this->__('Promotions'),
                $this->__('AJAX Cart Pro Rules')
            );
        return $this;
    }

    public function indexAction()
    {
        $this
            ->_initAction()
            ->_title($this->__('Promotions'))
            ->_title($this->__('AJAX Cart Pro Rules'))
            ->renderLayout()
        ;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $promoModel = $this->_initPromo();
        if (null === $promoModel) {
            return $this->_redirect('*/*/');
        }
        $isNewRulePage = !!$promoModel->getId();
        $this
            ->_initAction()
            ->_title($this->__('Promotions'))
            ->_title($this->__($isNewRulePage ? 'Edit Rule' : 'New Rule'))
            ->renderLayout()
        ;
        return $this;

    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('ajaxcartpro/promo'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        $html = '';
        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        }
        $this->getResponse()->setBody($html);
    }

    public function saveAction()
    {
        if (!$this->getRequest()->getPost()) {
            $this->_redirect('*/*/');
        }
        $promoModel = $this->_initPromo();
        $data = $this->getRequest()->getPost();
        $data = $this->_filterDates($data, array('from_date', 'to_date'));
        if (array_key_exists('from_date', $data) && empty($data['from_date'])) {
            $data['from_date'] = null;
        }

        if (array_key_exists('to_date', $data) && empty($data['to_date'])) {
            $data['to_date'] = null;
        }
        try {
            $validateResult = $promoModel->validateData(new Varien_Object($data));
            if ($validateResult !== true) {
                foreach($validateResult as $errorMessage) {
                    $this->_getSession()->addError($errorMessage);
                }
                $this->_getSession()->setAcpPromoData($data);
                $this->_redirect('*/*/edit', array('id' => $promoModel->getId()));
                return;
            }
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);
            $promoModel
                ->setFromDate($data['from_date'])
                ->setToDate($data['to_date'])
            ;
            unset($data['from_date']);
            unset($data['to_date']);
            $promoModel->loadPost($data);
            $promoModel->setRuleActionsSerialized($this->_getPreparedActionData($data));
            Mage::getSingleton('adminhtml/session')->setAcpPromoData($promoModel->getData());
            $promoModel->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('The rule has been saved.')
            );
            Mage::getSingleton('adminhtml/session')->setAcpPromoData(null);
            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('*/*/edit',
                    array(
                        'id'  => $promoModel->getId(),
                        'active_tab' => $this->getRequest()->getParam('tab')
                    )
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->__('An error occurred while saving the rule data. Please review the log and try again.')
            );
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->setAcpPromoData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
            return;
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $promoRule = $this->_initPromo();
        if (null !== $promoRule->getId()) {
            try {
                $promoRule->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('The rule has been deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('An error occurred while deleting the rule. Please review the log and try again.')
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            $this->__('Unable to find a rule to delete.')
        );
        $this->_redirect('*/*/');
    }

    protected function _getPreparedActionData(array $data)
    {
        $data = new Varien_Object($data);
        $actionData = array_intersect_key(
            $data->getData(),
            array(
                 'options_required_only'            => true,
                 'popup_content'                    => true,
                 'show_dialog'                      => true,
                 'close_dialog_after'               => true,
                 'use_config_options_required_only' => true,
                 'use_config_popup_content'         => true,
                 'use_config_show_dialog'           => true,
                 'use_config_close_dialog_after'    => true,
            )
        );
        return $actionData;
    }

    protected function _initPromo()
    {
        $promoModel = Mage::getModel('ajaxcartpro/promo');
        $ruleId  = (int) $this->getRequest()->getParam('id', null);
        if ($ruleId) {
            try {
                $promoModel->load($ruleId);
                if (null === $promoModel->getId()) {
                    throw new Exception($this->__('This rule no longer exists'));
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage())
                ;
                return null;
            }
        }

        if (null !== Mage::getSingleton('adminhtml/session')->getAcpPromoData()) {
            $promoModel->addData(Mage::getSingleton('adminhtml/session')->getAcpPromoData());
            Mage::getSingleton('adminhtml/session')->setAcpPromoData(null);
        }

        Mage::register('current_acp_promo', $promoModel);
        return $promoModel;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/ajaxcartpro');
    }
}