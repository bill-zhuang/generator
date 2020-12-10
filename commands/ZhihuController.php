<?php

namespace app\commands;


use app\models\Util;
use yii\console\Controller;
use yii\db\Connection;

class ZhihuController extends Controller
{
    //CREATE TABLE `zhihu_hot_collection` (
    //  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
    //  `title` varchar(256) NOT NULL DEFAULT '' COMMENT '标题',
    //  `abbr_answer` varchar(1024) NOT NULL DEFAULT '' COMMENT 'abbr answer',
    //  `answer_url` varchar(128) NOT NULL DEFAULT '' COMMENT '回答url',
    //  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1-有效；2-无效',
    //  `create_time` datetime DEFAULT NULL,
    //  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    //  PRIMARY KEY (`id`) USING BTREE,
    //  KEY `idx_answer_url` (`answer_url`)
    //) ENGINE=InnoDB AUTO_INCREMENT=815 DEFAULT CHARSET=utf8 COMMENT='知乎热门收藏表';
    public function actionDo()
    {
        $localDb = \Yii::$app->local_db;
        if (!($localDb instanceof Connection)) {
            return;
        }
        $offset = 0;
        $url = 'https://www.zhihu.com/api/v4/favlists/discover?limit=10&offset=';
        //echo 'Offset: ';
        while (true) {
            //echo $offset . ' ';
            $currentUrl = $url . $offset;
            $headers = [
                'User-Agent:Baiduspider'
            ];
            $jsonData = Util::curlGet($currentUrl, [], $headers);
            $decodeData = json_decode($jsonData, true);
            if (!isset($decodeData['data']) || empty($decodeData['data'])) {
                break;
            }
            foreach ($decodeData['data'] as $value) {
                if (!isset($value['favitems'][0]['content'])) {
                    break;
                }
                if (isset($value['favitems'][0]['content']['question'])) {
                    $questionTitle = $value['favitems'][0]['content']['question']['title'];
                } else {
                    $questionTitle = $value['favitems'][0]['content']['title'];
                }
                $defaultUrl = $value['favitems'][0]['content']['url'];
                if (strpos($defaultUrl, 'answers') !== false) {
                    $answerUrl = 'https://www.zhihu.com/answer/' . $value['favitems'][0]['content']['id'];
                } else {
                    $answerUrl = $defaultUrl;
                }
                $abbrContent = '';
                $content = '';
                if (isset($value['favitems'][0]['content']['excerpt'])) {
                    $abbrContent = $value['favitems'][0]['content']['excerpt'];
                }
                if (isset($value['favitems'][0]['content']['content'])) {
                    $content = ($value['favitems'][0]['content']['content']);
                }
                $data = $localDb->createCommand("select id from zhihu_hot_collection where answer_url='{$answerUrl}'")->queryOne();
                if (empty($data)) {
                    $localDb->createCommand()->insert('zhihu_hot_collection',
                        ['title' => $questionTitle, 'abbr_answer' => $abbrContent, 'content' => $content, 'answer_url' => $answerUrl,
                            'create_time' => date('Y-m-d H:i:s')])
                        ->execute();
                } else {
                    $localDb->createCommand()->update('zhihu_hot_collection',
                        ['title' => $questionTitle, 'abbr_answer' => $abbrContent, 'content' => $content, 'answer_url' => $answerUrl],
                        ['answer_url' => $answerUrl])
                        ->execute();
                }
            }
            if (isset($decodeData['paging']['is_end']) && $decodeData['paging']['is_end']) {
                break;
            }
            $offset += 10;
        }
    }
}