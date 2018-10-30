<?php

namespace App\Transformers;

use App\Models\Bible\Bible;
class BibleTransformer extends BaseTransformer
{

	/**
	 * A Fractal transformer.
	 *
	 * @param Bible $bible
	 *
	 * @return array
	 */
    public function transform($bible)
    {
	    switch ((int) $this->version) {
		    case 2:  return $this->transformForV2($bible);
		    case 3:  return $this->transformForV2($bible);
		    case 4:  return $this->transformForV4($bible);
		    default: return $this->transformForV4($bible);
	    }
    }

    public function transformForV2($bible)
    {

    	// Compute v2 ID
	    if(isset($bible->bible)) {
	    	$iso = $bible->bible->first()->language->iso;
		    $v2id = $iso.substr($bible->first()->id,3,3);
	    } elseif(isset($bible->id)) {
		    $iso = $bible->language->iso ?? null;
		    $v2id = $iso.substr($bible->id,3,3);
	    }

    	    switch($this->route) {
		        case 'v2_library_volume': {
				        return [
					        'dam_id'                    => $bible->id,
					        'fcbh_id'                   => $bible->id,
					        'volume_name'               => @$bible->currentTranslation->name ?? '',
					        'status'                    => 'live', // for the moment these default to Live
					        'dbp_agreement'             => 'true', // for the moment these default to True
					        'expiration'                => '0000-00-00',
					        'language_code'             => strtoupper($bible->iso) ?? '',
					        'language_name'             => @$bible->language->autonym ?? @$bible->language->name,
					        'language_english'          => @$bible->language->name ?? '',
					        'language_iso'              => $bible->iso ?? '',
					        'language_iso_2B'           => @$bible->language->iso2B ?? '',
					        'language_iso_2T'           => @$bible->language->iso2T ?? '',
					        'language_iso_1'            => @$bible->language->iso1 ?? '',
					        'language_iso_name'         => @$bible->language->name ?? '',
					        'language_family_code'      => ((@$bible->language->parent) ? strtoupper(@$bible->language->parent->iso) : strtoupper($bible->iso)) ?? '',
					        'language_family_name'      => ((@$bible->language->parent) ? @$bible->language->parent->autonym : @$bible->language->name) ?? '',
					        'language_family_english'   => ((@$bible->language->parent) ? @$bible->language->parent->name : @$bible->language->name) ?? '',
					        'language_family_iso'       => $iso ?? null,
					        'language_family_iso_2B'    => ((@$bible->language->parent) ? @$bible->language->parent->iso2B : @$bible->language->iso2B) ?? '',
					        'language_family_iso_2T'    => ((@$bible->language->parent) ? @$bible->language->parent->iso2T : @$bible->language->iso2T) ?? '',
					        'language_family_iso_1'     => ((@$bible->language->parent) ? @$bible->language->parent->iso1 : @$bible->language->iso1) ?? '',
					        'version_code'              => substr($bible->id,3) ?? '',
					        'version_name'              => 'Wycliffe Bible Translators, Inc.',
					        'version_english'           => @$bible->currentTranslation->name ?? $bible->id,
					        'collection_code'           => ($bible->name == 'Old Testament') ? 'OT' : 'NT',
					        'rich'                      => '0',
					        'collection_name'           => $bible->name,
					        'updated_on'                => (string) $bible->updated_at,
					        'created_on'                => (string) $bible->created_at,
					        'right_to_left'             => isset($bible->alphabet) ? (($bible->alphabet->direction == "rtl") ? "true" : "false") : "false",
					        'num_art'                   => '0',
					        'num_sample_audio'          => '0',
					        'sku'                       => '',
					        'audio_zip_path'            => '',
					        'font'                      => null,
					        'arclight_language_id'      => '',
					        'media'                     => (strpos($bible->set_type_code, 'audio') !== false) ? 'Audio' : 'Text',
					        'media_type'                => 'Drama',
					        'delivery'                  => [
					        	'mobile',
						        'web',
						        'local_bundled',
						        'subsplash'
					        ],
					        'resolution'                => []
				        ];
				        break;
		        }
		        default: return [];
	        }


    }

	public function transformForV4($bible)
	{

		switch($this->route) {

			/**
			 * @OA\Schema (
			*	type="array",
			*	schema="v4_bible.all",
			*	description="The bibles being returned",
			*	title="v4_bible.all",
			*	@OA\Xml(name="v4_bible.all"),
			*	@OA\Items(
			 *              @OA\Property(property="abbr",              ref="#/components/schemas/Bible/properties/id"),
			 *              @OA\Property(property="name",              ref="#/components/schemas/BibleTranslation/properties/name"),
			 *              @OA\Property(property="vname",             ref="#/components/schemas/BibleTranslation/properties/name"),
			 *              @OA\Property(property="language",          ref="#/components/schemas/Language/properties/name"),
			 *              @OA\Property(property="language_autonym",  ref="#/components/schemas/LanguageTranslation/properties/name"),
			 *              @OA\Property(property="language_altNames", ref="#/components/schemas/LanguageTranslation/properties/name"),
			 *              @OA\Property(property="iso",               ref="#/components/schemas/Language/properties/iso"),
			 *              @OA\Property(property="date",              ref="#/components/schemas/Bible/properties/date"),
			 *              @OA\Property(property="filesets",          ref="#/components/schemas/BibleFileset")
			 *     )
			 *   )
			 * )
			 */
            case 'v4_bible.archival': {

                $name = $bible->translatedTitles->where('language_id',$bible->english_language_id)->first();
	            $vName = ($bible->iso != 'eng') ? $bible->translatedTitles->where('language_id',$bible->language_id)->first() : false;
                $output = [
                    'abbr'              => $bible->id,
                    'script'            => $bible->script,
                    'name'              => $name ? $name->name : '',
                    'vname'             => $vName ? $vName->name : '',
                    'language'          => @$bible->language->name ?? '',
                    'autonym'           => @$bible->language->autonym ?? '',
                    'iso'               => @$bible->language->iso ?? '',
                    'date'              => $bible->date,
	                'links_count'       => $bible->links_count + $bible->filesets->count(),
                    'organizations'     => '',
                    'types'             => $bible->filesets->pluck('set_type_code')->unique()->implode(',')
                ];
                if($bible->langauge) if($bible->langauge->relationLoaded('translations')) $output['language_altNames'] = $bible->language->translations->pluck('name');
                if($bible->relationLoaded('filesets')) {
                    $output_organizations = [];
                    foreach($bible->filesets as $fileset) {
                        if($fileset->relationLoaded('copyrightOrganization')) {
                            $output_organizations[] = $fileset->copyrightOrganization->pluck('organization_id')->implode(',');
                        }
                    }
                    $output_organizations = array_flatten(array_unique($output_organizations));
                    $output['organizations'] = $output_organizations;
                }
                if($bible->relationLoaded('country')) {
                    $output['country_id']   = '';
	                $output['country_name'] = '';
	                $output['continent_id'] = '';
                    if(isset($bible->country[0])) {

	                    $output['country_name'] = $bible->country[0]->name;
                        $output['country_id']   = $bible->country[0]->country_id;
                        $output['continent_id'] = $bible->country[0]->continent;
                    }
                }
                return $output;
            }
			case 'v4_bible.all': {
				$output = [
					'abbr'              => $bible->id,
					'name'              => @$bible->translations->where('language_id',$GLOBALS['i18n_id'])->first()->name,
					'vname'             => @$bible->translations->where('language_id',$bible->language_id)->first()->name,
					'language'          => $bible->language->name ?? null,
					'autonym'           => $bible->language->autonym ?? null,
					'language_id'       => $bible->language->id,
					'iso'               => $bible->language->iso ?? null,
					'date'              => $bible->date,
					'filesets'          => $bible->filesets->mapToGroups(function ($item, $key) {
						return [$item['asset_id'] => ['id' => $item['id'],'type' => $item->set_type_code, 'size' => $item->set_size_code]];
					})
				];
				if($bible->langauge && $bible->langauge->relationLoaded('translations')) $output['language_altNames'] = $bible->language->translations->pluck('name');

				if($bible->relationLoaded('country')) {
					$output['country_id']   = '';
					$output['continent_id'] = '';
					if(isset($bible->country[0])) {
						$output['country_id'] = $bible->country[0]->id;
						$output['continent_id'] = $bible->country[0]->continent;
					}
				}
				return $output;
			}

			/**
			 * @OA\Schema (
			*	type="array",
			*	schema="v4_bible.one",
			*	description="The bible being returned",
			*	title="v4_bible.one",
			*	@OA\Xml(name="v4_bible.one"),
			*	@OA\Items(
			 *              @OA\Property(property="abbr",          ref="#/components/schemas/Bible/properties/id"),
			 *              @OA\Property(property="alphabet",      ref="#/components/schemas/Alphabet/properties/script"),
			 *              @OA\Property(property="mark",          ref="#/components/schemas/Bible/properties/copyright"),
			 *              @OA\Property(property="name",          ref="#/components/schemas/BibleTranslation/properties/name"),
			 *              @OA\Property(property="description",   ref="#/components/schemas/BibleTranslation/properties/description"),
			 *              @OA\Property(property="vname",         ref="#/components/schemas/BibleTranslation/properties/name"),
			 *              @OA\Property(property="vdescription",  ref="#/components/schemas/BibleTranslation/properties/description"),
			 *              @OA\Property(property="publishers",    ref="#/components/schemas/Organization"),
			 *              @OA\Property(property="providers",     ref="#/components/schemas/Organization"),
			 *              @OA\Property(property="language",      ref="#/components/schemas/Language/properties/name"),
			 *              @OA\Property(property="iso",           ref="#/components/schemas/Language/properties/iso"),
			 *              @OA\Property(property="date",          ref="#/components/schemas/Bible/properties/date"),
			 *              @OA\Property(property="country",       ref="#/components/schemas/Country/properties/name"),
			 *              @OA\Property(property="books",         ref="#/components/schemas/Book/properties/id"),
			 *              @OA\Property(property="links",         ref="#/components/schemas/BibleLink"),
			 *              @OA\Property(property="filesets",      ref="#/components/schemas/BibleFileset"),
			 *     )
			 *   )
			 * )
			 */
			case 'v4_bible.one': {
				return [
					'abbr'          => $bible->id,
					'alphabet'      => $bible->alphabet,
					'mark'          => $bible->copyright,
					'name'          => @$bible->currentTranslation->name ?? '',
					'description'   => @$bible->currentTranslation->description ?? '',
					'vname'         => @$bible->vernacularTranslation->name ?? '',
					'vdescription'  => @$bible->vernacularTranslation->description ?? '',
					'publishers'    => $bible->organizations->where('pivot.relationship_type','publisher')->all(),
					'providers'     => $bible->organizations->where('pivot.relationship_type','provider')->all(),
					'equivalents'   => $bible->equivalents,
					'language'      => @$bible->language->name ?? '',
					'language_id'   => $bible->language->id,
					'iso'           => $bible->language->iso ?? null,
					'date'          => $bible->date,
					'country'       => $bible->language->primaryCountry->name ?? '',
					'books'         => $bible->books->sortBy('book.'.$bible->versification.'_order')->each(function ($book) {
						// convert to integer array
						$chapters = explode(',',$book->chapters);
						foreach ($chapters as $key => $chapter) $chapters[$key] = intval($chapter);
						$book->chapters = $chapters;
						unset($book->book);
						return $book;
					})->values(),
					'links'        => $bible->links,
					'filesets'     => $bible->filesets->mapToGroups(function ($item, $key) {
						return [$item['asset_id'] => ['id' => $item['id'],'type' => $item->set_type_code, 'size' => $item->set_size_code]];
					})
				];
			}

			default: return [];
		}
	}

}
