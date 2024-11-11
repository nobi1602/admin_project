<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $l_name
 * @property string $birthday
 * @property integer $gender
 * @property string $address
 * @property string $avatar
 * @property integer $status
 */
class UserModel extends ActiveRecord  implements IdentityInterface
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%account_admin}}';
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'full_name'=> "Họ và tên",				
			'gender'=> "Giới tính",
			'birthday' => "Ngày tháng năm sinh",
			'email'=>'Email',		
		    'phone_number'=> "Số điện thoại",
			'password' => "Mật khẩu",
			'user_name' => "Tên đăng nhập",
			'id_permission' => "Phân quyền",
			'status' => "Trạng thái tài khoản",
		];
	}
	
	public function rules()
	{
		return [
			[['full_name','username','password','id_permission','address'], 'required'],
			[['email'], 'email'],
			[['username','email'], 'unique'],
			[['username','password','name'], 'string', 'max' => 250],			
			[['avatar'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],					
		];
	}
	
	public function scenarios()
	{
		return [
            'create' => ['full_name','status','id_permission','username','password','gender','email','phone_number'],
            'update' => ['full_name','status','id_permission','username','password','gender','email','phone_number'],
            'change_password' => ['password'],
            'login' => ['username','password'],
		];
	}
	
	
	public static function findIdentity($id) {
		return static::find()->where(['id'=>$id])->select(['username','id','id_permission','authKey','avatar','full_name'])->one();
	}
	
	public static function findIdentityByAccessToken($token, $userType = null) {
		$user = self::find()
		->where(["accessToken" => $token])
		->one();
		if (!count($user)) {
			return null;
		}
		return new static($user);
	}
	
	public static function findByUsername($username) {
		return static::find()->where(['status'=>'1', 'username' => $username])->one();
	}

	public static function findByPassword($password) {
		return static::find()->where(['status'=>'1', 'password' => $password])->one();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getAuthKey() {
		return $this->authKey;
	}
	
	public function validateAuthKey($authKey) {
		return $this->authKey === $authKey;
	}
	
	public function validatePassword($password) {
		return $this->password ===  md5($password);
	}

// 	public function getPer_use()
// 	{
// 	    return $this->hasOne(PermissionModel::className(), ['id' => 'id_permission']);
// 	}
	
	public function upload($id)
	{
	    if($this->avatar !== null){
	        if ($this->validate()) {
	            $path = 'upload/user/'.$id;
	            FileHelper::createDirectory($path);
	            $link_khong_dau = $this->move_dau($this->avatar->baseName);
	            $this->avatar->saveAs($path .'/'. $link_khong_dau . '.' . $this->avatar->extension);
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
	
	public function move_dau($str)
	{
	    $str = preg_replace("/(á|à|ả|ã|ạ|ă|ắ|ằ|ẳ­|ẵ|ặ|â|ấ|ầ|ẫ|ậ)/", 'a', $str);
	    $str = preg_replace("/(é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ)/", 'e', $str);
	    $str = preg_replace("/(í|ì­|ỉ|ĩ|ị)/", 'i', $str);
	    $str = preg_replace("/(ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ)/", 'o', $str);
	    $str = preg_replace("/(ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự)/", 'u', $str);
	    $str = preg_replace("/(ý|ỳ|ỷ|ỹ|ỵ)/", 'y', $str);
	    $str = preg_replace("/(đ)/", 'd', $str);
	    $str = preg_replace("/(Á|À|Ả|Ã|Ạ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ)/", 'A', $str);
	    $str = preg_replace("/(É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ)/", 'E', $str);
	    $str = preg_replace("/(Í|Ì|Ỉ|Ĩ|Ị)/", 'I', $str);
	    $str = preg_replace("/(Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ)/", 'O', $str);
	    $str = preg_replace("/(Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự)/", 'U', $str);
	    $str = preg_replace("/(Ý|Ỳ|Ỷ|Ỹ|Ỵ)/", 'Y', $str);
	    $str = preg_replace("/(Đ)/", 'D', $str);
	    $str = str_replace(" ", "-", str_replace("&*#39;","",$str));
	    return $str;
	}
}
