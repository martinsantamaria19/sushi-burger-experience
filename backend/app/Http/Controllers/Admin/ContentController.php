<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function index()
    {
        $groups = SiteSetting::select('group')->distinct()->orderBy('group')->pluck('group');
        $byGroup = [];
        foreach ($groups as $group) {
            $byGroup[$group] = SiteSetting::where('group', $group)->orderBy('key')->get();
        }

        return view('admin.contents.index', compact('byGroup'));
    }

    public function edit(string $group)
    {
        $settings = SiteSetting::where('group', $group)->orderBy('key')->get();
        if ($settings->isEmpty()) {
            return redirect()->route('admin.contents.index')->with('error', 'Grupo no encontrado');
        }

        return view('admin.contents.edit', compact('group', 'settings'));
    }

    public function update(Request $request, string $group)
    {
        $settings = SiteSetting::where('group', $group)->get();

        foreach ($settings as $setting) {
            $key = 'key_' . $setting->id;
            if (!$request->has($key)) {
                continue;
            }

            if ($setting->type === 'image') {
                $file = $request->file($key);
                if ($file && $file->isValid()) {
                    $path = $file->store('site', 'public');
                    $setting->update(['value' => $path]);
                }
            } else {
                $setting->update(['value' => $request->input($key, '')]);
            }
        }

        return redirect()->route('admin.contents.edit', $group)->with('success', 'Contenidos actualizados.');
    }
}
