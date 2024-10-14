<?php

// app/Http/Controllers/FormController.php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade as PDF;

class FormController extends Controller
{
    public function showForm()
    {
        return view('form');
    }

    public function submitForm(Request $request)
    {
        // Validate the form data and the file
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
            'pdf_file' => 'required|mimes:pdf|max:10240', // PDF file max size of 10MB
        ]);

        // Store the uploaded PDF file
        if ($request->hasFile('pdf_file')) {
            $filePath = $request->file('pdf_file')->store('pdfs', 'public');
        }

        // Redirect to the PDF view route
        return redirect()->route('pdf.view', ['filename' => basename($filePath)]);
    }

    public function viewPdf($filename)
    {
        $filePath = storage_path("app/public/pdfs/{$filename}");

        if (!file_exists($filePath)) {
            abort(404, 'PDF file not found');
        }

        //return response()->file($filePath,['Content-Type' => 'application/pdf','Content-Disposition' => 'inline; filename="$filename .']);
        // Use a custom Blade view to display the PDF viewer
        return view('pdf_view', ['filename' => $filename]);

    }


}

