<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index()
    {
        return response()->json(GeneralSetting::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'key' => 'required|string|unique:general_settings,key',
            'value' => 'nullable|string',
        ]);

        $setting = GeneralSetting::create($validated);
        return response()->json($setting, 201);
    }

    public function show($key)
    {
        $setting = GeneralSetting::where('key', $key)->firstOrFail();
        return response()->json($setting);
    }

    public function update(Request $request, GeneralSetting $generalSetting)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'key' => 'required|string|unique:general_settings,key,' . $generalSetting->id,
            'value' => 'nullable|string',
        ]);

        $generalSetting->update($validated);
        return response()->json($generalSetting);
    }

    public function destroy(GeneralSetting $generalSetting)
    {
        $generalSetting->delete();
        return response()->noContent();
    }

    public function getTargetScore()
    {
        $setting = GeneralSetting::where('key', 'target-score')->first();
        return response()->json(['target-score' => $setting ? $setting->value : 90]);
    }

    public function updateTargetScore(Request $request)
    {
        $request->validate([
            'target-score' => 'required|numeric|min:0|max:100',
        ]);

        GeneralSetting::updateOrCreate(
            ['key' => 'target-score'],
            [
                'name' => 'Target Score',
                'value' => $request->input('target-score')
            ]
        );

        return response()->json(['message' => 'Target score updated successfully']);
    }
}