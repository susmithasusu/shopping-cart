<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "display".
 *
 * @property integer $id
 * @property string $book_name
 * @property string $author_name
 * @property string $discription
 * @property integer $price
 * @property string $languages
 */
class Orders extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'product_id','count','flag'], 'required'],
            [['order_id'], 'integer'],
         
           
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
