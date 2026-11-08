<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\AppSetting;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AboutController extends BaseController
{
    private const FIELDS = ['title_ar', 'title_en', 'body_ar', 'body_en'];

    /** Anyone signed in may read the about page; the owner also gets every language to edit. */
    public function show()
    {
        return response()->json(['data' => $this->content()]);
    }

    /** Owner only (route middleware). Body is rich HTML, cleaned before it is stored. */
    public function update(Request $request)
    {
        $data = $request->validate([
            'title_ar' => 'nullable|string|max:150',
            'title_en' => 'nullable|string|max:150',
            'body_ar' => 'nullable|string|max:200000',
            'body_en' => 'nullable|string|max:200000',
        ]);

        foreach (self::FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }
            $value = str_starts_with($field, 'body_') ? HtmlSanitizer::clean($data[$field]) : strip_tags((string) $data[$field]);
            AppSetting::updateOrCreate(['key' => 'about.'.$field], ['value' => $value]);
        }

        return response()->json(['data' => $this->content()]);
    }

    /** Owner only: stores an image for the editor and returns its public URL. */
    public function uploadImage(Request $request)
    {
        $request->validate(['image' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120']);

        $file = $request->file('image');
        $name = Str::uuid().'.'.$file->extension();
        $file->move(public_path('uploads/about'), $name);

        return response()->json(['url' => '/uploads/about/'.$name]);
    }

    private function content(): array
    {
        $stored = AppSetting::whereIn('key', array_map(fn ($f) => 'about.'.$f, self::FIELDS))
            ->pluck('value', 'key');

        $content = [];
        foreach (self::FIELDS as $field) {
            $content[$field] = (string) ($stored['about.'.$field] ?? '');
        }

        return $content;
    }
}
