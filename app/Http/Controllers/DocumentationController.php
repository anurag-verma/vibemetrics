<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentationController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Documentation/Index', [
            'appUrl' => rtrim(config('app.url'), '/'),
        ]);
    }
}
