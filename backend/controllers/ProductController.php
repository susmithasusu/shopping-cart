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
use common\models\Total;

use Yii;

class ProductController extends RestController
{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors + [

           'apiauth' => [
               'class' => Apiauth::className(),
               'exclude' => ['view','create','index','delete','products','categories','list_customer','view_customer',
               'list','category_adding','customer_adding','update','delete','update_product','delete_product','cancel_order','delete_customer','list_category'],
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
                    'create','category_adding' => ['POST'],
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
        $params = Yii::$app->request->post();
        if($params){
        
        $category=Category::find(['id'])->where(['category_name' =>$params['category']])->one();
       
            $cat_id= $category['id'];
          
           
       $image=UploadedFile::getInstanceByName('image');
    //    print_r($image);
    //    exit();
      
       $imgName='img_'.$params['name'] .'.'.$image->getExtension();
          
       $image->saveAs(Yii::getAlias('@uploadsImgPath').'/'.$imgName);
     
        $model->category =$cat_id;
        $model->name=$params['name'];
        $model->image=$imgName;
        $model->description=$params['description'];
        $model->price=$params['price'];
        $model->count=$params['count'];

        if ($model->save()) {
          
            Yii::$app->api->sendSuccessResponse($model->attributes);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
        }
    }
    }

    

    public function actionUpdate($id)
    {

        $model = $this->findModel1($id);
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

        $model = $this->findModel1($id);
        $model->delete();
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
    protected function findmodel1($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        } else {
            Yii::$app->api->sendFailedResponse("Invalid Record requested");
        }
    }
    public function actionDelete_product($id)
    {

        $model = $this->findModel2($id);
        $model->delete();
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
    protected function findmodel2($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            Yii::$app->api->sendFailedResponse("Invalid Record requested");
        }
    }
    public function actionUpdate_product($id)
    {

        $model = $this->findModel2($id);
        $model->attributes = $this->request;

        if ($model->save()) {
            Yii::$app->api->sendSuccessResponse($model->attributes);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
        }

    }
  
      protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            $model1=Product::find()->where(['id'=>$id])->all();
            foreach($model1 as $row)
            {
                $category=Category::find()->where(['id' =>$row['category']])->all();
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
        // $params = $this->request['search'];
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $page = Yii::$app->getRequest()->getQueryParam('page');
        $limit = Yii::$app->getRequest()->getQueryParam('limit');
        $order = Yii::$app->getRequest()->getQueryParam('order');
        $limit = isset($limit) ? $limit : 10;
        $page = isset($page) ? $page : 1;
        $offset = ($page - 1) * $limit;
        // print_r($limit);
        // exit();
         $model=Category::find()->where(['category_name'=>$category])->all();
          foreach($model as $row)
         {
             
         $name=$row['id'];
       
         }
      
         $model=Product::find()->where(['category'=>$name])->limit($limit)->all();
         $i=0;
         foreach($model as $row)
         {
             $category=Category::find()->where(['id' =>$row['category']])->all();
              foreach($category as $ca)
              {
                 $name=$ca['category_name'];
                 $model[$i]['category']=$name;
                 $i=$i+1;
             
             }
             
         }
        
       

        $additional_info = [
            'page' => $page,
            'size' => $limit,
             'totalCount' =>count($model)
        ];

        return [
            'data' =>$model,
            'info' => $additional_info
        ];
   
    
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
    public function actionList($email)
     {
    //     \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $array = array();
      
        $cus_id=Customer::find()->where(['email' =>$email])->one();
        $query1 =Customer::find()
        ->select(['id', 'address','name', 'email', 'phone'])
        ->where(['email'=>$email])
        ->asArray(true);
     
        $category=Orders::find()->where(['customer_id' =>$cus_id])->all();
        $query=array();
        $order=array();
        foreach($category as $order_id)
        {
            $order=$order_id['order_id'];
            // $order++;
            print_r($order);
            exit();
        }
      
       
     
        for($i=0;$i<count($order);$i++)
        {
            // $order=$order_id['order_id'];
            // $order++;
            // print_r($order);
            // exit();
            $od=$order[$i];
            $order=$od;
            // $order++;
    // print_r($order);
    // exit();
            $product_details=Orders::find()->where(['order_id' =>$od])->all();
            foreach($product_details as $product)
            {
                $model = Product::findOne($product->product_id);
            $category=Category::find(['category_name'])->where(['id' =>$model['category']])->one();
            $name=$category['category_name'];
            $model['category']=$name;
            // $model['order_id'] = 'haai'; 
            $query[]=$model;
            $query++;
            // $query['order_id']=12;
            }

            
        }
        
     
      
        // exit();
        // foreach($category as $product){
        //     $model = Product::findOne($product->product_id);
        //     $category=Category::find(['category_name'])->where(['id' =>$model['category']])->one();
            
        //     $name=$category['category_name'];
        //     $model['category']=$name;
        //     // $model['order_id'] = 'haai'; 
        //     $query[]=$model;
        //     $query++;
        //     // $query['order_id']=12;
         
        // }
        $last_for_user = Total::find()->where('customer_id', $cus_id)->orderBy(['id' => SORT_DESC])->one();
        // print_r($last_for_user->total);
        // exit();

        return[
         
        'DeliveryAddress' =>$query1->all(),
        'productsCart'=>$query,
        'totelAmount'=>$last_for_user->total,
        'msg'=>'expected delivery date 5th november'
        ];
        
        Yii::$app->api->sendSuccessResponse($response['DeliveryAddress'],$response['productsCart']);
      
      

    }
    public function actionCategory_adding()
    {
        $model = new Category;
        $model->attributes = $this->request;

        if ($model->save()) {
            Yii::$app->api->sendSuccessResponse($model->attributes);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
        }

    }
    public function actionList_customer()
    {
        $params = $this->request['search'];
        $response = Customer::search($params);
        Yii::$app->api->sendSuccessResponse($response['data'], $response['info']);
    }
    public function actionDelete_customer($id)
    {

        $model = $this->findModel3($id);
        $model->delete();
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
    protected function findmodel3($id)
    {
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        } else {
            Yii::$app->api->sendFailedResponse("Invalid Record requested");
        }
    }
    public function actionView_customer($id)
    {

        $model = $this->findModel3($id);
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
    public function actionList_category()
    {
        $params = $this->request['search'];
        $response = Category::search($params);
        Yii::$app->api->sendSuccessResponse($response['data'], $response['info']);
    }
    public function actionCustomer_adding()
    {
        $model = new Customer;
        $model1=new Total;
       
        $params = Yii::$app->request->post();
        $cn=count($params);
        $email=Customer::find()->where(['email' =>$params['DeliveryAddress']['email']])->one(); 
        $max = Orders::find()->orderBy("order_id DESC")->one();
        if($email!=null)
        {
      
            $model1->customer_id=$email->id;
            $model1->total=$params['totelAmount'];
            $model1->save();
           
           
            for($i=0;$i<=$cn-2;$i++)
            {
                $mod=$max['order_id']+1;
              
                $model2=new Orders;
                $cus_id=Product::find()->where(['name' =>$params['productsCart'][$i]['name']])->one(); 
                $model2->order_id=$mod;
                $model2->customer_id=$email->id;
                $model2->product_id=$cus_id->id;
                $model2->count=$params['productsCart'][$i]['count'];
                $model2->flag=0;
                $model2->save();
            }
             return [
                'data' =>'successfully placed',
        
            ];
        Yii::$app->api->sendSuccessResponse($response['data']); 
    
        }
        
        else
        {
            if($params){
                // $model->id = $params['DeliveryAddress']['id'];
                $model->address = $params['DeliveryAddress']['address'];
                $model->email= $params['DeliveryAddress']['email'];
                $model->name = $params['DeliveryAddress']['name'];
                $model->phone = $params['DeliveryAddress']['phone'];
                $model->save();
               
             
            if ($model->save()) {
                // print_r($params['totelAmount']);
                // exit();
                $model1->customer_id=$model->id;
                $model1->total=$params['totelAmount'];
                $model1->save();
                for($i=0;$i<=$cn-2;$i++)
                {
                    $model2=new Orders;
                    $mod=$max['order_id']+1;
                    $cus_id=Product::find()->where(['name' =>$params['productsCart'][$i]['name']])->one(); 

                    $model2->order_id=$mod;
                    $model2->customer_id=$model->id;
                    $model2->product_id=$cus_id->id;
                    $model2->count=$params['productsCart'][$i]['count'];
                    $model2->flag=0;
                $model2->save();
               
               
               }
               return [
                'data' =>'successfully placed',
                
            ];
                Yii::$app->api->sendSuccessResponse($response['data']); 
            
         
          
            } else {
                // Yii::$app->api->sendFailedResponse($model->errors);
            }
        }
        }
    }
     
    public function actionCancel_order($order_id,$product_id)
    {
      
        $model=Orders::find()->andwhere(['order_id' =>$order_id])->andwhere([ 'product_id'=>$product_id])->one(); 
        $model->flag = 1;
        $model->save();
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
   

}
