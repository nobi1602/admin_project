<?php
namespace app\controllers;

use Yii;
use app\models\LoginForm;
use app\models\PermissionModel;

class LoginController extends MyController {
    
    public function behaviors() {
        return [
            'AccessControl' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'actions'=>['index'],
                        'allow'=>true,
                        //'roles'=>['?'],
                    ], [
                        'actions'=>['logout'],
                        'allow'=>true,
                        'roles'=>['@'],
                    ],
                ]
            ]
        ];
    }
    public function actionIndex() {
        $this->layout = 'login';
        $model = new LoginForm();
        $model->scenario = 'login';
        if ($model->load ( Yii::$app->request->post () ) && $model->login ()) {
            $uid = Yii::$app->security->generateRandomString();
            Yii::$app->db
            ->createCommand()
            ->insert('{{%ss_login}}', [
                'created_at' => NOW,
                'user_id' => Yii::$app->user->identity->id,
                'ip_address'=>isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : Yii::$app->request->getUserIP(),
                'ua_string'=>Yii::$app->request->getUserAgent(),
            ])
            ->execute();
            $_SESSION['list_permission'] = PermissionModel::find ()->where ( [
                'status' => '1' ,
                'id' => Yii::$app->user->identity->id_permission
            ] )
            ->select('url_home_page')
            ->one ();      
            Yii::$app->params['homesite'] = $_SESSION['list_permission']['url_home_page'];
            return $this->redirect('@web'.Yii::$app->params['homesite']);
        }
        return $this->render ( 'index', [
            'model' => $model
        ] );
    }
    
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}