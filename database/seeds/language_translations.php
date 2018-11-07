<?php

use Illuminate\Database\Seeder;
use database\seeds\SeederHelper;
use App\Models\Language\Language;
use App\Models\Language\LanguageTranslation;

class language_translations extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$seederHelper = new SeederHelper();
		$languages = $seederHelper->csv_to_array(storage_path().'/data/languages/language_translations.csv');
		foreach($languages as $language) {
			$languageExists = Language::where('iso',$language['iso_language'])->first();
			$referenceLanguageExists = Language::where('iso',$language['iso_translation'])->first();
			if($languageExists && $referenceLanguageExists) {
				$translationExists = LanguageTranslation::where(['name' => $language['name'],'language_source_id' => $languageExists->id, 'language_translation_id' => $referenceLanguageExists->id])->first();
				if($translationExists) { continue; }
				LanguageTranslation::create([
					'name'                     => $language['name'],
					'language_source_id'       => $languageExists->id,
					'language_translation_id'  => $referenceLanguageExists->id
				]);

			}
		}

	}

}
