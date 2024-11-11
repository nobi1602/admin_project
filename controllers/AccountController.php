<?php

namespace app\controllers;

use Yii;
use app\models\UserModel;
use app\models\PermissionModel;
use yii\web\HttpException;
use yii\data\Pagination;
use yii\web\UploadedFile;

class AccountController extends MyController {
    public function actions()
    {
        Yii::$app->params['page_id'] = "account";
    }
    public function actionIndex() {
        if(strpos($_SESSION['list_per_check'], 'account_view') == false ){
            return $this->render ('/error/error', ['name' => 'WRONG', 'message' => 'PAGE NOT FOUND']);
        }else{
            Yii::$app->params['page_title'] = "Danh sách tài khoản";
            Yii::$app->params['page_action'] = "";
            Yii::$app->params['page_action_url'] = "/account/c";
            $query = UserModel::find ()->where('status !="0"');
            $getName = Yii::$app->request->get ('g_name', '' );
            if($getName != ''){
                $query->andWhere('name like :name',[':name'=> '%'.$getName.'%']);
            }
            $countQuery = clone $query;
            $pages = new Pagination ( [
                'totalCount' => $countQuery->count (),
//                 'pageSize' => 10
            ] );
            $the_user = $query->orderBy ( 'id' )
            ->offset ( $pages->offset )
            ->limit ( $pages->limit )
            ->asArray()
            ->all ();
            return $this->render ( 'index', [
                'name' => $getName,
                'the_user' => $the_user,
                'pages' => $pages
            ] );
        }
    }
    
    public function actionC() {
            Yii::$app->params['page_title'] = "Tài khoản";
            Yii::$app->params['page_action'] = "Tạo tài khoản";
//         if(strpos($_SESSION['list_permission']['permission_values'], 'account_create') == false ){
//             return $this->render ('/site/error', ['name' => 'WRONG', 'message' => 'PAGE NOT FOUND']);
//         }else{
            $theUser = new UserModel ();
            $theUser->scenario = 'create';
            
            $list_permission =  $query = PermissionModel::find ()
            ->select('id, permission_name')
            ->where ( [ 'status' => '1' ] )
            ->orderBy ( 'id' )
            ->asArray()
            ->all ();
            
            if ($theUser->load ( Yii::$app->request->post () ) && $theUser->validate ()) {
                
                $theUser->created_at = NOW;
                $theUser->created_by = USER_ID;
                $theUser->updated_at = NOW;
                $theUser->updated_by = USER_ID;
                
                $theUser->password = md5 ( $theUser->password ) . 'n0b1';
                
                $theUser->avatar = UploadedFile::getInstance($theUser, 'avatar');
                
                $result = $theUser->save ( false );
                if ($result) {
                    $id = $theUser->id;
                    if ($id != '') {
                        $theUser->avatar = UploadedFile::getInstance($theUser, 'avatar');
                        if($theUser->avatar != null){
                            if ($theUser->upload($id)) {
                                $pic_name =  MyController::createSlug($theUser->avatar->name);
                                $theUser->avatar = Yii::$app->params['homesite'].'/upload/user/'.$id.'/'.$pic_name;
                            }else{
                                exit();
                            }
                        }
                        $theUser->update ();
                        $id = $theUser->id;
                        $action = 'Created user id :'.$id;
                        $result = '1';
                        MyController::actionSavelog($action , $result);
                    }
                }else{
                    $id = $theUser->id;
                    $action = 'Created user id :'.$id;
                    $result = '0';
                    MyController::actionSavelog($action , $result);
                }
                
                
                return $this->redirect('@web/account');
            }
            
            return $this->render ( 'users_c', [
                'theUser' => $theUser,
                'list_permission' => $list_permission
            ] );
//         }        
    }
    
    public function actionU($id = ''){
        Yii::$app->params['page_title'] = "Tài khoản";
        Yii::$app->params['page_action'] = "Cập nhật thông tin";
//         if(strpos($_SESSION['list_permission']['permission_values'], 'account_update') == false ){
//             return $this->render ('/site/error', ['name' => 'WRONG', 'message' => 'PAGE NOT FOUND']);
//         }else{
            $theUser = UserModel::find()
            ->where(['id'=>$id])
            ->one();
            
            if (!$theUser || $theUser['status'] === 0) {
                return $this->render ('/site/error', ['name' => 'WRONG', 'message' => 'USER NOT FOUND']);
            }
            
            $list_permission =  $query = PermissionModel::find ()
            ->select('id, permission_name')
            ->where ( [ 'status' => '1' ] )
            ->orderBy ( 'id' )
            ->asArray()
            ->all ();
            
            $old_avt = $theUser->avatar;
            
            $theUser->scenario = 'update';
            
            if ($theUser->load ( Yii::$app->request->post () ) && $theUser->validate ()) {
                $theUser->updated_at = NOW;
                $theUser->updated_by = 0;
          
                
                $theUser->avatar = UploadedFile::getInstance($theUser, 'avatar');
                if($theUser->avatar != null){
                    if ($theUser->upload($id)) {
                        $pic_name = MyController::createSlug($theUser->avatar->name);
                        $theUser->avatar = Yii::$app->params['homesite'].'/upload/user/'.$id.'/'.$pic_name;
                    }else{
                        exit();
                    }
                }else{
                    $theUser->avatar = $old_avt;
                }
                $result = $theUser->save ( false );
                
                if($result){
                    $action = 'updated information user id :'.$id;
                    $result = '1';
                    MyController::actionSavelog($action , $result);
                }else{
                    $action = 'updated information user id :'.$id;
                    $result = '0';
                    MyController::actionSavelog($action , $result);
                }
                
                return $this->redirect('@web/account');
            }
            
            return $this->render ( 'users_u', [
                'theUser' => $theUser,
                'list_permission' => $list_permission
            ] );
//         }        
    }
    
    public function actionD($id = ''){
//         if(strpos($_SESSION['list_permission']['permission_values'], 'account_delete') == false ){
//             return $this->render ('/site/error', ['name' => 'WRONG', 'message' => 'PAGE NOT FOUND']);
//         }else{
            $theUser = UserModel::find()
            ->where(['id'=>$id])
            ->one();
            
            if (!$theUser || $theUser['status'] === 0) {
                return $this->render ('/site/error', ['name' => 'WRONG', 'message' => 'USER NOT FOUND']);
            }
            
            $result = Yii::$app->db->createCommand()->update(
                '{{%account_admin}}', [
                    'updated_at'=>NOW,
                    'updated_by'=>USER_ID,
                    'status'=>'0',
                ], ['id'=>$id])->execute();
                
            if($result){
                $action = 'Delete user id :'.$id;
                $result = '1';
                MyController::actionSavelog($action , $result);
            }else{
                $action = 'Delete user id :'.$id;
                $result = '0';
                MyController::actionSavelog($action , $result);
            }
            return $this->redirect('@web/account');
        }        
//     }
}
