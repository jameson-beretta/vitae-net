<?php

namespace Tests\Feature\View\Patient;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientsPageTest extends TestCase
{
    use RefreshDatabase;

    public function testHasPatient()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $patient = factory(\App\Patient::class)->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee('<h3>'
            . $this->faker_escape($patient->first_name . ' ' . $patient->last_name)
            . ' (MRN: ' . $patient->medical_record_number . ')</h3>');
        $response->assertSee('<a class="btn btn-default" href="' . route('patients.show', ['id' => $patient->medical_record_number]) . '">Details</a>');
        $response->assertSee('<a class="btn btn-primary" href="' . route('patients.edit', ['id' => $patient->medical_record_number]) . '">Edit</a>');
        $response->assertSee('<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#patient-delete-modal" data-id="' . $patient->medical_record_number . '">Delete</button>');
        $response->assertSee('<h5><b><u>Name:</u></b></h5>');
        $response->assertSee($this->faker_escape($patient->first_name . ' ' . $patient->last_name));
        $response->assertSee('<h5><b><u>Date Of Birth:</u></b></h5>');
        $response->assertSee($patient->date_of_birth);
        $response->assertSee('<h5><b><u>Sex:</u></b></h5>');
        $response->assertSee(($patient->sex ? 'Male' : 'Female'));
        $response->assertSee('<h5><b><u>Height:</u></b></h5>');
        $response->assertSee($patient->height);
        $response->assertSee('<h5><b><u>Weight:</u></b></h5>');
        $response->assertSee($patient->weight);
        $response->assertSee('<h5><b><u>Diagnosis:</u></b></h5>');
        $response->assertSee($patient->diagnosis);
        $response->assertSee('<h5><b><u>Allergies:</u></b></h5>');
        $response->assertSee($patient->allergies);
        $response->assertSee('<h5><b><u>Code Status:</u></b></h5>');
        $response->assertSee($patient->code_status);
        $response->assertSee('<h5><b><u>Physician:</u></b></h5>');
        $response->assertSee($this->faker_escape($patient->physician));
        $response->assertSee('<h5><b><u>Room:</u></b></h5>');
        $response->assertSee($patient->room);
    }

    public function testHasModal()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee('<button type="button" class="close" data-dismiss="modal" ><span aria-hidden="true">&times;</span></button>');
        $response->assertSee('<h4 class="modal-title">Delete Patient</h4>');
        $response->assertSee('<p>Are you sure you want to delete this patient?</p>');
        $response->assertSee('<button type="button" class="btn btn-default col-md-offset-8 col-md-2" data-dismiss="modal">No</button>');
        $response->assertSee('<form name="delete-patient" action="" method="post" id="delete-patient">');
        $response->assertSee('<button type="submit" class="btn btn-danger col-md-2">Yes</button>');
    }

    public function testHasBarcode()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $patient = factory(\App\Patient::class)->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee('<h5><b><u>Bar Code</u></b></h5>');
        $response->assertSee($patient->generateBarcode());
    }

    public function testHasDownloadButton()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $patient = factory(\App\Patient::class)->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee($patient->generateDownloadButton());
    }

    public function testHasAddButtonIfEmpty()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee('<h3 class="text-center">No patients in the database.</h3>');
        $response->assertSee('<a href="' . route('patients.create') . '" class="col-md-offset-5 col-md-2 btn btn-default h3">Add Patients</a>');
    }

    public function testHasNoAddButtonIfEmptyAsStudent()
    {
        $user = factory(\App\User::class)->states('student')->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee('<h3 class="text-center">No patients in the database.</h3>');
        $response->assertDontSee('<a href="' . route('patients.create') . '" class="col-md-offset-5 col-md-2 btn btn-default h3">Add Patients</a>');
    }

    public function testHasHeader()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $patient = factory(\App\Patient::class)->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee('<a class="btn btn-success" href="' . route('patients.create') . '">Add Patient</a>');
        $response->assertSee('<h2>Patients</h2>');
    }

    public function testNoAddIfStudent()
    {
        $user = factory(\App\User::class)->states('student')->create();
        $patient = factory(\App\Patient::class)->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertDontSee('<a class="btn btn-success" href="' . route('patients.create') . '">Add Patient</a>');
        $response->assertSee('<h2>Patients</h2>');
    }

    public function testNoEditDeleteIfStudent()
    {
        $user = factory(\App\User::class)->states('student')->create();
        $patient = factory(\App\Patient::class)->create();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertDontSee(
            '<a class="btn btn-primary" href="'
            . route('patients.edit', ['id' => $patient->medical_record_number])
            . '">Edit</a>');
        $response->assertDontSee(
            '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#patient-delete-modal" data-id="'
            . $patient->medical_record_number
            . '">Delete</button>');
    }
}
