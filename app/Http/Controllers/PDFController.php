<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function showEditor() {
        return view('pdf-editor');  // Your HTML form for editing text
    }

    public function generatePDF(Request $request) {
        $pdf = new TCPDF();

        // Set document properties
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Generated PDF');

        // Add a page
        $pdf->AddPage();

        // Set font, size, and family from user input
        $fontFamily = $request->input('font_family', 'helvetica');
        $fontSize = $request->input('font_size', 12);
        $text = $request->input('text', 'Hello World!');

        $pdf->SetFont($fontFamily, '', $fontSize);
        $pdf->Write(0, $text);

        // Output the PDF (save to server or output to browser)
        $pdf->Output('generated_pdf.pdf', 'I'); // 'I' for inline, 'D' for download
    }
}
