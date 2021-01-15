<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "station".
 *
 * @property integer $station_id
 * @property string $station_name
 * @property string $station_created_at
 * @property string $station_updated_at
 */
class Station extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'station';
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
            [['station_name'], 'required'],
            [['station_created_at', 'station_updated_at'], 'safe'],
            [['station_name'], 'string', 'max' => 32],
            [['station_name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'station_id' => 'Station ID',
            'station_name' => 'station name',
            'station_created_at' => 'create at',
            'station_updated_at' => 'update at',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'station_created_at',
                'updatedAtAttribute' => 'station_updated_at',
                'value' => function ($event) {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }

    public function add($stationName)
    {
        $this->station_name = $stationName;
        $this->save();
        return $this->station_id;
    }

    public function getStationIDByName($stationName)
    {
        $data = $this->find()
            ->select('station_id')
            ->asArray()
            ->where([
                'station_name' => $stationName
            ])
            ->one();
        return isset($data['station_id']) ? $data['station_id'] : 0;
    }
}
