<?php
namespace app\controllers;

use Yii;


class ErrorController extends MyController
{
    public function actions()
    {
        $_SESSION['check_er']='1';
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
}
