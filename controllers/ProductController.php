<?php
namespace app\controllers;
use Yii;
use app\models\LangModel;
use app\models\ProductModel;
use app\models\PostForm;
use app\models\Category;
use yii\web\HttpException;
use yii\web\UploadedFile;
class ProductController extends MyController {
    public function actions()
    {
        Yii::$app->params['page_id'] = "product";
    }
    public function actionTest(){
        
        $t = unlink('./upload/test/Untitled-1.png');
        var_dump($t);exit();
    }
    public function actionIndex() {
        Yii::$app->params['page_title'] = "Danh sách sản phẩm";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/product/c";
        $query = ProductModel::find ()->where ( [ 
                'status' => '1' 
        ] );
        $the_post = $query
            ->orderBy ( '{{%product}}.id' )
            ->with ( [ 
                'product_detail' => function ($query) {
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
        Yii::$app->params['page_title'] = 'Sản phẩm';
        Yii::$app->params['page_action'] = 'Thêm sản phẩm mới';
        $thePost = new ProductModel ();
        $theCategoryHas =Category::find ()
            ->select('id, depth')
            ->where (['status' => '1' , 'tree' => '3'])
            ->andWhere('depth > 0')
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
        $lang_use = LangModel::find()->asArray()->all();    	
        $thePostForm = new PostForm ();
        $thePostForm->scenario = 'create';
        if ($thePostForm->load ( Yii::$app->request->post () ) && $thePostForm->validate ()) {
            $thePost->created_at = NOW;
            $thePost->created_by = USER_ID;
            $thePost->updated_at = NOW;
            $thePost->updated_by = USER_ID;
            $thePost->prod_hot = $thePostForm->hot_prod;
            $thePost->status = 1;
            $thePost->cate_id = $thePostForm->cate_id;
            $result = $thePost->save ( false );            
            if ($result) {
                $id = $thePost->id;                
                if ($id != '') {
                    $thePostForm->avatar = UploadedFile::getInstance($thePostForm, 'avatar');
                    if($thePostForm->avatar != null){
                        if ($thePostForm->upload($id,'prod')) {
                            $filename = MyController::move_dau($thePostForm->avatar->name);  
                            $thePost->avatar = Yii::$app->params['homesite'].'/upload/prod/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    
                    $thePostForm->catalog = UploadedFile::getInstances($thePostForm, 'catalog');
                    $avt = [];
                    if ($thePostForm->upload_catalog($id)) {
                        foreach($thePostForm->a as $key => $value){
                            $avt[] = '/upload/cataloge/'.$id.'/'.$value->name;
                        }
                        $thePost->catalog = serialize($avt);
                    }
                    $thePost->update ();
                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->insert ( '{{%product_detail}}', [ 
                                'prod_id' => $id,
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
                        $action = 'Created product id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            } else {
                $action = 'Created product id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }            
            return $this->redirect ( '@web/product' );
        }        
        return $this->render ( 'post_c', [ 
                'thePost' => $thePost,
                'theCategoryHas' => $theCategoryHas,
                'thePostForm' => $thePostForm,
                'lang_use' => $lang_use 
        ] );
    }
    public function actionU($id = '') {
        Yii::$app->params['page_title'] = "Sản phẩm";
        Yii::$app->params['page_action'] = "Cập nhật sản phẩm";
        $thePost = ProductModel::find ()->where ( [ 
                'id' => $id 
        ] )->with ( [ 
                'product_detail_update',
                'category_detail' => function ($query) {
                    $query->andWhere ( [ 
                        'lang_name' => Yii::$app->language 
                    ] );
                } 
        ] )->one ();       
        if (! $thePost || $thePost ['status'] === 0) {
            throw new HttpException ( 404, 'Product not found' );
        }        
        $lang_use = LangModel::find()->asArray()->all();    	
        $a = [];
        foreach($lang_use as $k => $v){
            $a['name'][$v['lang_value']] = $thePost['product_detail_update'][$k]['title'];
            $a['description'][$v['lang_value']] = $thePost['product_detail_update'][$k]['description'];
            $a['content'][$v['lang_value']] = $thePost['product_detail_update'][$k]['content'];
            $a['tags'][$v['lang_value']] = $thePost['product_detail_update'][$k]['tags'];
            $a['seo_title'][$v['lang_value']] = $thePost['product_detail_update'][$k]['seo_title'];    
            $a['seo_description'][$v['lang_value']] = $thePost['product_detail_update'][$k]['seo_description'];    
        }
        $theCategoryHas = Category::find ()
            ->select('id, depth')
            ->where (['status' => '1' , 'tree' => '3'])
            ->andWhere('depth > 0')
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
        $thePostForm = new PostForm ();
        $thePostForm->scenario = 'create_prod';
        $thePostForm->setAttributes ( $thePost->getAttributes (), false );
        $thePostForm->cate_id = $thePost['cate_id'];
        $thePostForm->hot_prod = $thePost['prod_hot'];
        $thePostForm->catalog = $thePost['catalog'];
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
            $thePost->prod_hot = $thePostForm->hot_prod;
            $result = $thePost->save ( false );
            if ($result) {
                $id = $thePost->id;
                if ($id != '') {
                    $thePostForm->avatar = UploadedFile::getInstance($thePostForm, 'avatar');
                    if($thePostForm->avatar != null){
                        if ($thePostForm->upload($id,'prod')) {
                            $filename = MyController::move_dau($thePostForm->avatar->name);  
                            $thePost->avatar = Yii::$app->params['homesite'].'/upload/prod/avatar/'.$id.'/'.$filename;
                        }else{
                            exit();
                        }
                    }
                    
                    $thePostForm->catalog = UploadedFile::getInstances($thePostForm, 'catalog');
                    $avt = [];
                    if ($thePostForm->upload_catalog($id)) {
                        foreach($thePostForm->a as $key => $value){
                            $filename = MyController::move_dau($value->name);  
                            $avt[] = Yii::$app->params['homesite'].'/upload/cataloge/'.$id.'/'.$filename;
                        }
                        $thePost->catalog = serialize($avt);
                    }
                    $thePost->update ();
                    
                    foreach($lang_use as $k => $v){                        
                        $r2 = Yii::$app->db->createCommand ()->update ( '{{%product_detail}}', [                                
                                'title' => $thePostForm['name'][$v['lang_value']],
                                'lang_value' => $v['lang_value'],
                                'description' => $thePostForm['description'][$v['lang_value']],
                                'content' => $thePostForm['content'][$v['lang_value']],
                                'slug' => str_replace(' ','-',MyController::move_dau($thePostForm['name'][$v['lang_value']])),
                                'seo_title' => $thePostForm['seo_title'][$v['lang_value']],
                                'seo_description' => $thePostForm['seo_description'][$v['lang_value']],
                        ] , 'prod_id =:p_id and lang_value=:lang', array (
                            ':p_id' => $id,
                            ':lang' => $v['lang_value']
                        ) )->execute ();
                    }			
                    if ($r2) {
                        $action = 'Update product id :' . $id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            } else {
                $action = 'Update product id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }
            return $this->redirect ( '@web/product' );
        }
        
        return $this->render ( 'post_u', [ 
                'thePost' => $thePost,
                'theCategoryHas' => $theCategoryHas,
                'thePostForm' => $thePostForm,
                'lang_use' => $lang_use
        ] );
    }
    public function actionD($id = '') {
        $Post = ProductModel::find ()->where ( [ 
                'id' => $id 
        ] )->one ();
        if (! $Post || $Post ['status'] === 0) {
            throw new HttpException ( 404, 'Product not found' );
        }
        $result = Yii::$app->db->createCommand ()->update ( '{{%product}}', [ 
                'updated_at' => NOW,
                'updated_by' => USER_ID,
                'status' => '0' 
        ], [ 
                'id' => $id 
        ] )->execute ();
        
        if ($result) {
            $action = 'Delete product id :' . $id;
            $result = '1';
            MyController::actionSavelog ( $action, $result );
        } else {
            $action = 'Delete product id :' . $id;
            $result = '0';
            MyController::actionSavelog ( $action, $result );
        }
        return $this->redirect ( '@web/product' );
    }
    public function actionFiledelete($link,$id){
        //done
        $theCatalog = ProductModel::findOne($id);
        
        if (! $theCatalog || $theCatalog['status'] === 0) {
            throw new HttpException ( 404, 'Cataloge not found' );
        }
        $theCatalog->updated_at = NOW;
        $theCatalog->updated_by = USER_ID;
        $a = unserialize($theCatalog->catalog);
        foreach($a as $key => $avt){
            if($avt == $link){
                $exp = explode('/', $avt);
                $link_del = './'.$exp[3].'/'.$exp[4].'/'.$exp[5].'/'.$exp[6];
                unlink($link_del);
                array_splice($a,$key);
            }
        }
        $theCatalog->catalog = serialize($a);
        $theCatalog->update();
        return true;
    }
}