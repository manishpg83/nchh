<?php 

namespace App\Repositories;

use App\Upload;
use Illuminate\Support\Str;
use Image;

class UploadRepository 
{
    protected $uploadModel;

    public function __construct(Upload $uploadModel)
    {
        $this->uploadModel = $uploadModel;
    }

    public function uploadDocument($params, $user, $docId = null)  {
        
        if(isset($params['document'])){
            $filename = $this->getFileName($params['title'], $params['document']);
            $file = $params['document'];
            $uploadPath = storage_path($params['upload_path'] . '/' . $filename);
            
            Image::make($file)->save($uploadPath);
        }

        if($docId != null) {
            $upload = $this->uploadModel::find($docId);
        } else {
            $upload = new $this->uploadModel();
        }
        $upload->title = $params['title'];
        if(isset($params['document'])) {
            $upload->type = $params['type'];
            $upload->user_id = $user->id;
            $upload->doc_orig_name = $file->getClientOriginalName();
            $upload->doc_file_name = $filename;
            $upload->mime_type = $file->getClientMimeType();
        }
        $upload->save();

        return $upload;
    }

    public function getFileName($name, $file)  {
        
        return Str::slug($name) . '-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
    }
}