<?php
function url_shorten( $url, $length = 35 ) {
    $stripped = str_replace( array( 'https://', 'http://', 'www.' ), '', $url );
    $short_url = rtrim( $stripped, '/\\' );

    if ( strlen( $short_url ) > $length ) {
        $short_url = substr( $short_url, 0, $length - 3 ) . '&hellip;';
    }
    return $short_url;
}

function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $post_max_size = parse_size(ini_get('post_max_size'));
    if ($post_max_size > 0) {
      $max_size = $post_max_size;
    }

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}

function recaptcha() {
    return NoCaptcha::renderJs().NoCaptcha::display();
}
function current_theme() {
    return Theme::current()->name;
}
function current_locale() {
    return LaravelLocalization::getCurrentLocale();
}
function default_locale() {
    return LaravelLocalization::getDefaultLocale();
}
function current_locale_direction() {
    return LaravelLocalization::getCurrentLocaleDirection();
}
function current_locale_native() {
    return LaravelLocalization::getCurrentLocaleNative();
}
function supported_locales() {
    return LaravelLocalization::getSupportedLocales();
}
function get_localized_url($locale_code) {
    return LaravelLocalization::getLocalizedURL($locale_code, null, [], ($locale_code != default_locale()));
}
function current_website() {
    return config('website');
}

function parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

function flatten($elements, $depth) {
    $result = array();

    foreach ($elements as $element) {
        $element['depth'] = $depth;

        if (isset($element['child'])) {
            $children = $element['child'];
            unset($element['child']);
        } else {
            $children = null;
        }

        $result[] = $element;

        if (isset($children)) {
            $result = array_merge($result, flatten($children, $depth + 1));
        }
    }

    return $result;
}

function metersToMiles($i) {
     return number_format($i*0.000621371192, 0) . ' miles';
}
function milesToMeters($i) {
     return number_format($i*1609.344, 0) . ' km';
}

function format_money($price, $currency) {
	$placement = 'before';
	try {
		$currency = new \Gerardojbaez\Money\Currency($currency);
		$currency->setSymbolPlacement($placement);
		if($price > 1000) {
			$currency->setPrecision(0);
		}
		$money = new \Gerardojbaez\Money\Money($price, $currency);
		return $money->format();
	} catch(\Exception $e) {
		if($placement == 'before')
			return $currency . ' '. number_format($price, 2);
		else
			return number_format($price, 2) . ' ' . $currency ;
	}
}
function getDir($id, $levels_deep = 32) {
    $file_hash   = md5($id);
    $dirname     = implode("/", str_split(
        substr($file_hash, 0, $levels_deep)
    ));
    return $dirname;
}
function store($dirname, $filename) {
    return $dirname . "/" . $filename;
}


function jsdeliver_combine($theme = 'default', $type = 'js') {
    $jsdeliver_js = "";
    if(file_exists(themes_path($theme.'/jsdeliver.json'))) {
        $files = json_decode(file_get_contents(themes_path($theme.'/jsdeliver.json')), true);
        $jsdeliver_js = implode(",", $files[$type]);
    }
    return $jsdeliver_js;
}

function array_to_string($input, $level = 0) {
    $array = json_decode(json_encode($input), true);
    $text = "";
    foreach($array as $k => $v) {
        if(is_array($v)) {
            $level = $level+1;
            $text .= str_repeat("&#8212;", $level) . " " . $k."\n";
            $text .= array_to_string($v, $level);
        } else{
            if($v)
                $text .= str_repeat("&#8212;", $level+1) . " $k: $v\n";
        }
    }
    return $text;
}

use Barryvdh\TranslationManager\Models\Translation;

function save_language_file($file, $strings) {
    /*$file = resource_path("views/langs/$file.php");
    $output = "<?php\n\n";
    foreach($strings as $string) {
        $output .= "__('".$string."');\n";
    }
    \File::put($file, $output);

    foreach($strings as $string) {
        $output .= "__('".$string."');\n";
    }*/

    //insert it into the database
    $selected_locale = 'en';

    if ($strings instanceof Illuminate\Support\Collection) {
        $strings = $strings->toArray();
    }
    $strings = array_unique($strings);
    $strings = array_filter($strings);
    foreach($strings as $string) {
        $translation_row = Translation::where('locale', $selected_locale)
            ->where('group', '_json')
            ->where('key', $string)
            ->first();
        if(!$translation_row) {
            $translation_row = new Translation();
            $translation_row->key = $string;
            $translation_row->locale = $selected_locale;
            $translation_row->group = '_json';
            $translation_row->value = null;
            $translation_row->save();
        }
    }
}

function menu($location = null, $locale = null) {
    if(!$locale)
        $locale = LaravelLocalization::getCurrentLocale();
    if(!$location)
        $location = 'top';
    $menu = \App\Models\Menu::where('location', $location)->where('locale', $locale)->first();
    if($menu)
        return $menu->items;
    return [];
}

function _l($string) {
    return __($string);
}

function _p($string, $number = 2) {

    if((int) $number == 1) {
        return __($string);
    }

    if(!str_contains(__($string.'_plural'), "_plural")) {
        return __($string.'_plural');
    }

    try {
        return str_plural(__($string));
    } catch(\Exception $e) {

    }

    return __($string);
}

function widget($widget_class, $params = []) {
    try {
        return \Widget::run($widget_class, $params);
    } catch(\Exception $e) {

    }
}

function asyncWidget($widget_class, $params = []) {
    try {
        return \AsyncWidget::run($widget_class, $params);
    } catch(\Exception $e) {

    }
}

function module_enabled($alias) {
    $module = \Module::findByAlias($alias);
    return (bool) ($module && $module->enabled());
}


function unslug($text, $delimiter = '-')
{
    return ucfirst(str_replace($delimiter, ' ', $text));
}

function glob_recursive($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
    {
        $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }

    return $files;
}

function language_options() {
    return [
        'ace'         => ['name' => 'Achinese',               'script' => 'Latn', 'native' => 'Aceh', 'regional' => ''],
        'af'          => ['name' => 'Afrikaans',              'script' => 'Latn', 'native' => 'Afrikaans', 'regional' => 'af_ZA'],
        'agq'         => ['name' => 'Aghem',                  'script' => 'Latn', 'native' => 'Aghem', 'regional' => ''],
        'ak'          => ['name' => 'Akan',                   'script' => 'Latn', 'native' => 'Akan', 'regional' => 'ak_GH'],
        'an'          => ['name' => 'Aragonese',              'script' => 'Latn', 'native' => 'aragon??s', 'regional' => 'an_ES'],
        'cch'         => ['name' => 'Atsam',                  'script' => 'Latn', 'native' => 'Atsam', 'regional' => ''],
        'gn'          => ['name' => 'Guaran??',                'script' => 'Latn', 'native' => 'Ava??e??????', 'regional' => ''],
        'ae'          => ['name' => 'Avestan',                'script' => 'Latn', 'native' => 'avesta', 'regional' => ''],
        'ay'          => ['name' => 'Aymara',                 'script' => 'Latn', 'native' => 'aymar aru', 'regional' => 'ay_PE'],
        'az'          => ['name' => 'Azerbaijani (Latin)',    'script' => 'Latn', 'native' => 'az??rbaycanca', 'regional' => 'az_AZ'],
        'id'          => ['name' => 'Indonesian',             'script' => 'Latn', 'native' => 'Bahasa Indonesia', 'regional' => 'id_ID'],
        'ms'          => ['name' => 'Malay',                  'script' => 'Latn', 'native' => 'Bahasa Melayu', 'regional' => 'ms_MY'],
        'bm'          => ['name' => 'Bambara',                'script' => 'Latn', 'native' => 'bamanakan', 'regional' => ''],
        'jv'          => ['name' => 'Javanese (Latin)',       'script' => 'Latn', 'native' => 'Basa Jawa', 'regional' => ''],
        'su'          => ['name' => 'Sundanese',              'script' => 'Latn', 'native' => 'Basa Sunda', 'regional' => ''],
        'bh'          => ['name' => 'Bihari',                 'script' => 'Latn', 'native' => 'Bihari', 'regional' => ''],
        'bi'          => ['name' => 'Bislama',                'script' => 'Latn', 'native' => 'Bislama', 'regional' => ''],
        'nb'          => ['name' => 'Norwegian Bokm??l',       'script' => 'Latn', 'native' => 'Bokm??l', 'regional' => 'nb_NO'],
        'bs'          => ['name' => 'Bosnian',                'script' => 'Latn', 'native' => 'bosanski', 'regional' => 'bs_BA'],
        'br'          => ['name' => 'Breton',                 'script' => 'Latn', 'native' => 'brezhoneg', 'regional' => 'br_FR'],
        'ca'          => ['name' => 'Catalan',                'script' => 'Latn', 'native' => 'catal??', 'regional' => 'ca_ES'],
        'ch'          => ['name' => 'Chamorro',               'script' => 'Latn', 'native' => 'Chamoru', 'regional' => ''],
        'ny'          => ['name' => 'Chewa',                  'script' => 'Latn', 'native' => 'chiChe??a', 'regional' => ''],
        'kde'         => ['name' => 'Makonde',                'script' => 'Latn', 'native' => 'Chimakonde', 'regional' => ''],
        'sn'          => ['name' => 'Shona',                  'script' => 'Latn', 'native' => 'chiShona', 'regional' => ''],
        'co'          => ['name' => 'Corsican',               'script' => 'Latn', 'native' => 'corsu', 'regional' => ''],
        'cy'          => ['name' => 'Welsh',                  'script' => 'Latn', 'native' => 'Cymraeg', 'regional' => 'cy_GB'],
        'da'          => ['name' => 'Danish',                 'script' => 'Latn', 'native' => 'dansk', 'regional' => 'da_DK'],
        'se'          => ['name' => 'Northern Sami',          'script' => 'Latn', 'native' => 'davvis??megiella', 'regional' => 'se_NO'],
        'de'          => ['name' => 'German',                 'script' => 'Latn', 'native' => 'Deutsch', 'regional' => 'de_DE'],
        'luo'         => ['name' => 'Luo',                    'script' => 'Latn', 'native' => 'Dholuo', 'regional' => ''],
        'nv'          => ['name' => 'Navajo',                 'script' => 'Latn', 'native' => 'Din?? bizaad', 'regional' => ''],
        'dua'         => ['name' => 'Duala',                  'script' => 'Latn', 'native' => 'du??l??', 'regional' => ''],
        'et'          => ['name' => 'Estonian',               'script' => 'Latn', 'native' => 'eesti', 'regional' => 'et_EE'],
        'na'          => ['name' => 'Nauru',                  'script' => 'Latn', 'native' => 'Ekakair?? Naoero', 'regional' => ''],
        'guz'         => ['name' => 'Ekegusii',               'script' => 'Latn', 'native' => 'Ekegusii', 'regional' => ''],
        'en'          => ['name' => 'English',                'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
        'en-AU'       => ['name' => 'Australian English',     'script' => 'Latn', 'native' => 'Australian English', 'regional' => 'en_AU'],
        'en-GB'       => ['name' => 'British English',        'script' => 'Latn', 'native' => 'British English', 'regional' => 'en_GB'],
        'en-US'       => ['name' => 'U.S. English',           'script' => 'Latn', 'native' => 'U.S. English', 'regional' => 'en_US'],
        'es'          => ['name' => 'Spanish',                'script' => 'Latn', 'native' => 'espa??ol', 'regional' => 'es_ES'],
        'eo'          => ['name' => 'Esperanto',              'script' => 'Latn', 'native' => 'esperanto', 'regional' => ''],
        'eu'          => ['name' => 'Basque',                 'script' => 'Latn', 'native' => 'euskara', 'regional' => 'eu_ES'],
        'ewo'         => ['name' => 'Ewondo',                 'script' => 'Latn', 'native' => 'ewondo', 'regional' => ''],
        'ee'          => ['name' => 'Ewe',                    'script' => 'Latn', 'native' => 'e??egbe', 'regional' => ''],
        'fil'         => ['name' => 'Filipino',               'script' => 'Latn', 'native' => 'Filipino', 'regional' => 'fil_PH'],
        'fr'          => ['name' => 'French',                 'script' => 'Latn', 'native' => 'fran??ais', 'regional' => 'fr_FR'],
        'fr-CA'       => ['name' => 'Canadian French',        'script' => 'Latn', 'native' => 'fran??ais canadien', 'regional' => 'fr_CA'],
        'fy'          => ['name' => 'Western Frisian',        'script' => 'Latn', 'native' => 'frysk', 'regional' => 'fy_DE'],
        'fur'         => ['name' => 'Friulian',               'script' => 'Latn', 'native' => 'furlan', 'regional' => 'fur_IT'],
        'fo'          => ['name' => 'Faroese',                'script' => 'Latn', 'native' => 'f??royskt', 'regional' => 'fo_FO'],
        'gaa'         => ['name' => 'Ga',                     'script' => 'Latn', 'native' => 'Ga', 'regional' => ''],
        'ga'          => ['name' => 'Irish',                  'script' => 'Latn', 'native' => 'Gaeilge', 'regional' => 'ga_IE'],
        'gv'          => ['name' => 'Manx',                   'script' => 'Latn', 'native' => 'Gaelg', 'regional' => 'gv_GB'],
        'sm'          => ['name' => 'Samoan',                 'script' => 'Latn', 'native' => 'Gagana fa???a S??moa', 'regional' => ''],
        'gl'          => ['name' => 'Galician',               'script' => 'Latn', 'native' => 'galego', 'regional' => 'gl_ES'],
        'ki'          => ['name' => 'Kikuyu',                 'script' => 'Latn', 'native' => 'Gikuyu', 'regional' => ''],
        'gd'          => ['name' => 'Scottish Gaelic',        'script' => 'Latn', 'native' => 'G??idhlig', 'regional' => 'gd_GB'],
        'ha'          => ['name' => 'Hausa',                  'script' => 'Latn', 'native' => 'Hausa', 'regional' => 'ha_NG'],
        'bez'         => ['name' => 'Bena',                   'script' => 'Latn', 'native' => 'Hibena', 'regional' => ''],
        'ho'          => ['name' => 'Hiri Motu',              'script' => 'Latn', 'native' => 'Hiri Motu', 'regional' => ''],
        'hr'          => ['name' => 'Croatian',               'script' => 'Latn', 'native' => 'hrvatski', 'regional' => 'hr_HR'],
        'bem'         => ['name' => 'Bemba',                  'script' => 'Latn', 'native' => 'Ichibemba', 'regional' => 'bem_ZM'],
        'io'          => ['name' => 'Ido',                    'script' => 'Latn', 'native' => 'Ido', 'regional' => ''],
        'ig'          => ['name' => 'Igbo',                   'script' => 'Latn', 'native' => 'Igbo', 'regional' => 'ig_NG'],
        'rn'          => ['name' => 'Rundi',                  'script' => 'Latn', 'native' => 'Ikirundi', 'regional' => ''],
        'ia'          => ['name' => 'Interlingua',            'script' => 'Latn', 'native' => 'interlingua', 'regional' => 'ia_FR'],
        'iu-Latn'     => ['name' => 'Inuktitut (Latin)',      'script' => 'Latn', 'native' => 'Inuktitut', 'regional' => 'iu_CA'],
        'sbp'         => ['name' => 'Sileibi',                'script' => 'Latn', 'native' => 'Ishisangu', 'regional' => ''],
        'nd'          => ['name' => 'North Ndebele',          'script' => 'Latn', 'native' => 'isiNdebele', 'regional' => ''],
        'nr'          => ['name' => 'South Ndebele',          'script' => 'Latn', 'native' => 'isiNdebele', 'regional' => 'nr_ZA'],
        'xh'          => ['name' => 'Xhosa',                  'script' => 'Latn', 'native' => 'isiXhosa', 'regional' => 'xh_ZA'],
        'zu'          => ['name' => 'Zulu',                   'script' => 'Latn', 'native' => 'isiZulu', 'regional' => 'zu_ZA'],
        'it'          => ['name' => 'Italian',                'script' => 'Latn', 'native' => 'italiano', 'regional' => 'it_IT'],
        'ik'          => ['name' => 'Inupiaq',                'script' => 'Latn', 'native' => 'I??upiaq', 'regional' => 'ik_CA'],
        'dyo'         => ['name' => 'Jola-Fonyi',             'script' => 'Latn', 'native' => 'joola', 'regional' => ''],
        'kea'         => ['name' => 'Kabuverdianu',           'script' => 'Latn', 'native' => 'kabuverdianu', 'regional' => ''],
        'kaj'         => ['name' => 'Jju',                    'script' => 'Latn', 'native' => 'Kaje', 'regional' => ''],
        'mh'          => ['name' => 'Marshallese',            'script' => 'Latn', 'native' => 'Kajin M??aje??', 'regional' => 'mh_MH'],
        'kl'          => ['name' => 'Kalaallisut',            'script' => 'Latn', 'native' => 'kalaallisut', 'regional' => 'kl_GL'],
        'kln'         => ['name' => 'Kalenjin',               'script' => 'Latn', 'native' => 'Kalenjin', 'regional' => ''],
        'kr'          => ['name' => 'Kanuri',                 'script' => 'Latn', 'native' => 'Kanuri', 'regional' => ''],
        'kcg'         => ['name' => 'Tyap',                   'script' => 'Latn', 'native' => 'Katab', 'regional' => ''],
        'kw'          => ['name' => 'Cornish',                'script' => 'Latn', 'native' => 'kernewek', 'regional' => 'kw_GB'],
        'naq'         => ['name' => 'Nama',                   'script' => 'Latn', 'native' => 'Khoekhoegowab', 'regional' => ''],
        'rof'         => ['name' => 'Rombo',                  'script' => 'Latn', 'native' => 'Kihorombo', 'regional' => ''],
        'kam'         => ['name' => 'Kamba',                  'script' => 'Latn', 'native' => 'Kikamba', 'regional' => ''],
        'kg'          => ['name' => 'Kongo',                  'script' => 'Latn', 'native' => 'Kikongo', 'regional' => ''],
        'jmc'         => ['name' => 'Machame',                'script' => 'Latn', 'native' => 'Kimachame', 'regional' => ''],
        'rw'          => ['name' => 'Kinyarwanda',            'script' => 'Latn', 'native' => 'Kinyarwanda', 'regional' => 'rw_RW'],
        'asa'         => ['name' => 'Kipare',                 'script' => 'Latn', 'native' => 'Kipare', 'regional' => ''],
        'rwk'         => ['name' => 'Rwa',                    'script' => 'Latn', 'native' => 'Kiruwa', 'regional' => ''],
        'saq'         => ['name' => 'Samburu',                'script' => 'Latn', 'native' => 'Kisampur', 'regional' => ''],
        'ksb'         => ['name' => 'Shambala',               'script' => 'Latn', 'native' => 'Kishambaa', 'regional' => ''],
        'swc'         => ['name' => 'Congo Swahili',          'script' => 'Latn', 'native' => 'Kiswahili ya Kongo', 'regional' => ''],
        'sw'          => ['name' => 'Swahili',                'script' => 'Latn', 'native' => 'Kiswahili', 'regional' => 'sw_KE'],
        'dav'         => ['name' => 'Dawida',                 'script' => 'Latn', 'native' => 'Kitaita', 'regional' => ''],
        'teo'         => ['name' => 'Teso',                   'script' => 'Latn', 'native' => 'Kiteso', 'regional' => ''],
        'khq'         => ['name' => 'Koyra Chiini',           'script' => 'Latn', 'native' => 'Koyra ciini', 'regional' => ''],
        'ses'         => ['name' => 'Songhay',                'script' => 'Latn', 'native' => 'Koyraboro senni', 'regional' => ''],
        'mfe'         => ['name' => 'Morisyen',               'script' => 'Latn', 'native' => 'kreol morisien', 'regional' => ''],
        'ht'          => ['name' => 'Haitian',                'script' => 'Latn', 'native' => 'Krey??l ayisyen', 'regional' => 'ht_HT'],
        'kj'          => ['name' => 'Kuanyama',               'script' => 'Latn', 'native' => 'Kwanyama', 'regional' => ''],
        'ksh'         => ['name' => 'K??lsch',                 'script' => 'Latn', 'native' => 'K??lsch', 'regional' => ''],
        'ebu'         => ['name' => 'Kiembu',                 'script' => 'Latn', 'native' => 'K??embu', 'regional' => ''],
        'mer'         => ['name' => 'Kim????ru',                'script' => 'Latn', 'native' => 'K??m??r??', 'regional' => ''],
        'lag'         => ['name' => 'Langi',                  'script' => 'Latn', 'native' => 'K??laangi', 'regional' => ''],
        'lah'         => ['name' => 'Lahnda',                 'script' => 'Latn', 'native' => 'Lahnda', 'regional' => ''],
        'la'          => ['name' => 'Latin',                  'script' => 'Latn', 'native' => 'latine', 'regional' => ''],
        'lv'          => ['name' => 'Latvian',                'script' => 'Latn', 'native' => 'latvie??u', 'regional' => 'lv_LV'],
        'to'          => ['name' => 'Tongan',                 'script' => 'Latn', 'native' => 'lea fakatonga', 'regional' => ''],
        'lt'          => ['name' => 'Lithuanian',             'script' => 'Latn', 'native' => 'lietuvi??', 'regional' => 'lt_LT'],
        'li'          => ['name' => 'Limburgish',             'script' => 'Latn', 'native' => 'Limburgs', 'regional' => 'li_BE'],
        'ln'          => ['name' => 'Lingala',                'script' => 'Latn', 'native' => 'ling??la', 'regional' => ''],
        'lg'          => ['name' => 'Ganda',                  'script' => 'Latn', 'native' => 'Luganda', 'regional' => 'lg_UG'],
        'luy'         => ['name' => 'Oluluyia',               'script' => 'Latn', 'native' => 'Luluhia', 'regional' => ''],
        'lb'          => ['name' => 'Luxembourgish',          'script' => 'Latn', 'native' => 'L??tzebuergesch', 'regional' => 'lb_LU'],
        'hu'          => ['name' => 'Hungarian',              'script' => 'Latn', 'native' => 'magyar', 'regional' => 'hu_HU'],
        'mgh'         => ['name' => 'Makhuwa-Meetto',         'script' => 'Latn', 'native' => 'Makua', 'regional' => ''],
        'mg'          => ['name' => 'Malagasy',               'script' => 'Latn', 'native' => 'Malagasy', 'regional' => 'mg_MG'],
        'mt'          => ['name' => 'Maltese',                'script' => 'Latn', 'native' => 'Malti', 'regional' => 'mt_MT'],
        'mtr'         => ['name' => 'Mewari',                 'script' => 'Latn', 'native' => 'Mewari', 'regional' => ''],
        'mua'         => ['name' => 'Mundang',                'script' => 'Latn', 'native' => 'Mundang', 'regional' => ''],
        'mi'          => ['name' => 'M??ori',                  'script' => 'Latn', 'native' => 'M??ori', 'regional' => 'mi_NZ'],
        'nl'          => ['name' => 'Dutch',                  'script' => 'Latn', 'native' => 'Nederlands', 'regional' => 'nl_NL'],
        'nmg'         => ['name' => 'Kwasio',                 'script' => 'Latn', 'native' => 'ngumba', 'regional' => ''],
        'yav'         => ['name' => 'Yangben',                'script' => 'Latn', 'native' => 'nuasue', 'regional' => ''],
        'nn'          => ['name' => 'Norwegian Nynorsk',      'script' => 'Latn', 'native' => 'nynorsk', 'regional' => 'nn_NO'],
        'oc'          => ['name' => 'Occitan',                'script' => 'Latn', 'native' => 'occitan', 'regional' => 'oc_FR'],
        'ang'         => ['name' => 'Old English',            'script' => 'Runr', 'native' => 'Old English', 'regional' => ''],
        'xog'         => ['name' => 'Soga',                   'script' => 'Latn', 'native' => 'Olusoga', 'regional' => ''],
        'om'          => ['name' => 'Oromo',                  'script' => 'Latn', 'native' => 'Oromoo', 'regional' => 'om_ET'],
        'ng'          => ['name' => 'Ndonga',                 'script' => 'Latn', 'native' => 'OshiNdonga', 'regional' => ''],
        'hz'          => ['name' => 'Herero',                 'script' => 'Latn', 'native' => 'Otjiherero', 'regional' => ''],
        'uz-Latn'     => ['name' => 'Uzbek (Latin)',          'script' => 'Latn', 'native' => 'o??zbekcha', 'regional' => 'uz_UZ'],
        'nds'         => ['name' => 'Low German',             'script' => 'Latn', 'native' => 'Plattd????tsch', 'regional' => 'nds_DE'],
        'pl'          => ['name' => 'Polish',                 'script' => 'Latn', 'native' => 'polski', 'regional' => 'pl_PL'],
        'pt'          => ['name' => 'Portuguese',             'script' => 'Latn', 'native' => 'portugu??s', 'regional' => 'pt_PT'],
        'pt-BR'       => ['name' => 'Brazilian Portuguese',   'script' => 'Latn', 'native' => 'portugu??s do Brasil', 'regional' => 'pt_BR'],
        'ff'          => ['name' => 'Fulah',                  'script' => 'Latn', 'native' => 'Pulaar', 'regional' => 'ff_SN'],
        'pi'          => ['name' => 'Pahari-Potwari',         'script' => 'Latn', 'native' => 'P??li', 'regional' => ''],
        'aa'          => ['name' => 'Afar',                   'script' => 'Latn', 'native' => 'Qafar', 'regional' => 'aa_ER'],
        'ty'          => ['name' => 'Tahitian',               'script' => 'Latn', 'native' => 'Reo M??ohi', 'regional' => ''],
        'ksf'         => ['name' => 'Bafia',                  'script' => 'Latn', 'native' => 'rikpa', 'regional' => ''],
        'ro'          => ['name' => 'Romanian',               'script' => 'Latn', 'native' => 'rom??n??', 'regional' => 'ro_RO'],
        'cgg'         => ['name' => 'Chiga',                  'script' => 'Latn', 'native' => 'Rukiga', 'regional' => ''],
        'rm'          => ['name' => 'Romansh',                'script' => 'Latn', 'native' => 'rumantsch', 'regional' => ''],
        'qu'          => ['name' => 'Quechua',                'script' => 'Latn', 'native' => 'Runa Simi', 'regional' => ''],
        'nyn'         => ['name' => 'Nyankole',               'script' => 'Latn', 'native' => 'Runyankore', 'regional' => ''],
        'ssy'         => ['name' => 'Saho',                   'script' => 'Latn', 'native' => 'Saho', 'regional' => ''],
        'sc'          => ['name' => 'Sardinian',              'script' => 'Latn', 'native' => 'sardu', 'regional' => 'sc_IT'],
        'de-CH'       => ['name' => 'Swiss High German',      'script' => 'Latn', 'native' => 'Schweizer Hochdeutsch', 'regional' => 'de_CH'],
        'gsw'         => ['name' => 'Swiss German',           'script' => 'Latn', 'native' => 'Schwiizert????tsch', 'regional' => ''],
        'trv'         => ['name' => 'Taroko',                 'script' => 'Latn', 'native' => 'Seediq', 'regional' => ''],
        'seh'         => ['name' => 'Sena',                   'script' => 'Latn', 'native' => 'sena', 'regional' => ''],
        'nso'         => ['name' => 'Northern Sotho',         'script' => 'Latn', 'native' => 'Sesotho sa Leboa', 'regional' => 'nso_ZA'],
        'st'          => ['name' => 'Southern Sotho',         'script' => 'Latn', 'native' => 'Sesotho', 'regional' => 'st_ZA'],
        'tn'          => ['name' => 'Tswana',                 'script' => 'Latn', 'native' => 'Setswana', 'regional' => 'tn_ZA'],
        'sq'          => ['name' => 'Albanian',               'script' => 'Latn', 'native' => 'shqip', 'regional' => 'sq_AL'],
        'sid'         => ['name' => 'Sidamo',                 'script' => 'Latn', 'native' => 'Sidaamu Afo', 'regional' => 'sid_ET'],
        'ss'          => ['name' => 'Swati',                  'script' => 'Latn', 'native' => 'Siswati', 'regional' => 'ss_ZA'],
        'sk'          => ['name' => 'Slovak',                 'script' => 'Latn', 'native' => 'sloven??ina', 'regional' => 'sk_SK'],
        'sl'          => ['name' => 'Slovene',                'script' => 'Latn', 'native' => 'sloven????ina', 'regional' => 'sl_SI'],
        'so'          => ['name' => 'Somali',                 'script' => 'Latn', 'native' => 'Soomaali', 'regional' => 'so_SO'],
        'sr-Latn'     => ['name' => 'Serbian (Latin)',        'script' => 'Latn', 'native' => 'Srpski', 'regional' => 'sr_RS'],
        'sh'          => ['name' => 'Serbo-Croatian',         'script' => 'Latn', 'native' => 'srpskohrvatski', 'regional' => ''],
        'fi'          => ['name' => 'Finnish',                'script' => 'Latn', 'native' => 'suomi', 'regional' => 'fi_FI'],
        'sv'          => ['name' => 'Swedish',                'script' => 'Latn', 'native' => 'svenska', 'regional' => 'sv_SE'],
        'sg'          => ['name' => 'Sango',                  'script' => 'Latn', 'native' => 'S??ng??', 'regional' => ''],
        'tl'          => ['name' => 'Tagalog',                'script' => 'Latn', 'native' => 'Tagalog', 'regional' => 'tl_PH'],
        'tzm-Latn'    => ['name' => 'Central Atlas Tamazight (Latin)', 'script' => 'Latn', 'native' => 'Tamazight', 'regional' => ''],
        'kab'         => ['name' => 'Kabyle',                 'script' => 'Latn', 'native' => 'Taqbaylit', 'regional' => 'kab_DZ'],
        'twq'         => ['name' => 'Tasawaq',                'script' => 'Latn', 'native' => 'Tasawaq senni', 'regional' => ''],
        'shi'         => ['name' => 'Tachelhit (Latin)',      'script' => 'Latn', 'native' => 'Tashelhit', 'regional' => ''],
        'nus'         => ['name' => 'Nuer',                   'script' => 'Latn', 'native' => 'Thok Nath', 'regional' => ''],
        'vi'          => ['name' => 'Vietnamese',             'script' => 'Latn', 'native' => 'Ti???ng Vi???t', 'regional' => 'vi_VN'],
        'tg-Latn'     => ['name' => 'Tajik (Latin)',          'script' => 'Latn', 'native' => 'tojik??', 'regional' => 'tg_TJ'],
        'lu'          => ['name' => 'Luba-Katanga',           'script' => 'Latn', 'native' => 'Tshiluba', 'regional' => 've_ZA'],
        've'          => ['name' => 'Venda',                  'script' => 'Latn', 'native' => 'Tshiven???a', 'regional' => ''],
        'tw'          => ['name' => 'Twi',                    'script' => 'Latn', 'native' => 'Twi', 'regional' => ''],
        'tr'          => ['name' => 'Turkish',                'script' => 'Latn', 'native' => 'T??rk??e', 'regional' => 'tr_TR'],
        'ale'         => ['name' => 'Aleut',                  'script' => 'Latn', 'native' => 'Unangax tunuu', 'regional' => ''],
        'ca-valencia' => ['name' => 'Valencian',              'script' => 'Latn', 'native' => 'valenci??', 'regional' => ''],
        'vai-Latn'    => ['name' => 'Vai (Latin)',            'script' => 'Latn', 'native' => 'Viyam????', 'regional' => ''],
        'vo'          => ['name' => 'Volap??k',                'script' => 'Latn', 'native' => 'Volap??k', 'regional' => ''],
        'fj'          => ['name' => 'Fijian',                 'script' => 'Latn', 'native' => 'vosa Vakaviti', 'regional' => ''],
        'wa'          => ['name' => 'Walloon',                'script' => 'Latn', 'native' => 'Walon', 'regional' => 'wa_BE'],
        'wae'         => ['name' => 'Walser',                 'script' => 'Latn', 'native' => 'Walser', 'regional' => 'wae_CH'],
        'wen'         => ['name' => 'Sorbian',                'script' => 'Latn', 'native' => 'Wendic', 'regional' => ''],
        'wo'          => ['name' => 'Wolof',                  'script' => 'Latn', 'native' => 'Wolof', 'regional' => 'wo_SN'],
        'ts'          => ['name' => 'Tsonga',                 'script' => 'Latn', 'native' => 'Xitsonga', 'regional' => 'ts_ZA'],
        'dje'         => ['name' => 'Zarma',                  'script' => 'Latn', 'native' => 'Zarmaciine', 'regional' => ''],
        'yo'          => ['name' => 'Yoruba',                 'script' => 'Latn', 'native' => '??d?? Yor??b??', 'regional' => 'yo_NG'],
        'de-AT'       => ['name' => 'Austrian German',        'script' => 'Latn', 'native' => '??sterreichisches Deutsch', 'regional' => 'de_AT'],
        'is'          => ['name' => 'Icelandic',              'script' => 'Latn', 'native' => '??slenska', 'regional' => 'is_IS'],
        'cs'          => ['name' => 'Czech',                  'script' => 'Latn', 'native' => '??e??tina', 'regional' => 'cs_CZ'],
        'bas'         => ['name' => 'Basa',                   'script' => 'Latn', 'native' => '????s??a', 'regional' => ''],
        'mas'         => ['name' => 'Masai',                  'script' => 'Latn', 'native' => '??l-Maa', 'regional' => ''],
        'haw'         => ['name' => 'Hawaiian',               'script' => 'Latn', 'native' => '????lelo Hawai??i', 'regional' => ''],
        'el'          => ['name' => 'Greek',                  'script' => 'Grek', 'native' => '????????????????', 'regional' => 'el_GR'],
        'uz'          => ['name' => 'Uzbek (Cyrillic)',       'script' => 'Cyrl', 'native' => '??????????', 'regional' => 'uz_UZ'],
        'az-Cyrl'     => ['name' => 'Azerbaijani (Cyrillic)', 'script' => 'Cyrl', 'native' => '????????????????????', 'regional' => 'uz_UZ'],
        'ab'          => ['name' => 'Abkhazian',              'script' => 'Cyrl', 'native' => '??????????', 'regional' => ''],
        'os'          => ['name' => 'Ossetic',                'script' => 'Cyrl', 'native' => '????????', 'regional' => 'os_RU'],
        'ky'          => ['name' => 'Kyrgyz',                 'script' => 'Cyrl', 'native' => '????????????', 'regional' => 'ky_KG'],
        'sr'          => ['name' => 'Serbian (Cyrillic)',     'script' => 'Cyrl', 'native' => '????????????', 'regional' => 'sr_RS'],
        'av'          => ['name' => 'Avaric',                 'script' => 'Cyrl', 'native' => '???????? ????????', 'regional' => ''],
        'ady'         => ['name' => 'Adyghe',                 'script' => 'Cyrl', 'native' => '????????????????', 'regional' => ''],
        'ba'          => ['name' => 'Bashkir',                'script' => 'Cyrl', 'native' => '?????????????? ????????', 'regional' => ''],
        'be'          => ['name' => 'Belarusian',             'script' => 'Cyrl', 'native' => '????????????????????', 'regional' => 'be_BY'],
        'bg'          => ['name' => 'Bulgarian',              'script' => 'Cyrl', 'native' => '??????????????????', 'regional' => 'bg_BG'],
        'kv'          => ['name' => 'Komi',                   'script' => 'Cyrl', 'native' => '???????? ??????', 'regional' => ''],
        'mk'          => ['name' => 'Macedonian',             'script' => 'Cyrl', 'native' => '????????????????????', 'regional' => 'mk_MK'],
        'mn'          => ['name' => 'Mongolian (Cyrillic)',   'script' => 'Cyrl', 'native' => '????????????', 'regional' => 'mn_MN'],
        'ce'          => ['name' => 'Chechen',                'script' => 'Cyrl', 'native' => '?????????????? ????????', 'regional' => 'ce_RU'],
        'ru'          => ['name' => 'Russian',                'script' => 'Cyrl', 'native' => '??????????????', 'regional' => 'ru_RU'],
        'sah'         => ['name' => 'Yakut',                  'script' => 'Cyrl', 'native' => '???????? ????????', 'regional' => ''],
        'tt'          => ['name' => 'Tatar',                  'script' => 'Cyrl', 'native' => '?????????? ????????', 'regional' => 'tt_RU'],
        'tg'          => ['name' => 'Tajik (Cyrillic)',       'script' => 'Cyrl', 'native' => '????????????', 'regional' => 'tg_TJ'],
        'tk'          => ['name' => 'Turkmen',                'script' => 'Cyrl', 'native' => '??????????????????', 'regional' => 'tk_TM'],
        'uk'          => ['name' => 'Ukrainian',              'script' => 'Cyrl', 'native' => '????????????????????', 'regional' => 'uk_UA'],
        'cv'          => ['name' => 'Chuvash',                'script' => 'Cyrl', 'native' => '?????????? ??????????', 'regional' => 'cv_RU'],
        'cu'          => ['name' => 'Church Slavic',          'script' => 'Cyrl', 'native' => '?????????? ????????????????????', 'regional' => ''],
        'kk'          => ['name' => 'Kazakh',                 'script' => 'Cyrl', 'native' => '?????????? ????????', 'regional' => 'kk_KZ'],
        'hy'          => ['name' => 'Armenian',               'script' => 'Armn', 'native' => '??????????????', 'regional' => 'hy_AM'],
        'yi'          => ['name' => 'Yiddish',                'script' => 'Hebr', 'native' => '????????????', 'regional' => 'yi_US'],
        'he'          => ['name' => 'Hebrew',                 'script' => 'Hebr', 'native' => '??????????', 'regional' => 'he_IL'],
        'ug'          => ['name' => 'Uyghur',                 'script' => 'Arab', 'native' => '????????????????', 'regional' => 'ug_CN'],
        'ur'          => ['name' => 'Urdu',                   'script' => 'Arab', 'native' => '????????', 'regional' => 'ur_PK'],
        'ar'          => ['name' => 'Arabic',                 'script' => 'Arab', 'native' => '??????????????', 'regional' => 'ar_AE'],
        'uz-Arab'     => ['name' => 'Uzbek (Arabic)',         'script' => 'Arab', 'native' => '????????????', 'regional' => ''],
        'tg-Arab'     => ['name' => 'Tajik (Arabic)',         'script' => 'Arab', 'native' => '????????????', 'regional' => 'tg_TJ'],
        'sd'          => ['name' => 'Sindhi',                 'script' => 'Arab', 'native' => '????????', 'regional' => 'sd_IN'],
        'fa'          => ['name' => 'Persian',                'script' => 'Arab', 'native' => '??????????', 'regional' => 'fa_IR'],
        'pa-Arab'     => ['name' => 'Punjabi (Arabic)',       'script' => 'Arab', 'native' => '??????????', 'regional' => 'pa_IN'],
        'ps'          => ['name' => 'Pashto',                 'script' => 'Arab', 'native' => '????????', 'regional' => 'ps_AF'],
        'ks'          => ['name' => 'Kashmiri (Arabic)',      'script' => 'Arab', 'native' => '??????????', 'regional' => 'ks_IN'],
        'ku'          => ['name' => 'Kurdish',                'script' => 'Arab', 'native' => '??????????', 'regional' => 'ku_TR'],
        'dv'          => ['name' => 'Divehi',                 'script' => 'Thaa', 'native' => '????????????????????', 'regional' => 'dv_MV'],
        'ks-Deva'     => ['name' => 'Kashmiri (Devaganari)',  'script' => 'Deva', 'native' => '???????????????', 'regional' => 'ks_IN'],
        'kok'         => ['name' => 'Konkani',                'script' => 'Deva', 'native' => '??????????????????', 'regional' => 'kok_IN'],
        'doi'         => ['name' => 'Dogri',                  'script' => 'Deva', 'native' => '???????????????', 'regional' => 'doi_IN'],
        'ne'          => ['name' => 'Nepali',                 'script' => 'Deva', 'native' => '??????????????????', 'regional' => ''],
        'pra'         => ['name' => 'Prakrit',                'script' => 'Deva', 'native' => '?????????????????????', 'regional' => ''],
        'brx'         => ['name' => 'Bodo',                   'script' => 'Deva', 'native' => '????????????', 'regional' => 'brx_IN'],
        'bra'         => ['name' => 'Braj',                   'script' => 'Deva', 'native' => '???????????? ????????????', 'regional' => ''],
        'mr'          => ['name' => 'Marathi',                'script' => 'Deva', 'native' => '???????????????', 'regional' => 'mr_IN'],
        'mai'         => ['name' => 'Maithili',               'script' => 'Tirh', 'native' => '??????????????????', 'regional' => 'mai_IN'],
        'raj'         => ['name' => 'Rajasthani',             'script' => 'Deva', 'native' => '???????????????????????????', 'regional' => ''],
        'sa'          => ['name' => 'Sanskrit',               'script' => 'Deva', 'native' => '???????????????????????????', 'regional' => 'sa_IN'],
        'hi'          => ['name' => 'Hindi',                  'script' => 'Deva', 'native' => '??????????????????', 'regional' => 'hi_IN'],
        'as'          => ['name' => 'Assamese',               'script' => 'Beng', 'native' => '?????????????????????', 'regional' => 'as_IN'],
        'bn'          => ['name' => 'Bengali',                'script' => 'Beng', 'native' => '???????????????', 'regional' => 'bn_BD'],
        'mni'         => ['name' => 'Manipuri',               'script' => 'Beng', 'native' => '????????????', 'regional' => 'mni_IN'],
        'pa'          => ['name' => 'Punjabi (Gurmukhi)',     'script' => 'Guru', 'native' => '??????????????????', 'regional' => 'pa_IN'],
        'gu'          => ['name' => 'Gujarati',               'script' => 'Gujr', 'native' => '?????????????????????', 'regional' => 'gu_IN'],
        'or'          => ['name' => 'Oriya',                  'script' => 'Orya', 'native' => '???????????????', 'regional' => 'or_IN'],
        'ta'          => ['name' => 'Tamil',                  'script' => 'Taml', 'native' => '???????????????', 'regional' => 'ta_IN'],
        'te'          => ['name' => 'Telugu',                 'script' => 'Telu', 'native' => '??????????????????', 'regional' => 'te_IN'],
        'kn'          => ['name' => 'Kannada',                'script' => 'Knda', 'native' => '???????????????', 'regional' => 'kn_IN'],
        'ml'          => ['name' => 'Malayalam',              'script' => 'Mlym', 'native' => '??????????????????', 'regional' => 'ml_IN'],
        'si'          => ['name' => 'Sinhala',                'script' => 'Sinh', 'native' => '???????????????', 'regional' => 'si_LK'],
        'th'          => ['name' => 'Thai',                   'script' => 'Thai', 'native' => '?????????', 'regional' => 'th_TH'],
        'lo'          => ['name' => 'Lao',                    'script' => 'Laoo', 'native' => '?????????', 'regional' => 'lo_LA'],
        'bo'          => ['name' => 'Tibetan',                'script' => 'Tibt', 'native' => '????????????????????????', 'regional' => 'bo_IN'],
        'dz'          => ['name' => 'Dzongkha',               'script' => 'Tibt', 'native' => '??????????????????', 'regional' => 'dz_BT'],
        'my'          => ['name' => 'Burmese',                'script' => 'Mymr', 'native' => '??????????????????????????????', 'regional' => 'my_MM'],
        'ka'          => ['name' => 'Georgian',               'script' => 'Geor', 'native' => '?????????????????????', 'regional' => 'ka_GE'],
        'byn'         => ['name' => 'Blin',                   'script' => 'Ethi', 'native' => '?????????', 'regional' => 'byn_ER'],
        'tig'         => ['name' => 'Tigre',                  'script' => 'Ethi', 'native' => '?????????', 'regional' => 'tig_ER'],
        'ti'          => ['name' => 'Tigrinya',               'script' => 'Ethi', 'native' => '????????????', 'regional' => 'ti_ET'],
        'am'          => ['name' => 'Amharic',                'script' => 'Ethi', 'native' => '????????????', 'regional' => 'am_ET'],
        'wal'         => ['name' => 'Wolaytta',               'script' => 'Ethi', 'native' => '???????????????', 'regional' => 'wal_ET'],
        'chr'         => ['name' => 'Cherokee',               'script' => 'Cher', 'native' => '?????????', 'regional' => ''],
        'iu'          => ['name' => 'Inuktitut (Canadian Aboriginal Syllabics)', 'script' => 'Cans', 'native' => '??????????????????', 'regional' => 'iu_CA'],
        'oj'          => ['name' => 'Ojibwa',                 'script' => 'Cans', 'native' => '????????????????????????', 'regional' => ''],
        'cr'          => ['name' => 'Cree',                   'script' => 'Cans', 'native' => '?????????????????????', 'regional' => ''],
        'km'          => ['name' => 'Khmer',                  'script' => 'Khmr', 'native' => '???????????????????????????', 'regional' => 'km_KH'],
        'mn-Mong'     => ['name' => 'Mongolian (Mongolian)',  'script' => 'Mong', 'native' => '????????????????????? ????????????', 'regional' => 'mn_MN'],
        'shi-Tfng'    => ['name' => 'Tachelhit (Tifinagh)',   'script' => 'Tfng', 'native' => '????????????????????????', 'regional' => ''],
        'tzm'         => ['name' => 'Central Atlas Tamazight (Tifinagh)','script' => 'Tfng', 'native' => '????????????????????????', 'regional' => ''],
        'yue'         => ['name' => 'Yue',                    'script' => 'Hant', 'native' => '?????????', 'regional' => 'yue_HK'],
        'ja'          => ['name' => 'Japanese',               'script' => 'Jpan', 'native' => '?????????', 'regional' => 'ja_JP'],
        'zh'          => ['name' => 'Chinese (Simplified)',   'script' => 'Hans', 'native' => '????????????', 'regional' => 'zh_CN'],
        'zh-Hant'     => ['name' => 'Chinese (Traditional)',  'script' => 'Hant', 'native' => '????????????', 'regional' => 'zh_CN'],
        'ii'          => ['name' => 'Sichuan Yi',             'script' => 'Yiii', 'native' => '?????????', 'regional' => ''],
        'vai'         => ['name' => 'Vai (Vai)',              'script' => 'Vaii', 'native' => '??????', 'regional' => ''],
        'jv-Java'     => ['name' => 'Javanese (Javanese)',    'script' => 'Java', 'native' => '????????????', 'regional' => ''],
        'ko'          => ['name' => 'Korean',                 'script' => 'Hang', 'native' => '?????????', 'regional' => 'ko_KR'],
    ];
}