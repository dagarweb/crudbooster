<?php namespace crocodicstudio\crudbooster\controllers;

use crocodicstudio\crudbooster\controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\PDF;
use Illuminate\Support\Facades\Excel;

class AdminFileManagerController extends CBController {

	public function cbInit() {					
	}
	

	public function getIndex() {

		$path = g('path')?base64_decode(g('path')):'';

		if(strpos($path, '..')!==FALSE || $path=='.' || strpos($path, '/.') !== FALSE) {
			return redirect()->route('AdminFileManagerControllerGetIndex');
		}					

		$currentPath = $path?$path:'uploads';
		$currentPath = trim($currentPath,'/');				

		$directories = Storage::directories($currentPath);
		$files = Storage::files($currentPath);		

		return view('crudbooster::filemanager.index',['files'=>$files,'directories'=>$directories,'currentPath'=>$currentPath]);
	}


	public function postCreateDirectory() {
		$path = base64_decode(g('path'));
		$path = ($path)?:'uploads';
		$name = g('name');
		$name = str_slug($name,'_');
		Storage::makeDirectory($path.'/'.$name);
		return redirect()->back()
		->with(['message'=>'Directory has been created','message_type'=>'success']);
	} 

	public function postUpload() {		
		$allowedExtension = explode(',',strtolower(config('crudbooster.UPLOAD_TYPES')));
		$path = g('path')?base64_decode(g('path')):'uploads';
		if(Request::hasFile('userfile')) {
			$file = Request::file('userfile');
			$filename = $file->getClientOriginalName();
			$ext = $file->getClientOriginalExtension();	

			$isAllowed = false;
			foreach($allowedExtension as $e) {
				if($ext == $e) {
					$isAllowed = true;
					break;
				}
			}

			if($isAllowed==true) { 								
				Storage::putFileAs($path,$file,$filename);
				return redirect()->back()->with(['message_type'=>'success','message'=>'The file '.$filename.' has been uploaded!']);
			}else{
				return redirect()->back()->with(['message_type'=>'warning','message'=>'The file '.$filename.' type is not allowed!']);
			}
		}
	}

	public function getDeleteDirectory($dir) {
		$dir = base64_decode($dir);
		Storage::deleteDirectory($dir);
		return redirect()->back()->with(['message_type'=>'success','message'=>'The directory has been deleted!']);
	}

	public function getDeleteFile($file) {
		$file = base64_decode($file);
		Storage::delete($file);

		return redirect()->back()->with(['message_type'=>'success','message'=>'The file has been deleted!']);
	}

}
