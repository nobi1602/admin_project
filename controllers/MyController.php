<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use app\models\PermissionModel;
use app\models\ContactModel;
use yii\helpers\Url;
use yii\data\Pagination;

class MyController extends Controller
{
	public function __construct($id, $module, $config = [])
	{
// 	    Yii::$app->params['homesite'] = Url::base(true);
		// Active Language
        $activeLanguage = "vi";	
		if (Yii::$app->user->isGuest) {
			$activeLanguage = "vi";	
		} else {
		    $activeLanguage = "vi";	
		    $_SESSION['check_er'] = '0';
			$_SESSION['list_permission'] = PermissionModel::find ()->where ( [ 
				'status' => '1' ,
			    'id' => Yii::$app->user->identity->id_permission
        	] )
        	->select('id , permission_values , url_home_page')
			->one ();        
			$string_per = $_SESSION['list_permission']['permission_values'];
			$_SESSION['list_per_check'] = $_SESSION['list_permission']['permission_values'];
			Yii::$app->params['homesite'] = $_SESSION['list_permission']['url_home_page'];
			Yii::$app->request->baseUrl = Yii::$app->params['homesite'];
// 			var_dump(Url::base(true));exit();
		}

		Yii::$app->language = $activeLanguage;
		
		if (!defined('MY_ID')) {
			if (Yii::$app->user->isGuest) {
				define('MY_ID', 0);			
				// exit();						
			} else {
				define('MY_ID', Yii::$app->user->identity->id);
			}
		}
		
		if (!defined('USER_ID')) {
			define('USER_ID', MY_ID);
		}
		
        $this->layout = 'layout';
		
        $contact_list = ContactModel::find()
        -> select('id,name,subject,created_at')
        -> where(['seen_by'=>'0'])
        -> orderBy ( 'created_at desc' );
        $countQuery = clone $contact_list;
        $pages = new Pagination ( [
            'totalCount' => $countQuery->count (),
            'pageSize' => 5
        ] );
        $contact = $contact_list
        ->offset ( $pages->offset )
        ->limit ( $pages->limit )
        ->asArray()
        ->all (); 	
        $txt = "";
        foreach($contact as $c ){
            $txt .= '<li class="message-item"> <a href="/contact/?id='.$c['id'].'"> <img src="/img/Asset-1.png" alt="" class="rounded-circle"> <div>';
            $txt .= '<h4>'.$c['name'].'</h4>';
            $txt .= '<p>'. $c['subject'].'</p>';
            $txt .= '<p style="color:red">'. $c['created_at'].'</p>';
            $txt .= '</div> </a> </li>';
            $txt .= '<li>
                      <hr class="dropdown-divider">
                    </li>';
        }
        Yii::$app->params['contact_list'] = $txt;
        Yii::$app->params['contact_count'] = $countQuery->count ();
        
        
        
		
		parent::__construct($id, $module, $config);				
	}
		
	public function actionSavelog($action, $result){
		Yii::$app->db->createCommand()
		->insert('{{%log}}', [
				'u_id'=> USER_ID,
				'action' => $action,
				'created_at' => NOW,
				'result' => $result,
		])
		->execute();
	}
	
	public function behaviors()
	{
		return [
			'AccessControl' => [
				'class' => \yii\filters\AccessControl::className(),
				'rules' => [
					[
						'allow'=>true,
						'roles' => ['@'],
					], [
						'allow'=>false,
					],
				]
			]
		];
	}

	public function move_dau($str)
	{
	    $str = preg_replace("/(á|à|ả|ã|ạ|ă|ắ|ằ|ẳ­|ẵ|ặ|â|ấ|ầ|ẫ|ậ|ẩ)/", 'a', $str);
	    $str = preg_replace("/(é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ)/", 'e', $str);
	    $str = preg_replace("/(í|ì­|ỉ|ĩ|ị)/", 'i', $str);
	    $str = str_replace("/(ì­)/", 'i', $str);
	    $str = preg_replace("/(ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ)/", 'o', $str);
	    $str = preg_replace("/(ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự)/", 'u', $str);
	    $str = preg_replace("/(ý|ỳ|ỷ|ỹ|ỵ)/", 'y', $str);
	    $str = preg_replace("/(đ)/",'d', $str);
	    $str = preg_replace("/(Á|À|Ả|Ã|Ạ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ)/", 'A', $str);
	    $str = preg_replace("/(É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ)/", 'E', $str);
	    $str = preg_replace("/(Í|Ì|Ỉ|Ĩ|Ị)/", 'I', $str);
	    $str = preg_replace("/(Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ)/", 'O', $str);
	    $str = preg_replace("/(Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự)/", 'U', $str);
	    $str = preg_replace("/(Ý|Ỳ|Ỷ|Ỹ|Ỵ)/", 'Y', $str);
	    $str = preg_replace("/(Đ)/", 'D', $str);
	    $str = preg_replace("/(,|-)/", ' ', $str);
	    $str = str_replace(" ", "-", str_replace("&*#39;","",$str));
		return $str;
	}
	
	public function createSlug($name){
	    $slug = strtolower(str_replace(' ','-',$this->move_dau($name)));
		return $slug;
	}
	public function ConvertData($data){
	    $data = trim($data);
	    $data = stripslashes($data);
	    $data = htmlspecialchars($data);
	    return $data;
	}
	
}