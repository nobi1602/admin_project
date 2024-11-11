<?php

namespace app\controllers;

use app\models\Category;
use app\models\LangModel;
use app\models\PostForm;
use app\models\PostModel;
use Yii;
use yii\web\HttpException;
use yii\web\UploadedFile;

class PostController extends MyController {
    
    public function actions()
    {
        Yii::$app->params['page_id'] = "post";
    }
    
    public function actionIndex() {
        Yii::$app->params['page_title'] = "Danh sách bài viết";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/post/c";
        $query = PostModel::find ()->where ( [ 
                'status' => '1' 
        ] );
        $the_post = $query
            ->orderBy ( '{{%post}}.id' )
            ->with ( [ 
                'post_detail' => function ($query) {
                    $query->andWhere ( [ 
                        'lang_value' => Yii::$app->language
                    ]);
                    
                },	
                'category_detail' => function ($query) {
                    $query->andWhere ( [ 
                        'lang_name' => Yii::$app->language
                    ] );
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
                'the_post' => $the_post,
        ] );
    }
    public function actionC() {
        Yii::$app->params['page_title'] = 'Bài viết';
        Yii::$app->params['page_action'] = 'Tạo bài viết mới';
        
        $thePost = new PostModel ();
        $theCategoryHas = $this->SelectCategory();
        $lang_use = LangModel::find()->asArray()->all();    	
        $thePostForm = new PostForm ();
        $thePostForm->scenario = 'create';
        
        if ($thePostForm->load ( Yii::$app->request->post () ) && $thePostForm->validate ()) {
            
            $thePost->created_at = NOW;
            $thePost->created_by = USER_ID;
            $thePost->updated_at = NOW;
            $thePost->updated_by = USER_ID;
            $thePost->status = 1;
            $thePost->cate_id = $thePostForm->cate_id;

            $result = $thePost->save ( false );            
            if ($result) {
                $id = $thePost->id;                
                if ($id != '') {
                    $thePostForm->avatar = UploadedFile::getInstance($thePostForm, 'avatar');
                    if($thePostForm->avatar != null){
                        if ($thePostForm->upload($id,'post')) {
                            $filename = MyController::move_dau($thePostForm->avatar->name);  
                            $thePost->avatar = Yii::$app->params['homesite'].'/upload/post/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    $thePost->update ();
                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->insert ( '{{%post_detail}}', [ 
                                'post_id' => $id,
                                'title' => $thePostForm['name'][$v['lang_value']],
                                'lang_value' => $v['lang_value'],
                                'description' => $thePostForm['description'][$v['lang_value']],
                                'content' => $thePostForm['content'][$v['lang_value']],
                                'slug' => str_replace(' ','-',MyController::move_dau($thePostForm['name'][$v['lang_value']])),
                                'seo_title' => $thePostForm['seo_title'][$v['lang_value']],
                                'seo_description' => $thePostForm['seo_description'][$v['lang_value']],
                        ] )->execute ();
                    }			                    
                    if ($r2) {
                        $action = 'Created post id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            } else {
                $action = 'Created post id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }            
            return $this->redirect ( '@web/post' );
        }        
        return $this->render ( 'post_c', [ 
                'thePost' => $thePost,
                'theCategoryHas' => $theCategoryHas,
                'thePostForm' => $thePostForm,
                'lang_use' => $lang_use 
        ] );
    }
    
    public function actionU($id = '') {
        Yii::$app->params['page_title'] = "Danh sách bài viết";
        Yii::$app->params['page_action'] = "Cập nhật bài viết";
        
        $thePost = PostModel::find ()->where ( [ 
                'id' => $id 
        ] )->with ( [ 
                'post_detail_update',
                'category_detail' => function ($query) {
                    $query->andWhere ( [ 
                        'lang_name' => Yii::$app->language 
                    ] );
                } 
        ] )->one ();       
        if (! $thePost || $thePost ['status'] === 0) {
            throw new HttpException ( 404, 'Category not found' );
        }        
        $lang_use = LangModel::find()->asArray()->all();    	
        $a = [];
        foreach($lang_use as $k => $v){
            $a['name'][$v['lang_value']] = $thePost['post_detail_update'][$k]['title'];
            $a['description'][$v['lang_value']] = $thePost['post_detail_update'][$k]['description'];
            $a['content'][$v['lang_value']] = $thePost['post_detail_update'][$k]['content'];
            $a['tags'][$v['lang_value']] = $thePost['post_detail_update'][$k]['tags'];
            $a['seo_title'][$v['lang_value']] = $thePost['post_detail_update'][$k]['seo_title'];    
            $a['seo_description'][$v['lang_value']] = $thePost['post_detail_update'][$k]['seo_description'];    
        }

        $theCategoryHas = $this->SelectCategory();
            
        $thePostForm = new PostForm ();
        $thePostForm->scenario = 'create';
        $thePostForm->setAttributes ( $thePost->getAttributes (), false );
        $thePostForm->cate_id = $thePost['cate_id'];
        $thePostForm->name = $a['name'];
        $thePostForm->description = $a['description'];
        $thePostForm->content = $a['content'];
        $thePostForm->tag = $a['tags'];
        $thePostForm->seo_title = $a['seo_title'];
        $thePostForm->seo_description = $a['seo_description'];
        
        if ($thePostForm->load ( Yii::$app->request->post () ) && $thePostForm->validate ()) {
            
            $thePost->updated_at = NOW;
            $thePost->updated_by = USER_ID;
            $thePost->status = 1;
            $thePost->cate_id = $thePostForm->cate_id;
            $thePost->avatar = $thePostForm->avatar;
            
            $result = $thePost->save ( false );
            
            if ($result) {
                $id = $thePost->id;
                
                if ($id != '') {

                    $thePostForm->avatar = UploadedFile::getInstance($thePostForm, 'avatar');
                    if($thePostForm->avatar != null){
                        if ($thePostForm->upload($id,'post')) {
                            $filename = MyController::move_dau($thePostForm->avatar->name);  
                            $thePost->avatar = Yii::$app->params['homesite'].'/upload/post/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    $thePost->update ();

                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->update ( '{{%post_detail}}', [                                
                                'title' => $thePostForm['name'][$v['lang_value']],
                                'lang_value' => $v['lang_value'],
                                'description' => $thePostForm['description'][$v['lang_value']],
                                'content' => $thePostForm['content'][$v['lang_value']],
                                'slug' => str_replace(' ','-',MyController::move_dau($thePostForm['name'][$v['lang_value']])),
                                'seo_title' => $thePostForm['seo_title'][$v['lang_value']],
                                'seo_description' => $thePostForm['seo_description'][$v['lang_value']],
                        ] , 'post_id =:p_id and lang_value=:lang', array (
                            ':p_id' => $id,
                            ':lang' => $v['lang_value']
                        ) )->execute ();
                    }			
                    
                    if ($r2) {
                        $action = 'Update post id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            } else {
                $action = 'Update post id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }
            
            return $this->redirect ( '@web/post' );
        }
        
        return $this->render ( 'post_u', [ 
                'thePost' => $thePost,
                'theCategoryHas' => $theCategoryHas,
                'thePostForm' => $thePostForm,
                'lang_use' => $lang_use
        ] );
    }
    
    public function actionD($id = '') {
        $Post = PostModel::find ()->where ( [ 
                'id' => $id 
        ] )->one ();
        if (! $Post || $Post ['status'] === 0) {
            throw new HttpException ( 404, 'Post not found' );
        }
        $result = Yii::$app->db->createCommand ()->update ( '{{%post}}', [ 
                'updated_at' => NOW,
                'updated_by' => USER_ID,
                'status' => '0' 
        ], [ 
                'id' => $id 
        ] )->execute ();
        
        if ($result) {
            $action = 'Delete post id :' . $id;
            $result = '1';
            MyController::actionSavelog ( $action, $result );
        } else {
            $action = 'Delete post id :' . $id;
            $result = '0';
            MyController::actionSavelog ( $action, $result );
        }
        return $this->redirect ( '@web/post' );
    }
    
    private function SelectCategory(){
        $CategoryHas = Category::find ()
        ->select('id, depth')
        ->where (['status' => '1' , 'tree' => '1'])
        ->orderBy ( '{{%category}}.lft' )
        ->with ( [
            'category_detail' => function ($query) {
            $query->andWhere ( [
                'lang_name' => Yii::$app->language
            ]);
            }
            ] )
        ->asArray()
        ->all (); 
        return $CategoryHas;
    }
}