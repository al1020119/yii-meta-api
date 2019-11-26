<?php
namespace api\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Meta model
 *
 * @property integer $id
 * @property string  $source_type
 * @property integer $db_name
 * @property string  $table_name
 * @property string  $table_desc
 * @property string  $field_name
 * @property string  $field_type
 * @property string  $is_dimension
 * @property integer $dimension_table
 * @property integer $is_null
 * @property integer $key
 * @property integer $default
 * @property integer $extra
 * @property string  $privileges
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $comment
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
