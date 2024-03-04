<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Section;

class SectionController extends Controller
{
    public function index() 
    {
        $sections = Section::all();

        return response()->json($sections);
    }

    public function store(Request $request)
    {
        $section = new Section; 
        $section->title = $request->title;
        $section->order = $request->order ?? 0;
        $section->save();

        $response = [
            "message" => "Section created.",
            "section" => $section
        ];

        return response()->json($response, 201);
    }

    public function update(Request $request, $id)
    {
        $section = Section::find($id); 
        $section->title = $request->title;
        $section->order = $request->order;
        $section->save();

        $response = [
            "message" => "Section updated.",
            "section" => $section
        ];

        return response()->json($response);
    }

    public function delete(Request $request, $id)
    {
        $section = Section::find($id); 
        $section->delete();

        $response = [
            "message" => "Section deleted."
        ];

        return response()->json($response, 200);
    }
}