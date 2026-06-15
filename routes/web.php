<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\User\Index;
use Illuminate\Support\Facades\Route;
use App\Services\SurveyProcessorService;
use Illuminate\Http\Request;
use App\Livewire\Admin\SurveyTemplateIndex;

Route::view('/', 'livewire.auth.login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('admin/dashboard', Dashboard::class)->name('dashboard');
    Route::get('users/index', Index::class)->name('users.index');

    // Rutas de Administración del Backend
    Route::prefix('admin')->name('admin.')->group(function () {

        // Listado de Plantillas de Encuestas
        Route::get('/survey-templates', SurveyTemplateIndex::class)->name('survey-templates.index');

        // Aquí irán las siguientes vistas del módulo...
        // Route::get('/survey-templates/create', SurveyTemplateCreate::class)->name('survey-templates.create');
    });
});

require __DIR__ . '/settings.php';


// Procesamiento del formulario enviando los datos al Servicio
Route::post('/test-survey', function (Request $request, SurveyProcessorService $service) {
    try {
        // Estructuramos la data tal como la espera el servicio
        $patientData = [
            'document_type' => $request->input('document_type'),
            'dni' => $request->input('dni'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'nationality' => $request->input('nationality'),
            'phone' => $request->input('phone'),
        ];

        $templateId = (int) $request->input('template_id');
        $answers = $request->input('answers', []); // Array indexado por question_id

        // Ejecutamos el servicio transaccional
        $survey = $service->process($patientData, $templateId, $answers, 'signatures/test_manual_sig.png');

        return response()->json([
            'status' => '✅ Éxito absoluto',
            'message' => 'Encuesta y paciente procesados correctamente de forma transaccional.',
            'survey_id' => $survey->id,
            'patient' => [
                'id' => $survey->patient->id,
                'name' => $survey->patient->name,
                'dni' => $survey->patient->dni
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => '❌ Error en la transacción',
            'message' => $e->getMessage()
        ], 500);
    }
})->name('survey.test.submit');


// fallback route for smart redirection
Route::fallback(function () {
    if (Auth::check()) {
        return redirect()->route('dashboard'); // Cambia 'dashboard' por el nombre de tu ruta del panel
    }
    return redirect()->route('login');
});
