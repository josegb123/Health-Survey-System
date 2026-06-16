<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insurer;

class PublicInsurerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $insurers = Insurer::select('id', 'name', 'type')
            ->where('is_active', 1)
            ->get();

        return response()->json([
            'success' => true,
            'message' => __('Lista de EPS en el sistema.'),
            'data' => $insurers,
        ], 200);
    }
}
