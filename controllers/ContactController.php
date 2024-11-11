<?php

namespace app\controllers;

use Yii;
use app\models\ContactForm;
use yii\web\HttpException;
use app\models\ContactModel;
/**
 * MenuController implements the CRUD actions for Menu model.
 */
class ContactController extends MyController {
    public function actions()
    {
        Yii::$app->params['page_id'] = "contact";
    }
    public function actionIndex($action = '' ) {
        Yii::$app->params['page_title'] = "Tin nhắn";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/contact/u";
        $contact_list = ContactModel::find()
            -> orderBy ( 'created_at desc' )
            -> asArray()
            -> all (); 	
        if($action == 'check'){
            $id = $_POST['id'];
            $this->Updatestatus($id);
        }
        return $this->render ( 'index', [
            'contact_list'=> $contact_list,
        ] );
    }
    public function actionU($id = ''){
        $theContact = $this->Updatestatus($id);
        var_dump($theContact);exit();
    }
    private function Findcontact($id = ''){
        $theContact = ContactModel::find()
        -> where(['id' => $id])
        -> limit(1)
        -> one ();
        if (!$theContact) {
            throw new HttpException ( 404, 'Contact not found' );
        }
        return $theContact;
    }
    private function Updatestatus($id = ''){
        $theContact = $this->Findcontact($id);
        $theContact->status ='1';
        $theContact->seen_by = USER_ID;
        $theContact->check_by = USER_ID;
        $a = $theContact->update();
        return $theContact;
    }
}