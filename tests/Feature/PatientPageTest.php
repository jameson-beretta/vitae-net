<?php

namespace Tests\Feature;

use App\Medication;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientPageTest extends TestCase
{
    use RefreshDatabase;

    public function testHasPatient()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $patient = factory(\App\Patient::class)->create();
        $response = $this->actingAs($user)->get('/patients/' . $patient->medical_record_number);
        $response->assertSee('<h3>'
            . $this->faker_escape($patient->first_name . ' ' . $patient->last_name)
            . '</h3>');
        $response->assertSee('<h5><b><u>First Name:</u></b></h5>');
        $response->assertSee('<p>' . $this->faker_escape($patient->first_name) . '</p>');
        $response->assertSee('<h5><b><u>Last Name:</u></b></h5>');
        $response->assertSee('<p>' . $this->faker_escape($patient->last_name) . '</p>');
        $response->assertSee('<h5><b><u>Date Of Birth:</u></b></h5>');
        $response->assertSee('<p>' . $patient->date_of_birth . '</p>');
        $response->assertSee('<h5><b><u>Sex:</u></b></h5>');
        $response->assertSee('<p>' . ($patient->sex ? 'Male' : 'Female') . '</p>');
        $response->assertSee('<h5><b><u>Height:</u></b></h5>');
        $response->assertSee('<p>' . $patient->height . '</p>');
        $response->assertSee('<h5><b><u>Weight:</u></b></h5>');
        $response->assertSee('<p>' . $patient->weight . '</p>');
        $response->assertSee('<h5><b><u>Diagnosis:</u></b></h5>');
        $response->assertSee('<p>' . $patient->diagnosis . '</p>');
        $response->assertSee('<h5><b><u>Allergies:</u></b></h5>');
        $response->assertSee('<p>' . $patient->allergies . '</p>');
        $response->assertSee('<h5><b><u>Code Status:</u></b></h5>');
        $response->assertSee('<p>' . $patient->code_status . '</p>');
        $response->assertSee('<h5><b><u>Physician:</u></b></h5>');
        $response->assertSee('<p>' . $this->faker_escape($patient->physician) . '</p>');
        $response->assertSee('<h5><b><u>Room:</u></b></h5>');
        $response->assertSee('<p>' . $patient->room . '</p>');
        $response->assertSee('<a href="#labs" class="collapsed" role="button" data-toggle="collapse">Lab Results</a>');
        $response->assertSee('<div id="labs" class="panel-collapse collapse in" role="tabpanel"');
        $response->assertSee('<a href="#orders" class="collapsed" role="button" data-toggle="collapse">Provider\'s Orders</a>');
        $response->assertSee('<div id="orders" class="panel-collapse collapse in" role="tabpanel"');
        $response->assertSee('<div id="mar" class="col-md-12 panel panel-default">');
        $response->assertSee('<table class="table">');
        $response->assertSee('<td colspan="15" class="stat-header"><b> STAT/PRN </b></td>');
    }

    public function testHasLabs()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $lab = factory(\App\Lab::class)->create();
        $response = $this->actingAs($user)->get('/patients/' . $lab->patient_id);
        $response->assertSee('<a class="list-group-item" href="'
            . route('labs.show', ['id' => $lab->id]) . '">'
            . $lab->name . '</a>');
    }

    public function testHasIncompleteOrder()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $order = factory(\App\Order::class)->states('incomplete')->create();
        $response = $this->actingAs($user)->get('/patients/' . $order->patient_id);
        $response->assertSee('<a class="list-group-item list-group-item-danger" href="'
            . route('orders.show', ['id' => $order->id]) . '">');
        $response->assertSee($order->name);
        $response->assertSee('</a>');
    }

    public function testHasCompleteOrder()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $order = factory(\App\Order::class)->states('complete')->create();
        $response = $this->actingAs($user)->get('/patients/' . $order->patient_id);
        $response->assertSee('<a class="list-group-item list-group-item-success" href="'
            . route('orders.show', ['id' => $order->id]) . '">');
        $response->assertSee($order->name);
        $response->assertSee('</a>');
    }

    public function testHasBarcode()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $patient = factory(\App\Patient::class)->create();
        $patcode = "p " . $patient->medical_record_number;
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertSee('<h5><b><u>Bar Code</u></b></h5>');
        $response->assertSee('<img src="data:image/png;base64,'. base64_encode($generator->getBarcode($patcode, $generator::TYPE_CODE_128, 3, 50)) .'" />');
    }

    public function testHasMarEntry()
    {
        $user = factory(\App\User::class)->states('admin')->create();
        $marEntry = factory(\App\MarEntry::class)->create();
        $medName = Medication::find($marEntry->medication_id)->toString();
        $response = $this->actingAs($user)->get('/patients/' . $marEntry->medical_record_number);
        $response->assertSee('<td> ' . $medName . ' </td>');
        $response->assertSee('<td> ' . $marEntry->instructions . ' </td>');
    }
}
