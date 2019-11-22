<?php
namespace api\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * User model
 *
 * @property integer $id
 * @property string  $db_name
 * @property integer $table_name
 * @property string  $table_desc
 * @property string  $field_name
 * @property string  $field_desc
 * @property string  $field_type
 * @property string  $field_value
 * @property integer $field_value_desc
 * @property integer $source_type
 * @property integer $status
 * @property integer $created_by
 * @property integer $created_at
 * @property string  $updated_by
 * @property integer $updated_at
 * @property integer $is_dimension
 * @property integer $dimension_table
 * @property string  $comment
 */
class MetaDatabase extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%database}}';
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
