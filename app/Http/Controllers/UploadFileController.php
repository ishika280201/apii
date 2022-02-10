<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Validator;

class UploadFileController extends Controller
{
    public function upload(Request $request){
        $validate = validator::make($request->all(),[
            'file' => 'required|mimes:doc,docx,pdf,txt,csv|max:2048', 
        ]);

        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()], 401);
        }

        if($file = $request->file('file')){
            $path = $file->store('New folder/');
            $name = $file->getClientOriginalName();

            $filename = new File();
            $filename->filename = $file;
            $filename->path = $path;
            $filename->save();

            return response()->json([
                'success' => true,
                'message' => 'file successfully uploaded',
                'file' => $file
            ]);
        }

        }
}

