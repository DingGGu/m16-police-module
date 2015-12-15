<?php

/**
 * User: DingGGu
 * Date: 2014-12-23
 */
class m16_policeView extends m16_police
{
    function init()
    {
        // 설정 값 세팅
        $oModuleModel = getModel('module');
        $config = $oModuleModel->getModulePartConfig('m16_police', $this->module_info->module_srl);
        Context::set('config', $config);

        // 스킨 경로 세팅
        $templatePath = sprintf('%sskins/%s/', $this->module_path, $this->module_info->skin);
        $this->setTemplatePath($templatePath);

    }

    function dispPoliceReport()
    {
        if (!$this->grant->write_report) {
            return new Object(-1, 'msg_not_permitted');
        }

        $logged_info = Context::get('logged_info');

        $police_srl = Context::get('police_srl');

        if (isset($police_srl)) {
            Context::set('police_srl', $police_srl);

            $oPoliceModel = &getModel('m16_police');

            $args = new stdClass();
            $args->police_srl = $police_srl;

            $oReport = $oPoliceModel->getReportContent($args);

            if (($logged_info->member_srl != $oReport->reporter_srl) || $oReport->already_judge == 'Y') {
                return new Object(-1, 'msg_not_permitted');
            }

            Context::set('oReport', $oReport);

            $oDocumentModel = getModel('document');
            $oDocument = $oDocumentModel->getDocument(0);
            $oDocument->add('module_srl', $this->module_srl);
            $oDocument->setDocument($oReport->document_srl);
        } else {
            Context::set('police_srl', '', true);
            $oDocumentModel = getModel('document');
            $oDocument = $oDocumentModel->getDocument(0);
            $oDocument->add('module_srl', $this->module_srl);
        }

        Context::set('oDocument', $oDocument);
        $this->setTemplateFile('report_form');
    }

    function dispPoliceView()
    {
        $policeSrl = Context::get('police_srl');

        if ($policeSrl) {
            return $this->viewDocument();
        } else {
            return $this->viewList();
        }
    }

    private function viewDocument()
    {
        if (!$this->grant->view) {
            return new Object(-1, 'msg_not_permitted');
        }

        $police_srl = Context::get('police_srl');
        $obj = new stdClass();
        $obj->police_srl = $police_srl;

        $oPoliceModel = &getModel('m16_police');
        $output = $oPoliceModel->getReportContent($obj);
        $documentSrl = $output->document_srl;
        $judgeSrl = $output->judge_srl;

        $oDocumentModel = getModel('document');
        $oDocument = $oDocumentModel->getDocument($documentSrl);
        $oJudge = $oDocumentModel->getDocument($judgeSrl);

        if (!$oDocument->isExists()) {
            return new Object(-1, 'msg_not_founded');
        }

        if ($this->grant->manager) {
            $oDocument->setGrant();
        }

        $oDocument->updateReadedCount();
        Context::addBrowserTitle($oDocument->getTitleText());
        Context::set('oDocument', $oDocument);
        Context::set('oReport', $output);
        Context::set('oJudge', $oJudge);

        $this->setTemplateFile('view');
    }

    private function viewList()
    {
        if (!$this->grant->list) return new Object(-1, 'msg_not_permitted');

        $args = new stdClass();
        $args->page = Context::get('page');

        $oPoliceModel = &getModel('m16_police');
        $output = $oPoliceModel->getReportList($args);

        $oDocumentModel = getModel('document');

        Context::set('oDocumentModel', $oDocumentModel);
        Context::set('report_list', $output->data);
        Context::set('total_count', $output->total_count);
        Context::set('total_page', $output->total_page);
        Context::set('page', $output->page);
        Context::set('page_navigation', $output->page_navigation);

        $this->setTemplateFile('list');
    }

    function dispMyReport()
    {
        if (!$this->grant->list) return new Object(-1, 'msg_not_permitted');

        $logged_info = Context::get('logged_info');

        $args = new stdClass();
        $args->page = Context::get('page');
        $args->s_reporter_srl = $logged_info->member_srl;

        $oPoliceModel = &getModel('m16_police');
        $output = $oPoliceModel->getReportList($args);

        $oDocumentModel = getModel('document');

        Context::set('oDocumentModel', $oDocumentModel);
        Context::set('report_list', $output->data);
        Context::set('total_count', $output->total_count);
        Context::set('total_page', $output->total_page);
        Context::set('page', $output->page);
        Context::set('page_navigation', $output->page_navigation);

        $this->setTemplateFile('list');
    }

    function dispWaitReport()
    {
        if (!$this->grant->list) return new Object(-1, 'msg_not_permitted');

        $args = new stdClass();
        $args->page = Context::get('page');
        $args->s_judge_status = 0;

        $oPoliceModel = &getModel('m16_police');
        $output = $oPoliceModel->getReportList($args);

        $oDocumentModel = getModel('document');

        Context::set('oDocumentModel', $oDocumentModel);
        Context::set('report_list', $output->data);
        Context::set('total_count', $output->total_count);
        Context::set('total_page', $output->total_page);
        Context::set('page', $output->page);
        Context::set('page_navigation', $output->page_navigation);

        $this->setTemplateFile('list');
    }

    /**
     *
     */
    function dispPoliceChart()
    {
        // 전체 신고 갯수
        $oPoliceModel = &getModel('m16_police');
        $output = $oPoliceModel->getReportChart();

        // TEAM_ADMIN 목록 불러오기
        $args = new stdClass();
        $args->selected_group_srl = 3205437;
        $args->list_count = 30;
        $output2 = executeQuery('member.getMemberListWithinGroup', $args);

        $report_chart = array();

        foreach ($output2->data as $key => $val) {
            $cnt_all = 0;
            $cnt_judge = 0;
            foreach ($output->data as $key2 => $val2) {
                if ($val2->nick_judge_admin == $val->nick_name) {
                    $cnt_all++;
                    if ($val2->judge_status == '1') {
                        $cnt_judge++;
                    }
                }
            }
            $obj = new stdClass();
            $obj->nick = $val->nick_name;
            $obj->judge = $cnt_judge;
            $obj->reject = $cnt_all - $cnt_judge;
            $obj->all = $cnt_all;
            $report_chart[$key] = $obj;
        }

        $a_cnt_wait = 0;
        $a_cnt_judge = 0;
        $a_cnt_reject = 0;
        $t_cnt_wait = 0;
        $t_cnt_judge = 0;
        $t_cnt_reject = 0;

        foreach ($output->data as $key => $val) {
            switch ($val->judge_status) {
                case 0:
                    $a_cnt_wait++;
                    break;
                case 1:
                    $a_cnt_judge++;
                    break;
                case 2:
                    $a_cnt_reject++;
                    break;
            }
            if ($val->regdate > date('Ymd000000', time()) && $val->regdate < date('Ymd999999', time())) {
                switch ($val->judge_status) {
                    case 0:
                        $t_cnt_wait++;
                        break;
                    case 1:
                        $t_cnt_judge++;
                        break;
                    case 2:
                        $t_cnt_reject++;
                        break;
                }
            }
        }

        $result = new stdClass();

        $result->today->wait = $t_cnt_wait;
        $result->today->reject = $t_cnt_reject;
        $result->today->judge = $t_cnt_judge;
        $result->today->all = $t_cnt_wait + $t_cnt_reject + $t_cnt_judge;

        $result->report->wait = $a_cnt_wait;
        $result->report->reject = $a_cnt_reject;
        $result->report->judge = $a_cnt_judge;
        $result->report->all = count($output->data);

        $result->admin = $report_chart;

        Context::set('result', $result);

        $this->setTemplateFile('chart');
    }
}