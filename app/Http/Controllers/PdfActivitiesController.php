<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use ZipArchive;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PdfActivitiesController extends Controller
{
    public function exportPDF($documents)
    {
        $ocr_text = $documents->ocr_text;
        $document_name = $documents->name;

        $data = [
            'ocr_text' => $ocr_text,
            'document_name' => $document_name
        ];

        $pdf = Pdf::loadView('pdf_template', compact('data'));
        return $pdf->download('pdf_template.pdf');
    }

    public function exportFolder($folder)
    {
        $folderPath = $this->createFolderStructure($folder);
        $zipPath = $this->zipFolder($folderPath);

        // Serve the zip file for download

        // Manually delete the zip file after download
        // register_shutdown_function(function () use ($zipPath) {
        //     if (file_exists($zipPath)) {
        //         unlink($zipPath);
        //     }
        // });
        // dd($response);
        return response()->download($zipPath);
    }

    public function createFolderStructure($folder, $path = null)
    {
        $folderPath = $path ?? storage_path('app/public/folders/directories/') . $folder->name . '/';
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        if ($folder->documents && !empty($folder->documents)) {
            $this->copyDocuments($folder, $folderPath);
        }

        if ($folder->subfolders && !empty($folder->subfolders)) {
            foreach ($folder->subfolders as $subfolder) {
                $this->createFolderStructure($subfolder, $folderPath . $subfolder->name . '/');
            }
        }

        return $folderPath;
    }

    public function copyDocuments($folder, $path)
    {
        foreach ($folder->documents as $document) {
            $documentPath = storage_path('app/public/documents/') . $document->documents[0]->name;
            $newDocumentPath = $path . basename($document->documents[0]->name);

            if (!file_exists(dirname($newDocumentPath))) {
                mkdir(dirname($newDocumentPath), 0777, true);
            }

            File::copy($documentPath, $newDocumentPath);
        }
    }

    public function zipFolder($folderPath)
    {
        $zipFileName = $folderPath . '.zip';

        $zip = new ZipArchive;
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $this->addFolderToZip($folderPath, $zip);
            $zip->close();
        }

        return $zipFileName;
    }

    private function addFolderToZip($folderPath, $zip, $localPath = '')
    {
        $files = glob($folderPath . '/*');

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->addFolderToZip($file, $zip, $localPath . basename($file) . '/');
            } else {
                $zip->addFile($file, $localPath . basename($file));
            }
        }
    }
}
