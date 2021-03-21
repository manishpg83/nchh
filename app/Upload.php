<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    public function getAgentDocumentSrcAttribute() {
        
        return  url('storage') . '/' . config('custom.uploads.agent_doc')  . $this->doc_file_name;
    }
}
