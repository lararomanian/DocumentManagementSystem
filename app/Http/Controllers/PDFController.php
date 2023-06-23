<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PDFController extends Controller
{

    public function convertAndProcessPdf(Request $request)
    {
        // Convert PDF to images
        $outputPath = storage_path('app/temp/');
        $pdf = new Pdf("D:\Downloads\Lab sheet  2.pdf");

        $pdf->setOutputFormat('png');
        $pdf->saveImage($outputPath);

        // Process each image with OCR
        $imageFiles = glob($outputPath . '*.png');
        $processedText = [];
        foreach ($imageFiles as $page => $imageFile) {
            // Perform OCR using Tesseract OCR
            $processedText[] = $this->processImageWithOCR($imageFile);

            // Delete the processed image
            unlink($imageFile);
        }

        // Delete the temporary PDF file
        // unlink(storage_path('app/' . $pdfPath));

        return response()->json($processedText);
    }

    private function processImageWithOCR($imagePath)
    {
        // Perform OCR using Tesseract OCR
        $text = (new TesseractOCR($imagePath))
            ->lang('eng')
            ->run();

        return [
            'page' => basename($imagePath, '.png'),
            'text' => $text,
        ];
    }
}
