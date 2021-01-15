<?php

namespace app\commands;

use app\models\Util;
use app\models\Line;
use app\models\LineStation;
use app\models\Station;
use yii\console\Controller;
use Yii;

class MetroController extends Controller
{
    public function actionLoadData()
    {
        $url = 'http://m.shmetro.com/core/shmetro/mdstationinfoback_new.ashx?act=getAllStations';
        $data = Util::curlGet($url);
        $data = json_decode($data, true);
        $lines = [];
        foreach ($data as $value) {
            $lineName = intval(substr($value['key'], 0, 2));
            if (!isset($lines[$lineName])) {
                $lines[$lineName] = [];
            }
            $lines[$lineName][] = $value['value'];
            $lineID = (new Line())->getLineIDByName($lineName);
            $stationID = (new Station())->getStationIDByName($value['value']);
            if ($lineID == 0) {
                $lineID = (new Line())->add($lineName);
            }
            if ($stationID == 0) {
                $stationID = (new Station())->add($value['value']);
            }
            $lineStationID = (new LineStation())->getIDByLineStationID($lineID, $stationID);
            if ($lineStationID == 0) {
                (new LineStation())->add($lineID, $stationID);
            }
        }
        print_r($data); exit;
    }

    public function actionT()
    {
        //
    }
}
 