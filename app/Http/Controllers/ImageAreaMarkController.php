<?php

namespace App\Http\Controllers;

use App\ImageAreaMark;
use Illuminate\Http\Request;

class ImageAreaMarkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function checkNullValue($value)
    {
        if (empty($value)) {
            return "";
        } else {
            return $value;
        }
    }

    public function index($requestid)
    {
        return view('file.image_area_mark', [
            'requestid' => $requestid,
            'files' => ImageAreaMark::where('filename', $requestid)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'filename' => 'required',
            'rectX0' => 'required',
            'rectY0' => 'required',
            'rectX1' => 'required',
            'rectY1' => 'required',
            'imageMarkLabel' => 'required',
            'textDescription' => 'nullable',
        ]);

        ImageAreaMark::create([
            'filename' => $request->filename,
            'rect_x0' => $request->rectX0,
            'rect_y0' => $request->rectY0,
            'rect_x1' => $request->rectX1,
            'rect_y1' => $request->rectY1,
            'file' => $request->imageMarkLabel,
            'description' => $this->checkNullValue($request->textDescription)
        ]);

        return redirect()
            ->back()
            ->with('message', 'Data berhasil disimpan!');
    }

    public function delete($requestid)
    {
        ImageAreaMark::where('id', $requestid)->first()->delete();

        return redirect()
            ->back()
            ->with('message', 'Data berhasil dihapus!');
    }
}
