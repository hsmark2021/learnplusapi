<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CourseController extends Controller
{
    public $user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->user = Auth::user();
    }


    private function getSectionsJSON(array $results){
        $courseid = 0;
        $sectionid = 0;
        $finalarray = array();
        $sectionobject = array();
        
        foreach ($results as $index => $result) {

            //Section
            $sectionobject =  array(
                "id" => $result->SectionId,
                "sectionName" => $result->SectionName,
                "courseId" => $result->CourseId,
                "lectures" => array()
            );

            //Lecture
            $lectureobject =  array(
                "id" => $result->LectureId,
                "title" => $result->LectureTitle,
                "content" => $result->LectureContent
            );
            
            $foundSectionIndex = -1;
            $foundLectureIndex = -1;
            
            foreach($finalarray as $sectionindex => $section){
                //echo "SectionId:".$result->SectionId."/".$section["id"];
                if($section["id"]==$result->SectionId){
                    $foundSectionIndex = $sectionindex;
                    // echo "foundSectionIndex:".$foundSectionIndex;
                    foreach($finalarray[$sectionindex]["lectures"] as $lectureindex => $lecture){
                        if($lecture["id"]==$result->LectureId){
                            $foundLectureIndex = $lectureindex;
                            // echo "foundLectureIndex:".$foundLectureIndex;
                        }
                    }
                }
            }

            //New Lecture add
            if($result->LectureId&&$foundLectureIndex==-1){
                if($foundSectionIndex==-1){
                    $sectionobject["lectures"][] = $lectureobject;
                }
                else {
                    $finalarray[$foundSectionIndex]["lectures"][] = $lectureobject; 
                }
            }

            if($foundSectionIndex==-1){
                $finalarray[] = $sectionobject; 
            }
        }
        return $finalarray;
    }

    private function getCoursesJSON(array $results){
        $courseid = 0;
        $sectionid = 0;
        $finalarray = array();
        $courseobject = array();
        $sectionobject = array();
        
        foreach ($results as $index => $result) {

            //Course
            $courseobject =  array(
                "id" => $result->CourseId,
                "title" => $result->CourseTitle,
                "shortDesc" => $result->CourseshortDesc,
                "longDesc" => $result->CourselongDesc,
                "courseImage" => $result->CourseCourseImage,
                "createdBy" => $result->CourseCreatedBy,
                "sections" => array(),
                "categories" => array()
            );

            //Section
            $sectionobject =  array(
                "id" => $result->SectionId,
                "sectionName" => $result->SectionName,
                "lectures" => array()
            );

            //Category
            $categoryobject =  array(
                "id" => $result->CategoryId,
                "categoryName" => $result->CategoryName
            );

            //Lecture
            $lectureobject =  array(
                "id" => $result->LectureId,
                "title" => $result->LectureTitle,
                "content" => $result->LectureContent
            );

            $foundCourse = false;
            $foundSection = false;
            $foundLecture = false;
            $foundCategory = false;
            
            $foundCourseIndex = -1;
            $foundSectionIndex = -1;
            $foundCategoryIndex = -1;
            $foundLectureIndex = -1;
            
            foreach($finalarray as $courseindex => $course){
                if($course["id"]==$result->CourseId){
                    $foundCourse = true;
                    $foundCourseIndex = $courseindex;
                    foreach($finalarray[$courseindex]["sections"] as $sectionindex => $section){
                        if($section["id"]==$result->SectionId){
                            $foundSection = true;
                            $foundSectionIndex = $sectionindex;
                            foreach($finalarray[$courseindex]["sections"][$sectionindex]["lectures"] as $lectureindex => $lecture){
                                if($lecture["id"]==$result->LectureId){
                                    $foundSection = true;
                                    $foundLectureIndex = $lectureindex;
                                }
                            }
                        }
                    }
                    foreach($finalarray[$courseindex]["categories"] as $categoryindex => $category){
                        if($category["id"]==$result->CategoryId){
                            $foundCategory = true;
                            $foundCategoryIndex = $categoryindex;
                        }
                    }
                }
            }

            //New Lecture add
            if($result->LectureId&&$foundLectureIndex==-1){
                if($result->SectionId&&$foundSectionIndex==-1){
                    $sectionobject["lectures"][] = $lectureobject;
                    //New Section Add
                    if($foundCourseIndex==-1){
                        $courseobject["sections"][] = $sectionobject;
                    }
                    else {
                        $finalarray[$foundCourseIndex]["sections"][] = $sectionobject; 
                    }
                }
                else {
                    $finalarray[$foundCourseIndex]["sections"][$foundSectionIndex]["lectures"][] = $lectureobject; 
                }
            }

            //New category add
            if($result->CategoryId&&$foundCategoryIndex==-1){
                if($foundCourseIndex==-1){
                    //new course add
                    $courseobject["categories"][] = $categoryobject;
                }
                else{
                    $finalarray[$foundCourseIndex]["categories"][] = $categoryobject; 
                }
            }

            if($foundCourseIndex==-1){
                $finalarray[] = $courseobject; 
            }
        }
        return $finalarray;
    }

    // Route Functions

    public function getCategories()
    {
        $results = DB::select("SELECT * FROM Category");
        return $results;
    }

    public function joinCourses($courseid)
    {
        $user = Auth::user();
        $affected = DB::insert('insert into UserCourse (userId,courseId) values (?, ?)', [$this->user->id, $courseid]);

        return $affected;
    }

    public function unjoinCourses($courseid)
    {
        $deleted = DB::delete('DELETE FROM UserCourse WHERE userId = ? AND courseId = ?',[$this->user->id,$courseid]);
        return $deleted;
    }

    public function getJoinedCourses()
    {
        $results = DB::select("SELECT DISTINCT Course.id,
            Course.title as title,
            Course.shortDesc as shortDesc,
            Course.longDesc as longDesc,
            Course.courseImage as courseImage,
            Course.createdBy as createdBy,
            UserCourse.userId as UserId
            FROM UserCourse
            left join Course on UserCourse.courseId = Course.id
            WHERE UserCourse.userId = ?",[$this->user->id]);
        return $results;
    }

    public function getCourses(Request $request)
    {
        $categoryidtext = $request->input('categoryids');
        $categoryids = explode(",",$categoryidtext);
        $keyword = $request->input('keyword');
        $supplierid = $request->input('supplierid');

        $sqlstatement = "SELECT Course.id as CourseId,
                Course.title as CourseTitle,
                Course.shortDesc as CourseshortDesc,
                Course.longDesc as CourselongDesc,
                Course.courseImage as CourseCourseImage,
                Course.createdBy as CourseCreatedBy,
                Category.id as CategoryId,
                Category.categoryName as CategoryName,
                Section.id as SectionId,
                Section.sectionName as SectionName,
                Lecture.id as LectureId,
                Lecture.title as LectureTitle,
                Lecture.content as LectureContent
                FROM Course
                left join CourseCategory on CourseCategory.courseId = Course.id
                left join Category on CourseCategory.categoryId = Category.id                
                left join Section on Section.courseId = Course.id
                left join Lecture on Lecture.sectionId = Section.id";

        if($categoryidtext||$keyword||$supplierid){
            $sqlstatement = $sqlstatement." WHERE ";
        }

        $sqlstatementarray = array();
        $tempsqlstatement = "";

        if($categoryidtext) {
            foreach($categoryids as $index => $categoryid){
                $tempsqlstatement = $tempsqlstatement." CourseCategory.categoryid = ".$categoryid;
                if(!($index === array_key_last($categoryids))){
                    $tempsqlstatement = $tempsqlstatement." OR ";
                }
            }
            $tempsqlstatement = " (".$tempsqlstatement.") ";
            $sqlstatementarray[] = $tempsqlstatement;
            $tempsqlstatement = "";
        }

        if($keyword){
            $tempsqlstatement = " Course.title LIKE '%".$keyword."%' ";
            $sqlstatementarray[] = $tempsqlstatement;
            $tempsqlstatement = "";
        }

        if($supplierid){
            $tempsqlstatement = " Course.createdBy = ".$supplierid." ";
            $sqlstatementarray[] = $tempsqlstatement;
            $tempsqlstatement = "";
        }

        $sqlstatement = $sqlstatement.implode(" AND ",$sqlstatementarray);

        $sqlstatement = $sqlstatement." order by Course.id asc, Section.id asc, Lecture.id asc";

        $results = DB::select($sqlstatement);

        return json_encode($this->getCoursesJSON($results));

    }


    //Supplier Functions

    public function getCreatedCourses()
    {
        $sqlstatement = "SELECT Course.id as CourseId,
                Course.title as CourseTitle,
                Course.shortDesc as CourseshortDesc,
                Course.longDesc as CourselongDesc,
                Course.courseImage as CourseCourseImage,
                Course.createdBy as CourseCreatedBy,
                Category.id as CategoryId,
                Category.categoryName as CategoryName,
                Section.id as SectionId,
                Section.sectionName as SectionName,
                Lecture.id as LectureId,
                Lecture.title as LectureTitle,
                Lecture.content as LectureContent
                FROM Course
                left join CourseCategory on CourseCategory.courseId = Course.id
                left join Category on CourseCategory.categoryId = Category.id                
                left join Section on Section.courseId = Course.id
                left join Lecture on Lecture.sectionId = Section.id
                WHERE Course.createdBy = ?";

        $results = DB::select($sqlstatement,[$this->user->id]);

        return json_encode($this->getCoursesJSON($results));
    }

    public function addCourse(Request $request)
    {
        $title = $request->input('title');
        $shortDesc = $request->input('shortDesc');
        $longDesc = $request->input('longDesc');
        $courseImage = $request->input('courseImage');
        $categoryIds = $request->input('categoryIds');
        $createdBy = $this->user->id;
        
        DB::beginTransaction();

        $affected = DB::insert('insert into Course (title,shortDesc,longDesc,courseImage,createdBy) values (?, ?, ?, ?, ?)', [$title, $shortDesc, $longDesc, $courseImage, $createdBy]);

        $courseId = DB::getPdo()->lastInsertId();

        DB::delete('delete from CourseCategory where courseId = ?',[$courseId]);

        $CourseCategoryStatement = "insert into CourseCategory (courseId,categoryId) values ";
        $categoryIdArray = explode(",",$categoryIds);
        $categoryIdTempArray = [];
        foreach($categoryIdArray as $categoryId){
            $categoryIdTempArray[] = "($courseId,$categoryId)";
        }
        $CourseCategoryStatement = $CourseCategoryStatement.implode(",",$categoryIdTempArray);
        // echo $CourseCategoryStatement;
        DB::insert($CourseCategoryStatement);

        DB::commit();

        return "";

    }

    public function editCourse($courseid,Request $request)
    {
        $title = $request->input('title');
        $shortDesc = $request->input('shortDesc');
        $longDesc = $request->input('longDesc');
        $courseImage = $request->input('courseImage');
        $categoryIds = $request->input('categoryIds'); // 1,4,7
        $createdBy = $this->user->id;
        
        DB::beginTransaction();

        $affected = DB::update("update Course set title=?,shortDesc=?,longDesc=?,courseImage=?  
            where id = ? and createdby = ?" , [$title, $shortDesc, $longDesc, $courseImage,
                $courseid, $createdBy]);

        DB::delete('delete from CourseCategory where courseId = ?',[$courseid]);

        $CourseCategoryStatement = "insert into CourseCategory (courseId,categoryId) values ";
        $categoryIdArray = explode(",",$categoryIds); //[1,4,7]
        $categoryIdTempArray = [];
        foreach($categoryIdArray as $categoryId){
            $categoryIdTempArray[] = "($courseid,$categoryId)"; // "(8,1)" => categoryIdTempArray
        }
        //categoryIdTempArray = ["(8,1)","(8,4)","(8,7)"]
        $CourseCategoryStatement = $CourseCategoryStatement.implode(",",$categoryIdTempArray); //"(8,1),(8,4),(8,7)"
        //echo $CourseCategoryStatement; 
        DB::insert($CourseCategoryStatement);

        DB::commit();

        return "";

    }

    public function deleteCourse($courseid)
    {
        $deleted = DB::delete('DELETE FROM Course WHERE id = ? and createdby = ?',
            [$courseid, $this->user->id]);
        return $deleted;
    }

    public function getCourse($id)
    {
        try {
            $sqlstatement = "SELECT Course.id as CourseId,
                Course.title as CourseTitle,
                Course.shortDesc as CourseshortDesc,
                Course.longDesc as CourselongDesc,
                Course.courseImage as CourseCourseImage,
                Course.createdBy as CourseCreatedBy,
                Category.id as CategoryId,
                Category.categoryName as CategoryName,
                Section.id as SectionId,
                Section.sectionName as SectionName,
                Lecture.id as LectureId,
                Lecture.title as LectureTitle,
                Lecture.content as LectureContent
                FROM Course
                left join CourseCategory on CourseCategory.courseId = Course.id
                left join Category on CourseCategory.categoryId = Category.id                
                left join Section on Section.courseId = Course.id
                left join Lecture on Lecture.sectionId = Section.id WHERE Course.id = ?";

            $results = DB::select($sqlstatement,[$id]);

            return json_encode($this->getCoursesJSON($results)[0]);

        }
        catch(\Exception $e) {
            return '{}'; 
            //'{error:100}';
        }

    }

    public function getCourseSections($courseid)
    {
        $sqlstatement = "SELECT 
            Section.id as SectionId,
            Section.sectionName as SectionName,
            Section.courseId as CourseId,
            Lecture.id as LectureId,
            Lecture.title as LectureTitle,
            Lecture.content as LectureContent
            FROM Section 
            left join Lecture on Section.id = Lecture.sectionId 
            where Section.courseId = ?";

        $results = DB::select($sqlstatement,[$courseid]);

        return json_encode($this->getSectionsJSON($results));
    }

    public function addSection($courseid, Request $request){
        $sectionName = $request->input('sectionName');
        $courseId = $request->input('courseId');

        return DB::insert("Insert into Section (sectionName, courseId) values (?,?)",
            [$sectionName,$courseId]);
    }
    
    public function updateSection($courseid, $sectionid,Request $request){
        $sectionName = $request->input('sectionName');
        // $courseId = $request->input('courseId');

        return DB::update("update Section set sectionName=?, courseId=? where id = ?",[$sectionName,$courseid, $sectionid]);
        
    }

    public function sendmail(Request $request){
        // $toemail = $request->input('toemail');
        Mail::raw('A new user just registered 2', function ($message) {
             $message->from('kenchan@anydomain.com', 'KC');
             $message->to('hsmark2021@gmail.com')->subject('test title2 ');
        });

        // Mail::send('emails.welcome', $data, function($message)
        // {
        //     // $message->from('us@example.com', 'Laravel');

        //     $message->to('toemail');

        //     $message->attach($pathToFile);
        // });
    }

    public function testSecurity(Request $request){
       return redirect('http://hk.yahoo.com');
    } 

}
