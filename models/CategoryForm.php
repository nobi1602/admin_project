<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;

class CategoryForm extends FormModel
{	

	public function attributeLabels()
	{
		return [
			'name'=> Yii::t('app', 'Name'),
			'description'=> Yii::t('app', 'description'),
			'content'=> Yii::t('app', 'Content'),	
			'slug'=> Yii::t('app', 'Slug'),
			'seo_title'=> Yii::t('app', 'Seo title'),
			'seo_description' => Yii::t('app', 'Seo Description'),	
			'avatar' => Yii::t('app','avatar'),
		    'avatar_2' => Yii::t('app','avatar_2'),
			'status' => Yii::t('app', 'status'),
			'use' => Yii::t('app', 'Used'),
			'type' => Yii::t('app', 'r_type'),
			'product_id' => Yii::t('app', 'product'),
		    'cate_id' =>Yii::t('app', 'cate_id'),
			'parentID' => Yii::t('app', 'parent id'),
			'map' => Yii::t('app', 'map'),
		    'link_map' => Yii::t('app', 'link_map'),
		    
		    'img_about' => Yii::t('app', 'img about'),
		    'img_contact' => Yii::t('app', 'img contact'),

			'phone' => Yii::t('app', 'phone'),
			'email' => Yii::t('app', 'email'),
			'addres' => Yii::t('app', 'addres'),
			'hotline' => Yii::t('app', 'hotline'),
			'facebook' => Yii::t('app', 'facebook'),
			'trip' => Yii::t('app', 'trip'),
		    'sort_title' => Yii::t('app', 'sort_title'),
		];
	}
	public function rules()
	{
		return [
			[['name', 'description'], 'trim'],
			[['avatar','map','img_about','img_contact'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],	
		];
	}
	public function scenarios()
	{
		return [
				[['name','parentID'], 'required'],
				'create' => ['cate_id','name','parentID', 'description','content','seo_title','seo_description'],
				'create_slide' => ['name', 'description','status','use','cate_id','content'],
				'create_product' => ['name','specifications','accommodation','cabin_facility','benefit','activities', 'description','content','seo_title','seo_description','ob_image','pic_list','parentID','product_id'],			
				'info' => ['name','link_map','phone','email', 'description','addres','seo_title','seo_description','sort_title'],	
		];
	}

	public function upload_avatar($id,$folder)
	{
		$path = 'upload/'.$folder.'/'.'avatar/'.$id;
		return FormModel::upload_avatar_form($id,$path);
	}
	
	public function upload_avatar_2($id,$folder)
	{
	    $path = 'upload/'.$folder.'/'.'avatar_2/'.$id;
	    return FormModel::upload_avatar_2_form($id,$path);
	}
	
	public function upload_img_about($id,$folder)
	{
	    $path = 'upload/'.$folder.'/'.'avatar/'.$id;
	    return FormModel::upload_img_about_form($id,$path);
	}
	public function upload_img_contact($id,$folder)
	{
	    $path = 'upload/'.$folder.'/'.'avatar/'.$id;
	    return FormModel::upload_img_contact_form($id,$path);
	}
	public function upload_img_post($id,$folder)
	{
	    $path = 'upload/'.$folder.'/'.'avatar/'.$id;
	    return FormModel::upload_img_post_form($id,$path);
	}
	public function upload_img_product($id,$folder)
	{
	    $path = 'upload/'.$folder.'/'.'avatar/'.$id;
	    return FormModel::upload_img_product_form($id,$path);
	}
	public function upload_img_line_contact($id,$folder)
	{
	    $path = 'upload/'.$folder.'/'.'avatar/'.$id;
	    return FormModel::upload_img_line_contact_form($id,$path);
	}
	public function upload_map($id,$folder)
	{
		$path = 'upload/'.$folder.'/'.'map/'.$id;
		return FormModel::upload_map_form($id,$path);
	}

	public function upload_slide($id)
	{
		$path = 'upload/slide/'.$id;
		return FormModel::upload_avatar_form($id,$path);
	}
	
}