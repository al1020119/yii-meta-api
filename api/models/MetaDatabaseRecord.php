<?php
namespace api\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Meta model
 *
 * @property integer $id
 * @property string  $row_id
 * @property integer $type
 * @property string  $content
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $created_by
 */
class MetaDatabaseRecord extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%database_record}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',// 自己根据数据库字段修改
                'updatedAtAttribute' => 'updated_at', // 自己根据数据库字段修改, // 自己根据数据库字段修改
                'value' => function(){return date('Y-m-d H:i:s',time()+8*60*60);},
            ],
        ];
    }

}
