<?php

namespace app\controllers;

use Yii;
use app\models\LangModel;
use app\models\Menu;
use app\models\MenuForm;
use app\models\Category;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends MyController {
    
    /**
     * Lists all Menu models.
     *
     * @return mixed
     */
    public function actions()
    {
        Yii::$app->params['page_id'] = "menu";
    }
    public function actionIndex() {
        Yii::$app->params['page_title'] = "Danh sách menu";
        Yii::$app->params['page_action'] = "";
        Yii::$app->params['page_action_url'] = "/menu/c";
        $query = Menu::find ()
        ->where (['status' => '1'])
        ->andWhere('depth > 0');
        $the_Menu = $query
        ->orderBy ( '{{%menu}}.lft' )
        ->with ( [
            'menu_detail' => function ($query) {
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
            'theMenu'=> $the_Menu,
        ] );
    }

    public function actionC() {
        Yii::$app->params['page_title'] = "Menu";
        Yii::$app->params['page_action'] = "Tạo menu";
        
        $model = new Menu ();
        $theForm = new MenuForm();
        $theForm->scenario = 'create';
        $lang_use = LangModel::find()->asArray()->all();
        $the_Menu =  $this->GetMenu(0,'create');
        $theTreeHas = $this->CallTreeCate();
        $theCateHas = [];
        
        if ($theForm->load(Yii::$app->request->post()) && $theForm->validate() && !empty(Yii::$app->request->post())) {
            
            $post= Yii::$app->request->post();
            $model->position = '0';
            $model->created_by = USER_ID;
            $model->updated_by = USER_ID;
            $model->created_at = NOW;
            $model->updated_at = NOW;
            $model->tree_relate = $theForm['tree'];
            $model->cate_relate = $theForm['cate_relate'];
            $model->status = '1';
            $parent_id = $post['MenuForm']['parent_id'];
            $model->parent_id = $parent_id;
            if($theForm['tree']!= ''){
                if($theForm['tree']== '1'){
                    $foot = '-nc'.$theForm['cate_relate'];
                }else{
                    $foot = '-pc'.$theForm['cate_relate'];
                }
            }else{
                if($model->depth < 2){
                    $foot = '';
                }else{
                    $foot = '-a0';
                }
            }
            if (empty ( $parent_id )) {
                $model->makeRoot ();
            } else {
                $parent = Menu::findOne ( $parent_id );
                $model->appendTo ($parent);
                $child_id = $model->id;
                if ($child_id != '') {
                    foreach($lang_use as $k => $v){
                        $r2 = Yii::$app->db->createCommand ()->insert ( '{{%menu_detail}}', [
                            'cate_id' => $child_id,
                            'title' => $theForm['name'][$v['lang_value']],
                            'lang_name' => $v['lang_value'],
                            'slug' => '/'.str_replace(' ','-',MyController::createSlug($theForm['name'][$v['lang_value']])).$foot,
                        ] )->execute ();
                    }
                    if ($r2) {
                        $action = 'Created Menu id :' . $child_id;
                        $result = '1';
                        MyController::actionSavelog ( $action, $result );
                    }else {
                        $action = 'Created Menu id :' . $child_id;
                        $result = '0';
                        MyController::actionSavelog ( $action, $result );
                    }
                }
            }
            $this->genderfile();
            return $this->redirect(['index']);
        }
        
        return $this->render ( 'menu_c', [
            'model' => $model ,
            'theForm' => $theForm,
            'theParent' => $the_Menu,
            'lang_use' => $lang_use,
            'theTreeHas' => $theTreeHas,
            'theCateHas' => $theCateHas,
        ] );
    }
    
    public function actionU($id='', $action='') {
        Yii::$app->params['page_title'] = "Menu";
        Yii::$app->params['page_action'] = "Cập nhật";
        
        $theMenu = Menu::find()
        ->where(['id' => $id])
        ->with ( [
            'menu_detail_update',
        ] )
        ->limit(1)
        ->one ();
        
        if (! $theMenu || $theMenu['status'] === 0) {
            throw new HttpException ( 404, 'Menu not found' );
        }
        $lang_use = LangModel::find()->asArray()->all();
        
        $a = [];
        
        foreach($lang_use as $k => $v){
            $a['name'][$v['lang_value']] = $theMenu['menu_detail_update'][$k]['title'];
        }
        
        $theForm = new MenuForm();
        $theForm->scenario = 'create';
        $theForm->setAttributes ( $theMenu->getAttributes (), false );
        
        $theForm->name = $a['name'];
        $theForm->tree = $theMenu['tree_relate'];
        $theForm->cate_relate = $theMenu['cate_relate'];
        $the_Menu =  $this->GetMenu($id,'create');
        $theTreeHas = $this->CallTreeCate();
        $theCateHas = $this->actionCallcate($theForm->tree);
          
        if ($theForm->load( Yii::$app->request->post ()) && $theForm->validate () && !empty(Yii::$app->request->post('MenuForm')) ) {
            
            $theMenu->updated_at = NOW;
            $theMenu->updated_by = USER_ID;
            
            $theMenu->status = 1;
            
            $post= Yii::$app->request->post('MenuForm');
            $parent_id = $post['parent_id'];
            
            $theMenu->tree_relate = $theForm['tree'];
            $theMenu->cate_relate = $theForm['cate_relate'];
            
            if($theMenu->save()){
                if($parent_id != $theMenu['parentId']){
                    if(empty($parent_id)){
                        $theMenu->makeRoot();
                    }else { //change root
                        $parent = Menu::findOne($parent_id);
                        $theMenu->appendTo($parent);
                    }
                }
            }else{
                exit('aaa');
            }

            $id = $theMenu->id;
            if ($id != '') {
                if($theForm['tree']!= ''){
                    if($theForm['tree']== '1'){
                        $foot = '-nc'.$theForm['cate_relate'];
                    }else{
                        $foot = '-pc'.$theForm['cate_relate'];
                    }
                }else{
                    if($theMenu->depth < 2 ){
                        $foot = '';
                    }else{
                        $foot = '-a0';
                    }
                }
                foreach($lang_use as $k => $v){
                    $mov = str_replace('ì','i',MyController::createSlug($theForm['name'][$v['lang_value']]));
                    $check = strpos($mov,'ì');
                    $r2 = Yii::$app->db->createCommand ()->update ( '{{%menu_detail}}', [
                        'title' => $theForm['name'][$v['lang_value']],
                        'lang_name' => $v['lang_value'],
                        'slug' => '/'.str_replace(' ','-',$mov).$foot,
                    ], 'cate_id =:c_id and lang_name=:lang', array (
                        ':c_id' => $id,
                        ':lang' => $v['lang_value']
                    ) )->execute ();
                }
                if ($r2) {
                    $action = 'Update Menu id :' . $id;
                    $result = '1';
                    MyController::actionSavelog ( $action, $result );
                }
            }else {
                $action = 'Update Menu id :' . $id;
                $result = '0';
                MyController::actionSavelog ( $action, $result );
            }
            $this->genderfile();
            return $this->redirect(['index']);
        }
        return $this->render ( 'menu_u', [
            'id_m' => $id,
            'model' => $theMenu,
            'theForm' => $theForm,
            'lang_use' => $lang_use,
            'theParent' => $the_Menu,
            'theTreeHas' => $theTreeHas,
            'theCateHas' => $theCateHas,
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
            $action = 'Delete Menu id :' . $id;
            $result = '1';
            MyController::actionSavelog ( $action, $result );
        } else {
            $action = 'Delete Menu :' . $id;
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
        if(isset($a)){
            $model->insertAfter($a);
        }
        $this->genderfile();
        return $this->redirect(['index']);
    }

    protected function findModel($id) {
        if (($model = Menu::findOne ( $id )) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException ( 'The requested page does not exist.' );
        }
    }
    
    private function GetMenu($id,$action){
        $query = Menu::find ()
        ->select('id, depth,lft,rgt,cate_relate,tree_relate')
        ->where (['status' => '1'])
        ->orderBy ( '{{%menu}}.lft');
        if($id != 0 ){
            $query->andWhere('id <> '. $id);
        }
        if($action=='update'){
            $query->with ( ['menu_detail_update'] );
        }else{
            $query->andWhere('depth < 2');
            $query->with ( [
                'menu_detail' => function ($query) {
                    $query->andWhere ( [
                        'lang_name' => Yii::$app->language
                    ]);
                }
                ]);
        }
        $the_Menu = $query
        ->asArray()
        ->all ();
        return $the_Menu;
    }
    
    private function CallTreeCate(){
        $TreeHas = Category::find ()
        ->select('id, depth')
        ->where (['status' => '1' , 'parent_id' => '0'])
        ->andWhere('id <> 2')
        ->orderBy ( '{{%category}}.lft' )
        ->with ( [
            'category_detail' => function ($query) {
                $query->select ( [
                    'id','cate_id','title'
                ]);
                $query->andWhere ( [
                    'lang_name' => Yii::$app->language
                ]);
            }
        ] )
        ->asArray()
        ->all (); 
        return $TreeHas;
    }
    
    public function actionCallcate($id=''){
        if($id!=''){
            $id_tree = $id;
        }else{
            if($id==0){
                return [];
            }else{
                $id_tree = $_POST['id_tree'];
            }
        }
        $CategoryHas = Category::find ()
        ->select('id, depth, status')
        ->where (['status' => '1','parent_id'=>$id_tree])
        ->orderBy ( '{{%category}}.lft' )
        ->with ( [
            'category_detail' => function ($query) {
                $query->select ( [
                    'id','cate_id','title'
                ]);
                $query->andWhere ( [
                    'lang_name' => Yii::$app->language
                ]);
            }
        ] )
        ->asArray()
        ->all ();
        foreach($CategoryHas as $t){
            $list[] = ['id'=>$t['id'] , 'name'=> $t['category_detail']['title'] ];
        }
        $a = ArrayHelper::map($list, 'id', 'name');
        if($id!=''){
            return $a;
        }else{
            return Json::encode([
                'status' => 200,
                'data'=> $a,
            ]);
        }
    }
    
    private function Genderfile(){
        $query = $this->GetMenu(0,'update');
        $lang_use = LangModel::find()->asArray()->all();
        foreach($lang_use as $k => $v){
            $filename= 'menu_'.$v['lang_value'].'.php';
            $myfile = fopen($filename, "w") or die("Unable to open file!");
            $txt = "";
            foreach($query as $key => $value){     
                if($query[$key]['lft'] + 1 == $query[$key]['rgt'] && $query[$key]['depth']==1){
                    $txt .= '<a href="'.$value['menu_detail_update'][$k]['slug'].'" class="nav-item nav-link">'.$value['menu_detail_update'][$k]['title'].'</a>';
                }
                if($query[$key]['lft'] + 1 != $query[$key]['rgt'] && $query[$key]['depth']==1){
                    $txt .= '<div class="nav-item dropdown">';
                    $txt .= '   <a href="'.$query[$key]['menu_detail_update'][$k]['slug'].'" class="nav-link dropdown-toggle" data-toggle="">'.$query[$key]['menu_detail_update'][$k]['title'].'</a>';
                    $txt .= '   <div class="dropdown-menu border-0 rounded-0 m-0">';
                }
                if($query[$key]['depth']== 2 ){
                    $txt .= '       <a href="'.$query[$key-1]['menu_detail_update'][$k]['slug'].''.$query[$key]['menu_detail_update'][$k]['slug'].'" class="dropdown-item">'.$value['menu_detail_update'][$k]['title'].'</a>';
                    if($query[$key+1]['depth'] != 2){
                        $txt .= '   </div>';
                        $txt .= '</div>';
                    }
                }
            }
            fwrite($myfile, $txt);
            fclose($myfile);
            
            $filename_footer = 'menu_footer_'.$v['lang_value'].'.php';
            $myfile_footer = fopen($filename_footer, "w") or die("Unable to open file!");
            $txt = "";
            foreach($query as $key => $value){
                if($query[$key]['depth']==1){
                    $txt .= '<li>';
                    $txt .= '<a href="'.$value['menu_detail_update'][$k]['slug'].'">'.$value['menu_detail_update'][$k]['title'].'</a>';
                    $txt .= '</li>';
                }
            }
            fwrite($myfile_footer, $txt);
            fclose($myfile_footer);
            
        }
    }
}