<?php

/**
 * User: DingGGu
 * Date: 2014-12-23
 */
class m16_policeModel extends m16_police
{

    function init()
    {
    }

    function getReportList($args)
    {
        $search_keyword = trim(Context::get('search_keyword'));
        $search_target = Context::get('search_target');
        $search_target_list = array("s_judge_ip_address", "s_nick_reporter", "s_nick_criminal", "s_nick_judge_admin");

        if ($search_keyword && in_array($search_target, $search_target_list)) {
            $args->{$search_target} = $search_keyword;
        }

        $output = executeQuery('m16_police.getReportList', $args);
        return $output;
    }

    function getReportChart()
    {
        $output = executeQuery('m16_police.getReportChart');

        return $output;
    }

    function getReportContent($args)
    {
        $output = executeQuery('m16_police.getReportContent', $args);
        return $output->data;
    }

    function triggerModuleListInSitemap(&$arr)
    {
        array_push($arr, 'm16_police');
    }
}
