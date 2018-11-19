<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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
class Product extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $flag;
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'name', 'image', 'description','price','count'], 'required'],
            [[ 'name', 'image', 'description'], 'string', 'max' => 200],
            [['price','count','category'], 'integer'],
           
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [      
            'id' => 'ID',
            'category' => 'category',
            'name' => 'Name',
            'description' => 'Description',
            'price' => 'Price',
            'count'=>'Count',
            'flag'=>'Flag',
            
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
        $query = Product::find()
            ->select(['id', 'name', 'category', 'price', 'description','image','count'])
            ->asArray(true)
            ->limit($limit)
            ->offset($offset);
            $i=0;
            $query1=$query->all();
          
            foreach($query1 as $row)
            {
                 $category=Category::find(['category_name'])->where(['id' =>$row['category']])->all();
                 foreach($category as $ca)
                 {
                    $name=$ca->category_name;
                    $query1[$i]['category']=$name;
                    $query1[$i]['image']=Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'.$query1[$i]['image'];
                    $i=$i+1;
               
                 }
            }
            $data = (object) $query1;
            // print_r($data);
            // exit();
            if(isset($params['name'])) {
                $data->andFilterWhere(['name' => $params['name']]);
            }
        //    if(isset($order)){ 
        //     $data->orderBy($order);
        //    }
        $additional_info = [
            'page' => $page,
            'size' => $limit,
             'totalCount' =>(int)$query->count()
        ];

        return [
            'data' =>  $query1,
            'info' => $additional_info
        ];
    }
}
