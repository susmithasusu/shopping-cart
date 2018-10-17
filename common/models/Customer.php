<?php

namespace common\models;

use Yii;


class Customer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'name', 'email','phone'], 'required'],
            [['address', 'name','phone'], 'string', 'max' => 200],
   
           
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            // 'id' => 'ID',
            // 'category' => 'category',
            // 'name' => 'Name',
            // 'description' => 'Description',
            // 'price' => 'Price',
            
        ];
    }
}
