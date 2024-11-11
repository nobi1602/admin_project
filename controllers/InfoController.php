<?php
namespace app\controllers;

use app\models\CategoryForm;
use app\models\InfoModel;
use app\models\LangModel;
use Yii;
use yii\web\HttpException;
use yii\web\UploadedFile;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class InfoController extends MyController{
    public function actionIndex(){
        Yii::$app->params['page_id'] = "info";
        Yii::$app->params['page_title'] = "Thông tin chung";
        Yii::$app->params['page_action'] = "Cập nhật";
        $theCategory = InfoModel::find()
            ->where(['id' => '1'])
            ->with ( [
                'info_detail',
            ] )
            ->limit(1)
            ->one ();
        if (! $theCategory) {
            throw new HttpException ( 404, 'Not found' );
        }
        $lang_use = LangModel::find()->asArray()->all();
        $a = [];
        foreach($lang_use as $k => $v){
//             var_dump($theCategory); exit('a');
            $a['phone'] = str_replace('.','',$theCategory['phone_number']);
            $a['email'] = $theCategory['email'];
            $a['map'] = $theCategory['map'];
            $a['name'][$v['lang_value']] = $theCategory['info_detail'][$k]['title'];
            $a['sort_title'][$v['lang_value']] = $theCategory['info_detail'][$k]['sort_title'];
            $a['addres'][$v['lang_value']] = $theCategory['info_detail'][$k]['address'];
            $a['description'][$v['lang_value']] = $theCategory['info_detail'][$k]['description'];         
            $a['seo_title'][$v['lang_value']] = $theCategory['info_detail'][$k]['seo_title'];
            $a['seo_description'][$v['lang_value']] = $theCategory['info_detail'][$k]['seo_description'];
        }
        $theForm = new CategoryForm();
        $theForm->scenario = 'info';
        $theForm->setAttributes ( $theCategory->getAttributes (), false );
        
        $theForm->phone = $a['phone'];
        
        $theForm->email = $a['email'];
        $theForm->name = $a['name'];
        $theForm->sort_title = $a['sort_title'];
        $theForm->description = $a['description'];        
        $theForm->seo_title = $a['seo_title'];
        $theForm->seo_description = $a['seo_description'];
        $theForm->addres = $a['addres'];
        $theForm->link_map = $a['map'];
        
        $id = '1';
        if ($theForm->load( Yii::$app->request->post ()) && $theForm->validate ()) {
            $theCategory->updated_at = NOW;
            $theCategory->updated_by = USER_ID;
            
            $phone_1 = substr($theForm->phone, 0,4);
            $phone_2 = substr($theForm->phone, 4,3);
            $phone_3 = substr($theForm->phone, 7,3);

            $theCategory->phone_number = $phone_1.'.'.$phone_2.'.'.$phone_3;
            
            $theCategory->email = $theForm->email;
            $theCategory->map = $theForm->link_map;
            
            if($theCategory->save ()){
                if ($id != '') {
                    $theForm->avatar = UploadedFile::getInstance($theForm, 'avatar');
                    if($theForm->avatar != null){
                        if ($theForm->upload_avatar($id,'info')) {
                            $filename = MyController::move_dau($theForm->avatar->name);
                            $theCategory->avatar = Yii::$app->params['homesite'].'/upload/info/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    $theForm->img_about = UploadedFile::getInstance($theForm, 'img_about');
                    if($theForm->img_about != null){
                        if ($theForm->upload_img_about($id,'info')) {
                            $filename = MyController::move_dau($theForm->img_about->name);
                            $theCategory->img_about = Yii::$app->params['homesite'].'/upload/info/avatar/'.$id.'/'.$filename;
                        }else{
                            exit('a');
                        }
                    }
                    $theForm->img_contact = UploadedFile::getInstance($theForm, 'img_contact');
                    if($theForm->img_contact != null){
                        if ($theForm->upload_img_contact($id,'info')) {
                            $filename = MyController::move_dau($theForm->img_contact->name);
                            $theCategory->img_contact = Yii::$app->params['homesite'].'/upload/info/avatar/'.$id.'/'.$filename;
                        }else{
                            exit('a');
                        }
                    }
                    $theForm->img_post = UploadedFile::getInstance($theForm, 'img_post');
                    if($theForm->img_post != null){
                        if ($theForm->upload_img_post($id,'info')) {
                            $filename = MyController::move_dau($theForm->img_post->name);
                            $theCategory->img_post = Yii::$app->params['homesite'].'/upload/info/avatar/'.$id.'/'.$filename;
                        }else{
                            exit('a');
                        }
                    }
                    $theForm->img_product = UploadedFile::getInstance($theForm, 'img_product');
                    if($theForm->img_product != null){
                        if ($theForm->upload_img_product($id,'info')) {
                            $filename = MyController::move_dau($theForm->img_product->name);
                            $theCategory->img_product = Yii::$app->params['homesite'].'/upload/info/avatar/'.$id.'/'.$filename;
                        }else{
                            exit('a');
                        }
                    }
                    $theForm->img_line_contact = UploadedFile::getInstance($theForm, 'img_line_contact');
                    if($theForm->img_line_contact != null){
                        if ($theForm->upload_img_line_contact($id,'info')) {
                            $filename = MyController::move_dau($theForm->img_line_contact->name);
                            $theCategory->img_line_contact = Yii::$app->params['homesite'].'/upload/info/avatar/'.$id.'/'.$filename;
                        }else{
                            exit('a');
                        }
                    }
                    $theCategory->update ();
                    foreach($lang_use as $k => $v){
                        $r2 = Yii::$app->db->createCommand ()->update ( '{{%info_detail}}', [
                            'title' => $theForm['name'][$v['lang_value']],
                            'sort_title' => $theForm['sort_title'][$v['lang_value']],
                            'lang_name' => $v['lang_value'],
                            'description' => $theForm['description'][$v['lang_value']],
                            'address' => $theForm['addres'][$v['lang_value']],
                            'seo_title' => $theForm['seo_title'][$v['lang_value']],
                            'seo_description' => $theForm['seo_description'][$v['lang_value']],
                        ], 'id_info =:c_id and lang_name=:lang', array (
                            ':c_id' => $id,
                            ':lang' => $v['lang_value']
                        ) )->execute ();
                    }
                    if ($r2) {
                        $action = 'Update info id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }else {
                    $action = 'Update info id :' . $id;
                    $result = '0';
                    MyController::actionSavelog ( $action, $result );
                }
                return $this->redirect(['/info/index']);
            }else{
                exit('a');
            }
        }
        return $this->render ( 'index', [
            'model' => $theCategory,
            'theForm' => $theForm,
            'lang_use' => $lang_use,
            'id' => $id,
        ] );
    }
}