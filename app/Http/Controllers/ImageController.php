<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Spatie\PdfToImage\Pdf;
use Dompdf\Dompdf;
class ImageController extends Controller
{

    protected $image_path = "";

    public function importImage(Request $request)
    {
        // Validate the request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('image')->store('temp');

        $text = (new TesseractOCR(storage_path('app/' . $imagePath)))
            ->lang('nep')
            ->run();

        return response()->json($text);
    }

    public function processPDF(Request $request)
    {
        // Validate the uploaded file as a PDF
        $request->validate([
            'pdf' => 'required|mimes:pdf',
            'lang' => 'required',
        ]);

        // Store the uploaded PDF file
        $pdfFile = $request->file('pdf');
        $pdfPath = $pdfFile->store('pdfs');

        // Convert the PDF to images
        $pdf = new Pdf(storage_path('app/' . $pdfPath));
        $pdf->setOutputFormat('png');
        $pdf->saveAllPages(storage_path('app/pdf_images'));

        // Process each image with Tesseract OCR
        $textResults = [];
        $imageFiles = glob(storage_path('app/pdf_images/*.png'));

        foreach ($imageFiles as $imageFile) {
            $text = (new TesseractOCR($imageFile))
                ->lang($request->lang)
                ->run();
            $textResults[] = $text;
        }

        return response()->json(['results' => $textResults]);
    }


    public function convertPdfToImage($file, $lang)
    {

        $pdf_folder_name = 'pdfs/' . bin2hex(random_bytes(7)) . '/';
        $pdfPath = $file->store($pdf_folder_name . $file->getClientOriginalName());

        // Converting the PDF to images
        $pdf = new Pdf(storage_path('app/' . $pdfPath));
        $pdf->setOutputFormat('png');

        // Creating a folder to store the images
        $pdf_images_folder = storage_path('app/' . $pdf_folder_name . 'pdf_images');
        if (!file_exists($pdf_images_folder)) {
            mkdir($pdf_images_folder, 0777, true);
        }

        // Looping through all pages and save each page as an image
        $numPages = $pdf->getNumberOfPages();
        for ($page = 1; $page <= $numPages; $page++) {
            $pdf->setPage($page)->saveImage($pdf_images_folder . "/page{$page}.png");
        }

        return $this->scanImages($pdf_images_folder, $lang);
    }

    private function scanImages($imagesFolder, $lang)
    {
        $combinedText = '';
        $imageFiles = glob($imagesFolder . '/*.png');

        foreach ($imageFiles as $imageFile) {
            $ocr = new TesseractOCR($imageFile);
            $ocr->setTempDir(storage_path('app/tmp')); // Setting a temporary directory for Tesseract to use
            $combinedText .= $ocr->lang($lang)->run(); // Performing OCR and append the extracted text to the result
        }

        return $combinedText;
    }

    // public function ocrEngine(Request $request)
    // {
        // $this->convertPdfToImage($request);
    // }

    // public function exportToPdf($text, ) {

    //     //when this method is called
    // }
}
