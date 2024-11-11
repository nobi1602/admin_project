<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\IdentityInterface;

class PermissionForm extends Model
{	
	public $name;
	public $category;
	public $post;
	public $product;
	public $permission;
	public $slide;
	
	public function attributeLabels()
	{
		return [
			'name'=> 'Nhóm quyền',
			'category'=> 'Danh mục',
			'post'=> 'Bài viết',
		    'product' => 'Sản phẩm',
			'permission'=> 'Phân quyền',
			'slide'=> 'Slide',
		];
	}
	public function rules()
	{
		return [
				[['product','name','category','post','slide','permission'], 'trim'],
		];
	}
	
}