<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "line_station".
 *
 * @property integer $line_station_id
 * @property integer $line_station_line_id
 * @property integer $line_station_station_id
 * @property string $line_station_created_at
 * @property string $line_station_updated_at
 */
class LineStation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'line_station';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('testDB');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['line_station_line_id', 'line_station_station_id'], 'required'],
            [['line_station_line_id', 'line_station_station_id'], 'integer'],
            [['line_station_created_at', 'line_station_updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'line_station_id' => 'Line Station ID',
            'line_station_line_id' => 'line id',
            'line_station_station_id' => 'station id',
            'line_station_created_at' => 'create at',
            'line_station_updated_at' => 'update at',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'line_station_created_at',
                'updatedAtAttribute' => 'line_station_updated_at',
                'value' => function ($event) {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }

    public function add($lineID, $stationID)
    {
        $this->line_station_line_id = $lineID;
        $this->line_station_station_id = $stationID;
        $this->save();
        return $this->line_station_id;
    }

    public function getIDByLineStationID($lineID, $stationID)
    {
        $data = $this->find()
            ->select('line_station_id')
            ->asArray()
            ->where([
                'line_station_line_id' => $lineID,
                'line_station_station_id' => $stationID,
            ])
            ->one();
        return isset($data['line_station_id']) ? $data['line_station_id'] : 0;
    }
}
