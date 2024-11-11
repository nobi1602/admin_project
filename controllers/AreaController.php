<?php
namespace app\controllers;
use Yii;
use app\models\LangModel;
use app\models\AreaModel;
use app\models\PostForm;
use yii\web\HttpException;
use yii\web\UploadedFile;
class AreaController extends MyController {
    public function actions()
    {
        Yii::$app->params['page_id'] = "area";
    }
    public function actionIndex() {
        Yii::$app->params['page_title'] = "Danh sách khu vực";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/area/c";
        $query = AreaModel::find ()->where ( [ 
                'status' => '1' 
        ] );
        $the_extra = $query
            ->orderBy ( '{{%area}}.id' )
            ->with ( [ 
                'area_detail' => function ($query) {
                    $query->andWhere ( [ 
                        'lang_value' => Yii::$app->language
                    ]);
                },	
                'user_created' => function ($query) {
                    $query->select ( [ 
                        'id',
                        'username' 
                    ] );
                } 
            ] )
            ->asArray()
            ->all ();
        return $this->render ( 'index', [ 
            'the_post' => $the_extra,
        ] );
    }
    public function actionC() {
        Yii::$app->params['page_title'] = 'Khu vực';
        Yii::$app->params['page_action'] = 'Thêm khu vực mới';
        $thePost = new AreaModel ();
        $lang_use = LangModel::find()->asArray()->all();    	
        $thePostForm = new PostForm ();
        $thePostForm->scenario = 'create';
        if ($thePostForm->load ( Yii::$app->request->post () ) && $thePostForm->validate ()) {
            $thePost->created_at = NOW;
            $thePost->created_by = USER_ID;
            $thePost->updated_at = NOW;
            $thePost->updated_by = USER_ID;
            $thePost->status = 1;
            $result = $thePost->save ( false );            
            if ($result) {
                $id = $thePost->id;                
                if ($id != '') {
                    $thePostForm->avatar = UploadedFile::getInstance($thePostForm, 'avatar');
                    if($thePostForm->avatar != null){
                        if ($thePostForm->upload($id,'extra')) {
                            $filename = MyController::move_dau($thePostForm->avatar->name);  
                            $thePost->avatar = Yii::$app->params['homesite'].'/upload/extra/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    $thePost->update ();
                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->insert ( '{{%extra_sv_detail}}', [ 
                                'extra_id' => $id,
                                'title' => $thePostForm['name'][$v['lang_value']],
                                'lang_value' => $v['lang_value'],
                                'description' => $thePostForm['description'][$v['lang_value']],
                                'seo_title' => $thePostForm['seo_title'][$v['lang_value']],
                                'seo_description' => $thePostForm['seo_description'][$v['lang_value']],
                        ] )->execute ();
                    }			                    
                    if ($r2) {
                        $action = 'Created area id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            } else {
                $action = 'Created area id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }            
            return $this->redirect ( '@web/extra' );
        }        
        return $this->render ( 'area_c', [ 
                'thePost' => $thePost,
                'thePostForm' => $thePostForm,
                'lang_use' => $lang_use 
        ] );
    }
    public function actionU($id = '') {
        Yii::$app->params['page_title'] = "Khu vực";
        Yii::$app->params['page_action'] = "Cập nhật khu vực";
        $thePost = AreaModel::find ()->where ( [ 
                'id' => $id 
        ] )->with ( [ 
                'area_detail_update',
        ] )->one ();       
        if (! $thePost || $thePost ['status'] === 0) {
            throw new HttpException ( 404, 'Area not found' );
        }        
        $lang_use = LangModel::find()->asArray()->all();    	
        $a = [];
        foreach($lang_use as $k => $v){
            $a['name'][$v['lang_value']] = $thePost['area_detail_update'][$k]['title'];
            $a['description'][$v['lang_value']] = $thePost['area_detail_update'][$k]['description'];
            $a['seo_title'][$v['lang_value']] = $thePost['area_detail_update'][$k]['seo_title'];    
            $a['seo_description'][$v['lang_value']] = $thePost['area_detail_update'][$k]['seo_description'];    
        }
        $thePostForm = new PostForm ();
        $thePostForm->scenario = 'create_prod';
        $thePostForm->setAttributes ( $thePost->getAttributes (), false );
        $thePostForm->name = $a['name'];
        $thePostForm->description = $a['description'];
        $thePostForm->seo_title = $a['seo_title'];
        $thePostForm->seo_description = $a['seo_description'];
        if ($thePostForm->load ( Yii::$app->request->post () ) && $thePostForm->validate ()) {
            $thePost->updated_at = NOW;
            $thePost->updated_by = USER_ID;
            $thePost->status = 1;
            $thePost->avatar = $thePostForm->avatar;
            $result = $thePost->save ( false );
            if ($result) {
                $id = $thePost->id;
                if ($id != '') {
                    $thePostForm->avatar = UploadedFile::getInstance($thePostForm, 'avatar');
                    if($thePostForm->avatar != null){
                        if ($thePostForm->upload($id,'extra')) {
                            $filename = MyController::move_dau($thePostForm->avatar->name);  
                            $thePost->avatar = Yii::$app->params['homesite'].'/upload/extra/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    $thePost->update ();
                    
                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->update ( '{{%area_detail}}', [                                
                                'title' => $thePostForm['name'][$v['lang_value']],
                                'lang_value' => $v['lang_value'],
                                'description' => $thePostForm['description'][$v['lang_value']],
                                'seo_title' => $thePostForm['seo_title'][$v['lang_value']],
                                'seo_description' => $thePostForm['seo_description'][$v['lang_value']],
                        ] , 'extra_id =:p_id and lang_value=:lang', array (
                            ':p_id' => $id,
                            ':lang' => $v['lang_value']
                        ) )->execute ();
                    }			
                    if ($r2) {
                        $action = 'Update area id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            } else {
                $action = 'Update area id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }
            return $this->redirect ( '@web/area' );
        }
        
        return $this->render ( 'area_u', [ 
                'thePost' => $thePost,
                'thePostForm' => $thePostForm,
                'lang_use' => $lang_use
        ] );
    }
    public function actionD($id = '') {
        $Post = AreaModel::find ()->where ( [ 
                'id' => $id 
        ] )->one ();
        if (! $Post || $Post ['status'] === 0) {
            throw new HttpException ( 404, 'area not found' );
        }
        $result = Yii::$app->db->createCommand ()->update ( '{{%area}}', [ 
                'updated_at' => NOW,
                'updated_by' => USER_ID,
                'status' => '0' 
        ], [ 
                'id' => $id 
        ] )->execute ();
        
        if ($result) {
            $action = 'Delete area id :' . $id;
            $result = '1';
            MyController::actionSavelog ( $action, $result );
        } else {
            $action = 'Delete area id :' . $id;
            $result = '0';
            MyController::actionSavelog ( $action, $result );
        }
        return $this->redirect ( '@web/area' );
    }
}