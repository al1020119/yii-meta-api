<?php
namespace api\models;

use yii\db\ActiveRecord;

class MetaDatabase extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%database}}';
    }

}
