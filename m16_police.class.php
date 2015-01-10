<?php
/**
 * User: DingGGu
 * Date: 2014-12-23
 */

class m16_police extends ModuleObject {
    function moduleInstall() {
        $oModuleController = getController('module');
        $oModuleController->insertTrigger('menu.getModuleListInSitemap', 'm16_police', 'model', 'triggerModuleListInSitemap', 'after');

        return new Object();
    }

    function checkUpdate() {
        $oModuleModel = &getModel('module');

        if(!$oModuleModel->getTrigger('menu.getModuleListInSitemap', 'm16_police', 'model', 'triggerModuleListInSitemap', 'after')) return true;

        return false;
    }

    function moduleUpdate() {
        $oModuleController = getController('module');
        $oModuleModel = &getModel('module');

        if(!$oModuleModel->getTrigger('menu.getModuleListInSitemap', 'm16_police', 'model', 'triggerModuleListInSitemap', 'after'))
            $oModuleController->insertTrigger('menu.getModuleListInSitemap', 'm16_police', 'model', 'triggerModuleListInSitemap', 'after');

        return new Object();
    }

    function moduleUninstall() {
        $oModuleController = getController('module');
        $oModuleController->deleteTrigger('menu.getModuleListInSitemap', 'm16_police', 'model', 'triggerModuleListInSitemap', 'after');

        return new Object();
    }
}