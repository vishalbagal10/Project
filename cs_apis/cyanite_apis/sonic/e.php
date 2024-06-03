<?php
try{
							$dbcon = include('../config.php');
							$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

							$sql_paras = "SELECT 
								tbl_cyanite.cy_energylevel,
								tbl_cyanite.cy_emotionalprofile,
								json_extract(tbl_cyanite.cy_bpmprediction, '$.value') AS cy_bpm,
								json_extract(tbl_cyanite.cy_keyprediction, '$.value') AS cy_key,
								tbl_cyanite.cy_timesignature,

								json_extract(tbl_cyanite.cy_mood, '$.aggressive') AS mood_aggressive,
								json_extract(tbl_cyanite.cy_mood, '$.calm') AS mood_calm,
								json_extract(tbl_cyanite.cy_mood, '$.chilled') AS mood_chilled,
								json_extract(tbl_cyanite.cy_mood, '$.dark') AS mood_dark,
								json_extract(tbl_cyanite.cy_mood, '$.energetic') AS mood_energetic,
								json_extract(tbl_cyanite.cy_mood, '$.epic') AS mood_epic,
								json_extract(tbl_cyanite.cy_mood, '$.happy') AS mood_happy,
								json_extract(tbl_cyanite.cy_mood, '$.romantic') AS mood_romantic,
								json_extract(tbl_cyanite.cy_mood, '$.sad') AS mood_sad,
								json_extract(tbl_cyanite.cy_mood, '$.scary') AS mood_scary,
								json_extract(tbl_cyanite.cy_mood, '$.sexy') AS mood_sexy,
								json_extract(tbl_cyanite.cy_mood, '$.ethereal') AS mood_ethereal,
								json_extract(tbl_cyanite.cy_mood, '$.uplifting') AS mood_uplifting,

								json_extract(tbl_cyanite.cy_moodAdvanced, '$.anxious') AS mooda_anxious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.barren') AS mooda_barren,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.cold') AS mooda_cold,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.creepy') AS mooda_creepy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dark') AS mooda_dark,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.disturbing') AS mooda_disturbing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.eerie') AS mooda_eerie,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.evil') AS mooda_evil,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.fearful') AS mooda_fearful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.mysterious') AS mooda_mysterious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.nervous') AS mooda_nervous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.restless') AS mooda_restless,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.spooky') AS mooda_spooky,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.strange') AS mooda_strange,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.supernatural') AS mooda_supernatural,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.suspenseful') AS mooda_suspenseful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.tense') AS mooda_tense,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.weird') AS mooda_weird,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.aggressive') AS mooda_aggressive,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.agitated') AS mooda_agitated,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.angry') AS mooda_angry,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dangerous') AS mooda_dangerous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.fiery') AS mooda_fiery,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.intense') AS mooda_intense,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.passionate') AS mooda_passionate,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.ponderous') AS mooda_ponderous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.violent') AS mooda_violent,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.comedic') AS mooda_comedic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.eccentric') AS mooda_eccentric,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.funny') AS mooda_funny,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.mischievous') AS mooda_mischievous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.quirky') AS mooda_quirky,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.whimsical') AS mooda_whimsical,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.boisterous') AS mooda_boisterous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.boingy') AS mooda_boingy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.bright') AS mooda_bright,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.celebratory') AS mooda_celebratory,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.cheerful') AS mooda_cheerful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.excited') AS mooda_excited,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.feelGood') AS mooda_feelGood,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.fun') AS mooda_fun,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.happy') AS mooda_happy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.joyous') AS mooda_joyous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.lighthearted') AS mooda_lighthearted,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.perky') AS mooda_perky,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.playful') AS mooda_playful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.rollicking') AS mooda_rollicking,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.upbeat') AS mooda_upbeat,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.calm') AS mooda_calm,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.contented') AS mooda_contented,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dreamy') AS mooda_dreamy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.introspective') AS mooda_introspective,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.laidBack') AS mooda_laidBack,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.leisurely') AS mooda_leisurely,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.lyrical') AS mooda_lyrical,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.peaceful') AS mooda_peaceful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.quiet') AS mooda_quiet,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.relaxed') AS mooda_relaxed,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.serene') AS mooda_serene,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.soothing') AS mooda_soothing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.spiritual') AS mooda_spiritual,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.tranquil') AS mooda_tranquil,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.bittersweet') AS mooda_bittersweet,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.blue') AS mooda_blue,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.depressing') AS mooda_depressing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.gloomy') AS mooda_gloomy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.lonely') AS mooda_lonely,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.melancholic') AS mooda_melancholic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.mournful') AS mooda_mournful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.poignant') AS mooda_poignant,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sad') AS mooda_sad,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.frightening') AS mooda_frightening,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.menacing') AS mooda_menacing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.nightmarish') AS mooda_nightmarish,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.ominous') AS mooda_ominous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.panicStricken') AS mooda_panicStricken,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.scary') AS mooda_scary,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.concerned') AS mooda_concerned,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.determined') AS mooda_determined,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dignified') AS mooda_dignified,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.emotional') AS mooda_emotional,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.noble') AS mooda_noble,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.serious') AS mooda_serious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.solemn') AS mooda_solemn,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.thoughtful') AS mooda_thoughtful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.cool') AS mooda_cool,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.seductive') AS mooda_seductive,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sexy') AS mooda_sexy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.adventurous') AS mooda_adventurous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.confident') AS mooda_confident,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.courageous') AS mooda_courageous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.resolute') AS mooda_resolute,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.energetic') AS mooda_energetic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.epic') AS mooda_epic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.exciting') AS mooda_exciting,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.exhilarating') AS mooda_exhilarating,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.heroic') AS mooda_heroic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.majestic') AS mooda_majestic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.powerful') AS mooda_powerful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.prestigious') AS mooda_prestigious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.relentless') AS mooda_relentless,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.strong') AS mooda_strong,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.triumphant') AS mooda_triumphant,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.victorious') AS mooda_victorious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.delicate') AS mooda_delicate,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.graceful') AS mooda_graceful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.hopeful') AS mooda_hopeful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.innocent') AS mooda_innocent,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.intimate') AS mooda_intimate,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.kind') AS mooda_kind,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.light') AS mooda_light,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.loving') AS mooda_loving,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.nostalgic') AS mooda_nostalgic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.reflective') AS mooda_reflective,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.romantic') AS mooda_romantic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sentimental') AS mooda_sentimental,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.soft') AS mooda_soft,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sweet') AS mooda_sweet,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.tender') AS mooda_tender,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.warm') AS mooda_warm,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.anthemic') AS mooda_anthemic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.aweInspiring') AS mooda_aweInspiring,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.euphoric') AS mooda_euphoric,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.inspirational') AS mooda_inspirational,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.motivational') AS mooda_motivational,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.optimistic') AS mooda_optimistic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.positive') AS mooda_positive,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.proud') AS mooda_proud,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.soaring') AS mooda_soaring,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.uplifting') AS mooda_uplifting,


								json_extract(tbl_cyanite.cy_genre, '$.ambient') AS genre_ambient,
								json_extract(tbl_cyanite.cy_genre, '$.blues') AS genre_blues,
								json_extract(tbl_cyanite.cy_genre, '$.classical') AS genre_classical,
								-- json_extract(tbl_cyanite.cy_genre, '$.country') AS country,
								json_extract(tbl_cyanite.cy_genre, '$.electronicDance') AS genre_electronicDance,
								json_extract(tbl_cyanite.cy_genre, '$.folkCountry') AS genre_folkCountry,
								json_extract(tbl_cyanite.cy_genre, '$.funkSoul') AS genre_funkSoul,
								-- json_extract(tbl_cyanite.cy_genre, '$.indieAlternative') AS indieAlternative,
								json_extract(tbl_cyanite.cy_genre, '$.jazz') AS genre_jazz,
								json_extract(tbl_cyanite.cy_genre, '$.latin') AS genre_latin,
								json_extract(tbl_cyanite.cy_genre, '$.metal') AS genre_metal,
								json_extract(tbl_cyanite.cy_genre, '$.pop') AS genre_pop,
								json_extract(tbl_cyanite.cy_genre, '$.punk') AS genre_punk,
								json_extract(tbl_cyanite.cy_genre, '$.rapHipHop') AS genre_rapHipHop,
								json_extract(tbl_cyanite.cy_genre, '$.reggae') AS genre_reggae,
								json_extract(tbl_cyanite.cy_genre, '$.rnb') AS genre_rnb,
								json_extract(tbl_cyanite.cy_genre, '$.rock') AS genre_rock,
								json_extract(tbl_cyanite.cy_genre, '$.singerSongwriter') AS genre_singerSongwriter,

								json_extract(tbl_cyanite.cy_character, '$.bold') AS character_bold,
								json_extract(tbl_cyanite.cy_character, '$.cool') AS character_cool,
								json_extract(tbl_cyanite.cy_character, '$.epic') AS character_epic,
								json_extract(tbl_cyanite.cy_character, '$.ethereal') AS character_ethereal,
								json_extract(tbl_cyanite.cy_character, '$.heroic') AS character_heroic,
								json_extract(tbl_cyanite.cy_character, '$.luxurious') AS character_luxurious,
								json_extract(tbl_cyanite.cy_character, '$.magical') AS character_magical,
								json_extract(tbl_cyanite.cy_character, '$.mysterious') AS character_mysterious,
								json_extract(tbl_cyanite.cy_character, '$.playful') AS character_playful,
								json_extract(tbl_cyanite.cy_character, '$.powerful') AS character_powerful,
								json_extract(tbl_cyanite.cy_character, '$.retro') AS character_retro,
								json_extract(tbl_cyanite.cy_character, '$.sophisticated') AS character_sophisticated,
								json_extract(tbl_cyanite.cy_character, '$.sparkling') AS character_sparkling,
								json_extract(tbl_cyanite.cy_character, '$.sparse') AS character_sparse,
								json_extract(tbl_cyanite.cy_character, '$.unpolished') AS character_unpolished,
								json_extract(tbl_cyanite.cy_character, '$.warm') AS character_warm,

								json_extract(tbl_cyanite.cy_movement, '$.bouncy') AS movement_bouncy,
								json_extract(tbl_cyanite.cy_movement, '$.driving') AS movement_driving,
								json_extract(tbl_cyanite.cy_movement, '$.flowing') AS movement_flowing,
								json_extract(tbl_cyanite.cy_movement, '$.groovy') AS movement_groovy,
								json_extract(tbl_cyanite.cy_movement, '$.nonrhythmic') AS movement_nonrhythmic,
								json_extract(tbl_cyanite.cy_movement, '$.pulsing') AS movement_pulsing,
								json_extract(tbl_cyanite.cy_movement, '$.robotic') AS movement_robotic,
								json_extract(tbl_cyanite.cy_movement, '$.running') AS movement_running,
								json_extract(tbl_cyanite.cy_movement, '$.steady') AS movement_steady,
								json_extract(tbl_cyanite.cy_movement, '$.stomping') AS movement_stomping,

								json_extract(tbl_cyanite.cy_subgenre, '$.bluesRock') AS subgenre_bluesRock,
								json_extract(tbl_cyanite.cy_subgenre, '$.folkRock') AS subgenre_folkRock,
								json_extract(tbl_cyanite.cy_subgenre, '$.hardRock') AS subgenre_hardRock,
								json_extract(tbl_cyanite.cy_subgenre, '$.indieAlternative') AS subgenre_indieAlternative,
								json_extract(tbl_cyanite.cy_subgenre, '$.psychedelicProgressiveRock') AS subgenre_psychedelicProgressiveRock,
								json_extract(tbl_cyanite.cy_subgenre, '$.punk') AS subgenre_punk,
								json_extract(tbl_cyanite.cy_subgenre, '$.rockAndRoll') AS subgenre_rockAndRoll,
								json_extract(tbl_cyanite.cy_subgenre, '$.popSoftRock') AS subgenre_popSoftRock,
								json_extract(tbl_cyanite.cy_subgenre, '$.abstractIDMLeftfield') AS subgenre_abstractIDMLeftfield,
								json_extract(tbl_cyanite.cy_subgenre, '$.breakbeatDnB') AS subgenre_breakbeatDnB,
								json_extract(tbl_cyanite.cy_subgenre, '$.deepHouse') AS subgenre_deepHouse,
								json_extract(tbl_cyanite.cy_subgenre, '$.electro') AS subgenre_electro,
								json_extract(tbl_cyanite.cy_subgenre, '$.house') AS subgenre_house,
								json_extract(tbl_cyanite.cy_subgenre, '$.minimal') AS subgenre_minimal,
								json_extract(tbl_cyanite.cy_subgenre, '$.synthPop') AS subgenre_synthPop,
								json_extract(tbl_cyanite.cy_subgenre, '$.techHouse') AS subgenre_techHouse,
								json_extract(tbl_cyanite.cy_subgenre, '$.techno') AS subgenre_techno,
								json_extract(tbl_cyanite.cy_subgenre, '$.trance') AS subgenre_trance,
								json_extract(tbl_cyanite.cy_subgenre, '$.contemporaryRnB') AS subgenre_contemporaryRnB,
								json_extract(tbl_cyanite.cy_subgenre, '$.gangsta') AS subgenre_gangsta,
								json_extract(tbl_cyanite.cy_subgenre, '$.jazzyHipHop') AS subgenre_jazzyHipHop,
								json_extract(tbl_cyanite.cy_subgenre, '$.popRap') AS subgenre_popRap,
								json_extract(tbl_cyanite.cy_subgenre, '$.trap') AS subgenre_trap,
								json_extract(tbl_cyanite.cy_subgenre, '$.blackMetal') AS subgenre_blackMetal,
								json_extract(tbl_cyanite.cy_subgenre, '$.deathMetal') AS subgenre_deathMetal,
								json_extract(tbl_cyanite.cy_subgenre, '$.doomMetal') AS subgenre_doomMetal,
								json_extract(tbl_cyanite.cy_subgenre, '$.heavyMetal') AS subgenre_heavyMetal,
								json_extract(tbl_cyanite.cy_subgenre, '$.metalcore') AS subgenre_metalcore,
								json_extract(tbl_cyanite.cy_subgenre, '$.nuMetal') AS subgenre_nuMetal,
								json_extract(tbl_cyanite.cy_subgenre, '$.disco') AS subgenre_disco,
								json_extract(tbl_cyanite.cy_subgenre, '$.funk') AS subgenre_funk,
								json_extract(tbl_cyanite.cy_subgenre, '$.gospel') AS subgenre_gospel,
								json_extract(tbl_cyanite.cy_subgenre, '$.neoSoul') AS subgenre_neoSoul,
								json_extract(tbl_cyanite.cy_subgenre, '$.soul') AS subgenre_soul,
								json_extract(tbl_cyanite.cy_subgenre, '$.bigBandSwing') AS subgenre_bigBandSwing,
								json_extract(tbl_cyanite.cy_subgenre, '$.bebop') AS subgenre_bebop,
								json_extract(tbl_cyanite.cy_subgenre, '$.contemporaryJazz') AS subgenre_contemporaryJazz,
								json_extract(tbl_cyanite.cy_subgenre, '$.easyListening') AS subgenre_easyListening,
								json_extract(tbl_cyanite.cy_subgenre, '$.fusion') AS subgenre_fusion,
								json_extract(tbl_cyanite.cy_subgenre, '$.latinJazz') AS subgenre_latinJazz,
								json_extract(tbl_cyanite.cy_subgenre, '$.smoothJazz') AS subgenre_smoothJazz,
								json_extract(tbl_cyanite.cy_subgenre, '$.country') AS subgenre_country,
								json_extract(tbl_cyanite.cy_subgenre, '$.folk') AS subgenre_folk,

								json_extract(tbl_cyanite.cy_segments, '$.timestamps') AS stimestamps,
								json_extract(tbl_cyanite.cy_segments, '$.mood.aggressive') AS smood_aggressive,
								json_extract(tbl_cyanite.cy_segments, '$.mood.calm') AS smood_calm,
								json_extract(tbl_cyanite.cy_segments, '$.mood.chilled') AS smood_chilled,
								json_extract(tbl_cyanite.cy_segments, '$.mood.dark') AS smood_dark,
								json_extract(tbl_cyanite.cy_segments, '$.mood.energetic') AS smood_energetic,
								json_extract(tbl_cyanite.cy_segments, '$.mood.epic') AS smood_epic,
								json_extract(tbl_cyanite.cy_segments, '$.mood.happy') AS smood_happy,
								json_extract(tbl_cyanite.cy_segments, '$.mood.romantic') AS smood_romantic,
								json_extract(tbl_cyanite.cy_segments, '$.mood.sad') AS smood_sad,
								json_extract(tbl_cyanite.cy_segments, '$.mood.scary') AS smood_scary,
								json_extract(tbl_cyanite.cy_segments, '$.mood.sexy') AS smood_sexy,
								json_extract(tbl_cyanite.cy_segments, '$.mood.ethereal') AS smood_ethereal,
								json_extract(tbl_cyanite.cy_segments, '$.mood.uplifting') AS smood_uplifting,

								json_extract(tbl_cyanite.cy_segments, '$.genre.ambient') AS sgenre_ambient,
								json_extract(tbl_cyanite.cy_segments, '$.genre.blues') AS sgenre_blues,
								json_extract(tbl_cyanite.cy_segments, '$.genre.classical') AS sgenre_classical,
								json_extract(tbl_cyanite.cy_segments, '$.genre.electronicDance') AS sgenre_electronicDance,
								json_extract(tbl_cyanite.cy_segments, '$.genre.folkCountry') AS sgenre_folkCountry,
								json_extract(tbl_cyanite.cy_segments, '$.genre.funkSoul') AS sgenre_funkSoul,
								json_extract(tbl_cyanite.cy_segments, '$.genre.jazz') AS sgenre_jazz,
								json_extract(tbl_cyanite.cy_segments, '$.genre.latin') AS sgenre_latin,
								json_extract(tbl_cyanite.cy_segments, '$.genre.metal') AS sgenre_metal,
								json_extract(tbl_cyanite.cy_segments, '$.genre.pop') AS sgenre_pop,
								-- json_extract(tbl_cyanite.cy_genre, '$.punk') AS genre_punk,
								json_extract(tbl_cyanite.cy_segments, '$.genre.rapHipHop') AS sgenre_rapHipHop,
								json_extract(tbl_cyanite.cy_segments, '$.genre.reggae') AS sgenre_reggae,
								json_extract(tbl_cyanite.cy_segments, '$.genre.rnb') AS sgenre_rnb,
								json_extract(tbl_cyanite.cy_segments, '$.genre.rock') AS sgenre_rock,
								json_extract(tbl_cyanite.cy_segments, '$.genre.singerSongwriter') AS sgenre_singerSongwriter,

								json_extract(tbl_cyanite.cy_segments, '$.character.bold') AS bold,
								json_extract(tbl_cyanite.cy_segments, '$.character.cool') AS cool,
								json_extract(tbl_cyanite.cy_segments, '$.character.epic') AS epic,
								json_extract(tbl_cyanite.cy_segments, '$.character.ethereal') AS ethereal,
								json_extract(tbl_cyanite.cy_segments, '$.character.heroic') AS heroic,
								json_extract(tbl_cyanite.cy_segments, '$.character.luxurious') AS luxurious,
								json_extract(tbl_cyanite.cy_segments, '$.character.magical') AS magical,
								json_extract(tbl_cyanite.cy_segments, '$.character.mysterious') AS mysterious,
								json_extract(tbl_cyanite.cy_segments, '$.character.playful') AS playful,
								json_extract(tbl_cyanite.cy_segments, '$.character.powerful') AS powerful,
								json_extract(tbl_cyanite.cy_segments, '$.character.retro') AS retro,
								json_extract(tbl_cyanite.cy_segments, '$.character.sophisticated') AS sophisticated,
								json_extract(tbl_cyanite.cy_segments, '$.character.sparkling') AS sparkling,
								json_extract(tbl_cyanite.cy_segments, '$.character.sparse') AS sparse,
								json_extract(tbl_cyanite.cy_segments, '$.character.unpolished') AS unpolished,
								json_extract(tbl_cyanite.cy_segments, '$.character.warm') AS warm,

								json_extract(tbl_cyanite.cy_segments, '$.genre.ambient') AS ambient,
								json_extract(tbl_cyanite.cy_segments, '$.genre.blues') AS blues,
								json_extract(tbl_cyanite.cy_segments, '$.genre.classical') AS classical,
								json_extract(tbl_cyanite.cy_segments, '$.genre.electronicDance') AS electronicDance,
								json_extract(tbl_cyanite.cy_segments, '$.genre.folkCountry') AS folkCountry,
								json_extract(tbl_cyanite.cy_segments, '$.genre.funkSoul') AS funkSoul,
								json_extract(tbl_cyanite.cy_segments, '$.genre.jazz') AS jazz,
								json_extract(tbl_cyanite.cy_segments, '$.genre.latin') AS latin,
								json_extract(tbl_cyanite.cy_segments, '$.genre.metal') AS metal,
								json_extract(tbl_cyanite.cy_segments, '$.genre.pop') AS pop,
								-- json_extract(tbl_cyanite.cy_genre, '$.punk') AS genre_punk,
								json_extract(tbl_cyanite.cy_segments, '$.genre.rapHipHop') AS rapHipHop,
								json_extract(tbl_cyanite.cy_segments, '$.genre.reggae') AS reggae,
								json_extract(tbl_cyanite.cy_segments, '$.genre.rnb') AS rnb,
								json_extract(tbl_cyanite.cy_segments, '$.genre.rock') AS rock,
								json_extract(tbl_cyanite.cy_segments, '$.genre.singerSongwriter') AS singerSongwriter,

								json_extract(tbl_cyanite.cy_segments, '$.subgenre.bluesRock') AS bluesRock,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.folkRock') AS folkRock,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.hardRock') AS hardRock,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.indieAlternative') AS indieAlternative,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.psychedelicProgressiveRock') AS psychedelicProgressiveRock,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.punk') AS punk,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.rockAndRoll') AS rockAndRoll,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.popSoftRock') AS popSoftRock,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.abstractIDMLeftfield') AS abstractIDMLeftfield,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.breakbeatDnB') AS breakbeatDnB,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.deepHouse') AS deepHouse,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.electro') AS electro,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.house') AS house,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.minimal') AS minimal,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.synthPop') AS synthPop,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.techHouse') AS techHouse,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.techno') AS techno,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.trance') AS trance,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.contemporaryRnB') AS contemporaryRnB,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.gangsta') AS gangsta,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.jazzyHipHop') AS jazzyHipHop,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.popRap') AS popRap,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.trap') AS trap,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.blackMetal') AS blackMetal,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.deathMetal') AS deathMetal,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.doomMetal') AS doomMetal,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.heavyMetal') AS heavyMetal,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.metalcore') AS metalcore,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.nuMetal') AS nuMetal,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.disco') AS disco,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.funk') AS funk,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.gospel') AS gospel,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.neoSoul') AS neoSoul,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.soul') AS soul,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.bigBandSwing') AS bigBandSwing,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.bebop') AS bebop,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.contemporaryJazz') AS contemporaryJazz,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.easyListening') AS easyListening,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.fusion') AS fusion,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.latinJazz') AS latinJazz,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.smoothJazz') AS smoothJazz,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.country') AS country,
								json_extract(tbl_cyanite.cy_segments, '$.subgenre.folk') AS folk,

								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.anxious') AS anxious,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.barren') AS barren,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.cold') AS cold,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.creepy') AS creepy,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.dark') AS dark,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.disturbing') AS disturbing,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.eerie') AS eerie,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.evil') AS evil,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.fearful') AS fearful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.mysterious') AS mysterious,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.nervous') AS nervous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.restless') AS restless,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.spooky') AS spooky,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.strange') AS strange,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.supernatural') AS supernatural,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.suspenseful') AS suspenseful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.tense') AS tense,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.weird') AS weird,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.aggressive') AS aggressive,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.agitated') AS agitated,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.angry') AS angry,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.dangerous') AS dangerous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.fiery') AS fiery,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.intense') AS intense,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.passionate') AS passionate,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.ponderous') AS ponderous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.violent') AS violent,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.comedic') AS comedic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.eccentric') AS eccentric,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.funny') AS funny,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.mischievous') AS mischievous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.quirky') AS quirky,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.whimsical') AS whimsical,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.boisterous') AS boisterous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.boingy') AS boingy,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.bright') AS bright,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.celebratory') AS celebratory,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.cheerful') AS cheerful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.excited') AS excited,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.feelGood') AS feelGood,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.fun') AS fun,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.happy') AS happy,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.joyous') AS joyous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.lighthearted') AS lighthearted,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.perky') AS perky,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.playful') AS playful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.rollicking') AS rollicking,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.upbeat') AS upbeat,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.calm') AS calm,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.contented') AS contented,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.dreamy') AS dreamy,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.introspective') AS introspective,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.laidBack') AS laidBack,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.leisurely') AS leisurely,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.lyrical') AS lyrical,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.peaceful') AS peaceful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.quiet') AS quiet,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.relaxed') AS relaxed,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.serene') AS serene,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.soothing') AS soothing,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.spiritual') AS spiritual,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.tranquil') AS tranquil,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.bittersweet') AS bittersweet,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.blue') AS blue,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.depressing') AS depressing,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.gloomy') AS gloomy,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.heavy') AS heavy,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.lonely') AS lonely,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.melancholic') AS melancholic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.mournful') AS mournful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.poignant') AS poignant,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.sad') AS sad,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.frightening') AS frightening,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.horror') AS horror,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.menacing') AS menacing,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.nightmarish') AS nightmarish,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.ominous') AS ominous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.panicStricken') AS panicStricken,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.scary') AS scary,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.concerned') AS concerned,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.determined') AS determined,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.dignified') AS dignified,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.emotional') AS emotional,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.noble') AS noble,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.serious') AS serious,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.solemn') AS solemn,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.thoughtful') AS thoughtful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.cool') AS cool,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.seductive') AS seductive,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.sexy') AS sexy,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.adventurous') AS adventurous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.confident') AS confident,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.courageous') AS courageous,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.resolute') AS resolute,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.energetic') AS energetic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.epic') AS epic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.exciting') AS exciting,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.exhilarating') AS exhilarating,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.heroic') AS heroic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.majestic') AS majestic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.powerful') AS powerful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.prestigious') AS prestigious,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.relentless') AS relentless,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.strong') AS strong,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.triumphant') AS triumphant,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.victorious') AS victorious,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.delicate') AS delicate,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.graceful') AS graceful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.hopeful') AS hopeful,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.innocent') AS innocent,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.intimate') AS intimate,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.kind') AS kind,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.light') AS light,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.loving') AS loving,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.nostalgic') AS nostalgic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.reflective') AS reflective,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.romantic') AS romantic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.sentimental') AS sentimental,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.soft') AS soft,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.sweet') AS sweet,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.tender') AS tender,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.warm') AS warm,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.anthemic') AS anthemic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.aweInspiring') AS aweInspiring,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.euphoric') AS euphoric,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.inspirational') AS inspirational,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.motivational') AS motivational,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.optimistic') AS optimistic,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.positive') AS positive,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.proud') AS proud,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.soaring') AS soaring,
								json_extract(tbl_cyanite.cy_segments, '$.moodAdvanced.uplifting') AS uplifting,

								json_extract(tbl_cyanite.cy_segments, '$.movement.bouncy') AS bouncy,
								json_extract(tbl_cyanite.cy_segments, '$.movement.driving') AS driving,
								json_extract(tbl_cyanite.cy_segments, '$.movement.flowing') AS flowing,
								json_extract(tbl_cyanite.cy_segments, '$.movement.groovy') AS groovy,
								json_extract(tbl_cyanite.cy_segments, '$.movement.nonrhythmic') AS nonrhythmic,
								json_extract(tbl_cyanite.cy_segments, '$.movement.pulsing') AS pulsing,
								json_extract(tbl_cyanite.cy_segments, '$.movement.robotic') AS robotic,
								json_extract(tbl_cyanite.cy_segments, '$.movement.running') AS running,
								json_extract(tbl_cyanite.cy_segments, '$.movement.steady') AS steady,
								json_extract(tbl_cyanite.cy_segments, '$.movement.stomping') AS stomping FROM tbl_cyanite where c_id=7765 and is_active=0";
							$result = $conn->query($sql_paras);

							print_r($result->fetch_assoc()); exit;
							while($row = $result->fetch_assoc())
							{
								$segment_timestamps = $row['stimestamps'];

								$cy_bpm = ($row['cy_bpm'] != null && $row['cy_bpm'] != 'null' && $row['cy_bpm'] != '') ? $row['cy_bpm'] : 0;

								$mood_aggressive = ($row['mood_aggressive'] != null && $row['mood_aggressive'] != 'null' && $row['mood_aggressive'] != '') ? $row['mood_aggressive'] : 0;
								$mood_calm = ($row['mood_calm'] != null && $row['mood_calm'] != 'null' && $row['mood_calm'] != '') ? $row['mood_calm'] : 0;
								$mood_chilled = ($row['mood_chilled'] != null && $row['mood_chilled'] != 'null' && $row['mood_chilled'] != '') ? $row['mood_chilled'] : 0;
								$mood_dark = ($row['mood_dark'] != null && $row['mood_dark'] != 'null' && $row['mood_dark'] != '') ? $row['mood_dark'] : 0;
								$mood_energetic = ($row['mood_energetic'] != null && $row['mood_energetic'] != 'null' && $row['mood_energetic'] != '') ? $row['mood_energetic'] : 0;
								$mood_epic = ($row['mood_epic'] != null && $row['mood_epic'] != 'null' && $row['mood_epic'] != '') ? $row['mood_epic'] : 0;
								$mood_happy = ($row['mood_happy'] != null && $row['mood_happy'] != 'null' && $row['mood_happy'] != '') ? $row['mood_happy'] : 0;
								$mood_romantic = ($row['mood_romantic'] != null && $row['mood_romantic'] != 'null' && $row['mood_romantic'] != '') ? $row['mood_romantic'] : 0;
								$mood_sad = ($row['mood_sad'] != null && $row['mood_sad'] != 'null' && $row['mood_sad'] != '') ? $row['mood_sad'] : 0;
								$mood_scary = ($row['mood_scary'] != null && $row['mood_scary'] != 'null' && $row['mood_scary'] != '') ? $row['mood_scary'] : 0;
								$mood_sexy = ($row['mood_sexy'] != null && $row['mood_sexy'] != 'null' && $row['mood_sexy'] != '') ? $row['mood_sexy'] : 0;
								$mood_ethereal = ($row['mood_ethereal'] != null && $row['mood_ethereal'] != 'null' && $row['mood_ethereal'] != '') ? $row['mood_ethereal'] : 0;
								$mood_uplifting = ($row['mood_uplifting'] != null && $row['mood_uplifting'] != 'null' && $row['mood_uplifting'] != '') ? $row['mood_uplifting'] : 0;
								$mooda_anxious = ($row['mooda_anxious'] != null && $row['mooda_anxious'] != 'null' && $row['mooda_anxious'] != '') ? $row['mooda_anxious'] : 0;
								$mooda_barren = ($row['mooda_barren'] != null && $row['mooda_barren'] != 'null' && $row['mooda_barren'] != '') ? $row['mooda_barren'] : 0;
								$mooda_cold = ($row['mooda_cold'] != null && $row['mooda_cold'] != 'null' && $row['mooda_cold'] != '') ? $row['mooda_cold'] : 0;
								$mooda_creepy = ($row['mooda_creepy'] != null && $row['mooda_creepy'] != 'null' && $row['mooda_creepy'] != '') ? $row['mooda_creepy'] : 0;
								$mooda_dark = ($row['mooda_dark'] != null && $row['mooda_dark'] != 'null' && $row['mooda_dark'] != '') ? $row['mooda_dark'] : 0;
								$mooda_disturbing = ($row['mooda_disturbing'] != null && $row['mooda_disturbing'] != 'null' && $row['mooda_disturbing'] != '') ? $row['mooda_disturbing'] : 0;
								$mooda_eerie = ($row['mooda_eerie'] != null && $row['mooda_eerie'] != 'null' && $row['mooda_eerie'] != '') ? $row['mooda_eerie'] : 0;
								$mooda_evil = ($row['mooda_evil'] != null && $row['mooda_evil'] != 'null' && $row['mooda_evil'] != '') ? $row['mooda_evil'] : 0;
								$mooda_fearful = ($row['mooda_fearful'] != null && $row['mooda_fearful'] != 'null' && $row['mooda_fearful'] != '') ? $row['mooda_fearful'] : 0;
								$mooda_mysterious = ($row['mooda_mysterious'] != null && $row['mooda_mysterious'] != 'null' && $row['mooda_mysterious'] != '') ? $row['mooda_mysterious'] : 0;
								$mooda_nervous = ($row['mooda_nervous'] != null && $row['mooda_nervous'] != 'null' && $row['mooda_nervous'] != '') ? $row['mooda_nervous'] : 0;
								$mooda_restless = ($row['mooda_restless'] != null && $row['mooda_restless'] != 'null' && $row['mooda_restless'] != '') ? $row['mooda_restless'] : 0;
								$mooda_spooky = ($row['mooda_spooky'] != null && $row['mooda_spooky'] != 'null' && $row['mooda_spooky'] != '') ? $row['mooda_spooky'] : 0;
								$mooda_strange = ($row['mooda_strange'] != null && $row['mooda_strange'] != 'null' && $row['mooda_strange'] != '') ? $row['mooda_strange'] : 0;
								$mooda_supernatural = ($row['mooda_supernatural'] != null && $row['mooda_supernatural'] != 'null' && $row['mooda_supernatural'] != '') ? $row['mooda_supernatural'] : 0;
								$mooda_suspenseful = ($row['mooda_suspenseful'] != null && $row['mooda_suspenseful'] != 'null' && $row['mooda_suspenseful'] != '') ? $row['mooda_suspenseful'] : 0;
								$mooda_tense = ($row['mooda_tense'] != null && $row['mooda_tense'] != 'null' && $row['mooda_tense'] != '') ? $row['mooda_tense'] : 0;
								$mooda_weird = ($row['mooda_weird'] != null && $row['mooda_weird'] != 'null' && $row['mooda_weird'] != '') ? $row['mooda_weird'] : 0;
								$mooda_aggressive = ($row['mooda_aggressive'] != null && $row['mooda_aggressive'] != 'null' && $row['mooda_aggressive'] != '') ? $row['mooda_aggressive'] : 0;
								$mooda_agitated = ($row['mooda_agitated'] != null && $row['mooda_agitated'] != 'null' && $row['mooda_agitated'] != '') ? $row['mooda_agitated'] : 0;
								$mooda_angry = ($row['mooda_angry'] != null && $row['mooda_angry'] != 'null' && $row['mooda_angry'] != '') ? $row['mooda_angry'] : 0;
								$mooda_dangerous = ($row['mooda_dangerous'] != null && $row['mooda_dangerous'] != 'null' && $row['mooda_dangerous'] != '') ? $row['mooda_dangerous'] : 0;
								$mooda_fiery = ($row['mooda_fiery'] != null && $row['mooda_fiery'] != 'null' && $row['mooda_fiery'] != '') ? $row['mooda_fiery'] : 0;
								$mooda_intense = ($row['mooda_intense'] != null && $row['mooda_intense'] != 'null' && $row['mooda_intense'] != '') ? $row['mooda_intense'] : 0;
								$mooda_passionate = ($row['mooda_passionate'] != null && $row['mooda_passionate'] != 'null' && $row['mooda_passionate'] != '') ? $row['mooda_passionate'] : 0;
								$mooda_ponderous = ($row['mooda_ponderous'] != null && $row['mooda_ponderous'] != 'null' && $row['mooda_ponderous'] != '') ? $row['mooda_ponderous'] : 0;
								$mooda_violent = ($row['mooda_violent'] != null && $row['mooda_violent'] != 'null' && $row['mooda_violent'] != '') ? $row['mooda_violent'] : 0;
								$mooda_comedic = ($row['mooda_comedic'] != null && $row['mooda_comedic'] != 'null' && $row['mooda_comedic'] != '') ? $row['mooda_comedic'] : 0;
								$mooda_eccentric = ($row['mooda_eccentric'] != null && $row['mooda_eccentric'] != 'null' && $row['mooda_eccentric'] != '') ? $row['mooda_eccentric'] : 0;
								$mooda_funny = ($row['mooda_funny'] != null && $row['mooda_funny'] != 'null' && $row['mooda_funny'] != '') ? $row['mooda_funny'] : 0;
								$mooda_mischievous = ($row['mooda_mischievous'] != null && $row['mooda_mischievous'] != 'null' && $row['mooda_mischievous'] != '') ? $row['mooda_mischievous'] : 0;
								$mooda_quirky = ($row['mooda_quirky'] != null && $row['mooda_quirky'] != 'null' && $row['mooda_quirky'] != '') ? $row['mooda_quirky'] : 0;
								$mooda_whimsical = ($row['mooda_whimsical'] != null && $row['mooda_whimsical'] != 'null' && $row['mooda_whimsical'] != '') ? $row['mooda_whimsical'] : 0;
								$mooda_boisterous = ($row['mooda_boisterous'] != null && $row['mooda_boisterous'] != 'null' && $row['mooda_boisterous'] != '') ? $row['mooda_boisterous'] : 0;
								$mooda_boingy = ($row['mooda_boingy'] != null && $row['mooda_boingy'] != 'null' && $row['mooda_boingy'] != '') ? $row['mooda_boingy'] : 0;
								$mooda_bright = ($row['mooda_bright'] != null && $row['mooda_bright'] != 'null' && $row['mooda_bright'] != '') ? $row['mooda_bright'] : 0;
								$mooda_celebratory = ($row['mooda_celebratory'] != null && $row['mooda_celebratory'] != 'null' && $row['mooda_celebratory'] != '') ? $row['mooda_celebratory'] : 0;
								$mooda_cheerful = ($row['mooda_cheerful'] != null && $row['mooda_cheerful'] != 'null' && $row['mooda_cheerful'] != '') ? $row['mooda_cheerful'] : 0;
								$mooda_excited = ($row['mooda_excited'] != null && $row['mooda_excited'] != 'null' && $row['mooda_excited'] != '') ? $row['mooda_excited'] : 0;
								$mooda_feelGood = ($row['mooda_feelGood'] != null && $row['mooda_feelGood'] != 'null' && $row['mooda_feelGood'] != '') ? $row['mooda_feelGood'] : 0;
								$mooda_fun = ($row['mooda_fun'] != null && $row['mooda_fun'] != 'null' && $row['mooda_fun'] != '') ? $row['mooda_fun'] : 0;
								$mooda_happy = ($row['mooda_happy'] != null && $row['mooda_happy'] != 'null' && $row['mooda_happy'] != '') ? $row['mooda_happy'] : 0;
								$mooda_joyous = ($row['mooda_joyous'] != null && $row['mooda_joyous'] != 'null' && $row['mooda_joyous'] != '') ? $row['mooda_joyous'] : 0;
								$mooda_lighthearted = ($row['mooda_lighthearted'] != null && $row['mooda_lighthearted'] != 'null' && $row['mooda_lighthearted'] != '') ? $row['mooda_lighthearted'] : 0;
								$mooda_perky = ($row['mooda_perky'] != null && $row['mooda_perky'] != 'null' && $row['mooda_perky'] != '') ? $row['mooda_perky'] : 0;
								$mooda_playful = ($row['mooda_playful'] != null && $row['mooda_playful'] != 'null' && $row['mooda_playful'] != '') ? $row['mooda_playful'] : 0;
								$mooda_rollicking = ($row['mooda_rollicking'] != null && $row['mooda_rollicking'] != 'null' && $row['mooda_rollicking'] != '') ? $row['mooda_rollicking'] : 0;
								$mooda_upbeat = ($row['mooda_upbeat'] != null && $row['mooda_upbeat'] != 'null' && $row['mooda_upbeat'] != '') ? $row['mooda_upbeat'] : 0;
								$mooda_calm = ($row['mooda_calm'] != null && $row['mooda_calm'] != 'null' && $row['mooda_calm'] != '') ? $row['mooda_calm'] : 0;
								$mooda_contented = ($row['mooda_contented'] != null && $row['mooda_contented'] != 'null' && $row['mooda_contented'] != '') ? $row['mooda_contented'] : 0;
								$mooda_dreamy = ($row['mooda_dreamy'] != null && $row['mooda_dreamy'] != 'null' && $row['mooda_dreamy'] != '') ? $row['mooda_dreamy'] : 0;
								$mooda_introspective = ($row['mooda_introspective'] != null && $row['mooda_introspective'] != 'null' && $row['mooda_introspective'] != '') ? $row['mooda_introspective'] : 0;
								$mooda_laidBack = ($row['mooda_laidBack'] != null && $row['mooda_laidBack'] != 'null' && $row['mooda_laidBack'] != '') ? $row['mooda_laidBack'] : 0;
								$mooda_leisurely = ($row['mooda_leisurely'] != null && $row['mooda_leisurely'] != 'null' && $row['mooda_leisurely'] != '') ? $row['mooda_leisurely'] : 0;
								$mooda_lyrical = ($row['mooda_lyrical'] != null && $row['mooda_lyrical'] != 'null' && $row['mooda_lyrical'] != '') ? $row['mooda_lyrical'] : 0;
								$mooda_peaceful = ($row['mooda_peaceful'] != null && $row['mooda_peaceful'] != 'null' && $row['mooda_peaceful'] != '') ? $row['mooda_peaceful'] : 0;
								$mooda_quiet = ($row['mooda_quiet'] != null && $row['mooda_quiet'] != 'null' && $row['mooda_quiet'] != '') ? $row['mooda_quiet'] : 0;
								$mooda_relaxed = ($row['mooda_relaxed'] != null && $row['mooda_relaxed'] != 'null' && $row['mooda_relaxed'] != '') ? $row['mooda_relaxed'] : 0;
								$mooda_serene = ($row['mooda_serene'] != null && $row['mooda_serene'] != 'null' && $row['mooda_serene'] != '') ? $row['mooda_serene'] : 0;
								$mooda_soothing = ($row['mooda_soothing'] != null && $row['mooda_soothing'] != 'null' && $row['mooda_soothing'] != '') ? $row['mooda_soothing'] : 0;
								$mooda_spiritual = ($row['mooda_spiritual'] != null && $row['mooda_spiritual'] != 'null' && $row['mooda_spiritual'] != '') ? $row['mooda_spiritual'] : 0;
								$mooda_tranquil = ($row['mooda_tranquil'] != null && $row['mooda_tranquil'] != 'null' && $row['mooda_tranquil'] != '') ? $row['mooda_tranquil'] : 0;
								$mooda_bittersweet = ($row['mooda_bittersweet'] != null && $row['mooda_bittersweet'] != 'null' && $row['mooda_bittersweet'] != '') ? $row['mooda_bittersweet'] : 0;
								$mooda_blue = ($row['mooda_blue'] != null && $row['mooda_blue'] != 'null' && $row['mooda_blue'] != '') ? $row['mooda_blue'] : 0;
								$mooda_depressing = ($row['mooda_depressing'] != null && $row['mooda_depressing'] != 'null' && $row['mooda_depressing'] != '') ? $row['mooda_depressing'] : 0;
								$mooda_gloomy = ($row['mooda_gloomy'] != null && $row['mooda_gloomy'] != 'null' && $row['mooda_gloomy'] != '') ? $row['mooda_gloomy'] : 0;
								$mooda_lonely = ($row['mooda_lonely'] != null && $row['mooda_lonely'] != 'null' && $row['mooda_lonely'] != '') ? $row['mooda_lonely'] : 0;
								$mooda_melancholic = ($row['mooda_melancholic'] != null && $row['mooda_melancholic'] != 'null' && $row['mooda_melancholic'] != '') ? $row['mooda_melancholic'] : 0;
								$mooda_mournful = ($row['mooda_mournful'] != null && $row['mooda_mournful'] != 'null' && $row['mooda_mournful'] != '') ? $row['mooda_mournful'] : 0;
								$mooda_poignant = ($row['mooda_poignant'] != null && $row['mooda_poignant'] != 'null' && $row['mooda_poignant'] != '') ? $row['mooda_poignant'] : 0;
								$mooda_sad = ($row['mooda_sad'] != null && $row['mooda_sad'] != 'null' && $row['mooda_sad'] != '') ? $row['mooda_sad'] : 0;
								$mooda_frightening = ($row['mooda_frightening'] != null && $row['mooda_frightening'] != 'null' && $row['mooda_frightening'] != '') ? $row['mooda_frightening'] : 0;
								$mooda_menacing = ($row['mooda_menacing'] != null && $row['mooda_menacing'] != 'null' && $row['mooda_menacing'] != '') ? $row['mooda_menacing'] : 0;
								$mooda_nightmarish = ($row['mooda_nightmarish'] != null && $row['mooda_nightmarish'] != 'null' && $row['mooda_nightmarish'] != '') ? $row['mooda_nightmarish'] : 0;
								$mooda_ominous = ($row['mooda_ominous'] != null && $row['mooda_ominous'] != 'null' && $row['mooda_ominous'] != '') ? $row['mooda_ominous'] : 0;
								$mooda_panicStricken = ($row['mooda_panicStricken'] != null && $row['mooda_panicStricken'] != 'null' && $row['mooda_panicStricken'] != '') ? $row['mooda_panicStricken'] : 0;
								$mooda_scary = ($row['mooda_scary'] != null && $row['mooda_scary'] != 'null' && $row['mooda_scary'] != '') ? $row['mooda_scary'] : 0;
								$mooda_concerned = ($row['mooda_concerned'] != null && $row['mooda_concerned'] != 'null' && $row['mooda_concerned'] != '') ? $row['mooda_concerned'] : 0;
								$mooda_determined = ($row['mooda_determined'] != null && $row['mooda_determined'] != 'null' && $row['mooda_determined'] != '') ? $row['mooda_determined'] : 0;
								$mooda_dignified = ($row['mooda_dignified'] != null && $row['mooda_dignified'] != 'null' && $row['mooda_dignified'] != '') ? $row['mooda_dignified'] : 0;
								$mooda_emotional = ($row['mooda_emotional'] != null && $row['mooda_emotional'] != 'null' && $row['mooda_emotional'] != '') ? $row['mooda_emotional'] : 0;
								$mooda_noble = ($row['mooda_noble'] != null && $row['mooda_noble'] != 'null' && $row['mooda_noble'] != '') ? $row['mooda_noble'] : 0;
								$mooda_serious = ($row['mooda_serious'] != null && $row['mooda_serious'] != 'null' && $row['mooda_serious'] != '') ? $row['mooda_serious'] : 0;
								$mooda_solemn = ($row['mooda_solemn'] != null && $row['mooda_solemn'] != 'null' && $row['mooda_solemn'] != '') ? $row['mooda_solemn'] : 0;
								$mooda_thoughtful = ($row['mooda_thoughtful'] != null && $row['mooda_thoughtful'] != 'null' && $row['mooda_thoughtful'] != '') ? $row['mooda_thoughtful'] : 0;
								$mooda_cool = ($row['mooda_cool'] != null && $row['mooda_cool'] != 'null' && $row['mooda_cool'] != '') ? $row['mooda_cool'] : 0;
								$mooda_seductive = ($row['mooda_seductive'] != null && $row['mooda_seductive'] != 'null' && $row['mooda_seductive'] != '') ? $row['mooda_seductive'] : 0;
								$mooda_sexy = ($row['mooda_sexy'] != null && $row['mooda_sexy'] != 'null' && $row['mooda_sexy'] != '') ? $row['mooda_sexy'] : 0;
								$mooda_adventurous = ($row['mooda_adventurous'] != null && $row['mooda_adventurous'] != 'null' && $row['mooda_adventurous'] != '') ? $row['mooda_adventurous'] : 0;
								$mooda_confident = ($row['mooda_confident'] != null && $row['mooda_confident'] != 'null' && $row['mooda_confident'] != '') ? $row['mooda_confident'] : 0;
								$mooda_courageous = ($row['mooda_courageous'] != null && $row['mooda_courageous'] != 'null' && $row['mooda_courageous'] != '') ? $row['mooda_courageous'] : 0;
								$mooda_resolute = ($row['mooda_resolute'] != null && $row['mooda_resolute'] != 'null' && $row['mooda_resolute'] != '') ? $row['mooda_resolute'] : 0;
								$mooda_energetic = ($row['mooda_energetic'] != null && $row['mooda_energetic'] != 'null' && $row['mooda_energetic'] != '') ? $row['mooda_energetic'] : 0;
								$mooda_epic = ($row['mooda_epic'] != null && $row['mooda_epic'] != 'null' && $row['mooda_epic'] != '') ? $row['mooda_epic'] : 0;
								$mooda_exciting = ($row['mooda_exciting'] != null && $row['mooda_exciting'] != 'null' && $row['mooda_exciting'] != '') ? $row['mooda_exciting'] : 0;
								$mooda_exhilarating = ($row['mooda_exhilarating'] != null && $row['mooda_exhilarating'] != 'null' && $row['mooda_exhilarating'] != '') ? $row['mooda_exhilarating'] : 0;
								$mooda_heroic = ($row['mooda_heroic'] != null && $row['mooda_heroic'] != 'null' && $row['mooda_heroic'] != '') ? $row['mooda_heroic'] : 0;
								$mooda_majestic = ($row['mooda_majestic'] != null && $row['mooda_majestic'] != 'null' && $row['mooda_majestic'] != '') ? $row['mooda_majestic'] : 0;
								$mooda_powerful = ($row['mooda_powerful'] != null && $row['mooda_powerful'] != 'null' && $row['mooda_powerful'] != '') ? $row['mooda_powerful'] : 0;
								$mooda_prestigious = ($row['mooda_prestigious'] != null && $row['mooda_prestigious'] != 'null' && $row['mooda_prestigious'] != '') ? $row['mooda_prestigious'] : 0;
								$mooda_relentless = ($row['mooda_relentless'] != null && $row['mooda_relentless'] != 'null' && $row['mooda_relentless'] != '') ? $row['mooda_relentless'] : 0;
								$mooda_strong = ($row['mooda_strong'] != null && $row['mooda_strong'] != 'null' && $row['mooda_strong'] != '') ? $row['mooda_strong'] : 0;
								$mooda_triumphant = ($row['mooda_triumphant'] != null && $row['mooda_triumphant'] != 'null' && $row['mooda_triumphant'] != '') ? $row['mooda_triumphant'] : 0;
								$mooda_victorious = ($row['mooda_victorious'] != null && $row['mooda_victorious'] != 'null' && $row['mooda_victorious'] != '') ? $row['mooda_victorious'] : 0;
								$mooda_delicate = ($row['mooda_delicate'] != null && $row['mooda_delicate'] != 'null' && $row['mooda_delicate'] != '') ? $row['mooda_delicate'] : 0;
								$mooda_graceful = ($row['mooda_graceful'] != null && $row['mooda_graceful'] != 'null' && $row['mooda_graceful'] != '') ? $row['mooda_graceful'] : 0;
								$mooda_hopeful = ($row['mooda_hopeful'] != null && $row['mooda_hopeful'] != 'null' && $row['mooda_hopeful'] != '') ? $row['mooda_hopeful'] : 0;
								$mooda_innocent = ($row['mooda_innocent'] != null && $row['mooda_innocent'] != 'null' && $row['mooda_innocent'] != '') ? $row['mooda_innocent'] : 0;
								$mooda_intimate = ($row['mooda_intimate'] != null && $row['mooda_intimate'] != 'null' && $row['mooda_intimate'] != '') ? $row['mooda_intimate'] : 0;
								$mooda_kind = ($row['mooda_kind'] != null && $row['mooda_kind'] != 'null' && $row['mooda_kind'] != '') ? $row['mooda_kind'] : 0;
								$mooda_light = ($row['mooda_light'] != null && $row['mooda_light'] != 'null' && $row['mooda_light'] != '') ? $row['mooda_light'] : 0;
								$mooda_loving = ($row['mooda_loving'] != null && $row['mooda_loving'] != 'null' && $row['mooda_loving'] != '') ? $row['mooda_loving'] : 0;
								$mooda_nostalgic = ($row['mooda_nostalgic'] != null && $row['mooda_nostalgic'] != 'null' && $row['mooda_nostalgic'] != '') ? $row['mooda_nostalgic'] : 0;
								$mooda_reflective = ($row['mooda_reflective'] != null && $row['mooda_reflective'] != 'null' && $row['mooda_reflective'] != '') ? $row['mooda_reflective'] : 0;
								$mooda_romantic = ($row['mooda_romantic'] != null && $row['mooda_romantic'] != 'null' && $row['mooda_romantic'] != '') ? $row['mooda_romantic'] : 0;
								$mooda_sentimental = ($row['mooda_sentimental'] != null && $row['mooda_sentimental'] != 'null' && $row['mooda_sentimental'] != '') ? $row['mooda_sentimental'] : 0;
								$mooda_soft = ($row['mooda_soft'] != null && $row['mooda_soft'] != 'null' && $row['mooda_soft'] != '') ? $row['mooda_soft'] : 0;
								$mooda_sweet = ($row['mooda_sweet'] != null && $row['mooda_sweet'] != 'null' && $row['mooda_sweet'] != '') ? $row['mooda_sweet'] : 0;
								$mooda_tender = ($row['mooda_tender'] != null && $row['mooda_tender'] != 'null' && $row['mooda_tender'] != '') ? $row['mooda_tender'] : 0;
								$mooda_warm = ($row['mooda_warm'] != null && $row['mooda_warm'] != 'null' && $row['mooda_warm'] != '') ? $row['mooda_warm'] : 0;
								$mooda_anthemic = ($row['mooda_anthemic'] != null && $row['mooda_anthemic'] != 'null' && $row['mooda_anthemic'] != '') ? $row['mooda_anthemic'] : 0;
								$mooda_aweInspiring = ($row['mooda_aweInspiring'] != null && $row['mooda_aweInspiring'] != 'null' && $row['mooda_aweInspiring'] != '') ? $row['mooda_aweInspiring'] : 0;
								$mooda_euphoric = ($row['mooda_euphoric'] != null && $row['mooda_euphoric'] != 'null' && $row['mooda_euphoric'] != '') ? $row['mooda_euphoric'] : 0;
								$mooda_inspirational = ($row['mooda_inspirational'] != null && $row['mooda_inspirational'] != 'null' && $row['mooda_inspirational'] != '') ? $row['mooda_inspirational'] : 0;
								$mooda_motivational = ($row['mooda_motivational'] != null && $row['mooda_motivational'] != 'null' && $row['mooda_motivational'] != '') ? $row['mooda_motivational'] : 0;

								$mooda_optimistic = ($row['mooda_optimistic'] != null && $row['mooda_optimistic'] != 'null' && $row['mooda_optimistic'] != '') ? $row['mooda_optimistic'] : 0;
								$mooda_positive = ($row['mooda_positive'] != null && $row['mooda_positive'] != 'null' && $row['mooda_positive'] != '') ? $row['mooda_positive'] : 0;
								$mooda_proud = ($row['mooda_proud'] != null && $row['mooda_proud'] != 'null' && $row['mooda_proud'] != '') ? $row['mooda_proud'] : 0;
								$mooda_soaring = ($row['mooda_soaring'] != null && $row['mooda_soaring'] != 'null' && $row['mooda_soaring'] != '') ? $row['mooda_soaring'] : 0;
								$mooda_uplifting = ($row['mooda_uplifting'] != null && $row['mooda_uplifting'] != 'null' && $row['mooda_uplifting'] != '') ? $row['mooda_uplifting'] : 0;
								$genre_ambient = ($row['genre_ambient'] != null && $row['genre_ambient'] != 'null' && $row['genre_ambient'] != '') ? $row['genre_ambient'] : 0;
								$genre_blues = ($row['genre_blues'] != null && $row['genre_blues'] != 'null' && $row['genre_blues'] != '') ? $row['genre_blues'] : 0;
								$genre_classical = ($row['genre_classical'] != null && $row['genre_classical'] != 'null' && $row['genre_classical'] != '') ? $row['genre_classical'] : 0;
								$genre_electronicDance = ($row['genre_electronicDance'] != null && $row['genre_electronicDance'] != 'null' && $row['genre_electronicDance'] != '') ? $row['genre_electronicDance'] : 0;
								$genre_folkCountry = ($row['genre_folkCountry'] != null && $row['genre_folkCountry'] != 'null' && $row['genre_folkCountry'] != '') ? $row['genre_folkCountry'] : 0;
								$genre_funkSoul = ($row['genre_funkSoul'] != null && $row['genre_funkSoul'] != 'null' && $row['genre_funkSoul'] != '') ? $row['genre_funkSoul'] : 0;
								$genre_jazz = ($row['genre_jazz'] != null && $row['genre_jazz'] != 'null' && $row['genre_jazz'] != '') ? $row['genre_jazz'] : 0;
								$genre_latin = ($row['genre_latin'] != null && $row['genre_latin'] != 'null' && $row['genre_latin'] != '') ? $row['genre_latin'] : 0;
								$genre_metal = ($row['genre_metal'] != null && $row['genre_metal'] != 'null' && $row['genre_metal'] != '') ? $row['genre_metal'] : 0;
								$genre_pop = ($row['genre_pop'] != null && $row['genre_pop'] != 'null' && $row['genre_pop'] != '') ? $row['genre_pop'] : 0;
								$genre_punk = ($row['genre_punk'] != null && $row['genre_punk'] != 'null' && $row['genre_punk'] != '') ? $row['genre_punk'] : 0;
								$genre_rapHipHop = ($row['genre_rapHipHop'] != null && $row['genre_rapHipHop'] != 'null' && $row['genre_rapHipHop'] != '') ? $row['genre_rapHipHop'] : 0;
								$genre_reggae = ($row['genre_reggae'] != null && $row['genre_reggae'] != 'null' && $row['genre_reggae'] != '') ? $row['genre_reggae'] : 0;
								$genre_rnb = ($row['genre_rnb'] != null && $row['genre_rnb'] != 'null' && $row['genre_rnb'] != '') ? $row['genre_rnb'] : 0;
								$genre_rock = ($row['genre_rock'] != null && $row['genre_rock'] != 'null' && $row['genre_rock'] != '') ? $row['genre_rock'] : 0;
								$genre_singerSongwriter = ($row['genre_singerSongwriter'] != null && $row['genre_singerSongwriter'] != 'null' && $row['genre_singerSongwriter'] != '') ? $row['genre_singerSongwriter'] : 0;
								$character_bold = ($row['character_bold'] != null && $row['character_bold'] != 'null' && $row['character_bold'] != '') ? $row['character_bold'] : 0;
								$character_cool = ($row['character_cool'] != null && $row['character_cool'] != 'null' && $row['character_cool'] != '') ? $row['character_cool'] : 0;
								$character_epic = ($row['character_epic'] != null && $row['character_epic'] != 'null' && $row['character_epic'] != '') ? $row['character_epic'] : 0;
								$character_ethereal = ($row['character_ethereal'] != null && $row['character_ethereal'] != 'null' && $row['character_ethereal'] != '') ? $row['character_ethereal'] : 0;
								$character_heroic = ($row['character_heroic'] != null && $row['character_heroic'] != 'null' && $row['character_heroic'] != '') ? $row['character_heroic'] : 0;
								$character_luxurious = ($row['character_luxurious'] != null && $row['character_luxurious'] != 'null' && $row['character_luxurious'] != '') ? $row['character_luxurious'] : 0;
								$character_magical = ($row['character_magical'] != null && $row['character_magical'] != 'null' && $row['character_magical'] != '') ? $row['character_magical'] : 0;
								$character_mysterious = ($row['character_mysterious'] != null && $row['character_mysterious'] != 'null' && $row['character_mysterious'] != '') ? $row['character_mysterious'] : 0;
								$character_playful = ($row['character_playful'] != null && $row['character_playful'] != 'null' && $row['character_playful'] != '') ? $row['character_playful'] : 0;
								$character_powerful = ($row['character_powerful'] != null && $row['character_powerful'] != 'null' && $row['character_powerful'] != '') ? $row['character_powerful'] : 0;
								$character_retro = ($row['character_retro'] != null && $row['character_retro'] != 'null' && $row['character_retro'] != '') ? $row['character_retro'] : 0;
								$character_sophisticated = ($row['character_sophisticated'] != null && $row['character_sophisticated'] != 'null' && $row['character_sophisticated'] != '') ? $row['character_sophisticated'] : 0;
								$character_sparkling = ($row['character_sparkling'] != null && $row['character_sparkling'] != 'null' && $row['character_sparkling'] != '') ? $row['character_sparkling'] : 0;
								$character_sparse = ($row['character_sparse'] != null && $row['character_sparse'] != 'null' && $row['character_sparse'] != '') ? $row['character_sparse'] : 0;
								$character_unpolished = ($row['character_unpolished'] != null && $row['character_unpolished'] != 'null' && $row['character_unpolished'] != '') ? $row['character_unpolished'] : 0;
								$character_warm = ($row['character_warm'] != null && $row['character_warm'] != 'null' && $row['character_warm'] != '') ? $row['character_warm'] : 0;
								$movement_bouncy = ($row['movement_bouncy'] != null && $row['movement_bouncy'] != 'null' && $row['movement_bouncy'] != '') ? $row['movement_bouncy'] : 0;
								$movement_driving = ($row['movement_driving'] != null && $row['movement_driving'] != 'null' && $row['movement_driving'] != '') ? $row['movement_driving'] : 0;
								$movement_flowing = ($row['movement_flowing'] != null && $row['movement_flowing'] != 'null' && $row['movement_flowing'] != '') ? $row['movement_flowing'] : 0;
								$movement_groovy = ($row['movement_groovy'] != null && $row['movement_groovy'] != 'null' && $row['movement_groovy'] != '') ? $row['movement_groovy'] : 0;
								$movement_nonrhythmic = ($row['movement_nonrhythmic'] != null && $row['movement_nonrhythmic'] != 'null' && $row['movement_nonrhythmic'] != '') ? $row['movement_nonrhythmic'] : 0;
								$movement_pulsing = ($row['movement_pulsing'] != null && $row['movement_pulsing'] != 'null' && $row['movement_pulsing'] != '') ? $row['movement_pulsing'] : 0;
								$movement_robotic = ($row['movement_robotic'] != null && $row['movement_robotic'] != 'null' && $row['movement_robotic'] != '') ? $row['movement_robotic'] : 0;
								$movement_running = ($row['movement_running'] != null && $row['movement_running'] != 'null' && $row['movement_running'] != '') ? $row['movement_running'] : 0;
								$movement_steady = ($row['movement_steady'] != null && $row['movement_steady'] != 'null' && $row['movement_steady'] != '') ? $row['movement_steady'] : 0;
								$movement_stomping = ($row['movement_stomping'] != null && $row['movement_stomping'] != 'null' && $row['movement_stomping'] != '') ? $row['movement_stomping'] : 0;
								
								$ins_result_qry = '';

								$ins_result_segment_qry = '';

								$ins_result_qry = "INSERT INTO `tbl_cyanite_result`(`c_id`, `energylevel`, `emotionalprofile`, `bpm`, `keyprediction`, `timesignature`, `mood_aggressive`, `mood_calm`, `mood_chilled`, `mood_dark`, `mood_energetic`, `mood_epic`, `mood_happy`, `mood_romantic`, `mood_sad`, `mood_scary`, `mood_sexy`, `mood_ethereal`, `mood_uplifting`, `mooda_anxious`, `mooda_barren`, `mooda_cold`, `mooda_creepy`, `mooda_dark`, `mooda_disturbing`, `mooda_eerie`, `mooda_evil`, `mooda_fearful`, `mooda_mysterious`, `mooda_nervous`, `mooda_restless`, `mooda_spooky`, `mooda_strange`, `mooda_supernatural`, `mooda_suspenseful`, `mooda_tense`, `mooda_weird`, `mooda_aggressive`, `mooda_agitated`, `mooda_angry`, `mooda_dangerous`, `mooda_fiery`, `mooda_intense`, `mooda_passionate`, `mooda_ponderous`, `mooda_violent`, `mooda_comedic`, `mooda_eccentric`, `mooda_funny`, `mooda_mischievous`, `mooda_quirky`, `mooda_whimsical`, `mooda_boisterous`, `mooda_boingy`, `mooda_bright`, `mooda_celebratory`, `mooda_cheerful`, `mooda_excited`, `mooda_feelGood`, `mooda_fun`, `mooda_happy`, `mooda_joyous`, `mooda_lighthearted`, `mooda_perky`, `mooda_playful`, `mooda_rollicking`, `mooda_upbeat`, `mooda_calm`, `mooda_contented`, `mooda_dreamy`, `mooda_introspective`, `mooda_laidBack`, `mooda_leisurely`, `mooda_lyrical`, `mooda_peaceful`, `mooda_quiet`, `mooda_relaxed`, `mooda_serene`, `mooda_soothing`, `mooda_spiritual`, `mooda_tranquil`, `mooda_bittersweet`, `mooda_blue`, `mooda_depressing`, `mooda_gloomy`, `mooda_lonely`, `mooda_melancholic`, `mooda_mournful`, `mooda_poignant`, `mooda_sad`, `mooda_frightening`, `mooda_menacing`, `mooda_nightmarish`, `mooda_ominous`, `mooda_panicStricken`, `mooda_scary`, `mooda_concerned`, `mooda_determined`, `mooda_dignified`, `mooda_emotional`, `mooda_noble`, `mooda_serious`, `mooda_solemn`, `mooda_thoughtful`, `mooda_cool`, `mooda_seductive`, `mooda_sexy`, `mooda_adventurous`, `mooda_confident`, `mooda_courageous`, `mooda_resolute`, `mooda_energetic`, `mooda_epic`, `mooda_exciting`, `mooda_exhilarating`, `mooda_heroic`, `mooda_majestic`, `mooda_powerful`, `mooda_prestigious`, `mooda_relentless`, `mooda_strong`, `mooda_triumphant`, `mooda_victorious`, `mooda_delicate`, `mooda_graceful`, `mooda_hopeful`, `mooda_innocent`, `mooda_intimate`, `mooda_kind`, `mooda_light`, `mooda_loving`, `mooda_nostalgic`, `mooda_reflective`, `mooda_romantic`, `mooda_sentimental`, `mooda_soft`, `mooda_sweet`, `mooda_tender`, `mooda_warm`, `mooda_anthemic`, `mooda_aweInspiring`, `mooda_euphoric`, `mooda_inspirational`, `mooda_motivational`, `mooda_optimistic`, `mooda_positive`, `mooda_proud`, `mooda_soaring`, `mooda_uplifting`, `genre_ambient`, `genre_blues`, `genre_classical`, `genre_electronicDance`, `genre_folkCountry`, `genre_funkSoul`, `genre_jazz`, `genre_latin`, `genre_metal`, `genre_pop`, `genre_punk`, `genre_rapHipHop`, `genre_reggae`, `genre_rnb`, `genre_rock`, `genre_singerSongwriter`, `character_bold`, `character_cool`, `character_epic`, `character_ethereal`, `character_heroic`, `character_luxurious`, `character_magical`, `character_mysterious`, `character_playful`, `character_powerful`, `character_retro`, `character_sophisticated`, `character_sparkling`, `character_sparse`, `character_unpolished`, `character_warm`, `movement_bouncy`, `movement_driving`, `movement_flowing`, `movement_groovy`, `movement_nonrhythmic`, `movement_pulsing`, `movement_robotic`, `movement_running`, `movement_steady`, `movement_stomping`) VALUES (".$c_id.",'".$row['cy_energylevel']."','".$row['cy_emotionalprofile']."',".$cy_bpm.",'".$row['cy_key']."','".$row['cy_timesignature']."',".$mood_aggressive.",".$mood_calm.",".$mood_chilled.",".$mood_dark.",".$mood_energetic.",".$mood_epic.",".$mood_happy.",".$mood_romantic.",".$mood_sad.",".$mood_scary.",".$mood_sexy.",".$mood_ethereal.",".$mood_uplifting.",".$mooda_anxious.",".$mooda_barren.",".$mooda_cold.",".$mooda_creepy.",".$mooda_dark.",".$mooda_disturbing.",".$mooda_eerie.",".$mooda_evil.",".$mooda_fearful.",".$mooda_mysterious.",".$mooda_nervous.",".$mooda_restless.",".$mooda_spooky.",".$mooda_strange.",".$mooda_supernatural.",".$mooda_suspenseful.",".$mooda_tense.",".$mooda_weird.",".$mooda_aggressive.",".$mooda_agitated.",".$mooda_angry.",".$mooda_dangerous.",".$mooda_fiery.",".$mooda_intense.",".$mooda_passionate.",".$mooda_ponderous.",".$mooda_violent.",".$mooda_comedic.",".$mooda_eccentric.",".$mooda_funny.",".$mooda_mischievous.",".$mooda_quirky.",".$mooda_whimsical.",".$mooda_boisterous.",".$mooda_boingy.",".$mooda_bright.",".$mooda_celebratory.",".$mooda_cheerful.",".$mooda_excited.",".$mooda_feelGood.",".$mooda_fun.",".$mooda_happy.",".$mooda_joyous.",".$mooda_lighthearted.",".$mooda_perky.",".$mooda_playful.",".$mooda_rollicking.",".$mooda_upbeat.",".$mooda_calm.",".$mooda_contented.",".$mooda_dreamy.",".$mooda_introspective.",".$mooda_laidBack.",".$mooda_leisurely.",".$mooda_lyrical.",".$mooda_peaceful.",".$mooda_quiet.",".$mooda_relaxed.",".$mooda_serene.",".$mooda_soothing.",".$mooda_spiritual.",".$mooda_tranquil.",".$mooda_bittersweet.",".$mooda_blue.",".$mooda_depressing.",".$mooda_gloomy.",".$mooda_lonely.",".$mooda_melancholic.",".$mooda_mournful.",".$mooda_poignant.",".$mooda_sad.",".$mooda_frightening.",".$mooda_menacing.",".$mooda_nightmarish.",".$mooda_ominous.",".$mooda_panicStricken.",".$mooda_scary.",".$mooda_concerned.",".$mooda_determined.",".$mooda_dignified.",".$mooda_emotional.",".$mooda_noble.",".$mooda_serious.",".$mooda_solemn.",".$mooda_thoughtful.",".$mooda_cool.",".$mooda_seductive.",".$mooda_sexy.",".$mooda_adventurous.",".$mooda_confident.",".$mooda_courageous.",".$mooda_resolute.",".$mooda_energetic.",".$mooda_epic.",".$mooda_exciting.",".$mooda_exhilarating.",".$mooda_heroic.",".$mooda_majestic.",".$mooda_powerful.",".$mooda_prestigious.",".$mooda_relentless.",".$mooda_strong.",".$mooda_triumphant.",".$mooda_victorious.",".$mooda_delicate.",".$mooda_graceful.",".$mooda_hopeful.",".$mooda_innocent.",".$mooda_intimate.",".$mooda_kind.",".$mooda_light.",".$mooda_loving.",".$mooda_nostalgic.",".$mooda_reflective.",".$mooda_romantic.",".$mooda_sentimental.",".$mooda_soft.",".$mooda_sweet.",".$mooda_tender.",".$mooda_warm.",".$mooda_anthemic.",".$mooda_aweInspiring.",".$mooda_euphoric.",".$mooda_inspirational.",".$mooda_motivational.",".$mooda_optimistic.",".$mooda_positive.",".$mooda_proud.",".$mooda_soaring.",".$mooda_uplifting.",".$genre_ambient.",".$genre_blues.",".$genre_classical.",".$genre_electronicDance.",".$genre_folkCountry.",".$genre_funkSoul.",".$genre_jazz.",".$genre_latin.",".$genre_metal.",".$genre_pop.",".$genre_punk.",".$genre_rapHipHop.",".$genre_reggae.",".$genre_rnb.",".$genre_rock.",".$genre_singerSongwriter.",".$character_bold.",".$character_cool.",".$character_epic.",".$character_ethereal.",".$character_heroic.",".$character_luxurious.",".$character_magical.",".$character_mysterious.",".$character_playful.",".$character_powerful.",".$character_retro.",".$character_sophisticated.",".$character_sparkling.",".$character_sparse.",".$character_unpolished.",".$character_warm.",".$movement_bouncy.",".$movement_driving.",".$movement_flowing.",".$movement_groovy.",".$movement_nonrhythmic.",".$movement_pulsing.",".$movement_robotic.",".$movement_running.",".$movement_steady.",".$movement_stomping.")";

echo "ins_result_qry---->".$ins_result_qry."<br><br><br><br>";
									exit;

									////////////////////////////////////////////////////////////////////////////////////

									$subgenre_bluesRock = ($row['subgenre_bluesRock'] != null && $row['subgenre_bluesRock'] != 'null' && $row['subgenre_bluesRock'] != '') ? $row['subgenre_bluesRock'] : 0;
									$subgenre_folkRock = ($row['subgenre_folkRock'] != null && $row['subgenre_folkRock'] != 'null' && $row['subgenre_folkRock'] != '') ? $row['subgenre_folkRock'] : 0;
									$subgenre_hardRock = ($row['subgenre_hardRock'] != null && $row['subgenre_hardRock'] != 'null' && $row['subgenre_hardRock'] != '') ? $row['subgenre_hardRock'] : 0;
									$subgenre_indieAlternative = ($row['subgenre_indieAlternative'] != null && $row['subgenre_indieAlternative'] != 'null' && $row['subgenre_indieAlternative'] != '') ? $row['subgenre_indieAlternative'] : 0;
									$subgenre_psychedelicProgressiveRock = ($row['subgenre_psychedelicProgressiveRock'] != null && $row['subgenre_psychedelicProgressiveRock'] != 'null' && $row['subgenre_psychedelicProgressiveRock'] != '') ? $row['subgenre_psychedelicProgressiveRock'] : 0;
									$subgenre_punk = ($row['subgenre_punk'] != null && $row['subgenre_punk'] != 'null' && $row['subgenre_punk'] != '') ? $row['subgenre_punk'] : 0;
									$subgenre_rockAndRoll = ($row['subgenre_rockAndRoll'] != null && $row['subgenre_rockAndRoll'] != 'null' && $row['subgenre_rockAndRoll'] != '') ? $row['subgenre_rockAndRoll'] : 0;
									$subgenre_popSoftRock = ($row['subgenre_popSoftRock'] != null && $row['subgenre_popSoftRock'] != 'null' && $row['subgenre_popSoftRock'] != '') ? $row['subgenre_popSoftRock'] : 0;
									$subgenre_abstractIDMLeftfield = ($row['subgenre_abstractIDMLeftfield'] != null && $row['subgenre_abstractIDMLeftfield'] != 'null' && $row['subgenre_abstractIDMLeftfield'] != '') ? $row['subgenre_abstractIDMLeftfield'] : 0;
									$subgenre_breakbeatDnB = ($row['subgenre_breakbeatDnB'] != null && $row['subgenre_breakbeatDnB'] != 'null' && $row['subgenre_breakbeatDnB'] != '') ? $row['subgenre_breakbeatDnB'] : 0;
									$subgenre_deepHouse = ($row['subgenre_deepHouse'] != null && $row['subgenre_deepHouse'] != 'null' && $row['subgenre_deepHouse'] != '') ? $row['subgenre_deepHouse'] : 0;
									$subgenre_electro = ($row['subgenre_electro'] != null && $row['subgenre_electro'] != 'null' && $row['subgenre_electro'] != '') ? $row['subgenre_electro'] : 0;
									$subgenre_house = ($row['subgenre_house'] != null && $row['subgenre_house'] != 'null' && $row['subgenre_house'] != '') ? $row['subgenre_house'] : 0;
									$subgenre_minimal = ($row['subgenre_minimal'] != null && $row['subgenre_minimal'] != 'null' && $row['subgenre_minimal'] != '') ? $row['subgenre_minimal'] : 0;
									$subgenre_synthPop = ($row['subgenre_synthPop'] != null && $row['subgenre_synthPop'] != 'null' && $row['subgenre_synthPop'] != '') ? $row['subgenre_synthPop'] : 0;
									$subgenre_techHouse = ($row['subgenre_techHouse'] != null && $row['subgenre_techHouse'] != 'null' && $row['subgenre_techHouse'] != '') ? $row['subgenre_techHouse'] : 0;
									$subgenre_techno = ($row['subgenre_techno'] != null && $row['subgenre_techno'] != 'null' && $row['subgenre_techno'] != '') ? $row['subgenre_techno'] : 0;
									$subgenre_trance = ($row['subgenre_trance'] != null && $row['subgenre_trance'] != 'null' && $row['subgenre_trance'] != '') ? $row['subgenre_trance'] : 0;
									$subgenre_contemporaryRnB = ($row['subgenre_contemporaryRnB'] != null && $row['subgenre_contemporaryRnB'] != 'null' && $row['subgenre_contemporaryRnB'] != '') ? $row['subgenre_contemporaryRnB'] : 0;
									$subgenre_gangsta = ($row['subgenre_gangsta'] != null && $row['subgenre_gangsta'] != 'null' && $row['subgenre_gangsta'] != '') ? $row['subgenre_gangsta'] : 0;
									$subgenre_jazzyHipHop = ($row['subgenre_jazzyHipHop'] != null && $row['subgenre_jazzyHipHop'] != 'null' && $row['subgenre_jazzyHipHop'] != '') ? $row['subgenre_jazzyHipHop'] : 0;
									$subgenre_popRap = ($row['subgenre_popRap'] != null && $row['subgenre_popRap'] != 'null' && $row['subgenre_popRap'] != '') ? $row['subgenre_popRap'] : 0;
									$subgenre_trap = ($row['subgenre_trap'] != null && $row['subgenre_trap'] != 'null' && $row['subgenre_trap'] != '') ? $row['subgenre_trap'] : 0;
									$subgenre_blackMetal = ($row['subgenre_blackMetal'] != null && $row['subgenre_blackMetal'] != 'null' && $row['subgenre_blackMetal'] != '') ? $row['subgenre_blackMetal'] : 0;
									$subgenre_deathMetal = ($row['subgenre_deathMetal'] != null && $row['subgenre_deathMetal'] != 'null' && $row['subgenre_deathMetal'] != '') ? $row['subgenre_deathMetal'] : 0;
									$subgenre_doomMetal = ($row['subgenre_doomMetal'] != null && $row['subgenre_doomMetal'] != 'null' && $row['subgenre_doomMetal'] != '') ? $row['subgenre_doomMetal'] : 0;
									$subgenre_heavyMetal = ($row['subgenre_heavyMetal'] != null && $row['subgenre_heavyMetal'] != 'null' && $row['subgenre_heavyMetal'] != '') ? $row['subgenre_heavyMetal'] : 0;
									$subgenre_metalcore = ($row['subgenre_metalcore'] != null && $row['subgenre_metalcore'] != 'null' && $row['subgenre_metalcore'] != '') ? $row['subgenre_metalcore'] : 0;
									$subgenre_nuMetal = ($row['subgenre_nuMetal'] != null && $row['subgenre_nuMetal'] != 'null' && $row['subgenre_nuMetal'] != '') ? $row['subgenre_nuMetal'] : 0;
									$subgenre_disco = ($row['subgenre_disco'] != null && $row['subgenre_disco'] != 'null' && $row['subgenre_disco'] != '') ? $row['subgenre_disco'] : 0;
									$subgenre_funk = ($row['subgenre_funk'] != null && $row['subgenre_funk'] != 'null' && $row['subgenre_funk'] != '') ? $row['subgenre_funk'] : 0;
									$subgenre_gospel = ($row['subgenre_gospel'] != null && $row['subgenre_gospel'] != 'null' && $row['subgenre_gospel'] != '') ? $row['subgenre_gospel'] : 0;
									$subgenre_neoSoul = ($row['subgenre_neoSoul'] != null && $row['subgenre_neoSoul'] != 'null' && $row['subgenre_neoSoul'] != '') ? $row['subgenre_neoSoul'] : 0;
									$subgenre_soul = ($row['subgenre_soul'] != null && $row['subgenre_soul'] != 'null' && $row['subgenre_soul'] != '') ? $row['subgenre_soul'] : 0;
									$subgenre_bigBandSwing = ($row['subgenre_bigBandSwing'] != null && $row['subgenre_bigBandSwing'] != 'null' && $row['subgenre_bigBandSwing'] != '') ? $row['subgenre_bigBandSwing'] : 0;
									$subgenre_bebop = ($row['subgenre_bebop'] != null && $row['subgenre_bebop'] != 'null' && $row['subgenre_bebop'] != '') ? $row['subgenre_bebop'] : 0;
									$subgenre_contemporaryJazz = ($row['subgenre_contemporaryJazz'] != null && $row['subgenre_contemporaryJazz'] != 'null' && $row['subgenre_contemporaryJazz'] != '') ? $row['subgenre_contemporaryJazz'] : 0;
									$subgenre_easyListening = ($row['subgenre_easyListening'] != null && $row['subgenre_easyListening'] != 'null' && $row['subgenre_easyListening'] != '') ? $row['subgenre_easyListening'] : 0;
									$subgenre_fusion = ($row['subgenre_fusion'] != null && $row['subgenre_fusion'] != 'null' && $row['subgenre_fusion'] != '') ? $row['subgenre_fusion'] : 0;
									$subgenre_latinJazz = ($row['subgenre_latinJazz'] != null && $row['subgenre_latinJazz'] != 'null' && $row['subgenre_latinJazz'] != '') ? $row['subgenre_latinJazz'] : 0;
									$subgenre_smoothJazz = ($row['subgenre_smoothJazz'] != null && $row['subgenre_smoothJazz'] != 'null' && $row['subgenre_smoothJazz'] != '') ? $row['subgenre_smoothJazz'] : 0;
									$subgenre_country = ($row['subgenre_country'] != null && $row['subgenre_country'] != 'null' && $row['subgenre_country'] != '') ? $row['subgenre_country'] : 0;
									$subgenre_folk = ($row['subgenre_folk'] != null && $row['subgenre_folk'] != 'null' && $row['subgenre_folk'] != '') ? $row['subgenre_folk'] : 0;
									

									/*$ins_result_part2_qry = "INSERT INTO `tbl_cyanite_result_part2`(`c_id`, `subgenre_bluesRock`, `subgenre_folkRock`, `subgenre_hardRock`, `subgenre_indieAlternative`, `subgenre_psychedelicProgressiveRock`, `subgenre_punk`, `subgenre_rockAndRoll`, `subgenre_popSoftRock`, `subgenre_abstractIDMLeftfield`, `subgenre_breakbeatDnB`, `subgenre_deepHouse`, `subgenre_electro`, `subgenre_house`, `subgenre_minimal`, `subgenre_synthPop`, `subgenre_techHouse`, `subgenre_techno`, `subgenre_trance`, `subgenre_contemporaryRnB`, `subgenre_gangsta`, `subgenre_jazzyHipHop`, `subgenre_popRap`, `subgenre_trap`, `subgenre_blackMetal`, `subgenre_deathMetal`, `subgenre_doomMetal`, `subgenre_heavyMetal`, `subgenre_metalcore`, `subgenre_nuMetal`, `subgenre_disco`, `subgenre_funk`, `subgenre_gospel`, `subgenre_neoSoul`, `subgenre_soul`, `subgenre_bigBandSwing`, `subgenre_bebop`, `subgenre_contemporaryJazz`, `subgenre_easyListening`, `subgenre_fusion`, `subgenre_latinJazz`, `subgenre_smoothJazz`, `subgenre_country`, `subgenre_folk`) VALUES (".$c_id.",'".$subgenre_bluesRock."','".$subgenre_folkRock."','".$subgenre_hardRock."','".$subgenre_indieAlternative."','".$subgenre_psychedelicProgressiveRock."','".$subgenre_punk."','".$subgenre_rockAndRoll."','".$subgenre_popSoftRock."','".$subgenre_abstractIDMLeftfield."','".$subgenre_breakbeatDnB."','".$subgenre_deepHouse."','".$subgenre_electro."','".$subgenre_house."','".$subgenre_minimal."','".$subgenre_synthPop."','".$subgenre_techHouse."','".$subgenre_techno."','".$subgenre_trance."','".$subgenre_contemporaryRnB."','".$subgenre_gangsta."','".$subgenre_jazzyHipHop."','".$subgenre_popRap."','".$subgenre_trap."','".$subgenre_blackMetal."','".$subgenre_deathMetal."','".$subgenre_doomMetal."','".$subgenre_heavyMetal."','".$subgenre_metalcore."','".$subgenre_nuMetal."','".$subgenre_disco."','".$subgenre_funk."','".$subgenre_gospel."','".$subgenre_neoSoul."','".$subgenre_soul."','".$subgenre_bigBandSwing."','".$subgenre_bebop."','".$subgenre_contemporaryJazz."','".$subgenre_easyListening."','".$subgenre_fusion."','".$subgenre_latinJazz."','".$subgenre_smoothJazz."','".$subgenre_country."','".$subgenre_folk."')";*/

									$ins_result_part2_qry = "INSERT INTO `tbl_cyanite_result_part2`(`c_id`, `subgenre_bluesRock`, `subgenre_folkRock`, `subgenre_hardRock`, `subgenre_indieAlternative`, `subgenre_psychedelicProgressiveRock`, `subgenre_punk`, `subgenre_rockAndRoll`, `subgenre_popSoftRock`, `subgenre_abstractIDMLeftfield`, `subgenre_breakbeatDnB`, `subgenre_deepHouse`, `subgenre_electro`, `subgenre_house`, `subgenre_minimal`, `subgenre_synthPop`, `subgenre_techHouse`, `subgenre_techno`, `subgenre_trance`, `subgenre_contemporaryRnB`, `subgenre_gangsta`, `subgenre_jazzyHipHop`, `subgenre_popRap`, `subgenre_trap`, `subgenre_blackMetal`, `subgenre_deathMetal`, `subgenre_doomMetal`, `subgenre_heavyMetal`, `subgenre_metalcore`, `subgenre_nuMetal`, `subgenre_disco`, `subgenre_funk`, `subgenre_gospel`, `subgenre_neoSoul`, `subgenre_soul`, `subgenre_bigBandSwing`, `subgenre_bebop`, `subgenre_contemporaryJazz`, `subgenre_easyListening`, `subgenre_fusion`, `subgenre_latinJazz`, `subgenre_smoothJazz`, `subgenre_country`, `subgenre_folk`) VALUES (".$c_id.",".$subgenre_bluesRock.",".$subgenre_folkRock.",".$subgenre_hardRock.",".$subgenre_indieAlternative.",".$subgenre_psychedelicProgressiveRock.",".$subgenre_punk.",".$subgenre_rockAndRoll.",".$subgenre_popSoftRock.",".$subgenre_abstractIDMLeftfield.",".$subgenre_breakbeatDnB.",".$subgenre_deepHouse.",".$subgenre_electro.",".$subgenre_house.",".$subgenre_minimal.",".$subgenre_synthPop.",".$subgenre_techHouse.",".$subgenre_techno.",".$subgenre_trance.",".$subgenre_contemporaryRnB.",".$subgenre_gangsta.",".$subgenre_jazzyHipHop.",".$subgenre_popRap.",".$subgenre_trap.",".$subgenre_blackMetal.",".$subgenre_deathMetal.",".$subgenre_doomMetal.",".$subgenre_heavyMetal.",".$subgenre_metalcore.",".$subgenre_nuMetal.",".$subgenre_disco.",".$subgenre_funk.",".$subgenre_gospel.",".$subgenre_neoSoul.",".$subgenre_soul.",".$subgenre_bigBandSwing.",".$subgenre_bebop.",".$subgenre_contemporaryJazz.",".$subgenre_easyListening.",".$subgenre_fusion.",".$subgenre_latinJazz.",".$subgenre_smoothJazz.",".$subgenre_country.",".$subgenre_folk.")";


									$ins_result_segment_qry = "INSERT INTO `tbl_cyanite_result_segment`(`c_id`, `stimestamps`, `smood_aggressive`, `smood_calm`, `smood_chilled`, `smood_dark`, `smood_energetic`, `smood_epic`, `smood_happy`, `smood_romantic`, `smood_sad`, `smood_scary`, `smood_sexy`, `smood_ethereal`, `smood_uplifting`, `sgenre_ambient`, `sgenre_blues`, `sgenre_classical`, `sgenre_electronicDance`, `sgenre_folkCountry`, `sgenre_funkSoul`, `sgenre_jazz`, `sgenre_latin`, `sgenre_metal`, `sgenre_pop`, `sgenre_rapHipHop`, `sgenre_reggae`, `sgenre_rnb`, `sgenre_rock`, `sgenre_singerSongwriter`) VALUES(".$c_id.",'".$row['stimestamps']."','".$row['smood_aggressive']."','".$row['smood_calm']."','".$row['smood_chilled']."','".$row['smood_dark']."','".$row['smood_energetic']."','".$row['smood_epic']."','".$row['smood_happy']."','".$row['smood_romantic']."','".$row['smood_sad']."','".$row['smood_scary']."','".$row['smood_sexy']."','".$row['smood_ethereal']."','".$row['smood_uplifting']."','".$row['sgenre_ambient']."','".$row['sgenre_blues']."','".$row['sgenre_classical']."','".$row['sgenre_electronicDance']."','".$row['sgenre_folkCountry']."','".$row['sgenre_funkSoul']."','".$row['sgenre_jazz']."','".$row['sgenre_latin']."','".$row['sgenre_metal']."','".$row['sgenre_pop']."','".$row['sgenre_rapHipHop']."','".$row['sgenre_reggae']."','".$row['sgenre_rnb']."','".$row['sgenre_rock']."','".$row['sgenre_singerSongwriter']."')";



									$ins_result_character_segments_qry = '';

									$ins_result_character_segments_qry = "INSERT INTO `tbl_cyanite_result_character_segments`(`c_id`, `stimestamps`, `bold`, `cool`, `epic`, `ethereal`, `heroic`, `luxurious`, `magical`, `mysterious`, `playful`, `powerful`, `retro`, `sophisticated`, `sparkling`, `sparse`, `unpolished`, `warm`)  VALUES(".$c_id.",'".$row['stimestamps']."','".$row['bold']."','".$row['cool']."','".$row['epic']."','".$row['ethereal']."','".$row['heroic']."','".$row['luxurious']."','".$row['magical']."','".$row['mysterious']."','".$row['playful']."','".$row['powerful']."','".$row['retro']."','".$row['sophisticated']."','".$row['sparkling']."','".$row['sparse']."','".$row['unpolished']."','".$row['warm']."')";

									echo "ins_result_character_segments_qry---->".$ins_result_character_segments_qry."<br><br><br><br>";


									$ins_result_genre_segments_qry = '';

									$ins_result_genre_segments_qry = "INSERT INTO `tbl_cyanite_result_genre_segments`(`c_id`, `stimestamps`, `ambient`, `blues`, `classical`, `electronicDance`, `folkCountry`, `funkSoul`, `jazz`, `latin`, `metal`, `pop`, `rapHipHop`, `reggae`, `rnb`, `rock`, `singerSongwriter`)  VALUES(".$c_id.",'".$row['stimestamps']."','".$row['ambient']."','".$row['blues']."','".$row['classical']."','".$row['electronicDance']."','".$row['folkCountry']."','".$row['funkSoul']."','".$row['jazz']."','".$row['latin']."','".$row['metal']."','".$row['pop']."','".$row['rapHipHop']."','".$row['reggae']."','".$row['rnb']."','".$row['rock']."','".$row['singerSongwriter']."')";

									echo "ins_result_genre_segments_qry---->".$ins_result_genre_segments_qry."<br><br><br><br>";


									$ins_result_subgenre_segments_qry = '';

									$ins_result_subgenre_segments_qry = "INSERT INTO `tbl_cyanite_result_subgenre_segments`(`c_id`, `stimestamps`, `bluesRock`, `folkRock`, `hardRock`, `indieAlternative`, `psychedelicProgressiveRock`, `punk`, `rockAndRoll`, `popSoftRock`, `abstractIDMLeftfield`, `breakbeatDnB`, `deepHouse`, `electro`, `house`, `minimal`, `synthPop`, `techHouse`, `techno`, `trance`, `contemporaryRnB`, `gangsta`, `jazzyHipHop`, `popRap`, `trap`, `blackMetal`, `deathMetal`, `doomMetal`, `heavyMetal`, `metalcore`, `nuMetal`, `disco`, `funk`, `gospel`, `neoSoul`, `soul`, `bigBandSwing`, `bebop`, `contemporaryJazz`, `easyListening`, `fusion`, `latinJazz`, `smoothJazz`, `country`, `folk`) VALUES (".$c_id.",'".$row['stimestamps']."','".$row['bluesRock']."','".$row['folkRock']."','".$row['hardRock']."','".$row['indieAlternative']."','".$row['psychedelicProgressiveRock']."','".$row['punk']."','".$row['rockAndRoll']."','".$row['popSoftRock']."','".$row['abstractIDMLeftfield']."','".$row['breakbeatDnB']."','".$row['deepHouse']."','".$row['electro']."','".$row['house']."','".$row['minimal']."','".$row['synthPop']."','".$row['techHouse']."','".$row['techno']."','".$row['trance']."','".$row['contemporaryRnB']."','".$row['gangsta']."','".$row['jazzyHipHop']."','".$row['popRap']."','".$row['trap']."','".$row['blackMetal']."','".$row['deathMetal']."','".$row['doomMetal']."','".$row['heavyMetal']."','".$row['metalcore']."','".$row['nuMetal']."','".$row['disco']."','".$row['funk']."','".$row['gospel']."','".$row['neoSoul']."','".$row['soul']."','".$row['bigBandSwing']."','".$row['bebop']."','".$row['contemporaryJazz']."','".$row['easyListening']."','".$row['fusion']."','".$row['latinJazz']."','".$row['smoothJazz']."','".$row['country']."','".$row['folk']."')";

									echo "ins_result_subgenre_segments_qry---->".$ins_result_subgenre_segments_qry."<br><br><br><br>";


									$ins_result_moodadvanced_segments_qry = '';

									$ins_result_moodadvanced_segments_qry = "INSERT INTO `tbl_cyanite_result_moodadvanced_segments`(`c_id`, `stimestamps`, `anxious`, `barren`, `cold`, `creepy`, `dark`, `disturbing`, `eerie`, `evil`, `fearful`, `mysterious`, `nervous`, `restless`, `spooky`, `strange`, `supernatural`, `suspenseful`, `tense`, `weird`, `aggressive`, `agitated`, `angry`, `dangerous`, `fiery`, `intense`, `passionate`, `ponderous`, `violent`, `comedic`, `eccentric`, `funny`, `mischievous`, `quirky`, `whimsical`, `boisterous`, `boingy`, `bright`, `celebratory`, `cheerful`, `excited`, `feelGood`, `fun`, `happy`, `joyous`, `lighthearted`, `perky`, `playful`, `rollicking`, `upbeat`, `calm`, `contented`, `dreamy`, `introspective`, `laidBack`, `leisurely`, `lyrical`, `peaceful`, `quiet`, `relaxed`, `serene`, `soothing`, `spiritual`, `tranquil`, `bittersweet`, `blue`, `depressing`, `gloomy`, `heavy`, `lonely`, `melancholic`, `mournful`, `poignant`, `sad`, `frightening`, `horror`, `menacing`, `nightmarish`, `ominous`, `panicStricken`, `scary`, `concerned`, `determined`, `dignified`, `emotional`, `noble`, `serious`, `solemn`, `thoughtful`, `cool`, `seductive`, `sexy`, `adventurous`, `confident`, `courageous`, `resolute`, `energetic`, `epic`, `exciting`, `exhilarating`, `heroic`, `majestic`, `powerful`, `prestigious`, `relentless`, `strong`, `triumphant`, `victorious`, `delicate`, `graceful`, `hopeful`, `innocent`, `intimate`, `kind`, `light`, `loving`, `nostalgic`, `reflective`, `romantic`, `sentimental`, `soft`, `sweet`, `tender`, `warm`, `anthemic`, `aweInspiring`, `euphoric`, `inspirational`, `motivational`, `optimistic`, `positive`, `proud`, `soaring`, `uplifting`)  VALUES(".$c_id.",'".$row['stimestamps']."','".$row['anxious']."','".$row['barren']."','".$row['cold']."','".$row['creepy']."','".$row['dark']."','".$row['disturbing']."','".$row['eerie']."','".$row['evil']."','".$row['fearful']."','".$row['mysterious']."','".$row['nervous']."','".$row['restless']."','".$row['spooky']."','".$row['strange']."','".$row['supernatural']."','".$row['suspenseful']."','".$row['tense']."','".$row['weird']."','".$row['aggressive']."','".$row['agitated']."','".$row['angry']."','".$row['dangerous']."','".$row['fiery']."','".$row['intense']."','".$row['passionate']."','".$row['ponderous']."','".$row['violent']."','".$row['comedic']."','".$row['eccentric']."','".$row['funny']."','".$row['mischievous']."','".$row['quirky']."','".$row['whimsical']."','".$row['boisterous']."','".$row['boingy']."','".$row['bright']."','".$row['celebratory']."','".$row['cheerful']."','".$row['excited']."','".$row['feelGood']."','".$row['fun']."','".$row['happy']."','".$row['joyous']."','".$row['lighthearted']."','".$row['perky']."','".$row['playful']."','".$row['rollicking']."','".$row['upbeat']."','".$row['calm']."','".$row['contented']."','".$row['dreamy']."','".$row['introspective']."','".$row['laidBack']."','".$row['leisurely']."','".$row['lyrical']."','".$row['peaceful']."','".$row['quiet']."','".$row['relaxed']."','".$row['serene']."','".$row['soothing']."','".$row['spiritual']."','".$row['tranquil']."','".$row['bittersweet']."','".$row['blue']."','".$row['depressing']."','".$row['gloomy']."','".$row['heavy']."','".$row['lonely']."','".$row['melancholic']."','".$row['mournful']."','".$row['poignant']."','".$row['sad']."','".$row['frightening']."','".$row['horror']."','".$row['menacing']."','".$row['nightmarish']."','".$row['ominous']."','".$row['panicStricken']."','".$row['scary']."','".$row['concerned']."','".$row['determined']."','".$row['dignified']."','".$row['emotional']."','".$row['noble']."','".$row['serious']."','".$row['solemn']."','".$row['thoughtful']."','".$row['cool']."','".$row['seductive']."','".$row['sexy']."','".$row['adventurous']."','".$row['confident']."','".$row['courageous']."','".$row['resolute']."','".$row['energetic']."','".$row['epic']."','".$row['exciting']."','".$row['exhilarating']."','".$row['heroic']."','".$row['majestic']."','".$row['powerful']."','".$row['prestigious']."','".$row['relentless']."','".$row['strong']."','".$row['triumphant']."','".$row['victorious']."','".$row['delicate']."','".$row['graceful']."','".$row['hopeful']."','".$row['innocent']."','".$row['intimate']."','".$row['kind']."','".$row['light']."','".$row['loving']."','".$row['nostalgic']."','".$row['reflective']."','".$row['romantic']."','".$row['sentimental']."','".$row['soft']."','".$row['sweet']."','".$row['tender']."','".$row['warm']."','".$row['anthemic']."','".$row['aweInspiring']."','".$row['euphoric']."','".$row['inspirational']."','".$row['motivational']."','".$row['optimistic']."','".$row['positive']."','".$row['proud']."','".$row['soaring']."','".$row['uplifting']."')";

									echo "ins_result_moodadvanced_segments_qry---->".$ins_result_moodadvanced_segments_qry."<br><br><br><br>";


									$ins_result_movement_segments_qry = '';

									$ins_result_movement_segments_qry = "INSERT INTO `tbl_cyanite_result_movement_segments`(`c_id`, `stimestamps`, `bouncy`, `driving`, `flowing`, `groovy`, `nonrhythmic`, `pulsing`, `robotic`, `running`, `steady`, `stomping`)  VALUES(".$c_id.",'".$row['stimestamps']."','".$row['bouncy']."','".$row['driving']."','".$row['flowing']."','".$row['groovy']."','".$row['nonrhythmic']."','".$row['pulsing']."','".$row['robotic']."','".$row['running']."','".$row['steady']."','".$row['stomping']."')";

									echo "ins_result_movement_segments_qry---->".$ins_result_movement_segments_qry."<br><br><br><br>*****************************************************************<br>";

								if ($conn->query($ins_result_qry) === TRUE)
								{
									if ($conn->query($ins_result_part2_qry) === TRUE)
									{
										if ($conn->query($ins_result_segment_qry) === TRUE)
										{
											if ($conn->query($ins_result_character_segments_qry) === TRUE)
											{
												if ($conn->query($ins_result_genre_segments_qry) === TRUE)
												{
													if ($conn->query($ins_result_subgenre_segments_qry) === TRUE)
													{
														if ($conn->query($ins_result_moodadvanced_segments_qry) === TRUE)
														{
															if ($conn->query($ins_result_movement_segments_qry) === TRUE)
															{
																if($segment_timestamps != '')
																{
																	echo "UPDATE `tbl_assets` SET `segment_timestamps`='".$segment_timestamps."'' WHERE `c_id` = ".$c_id."<br><br>";
																	if ($conn->query("UPDATE `tbl_assets` SET `segment_timestamps`='".$segment_timestamps."' WHERE `c_id` = ".$c_id) === TRUE)
																	{
																		echo "segment_timestamps succesfully updated for asset mapped to cid->".$c_id." into assets table";
																		// //error_log("segment_timestamps succesfully updated for asset mapped to cid->".$c_id." into assets table");
																	}
																	else
																	{
																		echo "Error occured while updating segment_timestamps for asset mapped to cid->".$c_id." into assets table";
																		// //error_log("Error occured while updating segment_timestamps for asset mapped to cid->".$c_id." into assets table");
																	}

																}

																if ($conn->query("UPDATE `tbl_cyanite` SET `extraction_status`= 1 WHERE `c_id` =".$c_id) === TRUE)
																{
																	echo "Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table";
																	// //error_log("Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table");
																	$obj = new db_dump();
																	$updt_status = $obj->update_analysis_status($c_id);
																	echo "Data inserted into cyanite json table for c_id->".$c_id;
																	// //error_log("Data inserted into cyanite json table for c_id->".$c_id);
																	echo "updt_status".$updt_status;
																	//$return_data = 1;
																	if($updt_status == 1)
																	{
																		//extract_required_cyanite_data();
																		$return_data = 1; // success
																		
																	}
																	else
																	{
																		$return_data = 0;
																	}
																}
																else
																{
																	echo "Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table";
																	////error_log("Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table");
																}
															}
															else
															{
																echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result movement segment table";
																////error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result movement segment table");
															}
														}
														else
														{
															echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result moodadvanced segment table";
															////error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result moodadvanced segment table");
														}
													}
													else
													{
														echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result subgenre segment table";
														//error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result subgenre segment table");
													}
												}
												else
												{
													echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result genre segment table";
													//error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result genre segment table");
												}
											}
											else
											{
												echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result character segment table";
												//error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result character segment table");
											}
										}
										else
										{
											echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result segment table";
											//error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result segment table");
										}
									}
									else
									{
										echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result part2 table";
										//error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result part2 table");
									}
								}
								else
								{
									echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result table";
									//error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result table");
								}

							}

							/*$get_cid_qry = "SELECT * FROM `tbl_cyanite` WHERE is_active = 0 AND extraction_status = 0";
							$get_cid_qry_res = $conn->query($get_cid_qry);

							if($get_cid_qry_res->num_rows > 0)
							{
							  while($get_cid_qry_res_row = $get_cid_qry_res->fetch_assoc())
							  {
							  	$sql_paras = "SELECT 
								tbl_cyanite.cy_energylevel,
								tbl_cyanite.cy_emotionalprofile,
								json_extract(tbl_cyanite.cy_bpmprediction, '$.value') AS cy_bpm,
								json_extract(tbl_cyanite.cy_keyprediction, '$.value') AS cy_key,

								json_extract(tbl_cyanite.cy_mood, '$.aggressive') AS mood_aggressive,
								json_extract(tbl_cyanite.cy_mood, '$.calm') AS mood_calm,
								json_extract(tbl_cyanite.cy_mood, '$.chilled') AS mood_chilled,
								json_extract(tbl_cyanite.cy_mood, '$.dark') AS mood_dark,
								json_extract(tbl_cyanite.cy_mood, '$.energetic') AS mood_energetic,
								json_extract(tbl_cyanite.cy_mood, '$.epic') AS mood_epic,
								json_extract(tbl_cyanite.cy_mood, '$.happy') AS mood_happy,
								json_extract(tbl_cyanite.cy_mood, '$.romantic') AS mood_romantic,
								json_extract(tbl_cyanite.cy_mood, '$.sad') AS mood_sad,
								json_extract(tbl_cyanite.cy_mood, '$.scary') AS mood_scary,
								json_extract(tbl_cyanite.cy_mood, '$.sexy') AS mood_sexy,
								json_extract(tbl_cyanite.cy_mood, '$.ethereal') AS mood_ethereal,
								json_extract(tbl_cyanite.cy_mood, '$.uplifting') AS mood_uplifting,

								json_extract(tbl_cyanite.cy_moodAdvanced, '$.anxious') AS mooda_anxious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.barren') AS mooda_barren,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.cold') AS mooda_cold,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.creepy') AS mooda_creepy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dark') AS mooda_dark,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.disturbing') AS mooda_disturbing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.eerie') AS mooda_eerie,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.evil') AS mooda_evil,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.fearful') AS mooda_fearful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.mysterious') AS mooda_mysterious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.nervous') AS mooda_nervous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.restless') AS mooda_restless,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.spooky') AS mooda_spooky,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.strange') AS mooda_strange,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.supernatural') AS mooda_supernatural,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.suspenseful') AS mooda_suspenseful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.tense') AS mooda_tense,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.weird') AS mooda_weird,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.aggressive') AS mooda_aggressive,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.agitated') AS mooda_agitated,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.angry') AS mooda_angry,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dangerous') AS mooda_dangerous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.fiery') AS mooda_fiery,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.intense') AS mooda_intense,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.passionate') AS mooda_passionate,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.ponderous') AS mooda_ponderous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.violent') AS mooda_violent,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.comedic') AS mooda_comedic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.eccentric') AS mooda_eccentric,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.funny') AS mooda_funny,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.mischievous') AS mooda_mischievous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.quirky') AS mooda_quirky,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.whimsical') AS mooda_whimsical,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.boisterous') AS mooda_boisterous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.boingy') AS mooda_boingy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.bright') AS mooda_bright,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.celebratory') AS mooda_celebratory,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.cheerful') AS mooda_cheerful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.excited') AS mooda_excited,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.feelGood') AS mooda_feelGood,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.fun') AS mooda_fun,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.happy') AS mooda_happy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.joyous') AS mooda_joyous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.lighthearted') AS mooda_lighthearted,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.perky') AS mooda_perky,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.playful') AS mooda_playful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.rollicking') AS mooda_rollicking,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.upbeat') AS mooda_upbeat,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.calm') AS mooda_calm,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.contented') AS mooda_contented,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dreamy') AS mooda_dreamy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.introspective') AS mooda_introspective,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.laidBack') AS mooda_laidBack,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.leisurely') AS mooda_leisurely,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.lyrical') AS mooda_lyrical,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.peaceful') AS mooda_peaceful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.quiet') AS mooda_quiet,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.relaxed') AS mooda_relaxed,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.serene') AS mooda_serene,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.soothing') AS mooda_soothing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.spiritual') AS mooda_spiritual,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.tranquil') AS mooda_tranquil,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.bittersweet') AS mooda_bittersweet,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.blue') AS mooda_blue,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.depressing') AS mooda_depressing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.gloomy') AS mooda_gloomy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.lonely') AS mooda_lonely,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.melancholic') AS mooda_melancholic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.mournful') AS mooda_mournful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.poignant') AS mooda_poignant,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sad') AS mooda_sad,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.frightening') AS mooda_frightening,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.menacing') AS mooda_menacing,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.nightmarish') AS mooda_nightmarish,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.ominous') AS mooda_ominous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.panicStricken') AS mooda_panicStricken,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.scary') AS mooda_scary,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.concerned') AS mooda_concerned,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.determined') AS mooda_determined,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.dignified') AS mooda_dignified,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.emotional') AS mooda_emotional,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.noble') AS mooda_noble,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.serious') AS mooda_serious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.solemn') AS mooda_solemn,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.thoughtful') AS mooda_thoughtful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.cool') AS mooda_cool,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.seductive') AS mooda_seductive,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sexy') AS mooda_sexy,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.adventurous') AS mooda_adventurous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.confident') AS mooda_confident,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.courageous') AS mooda_courageous,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.resolute') AS mooda_resolute,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.energetic') AS mooda_energetic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.epic') AS mooda_epic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.exciting') AS mooda_exciting,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.exhilarating') AS mooda_exhilarating,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.heroic') AS mooda_heroic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.majestic') AS mooda_majestic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.powerful') AS mooda_powerful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.prestigious') AS mooda_prestigious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.relentless') AS mooda_relentless,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.strong') AS mooda_strong,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.triumphant') AS mooda_triumphant,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.victorious') AS mooda_victorious,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.delicate') AS mooda_delicate,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.graceful') AS mooda_graceful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.hopeful') AS mooda_hopeful,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.innocent') AS mooda_innocent,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.intimate') AS mooda_intimate,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.kind') AS mooda_kind,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.light') AS mooda_light,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.loving') AS mooda_loving,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.nostalgic') AS mooda_nostalgic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.reflective') AS mooda_reflective,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.romantic') AS mooda_romantic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sentimental') AS mooda_sentimental,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.soft') AS mooda_soft,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.sweet') AS mooda_sweet,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.tender') AS mooda_tender,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.warm') AS mooda_warm,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.anthemic') AS mooda_anthemic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.aweInspiring') AS mooda_aweInspiring,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.euphoric') AS mooda_euphoric,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.inspirational') AS mooda_inspirational,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.motivational') AS mooda_motivational,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.optimistic') AS mooda_optimistic,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.positive') AS mooda_positive,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.proud') AS mooda_proud,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.soaring') AS mooda_soaring,
								json_extract(tbl_cyanite.cy_moodAdvanced, '$.uplifting') AS mooda_uplifting,


								json_extract(tbl_cyanite.cy_genre, '$.ambient') AS genre_ambient,
								json_extract(tbl_cyanite.cy_genre, '$.blues') AS genre_blues,
								json_extract(tbl_cyanite.cy_genre, '$.classical') AS genre_classical,
								-- json_extract(tbl_cyanite.cy_genre, '$.country') AS country,
								json_extract(tbl_cyanite.cy_genre, '$.electronicDance') AS genre_electronicDance,
								json_extract(tbl_cyanite.cy_genre, '$.folkCountry') AS genre_folkCountry,
								json_extract(tbl_cyanite.cy_genre, '$.funkSoul') AS genre_funkSoul,
								-- json_extract(tbl_cyanite.cy_genre, '$.indieAlternative') AS indieAlternative,
								json_extract(tbl_cyanite.cy_genre, '$.jazz') AS genre_jazz,
								json_extract(tbl_cyanite.cy_genre, '$.latin') AS genre_latin,
								json_extract(tbl_cyanite.cy_genre, '$.metal') AS genre_metal,
								json_extract(tbl_cyanite.cy_genre, '$.pop') AS genre_pop,
								json_extract(tbl_cyanite.cy_genre, '$.punk') AS genre_punk,
								json_extract(tbl_cyanite.cy_genre, '$.rapHipHop') AS genre_rapHipHop,
								json_extract(tbl_cyanite.cy_genre, '$.reggae') AS genre_reggae,
								json_extract(tbl_cyanite.cy_genre, '$.rnb') AS genre_rnb,
								json_extract(tbl_cyanite.cy_genre, '$.rock') AS genre_rock,
								json_extract(tbl_cyanite.cy_genre, '$.singerSongwriter') AS genre_singerSongwriter,

								json_extract(tbl_cyanite.cy_character, '$.bold') AS character_bold,
								json_extract(tbl_cyanite.cy_character, '$.cool') AS character_cool,
								json_extract(tbl_cyanite.cy_character, '$.epic') AS character_epic,
								json_extract(tbl_cyanite.cy_character, '$.ethereal') AS character_ethereal,
								json_extract(tbl_cyanite.cy_character, '$.heroic') AS character_heroic,
								json_extract(tbl_cyanite.cy_character, '$.luxurious') AS character_luxurious,
								json_extract(tbl_cyanite.cy_character, '$.magical') AS character_magical,
								json_extract(tbl_cyanite.cy_character, '$.mysterious') AS character_mysterious,
								json_extract(tbl_cyanite.cy_character, '$.playful') AS character_playful,
								json_extract(tbl_cyanite.cy_character, '$.powerful') AS character_powerful,
								json_extract(tbl_cyanite.cy_character, '$.retro') AS character_retro,
								json_extract(tbl_cyanite.cy_character, '$.sophisticated') AS character_sophisticated,
								json_extract(tbl_cyanite.cy_character, '$.sparkling') AS character_sparkling,
								json_extract(tbl_cyanite.cy_character, '$.sparse') AS character_sparse,
								json_extract(tbl_cyanite.cy_character, '$.unpolished') AS character_unpolished,
								json_extract(tbl_cyanite.cy_character, '$.warm') AS character_warm FROM tbl_cyanite where c_id=".$get_cid_qry_res_row['c_id']." and is_active=0";
								$result = $conn->query($sql_paras);
								while($row = $result->fetch_assoc())
								{
									$ins_result_qry = '';

									$ins_result_qry = "INSERT INTO `tbl_cyanite_result`(`c_id`, `energylevel`, `emotionalprofile`, `bpm`, `keyprediction`, `mood_aggressive`, `mood_calm`, `mood_chilled`, `mood_dark`, `mood_energetic`, `mood_epic`, `mood_happy`, `mood_romantic`, `mood_sad`, `mood_scary`, `mood_sexy`, `mood_ethereal`, `mood_uplifting`, `mooda_anxious`, `mooda_barren`, `mooda_cold`, `mooda_creepy`, `mooda_dark`, `mooda_disturbing`, `mooda_eerie`, `mooda_evil`, `mooda_fearful`, `mooda_mysterious`, `mooda_nervous`, `mooda_restless`, `mooda_spooky`, `mooda_strange`, `mooda_supernatural`, `mooda_suspenseful`, `mooda_tense`, `mooda_weird`, `mooda_aggressive`, `mooda_agitated`, `mooda_angry`, `mooda_dangerous`, `mooda_fiery`, `mooda_intense`, `mooda_passionate`, `mooda_ponderous`, `mooda_violent`, `mooda_comedic`, `mooda_eccentric`, `mooda_funny`, `mooda_mischievous`, `mooda_quirky`, `mooda_whimsical`, `mooda_boisterous`, `mooda_boingy`, `mooda_bright`, `mooda_celebratory`, `mooda_cheerful`, `mooda_excited`, `mooda_feelGood`, `mooda_fun`, `mooda_happy`, `mooda_joyous`, `mooda_lighthearted`, `mooda_perky`, `mooda_playful`, `mooda_rollicking`, `mooda_upbeat`, `mooda_calm`, `mooda_contented`, `mooda_dreamy`, `mooda_introspective`, `mooda_laidBack`, `mooda_leisurely`, `mooda_lyrical`, `mooda_peaceful`, `mooda_quiet`, `mooda_relaxed`, `mooda_serene`, `mooda_soothing`, `mooda_spiritual`, `mooda_tranquil`, `mooda_bittersweet`, `mooda_blue`, `mooda_depressing`, `mooda_gloomy`, `mooda_lonely`, `mooda_melancholic`, `mooda_mournful`, `mooda_poignant`, `mooda_sad`, `mooda_frightening`, `mooda_menacing`, `mooda_nightmarish`, `mooda_ominous`, `mooda_panicStricken`, `mooda_scary`, `mooda_concerned`, `mooda_determined`, `mooda_dignified`, `mooda_emotional`, `mooda_noble`, `mooda_serious`, `mooda_solemn`, `mooda_thoughtful`, `mooda_cool`, `mooda_seductive`, `mooda_sexy`, `mooda_adventurous`, `mooda_confident`, `mooda_courageous`, `mooda_resolute`, `mooda_energetic`, `mooda_epic`, `mooda_exciting`, `mooda_exhilarating`, `mooda_heroic`, `mooda_majestic`, `mooda_powerful`, `mooda_prestigious`, `mooda_relentless`, `mooda_strong`, `mooda_triumphant`, `mooda_victorious`, `mooda_delicate`, `mooda_graceful`, `mooda_hopeful`, `mooda_innocent`, `mooda_intimate`, `mooda_kind`, `mooda_light`, `mooda_loving`, `mooda_nostalgic`, `mooda_reflective`, `mooda_romantic`, `mooda_sentimental`, `mooda_soft`, `mooda_sweet`, `mooda_tender`, `mooda_warm`, `mooda_anthemic`, `mooda_aweInspiring`, `mooda_euphoric`, `mooda_inspirational`, `mooda_motivational`, `mooda_optimistic`, `mooda_positive`, `mooda_proud`, `mooda_soaring`, `mooda_uplifting`, `genre_ambient`, `genre_blues`, `genre_classical`, `genre_electronicDance`, `genre_folkCountry`, `genre_funkSoul`, `genre_jazz`, `genre_latin`, `genre_metal`, `genre_pop`, `genre_punk`, `genre_rapHipHop`, `genre_reggae`, `genre_rnb`, `genre_rock`, `genre_singerSongwriter`, `character_bold`, `character_cool`, `character_epic`, `character_ethereal`, `character_heroic`, `character_luxurious`, `character_magical`, `character_mysterious`, `character_playful`, `character_powerful`, `character_retro`, `character_sophisticated`, `character_sparkling`, `character_sparse`, `character_unpolished`, `character_warm`) VALUES (".$c_id.",'".$row['cy_energylevel']."','".$row['cy_emotionalprofile']."','".$row['cy_bpm']."','".$row['cy_key']."','".$row['mood_aggressive']."','".$row['mood_calm']."','".$row['mood_chilled']."','".$row['mood_dark']."','".$row['mood_energetic']."','".$row['mood_epic']."','".$row['mood_happy']."','".$row['mood_romantic']."','".$row['mood_sad']."','".$row['mood_scary']."','".$row['mood_sexy']."','".$row['mood_ethereal']."','".$row['mood_uplifting']."','".$row['mooda_anxious']."','".$row['mooda_barren']."','".$row['mooda_cold']."','".$row['mooda_creepy']."','".$row['mooda_dark']."','".$row['mooda_disturbing']."','".$row['mooda_eerie']."','".$row['mooda_evil']."','".$row['mooda_fearful']."','".$row['mooda_mysterious']."','".$row['mooda_nervous']."','".$row['mooda_restless']."','".$row['mooda_spooky']."','".$row['mooda_strange']."','".$row['mooda_supernatural']."','".$row['mooda_suspenseful']."','".$row['mooda_tense']."','".$row['mooda_weird']."','".$row['mooda_aggressive']."','".$row['mooda_agitated']."','".$row['mooda_angry']."','".$row['mooda_dangerous']."','".$row['mooda_fiery']."','".$row['mooda_intense']."','".$row['mooda_passionate']."','".$row['mooda_ponderous']."','".$row['mooda_violent']."','".$row['mooda_comedic']."','".$row['mooda_eccentric']."','".$row['mooda_funny']."','".$row['mooda_mischievous']."','".$row['mooda_quirky']."','".$row['mooda_whimsical']."','".$row['mooda_boisterous']."','".$row['mooda_boingy']."','".$row['mooda_bright']."','".$row['mooda_celebratory']."','".$row['mooda_cheerful']."','".$row['mooda_excited']."','".$row['mooda_feelGood']."','".$row['mooda_fun']."','".$row['mooda_happy']."','".$row['mooda_joyous']."','".$row['mooda_lighthearted']."','".$row['mooda_perky']."','".$row['mooda_playful']."','".$row['mooda_rollicking']."','".$row['mooda_upbeat']."','".$row['mooda_calm']."','".$row['mooda_contented']."','".$row['mooda_dreamy']."','".$row['mooda_introspective']."','".$row['mooda_laidBack']."','".$row['mooda_leisurely']."','".$row['mooda_lyrical']."','".$row['mooda_peaceful']."','".$row['mooda_quiet']."','".$row['mooda_relaxed']."','".$row['mooda_serene']."','".$row['mooda_soothing']."','".$row['mooda_spiritual']."','".$row['mooda_tranquil']."','".$row['mooda_bittersweet']."','".$row['mooda_blue']."','".$row['mooda_depressing']."','".$row['mooda_gloomy']."','".$row['mooda_lonely']."','".$row['mooda_melancholic']."','".$row['mooda_mournful']."','".$row['mooda_poignant']."','".$row['mooda_sad']."','".$row['mooda_frightening']."','".$row['mooda_menacing']."','".$row['mooda_nightmarish']."','".$row['mooda_ominous']."','".$row['mooda_panicStricken']."','".$row['mooda_scary']."','".$row['mooda_concerned']."','".$row['mooda_determined']."','".$row['mooda_dignified']."','".$row['mooda_emotional']."','".$row['mooda_noble']."','".$row['mooda_serious']."','".$row['mooda_solemn']."','".$row['mooda_thoughtful']."','".$row['mooda_cool']."','".$row['mooda_seductive']."','".$row['mooda_sexy']."','".$row['mooda_adventurous']."','".$row['mooda_confident']."','".$row['mooda_courageous']."','".$row['mooda_resolute']."','".$row['mooda_energetic']."','".$row['mooda_epic']."','".$row['mooda_exciting']."','".$row['mooda_exhilarating']."','".$row['mooda_heroic']."','".$row['mooda_majestic']."','".$row['mooda_powerful']."','".$row['mooda_prestigious']."','".$row['mooda_relentless']."','".$row['mooda_strong']."','".$row['mooda_triumphant']."','".$row['mooda_victorious']."','".$row['mooda_delicate']."','".$row['mooda_graceful']."','".$row['mooda_hopeful']."','".$row['mooda_innocent']."','".$row['mooda_intimate']."','".$row['mooda_kind']."','".$row['mooda_light']."','".$row['mooda_loving']."','".$row['mooda_nostalgic']."','".$row['mooda_reflective']."','".$row['mooda_romantic']."','".$row['mooda_sentimental']."','".$row['mooda_soft']."','".$row['mooda_sweet']."','".$row['mooda_tender']."','".$row['mooda_warm']."','".$row['mooda_anthemic']."','".$row['mooda_aweInspiring']."','".$row['mooda_euphoric']."','".$row['mooda_inspirational']."','".$row['mooda_motivational']."','".$row['mooda_optimistic']."','".$row['mooda_positive']."','".$row['mooda_proud']."','".$row['mooda_soaring']."','".$row['mooda_uplifting']."','".$row['genre_ambient']."','".$row['genre_blues']."','".$row['genre_classical']."','".$row['genre_electronicDance']."','".$row['genre_folkCountry']."','".$row['genre_funkSoul']."','".$row['genre_jazz']."','".$row['genre_latin']."','".$row['genre_metal']."','".$row['genre_pop']."','".$row['genre_punk']."','".$row['genre_rapHipHop']."','".$row['genre_reggae']."','".$row['genre_rnb']."','".$row['genre_rock']."','".$row['genre_singerSongwriter']."','".$row['character_bold']."','".$row['character_cool']."','".$row['character_epic']."','".$row['character_ethereal']."','".$row['character_heroic']."','".$row['character_luxurious']."','".$row['character_magical']."','".$row['character_mysterious']."','".$row['character_playful']."','".$row['character_powerful']."','".$row['character_retro']."','".$row['character_sophisticated']."','".$row['character_sparkling']."','".$row['character_sparse']."','".$row['character_unpolished']."','".$row['character_warm']."')";

										echo "ins_result_qry---->".$ins_result_qry."<br><br><br><br>";

									if ($conn->query($ins_result_qry) === TRUE)
									{
										if ($conn->query("UPDATE `tbl_cyanite` SET `extraction_status`= 1 WHERE `c_id` =".$c_id) === TRUE)
										{
											echo "Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table";
											//error_log("Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table");
											$obj = new db_dump();
											$updt_status = $obj->update_analysis_status($c_id);
											echo "Data inserted into cyanite json table for c_id->".$c_id;
											//error_log("Data inserted into cyanite json table for c_id->".$c_id);
											echo "updt_status".$updt_status;
											//$return_data = 1;
											if($updt_status == 1)
											{
												//extract_required_cyanite_data();
												$return_data = 1; // success
												
											}
											else
											{
												$return_data = 0;
											}
										}
										else
										{
											echo "Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table";
											//error_log("Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table");
										}
									}
									else
									{
										echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result table";
										//error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result table");
									}

								}

							  }
							}
							else
							{
							  //error_log("No data found for extraction");
							}*/
						}
						catch(Exception $e)
						{
							//error_log("page : [db_dump] : function [extract_mood_and_genere] : error : ".$e->getMessage());
							$sonic_functions->trigger_log_email("db_dump","extract_mood_and_genere",$e->getMessage());
						}
?>