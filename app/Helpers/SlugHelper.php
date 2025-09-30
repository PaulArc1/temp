<?php

namespace App\Helpers;

class SlugHelper
{
    /**
     * Generate a Unique Slug.
     *
     * @param  string  $title
     * @param  object  $model
     * @param  string  $field
     * @param  string  $separator
     *
     * @throws \Exception
     */
    public static function generate($model, $title, $field, $separator = '-'): string
    {
        $id = 0;

        $hostname = preg_replace('(^https?://)', '', config('app.url'));

        $slug = preg_replace('/\s+/', $separator, (trim(strtolower($title))));
        $slug = preg_replace('/\?+/', $separator, (trim(strtolower($slug))));
        $slug = preg_replace('/\#+/', $separator, (trim(strtolower($slug))));
        $slug = preg_replace('/\/+/', $separator, (trim(strtolower($slug))));

        //$slug = str_replace($hostname, '', $slug);

        // $slug = preg_replace('!['.preg_quote($separator).']+!u', $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        // $slug = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($slug));

        // Replace all separator characters and whitespace by a single separator
        $slug = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $slug);

        // Get any that could possibly be related.
        // This cuts the queries down by doing it once.
        $allSlugs = static::getRelatedSlugs($slug, $id, $model, $field);

        // If we haven't used it before then we are all good.
        if (! $allSlugs->contains("$field", $slug)) {
            return $slug;
        }

        // Just append numbers like a savage until we find not used.
        for ($i = 1; $i <= 10; $i++) {
            //$newSlug = $slug . $separator . $i;

            $newSlug = str_replace('.'.$hostname, '', $slug).$separator.$i.'.'.$hostname;

            if (! $allSlugs->contains("$field", $newSlug)) {
                return $newSlug;
            }
        }

        throw new \Exception('Can not create a unique slug');
    }

    private static function getRelatedSlugs($slug, $id, $model, $field)
    {
        if (empty($id)) {
            $id = 0;
        }

        return $model::select("$field")->where("$field", 'like', $slug.'%')
            ->where('id', '<>', $id)
            ->get();
    }
}
