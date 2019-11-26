<?php
namespace api\models;

use yii\db\ActiveRecord;

/**
 * Dimension model
 *
 * @property integer $id
 * @property string  $table_name
 * @property integer $table_desc
 * @property string  $dimension_name
 * @property string  $dimension_desc
 * @property string  $dimension_value
 * @property string  $dimension_value_desc
 * @property string  $source_db
 * @property integer $source_table
 * @property integer $source_field
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property string  $updated_at
 * @property integer $comment
 */
class MetaDimension extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dimension}}';
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
