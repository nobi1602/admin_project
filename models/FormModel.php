<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class FormModel extends Model
{	
    public $name;
	public $description;
	public $content;
	public $slug;
	public $seo_title;
	public $seo_description;
	public $avatar;
	public $avatar_2;
	public $a;
	public $status;
	public $product_id;
	public $cate_id;
	public $parentID;
	
	//General information
	public $phone;
	public $email;
	public $addres;
	public $hotline;
	public $facebook;
	public $trip;
	public $img_about;
	public $img_product;
	public $img_post;
	public $img_contact;
	public $img_line_contact;
	public $sort_title;
	public $map;
	public $link_map;

	//silde
	public $use;
    public $type;
    
    //product
    public $hot_prod;
    public $catalog;
    public $ob_image;

    public function upload_avatar_form($id,$path){
		if($this->avatar !== null){
			if ($this->validate()) {			
				$link_khong_dau = FormModel::move_dau($this->avatar->baseName);				
				// $path ='upload/cataloge/avatar/'.$id;
				FileHelper::createDirectory($path);
				$this->avatar->saveAs($path.'/'. $link_khong_dau. '.' . $this->avatar->extension);
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function upload_avatar_2_form($id,$path){
	    if($this->avatar_2 !== null){
	        if ($this->validate()) {
	            $link_khong_dau = FormModel::move_dau($this->avatar_2->baseName);
	            // $path ='upload/cataloge/avatar/'.$id;
	            FileHelper::createDirectory($path);
	            $this->avatar_2->saveAs($path.'/'. $link_khong_dau. '.' . $this->avatar_2->extension);
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
	
	public function upload_img_about_form($id,$path){
	    if($this->img_about !== null){
	        if ($this->validate()) {
	            $link_khong_dau = FormModel::move_dau($this->img_about->baseName);
	            // $path ='upload/cataloge/avatar/'.$id;
	            FileHelper::createDirectory($path);
	            $this->img_about->saveAs($path.'/'. $link_khong_dau. '.' . $this->img_about->extension);
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
	public function upload_img_contact_form($id,$path){
	    if($this->img_contact !== null){
	        if ($this->validate()) {
	            $link_khong_dau = FormModel::move_dau($this->img_contact->baseName);
	            // $path ='upload/cataloge/avatar/'.$id;
	            FileHelper::createDirectory($path);
	            $this->img_contact->saveAs($path.'/'. $link_khong_dau. '.' . $this->img_contact->extension);
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
	public function upload_img_post_form($id,$path){
	    if($this->img_post !== null){
	        if ($this->validate()) {
	            $link_khong_dau = FormModel::move_dau($this->img_post->baseName);
	            // $path ='upload/cataloge/avatar/'.$id;
	            FileHelper::createDirectory($path);
	            $this->img_post->saveAs($path.'/'. $link_khong_dau. '.' . $this->img_post->extension);
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
	public function upload_img_line_contact_form($id,$path){
	    if($this->img_line_contact !== null){
	        if ($this->validate()) {
	            $link_khong_dau = FormModel::move_dau($this->img_line_contact->baseName);
	            // $path ='upload/cataloge/avatar/'.$id;
	            FileHelper::createDirectory($path);
	            $this->img_line_contact->saveAs($path.'/'. $link_khong_dau. '.' . $this->img_line_contact->extension);
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
	public function upload_img_product_form($id,$path){
	    if($this->img_product !== null){
	        if ($this->validate()) {
	            $link_khong_dau = FormModel::move_dau($this->img_product->baseName);
	            // $path ='upload/cataloge/avatar/'.$id;
	            FileHelper::createDirectory($path);
	            $this->img_product->saveAs($path.'/'. $link_khong_dau. '.' . $this->img_product->extension);
	            return true;
	        } else {
	            return false;
	        }
	    }
	}

	public function upload_form($id,$path){
	    if($this->catalog != null){
			if ($this->validate()) {	
			    foreach ($this->catalog as $file) {
					$link_khong_dau = FormModel::move_dau($file->baseName);
					// $path ='upload/cataloge/'.$id;
					FileHelper::createDirectory($path);
					$file->saveAs($path.'/'. $link_khong_dau. '.' . $file->extension);
					$this->a[] = $file;
				}		
				return true;
			} else {
				return false;
			}
		}
	}
		
	public function move_dau($str){
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        $str = str_replace(" ", "-", str_replace("&*#39;","",$str));
        return $str;
	}
}