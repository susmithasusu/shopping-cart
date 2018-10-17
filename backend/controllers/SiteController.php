<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\AuthorizationCodes;
use common\models\AccessTokens;

use backend\models\SignupForm;
use backend\behaviours\Verbcheck;
use backend\behaviours\Apiauth;

use common\models\Product;

/**
 * Site controller
 */
class SiteController extends RestController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors + [
            'apiauth' => [
                'class' => Apiauth::className(),
                'exclude' => ['authorize', 'register','create', 'accesstoken','index','list','details'],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'me'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['authorize', 'register', 'accesstoken'],
                        'allow' => true,
                        'roles' => ['*'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => Verbcheck::className(),
                'actions' => [
                    'logout' => ['GET'],
                    'authorize' => ['POST'],
                    'register' => ['POST'],
                    'accesstoken' => ['POST'],
                    'me' => ['GET'],
                ],
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        Yii::$app->api->sendSuccessResponse(['Yii2 RESTful API with OAuth2']);
        //  return $this->render('index');
    }

    public function actionRegister()
    {

        $model = new SignupForm();
        $model->attributes = $this->request;

        if ($user = $model->signup()) {

            $data=$user->attributes;
            unset($data['auth_key']);
            unset($data['password_hash']);
            unset($data['password_reset_token']);

            Yii::$app->api->sendSuccessResponse($data);

        }

    }


    public function actionMe()
    {
        $data = Yii::$app->user->identity;
        $data = $data->attributes;
        unset($data['auth_key']);
        unset($data['password_hash']);
        unset($data['password_reset_token']);

        Yii::$app->api->sendSuccessResponse($data);
    }

    public function actionAccesstoken()
    {

        if (!isset($this->request["authorization_code"])) {
            Yii::$app->api->sendFailedResponse("Authorization code missing");
        }

        $authorization_code = $this->request["authorization_code"];

        $auth_code = AuthorizationCodes::isValid($authorization_code);
        if (!$auth_code) {
            Yii::$app->api->sendFailedResponse("Invalid Authorization Code");
        }

        $accesstoken = Yii::$app->api->createAccesstoken($authorization_code);

        $data = [];
        $data['access_token'] = $accesstoken->token;
        $data['expires_at'] = $accesstoken->expires_at;
        Yii::$app->api->sendSuccessResponse($data);

    }

    public function actionAuthorize()
    {
        $model = new LoginForm();

        $model->attributes = $this->request;


        if ($model->validate() && $model->login()) {

            $auth_code = Yii::$app->api->createAuthorizationCode(Yii::$app->user->identity['id']);

            $data = [];
            $data['authorization_code'] = $auth_code->code;
            $data['expires_at'] = $auth_code->expires_at;

            Yii::$app->api->sendSuccessResponse($data);
        } else {
            Yii::$app->api->sendFailedResponse($model->errors);
        }
    }

    public function actionLogout()
    {
        $headers = Yii::$app->getRequest()->getHeaders();
        $access_token = $headers->get('x-access-token');

        if(!$access_token){
            $access_token = Yii::$app->getRequest()->getQueryParam('access-token');
        }

        $model = AccessTokens::findOne(['token' => $access_token]);

        if ($model->delete()) {

            Yii::$app->api->sendSuccessResponse(["Logged Out Successfully"]);

        } else {
            Yii::$app->api->sendFailedResponse("Invalid Request");
        }


    }
    public function actionCreate()
    {
        $model = new Product();
         $params = Yii::$app->request->post();
        // $params=$_REQUEST['id'];
      
        //  print_r($uploads);
        //   exit();
         if($params)
         {
           
            //  $model->attributes = $_REQUEST['Candidate'];
             //$formModel->id = $params['id'];

            //  $model->image = UploadedFile::getInstancesByName("image");
            //  if($model->image)
            //  {
            //      $model->image->saveAs('uploads/'.$name.'.
            //      '.$model->image->extension);
            //       $model->image = $uploads->name.'.'.
            //       $model->image->extension;
            //  }


            //  if ($model->file = UploadedFile::getInstancesByName('image')) {
            //     $model->file->saveAs( '/uploads/'.$imageName.'.'.$model->file->extension );
            //     //save the path in DB..
            //     $model->image = 'uploads/'.$imageName.'.'.$model->file->extension;
            //     $model->save();
            // }
           
             $model->category = $params['category'];
             $model->name = $params['name'];
             $model->image = $params['image'];
             $model->description = $params['description'];
             $model->price = $params['price'];
               if($model->validate())
               {
               	$model->save();
                   return $model;
               }
            
         }
    }
    public function actionList()
    {
         $model=Product::find()->all();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $model;
    
    }
    public function actionProducts($category)
    {
       
         $model=Product::find()->where(['category'=>$category])->all();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $model;
    
    }
   
    public function actionDetails($id)
    {
        $model=Product::find()->where(['id'=>$id])->all();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $model;

    }
    public function actionCategories()
    {
        // $model = new Query;
        //    $model ->select('category')
        //     ->from('product')
        //     ->distinct()
        //     ->all();
            
        //     if ($model) {
        //         foreach ($model as $row) {
        //     print_r($row['category']);
        //         }
        //     }
            $query = product::find()->select('category')->distinct();
            foreach($query as $row)
            {
                print_r($row->category);
            }
           
            exit();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $model;

    }
    public function actionOrder()
    {
        $model = new Customer();
        $model1=new Order();
        $params = Yii::$app->request->post();

    }

}
