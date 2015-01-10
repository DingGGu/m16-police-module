<?php
/**
 * Created by PhpStorm.
 * User: DingGGu
 * Date: 2015-01-01
 * Time: 오후 10:51
 */

class m16_policeAdminController extends m16_police {
    function init() {
    }

    function procPoliceAdminConfig() {
        // request 값을 모두 받음
        $args = Context::getRequestVars();
        $args->mid = "police";
        $args->module = 'm16_police';

        if(!$args->module_srl) return new Object(-1,'invalid_module');

        // module_srl의 값에 따라 insert/update
        $oModuleController = &getController('module');
        $output = $oModuleController->updateModule($args);

        // 오류가 있으면 리턴
        if(!$output->toBool()) return $output;
        $this->setMessage('success_updated');

        $returnUrl = getNotEncodedUrl('', 'module', 'admin', 'act', 'dispPoliceAdminConfig');
        $this->setRedirectUrl($returnUrl);
    }
}