<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //

    public function getLectures(){
        return DB::select("select id as LectureId , 
            title as LectureTitle, 
            content as LectureContent, 
            sectionId as SectionId from Lecture");
    }

    public function getSingleLecture($id){
        $result = DB::select("select * from Lecture where id = ?",[$id]);
        // if(count($result)==0){
        //     return (object)[];
        // }
        // else{
        //     return $result[0];
        // }
        return json_encode($result[0]);
    }

    public function addLecture(Request $request){
        $title = $request->input('title');
        $content = $request->input('content');
        $sectionId = $request->input('sectionId');

        return DB::insert("Insert into Lecture (title, content, sectionId) values (?,?,?)",
            [$title,$content,$sectionId]);
    }
    
    public function updateLecture($id,Request $request){
        $title = $request->input('title');
        $content = $request->input('content');
        $sectionId = $request->input('sectionId');

        return DB::update("update Lecture set title=?, content=?, sectionId=? where id = ?",[$title,$content,$sectionId,$id]);
        
    }
    
    public function deleteLecture($id){
        return DB::delete("delete from Lecture where id = ?",[$id]);
    }

}
