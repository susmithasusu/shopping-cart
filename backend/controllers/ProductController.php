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
use yii\helpers\Url;

use Yii;

class ProductController extends RestController
{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors + [

           'apiauth' => [
               'class' => Apiauth::className(),
               'exclude' => ['view','create','index','delete','products','categories','category_all','list_customer','view_customer','listing_orders','cancel_all','list_emails','listing_address','create_customer','update_customer','view_category','list_oneorder','block_user','user_checking','user_unblock',
               'list','category_adding','customer_adding','update','delete','update_product','delete_product','cancel_order','delete_customer','list_category','list_allorders'],
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
                 'delete','delete_product','delete_customer'=> ['DELETE']
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
       
        $content= base64_decode($this->request['image']);
        $model = new Product;
        $image = $this->request['image']; 
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $data = base64_decode($image);
        $imgName='img_'.$this->request['name'] .'.'.'png';
        $img=\Yii::$app->basePath.'/web/uploads/'.$imgName;
        $new_img=Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'.$imgName;
        file_put_contents(\Yii::$app->basePath.'/web/uploads/'.$imgName, $data);
        exec('sudo chmod ' .Yii::$app->basePath.'/web/uploads/'.$imgName.'777');
        $category=Category::find(['id'])->where(['category_name' =>$this->request['category']])->one();
        $cat_id= $category['id'];
        $model->category =$cat_id;
        $model->name=$this->request['name'];
        $model->image=$imgName;
        $model->description=$this->request['description'];
        $model->price=$this->request['price'];
        $model->count=$this->request['count'];
        $model->save();
        // $model['image']=\Yii::$app->basePath.'/web/uploads/'.$imgName;
        
        if ($model->save()) {
            $model->image=$new_img;
            Yii::$app->api->sendSuccessResponse($model->attributes);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
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

    public function actionView($id)
    {

        $model = $this->findModel($id);
        Yii::$app->api->sendSuccessResponse($model->attributes);
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
                    $model['image']=Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'. $model['image'];
                 }
                
            }
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
        // print_r($id);
        // exit();
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            Yii::$app->api->sendFailedResponse("Invalid Record requested");
        }
    }

    public function actionUpdate_product($id)
    {
      
        if($this->request['image']!=" ")
        {
          
            $model = $this->findModel2($id);
            $new_img=\Yii::$app->basePath.'/web/uploads/'.$model['image'];
                if (file_exists($new_img)) {
                    unlink(\Yii::$app->basePath.'/web/uploads/'.$model['image']);
          
                }
      
            $content= base64_decode($this->request['image']);
            $image = $this->request['image']; 
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $data = base64_decode($image);
            $imgName='img_'.$this->request['name'] .'.'.'png';
            $new_img=Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'. $imgName;
            file_put_contents(\Yii::$app->basePath.'/web/uploads/'.$imgName, $data);
            exec('sudo chmod ' .Yii::$app->basePath.'/web/uploads/'.$imgName.'777');
       
            $category=Category::find(['id'])->where(['category_name' =>$this->request['category']])->one();
            $cat_id= $category['id'];
            $model->category =$cat_id;
            $model->name=$this->request['name'];
            $model->image=$imgName;
            $model->description=$this->request['description'];
            $model->price=$this->request['price'];
            $model->count=$this->request['count'];
            $model->save();
            if ($model->save()) {
                 $model->image=$new_img;
                Yii::$app->api->sendSuccessResponse($model->attributes);
            } else {
                Yii::$app->api->sendFailedResponse($model->errors);
            }
        }
        else{
         
            $model = $this->findModel2($this->request['id']);
          
            $new_img=Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'.$model['image'];
            $category=Category::find(['id'])->where(['category_name' =>$this->request['category']])->one();
            $cat_id= $category['id'];
            $model->category =$cat_id;
            $model->name=$this->request['name'];
            $model->description=$this->request['description'];
            $model->price=$this->request['price'];
            $model->image=$model['image'];
            $model->count=$this->request['count'];
            $model->save();
         
            if ($model->save()) {
                $model->image=$new_img;
                
                Yii::$app->api->sendSuccessResponse($model->attributes);
            } else {
                Yii::$app->api->sendFailedResponse($model->errors);
            }

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
         $mod=Product::find()->where(['category'=>$name])->all();
         $model=Product::find()->where(['category'=>$name])->limit($limit)->offset($offset)->all();
         $i=0;
         foreach($model as $row)
         {
             $category=Category::find()->where(['id' =>$row['category']])->all();
              foreach($category as $ca)
              {
                 $name=$ca['category_name'];
                 $model[$i]['category']=$name;
                 $model[$i]['image']=Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'. $model[$i]['image'];
                 $i=$i+1;
              }
             
         }
        //  $additional_info = [
        //     'page' => $page,
        //     'size' => $limit,
        //      'totalCount' =>count($model)
        // ];

        return [
            'products' =>$model,
            'page' => $page,
            'size' => $limit,
             'totalCount' =>count($mod)

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
    public function actionList_category()
    {
        $params = $this->request['search'];
        $response = Category::search($params);
        Yii::$app->api->sendSuccessResponse($response['data'], $response['info']);
    }
    public function actionView_category($id)
    {

        $model = $this->findModel1($id);
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
    public function actionCreate_customer()
    {   
        $model = new Customer;
        $model->address = $this->request['address'];
        $model->email = $this->request['email'];
        $model->name = $this->request['name'];
        $model->phone = $this->request['phone'];
        $model->flag = 0;
        $model->save();
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
    public function actionView_customer($id)
    {
        $model = $this->findModel3($id);
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
    public function actionUpdate_customer($id)
    {
        $model = $this->findModel3($id);
        $model->attributes = $this->request;

        if ($model->save()) {
            Yii::$app->api->sendSuccessResponse($model->attributes);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
        }

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
   
    public function actionCustomer_adding()
    {
        $model = new Customer;
        $model1=new Total;
        $i=0;
       
        $params = Yii::$app->request->post();
        $cn=count($params['productsCart']);
        $dt = new \DateTime('now +10 day');
        $i=0;
       
            foreach($dt as $tim)
            {
                if($i==0)
                {
                
                $time=$tim;
                $i=$i+1;
                }
            }
            $time=explode(' ',$time);
          
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
                $cus_id=Product::find()->where(['name' =>$params['productsCart'][$i]['name']])->one();$model2->order_id=$mod;
                $model2->customer_id=$email->id;
                $model2->product_id=$cus_id->id;
                $model2->count=$params['productsCart'][$i]['count'];
                $model2->flag=0;
                $model2->save();
                $cnt=$i;
            }
            
            $model1->customer_id=$email->id;
            $model1->order_id=$order;
            $model1->email=$email->email;
            $model1->delivery_address=$params['DeliveryAddress']['address'];
            $model1->flag=0;
            $model1->delivery_at=$time[0];
            $model1->total=$params['totelAmount'];
            $model1->total_quantity=$i;
            $model1->save();
           
             return [
                'data' =>'successfully placed',
                'delivery-date'=>$time[0]
        
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
                $model->flag=0;
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
                    // $model1->customer_id=$model->id;
                    // $model1->order_id=$order;
                    // $model1->total=$params['totelAmount'];
                    // $model1->total_quantity=$i;
                    // $model1->save();
                    $model1->customer_id=$model->id;
                    $model1->order_id=$order;
                    $model1->email=$model->email;
                    $model1->delivery_address=$params['DeliveryAddress']['address'];
                    $model1->flag=0;
                    $model1->delivery_at=$time[0];
                    $model1->total=$params['totelAmount'];
                    $model1->total_quantity=$i;
                    $model1->save();
                    return [
                        'data' =>'successfully placed',
                        'delivery_date'=>$time[0]
                
                    ];
                    Yii::$app->api->sendSuccessResponse($response['data']); 
            
                } else {
                // Yii::$app->api->sendFailedResponse($model->errors);
                }
            }
        }
    }
    public function actionBlock_user($id)
    {
        $model=Customer::find()->where(['id' =>$id])->one();
        $model->flag = 1;
        $model->save();
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
     
    public function actionListing_orders($email) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ordering=array();
        $new_arr=array();
        $cus_id=Customer::find()->where(['email' =>$email])->one();
        $all_orders='';
        $products=array();
        $check = Orders::find()->where(['customer_id' =>$cus_id['id']])->orderBy("order_id DESC")->one();
        $max = Orders::find()->where(['customer_id' =>$cus_id['id']])->orderBy("order_id DESC")->all();
        
        if( $cus_id=="")
        {
            return [
                'data' =>'you have no orders yet',
               
            ];

        }
        elseif($check=="")
        {
            return [
                'data' =>'you have no orders yet',
               
            ];

        }
        else{
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
                $new_query=array();
                $orders = Orders::find()->where(['order_id' =>$new[$i]])->all();
                $result=array();
           
                foreach($orders as $ord)
                {
         
                    $product_details = Product::find()->where(['id' =>$ord['product_id']])->one();
                    $total=Total::find()->where(['order_id' =>$ord['order_id']])->one();
                    $address=Total::find()->andwhere(['order_id' =>$ord['order_id']])->andwhere(['customer_id' =>$cus_id['id']])->one();
                    $details=Customer::find()->where(['email' =>$email])->one();
                    $category=Category::find(['category_name'])->where(['id' =>$product_details['category']])->one();
                     // $product_details['image']=Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'.$product_details['image'];
                    $details['address']=$address['delivery_address'];
                    $results = ArrayHelper::toArray($product_details , [
                    'common\models\Product' => [
                        'id',
                        'name',
                        'category',
                        'description',
                        'image',
                        'price',
                        'count',
                       
                        ],
                    ]);
                    $name=$category['category_name'];
                    $results['count']=$ord['count'];
                    $results['category']= $name;
                    $results['image'] =Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'.$results['image'];
                    $results['flag'] =$ord['flag'];
                    // print_r($results);
                    // exit();
                    $new_query[]=$results;
                    $new_query++;
               
                }
                    $ordering= $new_query;
                     $array[$i-1]= [
                        'totelAmount'=>$total->total,
                        'totalQuantity'=>$total->total_quantity,
                        'delivery_date'=>$total->delivery_at,
                        'order_id'=>$new[$i],
                        'DeliveryAddress' =>$details,
                        'products'=> $ordering           
                    ];
          
                $array++;
            }
        

            return [
                'orders'=>$array
            ];
                
            Yii::$app->api->sendSuccessResponse($response['DeliveryAddress'],$response['productsCart']);
        }
    }
    public function actionCancel_order($order_id,$product_id)
    {
        $model=Orders::find()->andwhere(['order_id' =>$order_id])->andwhere([ 'product_id'=>$product_id])->one(); 
        $model->flag = 1;
        $model->save();
        $mode=Orders::find()->where(['order_id' =>$order_id])->all(); 
        $i=0;
        foreach($mode as $mod)
        {
            $fl=$mod['flag'];
            if($fl==0)
         {
             $i=1;

         }
        }
        if($i!=1)
        {
            $total=Total::find()->andwhere(['order_id' =>$order_id])->one(); 
            $total->flag = 1;
            $total->save();
        }
        Yii::$app->api->sendSuccessResponse($model->attributes);
    }
    public function actionCancel_all($order_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model=Orders::find()->where(['order_id' =>$order_id])->all(); 
        foreach($model as $del)
        {
        $del->flag = 1;
        $del->save();
        }
        $model=Total::find()->where(['order_id' =>$order_id])->one();
        $model->flag = 1;
        $model->save();
        return [
            'data'=>'successfully canceled'
        ];

    }
    public function actionList_emails()
    {   
        $params = $this->request['search'];
        $response = Customer::search_email($params);
        Yii::$app->api->sendSuccessResponse($response['data'], $response['info']);
    }
    public function actionList_allorders()
    {   
        $params = $this->request['search'];
        $response = Total::search($params);
        Yii::$app->api->sendSuccessResponse($response['data'], $response['info']);
    }
    public function actionList_oneorder($order_id)
    {   
        
        $result=array();
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
        $orders1 = Orders::find()->where(['order_id' =>$order_id])->limit($limit)
        ->offset($offset);
        
        $orders=$orders1->all();
           
            foreach($orders as $ord)
            {
         
                $product_details = Product::find()->where(['id' =>$ord['product_id']])->one();
                $total=Total::find()->where(['order_id' =>$ord['order_id']])->one();
                $address=Address::find()->andwhere(['order_id' =>$ord['order_id']])->andwhere(['customer_id' =>$ord['customer_id']])->one();
                $category=Category::find(['category_name'])->where(['id' =>$product_details['category']])->one();
                $img=
                $name=$category['category_name'];
                $results = ArrayHelper::toArray($product_details , [
                    'common\models\Product' => [
                        'id',
                        'name',
                        'category',
                        'image',
                        'description',
                        'price',
                        'count',
                       
                    ],
                ]);
                $name=$category['category_name'];
                $results['count']=$ord['count'];
                $results['category']= $name;
                $results['flag'] =$ord['flag'];
                $results['image']= $results['image'] =Yii::$app->urlManager->createAbsoluteUrl("uploads").'/'.$results['image'];
                $new_query[]=$results;
                $new_query++;
               
            }
              
                    $additional_info = [
                    'page' => $page,
                    'size' => $limit,
                    'totalCount' => (int)$orders1->count()
                ];
        
                return [
                    'products'=> $new_query,
                    'info' => $additional_info
                ];
                        
                Yii::$app->api->sendSuccessResponse($response['DeliveryAddress'],$response['productsCart']);
          
                // $array++;
     }
    
       public function actionListing_address($email)
    {

        $array=array();
        $new_array=array();
        $cus_id=Customer::find()->where(['email' =>$email])->one();
        $address=Address::find()->where(['customer_id' =>$cus_id['id']])->all();
        foreach($address as $add)
                {
                    $array[]=$add['address'];
                    $array++;
                }
            return[
                'data'=>$array
            ];
    }
    public function actionUser_checking($email)
    {
        $cus_id=Customer::find()->where(['email' =>$email])->one();
        $status=$cus_id['flag'];
      

            return[
                'status'=> $status
            ];
            Yii::$app->api->sendSuccessResponse($response['data']); 

    }
    public function actionUser_unblock($id)
    { 
        $model=Customer::find()->where(['id' =>$id])->one();
        $model->flag = 0;
        $model->save();
        Yii::$app->api->sendSuccessResponse($model->attributes);
        

    }
   
}
    
    






           