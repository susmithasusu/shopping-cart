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
class Total extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'total';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'total'], 'required'],
         
           
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
    static public function search($params)
    {

        $page = Yii::$app->getRequest()->getQueryParam('page');
        $limit = Yii::$app->getRequest()->getQueryParam('limit');
        $order = Yii::$app->getRequest()->getQueryParam('order');

        $search = Yii::$app->getRequest()->getQueryParam('search');

        if(isset($search)){
            $params=$search;
        }

        $limit = isset($limit) ? $limit : 10;
        $page = isset($page) ? $page : 1;
        $offset = ($page - 1) * $limit;
        $query = Total::find()
            ->select(['id', 'order_id','email', 'delivery_address','total','created_at','delivery_at','flag','total_quantity'])
            ->asArray(true)
            ->limit($limit)
            ->offset($offset);

    

        if(isset($params['created_at'])) {
            $query->andFilterWhere(['like','created_at', $params['created_at']]);
        }
        if(isset($params['email'])) {
            $query->andFilterWhere(['like','email', $params['email']]);
        }
        if(isset($params['order_id'])) {
            $query->andFilterWhere(['like', 'order_id', $params['order_id']]);
        }
        if(isset($params['delivery_address'])){
            $query->andFilterWhere(['like', 'delivery_address', $params['delivery_address']]);
        }
        if(isset($params['flag'])){
            $query->andFilterWhere(['like', 'flag', $params['flag']]);
        }
        if(isset($params['delivery_at'])){
            $query->andFilterWhere(['like', 'delivery_at', $params['delivery_at']]);
        }


        if(isset($order)){
            $query->orderBy($order);
        }


        $additional_info = [
            'page' => $page,
            'size' => $limit,
            'totalCount' => (int)$query->count()
        ];

        return [
            'data' => $query->all(),
            'info' => $additional_info
        ];
    }

    public function beforeSave($insert)
    {

        if (parent::beforeSave($insert)) {

            if ($this->isNewRecord) {
                $this->created_at = date("Y-m-d H:i:s", time());
                $this->updated_at = date("Y-m-d H:i:s", time());

            } else {

                $this->updated_at = date("Y-m-d H:i:s", time());
            }
            return true;
        } else {
            return false;
        }


    }
}
