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
use common\models\Address;

use Yii;

class ProductController extends RestController
{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors + [

           'apiauth' => [
               'class' => Apiauth::className(),
               'exclude' => ['view','create','index','delete','products','categories','list_customer','view_customer','listing_orders',
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $page = Yii::$app->getRequest()->getQueryParam('page');
        $limit = Yii::$app->getRequest()->getQueryParam('limit');
        $order = Yii::$app->getRequest()->getQueryParam('order');
        $limit = isset($limit) ? $limit : 10;
        $page = isset($page) ? $page : 1;
        $offset = ($page - 1) * $limit;
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
        $i=0;
       
        $params = Yii::$app->request->post();
        $cn=count($params['productsCart']);
        
        $email=Customer::find()->where(['email' =>$params['DeliveryAddress']['email']])->one(); 
        $max = Orders::find()->orderBy("order_id DESC")->one();
        $order=$max['order_id']+1;
        if($email!=null)
        {
            $email1=Address::find()->where(['customer_id' =>$email->id])->all(); 
            foreach($email1 as $address)
            {
                // print_r($address['address']);
                if(($address['address'])==($params['DeliveryAddress']['address'])){
                    $i=1;
                }
                
            }
            if($i==0)
            {
                $address_model=new Address;
                $address_model->customer_id=$email->id;
                $address_model->order_id=$order;
                $address_model->address=$params['DeliveryAddress']['address'];
                $address_model->save();
            }
         
            for($i=0;$i<=$cn-1;$i++)
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
                $cnt=$i;
            }
            
            $model1->customer_id=$email->id;
            $model1->order_id=$order;
            $model1->total=$params['totelAmount'];
            $model1->total_quantity=$i;
            $model1->save();
           
             return [
                'data' =>'successfully placed',
        
            ];
        Yii::$app->api->sendSuccessResponse($response['data']); 
    
        }
        
        else
        {
        
            if($params){
           
                $model->address = $params['DeliveryAddress']['address'];
                $model->email= $params['DeliveryAddress']['email'];
                $model->name = $params['DeliveryAddress']['name'];
                $model->phone = $params['DeliveryAddress']['phone'];
                $model->save();
               
                if ($model->save()) {

                    $address_model=new Address;
                    $address_model->customer_id=$model->id;
                    $address_model->order_id=$order;
                    $address_model->address=$params['DeliveryAddress']['address'];
                    $address_model->save();
               
                    for($i=0;$i<=$cn-1;$i++)
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
                    $model1->customer_id=$model->id;
                    $model1->order_id=$order;
                    $model1->total=$params['totelAmount'];
                    $model1->total_quantity=$i;
                    $model1->save();
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

    public function actionListing_orders($email) {
        //     \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ordering=array();
        $new_arr=array();
        $cus_id=Customer::find()->where(['email' =>$email])->one();
        $all_orders='';
        $products=array();
        $max = Orders::find()->where(['customer_id' =>$cus_id['id']])->orderBy("order_id DESC")->all();
        
        foreach($max as $ma){
            $all_orders=$all_orders.'$'.$ma['order_id'];
        } 
        
        $arr = explode("$", $all_orders);
        $new=array();
        for($i=0;$i<count($arr);$i++){
            
            if (in_array($arr[$i],$new)){
                
            }
            else{
                $new[]=$arr[$i];
                $new++; 
            }
        }
        
        $array=array();
        for($i=1;$i<count($new);$i++)
        {
            $query=array();
            $orders = Orders::find()->andwhere(['order_id' =>$new[$i]])->all();
           
            foreach($orders as $ord)
            {
         
                $product_details = Product::find()->andwhere(['id' =>$ord['product_id']])->one();
                $total=Total::find()->andwhere(['order_id' =>$ord['order_id']])->one();
                $category=Category::find(['category_name'])->where(['id' =>$product_details['category']])->one();
                $name=$category['category_name'];
                $product_details['category']=$ord['flag'];
                $product_details['count']=$ord['count'];
                $query[]=$product_details;
                $query++;
                $ordering=$query;
                
                $array[$i-1]= [
                    'totelAmount'=>$total->total,
                    'totalQuantity'=>$total->total_quantity,
                    'msg'=>'expected delivery date 5th november',
                    'order_id'=>$new[$i],
                    'DeliveryAddress' =>$cus_id,
                    'products'=> $ordering           
                ];
          
                $array++;
             
            }  
        }
        return [
            'orders'=>$array
        ];
                
        Yii::$app->api->sendSuccessResponse($response['DeliveryAddress'],$response['productsCart']);
    }
}
    
    






           