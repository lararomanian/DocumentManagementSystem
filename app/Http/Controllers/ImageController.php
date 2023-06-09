<?php

namespace App\Http\Controllers;

use Alimranahmed\LaraOCR\Facades\OCR;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Spatie\PdfToImage\Pdf;
class ImageController extends Controller
{
    public function importImage(Request $request)
    {
        // Validate the request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Store the uploaded image temporarily
        $imagePath = $request->file('image')->store('temp');

        // Process the image using OCR
        $text = (new TesseractOCR(storage_path('app/' . $imagePath)))->pdf()
            ->run();

        //download $text
        return response()->download($text);
    }

    public function processPDF(Request $request)
    {
        // Validate the uploaded file as a PDF
        $request->validate([
            'pdf' => 'required|mimes:pdf',
        ]);

        // Store the uploaded PDF file
        $pdfFile = $request->file('pdf');
        $pdfPath = $pdfFile->store('pdfs');

        // Convert the PDF to images
        $pdf = new Pdf(storage_path('app/' . $pdfPath));
        $pdf->saveAllPagesAsImages(storage_path('app/pdf_images'));

        // Process each image with Tesseract OCR
        $textResults = [];
        $imageFiles = glob(storage_path('app/pdf_images/*.jpg'));

        foreach ($imageFiles as $imageFile) {
            $text = (new TesseractOCR($imageFile))->run();
            $textResults[] = $text;
        }

        // Return the extracted text as JSON response
        return response()->json(['results' => $textResults]);
    }
}
