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
            "message" => "Section created."
        ];

        return response()->json($response, 201);
    }

    public function update(Request $request, $id)
    {
        $section = Section::find($id); 
        $section->title = $request->title;
        $section->order = $request->order;
        $section->save();

        return response()->json($section);
    }

    public function delete(Request $request, $id)
    {
        $section = Section::find($id); 
        $section->delete();

        return response()->json($section);
    }
}