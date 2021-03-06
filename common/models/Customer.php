<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "employee".
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 */
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
            [['address','name', 'email','phone'], 'required'],
            // [['email'],'email','message'=>"Please enter a valid email"],
            // [['email'],'unique'],
            [['name','email','address'], 'string', 'max' => 200],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            // 'id' => 'ID',
            // 'name' => 'Name',
            // 'email' => 'Email',
            // 'created_at' => 'Created At',
            // 'updated_at' => 'Updated At',
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

        $query =Customer::find()
            ->select(['id', 'address','name', 'email', 'phone','flag'])
            ->asArray(true)
            ->limit($limit)
            ->offset($offset);

      
            if(isset($params['email'])){
                $query->andFilterWhere(['like', 'email', $params['email']]);
            }
            if(isset($params['name'])){
                $query->andFilterWhere(['like', 'name', $params['name']]);
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
    static public function search_email($params)
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

        $query = Customer::find()
            ->select([ 'email'])
            ->asArray(true)
            ->limit($limit)
            ->offset($offset);

        if(isset($params['email'])){
            $query->andFilterWhere(['like', 'email', $params['email']]);
        }
        if(isset($params['name'])){
            $query->andFilterWhere(['like', 'name', $params['name']]);
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
}
