<?php

namespace app\commands;

use app\models\Ty;
use app\models\Util;
use yii\console\Controller;

/**
 * Class TianyaController
 * @package app\commands
 *
 *
 * CREATE TABLE `ty` (
`ty_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`ty_floor` int(11) unsigned NOT NULL COMMENT 'floor',
`ty_type` tinyint(3) unsigned DEFAULT '1' COMMENT '0-not lz 1-lz',
`ty_reply` text COMMENT 'reply',
`ty_reply_floor` text COMMENT 'reply floor',
PRIMARY KEY (`ty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ty article';
 */
class TianyaController extends Controller
{
    public function actionT()
    {
        $list = [
        ];
        foreach ($list as $url) {
            echo $url . ' started'  .PHP_EOL;
            $this->runCrawler($url);
            echo $url . ' finished'  .PHP_EOL;
        }
    }

    private function runCrawler($url)
    {
        $crawlUrl = str_replace('{page}', 1, $url);
        $content = $this->curlGet($crawlUrl);
        $totalPage = $this->getTotalPage($content);
        if ($totalPage == -1) {
            echo 'fetch total page failed.' . PHP_EOL;
        }
        $title = $this->getTitle($content);
        if ($title == '') {
            $title = time();
        }

        $path = './temp/' . $title . '.txt';
        $handle = fopen($path, 'a+');

        for ($currentPage = 1; $currentPage <= $totalPage; $currentPage++) {
            $crawlUrl = str_replace('{page}', $currentPage, $url);
            $content = $this->curlGet($crawlUrl);
            /*fwrite($handle, str_repeat('===', 30) . PHP_EOL);
            if ($currentPage == 1) {
                preg_match_all('/<div class="item item-zt item-lz"[^>]+>.*?<div class="bd">(.*?)<\/div>/s', $content, $matches);
                foreach ($matches[1] as $matchContent) {
                    $arr = explode('</p>', trim($matchContent));
                    foreach ($arr as $value) {
                        fwrite($handle, str_replace(['<p>', '</p>'], '', $value) . PHP_EOL);
                    }
                }
            }
            preg_match_all('/<div class="item item-ht item-lz"[^>]+>.*?<div class="reply-div">(.*?)<\/div>/s', $content, $matches);
            foreach ($matches[1] as $matchContent) {
                $arr = explode('</p>', trim($matchContent));
                foreach ($arr as $value) {
                    fwrite($handle, str_replace(['<p>', '</p>'], '', $value) . PHP_EOL);
                }
            }*/
            //write to db
            preg_match_all('/<div class="item item-ht([^"]+)"([^>]+)>.*?<div class="reply-div">(.*?)<\/div>/s', $content, $matches);
            foreach ($matches[1] as $idx => $matchContent) {
                $isLz = (strpos($matchContent, 'item-lz') !== false) ? 1 : 0;
                preg_match('/data-id="(\d+)"/', $matches[2][$idx], $floorMatches);
                $floor = (isset($floorMatches[1])) ? $floorMatches[1] : 0;
                $arr = explode('</p>', trim($matches[3][$idx]));
                $replyContent = [];
                foreach ($arr as $value) {
                    $tempContent = str_replace(['<p>', '</p>'], '', $value);
                    if ($tempContent != '') {
                        $replyContent[] = $tempContent;
                    }
                }
                $mTy = new Ty();
                $mTy->ty_floor = $floor;
                $mTy->ty_type = $isLz;
                $mTy->ty_reply = implode("\r\n", $replyContent);
                $mTy->ty_reply_floor = '';
                $mTy->save();
            }
            echo 'page ' . $crawlUrl . PHP_EOL;
        }
        fclose($handle);
    }

    private function getTotalPage($pageContent)
    {
        preg_match('%<a class="u-btn last-btn" href="/m/post-house-\d+-(\d+)\.shtml">%', $pageContent, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        return -1;
    }

    private function getTitle($pageContent)
    {
        preg_match('%<div class="title">.*?<h1>(.*?)</h1>%s', $pageContent, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        return '';
    }

    private function curlGet($url, $params = [])
    {
        $query = http_build_query($params);
        if (!empty($query)) {
            $ch = curl_init($url . '?' . $query);
        } else {
            $ch = curl_init($url);
        }
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        //curl的额外参数
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
 