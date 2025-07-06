<?php

namespace app\commands;


use app\models\Util;
use yii\console\Controller;
use yii\db\Connection;

class ZhihuController extends Controller
{
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
                $questionTitle = urldecode($questionTitle);
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
                    continue;
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

    public function actionDoV1()
    {
        $localDb = \Yii::$app->local_db;
        if (!($localDb instanceof Connection)) {
            return;
        }
        $url = 'https://www.zhihu.com/explore';
        $loopCnt = 20;
        while ($loopCnt) {
            $headers = [
                'User-Agent:Baiduspider'
            ];
            $htmlData = Util::curlGet($url, [], $headers);
            $flag = preg_match('/(\{"initialState.*"use_cached_supported_countries":"1"}})<\\/script/', $htmlData, $matches);
            if ($flag) {
                $decode = json_decode($matches[1], true);
                //var_dump(json_last_error());
                $decodeData = ($decode['initialState']['explore']['collections']);
            }
            if (!isset($decodeData)) {
                continue;
            }

            foreach ($decodeData as $value) {
                if (!isset($value['favitems'])) {
                    continue;
                }
                $itemCnt = count($value['favitems']);//default have 2 items
                for ($i = 0; $i < $itemCnt; $i++) {
                    if (!isset($value['favitems'][$i]['content'])) {
                        continue;
                    }
                    if (isset($value['favitems'][$i]['content']['question'])) {
                        $questionTitle = $value['favitems'][$i]['content']['question']['title'];
                    } else {
                        $questionTitle = $value['favitems'][$i]['content']['title'];
                    }
                    $questionTitle = urldecode($questionTitle);
                    $defaultUrl = $value['favitems'][$i]['content']['url'];
                    if (strpos($defaultUrl, 'answers') !== false) {
                        $answerUrl = 'https://www.zhihu.com/answer/' . $value['favitems'][$i]['content']['id'];
                    } else {
                        $answerUrl = $defaultUrl;
                    }
                    $abbrContent = '';
                    $content = '';
                    if (isset($value['favitems'][$i]['content']['excerpt'])) {
                        $abbrContent = $value['favitems'][$i]['content']['excerpt'];
                    }
                    if (isset($value['favitems'][$i]['content']['content'])) {
                        $content = ($value['favitems'][$i]['content']['content']);
                    }
                    $data = $localDb->createCommand("select id from zhihu_hot_collection where answer_url='{$answerUrl}'")->queryOne();
                    if (empty($data)) {
                        $localDb->createCommand()->insert('zhihu_hot_collection',
                            ['title' => $questionTitle, 'abbr_answer' => $abbrContent, 'content' => $content, 'answer_url' => $answerUrl,
                                'create_time' => date('Y-m-d H:i:s')])
                            ->execute();
                    } else {
                        continue;
                        $localDb->createCommand()->update('zhihu_hot_collection',
                            ['title' => $questionTitle, 'abbr_answer' => $abbrContent, 'content' => $content, 'answer_url' => $answerUrl],
                            ['answer_url' => $answerUrl])
                            ->execute();
                    }
                }

            }
            /*if (isset($decodeData['paging']['is_end']) && $decodeData['paging']['is_end']) {
                break;
            }*/
            $loopCnt--;
        }
    }

    public function actionDoSalt($all = false)
    {
        $localDb = \Yii::$app->local_db;
        $yesterdayStartStamp = strtotime(date('Y-m-d')) - 86400;
        //
        $regexPage = '/\/page\/(\d+)\/#board/';
        $regexUrl = '/<a\s+href="([^"]+)"\s+target="_self">([^<]+)<\/a>/';
        $regPublish = '/<time\s+datetime="([^"]+)"\s+pubdate>/';
        $regDesc = '/<meta name="description" content="([^"]+)">/';
        $regContent = '/<div\s+class="markdown-body">(.*?)<\/div>/s';
        $url = 'https://onehu.xyz';
        $headers = [
            'User-Agent:Baiduspider'
        ];
        $content = Util::curlGet($url, [], $headers);
        $isPage = preg_match_all($regexPage, $content, $pageMatches);
        if (!$isPage) {
            return;
        }
        $maxPage = max($pageMatches[1]);
        for ($page = 1; $page <= $maxPage; $page++) {
            echo $page . PHP_EOL;
            if ($page != 1) {
                $url = 'https://onehu.xyz/page/' . $page;
                $content = Util::curlGet($url);
            }
            $isUrl = preg_match_all($regexUrl, $content, $urlMatches);
            if (!$isUrl) {
                break;
            }
            $isPublish = preg_match_all($regPublish, $content, $publishMatches);
            foreach ($urlMatches[1] as $idx => $urlVal) {
                if (!$all && strtotime($publishMatches[1][$idx]) < $yesterdayStartStamp) {
                    break 2;
                }
                $title = str_replace("\n", '', $urlMatches[2][$idx]);
                $originUrl = 'https://onehu.xyz'. $urlVal;
                $url = urldecode($originUrl);
                $data = $localDb->createCommand("select id from zhihu_salt where answer_url='{$url}'")->queryOne();
                if (!empty($data)) {
                    continue;
                }

                $detailContent = Util::curlGet($originUrl, [], $headers);
                preg_match($regContent, $detailContent, $contentMatches);
                preg_match($regDesc, $detailContent, $descMatches);
                //echo $title . "\t\"" . $url . "\"" . PHP_EOL;
                if (isset($descMatches[1]) && isset($contentMatches[1])) {
                    $filterContent = str_replace(['<p>.<img src="/../1.png" srcset="/img/loading.gif" lazyload alt="公号" title="公号"></p>',
                        '<center>关注不迷路~</center>'], ['', ''], $contentMatches[1]);
                    $localDb->createCommand()->insert('zhihu_salt',
                        ['title' => $title, 'abbr_answer' => $descMatches[1], 'content' => $filterContent, 'answer_url' => $url,
                            'create_time' => date('Y-m-d H:i:s')])
                        ->execute();
                } else {
                    echo $title . "\t\"" . $url . "\"" . $detailContent . PHP_EOL;
                }
            }
        }
    }

    public function actionDoSaltEmptyContent()
    {
        $localDb = \Yii::$app->local_db;
        $data = $localDb->createCommand("select id, answer_url from zhihu_salt where abbr_answer=''")->queryAll();
        if (empty($data)) {
            return;
        }

        $headers = [
            'User-Agent:Baiduspider'
        ];
        $regDesc = '/<meta name="description" content="([^"]+)">/';
        $regContent = '/<div\s+class="markdown-body">(.*?)<\/div>/s';

        foreach ($data as $key => $value) {
            echo $value['id'] . PHP_EOL;
            $url = $value['answer_url'];
            $detailContent = Util::curlGet($url, [], $headers);
            preg_match($regContent, $detailContent, $contentMatches);
            preg_match($regDesc, $detailContent, $descMatches);
            //echo $title . "\t\"" . $url . "\"" . PHP_EOL;
            if (isset($descMatches[1]) && isset($contentMatches[1])) {
                $filterContent = str_replace(['<p>.<img src="/../1.png" srcset="/img/loading.gif" lazyload alt="公号" title="公号"></p>',
                    '<center>关注不迷路~</center>'], ['', ''], $contentMatches[1]);
                $localDb->createCommand()->update('zhihu_salt',
                    ['abbr_answer' => $descMatches[1], 'content' => $filterContent],
                    ['id' => $value['id']])
                    ->execute();
            } else {
                echo "\t\"" . $url . "\"" . $detailContent . PHP_EOL;
            }
        }

    }

    public function actionDoSaltAnother($all = false)
    {
        $localDb = \Yii::$app->local_db;
        $regexPage = "/<span class=\"current\-page\">\d\s+\/\s+(\d+)<\/span>/";//
        $regexUrl = '/<a href="(\/post[^"]+)">([^<]+)<\/a>/';
        $regContent = '/<div class="post\-content">(.*?)<\/div>/';
        $regDesc = "/<meta name='description' content=\"([^\"]+)\">/";
        $url = 'https://www.chneye.com/all';
        $headers = [
            'User-Agent:Baiduspider'
        ];
        $content = Util::curlGet($url, [], $headers);
        if (!$content) {
            echo 'Get content failed.' . PHP_EOL;
        }
        $isPage = preg_match_all($regexPage, $content, $pageMatches);
        if (!$isPage) {
            return;
        }
        $maxPage = intval($pageMatches[1]);
        for ($page = 1; $page <= $maxPage; $page++) {
            echo $page . PHP_EOL;
            if ($page != 1) {
                $url = 'https://www.chneye.com/all/?page=' . $page;
                $content = Util::curlGet($url);
            }
            $isUrl = preg_match_all($regexUrl, $content, $urlMatches);
            if (!$isUrl) {
                break;
            }
            foreach ($urlMatches[1] as $idx => $urlVal) {
                /*if (!$all && strtotime($publishMatches[1][$idx]) < $yesterdayStartStamp) {
                    break 2;
                }*/
                $title = str_replace("\n", '', $urlMatches[2][$idx]);
                $originUrl = 'https://www.chneye.com'. $urlVal;
                $url = urldecode($originUrl);
                $data = $localDb->createCommand("select id from zhihu_salt_test where answer_url='{$url}'")->queryOne();
                if (!empty($data)) {
                    continue;
                }

                $detailContent = Util::curlGet($originUrl, [], $headers);
                preg_match($regContent, $detailContent, $contentMatches);
                preg_match($regDesc, $detailContent, $descMatches);
                //echo $title . "\t\"" . $url . "\"" . PHP_EOL;
                if (isset($descMatches[1]) && isset($contentMatches[1])) {
                    $filterContent = $contentMatches[1];
                    $localDb->createCommand()->insert('zhihu_salt_test',
                        ['title' => $title, 'abbr_answer' => $descMatches[1], 'content' => $filterContent, 'answer_url' => $url,
                            'create_time' => date('Y-m-d H:i:s')])
                        ->execute();
                } else {
                    echo $title . "\t\"" . $url . "\"" . $detailContent . PHP_EOL;
                }
            }
        }
    }
}