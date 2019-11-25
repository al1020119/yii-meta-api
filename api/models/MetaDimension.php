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

}
