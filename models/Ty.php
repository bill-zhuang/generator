<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ty".
 *
 * @property integer $ty_id
 * @property integer $ty_floor
 * @property integer $ty_type
 * @property string $ty_reply
 * @property string $ty_reply_floor
 */
class Ty extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ty';
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
            [['ty_floor'], 'required'],
            [['ty_floor', 'ty_type'], 'integer'],
            [['ty_reply', 'ty_reply_floor'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ty_id' => 'Ty ID',
            'ty_floor' => 'floor',
            'ty_type' => '0-not lz 1-lz',
            'ty_reply' => 'reply',
            'ty_reply_floor' => 'reply floor',
        ];
    }
}
