<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\CoursesByTeacherModel;
use App\CoursesByStudentModel;
use App\StudentsByCourseModel;
use App\CoursesModel;
use App\TeachersModel;
use App\StudentsModel;
use Illuminate\Http\Request;

class CoursesController extends Controller {

    /**
     * FunciÃ³n para mostrar toda la informaciÃ³n de un curso, pasando como referencia el NRC_PERIODO_KEY
     * @param $NRC
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCoursesInfo($NRC) {
        $course = CoursesModel::where("NRC_PERIODO_KEY", "=", $NRC)->first();

        if ($course) {
            $object = array(
                "subject_name" => $course["NOMBREASIGNATURA"],
                "nrc" => $course["NRC_PERIODO_KEY"],
                "period" => $course["PERIODO"],
                "credits" => $course["CREDITOS"],
                "week_hours" => $course["HORAS_SEMANALES"],
                "subject" => $course["MATERIA"],
                "section" => $course["SECCION"],
                "course" => $course["CURSO"],
                "teacher_id" => $course["DOCENTEID"],
                "links" => array(
                    "students_uri" => "/course/" . $course["NRC_PERIODO_KEY"] . "/students",
                    "statistics_uri" => $course["NRC_PERIODO_KEY"] . "/attendance/",
                    "teacher_uri" => "/teacher/" . $course["DOCENTEID"]
                )
            );
        } else {
            $object = "No existe ningún curso con el NRC " . $NRC . ".";
        }

        return response()->json($object);
    }

    /**
     * FunciÃ³n para listar los cursos de un profesor, pasando como referencia el TEACHERID
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCoursesByTeacher($id) {
        $course = CoursesByTeacherModel::where("TEACHERID", "=", $id)->get();

        if (!$course->isEmpty()) {
            $object = array(
                "id" => $course[0]["TEACHERID"],
                "names" => $course[0]["NAMES"],
                "lastnames" => $course[0]["LASTNAMES"]
            );
            $object["resource_uri"] = "/teacher/" . $course[0]["TEACHERID"];

            foreach ($course as $value) {
                $var = array(
                    "subject_name" => $value["SUBJECTNAME"],
                    "nrc" => $value["NRC"],
                    "section" => $value["SECTION"],
                    "resource_uri" => "/course/" . $value["NRC"],
                );
                $object["courses"][] = $var;
            }
        } else {
            $teacher = TeachersModel::where("ID", "=", $id)->first();
            if ($teacher) {
                $object = "El usuario no tiene ningún curso.";
            } else {
                $object = "El usuario con el código" . $id . " no existe o no es un docente.";
            }
        }
        return response()->json($object);
    }

    /**
     * Funcion para listar los alumnos de un curso, pasando como referencia el NRC
     * @param $NRC
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStudentsByCourse($NRC) {
        $studentsbycourse = StudentsByCourseModel::where("IDNUMBER", "=", $NRC)->where('ROLE', '=', 'student')->get();
        if (!$studentsbycourse->isEmpty()) {
            $studentsbycourse_INIT = $studentsbycourse[0];

            $studentsbycourse_JSON = array(
                "subject" => $studentsbycourse_INIT->course["NOMBREASIGNATURA"],
                "nrc" => $studentsbycourse_INIT["IDNUMBER"],
                "teacher_id" => $studentsbycourse_INIT->course["DOCENTEID"],
            );

            $studentsbycourse_JSON["resource_uri"] = "/course/" . $studentsbycourse[0]["NRC"];

            foreach ($studentsbycourse as $value) {

                $var = array(
                    "id" => $value->student["ID"],
                    "names" => $value->student["NOMBRES"],
                    "lastnames" => $value->student["APELLIDOS"],
                    "program" => $value->student["PROGRAMA"],
                    "email" => $value->student["EMAIL"],
                    "resource_uri" => "/student/" . $value->student["ID"],
                );
                $studentsbycourse_JSON["students"][] = $var;
            }
        } else {
            $course = CoursesModel::where("NRC", "=", $NRC)->first();
            if ($course) {
                $studentsbycourse_JSON = "No hay estudiantes matriculados en el curso con el NRC " . $NRC;
            } else {
                $studentsbycourse_JSON = "El curso con NRC " . $NRC . " no existe";
            }
        }

        return response()->json($studentsbycourse_JSON);
    }

    /**
     * FunciÃ³n para mostrar los cursos de un estudiante, pasando como referencia el STUDENTID
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCoursesByStudent($id) {
        $coursesbystudent = CoursesByStudentModel::where("STUDENTID", "=", $id)->get();

        if (!$coursesbystudent->isEmpty()) {
            $coursesbystudent_INIT = $coursesbystudent[0];

            $coursesbystudent_JSON = array(
                "student_id" => $coursesbystudent_INIT["STUDENTID"],
                "courses" => array()
            );

            foreach ($coursesbystudent as $value) {
                $var = array(
                    "subject_name" => $value["SUBJECTNAME"],
                    "nrc" => $value["NRC"],
                    "section" => $value["SECTION"],
                    "names" => $value["NAMES"],
                    "lastnames" => $value["LASTNAMES"],
                    "teacher_id" => $value["TEACHERID"],
                );
                $coursesbystudent_JSON["courses"][] = $var;
            }
        } else {
            $student = StudentsModel::where("ID", "=", $id)->first();
            if ($student) {
                $coursesbystudent_JSON = "El estudiante con código " . $id . " no tiene cursos matriculados";
            } else {
                $coursesbystudent_JSON = "El estudiante con código " . $id . " no existe o no está matriculado como estudiante en ningún curso.";
            }
        }

        return response()->json($coursesbystudent_JSON);
    }

}
