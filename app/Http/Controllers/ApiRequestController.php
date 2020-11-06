<?php

namespace App\Http\Controllers;

use App\ImageAreaMark;
use App\ImageLabel;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ApiRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private const LABEL_POSITIVE = 'positive';

    private const LABEL_POSITIVE_CODE = 1;

    private const LABEL_NEGATIVE = 'negative';

    private const LABEL_NEGATIVE_CODE = 0;

    public function downloadPositives()
    {
        // Prepare ZipArchive
        $zip = new ZipArchive;
        $fileName = 'download-positives.zip';

        // Get all positive images
        $images_positive = [];
        foreach (ImageLabel::where('label', self::LABEL_POSITIVE_CODE)->get() as $file) {
            array_push($images_positive, $file->filename);
        }

        if (count($images_positive) != 0) {
            // Initiate inconclusive images array.
            $inconclusiveImages = [];

            // Initiate non positive images array.
            $nonPositives = [];

            // Iterate through each of the images data.
            foreach ($images_positive as $key => $value) {
                // Get all image labels.
                $imageLabels = ImageLabel::where('filename', $value)->get();

                // Check if count of the image labels is more than one.
                if (count($imageLabels) > 1) {
                    // Initiate count positive and negative labels.
                    $countPositives = 0;
                    $countNegatives = 0;

                    // Iterate through each of the image labels given.
                    foreach ($imageLabels as $key2 => $value2) {
                        if ($value2->label == self::LABEL_POSITIVE_CODE) {
                            $countPositives++;
                        }

                        if ($value2->label == self::LABEL_NEGATIVE_CODE) {
                            $countNegatives++;
                        }
                    }

                    // If positive and negative image labels is fifty:fifty then assigned as inconclusive image.
                    if ($countPositives == $countNegatives) {
                        array_push($inconclusiveImages, $value);
                    }

                    // If count of negatives is larger than positives then assigned file as negative
                    // so it should be removed from the positive image dataset.
                    if ($countNegatives > $countPositives) {
                        array_push($nonPositives, $value);
                    }
                }
            }

            // Intersect array.
            $intersectFromInconclusiveImages = array_diff($images_positive, $inconclusiveImages);
            $intersectFromNonPositives = array_diff($intersectFromInconclusiveImages, $nonPositives);
            $files = $intersectFromNonPositives;

            if ($zip->open(public_path($fileName), ZipArchive::CREATE || ZipArchive::OVERWRITE) === TRUE) {
                // Prepare metadata JSON file
                $data_json = [];

                foreach (array_unique($files) as $key => $value) {
                    $item = public_path('files/images/iva/' . $value);
                    $relativeNameInZipFile = basename($item);
                    $zip->addFile($item, $relativeNameInZipFile);

                    // Populate file metadata for JSON.
                    $name = $value;
                    $email = ImageLabel::where('filename', $name)->first()->email;
                    $bounding_boxes = [];
                    foreach (ImageAreaMark::where('filename', $value)->get() as $key2 => $value2) {
                        if ($value2->label == 0) {
                            array_push($bounding_boxes, [$value2->rect_x0, $value2->rect_y0, $value2->rect_x1, $value2->rect_y1]);
                        }
                    }
                    array_push($data_json, [
                        'name' => $name,
                        'email' => $email,
                        'label' => self::LABEL_POSITIVE,
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
            return response()->json([
                'message' => 'File not found!'
            ]);
        }
    }

    public function downloadNegatives()
    {
        // Prepare ZipArchive
        $zip = new ZipArchive;
        $fileName = 'download-negatives.zip';

        // Get all negative images
        $images_negative = [];
        foreach (ImageLabel::where('label', self::LABEL_NEGATIVE_CODE)->get() as $file) {
            array_push($images_negative, $file->filename);
        }

        if (count($images_negative) != 0) {
            // Initiate inconclusive images array.
            $inconclusiveImages = [];

            // Initiate non negative images array.
            $nonNegatives = [];

            // Iterate through each of the images data.
            foreach ($images_negative as $key => $value) {
                // Get all image labels.
                $imageLabels = ImageLabel::where('filename', $value)->get();

                // Check if count of the image labels is more than one.
                if (count($imageLabels) > 1) {
                    // Initiate count positives and negatives labels.
                    $countPositives = 0;
                    $countNegatives = 0;

                    // Iterate through each of the image labels is given.
                    foreach ($imageLabels as $key2 => $value2) {
                        if ($value2->label == self::LABEL_POSITIVE_CODE) {
                            $countPositives++;
                        }

                        if ($value2->label == self::LABEL_NEGATIVE_CODE) {
                            $countNegatives++;
                        }
                    }

                    // If positive and negative image labels is fifty:fifty then assigned as inconclusive image.
                    if ($countPositives == $countNegatives) {
                        array_push($inconclusiveImages, $value);
                    }

                    // If count of positives is larger than negatives then assigned file as positive
                    // so it should be removed from the negative image dataset.
                    if ($countPositives > $countNegatives) {
                        array_push($nonNegatives, $value);
                    }
                }
            }

            // Intersect array.
            $intersectFromInconclusiveImages = array_diff($images_negative, $inconclusiveImages);
            $intersectFromNonNegatives = array_diff($intersectFromInconclusiveImages, $nonNegatives);
            $files = $intersectFromNonNegatives;

            if ($zip->open(public_path($fileName), ZipArchive::CREATE || ZipArchive::OVERWRITE) === TRUE) {
                // Prepare metadata JSON file
                $data_json = [];

                foreach (array_unique($files) as $key => $value) {
                    $item = public_path('files/images/iva/' . $value);
                    $relativeNameInZipFile = basename($item);
                    $zip->addFile($item, $relativeNameInZipFile);

                    // Populate file metadata for JSON.
                    $name = $value;
                    $email = ImageLabel::where('filename', $name)->first()->email;
                    $bounding_boxes = [];
                    foreach (ImageAreaMark::where('filename', $value)->get() as $key2 => $value2) {
                        if ($value2->label == 0) {
                            array_push($bounding_boxes, [$value2->rect_x0, $value2->rect_y0, $value2->rect_x1, $value2->rect_y1]);
                        }
                    }
                    array_push($data_json, [
                        'name' => $name,
                        'email' => $email,
                        'label' => self::LABEL_NEGATIVE,
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
            return response()->json([
                'message' => 'File not found!'
            ]);
        }
    }
}
