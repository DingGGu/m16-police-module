<?php
class m16_policeAdminView extends m16_police {
    function init() {
        // 설정 정보 가져오기
        $oModuleModel = &getModel('module');
        $police_info = $oModuleModel->getModuleInfoByMid("police");
        $police_config = $oModuleModel->getModuleConfig('m16_police');
        $module_category = $oModuleModel->getModuleCategories();

        // 설정 변수 지정
        Context::set('police_info', $police_info);
        Context::set('police_config', $police_config);
        Context::set('module_category', $module_category);

        // template path지정
        $this->setTemplatePath($this->module_path.'tpl');
    }

    function dispPoliceAdminConfig() {
        // 스킨목록 가져오기
        $oModuleModel = &getModel('module');
        $skin_list = $oModuleModel->getSkins($this->module_path);
        Context::set('skin_list',$skin_list);

        // 레이아웃 목록을 구해옴
        $oLayoutMode = &getModel('layout');
        $layout_list = $oLayoutMode->getLayoutList();
        Context::set('layout_list', $layout_list);

        // 템플릿 파일 지정
        $this->setTemplateFile('config');
    }
}