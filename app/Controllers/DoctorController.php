<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Doctor;
use App\Models\Specialty;

class DoctorController extends Controller
{
    private Doctor $doctorModel;
    private Specialty $specialtyModel;

    public function __construct()
    {
        $this->doctorModel = new Doctor();
        $this->specialtyModel = new Specialty();
    }

    public function index(Request $request, Response $response): string
    {
        $doctors = $this->doctorModel->allDetailed();
        $specialties = $this->specialtyModel->all();

        return $this->view('doctors/index', [
            'pageTitle' => 'قائمة الأطباء والاستشاريين',
            'doctors' => $doctors,
            'specialties' => $specialties,
        ]);
    }
}
