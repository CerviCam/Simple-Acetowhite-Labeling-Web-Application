<?php

namespace App\Http\Controllers;

use App\ImageAreaMark;
use App\ImageUpload;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ArchiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private const LABEL_IVA_POSITIVE = 'positive';
    private const LABEL_IVA_NEGATIVE = 'negative';

    public function downloadZipPositiveIVA()
    {
        $zip = new ZipArchive;

        $fileName = 'download-iva-positive.zip';

        $files = ImageUpload::where('label', 1)->get();

        if (!$files->isEmpty()) {
            if ($zip->open(public_path($fileName), ZipArchive::CREATE || ZipArchive::OVERWRITE) === TRUE) {
                $data_json = [];
                foreach ($files as $key => $value) {
                    $item = public_path('files/images/iva/' . $value->filename_post_iva);
                    $relativeNameInZipFile = basename($item);
                    $zip->addFile($item, $relativeNameInZipFile);

                    // Populate file metadata for JSON.
                    $name = $value->filename_post_iva;
                    $bounding_boxes = [];
                    foreach (ImageAreaMark::where('filename', $value->filename_post_iva)->get() as $key => $value) {
                        array_push($bounding_boxes, [$value->rect_x0, $value->rect_y0, $value->rect_x1, $value->rect_y1]);
                    }
                    array_push($data_json, [
                        'name' => $name,
                        'label' => self::LABEL_IVA_POSITIVE,
                        'bounding_box' => $bounding_boxes,
                    ]);
                }

                // Create file metadata JSON object.
                file_put_contents(public_path('file_metadata.json'), json_encode($data_json));

                // Add file metadata to zip file.
                $zip->addFile(public_path('file_metadata.json'), basename(public_path('file_metadata.json')));

                $zip->close();
            }

            // Delete file metadata JSON file.
            File::delete(public_path('file_metadata.json'));

            return response()->download(public_path($fileName))->deleteFileAfterSend(true);
        } else {
            return view('error.filenotfound');
        }
    }

    public function downloadZipNegativeIVA()
    {
        $zip = new ZipArchive;

        $fileName = 'download-iva-negative.zip';

        $files = ImageUpload::where('label', 0)->get();

        if (!$files->isEmpty()) {
            if ($zip->open(public_path($fileName), ZipArchive::CREATE || ZipArchive::OVERWRITE) === TRUE) {
                $data_json = [];
                foreach ($files as $key => $value) {
                    $item = public_path('files/images/iva/' . $value->filename_post_iva);
                    $relativeNameInZipFile = basename($item);
                    $zip->addFile($item, $relativeNameInZipFile);

                    // Populate file metadata for JSON.
                    $name = $value->filename_post_iva;
                    $bounding_boxes = [];
                    foreach (ImageAreaMark::where('filename', $value->filename_post_iva)->get() as $key => $value) {
                        array_push($bounding_boxes, [$value->rect_x0, $value->rect_y0, $value->rect_x1, $value->rect_y1]);
                    }
                    array_push($data_json, [
                        'name' => $name,
                        'label' => self::LABEL_IVA_NEGATIVE,
                        'bounding_box' => $bounding_boxes,
                    ]);
                }

                // Create file metadata JSON object.
                file_put_contents(public_path('file_metadata.json'), json_encode($data_json));

                // Add file metadata to zip file.
                $zip->addFile(public_path('file_metadata.json'), basename(public_path('file_metadata.json')));

                $zip->close();
            }

            // Delete file metadata JSON file.
            File::delete(public_path('file_metadata.json'));

            return response()->download(public_path($fileName))->deleteFileAfterSend(true);
        } else {
            return view('error.filenotfound');
        }
    }
}
