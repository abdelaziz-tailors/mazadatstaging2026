<?php

namespace App\Helpers;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class TranslationHelper {
    public static function translate($key, $localeCode = NULL)
    {
        if ($localeCode == NULL) {
            $local = app()->getLocale();
        }
        else {
            $local = $localeCode;
        }

        foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            if (!file_exists(base_path('resources/lang/' . $localeCode . '/app.php')))
            {
                if(!is_dir(base_path('resources/lang/' . $localeCode))) {
                    mkdir(base_path('resources/lang/' . $localeCode));
                }
                copy(base_path('resources/lang/en/app.php'), base_path('resources/lang/' . $localeCode . '/app.php'));
            }
            $lang_array = include(base_path('resources/lang/' . $localeCode . '/app.php'));
            $key = str_ireplace(['\'', '"', ',', ';', '<', '>', '?', '.', '@', '#', '!', '%', '$', '؟'], ' ', $key);
            $clean_key = strtolower(str_replace(' ', '_', $key));
            if (!array_key_exists($clean_key, $lang_array)) {
                $lang_array[$clean_key] = $key;
                $str = "<?php return " . var_export($lang_array, true) . ";";
                file_put_contents(base_path('resources/lang/' . $localeCode . '/app.php'), $str);
            }
        }
        $result = trans('app.'.$clean_key, [], $local);
        return $result;
    }

    public static function language_direction ($localeCode) {
        if ($localeCode == 'ar') {
            return 'rtl';
        }
        else {
            return 'ltr';
        }
    }
}
