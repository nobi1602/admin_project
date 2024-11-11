<?php

namespace app\controllers;

use Yii;
use app\models\LangModel;
use app\models\Category;
use app\models\CategoryForm;
use app\models\CategorySearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends MyController {
    /**
     * Lists all Category models.
     * 
     * @return mixed
     */
//     BÀI VIẾT
    public function actions()
    {
        Yii::$app->params['page_id'] = "category";
    }
    public function actionIndex() {
        Yii::$app->params['page_title'] = "Danh mục bài viết";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/category/c";
        $query = Category::find ()
                ->where (['status' => '1', 'tree'=> '1'])
                ->andWhere('depth > 0');
        $the_category = $query
        ->orderBy ( '{{%category}}.lft' )
        ->with ( [
                    'category_detail' => function ($query) {
                        $query->andWhere ( [
                            'lang_name' => Yii::$app->language
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
                'theCategory'=> $the_category,
        ] );
    }

    public function actionC() {
        Yii::$app->params['page_title'] = "Danh mục bài viết";
        Yii::$app->params['page_action'] = "Tạo danh mục bài viết";
        
        $model = new Category ();
        $lang_use = LangModel::find()->asArray()->all();
        $categoryHas = $this->CategoryHas(0,1);

        $theForm = new CategoryForm();
        $theForm->scenario = 'create';
        $query = Category::find ()
        ->select('id, depth')
        ->where (['status' => '1']);	
     
        $theForm->parentID = 1;    
                
        if ($theForm->load(Yii::$app->request->post()) && $theForm->validate()) {
            $model->position = '0';
            $model->created_by = USER_ID;
            $model->updated_by = USER_ID;
            $model->created_at = NOW;
            $model->updated_at = NOW;
            $model->status = '1';            
            $parent_id = $theForm->parentID;
            $model->parent_id = $parent_id;                   
            if (empty ( $parent_id )) {
                $model->makeRoot ();				
            } else {                
                $parent = Category::findOne ( $parent_id );                
                $a = $model->appendTo ($parent);
                $child_id = $model->id;
                if ($child_id != '') {                   
                    $model->update ();
                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->insert ( '{{%category_detail}}', [
                                'cate_id' => $child_id,
                                'title' => $theForm['name'][$v['lang_value']],
                                'lang_name' => $v['lang_value'],
                                'description' => $theForm['description'][$v['lang_value']],
                                'content' => $theForm['content'][$v['lang_value']],
                                'slug' => str_replace(' ','-',MyController::move_dau($theForm['name'][$v['lang_value']])),
                                'seo_title' => $theForm['seo_title'][$v['lang_value']],
                                'seo_description' => $theForm['seo_description'][$v['lang_value']],
                        ] )->execute ();
                    }			
                    //avartar                    
                    $theForm->avatar = UploadedFile::getInstance($theForm, 'avatar');
                    if ($theForm->upload_avatar($child_id,'category')) {
                        $filename = MyController::move_dau($theForm->avatar->name);                        
                        $model->avatar = Yii::$app->params['homesite'].'/upload/category/avatar/'.$child_id.'/'.$filename;
                        $model->update ();
                    }else{
                        exit();
                    }						
                    if ($r2) {
                        $action = 'Created category id :' . $child_id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }else {
                        $action = 'Created category id :' . $child_id;
                        $result = '0';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            }
            return $this->redirect ( '@web/category' );
        }
        return $this->render ( 'category_c', [ 
            'model' => $model ,
            'theForm' => $theForm,
            'lang_use' => $lang_use,
            'categoryHas' => $categoryHas
        ] );
    }
    public function actionU($id = '') {
        $theCategory = Category::find()
                        ->where(['id' => $id])						
                        ->with ( [
                            'category_detail_update',
                        ] )
                        ->limit(1)
                        ->one ();		              								
        if (! $theCategory || $theCategory['status'] === 0) {
            throw new HttpException ( 404, 'Category not found' );
        }
        $lang_use = LangModel::find()->asArray()->all(); 
        if($theCategory['tree']=='1'){
            $categoryHas = $this->CategoryHas($id,1);   
            Yii::$app->params['page_title'] = "Danh mục bài viết";
            Yii::$app->params['page_action'] = "Cập nhật danh mục bài viết";
        }else{
            if($theCategory['tree']=='3'){
                $categoryHas = $this->CategoryHas($id,3);   
                Yii::$app->params['page_title'] = "Sản phẩm";
                Yii::$app->params['page_action'] = "Cập nhật danh mục sản phẩm";
            }else{
                exit('a');
            }
        }
        
        $a = [];
        foreach($lang_use as $k => $v){
            $a['name'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['title'];
            $a['description'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['description'];
            $a['content'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['content'];
            $a['seo_title'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['seo_title'];    
            $a['seo_description'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['seo_description'];    
        }

        $theForm = new CategoryForm();
        $theForm->scenario = 'create';
        $theForm->setAttributes ( $theCategory->getAttributes (), false );

        $theForm->parentID = $theCategory->parent_id;
        $theForm->name = $a['name'];
        $theForm->description = $a['description'];
        $theForm->content = $a['content'];			
        $theForm->seo_title = $a['seo_title'];
        $theForm->seo_description = $a['seo_description'];
        if ($theForm->load(Yii::$app->request->post ()) && $theForm->validate () ) {
            
            $theCategory->updated_at = NOW;
            $theCategory->updated_by = USER_ID;
            $theCategory->status = 1;    		
            $parent_id = $theForm->parentID ;
            $theCategory->parent_id = $parent_id;	            
            if($theCategory->save()){
                if($parent_id != $theCategory['parentID']){               
                    if(empty($parent_id)){
                        $theCategory->makeRoot();
                    }else { //change root
                        $parent = Category::findOne($parent_id);
                        $theCategory->appendTo($parent);
                    }					
                }
            }
            $id = $theCategory->id;
            if ($id != '') {
                //avartar
                $theForm->avatar = UploadedFile::getInstance($theForm, 'avatar');
                if($theForm->avatar != null){
                    if ($theForm->upload_avatar($id,'category')) {
                        $filename = MyController::move_dau($theForm->avatar->name);			
                        $theCategory->avatar = Yii::$app->params['homesite'].'/upload/category/avatar/'.$id.'/'.$filename;
                    }else{
                        exit();
                    }
                }
                $theCategory->update ();
                foreach($lang_use as $k => $v){                        
                    $r2 = Yii::$app->db->createCommand ()->update ( '{{%category_detail}}', [
                            'title' => $theForm['name'][$v['lang_value']],
                            'lang_name' => $v['lang_value'],
                            'description' => $theForm['description'][$v['lang_value']],
                            'content' => $theForm['content'][$v['lang_value']],
                            'slug' => str_replace(' ','-',MyController::move_dau($theForm['name'][$v['lang_value']])),
                            'seo_title' => $theForm['seo_title'][$v['lang_value']],
                            'seo_description' => $theForm['seo_description'][$v['lang_value']],
                    ], 'cate_id =:c_id and lang_name=:lang', array (
                        ':c_id' => $id,
                        ':lang' => $v['lang_value']
                    ) )->execute ();
                }			
                if ($r2) {
                    $action = 'Update category id :' . $id;
                    $result = '1';
                    MyController::actionSavelog ( $action, $result );
                }
            }else {
                $action = 'Update category id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
             }
              if($theCategory['tree']=='1'){
                return $this->redirect ( '@web/category' );
             }else{
                 return $this->redirect ( '@web/category/product' );
             }
        }
        return $this->render ( 'category_u', [
                'model' => $theCategory,
                'theForm' => $theForm,
                'lang_use' => $lang_use,
                'categoryHas' => $categoryHas ,
                'id' => $id
        ] );
    }
    public function actionD($id = '') {
        $model = $this->findModel($id);
        if (! $model|| $model['status'] === 0) {
            throw new HttpException ( 404, 'Post not found' );
        }
        if($model->isRoot()){
            $result = $model->deleteWithChildren();
        }else {
            $result = $model->delete();
        }
        if ($result) {
            $action = 'Delete category id :' . $id;
            $result = '1';
            MyController::actionSavelog ( $action, $result );
        } else {
            $action = 'Delete category :' . $id;
            $result = '0';
            MyController::actionSavelog ( $action, $result );
        }
        return $this->redirect ( [
                'index'
        ] );
    }
//END BÀI VIẾT
//GIỚI THIỆU
    public function actionAbout(){
        Yii::$app->params['page_title'] = "Bài viết trang giới thiệu";
        Yii::$app->params['page_action'] = "";
        $theCategory = Category::find()
                        ->where(['id' => '2'])						
                        ->with ( [
                            'category_detail_update',
                        ] )
                        ->limit(1)
                        ->one ();		              								
        if (! $theCategory || $theCategory['status'] === 0) {
            throw new HttpException ( 404, 'Category not found' );
        }
        $lang_use = LangModel::find()->asArray()->all(); 
        $categoryHas = $this->CategoryHas(2,2);
        $a = [];
        foreach($lang_use as $k => $v){
            $a['name'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['title'];
            $a['description'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['description'];
            $a['content'][$v['lang_value']] = html_entity_decode($theCategory['category_detail_update'][$k]['content']);
            $a['seo_title'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['seo_title'];    
            $a['seo_description'][$v['lang_value']] = $theCategory['category_detail_update'][$k]['seo_description'];    
        }
        $theForm = new CategoryForm();
        $theForm->scenario = 'create';
        $theForm->setAttributes ( $theCategory->getAttributes (), false );
        $theForm->name = $a['name'];
        $theForm->description = $a['description'];
        $theForm->content = $a['content'];			
        $theForm->seo_title = $a['seo_title'];
        $theForm->seo_description = $a['seo_description'];
        $id = '2';
        if ($theForm->load( Yii::$app->request->post ()) && $theForm->validate ()) {
            $theCategory->updated_at = NOW;
            $theCategory->updated_by = USER_ID;
            $theCategory->status = 1;			
            if($theCategory->save ()){
                if ($id != '') {
                    $theForm->avatar = UploadedFile::getInstance($theForm, 'avatar');
                    if($theForm->avatar != null){
                        if ($theForm->upload_avatar($id,'category')) {
                            $filename = MyController::move_dau($theForm->avatar->name);	
                            $theCategory->avatar = Yii::$app->params['homesite'].'/upload/category/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    
                    $theForm->avatar_2 = UploadedFile::getInstance($theForm, 'avatar_2');
                    if($theForm->avatar_2 != null){
                        if ($theForm->upload_avatar_2($id,'category')) {
                            $filename = MyController::move_dau($theForm->avatar_2->name);
                            $theCategory->avatar_2 = Yii::$app->params['homesite'].'/upload/category/avatar_2/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    
                    $theCategory->update ();
                    foreach($lang_use as $k => $v){   
                        $r2 = Yii::$app->db->createCommand ()->update ( '{{%category_detail}}', [
                                'title' => $theForm['name'][$v['lang_value']],
                                'lang_name' => $v['lang_value'],
                                'description' => $theForm['description'][$v['lang_value']],
                                'content' => MyController::ConvertData($theForm['content'][$v['lang_value']]),
                                'slug' => str_replace(' ','-',MyController::move_dau($theForm['name'][$v['lang_value']])),
                                'seo_title' => $theForm['seo_title'][$v['lang_value']],
                                'seo_description' => $theForm['seo_description'][$v['lang_value']],
                        ], 'cate_id =:c_id and lang_name=:lang', array (
                            ':c_id' => $id,
                            ':lang' => $v['lang_value']
                        ) )->execute ();
                    }
                    if ($r2) {
                        $action = 'Update about id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }else {
                    $action = 'Update about id :' . $id;
                    $result = '0';
                    MyController::actionSavelog ( $action, $result );
                }
                
                    return $this->redirect(['about']);
            }else{
                exit('a');
            }
        }
        return $this->render ( 'category_about', [
                'model' => $theCategory,
                'theForm' => $theForm,
                'lang_use' => $lang_use,
                'id' => $id,
                'categoryHas' => $categoryHas
        ] );
    }
//END GIOI THIEU    
//PRODUCT
    public function actionProduct() {
        Yii::$app->params['page_title'] = "Sản phẩm";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/category/product-create";
        $query = Category::find ()
        ->where (['status' => '1', 'tree'=> '3'])
        ->andWhere('depth > 0');
        
        $the_category = $query
        ->orderBy ( '{{%category}}.lft' )
        ->with ( [
            'category_detail' => function ($query) {
                $query->andWhere ( [
                    'lang_name' => Yii::$app->language
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
            'theCategory'=> $the_category,
        ] );
    }
    
    public function actionProductCreate() {
        Yii::$app->params['page_title'] = "Sản phẩm";
        Yii::$app->params['page_action'] = "Thêm danh mục sản phẩm";
        $model = new Category ();
        $lang_use = LangModel::find()->asArray()->all();
        $categoryHas = $this->CategoryHas(0,3);
        $theForm = new CategoryForm();
        $theForm->scenario = 'create';

        if ($theForm->load(Yii::$app->request->post()) && $theForm->validate()) {
            $model->position = '0';
            $model->created_by = USER_ID;
            $model->updated_by = USER_ID;
            $model->created_at = NOW;
            $model->updated_at = NOW;
            $model->status = '1';
            $parent_id = $theForm->cate_id;
            $model->parent_id = $parent_id;
            
            if (empty ( $parent_id )) {
                $model->makeRoot ();
            } else {
                $parent = Category::findOne ( $parent_id );
                $a = $model->appendTo ($parent);
                $child_id = $model->id;
                if ($child_id != '') {
                    $model->update ();
                    foreach($lang_use as $k => $v){
                        $r2 = Yii::$app->db->createCommand ()->insert ( '{{%category_detail}}', [
                            'cate_id' => $child_id,
                            'title' => $theForm['name'][$v['lang_value']],
                            'lang_name' => $v['lang_value'],
                            'description' => $theForm['description'][$v['lang_value']],
                            'content' => $theForm['content'][$v['lang_value']],
                            'slug' => str_replace(' ','-',MyController::move_dau($theForm['name'][$v['lang_value']])),
                            'seo_title' => $theForm['seo_title'][$v['lang_value']],
                            'seo_description' => $theForm['seo_description'][$v['lang_value']],
                        ] )->execute ();
                    }
                    //avartar
                    $theForm->avatar = UploadedFile::getInstance($theForm, 'avatar');
                    if ($theForm->upload_avatar($child_id,'category')) {
                        $filename = MyController::move_dau($theForm->avatar->name);
                        $model->avatar = Yii::$app->params['homesite'].'/upload/category/avatar/'.$child_id.'/'.$filename;
                        $model->update ();
                    }else{
                        exit();
                    }
                    if ($r2) {
                        $action = 'Created category Product id :' . $child_id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }else {
                        $action = 'Created category Product id :' . $child_id;
                        $result = '0';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            }
            return $this->redirect ( '@web/category/product' );
        }
        return $this->render ( 'category_c', [
            'model' => $model ,
            'theForm' => $theForm,
//             'theParent' => $the_category,
            'lang_use' => $lang_use,
            'categoryHas' => $categoryHas
        ] );
    }
//END PRODUCT
//PHAN CHUNG
    private function CategoryHas($id , $tree){
        $CategoryHas = '';
        if($id != 0 && $tree != 2){
            $CategoryHas = Category::find ()
            ->select('id, depth')
            ->where (['tree' => $tree])
            ->andWhere('id <> '. $id)
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
        }else{
            if($tree == 1){
                $CategoryHas = Category::find ()
                ->select('id, depth')
                ->where (['tree' => $tree , 'id' => '1'])
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
            }else{
                $CategoryHas = Category::find ()
                ->select('id, depth')
                ->where (['tree' => $tree])
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
            }
        }        
        return $CategoryHas;
    }
    
    public function actionMoveup($id){
        $model = $this->findModel($id);
        $a = $model->prev()->one();
        if(isset($a)){
            $model->insertBefore($a);
        }
        return $this->redirect(['index']);
    }
    public function actionMovedown($id){
        $model = $this->findModel($id);
        $a = $model->next()->one();
        if(isset($a)){
            $model->insertAfter($a);
        }
        return $this->redirect(['index']);
    }
    protected function findModel($id) {
        if (($model = Category::findOne ( $id )) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException ( 'The requested page does not exist.' );
        }
    }
    
}
