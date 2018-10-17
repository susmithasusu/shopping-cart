<?php

namespace backend\controllers;
use yii\filters\AccessControl;
use common\models\Product;
use backend\behaviours\Verbcheck;
use backend\behaviours\Apiauth;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\models\Category;
use yii\web\UploadedFile;
use common\models\Orders;
use common\models\Customer;

use Yii;

class ProductController extends RestController
{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors + [

           'apiauth' => [
               'class' => Apiauth::className(),
               'exclude' => ['view','create','index','delete','products','categories','list'],
               'callback'=>[]
           ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'index'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['*'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => Verbcheck::className(),
                'actions' => [
                    'index' => ['GET', 'POST'],
                    'create' => ['POST'],
                    'update' => ['PUT'],
                    'view' => ['GET'],
                    'delete' => ['DELETE']
                ],
            ],

        ];
    }

    public function actionIndex()
    {
        $params = $this->request['search'];
        $response = Product::search($params);
        Yii::$app->api->sendSuccessResponse($response['data'], $response['info']);
    }

    public function actionCreate()
    {
        
        $model = new Product;
        
        $category=Category::find(['category_id'])->where(['category_name' =>$this->request['category']])->one();
       
            $cat_id= $category['category_id'];
           
    //    $image=UploadedFile::getInstanceByName($this->request['image']);
    
        $model->category =$cat_id;
        $model->name=$this->request['name'];
        $model->image=$this->request['image'];
        $model->description=$this->request['description'];
        $model->price=$this->request['price'];
        $model->count=$this->request['count'];


        if ($model->save()) {
            // $image->saveAs('uploads/'.$model->image);
            Yii::$app->api->sendSuccessResponse($model->attributes);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
        }

    }

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);
        $model->attributes = $this->request;

        if ($model->save()) {
            Yii::$app->api->sendSuccessResponse($model->attributes);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
        }

    }

    public function actionView($id)
    {

        $model = $this->findModel($id);
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }

    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }

    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            $model1=Product::find()->where(['id'=>$id])->all();
            foreach($model1 as $row)
            {
                $category=Category::find()->where(['category_id' =>$row['category']])->all();
                 foreach($category as $ca)
                 {
                    $name=$ca['category_name'];
                    $model['category']=$name;
                
                }
                
            }
            return $model;
        } else {
            Yii::$app->api->sendFailedResponse("Invalid Record requested");
        }
    }
    
    public function actionProducts($category)
    {

         $model=Category::find()->where(['category_name'=>$category])->all();
          foreach($model as $row)
         {
         $name=$row['category_id'];
       
         }
         $model=Product::find()->where(['category'=>$name])->all();
         $i=0;
         foreach($model as $row)
         {
             $category=Category::find()->where(['category_id' =>$row['category']])->all();
              foreach($category as $ca)
              {
                 $name=$ca['category_name'];
                 $model[$i]['category']=$name;
                 $i=$i+1;
             
             }
             
         }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $model;
    
    }
   
    public function actionCategories()
    {
            $model = Category::find()
            ->select('category_name')
            ->from('category')
            ->all();
            $model1 = ArrayHelper::getColumn($model, 'category_name');

       \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
       return $model1;
     }
    public function actionOrder()
    {
        $model = new Customer();
        $model1=new Order();
     
        $model->attributes = $this->request;

    }
    public function actionList($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $category=Orders::find()->where(['customer_id' =>$id])->all();
        $query=array();
        foreach($category as $product){
            $model = Product::findOne($product->product_id);
            $category=Category::find(['category_name'])->where(['category_id' =>$model['category']])->one();
            
            $name=$category['category_name'];
            $model['category']=$name;
            $query[]=$model;
            $query++;
         
        }
          
         return $query;
    }

}