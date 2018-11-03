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
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_name'], 'required'],
           
            [['category_name'], 'string', 'max' => 200],
          
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
     
            'category_name' => 'Category Name',
           
           
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

        $query =Category::find()
            ->select(['id', 'category_name'])
            ->asArray(true)
            ->limit($limit)
            ->offset($offset);

        // if(isset($params['id'])) {
        //     $query->andFilterWhere(['id' => $params['id']]);
        // }

        // if(isset($params['created_at'])) {
        //     $query->andFilterWhere(['created_at' => $params['created_at']]);
        // }
        // if(isset($params['updated_at'])) {
        //     $query->andFilterWhere(['updated_at' => $params['updated_at']]);
        // }
        if(isset($params['category_name'])) {
            $query->andFilterWhere(['like', 'category_name', $params['category_name']]);
        }
        // if(isset($params['email'])){
        //     $query->andFilterWhere(['like', 'email', $params['email']]);
        // }


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
