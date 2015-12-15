<?php

/**
 * User: DingGGu
 * Date: 2014-12-23
 */
class m16_policeController extends m16_police
{
    function Init()
    {
    }


    function procPoliceReport()
    {
        // 권한 체크
        if (!$this->grant->write_report) {
            return new Object(-1, 'msg_not_permitted');
        }

        $logged_info = Context::get('logged_info');

        $obj = Context::getRequestVars();

        if (isset($obj->police_srl)) {
            if ($obj->already_judge == 'Y') return new Object(-1, '이미 처리된 사건입니다.');

            $oDocumentModel = &getModel('document');
            $oDocument = $oDocumentModel->getDocument($obj->document_srl);

            $oDocumentController = getController('document');
            $args = new stdClass();
            $args->document_srl = Context::get('document_srl');
            $args->title = Context::get('title');
            $args->content = Context::get('content');
            $output = $oDocumentController->updateDocument($oDocument, $obj);

            $output2 = executeQuery("m16_police.updateReport", $obj);
            if (!$output2->toBool()) return $output2;
        } else {

            $obj->reporter_srl = $logged_info->member_srl;
            $obj->nick_reporter = $logged_info->nick_name;

            // 데이터 준비
            $args = new stdClass();
            $args->module_srl = $this->module_srl;
            $args->document_srl = Context::get('document_srl');
            $args->nick_name = Context::get('nick_name');
            $args->title = Context::get('title');
            $args->content = Context::get('content');

            // document 모듈에 등록
            $oDocumentController = getController('document');
            $output = $oDocumentController->insertDocument($args);

            if (!$output->get('document_srl')) {
                return new Object(-1, 'invalid_request');
            }
            $obj->document_srl = $output->get('document_srl');

            $output2 = executeQuery("m16_police.insertReport", $obj);
            if (!$output2->toBool()) return $output2;
        }

        $returnUrl = getNotEncodedUrl('', 'mid', $this->mid, 'document_srl', $output->get('document_srl'));
        $this->setRedirectUrl($returnUrl);

    }

    function procPoliceJudge()
    {
        if (!$this->grant->write_judge) {
            return new Object(-1, 'msg_not_permitted');
        }

        $obj = Context::getRequestVars();

        $oPoliceModel = &getModel('m16_police');
        $output2 = $oPoliceModel->getReportContent($obj);

        $logged_info = Context::get('logged_info');

        $args = new stdClass();
        $args->module_srl = $this->module_srl;
        $args->nick_name = $logged_info->nick_name;
        $args->title = "M16 신고센터 2.0 처리";
        $args->content = Context::get('content');

        // document 모듈에 등록
        $oDocumentController = getController('document');
        $output = $oDocumentController->insertDocument($args);

        if (!$output->get('document_srl')) {
            return new Object(-1, 'invalid_request');
        }

        $obj->police_srl = Context::get('police_srl');
        $obj->nick_judge_admin = $logged_info->nick_name;
        $obj->judge_admin_srl = $logged_info->member_srl;
        $obj->judge_srl = $output->get('document_srl');
        $obj->already_judge = 'Y';

        executeQuery("m16_police.updateReport", $obj);

        $admin_srl = $logged_info->member_srl;
        $reporter_srl = $output2->reporter_srl;


        // 처리 성공 시
        if ($obj->judge_status == 1 && $output2->already_judge == 'N') {
            // 신고처리 성공 시 운영진, 유저에게 포인트 지급
            $add_point_value = 100; //TODO: 운영진 / 유저 추가 지급 포인트

            $oPointController = &getController('point');
            $oPointController->setPoint($admin_srl, $add_point_value, 'add');
            $oPointController->setPoint($reporter_srl, $add_point_value, 'add');
        }

        $returnUrl = getNotEncodedUrl('', 'mid', $this->mid, 'police_srl', $output2->police_srl);

        $oCommunicationController = &getController('communication');

        $msg_title = "접수하신 사건번호 #" . $output2->police_srl . "의 신고처리가 완료되었습니다.";
        $msg_content = "접수하신 사건번호 #" . $output2->police_srl . "의 신고처리가 완료되었습니다.<br /> <a href=" . $returnUrl . ">신고 보기</a>";
        $output4 = $oCommunicationController->sendMessage($admin_srl, $reporter_srl, $msg_title, $msg_content);

        $this->setRedirectUrl($returnUrl);

    }

}