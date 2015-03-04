<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.3.1
 * @license:     lMnQrjiQcxfPlbdwpFhsKslnzVPfVqO45IEZa4tVHs
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Model_Cronstat extends Mage_Core_Model_Abstract
{
    const _configPath = 'catalog/adjcartalert/statconfig';
    
    private function _getConfig($key = null)
    {
        
        $configModel = Mage::getModel('core/config_data')->getCollection()
            ->addFieldToFilter('path', self::_configPath)
            ->getFirstItem();        
        
        $config = unserialize($configModel->getValue());
        
        if(is_null($key))
        {
            return $config;
        }
        
        return isset($config[$key])?$config[$key]:'';
    }


    private function _setConfig($key, $value = null)
    {
        if(is_array($key))
        {
            $config = $key;
        }
        else
        {
            $config = $this->_getConfig();
            $config[$key]=$value;
        }
        
        $configModel = Mage::getModel('core/config_data')->getCollection()
            ->addFieldToFilter('path', self::_configPath)
            ->getFirstItem();             
        $configModel->setPath(self::_configPath)->setValue(serialize($config))->save();
       
    }    
    
    public function run()
    {
        $config = $this->_getConfig();
        if(isset($config['task_isset']) && $config['task_isset']==1)
        {   
            $start = str_replace('-','',$config['task_startdate']);
            $end = str_replace('-','',$config['task_enddate']);
            while((int)$start<=(int)$end)
            {
                $config = $this->_getConfig();
                call_user_func(array($config['task_class'],$config['task_method']), $config['task_startdate']);                 
                $this->_setConfig('task_startdate', date('Y-m-d',strtotime($config['task_startdate'])+3600*24));
                $start = str_replace('-','',$config['task_startdate']);
                $end = str_replace('-','',$config['task_enddate']);                
            }
            call_user_func(array($config['task_class'],$config['task_method']), $config['task_enddate']);            
            $this->_setConfig('task_isset',0);
            return 1;
        }
        return 0;
    }    
    
    public function cron()
    {
        $config = $this->_getConfig();
        if(!isset($config['dailytask_updated']) || $config['dailytask_updated']!=date('Y-m-d'))
        {
            $taskCreated = $this->createTask('AdjustWare_Cartalert_Model_Dailystat', 'collectDay', date('Y-m-d', time()-86400*30), date('Y-m-d'));
            if($taskCreated)
            {
                $this->_setConfig('dailytask_updated',date('Y-m-d'));
            }
        }
        $this->run();
    }
    
    public function createTask($class, $method, $fromdate, $todate)
    {
        $config = $this->_getConfig();
        if(isset($config['task_isset']) && $config['task_isset']==1)
        {
            return 0;
        }
        else
        {
            $this->_setConfig(array(
                'task_startdate'    => $fromdate,
                'task_enddate'      => $todate,
                'task_isset'        => 1,
                'task_class'        => $class,
                'task_method'       => $method,
            ));
            return 1;
        }
    }
    
    public function isBusy()
    {

        $config = $this->_getConfig();
        if(isset($config['task_isset']) && $config['task_isset']==1)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    
    }

}