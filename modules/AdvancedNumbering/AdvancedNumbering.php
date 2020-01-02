<?php

class AdvancedNumbering
{

    /**
     * Invoked when special actions are performed on the module.
     * 
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    function vtlib_handler($modulename, $event_type)
    {
        require_once('include/utils/utils.php');
        if ($event_type == 'module.postinstall') {
            self::installEventHandler();
        } else if ($event_type == 'module.disabled') {
            // TODO Handle actions when this module is disabled.
            return;
        } else if ($event_type == 'module.enabled') {
            // TODO Handle actions when this module is enabled.
            return;
        } else if ($event_type == 'module.preuninstall') {
            // TODO Handle actions when this module is about to be deleted.
            //$this->uninstallberliWidgets();
            return;
        } else if ($event_type == 'module.preupdate') {
            // TODO Handle actions before this module is updated.
            return;
        } else if ($event_type == 'module.postupdate') {
            return;
        }
    }

    function installEventHandler()
    {
        $adb = PearDatabase::getInstance();
        $em = new VTEventsManager($adb);

        $em->registerHandler('vtiger.entity.beforesave',
            'modules/AdvancedNumbering/EventHandler.php', 'EventHandler');
    }

}
