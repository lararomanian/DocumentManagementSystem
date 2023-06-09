<?php

namespace App\Http\Controllers;

use Thiagoalessio\TesseractOCR\TesseractOCR;

class OCRController extends Controller
{
    public function performOCR()
    {
        $imagePath = public_path('images/example.png');
        $text = (new TesseractOCR($imagePath))->run();

        // Process the extracted text
        // ...
    }
}
