<?php

namespace app\controllers;

use Yii;
use app\models\PermissionModel;
use app\models\PermissionForm;
use yii\web\HttpException;

class PermissionController extends MyController {
    public function actionIndex() {
        $query = PermissionModel::find ()->where ( [ 
                'status' => '1' 
        ] );

        $the_permission = $query
            ->orderBy ( 'id' )         
            ->asArray()
            ->all ();

        return $this->render ( 'index', [ 
                'the_permission' => $the_permission,
        ] );
    }
    public function actionC() {
        $thePermission = new PermissionModel ();

        $thePermissionForm = new PermissionForm();

        if ($thePermissionForm->load ( Yii::$app->request->post () ) && $thePermissionForm->validate ()) {
            $newMetas = '';

            if($thePermissionForm['category']!= ''){
                foreach($thePermissionForm['category'] as $v){
                    $newMetas.= 'category_'.$v.',';
                }
            }
            if($thePermissionForm['post']!= ''){
                foreach($thePermissionForm['post'] as $v){
                    $newMetas.= 'post_'.$v.',';
                }                
            }
            if($thePermissionForm['permission']!= ''){
                foreach($thePermissionForm['permission'] as $v){
                    $newMetas.= 'permission_'.$v.',';
                }
            }
            if($thePermissionForm['slide']!= ''){
                foreach($thePermissionForm['slide'] as $v){
                    $newMetas.= 'slide_'.$v.',';
                }
            }
            $thePermission->permission_name = $thePermissionForm['name'];
            $thePermission->permission_values = $newMetas;
            $thePermission->status ='1';
            $result = $thePermission->save ( false );
           
            return $this->redirect ( '@web/permission' );
        }
        
        return $this->render ( 'permission_c', [ 
            'thePermission' => $thePermission,
            'thePermissionForm' => $thePermissionForm,
        ] );
    }
    
    public function actionU($id = '') {
        $thePermission = PermissionModel::find ()
            ->where (['id' => $id])            
            ->one ();
        
        if (! $thePermission || $thePermission ['status'] === 0) {
            throw new HttpException ( 404, 'Permission not found' );
        }

        $thePermissionForm = new PermissionForm();
       
        $a = explode (',', rtrim($thePermission['permission_values'],','));
        $cate_list = [];
        $post_list = [];
        $slide_list = [];
        $permission_list = [];

        foreach($a as $value){
            if(strpos($value, 'ory')){
                $str_val = str_replace( 'category_', '', $value );
                $cate_list[] = $str_val;               
            }
            if(strpos($value, 'ost')){
                $str_val = str_replace( 'post_', '', $value );
                $post_list[] = $str_val;   
            }
            if(strpos($value, 'lide')){
                $str_val = str_replace( 'slide_', '', $value );
                $slide_list[] = $str_val;   
            }
            if(strpos($value, 'ermiss')){
                $str_val = str_replace( 'permission_', '', $value );
                $permission_list[] = $str_val;   
            }
        }

        $thePermissionForm['name'] = $thePermission['permission_name'];
        $thePermissionForm['category'] = $cate_list;
        $thePermissionForm['post'] = $post_list;
        $thePermissionForm['slide'] = $slide_list;
        $thePermissionForm['permission'] = $permission_list;
        
        if ($thePermissionForm->load ( Yii::$app->request->post () ) && $thePermissionForm->validate ()) {
                                
            $newMetas = '';

            if($thePermissionForm['category']!= ''){
                foreach($thePermissionForm['category'] as $v){
                    $newMetas.= 'category_'.$v.',';
                }
            }
            if($thePermissionForm['post']!= ''){
                foreach($thePermissionForm['post'] as $v){
                    $newMetas.= 'post_'.$v.',';
                }                
            }
            if($thePermissionForm['permission']!= ''){
                foreach($thePermissionForm['permission'] as $v){
                    $newMetas.= 'permission_'.$v.',';
                }
            }
            if($thePermissionForm['slide']!= ''){
                foreach($thePermissionForm['slide'] as $v){
                    $newMetas.= 'slide_'.$v.',';
                }
            }

            $thePermission->permission_name = $thePermissionForm['name'];
            $thePermission->permission_values = $newMetas;
            $thePermission->status ='1';

            $result = $thePermission->save ( false );
            
            return $this->redirect ( '@web/permission' );
        }
        
        return $this->render ( 'permission_u', [ 
            'thePermission' => $thePermission,
            'thePermissionForm' => $thePermissionForm
        ] );
    }
    
    public function actionD($id = '') {
        $thePermission = PermissionModel::find ()
            ->where (['id' => $id])
            ->one ();
        
        if (! $thePermission || $thePermission ['status'] === 0) {
            throw new HttpException ( 404, 'Permission not found' );
        }
        
        $result = Yii::$app->db->createCommand ()->update ( '{{%permission}}', [ 
                'status' => '0' 
        ], [ 
                'id' => $id 
        ] )->execute ();
        
        return $this->redirect ( '@web/permission' );
    }
}