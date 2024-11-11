<?php

namespace app\controllers;

use Yii;
use app\models\Category;
use app\models\LangModel;
use app\models\Slide;
use app\models\CategoryForm;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\HttpException;


/**
 * SlideController implements the CRUD actions for Slide model.
 */
class SlideController extends MyController {

    public function actions()
    {
        Yii::$app->params['page_id'] = "slide";
    }
    
    public function actionIndex($action = '' ) {
        Yii::$app->params['page_title'] = "Danh sách Slide";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/slide/c";
        $query = Slide::find ()
                ->where (['status' => '1'])
                ->andWhere('depth > 0')
                ->orderBy('lft');
        $the_Slide = $query
        ->with ([
                    'slide_detail' => function ($query) {
                        $query->andWhere ( [
                            'lang_name' => Yii::$app->language
                        ]);			
                    },
                    'user_created' => function ($query) {
                        $query->select ( [
                            'id','username'
                        ] );
                    }
                ])
        ->asArray()
        ->all (); 	
        if($action == 'update_use'){
            $list_id = $_POST['list_id'];
            $id_s = explode(",",$list_id);
            foreach($id_s as $id){
                $sql = 'UPDATE {{%slide}} SET use_slide = "0" WHERE id =:id  ';
                Yii::$app->db->createCommand($sql, [':id'=>$id])->execute();  
            }                   
            $id_check = rtrim($_POST['id_check'], ',');
            $id_s = explode(",",$id_check);
            foreach($id_s as $id){
                $sql = 'UPDATE {{%slide}} SET {{%slide}}.use_slide = "1" WHERE id =:id  ';
                Yii::$app->db->createCommand($sql, [':id'=>$id])->execute();  
            }                   
            $this->genderfile();
        }
        return $this->render ( 'index', [
                'theSlide'=> $the_Slide,
        ] );
    }

    public function actionC() {
        Yii::$app->params['page_title'] = "Slide";
        Yii::$app->params['page_action'] = "Tạo mới";
        $model = new Slide ();
        
        $theForm = new CategoryForm();
        $lang_use = LangModel::find()->asArray()->all();
        $theForm->scenario = 'create_slide';
        
        $query = Slide::find ()
        ->select('id, depth')
        ->where (['status' => '1']);	
        
        $the_Slide = $query
        ->orderBy ( '{{%slide}}.lft' )			
        ->with ( [
            'slide_detail' => function ($query) {
                $query->andWhere ( [
                        'lang_name' => Yii::$app->language
                ]);
            }    
        ])
        ->asArray()
        ->all (); 
        
        if ($theForm->load(Yii::$app->request->post()) && $theForm->validate() && Yii::$app->request->isPost) {
            
            $post= Yii::$app->request->post();
            
            $model->position = '0';
            $model->created_by = USER_ID;
            $model->updated_by = USER_ID;
            $model->created_at = NOW;
            $model->updated_at = NOW;
            $model->status = '1';
            $model->use_slide = $theForm->use;
            
            $parent_id = '1';
            $model->parent_id = $parent_id;
            
            if (empty ( $parent_id )) {
                $model->makeRoot();				
            } else {

                $parent = Slide::findOne ( $parent_id );                
                $model->appendTo ($parent);
                $child_id = $model->id;

                if ($child_id != '') {
                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->insert ( '{{%slide_detail}}', [
                                'cate_id' => $child_id,
                                'title' => $theForm['name'][$v['lang_value']],
                                'lang_name' => $v['lang_value'],                           
                                'description' => $theForm['description'][$v['lang_value']],
                                'content' => $theForm['content'][$v['lang_value']],
                                'slug' => str_replace(' ','-',MyController::move_dau($theForm['name'][$v['lang_value']])),                     
                        ] )->execute ();
                    }	

                    $theForm->avatar = UploadedFile::getInstance($theForm, 'avatar');
                    if ($theForm->upload_slide($child_id)) {
                        $filename = MyController::move_dau($theForm->avatar->name);     
                        $model->avatar = Yii::$app->params['homesite'].'/upload/slide/'.$child_id.'/'.$filename;
                        $model->update();
                    }else{
                        exit();
                    }
                        
                    if ($r2) {
                        $action = 'Created Slide id :' . $child_id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }else {
                        $action = 'Created Slide id :' . $child_id;
                        $result = '0';
                        MyController::actionSavelog ( $action, $result );
                    }
                }	
            }
            $this->genderfile();
            return $this->redirect ( '@web/slide' );
        }
        
        return $this->render ( 'slide_c', [ 
            'model' => $model ,
            'theForm' => $theForm,
            'theParent' => $the_Slide, 
            'lang_use' => $lang_use,
        ] );
    }
    
    public function actionU($id = '') {
        Yii::$app->params['page_title'] = "Slide";
        Yii::$app->params['page_action'] = "Cập nhật";
        $theSlide = Slide::find()
                        ->where(['id' => $id])						
                        ->with ( [
                            'slide_detail_update' ,				
                        ] )
                        ->limit(1)
                        ->one ();										
        if (! $theSlide || $theSlide['status'] === 0) {
            throw new HttpException ( 404, 'Slide not found' );
        }
        
        $lang_use = LangModel::find()->asArray()->all();       
        $a = [];
        foreach($lang_use as $k => $v){
            $a['name'][$v['lang_value']] = $theSlide['slide_detail_update'][$k]['title'];
            $a['description'][$v['lang_value']] = $theSlide['slide_detail_update'][$k]['description'];            
            $a['content'][$v['lang_value']] = $theSlide['slide_detail_update'][$k]['content'];
            $a['seo_title'][$v['lang_value']] = $theSlide['slide_detail_update'][$k]['seo_title'];           
            $a['seo_description'][$v['lang_value']] = $theSlide['slide_detail_update'][$k]['seo_description'];            
        }

        $theForm = new CategoryForm();
        $theForm->scenario = 'create_slide';
        $theForm->setAttributes ( $theSlide->getAttributes (), false );
        
        $theForm->name = $a['name'];
        $theForm->description = $a['description'];
        $theForm->content = $a['content'];			
        $theForm->seo_title = $a['seo_title'];
        $theForm->seo_description = $a['seo_description'];
        
        $theForm->use = $theSlide ['use_slide'];
        $theForm->avatar = $theSlide ['avatar'];

        if ($theForm->load(Yii::$app->request->post()) && $theForm->validate() && Yii::$app->request->isPost) {

            $theSlide->position = '0';
            $theSlide->updated_by = USER_ID;
            $theSlide->updated_at = NOW;
            $theSlide->status = '1';
            $theSlide->use_slide = $theForm->use;
            $theForm->avatar = UploadedFile::getInstance($theForm, 'avatar');
            
            if($theForm->avatar!= null){
                if ($theForm->upload_slide($id)) {
                    $filename = MyController::move_dau($theForm->avatar->name);  
                    $theSlide->avatar = Yii::$app->params['homesite'].'/upload/slide/'.$id.'/'.$filename;
                }else{
                    exit();
                }
            }
            
            $parent_id = '1';
            if($parent_id != $theSlide['parentId']){
                if($theSlide->save()){
                    if(empty($parent_id)){
                        $theSlide->makeRoot();
                    }else { //change root
                        $parent = Slide::findOne($parent_id);
                        $theSlide->appendTo($parent);
                    }					
                }
            }else{
                $theSlide->save();
            }
            $id = $theSlide->id;
            if ($id != '') {
                foreach($lang_use as $k => $v){                        
                    $r2 = Yii::$app->db->createCommand ()->update ( '{{%slide_detail}}', [                            
                            'title' => $theForm['name'][$v['lang_value']],
                            'lang_name' => $v['lang_value'],                           
                            'description' => $theForm['description'][$v['lang_value']],
                            'content' => $theForm['content'][$v['lang_value']],
                            'slug' => str_replace(' ','-',MyController::move_dau($theForm['name'][$v['lang_value']])),                                                     
                    ], 'cate_id =:c_id and lang_name=:lang', array (
                        ':c_id' => $id,
                        ':lang' => $v['lang_value'],                
                    ) )->execute ();
                }	              
                
                if ($r2) {
                    $action = 'Update Slide id :' . $id;
                    $result = '1';
                    MyController::actionSavelog ( $action, $result );
                }
            }else {
                $action = 'Update Slide id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }
            $this->genderfile();
            return $this->redirect(['index']);
        }
        return $this->render ( 'slide_u', [
                'model' => $theSlide,
                'theForm' => $theForm,
                'lang_use' => $lang_use,
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
            $action = 'Delete Slide id :' . $id;
            $result = '1';
            MyController::actionSavelog ( $action, $result );
        } else {
            $action = 'Delete Slide :' . $id;
            $result = '0';
            MyController::actionSavelog ( $action, $result );
        }
        $this->genderfile();
        return $this->redirect ( [
                'index'
        ] );
    }
    public function actionMoveup($id){
        $model = $this->findModel($id);
        $a = $model->prev()->one();
        if(isset($a)){
            $model->insertBefore($a);
        }        
        $this->genderfile();
        return $this->redirect(['index']);
    }
    public function actionMovedown($id){
        $model = $this->findModel($id);
        $a = $model->next()->one();
        var_dump($a);exit();
        if(isset($a)){
            $model->insertAfter($a);
            exit('a');
        }        else{
            exit('b');
        }
        $this->genderfile();
        return $this->redirect(['index']);
    }
    protected function findModel($id) {
        if (($model = Slide::findOne ( $id )) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException ( 'The requested page does not exist.' );
        }
    }
    private function SelectCategory(){
        $CategoryHas = '';
        $CategoryHas = Category::find ()
        ->select('id,depth')
        ->where (['status' => '1'])
        ->andWhere('tree <> 2 ')
        ->orderBy ( '{{%category}}.tree' )
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

    private function Genderfile(){
        $query = Slide::find ()
            ->select('id, avatar,lft')
            ->where (['status' => '1'])
            ->andWhere('depth > 0')
            ->andWhere('use_slide = 1')
            ->with ( [
                        'slide_detail_update',				
                    ] )
            ->orderBy ( '{{%slide}}.lft' )
            ->asArray()
            ->all (); 	
        $lang_use = LangModel::find()->asArray()->all(); 
        foreach($lang_use as $k => $v){
            $filename= 'slide_'.$v['lang_value'].'.php';
            $myfile = fopen($filename, "w") or die("Unable to open file!");
            $txt = "";
            foreach($query as $key => $value){
                $txt .= '<div class="text-center item bg-img" data-overlay-dark="4" data-background="'.$value['avatar'].'">';
                $txt .= '   <div class="v-middle caption">';
                $txt .= '       <div class="container">';
                $txt .= '           <div class="row">';
                $txt .= '               <div class="col-md-10 offset-md-1">';
                $txt .= '                   <span>';
                $txt .= '                        <i class="star-rating"></i>';
                $txt .= '                        <i class="star-rating"></i>';
                $txt .= '                        <i class="star-rating"></i>';
                $txt .= '                        <i class="star-rating"></i>';
                $txt .= '                        <i class="star-rating"></i>';
                $txt .= '                   </span>';
                $txt .= '                   <h4>'.$value['slide_detail_update'][$k]['title'].'</h4>';
                $txt .= '                   <h1>'.$value['slide_detail_update'][$k]['description'].'</h1>';
                $txt .= '                   <div class="butn-light mt-30 mb-30"> <a href="#" data-scroll-nav="1"><span>Rooms & Suites</span></a> </div>';
                $txt .= '               </div>';
                $txt .= '           </div>';
                $txt .= '       </div>';
                $txt .= '   </div>';
                $txt .= '</div>';
            }
            fwrite($myfile, $txt);
            fclose($myfile);
        }    
    }
}