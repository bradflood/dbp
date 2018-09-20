<?php

namespace App\Transformers\V2;

use App\Models\Bible\BibleFileset;
use App\Transformers\BaseTransformer;

class LibraryVolumeTransformer extends BaseTransformer
{

    public function transform(BibleFileset $fileset)
    {
	    switch($this->route) {

            case "v2_volume_history": {
                return [
                    "dam_id" => $fileset->v2_id,
                    "time"   => $fileset->updated_at->toDateTimeString(),
                    "event"  => "Updated"
                ];
                break;
            }

		    case "v2_library_volume": {
		    	$bible = $fileset->bible->first();
			    $bible_id = $fileset->bible->first()->id;
			    $language = $fileset->bible->first()->language;

			    $ver_title = @$bible->translatedTitles->where('language_id',$language->id)->first()->name;
			    $eng_title = @$bible->translatedTitles->where('language_id','eng')->first()->name;

			    $font_array = [
				    "id" => "12",
				    "name" => "Charis SIL",
				    "base_url" => "http://cloud.faithcomesbyhearing.com/fonts/Charis_SIL",
				    "files" => [
					    "zip" => "all.zip",
					    "ttf" => "font.ttf"
				    ],
				    "platforms" => [
					    "android" => true,
					    "ios" => true,
					    "web" => true
				    ],
				    "copyright" => "&copy; 2000-2013, SIL International  ",
				    "url" => "http://bit.ly/1uKBBMx"
			    ];

			    if (strpos($fileset->set_type_code, 'P') !== false) {
				    $collection_code = "AL";
			    } else {
				    $collection_code = (substr($fileset->id,6,1) == "O") ? "OT" : "NT";
			    }
			    /**
			     * @OA\Schema (
			     *	type="array",
			     *	schema="v2_library_volume",
			     *	description="",
			     *	title="v2_library_volume",
			     *	@OA\Xml(name="v2_library_volume"),
			     *	@OA\Items(
			     *      @OA\Property(property="dam_id",                  ref="#/components/schemas/BibleFileset/properties/id"),
			     *      @OA\Property(property="fcbh_id",                 ref="#/components/schemas/BibleFileset/properties/id"),
			     *      @OA\Property(property="volume_name",             ref="#/components/schemas/BibleTranslation/properties/name"),
			     *      @OA\Property(property="status",                  @OA\Schema(type="string",example="live")),
			     *      @OA\Property(property="dbp_agreement",           @OA\Schema(type="string",example="true")),
			     *      @OA\Property(property="expiration",              @OA\Schema(type="string",example="0000-00-00")),
			     *      @OA\Property(property="language_code",           ref="#/components/schemas/Language/properties/iso"),
			     *      @OA\Property(property="language_name",           ref="#/components/schemas/LanguageTranslation/properties/name"),
			     *      @OA\Property(property="language_english",        ref="#/components/schemas/Language/properties/name"),
			     *      @OA\Property(property="language_iso",            ref="#/components/schemas/Language/properties/iso"),
			     *      @OA\Property(property="language_iso_2B",         ref="#/components/schemas/Language/properties/iso2B"),
			     *      @OA\Property(property="language_iso_2T",         ref="#/components/schemas/Language/properties/iso2T"),
			     *      @OA\Property(property="language_iso_1",          ref="#/components/schemas/Language/properties/iso1"),
			     *      @OA\Property(property="language_iso_name",       ref="#/components/schemas/Language/properties/name"),
			     *      @OA\Property(property="language_family_code",    ref="#/components/schemas/Language/properties/iso"),
			     *      @OA\Property(property="language_family_name",    ref="#/components/schemas/LanguageTranslation/properties/name"),
			     *      @OA\Property(property="language_family_english", ref="#/components/schemas/Language/properties/name"),
			     *      @OA\Property(property="language_family_iso",     ref="#/components/schemas/Language/properties/iso"),
			     *      @OA\Property(property="language_family_iso_2B",  ref="#/components/schemas/Language/properties/iso2B"),
			     *      @OA\Property(property="language_family_iso_2T",  ref="#/components/schemas/Language/properties/iso2T"),
			     *      @OA\Property(property="language_family_iso_1",   ref="#/components/schemas/Language/properties/iso1"),
			     *      @OA\Property(property="version_code",            ref="#/components/schemas/BibleFileset/properties/id"),
			     *      @OA\Property(property="version_name",            ref="#/components/schemas/BibleTranslation/properties/name"),
			     *      @OA\Property(property="version_english",         ref="#/components/schemas/BibleTranslation/properties/name"),
			     *      @OA\Property(property="collection_code",         @OA\Schema(type="string", example="NT",enum={"OT", "NT"})),
			     *      @OA\Property(property="rich",                    @OA\Schema(type="integer",example=1,enum={1, 0})),
			     *      @OA\Property(property="collection_name",         @OA\Schema(type="string",example="New Testament",enum={"Old Testament", "New Testament"})),
			     *      @OA\Property(property="updated_on",              ref="#/components/schemas/BibleFileset/properties/updated_at"),
			     *      @OA\Property(property="created_on",              ref="#/components/schemas/BibleFileset/properties/created_at"),
			     *      @OA\Property(property="right_to_left",           @OA\Schema(type="string", example="rtl",enum={"rtl", "ltr"})),
			     *      @OA\Property(property="num_art",                 @OA\Schema(type="integer",example=0)),
			     *      @OA\Property(property="num_sample_audio",        @OA\Schema(type="integer",example=0)),
			     *      @OA\Property(property="sku",                     ref="#/components/schemas/BibleEquivalent/properties/equivalent_id"),
			     *      @OA\Property(property="audio_zip_path",          @OA\Schema(type="string")),
			     *      @OA\Property(property="font",                    ref="#/components/schemas/AlphabetFont"),
			     *      @OA\Property(property="arclight_language_id",    ref="#/components/schemas/LanguageCode/properties/code"),
			     *      @OA\Property(property="media",                   @OA\Schema(type="string",example="Audio",enum={"Audio", "Text"})),
			     *      @OA\Property(property="media_type",              @OA\Schema(type="string",example="Drama",enum={"Drama", "Non-Drama"})),
			     *      @OA\Property(property="delivery",                @OA\Schema(type="string")),
			     *      @OA\Property(property="resolution",              @OA\Schema(type="array"))
			     *     )
			     *   )
			     * )
			     */
			    return [
				    "dam_id"                    => (string) $fileset->generated_id,
				    "fcbh_id"                   => (string) $fileset->generated_id,
				    "volume_name"               => (string) $ver_title,
				    "status"                    => "live", // for the moment these default to Live
				    "dbp_agreement"             => "true", // for the moment these default to True
				    "expiration"                => "0000-00-00",
				    "language_code"             => (string) strtoupper($bible->iso),
				    "language_name"             => (string) $language->autonym ?? $language->name,
				    "language_english"          => (string) $language->name,
				    "language_iso"              => (string) $bible->iso,
				    "language_iso_2B"           => (string) $language->iso2B,
				    "language_iso_2T"           => (string) $language->iso2T,
				    "language_iso_1"            => (string) $language->iso1,
				    "language_iso_name"         => (string) $language->name,
				    "language_family_code"      => (string) ((@$language->parent) ? strtoupper(@$language->parent->iso) : strtoupper($language->iso)),
				    "language_family_name"      => (string) ((@$language->parent) ? @$language->parent->autonym : $language->name),
				    "language_family_english"   => (string) ((@$language->parent) ? @$language->parent->name : $language->name),
				    "language_family_iso"       => (string) $bible->iso,
				    "language_family_iso_2B"    => (string) ((@$language->parent) ? @$language->parent->iso2B : @$language->iso2B) ?? $language->iso2B,
				    "language_family_iso_2T"    => (string) ((@$language->parent) ? @$language->parent->iso2T : @$language->iso2T) ?? $language->iso2T,
				    "language_family_iso_1"     => (string) ((@$language->parent) ? @$language->parent->iso1 : @$language->iso1) ?? $language->iso1,
				    "version_code"              => (string) substr($fileset->id,3,3),
				    "version_name"              => (string) $ver_title ?? $eng_title,
				    "version_english"           => (string) $eng_title ?? $ver_title,
				    "collection_code"           => (string) $collection_code,
				    "rich"                      => (string) ($fileset->set_type_code == 'text_format') ? "1" : "0",
				    "collection_name"           => (string) ($collection_code == "NT") ? "New Testament" : "Old Testament",
				    "updated_on"                => (string) $fileset->updated_at->toDateTimeString(),
				    "created_on"                => (string) $fileset->created_at->toDateTimeString(),
				    "right_to_left"             => (isset($bible->alphabet)) ? (($bible->alphabet->direction == "rtl") ? "true" : "false") : "false",
				    "num_art"                   => "0",
				    "num_sample_audio"          => "0",
				    "sku"                       => "",
				    "audio_zip_path"            => "",
				    "font"                      => (@$bible->alphabet->requires_font) ? $font_array : null,
				    "arclight_language_id"      => "",
				    "media"                     => (strpos($fileset->set_type_code, 'audio') !== false) ? 'Audio' : 'Text',
				    "media_type"                => ($fileset->set_type_code == 'audio_drama') ? "Drama" : "Non-Drama",
				    "delivery"                  => [
					    "mobile",
					    "web",
					    "local_bundled",
					    "subsplash"
				    ],
				    "resolution"                => []
			    ];
			    break;
		    }
		    default: return [];
	    }
    }
}
