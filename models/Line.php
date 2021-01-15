<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "line".
 *
 * @property integer $line_id
 * @property string $line_name
 * @property string $line_created_at
 * @property string $line_updated_at
 */
class Line extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'line';
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
            [['line_name'], 'required'],
            [['line_created_at', 'line_updated_at'], 'safe'],
            [['line_name'], 'string', 'max' => 8],
            [['line_name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'line_id' => 'Line ID',
            'line_name' => 'line name',
            'line_created_at' => 'create at',
            'line_updated_at' => 'update at',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'line_created_at',
                'updatedAtAttribute' => 'line_updated_at',
                'value' => function ($event) {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }

    public function add($lineName)
    {
        $this->line_name = $lineName;
        $this->save();
        return $this->line_id;
    }

    public function getLineIDByName($lineName)
    {
        $data = $this->find()
            ->select('line_id')
            ->asArray()
            ->where([
                'line_name' => $lineName
            ])
            ->one();
        return isset($data['line_id']) ? $data['line_id'] : 0;
    }
}
