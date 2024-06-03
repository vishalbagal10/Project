<?php

require_once('sonic_functions.php');

class db_dump
{
	function fetch_and_dump_analised_record($track_id, $c_id, $cyanite_key){		
		$sonic_functions = new sonic_functions();
		error_log("Fetching analised data for track id->".$track_id." of ".$c_id);
		$dbcon = include('../config.php');
		$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);
		try{
			//error_log($_SERVER["DOCUMENT_ROOT"].'/cynite_apis.php');
			//include $_SERVER["DOCUMENT_ROOT"].'/cynite_apis.php';
			// include $_SERVER['DOCUMENT_ROOT'] .'/apis/cyanite_apis/cynite_apis.php';

			$cynite_api = new CyaniteAI();

			$json_str = json_decode($cynite_api->Fetch_AnyalisedData($track_id, $cyanite_key));
			//echo "response-------------------";
			//print_r($json_str);

			$analysis_status = $json_str ->data ->libraryTrack ->audioAnalysisV6 ->__typename;

			if ($analysis_status == "AudioAnalysisV6Finished") {

				$c_id 				= $c_id;
				$track_id 			= $json_str ->data ->libraryTrack ->id;
				$valence 			= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->valence;
				$arousal 			= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->arousal;
				$energylevel 		= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->energyLevel;
				$emotionalprofile 	= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->emotionalProfile;
				$bpmprediction 		= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->bpmPrediction );
				$bpmrangeadjusted 	= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->bpmRangeAdjusted;
				$keyprediction 		= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->keyPrediction );
				$timesignature 		= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->timeSignature );

				$mood 				= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->mood );
				$moodtags 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->moodTags );
				$moodadvanced 		= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->moodAdvanced );
				$moodadvancedtags 	= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->moodAdvancedTags );
				$genre 				= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->genre );
				$genretags 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->genreTags );
				$character 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->character );
				$charactertags 		= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->characterTags );
				$voice 				= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->voice );
				$instrumentpresence = json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->instrumentPresence );
				$instrumenttags 	= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->instrumentTags );	
				$subgenre 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->subgenre );	
				$movement 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->movement );	
				$segments 			= json_encode( $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->segments );
				$cyanitejson 		= json_encode($json_str);

				if($json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->voiceoverExists === false || $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->voiceoverExists == false)
				{
					$voiceover_exists = 0;
				}
				else
				{
					$voiceover_exists = 1;
				}
				//$voiceover_exists	= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->voiceoverExists;
				$voiceover_degree	= $json_str ->data ->libraryTrack ->audioAnalysisV6 ->result ->voiceoverDegree;

				$status 			= "synced";
				$version 			= "6";

				

				$sql = "UPDATE `tbl_cyanite` SET `cy_valence`='".$valence."',`cy_arousal`='".$arousal."',`cy_energylevel`='".$energylevel."',`cy_emotionalprofile`='".$emotionalprofile."',`cy_bpmprediction`='".$bpmprediction."',`cy_bpmrangeadjusted`='".$bpmrangeadjusted."',`cy_keyprediction`='".$keyprediction."',`cy_timesignature`='".$timesignature."',`cy_mood`='".$mood."',`cy_moodtags`='".$moodtags."',`cy_moodadvanced`='".$moodadvanced."',`moodadvancedtags`='".$moodadvancedtags."',`cy_genre`='".$genre."',`cy_genretags`='".$genretags."',`cy_character`='".$character."',`cy_charactertags`='".$charactertags."',`cy_voice`='".$voice."',`cy_instrumentpresence`='".$instrumentpresence."',`cy_instrumenttags`='".$instrumenttags."',`cy_subgenre`='".$subgenre."',`cy_movement`='".$movement."',`cy_segments`='".$segments."',`cy_voiceover_exists`='".$voiceover_exists."',`cy_voiceover_degree`='".$voiceover_degree."' WHERE `c_id` =".$c_id." AND `track_id` =".$track_id;
				
				
				if ($conn->query($sql) === TRUE)
				{
					if($conn->query("INSERT INTO `tbl_cynite_json`(`c_id`, `cyanitejson`, `status`, `version`) VALUES ($c_id,'".str_replace("'","\'",$cyanitejson)."','".$status."','6')") === TRUE)
              		{
              			echo "Extracting data from cyanite table";
						error_log("Extracting data from cyanite table");
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
								json_extract(tbl_cyanite.cy_segments, '$.movement.stomping') AS stomping FROM tbl_cyanite where c_id=".$c_id." and is_active=0";
							$result = $conn->query($sql_paras);
							while($row = $result->fetch_assoc())
							{
								$segment_timestamps = $row['stimestamps'];
								
								$ins_result_qry = '';

								$ins_result_segment_qry = '';

								$ins_result_qry = "INSERT INTO `tbl_cyanite_result`(`c_id`, `energylevel`, `emotionalprofile`, `bpm`, `keyprediction`, `timesignature`, `mood_aggressive`, `mood_calm`, `mood_chilled`, `mood_dark`, `mood_energetic`, `mood_epic`, `mood_happy`, `mood_romantic`, `mood_sad`, `mood_scary`, `mood_sexy`, `mood_ethereal`, `mood_uplifting`, `mooda_anxious`, `mooda_barren`, `mooda_cold`, `mooda_creepy`, `mooda_dark`, `mooda_disturbing`, `mooda_eerie`, `mooda_evil`, `mooda_fearful`, `mooda_mysterious`, `mooda_nervous`, `mooda_restless`, `mooda_spooky`, `mooda_strange`, `mooda_supernatural`, `mooda_suspenseful`, `mooda_tense`, `mooda_weird`, `mooda_aggressive`, `mooda_agitated`, `mooda_angry`, `mooda_dangerous`, `mooda_fiery`, `mooda_intense`, `mooda_passionate`, `mooda_ponderous`, `mooda_violent`, `mooda_comedic`, `mooda_eccentric`, `mooda_funny`, `mooda_mischievous`, `mooda_quirky`, `mooda_whimsical`, `mooda_boisterous`, `mooda_boingy`, `mooda_bright`, `mooda_celebratory`, `mooda_cheerful`, `mooda_excited`, `mooda_feelGood`, `mooda_fun`, `mooda_happy`, `mooda_joyous`, `mooda_lighthearted`, `mooda_perky`, `mooda_playful`, `mooda_rollicking`, `mooda_upbeat`, `mooda_calm`, `mooda_contented`, `mooda_dreamy`, `mooda_introspective`, `mooda_laidBack`, `mooda_leisurely`, `mooda_lyrical`, `mooda_peaceful`, `mooda_quiet`, `mooda_relaxed`, `mooda_serene`, `mooda_soothing`, `mooda_spiritual`, `mooda_tranquil`, `mooda_bittersweet`, `mooda_blue`, `mooda_depressing`, `mooda_gloomy`, `mooda_lonely`, `mooda_melancholic`, `mooda_mournful`, `mooda_poignant`, `mooda_sad`, `mooda_frightening`, `mooda_menacing`, `mooda_nightmarish`, `mooda_ominous`, `mooda_panicStricken`, `mooda_scary`, `mooda_concerned`, `mooda_determined`, `mooda_dignified`, `mooda_emotional`, `mooda_noble`, `mooda_serious`, `mooda_solemn`, `mooda_thoughtful`, `mooda_cool`, `mooda_seductive`, `mooda_sexy`, `mooda_adventurous`, `mooda_confident`, `mooda_courageous`, `mooda_resolute`, `mooda_energetic`, `mooda_epic`, `mooda_exciting`, `mooda_exhilarating`, `mooda_heroic`, `mooda_majestic`, `mooda_powerful`, `mooda_prestigious`, `mooda_relentless`, `mooda_strong`, `mooda_triumphant`, `mooda_victorious`, `mooda_delicate`, `mooda_graceful`, `mooda_hopeful`, `mooda_innocent`, `mooda_intimate`, `mooda_kind`, `mooda_light`, `mooda_loving`, `mooda_nostalgic`, `mooda_reflective`, `mooda_romantic`, `mooda_sentimental`, `mooda_soft`, `mooda_sweet`, `mooda_tender`, `mooda_warm`, `mooda_anthemic`, `mooda_aweInspiring`, `mooda_euphoric`, `mooda_inspirational`, `mooda_motivational`, `mooda_optimistic`, `mooda_positive`, `mooda_proud`, `mooda_soaring`, `mooda_uplifting`, `genre_ambient`, `genre_blues`, `genre_classical`, `genre_electronicDance`, `genre_folkCountry`, `genre_funkSoul`, `genre_jazz`, `genre_latin`, `genre_metal`, `genre_pop`, `genre_punk`, `genre_rapHipHop`, `genre_reggae`, `genre_rnb`, `genre_rock`, `genre_singerSongwriter`, `character_bold`, `character_cool`, `character_epic`, `character_ethereal`, `character_heroic`, `character_luxurious`, `character_magical`, `character_mysterious`, `character_playful`, `character_powerful`, `character_retro`, `character_sophisticated`, `character_sparkling`, `character_sparse`, `character_unpolished`, `character_warm`, `movement_bouncy`, `movement_driving`, `movement_flowing`, `movement_groovy`, `movement_nonrhythmic`, `movement_pulsing`, `movement_robotic`, `movement_running`, `movement_steady`, `movement_stomping`) VALUES (".$c_id.",'".$row['cy_energylevel']."','".$row['cy_emotionalprofile']."','".$row['cy_bpm']."','".$row['cy_key']."','".$row['cy_timesignature']."','".$row['mood_aggressive']."','".$row['mood_calm']."','".$row['mood_chilled']."','".$row['mood_dark']."','".$row['mood_energetic']."','".$row['mood_epic']."','".$row['mood_happy']."','".$row['mood_romantic']."','".$row['mood_sad']."','".$row['mood_scary']."','".$row['mood_sexy']."','".$row['mood_ethereal']."','".$row['mood_uplifting']."','".$row['mooda_anxious']."','".$row['mooda_barren']."','".$row['mooda_cold']."','".$row['mooda_creepy']."','".$row['mooda_dark']."','".$row['mooda_disturbing']."','".$row['mooda_eerie']."','".$row['mooda_evil']."','".$row['mooda_fearful']."','".$row['mooda_mysterious']."','".$row['mooda_nervous']."','".$row['mooda_restless']."','".$row['mooda_spooky']."','".$row['mooda_strange']."','".$row['mooda_supernatural']."','".$row['mooda_suspenseful']."','".$row['mooda_tense']."','".$row['mooda_weird']."','".$row['mooda_aggressive']."','".$row['mooda_agitated']."','".$row['mooda_angry']."','".$row['mooda_dangerous']."','".$row['mooda_fiery']."','".$row['mooda_intense']."','".$row['mooda_passionate']."','".$row['mooda_ponderous']."','".$row['mooda_violent']."','".$row['mooda_comedic']."','".$row['mooda_eccentric']."','".$row['mooda_funny']."','".$row['mooda_mischievous']."','".$row['mooda_quirky']."','".$row['mooda_whimsical']."','".$row['mooda_boisterous']."','".$row['mooda_boingy']."','".$row['mooda_bright']."','".$row['mooda_celebratory']."','".$row['mooda_cheerful']."','".$row['mooda_excited']."','".$row['mooda_feelGood']."','".$row['mooda_fun']."','".$row['mooda_happy']."','".$row['mooda_joyous']."','".$row['mooda_lighthearted']."','".$row['mooda_perky']."','".$row['mooda_playful']."','".$row['mooda_rollicking']."','".$row['mooda_upbeat']."','".$row['mooda_calm']."','".$row['mooda_contented']."','".$row['mooda_dreamy']."','".$row['mooda_introspective']."','".$row['mooda_laidBack']."','".$row['mooda_leisurely']."','".$row['mooda_lyrical']."','".$row['mooda_peaceful']."','".$row['mooda_quiet']."','".$row['mooda_relaxed']."','".$row['mooda_serene']."','".$row['mooda_soothing']."','".$row['mooda_spiritual']."','".$row['mooda_tranquil']."','".$row['mooda_bittersweet']."','".$row['mooda_blue']."','".$row['mooda_depressing']."','".$row['mooda_gloomy']."','".$row['mooda_lonely']."','".$row['mooda_melancholic']."','".$row['mooda_mournful']."','".$row['mooda_poignant']."','".$row['mooda_sad']."','".$row['mooda_frightening']."','".$row['mooda_menacing']."','".$row['mooda_nightmarish']."','".$row['mooda_ominous']."','".$row['mooda_panicStricken']."','".$row['mooda_scary']."','".$row['mooda_concerned']."','".$row['mooda_determined']."','".$row['mooda_dignified']."','".$row['mooda_emotional']."','".$row['mooda_noble']."','".$row['mooda_serious']."','".$row['mooda_solemn']."','".$row['mooda_thoughtful']."','".$row['mooda_cool']."','".$row['mooda_seductive']."','".$row['mooda_sexy']."','".$row['mooda_adventurous']."','".$row['mooda_confident']."','".$row['mooda_courageous']."','".$row['mooda_resolute']."','".$row['mooda_energetic']."','".$row['mooda_epic']."','".$row['mooda_exciting']."','".$row['mooda_exhilarating']."','".$row['mooda_heroic']."','".$row['mooda_majestic']."','".$row['mooda_powerful']."','".$row['mooda_prestigious']."','".$row['mooda_relentless']."','".$row['mooda_strong']."','".$row['mooda_triumphant']."','".$row['mooda_victorious']."','".$row['mooda_delicate']."','".$row['mooda_graceful']."','".$row['mooda_hopeful']."','".$row['mooda_innocent']."','".$row['mooda_intimate']."','".$row['mooda_kind']."','".$row['mooda_light']."','".$row['mooda_loving']."','".$row['mooda_nostalgic']."','".$row['mooda_reflective']."','".$row['mooda_romantic']."','".$row['mooda_sentimental']."','".$row['mooda_soft']."','".$row['mooda_sweet']."','".$row['mooda_tender']."','".$row['mooda_warm']."','".$row['mooda_anthemic']."','".$row['mooda_aweInspiring']."','".$row['mooda_euphoric']."','".$row['mooda_inspirational']."','".$row['mooda_motivational']."','".$row['mooda_optimistic']."','".$row['mooda_positive']."','".$row['mooda_proud']."','".$row['mooda_soaring']."','".$row['mooda_uplifting']."','".$row['genre_ambient']."','".$row['genre_blues']."','".$row['genre_classical']."','".$row['genre_electronicDance']."','".$row['genre_folkCountry']."','".$row['genre_funkSoul']."','".$row['genre_jazz']."','".$row['genre_latin']."','".$row['genre_metal']."','".$row['genre_pop']."','".$row['genre_punk']."','".$row['genre_rapHipHop']."','".$row['genre_reggae']."','".$row['genre_rnb']."','".$row['genre_rock']."','".$row['genre_singerSongwriter']."','".$row['character_bold']."','".$row['character_cool']."','".$row['character_epic']."','".$row['character_ethereal']."','".$row['character_heroic']."','".$row['character_luxurious']."','".$row['character_magical']."','".$row['character_mysterious']."','".$row['character_playful']."','".$row['character_powerful']."','".$row['character_retro']."','".$row['character_sophisticated']."','".$row['character_sparkling']."','".$row['character_sparse']."','".$row['character_unpolished']."','".$row['character_warm']."','".$row['movement_bouncy']."','".$row['movement_driving']."','".$row['movement_flowing']."','".$row['movement_groovy']."','".$row['movement_nonrhythmic']."','".$row['movement_pulsing']."','".$row['movement_robotic']."','".$row['movement_running']."','".$row['movement_steady']."','".$row['movement_stomping']."')";


									$ins_result_part2_qry = "INSERT INTO `tbl_cyanite_result_part2`(`c_id`, `subgenre_bluesRock`, `subgenre_folkRock`, `subgenre_hardRock`, `subgenre_indieAlternative`, `subgenre_psychedelicProgressiveRock`, `subgenre_punk`, `subgenre_rockAndRoll`, `subgenre_popSoftRock`, `subgenre_abstractIDMLeftfield`, `subgenre_breakbeatDnB`, `subgenre_deepHouse`, `subgenre_electro`, `subgenre_house`, `subgenre_minimal`, `subgenre_synthPop`, `subgenre_techHouse`, `subgenre_techno`, `subgenre_trance`, `subgenre_contemporaryRnB`, `subgenre_gangsta`, `subgenre_jazzyHipHop`, `subgenre_popRap`, `subgenre_trap`, `subgenre_blackMetal`, `subgenre_deathMetal`, `subgenre_doomMetal`, `subgenre_heavyMetal`, `subgenre_metalcore`, `subgenre_nuMetal`, `subgenre_disco`, `subgenre_funk`, `subgenre_gospel`, `subgenre_neoSoul`, `subgenre_soul`, `subgenre_bigBandSwing`, `subgenre_bebop`, `subgenre_contemporaryJazz`, `subgenre_easyListening`, `subgenre_fusion`, `subgenre_latinJazz`, `subgenre_smoothJazz`, `subgenre_country`, `subgenre_folk`) VALUES (".$c_id.",'".$row['subgenre_bluesRock']."','".$row['subgenre_folkRock']."','".$row['subgenre_hardRock']."','".$row['subgenre_indieAlternative']."','".$row['subgenre_psychedelicProgressiveRock']."','".$row['subgenre_punk']."','".$row['subgenre_rockAndRoll']."','".$row['subgenre_popSoftRock']."','".$row['subgenre_abstractIDMLeftfield']."','".$row['subgenre_breakbeatDnB']."','".$row['subgenre_deepHouse']."','".$row['subgenre_electro']."','".$row['subgenre_house']."','".$row['subgenre_minimal']."','".$row['subgenre_synthPop']."','".$row['subgenre_techHouse']."','".$row['subgenre_techno']."','".$row['subgenre_trance']."','".$row['subgenre_contemporaryRnB']."','".$row['subgenre_gangsta']."','".$row['subgenre_jazzyHipHop']."','".$row['subgenre_popRap']."','".$row['subgenre_trap']."','".$row['subgenre_blackMetal']."','".$row['subgenre_deathMetal']."','".$row['subgenre_doomMetal']."','".$row['subgenre_heavyMetal']."','".$row['subgenre_metalcore']."','".$row['subgenre_nuMetal']."','".$row['subgenre_disco']."','".$row['subgenre_funk']."','".$row['subgenre_gospel']."','".$row['subgenre_neoSoul']."','".$row['subgenre_soul']."','".$row['subgenre_bigBandSwing']."','".$row['subgenre_bebop']."','".$row['subgenre_contemporaryJazz']."','".$row['subgenre_easyListening']."','".$row['subgenre_fusion']."','".$row['subgenre_latinJazz']."','".$row['subgenre_smoothJazz']."','".$row['subgenre_country']."','".$row['subgenre_folk']."')";


									$ins_result_segment_qry = "INSERT INTO `tbl_cyanite_result_segment`(`c_id`, `stimestamps`, `smood_aggressive`, `smood_calm`, `smood_chilled`, `smood_dark`, `smood_energetic`, `smood_epic`, `smood_happy`, `smood_romantic`, `smood_sad`, `smood_scary`, `smood_sexy`, `smood_ethereal`, `smood_uplifting`, `sgenre_ambient`, `sgenre_blues`, `sgenre_classical`, `sgenre_electronicDance`, `sgenre_folkCountry`, `sgenre_funkSoul`, `sgenre_jazz`, `sgenre_latin`, `sgenre_metal`, `sgenre_pop`, `sgenre_rapHipHop`, `sgenre_reggae`, `sgenre_rnb`, `sgenre_rock`, `sgenre_singerSongwriter`) VALUES(".$c_id.",'".$row['stimestamps']."','".$row['smood_aggressive']."','".$row['smood_calm']."','".$row['smood_chilled']."','".$row['smood_dark']."','".$row['smood_energetic']."','".$row['smood_epic']."','".$row['smood_happy']."','".$row['smood_romantic']."','".$row['smood_sad']."','".$row['smood_scary']."','".$row['smood_sexy']."','".$row['smood_ethereal']."','".$row['smood_uplifting']."','".$row['sgenre_ambient']."','".$row['sgenre_blues']."','".$row['sgenre_classical']."','".$row['sgenre_electronicDance']."','".$row['sgenre_folkCountry']."','".$row['sgenre_funkSoul']."','".$row['sgenre_jazz']."','".$row['sgenre_latin']."','".$row['sgenre_metal']."','".$row['sgenre_pop']."','".$row['sgenre_rapHipHop']."','".$row['sgenre_reggae']."','".$row['sgenre_rnb']."','".$row['sgenre_rock']."','".$row['sgenre_singerSongwriter']."')";

									echo "ins_result_qry---->".$ins_result_qry."<br><br><br><br>";


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
																		error_log("segment_timestamps succesfully updated for asset mapped to cid->".$c_id." into assets table");
																	}
																	else
																	{
																		echo "Error occured while updating segment_timestamps for asset mapped to cid->".$c_id." into assets table";
																		error_log("Error occured while updating segment_timestamps for asset mapped to cid->".$c_id." into assets table");
																	}

																}

																if ($conn->query("UPDATE `tbl_cyanite` SET `extraction_status`= 1 WHERE `c_id` =".$c_id) === TRUE)
																{
																	echo "Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table";
																	error_log("Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table");
																	$obj = new db_dump();
																	$updt_status = $obj->update_analysis_status($c_id);
																	echo "Data inserted into cyanite json table for c_id->".$c_id;
																	error_log("Data inserted into cyanite json table for c_id->".$c_id);
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
																	error_log("Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table");
																}
															}
															else
															{
																echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result movement segment table";
																error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result movement segment table");
															}
														}
														else
														{
															echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result moodadvanced segment table";
															error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result moodadvanced segment table");
														}
													}
													else
													{
														echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result subgenre segment table";
														error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result subgenre segment table");
													}
												}
												else
												{
													echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result genre segment table";
													error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result genre segment table");
												}
											}
											else
											{
												echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result character segment table";
												error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result character segment table");
											}
										}
										else
										{
											echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result segment table";
											error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result segment table");
										}
									}
									else
									{
										echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result part2 table";
										error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result part2 table");
									}
								}
								else
								{
									echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result table";
									error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result table");
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
											error_log("Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table");
											$obj = new db_dump();
											$updt_status = $obj->update_analysis_status($c_id);
											echo "Data inserted into cyanite json table for c_id->".$c_id;
											error_log("Data inserted into cyanite json table for c_id->".$c_id);
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
											error_log("Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table");
										}
									}
									else
									{
										echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result table";
										error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result table");
									}

								}

							  }
							}
							else
							{
							  error_log("No data found for extraction");
							}*/
						}
						catch(Exception $e)
						{
							error_log("page : [db_dump] : function [extract_mood_and_genere] : error : ".$e->getMessage());
							$sonic_functions->trigger_log_email("db_dump","extract_mood_and_genere",$e->getMessage());
						}

					}
					else
					{
						$return_data = 0;
						error_log("Error ocurred while inserting data into cyanite json table");
					}

				} else {
					$return_data = 0;
					error_log("Error ocurred while updating data into cyanite table");
				}
			}
			elseif ($analysis_status == "AudioAnalysisV6Failed")
			{
				$c_date = date('Y-m-d H:i:s');
				$conn->query("UPDATE `tbl_assets` SET `c_status`= 3, `asset_cyanite_analysis_date` ='".$c_date."' WHERE `c_id` = ".$c_id);
				$return_data = 0;
			}
			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [fetch_and_dump_analised_record] : error : ".$e->getMessage());
			error_log("page : [db_dump] : function [fetch_and_dump_analised_record] : analysis_status from cyanite : ".$analysis_status);
			//$sonic_functions->trigger_log_email("db_dump","fetch_and_dump_analised_record",$e->getMessage());
		}
		
	}	

	function extract_required_cyanite_data(){
		$sonic_functions = new sonic_functions();
		echo "Extracting data from cyanite table";
		error_log("Extracting data from cyanite table");
		try{
			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			$get_cid_qry = "SELECT * FROM `tbl_cyanite` WHERE is_active = 0 AND extraction_status = 0";
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

					$ins_result_qry = "INSERT INTO `tbl_cyanite_result`(`c_id`, `energylevel`, `emotionalprofile`, `bpm`, `keyprediction`, `mood_aggressive`, `mood_calm`, `mood_chilled`, `mood_dark`, `mood_energetic`, `mood_epic`, `mood_happy`, `mood_romantic`, `mood_sad`, `mood_scary`, `mood_sexy`, `mood_ethereal`, `mood_uplifting`, `mooda_anxious`, `mooda_barren`, `mooda_cold`, `mooda_creepy`, `mooda_dark`, `mooda_disturbing`, `mooda_eerie`, `mooda_evil`, `mooda_fearful`, `mooda_mysterious`, `mooda_nervous`, `mooda_restless`, `mooda_spooky`, `mooda_strange`, `mooda_supernatural`, `mooda_suspenseful`, `mooda_tense`, `mooda_weird`, `mooda_aggressive`, `mooda_agitated`, `mooda_angry`, `mooda_dangerous`, `mooda_fiery`, `mooda_intense`, `mooda_passionate`, `mooda_ponderous`, `mooda_violent`, `mooda_comedic`, `mooda_eccentric`, `mooda_funny`, `mooda_mischievous`, `mooda_quirky`, `mooda_whimsical`, `mooda_boisterous`, `mooda_boingy`, `mooda_bright`, `mooda_celebratory`, `mooda_cheerful`, `mooda_excited`, `mooda_feelGood`, `mooda_fun`, `mooda_happy`, `mooda_joyous`, `mooda_lighthearted`, `mooda_perky`, `mooda_playful`, `mooda_rollicking`, `mooda_upbeat`, `mooda_calm`, `mooda_contented`, `mooda_dreamy`, `mooda_introspective`, `mooda_laidBack`, `mooda_leisurely`, `mooda_lyrical`, `mooda_peaceful`, `mooda_quiet`, `mooda_relaxed`, `mooda_serene`, `mooda_soothing`, `mooda_spiritual`, `mooda_tranquil`, `mooda_bittersweet`, `mooda_blue`, `mooda_depressing`, `mooda_gloomy`, `mooda_lonely`, `mooda_melancholic`, `mooda_mournful`, `mooda_poignant`, `mooda_sad`, `mooda_frightening`, `mooda_menacing`, `mooda_nightmarish`, `mooda_ominous`, `mooda_panicStricken`, `mooda_scary`, `mooda_concerned`, `mooda_determined`, `mooda_dignified`, `mooda_emotional`, `mooda_noble`, `mooda_serious`, `mooda_solemn`, `mooda_thoughtful`, `mooda_cool`, `mooda_seductive`, `mooda_sexy`, `mooda_adventurous`, `mooda_confident`, `mooda_courageous`, `mooda_resolute`, `mooda_energetic`, `mooda_epic`, `mooda_exciting`, `mooda_exhilarating`, `mooda_heroic`, `mooda_majestic`, `mooda_powerful`, `mooda_prestigious`, `mooda_relentless`, `mooda_strong`, `mooda_triumphant`, `mooda_victorious`, `mooda_delicate`, `mooda_graceful`, `mooda_hopeful`, `mooda_innocent`, `mooda_intimate`, `mooda_kind`, `mooda_light`, `mooda_loving`, `mooda_nostalgic`, `mooda_reflective`, `mooda_romantic`, `mooda_sentimental`, `mooda_soft`, `mooda_sweet`, `mooda_tender`, `mooda_warm`, `mooda_anthemic`, `mooda_aweInspiring`, `mooda_euphoric`, `mooda_inspirational`, `mooda_motivational`, `mooda_optimistic`, `mooda_positive`, `mooda_proud`, `mooda_soaring`, `mooda_uplifting`, `genre_ambient`, `genre_blues`, `genre_classical`, `genre_electronicDance`, `genre_folkCountry`, `genre_funkSoul`, `genre_jazz`, `genre_latin`, `genre_metal`, `genre_pop`, `genre_punk`, `genre_rapHipHop`, `genre_reggae`, `genre_rnb`, `genre_rock`, `genre_singerSongwriter`, `character_bold`, `character_cool`, `character_epic`, `character_ethereal`, `character_heroic`, `character_luxurious`, `character_magical`, `character_mysterious`, `character_playful`, `character_powerful`, `character_retro`, `character_sophisticated`, `character_sparkling`, `character_sparse`, `character_unpolished`, `character_warm`) VALUES (".$c_id.",'".$row['cy_energylevel']."','".$row['cy_emotionalprofile']."','".$row['cy_bpm']."','".$row['cy_key']."','".$row['mood_aggressive']."','".$row['mood_calm']."','".$row['mood_chilled']."','".$row['mood_dark']."','".$row['mood_energetic']."','".$row['mood_epic']."','".$row['mood_happy']."','".$row['mood_romantic']."','".$row['mood_sad']."','".$row['mood_scary']."','".$row['mood_sexy']."','".$row['mood_ethereal']."','".$row['mood_uplifting']."','".$row['mooda_anxious']."','".$row['mooda_barren']."','".$row['mooda_cold']."','".$row['mooda_creepy']."','".$row['mooda_dark']."','".$row['mooda_disturbing']."','".$row['mooda_eerie']."','".$row['mooda_evil']."','".$row['mooda_fearful']."','".$row['mooda_mysterious']."','".$row['mooda_nervous']."','".$row['mooda_restless']."','".$row['mooda_spooky']."','".$row['mooda_strange']."','".$row['mooda_supernatural']."','".$row['mooda_suspenseful']."','".$row['mooda_tense']."','".$row['mooda_weird']."','".$row['mooda_aggressive']."','".$row['mooda_agitated']."','".$row['mooda_angry']."','".$row['mooda_dangerous']."','".$row['mooda_fiery']."','".$row['mooda_intense']."','".$row['mooda_passionate']."','".$row['mooda_ponderous']."','".$row['mooda_violent']."','".$row['mooda_comedic']."','".$row['mooda_eccentric']."','".$row['mooda_funny']."','".$row['mooda_mischievous']."','".$row['mooda_quirky']."','".$row['mooda_whimsical']."','".$row['mooda_boisterous']."','".$row['mooda_boingy']."','".$row['mooda_bright']."','".$row['mooda_celebratory']."','".$row['mooda_cheerful']."','".$row['mooda_excited']."','".$row['mooda_feelGood']."','".$row['mooda_fun']."','".$row['mooda_happy']."','".$row['mooda_joyous']."','".$row['mooda_lighthearted']."','".$row['mooda_perky']."','".$row['mooda_playful']."','".$row['mooda_rollicking']."','".$row['mooda_upbeat']."','".$row['mooda_calm']."','".$row['mooda_contented']."','".$row['mooda_dreamy']."','".$row['mooda_introspective']."','".$row['mooda_laidBack']."','".$row['mooda_leisurely']."','".$row['mooda_lyrical']."','".$row['mooda_peaceful']."','".$row['mooda_quiet']."','".$row['mooda_relaxed']."','".$row['mooda_serene']."','".$row['mooda_soothing']."','".$row['mooda_spiritual']."','".$row['mooda_tranquil']."','".$row['mooda_bittersweet']."','".$row['mooda_blue']."','".$row['mooda_depressing']."','".$row['mooda_gloomy']."','".$row['mooda_lonely']."','".$row['mooda_mournful']."','".$row['mooda_poignant']."','".$row['mooda_sad']."','".$row['mooda_frightening']."','".$row['mooda_menacing']."','".$row['mooda_nightmarish']."','".$row['mooda_ominous']."','".$row['mooda_panicStricken']."','".$row['mooda_scary']."','".$row['mooda_concerned']."','".$row['mooda_determined']."','".$row['mooda_dignified']."','".$row['mooda_emotional']."','".$row['mooda_noble']."','".$row['mooda_serious']."','".$row['mooda_solemn']."','".$row['mooda_thoughtful']."','".$row['mooda_cool']."','".$row['mooda_seductive']."','".$row['mooda_sexy']."','".$row['mooda_adventurous']."','".$row['mooda_confident']."','".$row['mooda_courageous']."','".$row['mooda_resolute']."','".$row['mooda_energetic']."','".$row['mooda_epic']."','".$row['mooda_exciting']."','".$row['mooda_exhilarating']."','".$row['mooda_heroic']."','".$row['mooda_majestic']."','".$row['mooda_powerful']."','".$row['mooda_prestigious']."','".$row['mooda_relentless']."','".$row['mooda_strong']."','".$row['mooda_triumphant']."','".$row['mooda_victorious']."','".$row['mooda_delicate']."','".$row['mooda_graceful']."','".$row['mooda_hopeful']."','".$row['mooda_innocent']."','".$row['mooda_intimate']."','".$row['mooda_kind']."','".$row['mooda_light']."','".$row['mooda_loving']."','".$row['mooda_nostalgic']."','".$row['mooda_reflective']."','".$row['mooda_romantic']."','".$row['mooda_sentimental']."','".$row['mooda_soft']."','".$row['mooda_sweet']."','".$row['mooda_tender']."','".$row['mooda_warm']."','".$row['mooda_anthemic']."','".$row['mooda_aweInspiring']."','".$row['mooda_euphoric']."','".$row['mooda_inspirational']."','".$row['mooda_motivational']."','".$row['mooda_optimistic']."','".$row['mooda_positive']."','".$row['mooda_proud']."','".$row['mooda_soaring']."','".$row['mooda_uplifting']."','".$row['genre_ambient']."','".$row['genre_blues']."','".$row['genre_classical']."','".$row['country']."','".$row['genre_electronicDance']."','".$row['genre_folkCountry']."','".$row['genre_funkSoul']."','".$row['indieAlternative']."','".$row['genre_jazz']."','".$row['genre_latin']."','".$row['genre_metal']."','".$row['genre_pop']."','".$row['genre_punk']."','".$row['genre_rapHipHop']."','".$row['genre_reggae']."','".$row['genre_rnb']."','".$row['genre_rock']."','".$row['genre_singerSongwriter']."','".$row['character_bold']."','".$row['character_cool']."','".$row['character_epic']."','".$row['character_ethereal']."','".$row['character_heroic']."','".$row['character_luxurious']."','".$row['character_magical']."','".$row['character_mysterious']."','".$row['character_playful']."','".$row['character_powerful']."','".$row['character_retro']."','".$row['character_sophisticated']."','".$row['character_sparkling']."','".$row['character_sparse']."','".$row['character_unpolished']."','".$row['character_warm']."')";

					if ($conn->query($ins_result_qry) === TRUE)
					{
						if ($conn->query("UPDATE `tbl_cyanite` SET `extraction_status`= 1 WHERE `c_id` =".$c_id) === TRUE)
						{
							echo "Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table";
							error_log("Data succesfully inserted for cid->".$c_id." into cyanite result table and extraction status updated for c_id->".$c_id." into cyanite table");
						}
						else
						{
							echo "Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table";
							error_log("Error occured while updating extraction status = 1 for cid->".$c_id." into cyanite table");
						}
					}
					else
					{
						echo "Error occured while inserting extracted data of cid->".$c_id." into cyanite result table";
						error_log("Error occured while inserting extracted data of cid->".$c_id." into cyanite result table");
					}

				}

			  }
			}
			else
			{
			  error_log("No data found for extraction");
			}
			
			
			//error_log("page : [db_dump] : function [extract_mood_and_genere] sql_paras: ".$sql_paras);
			$result = $conn->query($sql_paras);
			$track_id_array = array();
			if ($result->num_rows > 0) {
				error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
				$ins_mood_temp_query = "insert into tbl_cv_block_".$table_name_id."_mood_graph_temp_data (cv_id,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
				$ins_genre_temp_query = "insert into tbl_cv_block_".$table_name_id."_genre_graph_temp_data (cv_id,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
				while($row = $result->fetch_assoc()) {
					$LTrack_id = $row["LTrack_id"];
					error_log("page : [db_dump] : function [extract_mood_and_genere] track id extracting : ".$LTrack_id);
					//error_log("page : [db_dump] : function [extract_mood_and_genere] generating mood insert query for : ".$LTrack_id);
					
					$ins_mood_temp_query .= "(".$brand_id.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
					
					//error_log("page : [db_dump] : function [extract_mood_and_genere] generating genre insert query for : ".$LTrack_id);
					
					$ins_genre_temp_query .= "(".$brand_id.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
					
					array_push($track_id_array,$LTrack_id);
						
				}
				$multi_ins_mood_temp_query = rtrim($ins_mood_temp_query,",");
				//error_log("page : [db_dump] : function [extract_mood_and_genere] generated multi mood insert query : ".$multi_ins_mood_temp_query." for : ".$brand_id);
				error_log("page : [db_dump] : function [extract_mood_and_genere] multi mood insert query  for cv: ".$brand_id." is generated");
				
				$multi_ins_genre_temp_query = rtrim($ins_genre_temp_query,",");
				//error_log("page : [db_dump] : function [extract_mood_and_genere] generated multi genre insert query : ".$multi_ins_genre_temp_query." for : ".$brand_id);
				error_log("page : [db_dump] : function [extract_mood_and_genere] multi genre insert query  for cv: ".$brand_id." is generated");
				
				if($conn->query($multi_ins_mood_temp_query)){
					// update status 3
					$sql1 = "update tbl_social_spyder_graph_meta_data set status=3 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 3 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [extract_mood_and_genere] status updated to 3 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					$conn->query($sql1);						
				}
				
				if($conn->query($multi_ins_genre_temp_query)){
					// update status 4
					$sql2 = "update tbl_social_spyder_graph_meta_data set status=4 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 3 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [extract_mood_and_genere] status updated to 4 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					if($conn->query($sql2))
					{
						$chk_query = "select * from tbl_cv_block_".$table_name_id."_mood_graph_data where cv_id =".$brand_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_mood_graph_data WHERE cv_id=".$brand_id);
							error_log("page : [db_dump] : function [extract_mood_and_genere] exisiting cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_mood_graph_data");
						}
						
						$sql_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_cv_block_".$table_name_id."_mood_graph_temp_data` WHERE `cv_id` =".$brand_id;
						error_log("sql_mood_avg_query:".$sql_mood_avg_query);
						$result = $conn->query($sql_mood_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s1 = "insert into tbl_cv_block_".$table_name_id."_mood_graph_data(cv_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$brand_id.",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting'].")";							
				
								error_log("tbl_cv_block_".$table_name_id."_mood_graph_data:".$s1);
								if($conn->query($s1))
								{
									$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_mood_graph_temp_data WHERE cv_id=".$brand_id);
									error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_mood_graph_temp_data");
								}
							}
						}
						
						$chk_query = "select * from tbl_cv_block_".$table_name_id."_genre_graph_data where cv_id =".$brand_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_genre_graph_data WHERE cv_id=".$brand_id);
							error_log("page : [db_dump] : function [extract_mood_and_genere] exisiting cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_genre_graph_data");
						}
						
						$sql_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_cv_block_".$table_name_id."_genre_graph_temp_data` WHERE `cv_id` =".$brand_id;
						error_log("sql_genre_avg_query:".$sql_genre_avg_query);
						$result = $conn->query($sql_genre_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s2 = "insert into tbl_cv_block_".$table_name_id."_genre_graph_data(cv_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$brand_id.",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter'].")";							
				
								error_log("tbl_cv_block_".$table_name_id."_genre_graph_data:".$s2);
								if($conn->query($s2))
								{
									$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_genre_graph_temp_data WHERE cv_id=".$brand_id);
									$conn->query("UPDATE `tbl_social_media_sync_process_data` SET `".$col_name."` = 2  WHERE `cv_id` = ".$brand_id);

									$chk_meta_status_qry = "select * from tbl_social_spyder_graph_meta_data where cv_id = ".$brand_id." and track_id IN (".$track_ids.") and status < 4 and is_active = 0";
									  $chk_meta_status_qry_res = $conn->query($chk_meta_status_qry);

									  if ($chk_meta_status_qry_res->num_rows == 0) {

											$conn->query("update tbl_social_spyder_graph_request_data set new_status = 3 where cv_id =".$brand_id." and new_status!=0 and chn_id in (".$channel_ids.")");
											error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_genre_graph_temp_data");
										}
								}
							}
						}
						
					}						
				}
				error_log("page : [db_dump] : function [extract_mood_and_genere] : Message : Both Graphs Average Data inserted for cv id:".$brand_id);
				error_log("====================================================================================================================================================");
			}
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [extract_mood_and_genere] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","extract_mood_and_genere",$e->getMessage());
		}
	}
	

	function extract_mood_and_genere($brand_id, $brand_process_type, $track_ids, $channel_ids){
		$sonic_functions = new sonic_functions();
		error_log("page : [db_dump] : function [extract_mood_and_genere] for cv id:".$brand_id." and process_type:".$brand_process_type);
		try{

			switch($brand_process_type){
				case 'youtube':
					$table_name_id = "16";
					$col_name = "yt";
					break;
				case 'instagram':
					$table_name_id = "17";
					$col_name = "ig";
					break;
				case 'tiktok':
					$table_name_id = "18";
					$col_name = "tt";
					break;
				case 'twitter':
					$table_name_id = "19";
					$col_name = "twt";
					break;
			}

			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			
			$sql_paras = "SELECT 
			cyanite.LTrack_id,
			cyanite.brand_id,
			json_extract(cyanite.mood, '$.aggressive') AS aggressive,
			json_extract(cyanite.mood, '$.calm') AS calm,
			json_extract(cyanite.mood, '$.chilled') AS chilled,
			json_extract(cyanite.mood, '$.dark') AS dark,
			json_extract(cyanite.mood, '$.energetic') AS energetic,
			json_extract(cyanite.mood, '$.epic') AS epic,
			json_extract(cyanite.mood, '$.happy') AS happy,
			json_extract(cyanite.mood, '$.romantic') AS romantic,
			json_extract(cyanite.mood, '$.sad') AS sad,
			json_extract(cyanite.mood, '$.scary') AS scary,
			json_extract(cyanite.mood, '$.sexy') AS sexy,
			json_extract(cyanite.mood, '$.ethereal') AS ethereal,
			json_extract(cyanite.mood, '$.uplifting') AS uplifting,
			json_extract(cyanite.genre, '$.ambient') AS ambient,
			json_extract(cyanite.genre, '$.blues') AS blues,
			json_extract(cyanite.genre, '$.classical') AS classical,
			json_extract(cyanite.genre, '$.country') AS country,
			json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
			json_extract(cyanite.genre, '$.folk') AS folk,
			json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
			json_extract(cyanite.genre, '$.jazz') AS jazz,
			json_extract(cyanite.genre, '$.latin') AS latin,
			json_extract(cyanite.genre, '$.metal') AS metal,
			json_extract(cyanite.genre, '$.pop') AS pop,
			json_extract(cyanite.genre, '$.punk') AS punk,
			json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
			json_extract(cyanite.genre, '$.reggae') AS reggae,
			json_extract(cyanite.genre, '$.rnb') AS rnb,
			json_extract(cyanite.genre, '$.rock') AS rock,
			json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$brand_id." and cyanite.process_type='".$brand_process_type."' and cyanite.is_active=0 and cyanite.LTrack_id IN (".$track_ids.")";
			//error_log("page : [db_dump] : function [extract_mood_and_genere] sql_paras: ".$sql_paras);
			$result = $conn->query($sql_paras);
			$track_id_array = array();
			if ($result->num_rows > 0) {
				error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
				$ins_mood_temp_query = "insert into tbl_cv_block_".$table_name_id."_mood_graph_temp_data (cv_id,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
				$ins_genre_temp_query = "insert into tbl_cv_block_".$table_name_id."_genre_graph_temp_data (cv_id,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
				while($row = $result->fetch_assoc()) {
					$LTrack_id = $row["LTrack_id"];
					error_log("page : [db_dump] : function [extract_mood_and_genere] track id extracting : ".$LTrack_id);
					//error_log("page : [db_dump] : function [extract_mood_and_genere] generating mood insert query for : ".$LTrack_id);
					
					$ins_mood_temp_query .= "(".$brand_id.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
					
					//error_log("page : [db_dump] : function [extract_mood_and_genere] generating genre insert query for : ".$LTrack_id);
					
					$ins_genre_temp_query .= "(".$brand_id.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
					
					array_push($track_id_array,$LTrack_id);
						
				}
				$multi_ins_mood_temp_query = rtrim($ins_mood_temp_query,",");
				//error_log("page : [db_dump] : function [extract_mood_and_genere] generated multi mood insert query : ".$multi_ins_mood_temp_query." for : ".$brand_id);
				error_log("page : [db_dump] : function [extract_mood_and_genere] multi mood insert query  for cv: ".$brand_id." is generated");
				
				$multi_ins_genre_temp_query = rtrim($ins_genre_temp_query,",");
				//error_log("page : [db_dump] : function [extract_mood_and_genere] generated multi genre insert query : ".$multi_ins_genre_temp_query." for : ".$brand_id);
				error_log("page : [db_dump] : function [extract_mood_and_genere] multi genre insert query  for cv: ".$brand_id." is generated");
				
				if($conn->query($multi_ins_mood_temp_query)){
					// update status 3
					$sql1 = "update tbl_social_spyder_graph_meta_data set status=3 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 3 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [extract_mood_and_genere] status updated to 3 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					$conn->query($sql1);						
				}
				
				if($conn->query($multi_ins_genre_temp_query)){
					// update status 4
					$sql2 = "update tbl_social_spyder_graph_meta_data set status=4 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 3 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [extract_mood_and_genere] status updated to 4 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					if($conn->query($sql2))
					{
						$chk_query = "select * from tbl_cv_block_".$table_name_id."_mood_graph_data where cv_id =".$brand_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_mood_graph_data WHERE cv_id=".$brand_id);
							error_log("page : [db_dump] : function [extract_mood_and_genere] exisiting cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_mood_graph_data");
						}
						
						$sql_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_cv_block_".$table_name_id."_mood_graph_temp_data` WHERE `cv_id` =".$brand_id;
						error_log("sql_mood_avg_query:".$sql_mood_avg_query);
						$result = $conn->query($sql_mood_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s1 = "insert into tbl_cv_block_".$table_name_id."_mood_graph_data(cv_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$brand_id.",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting'].")";							
				
								error_log("tbl_cv_block_".$table_name_id."_mood_graph_data:".$s1);
								if($conn->query($s1))
								{
									$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_mood_graph_temp_data WHERE cv_id=".$brand_id);
									error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_mood_graph_temp_data");
								}
							}
						}
						
						$chk_query = "select * from tbl_cv_block_".$table_name_id."_genre_graph_data where cv_id =".$brand_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_genre_graph_data WHERE cv_id=".$brand_id);
							error_log("page : [db_dump] : function [extract_mood_and_genere] exisiting cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_genre_graph_data");
						}
						
						$sql_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_cv_block_".$table_name_id."_genre_graph_temp_data` WHERE `cv_id` =".$brand_id;
						error_log("sql_genre_avg_query:".$sql_genre_avg_query);
						$result = $conn->query($sql_genre_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s2 = "insert into tbl_cv_block_".$table_name_id."_genre_graph_data(cv_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$brand_id.",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter'].")";							
				
								error_log("tbl_cv_block_".$table_name_id."_genre_graph_data:".$s2);
								if($conn->query($s2))
								{
									$conn->query("DELETE FROM tbl_cv_block_".$table_name_id."_genre_graph_temp_data WHERE cv_id=".$brand_id);
									$conn->query("UPDATE `tbl_social_media_sync_process_data` SET `".$col_name."` = 2  WHERE `cv_id` = ".$brand_id);

									$chk_meta_status_qry = "select * from tbl_social_spyder_graph_meta_data where cv_id = ".$brand_id." and track_id IN (".$track_ids.") and status < 4 and is_active = 0";
									  $chk_meta_status_qry_res = $conn->query($chk_meta_status_qry);

									  if ($chk_meta_status_qry_res->num_rows == 0) {

											$conn->query("update tbl_social_spyder_graph_request_data set new_status = 3 where cv_id =".$brand_id." and new_status!=0 and chn_id in (".$channel_ids.")");
											error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$brand_id." data is deleted from tbl_cv_block_".$table_name_id."_genre_graph_temp_data");
										}
								}
							}
						}
						
					}						
				}
				error_log("page : [db_dump] : function [extract_mood_and_genere] : Message : Both Graphs Average Data inserted for cv id:".$brand_id);
				error_log("====================================================================================================================================================");
			}
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [extract_mood_and_genere] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","extract_mood_and_genere",$e->getMessage());
		}
	}

	function update_crate_id_matadata_table($crate_id, $brand_id){
		$sonic_functions = new sonic_functions();
		try{
			$return_data = null;

			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			$sql = "update tbl_social_spyder_graph_meta_data set crate_id = ".$crate_id. " where cv_id = ".$brand_id;
			
			if ($conn->query($sql) === TRUE) {

				$return_data = $crate_id;
			} else {

				$return_data = 0;
			}

			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [update_crate_id_matadata_table] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","update_crate_id_matadata_table",$e->getMessage());
		}
		
	}

	function update_trackid_and_status($track_id, $asset_id){
		$sonic_functions = new sonic_functions();
		try{		
			$return_data = null;

			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	

			if($conn->query("INSERT INTO `tbl_cyanite`(`track_id`) VALUES (".$track_id.")") === TRUE)
              {
                $c_id = $conn->insert_id;

                $c_date = date('Y-m-d H:i:s');

                if($conn->query("UPDATE `tbl_assets` SET `c_id` = ".$c_id.", `asset_cyanite_u_status` = 1, `asset_cyanite_u_date` = '".$c_date."', `c_status` = 1, `process_status` = 1 WHERE `asset_id` ='".$asset_id."'") === TRUE)
                {
                  error_log("Track ID -".$track_id." successfully inserted into cyanite table and c_id=".$c_id.", asset_cyanite_u_status=1, asset_cyanite_u_date".$c_date." and c_status=1 successfully updated into asset table");
                  echo "Track ID -".$track_id." successfully inserted into cyanite table and c_id=".$c_id.", asset_cyanite_u_status=1, asset_cyanite_u_date".$c_date." and c_status=1 successfully updated into asset table";
                  $return_data = 1; // success
                }
                else
                {
                  error_log("Error ocurred while updating c_id=".$c_id." and c_status=1 for asset=".$asset_id." into asset table");
                  $return_data = 0;
                }
              }
              else
              {
                error_log("Error ocurred while inserting Track ID -".$track_id." into cyanite table");
                $return_data = 0;
              }

			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [update_trackid_and_status] : error : ".$e->getMessage());
			//$sonic_functions->trigger_log_email("db_dump","update_trackid_and_status",$e->getMessage());
		}
	}

	function update_analysis_status($id){
		$sonic_functions = new sonic_functions();
		try{
			$return_data = null;

			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			$c_date = date('Y-m-d H:i:s');
			if($conn->query("UPDATE `tbl_assets` SET `c_status` = 2, `asset_cyanite_analysis_date` ='".$c_date."', `process_status` = 1 WHERE  `c_id` = ".$id) === TRUE)
            {
              error_log("c_status=2 successfully updated into asset table for c_id->".$id);
              echo "c_status=2 successfully updated into asset table for c_id->".$id;
              $return_data = 1; // success
            }
            else
            {
              error_log("Error ocurred while updating c_status=2 for c_id->".$id." into asset table");
              $return_data = 0;
            }
			$conn->close();

			return $return_data;
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [update_analysis_status] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","update_analysis_status",$e->getMessage());
		}
	}

	function old_cyanite_data_disable($cv_id,$c_date,$chnl_id,$process_type){
		$sonic_functions = new sonic_functions();
		try{
			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			
			// cdate is in filter to diable old records
			// so if process inteurrpt so next time we will disable old records only.
			// all channels of each brand will have same cdate
			$sql =  "update cyanite set is_active=1 where cv_id=".$cv_id." and c_date !='".$c_date."' and process_type ='".$process_type."'";
			$conn->query($sql);
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [old_cyanite_data_disable] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","old_cyanite_data_disable",$e->getMessage());
		}
	}

	function aggr_of_aggr($cv_id, $track_ids, $channel_ids){
		//echo ">> ".$cv_id;
		error_log("page : [db_dump] : function [aggr_of_aggr] for cv id:".$cv_id);
		$sonic_functions = new sonic_functions();
		
		try{
			
			$dbcon = include('../config.php');
			$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);	
			
			
			$sql_paras = "SELECT 
			cyanite.LTrack_id,
			cyanite.brand_id,
			json_extract(cyanite.mood, '$.aggressive') AS aggressive,
			json_extract(cyanite.mood, '$.calm') AS calm,
			json_extract(cyanite.mood, '$.chilled') AS chilled,
			json_extract(cyanite.mood, '$.dark') AS dark,
			json_extract(cyanite.mood, '$.energetic') AS energetic,
			json_extract(cyanite.mood, '$.epic') AS epic,
			json_extract(cyanite.mood, '$.happy') AS happy,
			json_extract(cyanite.mood, '$.romantic') AS romantic,
			json_extract(cyanite.mood, '$.sad') AS sad,
			json_extract(cyanite.mood, '$.scary') AS scary,
			json_extract(cyanite.mood, '$.sexy') AS sexy,
			json_extract(cyanite.mood, '$.ethereal') AS ethereal,
			json_extract(cyanite.mood, '$.uplifting') AS uplifting,
			json_extract(cyanite.genre, '$.ambient') AS ambient,
			json_extract(cyanite.genre, '$.blues') AS blues,
			json_extract(cyanite.genre, '$.classical') AS classical,
			json_extract(cyanite.genre, '$.country') AS country,
			json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
			json_extract(cyanite.genre, '$.folk') AS folk,
			json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
			json_extract(cyanite.genre, '$.jazz') AS jazz,
			json_extract(cyanite.genre, '$.latin') AS latin,
			json_extract(cyanite.genre, '$.metal') AS metal,
			json_extract(cyanite.genre, '$.pop') AS pop,
			json_extract(cyanite.genre, '$.punk') AS punk,
			json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
			json_extract(cyanite.genre, '$.reggae') AS reggae,
			json_extract(cyanite.genre, '$.rnb') AS rnb,
			json_extract(cyanite.genre, '$.rock') AS rock,
			json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$cv_id." and cyanite.is_active=0 and cyanite.LTrack_id IN (".$track_ids.")";
	
			$result = $conn->query($sql_paras);
			$track_id_array = array();
			if ($result->num_rows > 0) {
				error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
				$ins_mood_temp_query = "insert into tbl_mood_aggr_graph_temp_data (cv_id,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
				$ins_genre_temp_query = "insert into tbl_genre_aggr_graph_temp_data (cv_id,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
				while($row = $result->fetch_assoc()) {
					$LTrack_id = $row["LTrack_id"];
					
					//error_log("page : [db_dump] : function [aggr_of_aggr] track id extracting : ".$LTrack_id);
					//error_log("page : [db_dump] : function [aggr_of_aggr] generating mood insert query for : ".$LTrack_id);
					
					$ins_mood_temp_query .= "(".$cv_id.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
					
					//error_log("page : [db_dump] : function [aggr_of_aggr] generating genre insert query for : ".$LTrack_id);
					
					$ins_genre_temp_query .= "(".$cv_id.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
					
					array_push($track_id_array,$LTrack_id);
						
				}
				
				$multi_ins_mood_temp_query = rtrim($ins_mood_temp_query,",");
				//error_log("page : [db_dump] : function [aggr_of_aggr] generated multi mood insert query : ".$multi_ins_mood_temp_query." for : ".$cv_id);
				error_log("page : [db_dump] : function [aggr_of_aggr] multi mood insert query  for cv: ".$cv_id." is generated");
				
				$multi_ins_genre_temp_query = rtrim($ins_genre_temp_query,",");
				//error_log("page : [db_dump] : function [aggr_of_aggr] generated multi genre insert query : ".$multi_ins_genre_temp_query." for : ".$cv_id);
				error_log("page : [db_dump] : function [aggr_of_aggr] multi genre insert query  for cv: ".$cv_id." is generated");
				
				if($conn->query($multi_ins_mood_temp_query)){
					// update status 5
					$sql1 = "update tbl_social_spyder_graph_meta_data set status=5 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [aggr_of_aggr] update status to 5 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [aggr_of_aggr] status updated to 5 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					$conn->query($sql1);						
				}
				
				if($conn->query($multi_ins_genre_temp_query)){
					// update status 6
					$sql2 = "update tbl_social_spyder_graph_meta_data set status=6 where track_id IN (".implode(",",$track_id_array).")";
					//error_log("page : [db_dump] : function [extract_mood_and_genere] update status to 6 at tbl_social_spyder_graph_meta_data of trackID : ".$LTrack_id);
					error_log("page : [db_dump] : function [aggr_of_aggr] status updated to 6 in tbl_social_spyder_graph_meta_data for tracks :".implode(",",$track_id_array));
					if($conn->query($sql2))
					{
						$chk_query = "select * from tbl_mood_aggr_graph_data where cv_id =".$cv_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_mood_aggr_graph_data WHERE cv_id=".$cv_id);
							error_log("page : [db_dump] : function [aggr_of_aggr] exisiting cv_id=".$cv_id." data is deleted from tbl_mood_aggr_graph_data");
						}
						
						$sql_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_mood_aggr_graph_temp_data` WHERE `cv_id` =".$cv_id;
						error_log("[db_dump] : function [aggr_of_aggr] sql_mood_avg_query:".$sql_mood_avg_query);
						$result = $conn->query($sql_mood_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s1 = "insert into tbl_mood_aggr_graph_data(cv_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting'].")";							
				
								error_log("[db_dump] : function [aggr_of_aggr] tbl_mood_aggr_graph_data:".$s1);
								if($conn->query($s1))
								{
									$conn->query("DELETE FROM tbl_mood_aggr_graph_temp_data WHERE cv_id=".$cv_id);
									error_log("page : [db_dump] : function [extract_mood_and_genere] cv_id=".$cv_id." data is deleted from tbl_mood_aggr_graph_temp_data");
								}
							}
						}
						
						$chk_query = "select * from tbl_genre_aggr_graph_data where cv_id =".$cv_id;
						$chk_query_res = $conn->query($chk_query);
						if($chk_query_res->num_rows > 0)
						{
							$conn->query("DELETE FROM tbl_genre_aggr_graph_data WHERE cv_id=".$cv_id);
							error_log("page : [db_dump] : function [aggr_of_aggr] exisiting cv_id=".$cv_id." data is deleted from tbl_genre_aggr_graph_data");
						}
						
						$sql_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_genre_aggr_graph_temp_data` WHERE `cv_id` =".$cv_id;
						error_log("[db_dump] : function [aggr_of_aggr] sql_genre_avg_query:".$sql_genre_avg_query);
						$result = $conn->query($sql_genre_avg_query);
						if ($result->num_rows > 0) {
				  			while($row = $result->fetch_assoc()) {
								$s2 = "insert into tbl_genre_aggr_graph_data(cv_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter'].")";							
				
								error_log("[db_dump] : function [aggr_of_aggr] tbl_genre_aggr_graph_data:".$s2);
								if($conn->query($s2))
								{
									$conn->query("DELETE FROM tbl_genre_aggr_graph_temp_data WHERE cv_id=".$cv_id);
									$conn->query("UPDATE `tbl_social_media_sync_process_data` SET `aggr` = 2  WHERE `cv_id` = ".$cv_id);
									//error_log("channel_ids:".$channel_ids);
									foreach($channel_ids as $chnl_id)
									{
										$get_chnl_total_cntn_count_qry = "select * from tbl_social_spyder_graph_request_data where cv_id =".$cv_id." and chn_id=".$chnl_id." and is_active=0 and `chn_notfound` = 0";
										$get_chnl_total_cntn_count_qry_res = $conn->query($get_chnl_total_cntn_count_qry);
										$chnl_total_cntn_count = $get_chnl_total_cntn_count_qry_res->num_rows;
										$chnl_start_id = '';
										$chnl_end_id = '';

										//$get_chnl_cnt_qry = "SELECT chn_id FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$cv_id." and `is_active` = 0 and `chn_notfound` = 0 and `v_count` != 0 and `down_count` != 0";
										$get_chnl_cnt_qry = "SELECT chn_id FROM ( SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$cv_id." and `is_active` = 0 and `chn_notfound` = 0 and v_count != 0 and down_count != 0 UNION SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$cv_id." and `is_active` = 0 and `chn_notfound` = 0 and v_count is null and down_count is null) ch";
										$get_chnl_cnt_qry_res = $conn->query($get_chnl_cnt_qry);
										$channel_count = $get_chnl_cnt_qry_res->num_rows;
										$upload_count = round($dbcon['video_upload_limit_count'] / $channel_count);
										error_log("upload_count:".$upload_count);
										while($get_chnl_total_cntn_count_qry_res_row = $get_chnl_total_cntn_count_qry_res->fetch_assoc())
										{
											$chnl_start_id = $get_chnl_total_cntn_count_qry_res_row['uploaded_start_id'];
											$chnl_end_id = $get_chnl_total_cntn_count_qry_res_row['uploaded_end_id'];
											$new_status = $get_chnl_total_cntn_count_qry_res_row['new_status'];
											error_log("chnl_start_id:".$chnl_start_id."|chnl_end_id".$chnl_end_id."|new_status".$new_status);
											if($chnl_start_id > 0 && $chnl_end_id > 0 && $new_status != 0)
											{
												$get_chnl_processed_cntn_count_qry = "select * from tbl_social_spyder_graph_meta_data where cv_id =".$cv_id." and chn_id=".$chnl_id." and is_active=0 and path is not null and id between ".$chnl_start_id." and ".$chnl_end_id;
												$get_chnl_processed_cntn_count_qry_res = $conn->query($get_chnl_processed_cntn_count_qry);
												$chnl_processed_cntn_count = $get_chnl_processed_cntn_count_qry_res->num_rows;
												error_log($get_chnl_processed_cntn_count_qry);
												error_log("chnl_start_id:".$chnl_start_id."|chnl_end_id".$chnl_end_id."|new_status".$new_status."|chnl_processed_cntn_count".$chnl_processed_cntn_count);
												if($chnl_processed_cntn_count <= $upload_count && $chnl_total_cntn_count <= $upload_count)
												{
													$conn->query("update tbl_social_spyder_graph_request_data set new_status = 5 where cv_id =".$cv_id." and chn_id =".$chnl_id);
													error_log("[db_dump] : function [aggr_of_aggr] new status updated to 5 (completed) into tbl_social_spyder_graph_request_data for cv_id =".$cv_id." and chn_id =".$chnl_id);
												}
												else
												{
													$conn->query("update tbl_social_spyder_graph_request_data set new_status = 4 where cv_id =".$cv_id." and chn_id =".$chnl_id);
													error_log("[db_dump] : function [aggr_of_aggr] new status updated to 4 (Aggregate grap generated) into tbl_social_spyder_graph_request_data for cv_id =".$cv_id." and chn_id =".$chnl_id);
												}
											}
										}
										
									}									

									//get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos start
									$chk_mgytv_qry = "SELECT * FROM `tbl_mood_genre_yt_videos` WHERE cv_id=".$cv_id;
									$chk_mgytv_qry_result = $conn->query($chk_mgytv_qry);
									if ($chk_mgytv_qry_result->num_rows > 0)
									{
									    while($chk_mgytv_qry_result_row = $chk_mgytv_qry_result->fetch_assoc())
									    {
									        $row_id = $chk_mgytv_qry_result_row['mgytv_id'];
									    }

									    $mgytv_ins_qry = "REPLACE INTO `tbl_mood_genre_yt_videos`(`mgytv_id`, `cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$row_id.",".$cv_id.",";
									}
									else
									{
									    $mgytv_ins_qry = "INSERT INTO `tbl_mood_genre_yt_videos`(`cv_id`, `mood_v1_id`, `mood_v1_title`, `mood_v2_id`, `mood_v2_title`, `mood_v3_id`, `mood_v3_title`, `genre_v1_id`, `genre_v1_title`, `genre_v2_id`, `genre_v2_title`, `genre_v3_id`, `genre_v3_title`) VALUES (".$cv_id.",";
									}

									$cv_mood_aggr_graph_values_data_qry = "select `aggressive`,`calm`,`chilled`,`energetic`,`happy`,`romantic`,`sad`,`scary`,`sexy`,`ethereal`,`uplifting` from `tbl_mood_aggr_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
									$cv_mood_aggr_graph_values_data_qry_result = $conn->query($cv_mood_aggr_graph_values_data_qry);

									if ($cv_mood_aggr_graph_values_data_qry_result->num_rows > 0)
									{
									    $cv_mood_aggr_graph_values_data_array = [];
									    while($cv_mood_aggr_graph_values_data_qry_result_row = $cv_mood_aggr_graph_values_data_qry_result->fetch_assoc())
									    {
									        $cv_mood_aggr_graph_values_data_array['aggressive'] = $cv_mood_aggr_graph_values_data_qry_result_row['aggressive'];
									        $cv_mood_aggr_graph_values_data_array['calm'] = $cv_mood_aggr_graph_values_data_qry_result_row['calm'];
									        $cv_mood_aggr_graph_values_data_array['chilled'] = $cv_mood_aggr_graph_values_data_qry_result_row['chilled'];
									        $cv_mood_aggr_graph_values_data_array['energetic'] = $cv_mood_aggr_graph_values_data_qry_result_row['energetic'];
									        $cv_mood_aggr_graph_values_data_array['happy'] = $cv_mood_aggr_graph_values_data_qry_result_row['happy'];
									        $cv_mood_aggr_graph_values_data_array['romantic'] = $cv_mood_aggr_graph_values_data_qry_result_row['romantic'];
									        $cv_mood_aggr_graph_values_data_array['sad'] = $cv_mood_aggr_graph_values_data_qry_result_row['sad'];
									        $cv_mood_aggr_graph_values_data_array['scary'] = $cv_mood_aggr_graph_values_data_qry_result_row['scary'];
									        $cv_mood_aggr_graph_values_data_array['sexy'] = $cv_mood_aggr_graph_values_data_qry_result_row['sexy'];
									        $cv_mood_aggr_graph_values_data_array['ethereal'] = $cv_mood_aggr_graph_values_data_qry_result_row['ethereal'];
									        $cv_mood_aggr_graph_values_data_array['uplifting'] = $cv_mood_aggr_graph_values_data_qry_result_row['uplifting'];
									    }

									    //print_r($cv_mood_aggr_graph_values_data_array);

									    $cv_mood_aggr_graph_values_arr = $cv_mood_aggr_graph_values_data_array;
									    $cv_mood_aggr_graph_values_arr1 = $cv_mood_aggr_graph_values_data_array;
									    rsort($cv_mood_aggr_graph_values_arr);
									    $top3_mood = array_slice($cv_mood_aggr_graph_values_arr, 0, 3);   
									    foreach ($top3_mood as $mkey => $mval) {
									        //echo "key-".$mkey."----------- val-".$mval."<br>";
									        $mkey = array_search ($mval, $cv_mood_aggr_graph_values_arr1);
									        unset($cv_mood_aggr_graph_values_arr1[$mkey]);
									        //echo $mkey."<br>";
									        $top_3_mood_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT LTrack_id FROM `cyanite` WHERE `brand_id`=".$cv_id."  and is_active=0 and moodtags Like '%".$mkey."%' and process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.LTrack_id";

									        $top_3_mood_video_id_data_result = $conn->query($top_3_mood_video_id_data);

									        if ($top_3_mood_video_id_data_result->num_rows > 0)
									        {
									            $cv_mood_aggr_graph_values_data_array = [];
									            while($top_3_mood_video_id_data_result_row = $top_3_mood_video_id_data_result->fetch_assoc())
									            {
									                //echo $top_3_mood_video_id_data_result_row['video_id']."<br>";
									                $mapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
									                $mvideo_id = $top_3_mood_video_id_data_result_row['video_id'];
									                $mtitle='';
									                $murl = "https://www.googleapis.com/youtube/v3/videos?id=" . $mvideo_id . "&key=" . $mapi_key . "&part=snippet,contentDetails,statistics,status";
									                $mjson = file_get_contents($murl);
									                //$mgetData = json_decode( $mjson , true);
									                $mgetData = json_decode( mb_convert_encoding($mjson, "HTML-ENTITIES", 'UTF-8') , true);
									                foreach((array)$mgetData['items'] as $key => $gDat){
									                    $mtitle = $gDat['snippet']['title'];
									                }
									                // Output title
									                //echo $mtitle."<br><br>";
									                $mgytv_ins_qry .= "'".$mkey."$|$".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
									            }
									        }
									        else
								            {
								                $mvideo_id = '';
								                $mtitle='';
								                $mgytv_ins_qry .= "'".$mvideo_id."','".str_replace("'","\'",$mtitle)."',";
								            }
									    }
									}

									$cv_genre_aggr_graph_values_data_qry = "select `ambient`,`blues`,`classical`,`country`,`electronicDance`,`folk`,`indieAlternative`,`jazz`,`latin`,`metal`,`pop`,`punk`,`rapHipHop`,`reggae`,`rnb`,`rock`,`singerSongwriter` from `tbl_genre_aggr_graph_data` where `cv_id` =".$cv_id." and `is_active`=0";
									$cv_genre_aggr_graph_values_data_result = $conn->query($cv_genre_aggr_graph_values_data_qry);

									if ($cv_genre_aggr_graph_values_data_result->num_rows > 0)
									{
									    $cv_genre_aggr_graph_values_data_array = [];
									    while($cv_genre_aggr_graph_values_data_result_row = $cv_genre_aggr_graph_values_data_result->fetch_assoc())
									    {
									        $cv_genre_aggr_graph_values_data_array['ambient'] = $cv_genre_aggr_graph_values_data_result_row['ambient'];
									        $cv_genre_aggr_graph_values_data_array['blues'] = $cv_genre_aggr_graph_values_data_result_row['blues'];
									        $cv_genre_aggr_graph_values_data_array['classical'] = $cv_genre_aggr_graph_values_data_result_row['classical'];
									        $cv_genre_aggr_graph_values_data_array['country'] = $cv_genre_aggr_graph_values_data_result_row['country'];
									        $cv_genre_aggr_graph_values_data_array['electronicDance'] = $cv_genre_aggr_graph_values_data_result_row['electronicDance'];
									        $cv_genre_aggr_graph_values_data_array['folk'] = $cv_genre_aggr_graph_values_data_result_row['folk'];
									        $cv_genre_aggr_graph_values_data_array['indieAlternative'] = $cv_genre_aggr_graph_values_data_result_row['indieAlternative'];
									        $cv_genre_aggr_graph_values_data_array['jazz'] = $cv_genre_aggr_graph_values_data_result_row['jazz'];
									        $cv_genre_aggr_graph_values_data_array['latin'] = $cv_genre_aggr_graph_values_data_result_row['latin'];
									        $cv_genre_aggr_graph_values_data_array['metal'] = $cv_genre_aggr_graph_values_data_result_row['metal'];
									        $cv_genre_aggr_graph_values_data_array['pop'] = $cv_genre_aggr_graph_values_data_result_row['pop'];
									        $cv_genre_aggr_graph_values_data_array['punk'] = $cv_genre_aggr_graph_values_data_result_row['punk'];
									        $cv_genre_aggr_graph_values_data_array['rapHipHop'] = $cv_genre_aggr_graph_values_data_result_row['rapHipHop'];
									        $cv_genre_aggr_graph_values_data_array['reggae'] = $cv_genre_aggr_graph_values_data_result_row['reggae'];
									        $cv_genre_aggr_graph_values_data_array['rnb'] = $cv_genre_aggr_graph_values_data_result_row['rnb'];
									        $cv_genre_aggr_graph_values_data_array['rock'] = $cv_genre_aggr_graph_values_data_result_row['rock'];
									        $cv_genre_aggr_graph_values_data_array['singerSongwriter'] = $cv_genre_aggr_graph_values_data_result_row['singerSongwriter'];

									    }

									    //print_r($cv_genre_aggr_graph_values_data_array);

									    $cv_genre_aggr_graph_values_arr = $cv_genre_aggr_graph_values_data_array;
									    $cv_genre_aggr_graph_values_arr1 = $cv_genre_aggr_graph_values_data_array;
									    rsort($cv_genre_aggr_graph_values_arr);
									    $top3_genre = array_slice($cv_genre_aggr_graph_values_arr, 0, 3);  
									    foreach ($top3_genre as $gkey => $gval) {
									        //echo "key-".$gkey."----------- val-".$gval."<br>";
									        $gkey = array_search ($gval, $cv_genre_aggr_graph_values_arr1);
									        unset($cv_genre_aggr_graph_values_arr1[$gkey]);
									        //echo $gkey."<br>";
									        $top_3_genre_video_id_data = "SELECT video_id FROM `tbl_social_spyder_graph_meta_data` as a inner join (SELECT LTrack_id FROM `cyanite` WHERE `brand_id`=".$cv_id."  and is_active=0 and genretags Like '%".$gkey."%' and process_type='youtube' LIMIT 1) as a2 ON a.track_id=a2.LTrack_id";

									        $top_3_genre_video_id_data_result = $conn->query($top_3_genre_video_id_data);

									        if ($top_3_genre_video_id_data_result->num_rows > 0)
									        {
									            $cv_genre_aggr_graph_values_data_array = [];
									            while($top_3_genre_video_id_data_result_row = $top_3_genre_video_id_data_result->fetch_assoc())
									            {
									                //echo $top_3_genre_video_id_data_result_row['video_id']."<br>";
									                $gapi_key = "AIzaSyCftMwHzQQ9Tt5KNtbWtAzf9k-2V8WIXU8";
									                $gvideo_id = $top_3_genre_video_id_data_result_row['video_id'];
									                $gtitle='';
									                $gurl = "https://www.googleapis.com/youtube/v3/videos?id=" . $gvideo_id . "&key=" . $gapi_key . "&part=snippet,contentDetails,statistics,status";
									                $gjson = file_get_contents($gurl);
									                //$ggetData = json_decode( $gjson , true);
									                $ggetData = json_decode( mb_convert_encoding($gjson, "HTML-ENTITIES", 'UTF-8') , true);
									                foreach((array)$ggetData['items'] as $key => $gDat){
									                    $gtitle = $gDat['snippet']['title'];
									                }
									                // Output title
									                //echo $gtitle."<br><br>";
									                $mgytv_ins_qry .= "'".$gkey."$|$".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";                
									            }
									        }
									        else
								            {
								                $gtitle=''; 
								                $gvideo_id='';
								                $mgytv_ins_qry .= "'".$gvideo_id."','".str_replace("'","\'",$gtitle)."',";
								            }
									    }
									}
									$final_mgytv_ins_qry = rtrim($mgytv_ins_qry,",").")";
									//echo $final_mgytv_ins_qry;
									if($conn->query($final_mgytv_ins_qry))
									{
										error_log("top 3 mood and genre video id and title of youtube inserted into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
									}
									else
									{
										error_log("someting went wrong while inserting top 3 mood and genre video id and title of youtube into tbl_mood_genre_yt_videos for cv_id - ".$cv_id);
									}
									//get and store top 3 mood and genre video id and title of youtube in tbl_mood_genre_yt_videos end

									// Industry and Sub Industry Graph generation of current cv start
									$get_cv_ind_and_sub_ind_qry = "SELECT * FROM `tbl_cvs` where cv_id = ".$cv_id;
									$get_cv_ind_and_sub_ind_qry_res = $conn->query($get_cv_ind_and_sub_ind_qry);
									$industry_id = '';
									$sub_ind_id = '';
									if ($get_cv_ind_and_sub_ind_qry_res->num_rows > 0) {
				  						while($get_cv_ind_and_sub_ind_qry_res_row = $get_cv_ind_and_sub_ind_qry_res->fetch_assoc()) {
											$industry_id = $get_cv_ind_and_sub_ind_qry_res_row['industry_id'];
											$sub_ind_id = $get_cv_ind_and_sub_ind_qry_res_row['sub_industry_id'];
											$cv_year = $get_cv_ind_and_sub_ind_qry_res_row['cv_year']; 
										}
									}

									// Industry Graph generation start
									if($industry_id != '')
									{
										error_log("Industry graph generation started for Industry - ".$industry_id);
										$ind_cv_ids_arr = array();
										$ind_cv_ids_qry = "SELECT * FROM `tbl_cvs` WHERE industry_id=".$industry_id." and is_active=0 and status=1 and cv_year=".$cv_year;
										$ind_cv_ids_qry_res = $conn->query($ind_cv_ids_qry);
										if($ind_cv_ids_qry_res->num_rows > 0)
										{
											while($ind_cv_ids_qry_res_row = $ind_cv_ids_qry_res->fetch_assoc())
											{
												array_push($ind_cv_ids_arr, $ind_cv_ids_qry_res_row['cv_id']);
											}

											$ind_cv_ids_arr_str = implode(",",$ind_cv_ids_arr);
											error_log($ind_cv_ids_arr_str);
											$chk_pending_request_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` in (".$ind_cv_ids_arr_str.") and is_active=0 and status<2 and chn_notfound = 0";
											$chk_pending_request_qry_res = $conn->query($chk_pending_request_qry);
											error_log($chk_pending_request_qry_res->num_rows);
											if($chk_pending_request_qry_res->num_rows == 0)
											{
												$chk_process_priority_qry = "SELECT * FROM `tbl_social_media_sync_process_data` where cv_id IN (".$ind_cv_ids_arr_str.") and is_active=0 and (yt=0 or ig=0 || tt=0 || twt=0)";
												$chk_process_priority_qry_res = $conn->query($chk_process_priority_qry);
												error_log($chk_process_priority_qry_res->num_rows);
												if($chk_process_priority_qry_res->num_rows == 0)
												{
													$data_inserted_type_arr = array();
													$process_type_arr = array('youtube','instagram','tiktok','twitter');
													for($i=0; $i<count($process_type_arr); $i++)
													{
														for($j=0; $j<count($ind_cv_ids_arr); $j++)
														{
															$get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$ind_cv_ids_arr[$j]." and process_type = '".$process_type_arr[$i]."' and is_active = 0 and `uploaded_start_id` > 0 and `uploaded_end_id` > 0";
															$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
															if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 0)
															{
																$sql_pendings = "SELECT SUM(pending) as pending FROM (";
																$get_track_ids_qry = '';
																while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
																{
																	$sql_pendings .= "select count(id) as pending from tbl_social_spyder_graph_meta_data where cv_id=".$brand_Ids[$i]." and (status < 4) and is_active = 0 and `process_type`='".$PType[$i]."' and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
																	$sql_pendings .= " UNION ";

																	$get_track_ids_qry .= "select track_id from tbl_social_spyder_graph_meta_data where cv_id=".$ind_cv_ids_arr[$j]." and is_active = 0 and `process_type`='".$process_type_arr[$i]."' and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
																	$get_track_ids_qry .= " UNION ";
																}

																$final_sql_pendings = rtrim($sql_pendings," UNION ")." ) sp";
																$final_get_track_ids_qry = rtrim($get_track_ids_qry," UNION ");

																$pendings_result = $conn->query($final_sql_pendings);
																if ($pendings_result->num_rows > 0) { 
																	while($pendings_result_row = $pendings_result->fetch_assoc()) {
																	  	
																	  	if($pendings_result_row["pending"]==0){

																			$get_track_ids_qry_result = $conn->query($final_get_track_ids_qry);
																	  		if ($get_track_ids_qry_result->num_rows > 0) {  
																	  			$track_id_arr = [];
																	  			while($get_track_ids_qry_result_row = $get_track_ids_qry_result->fetch_assoc()) {
																	  				array_push($track_id_arr,$get_track_ids_qry_result_row['track_id']);
																	  			}
																	  			$track_id_str = implode(",", $track_id_arr);
																	  		}
																	  	}
																	}
																}
															}

															$brand_id = $ind_cv_ids_arr[$j];
															$brand_process_type = $process_type_arr[$i];
															switch($brand_process_type){
																case 'youtube':
																	$table_name = "youtube";
																	break;
																case 'instagram':
																	$table_name = "instagram";								
																	break;
																case 'tiktok':
																	$table_name = "tiktok";
																	break;
																case 'twitter':
																	$table_name = "twitter";
																	break;
															}

															$sql_paras = "SELECT 
																		cyanite.LTrack_id,
																		cyanite.brand_id,
																		json_extract(cyanite.mood, '$.aggressive') AS aggressive,
																		json_extract(cyanite.mood, '$.calm') AS calm,
																		json_extract(cyanite.mood, '$.chilled') AS chilled,
																		json_extract(cyanite.mood, '$.dark') AS dark,
																		json_extract(cyanite.mood, '$.energetic') AS energetic,
																		json_extract(cyanite.mood, '$.epic') AS epic,
																		json_extract(cyanite.mood, '$.happy') AS happy,
																		json_extract(cyanite.mood, '$.romantic') AS romantic,
																		json_extract(cyanite.mood, '$.sad') AS sad,
																		json_extract(cyanite.mood, '$.scary') AS scary,
																		json_extract(cyanite.mood, '$.sexy') AS sexy,
																		json_extract(cyanite.mood, '$.ethereal') AS ethereal,
																		json_extract(cyanite.mood, '$.uplifting') AS uplifting,
																		json_extract(cyanite.genre, '$.ambient') AS ambient,
																		json_extract(cyanite.genre, '$.blues') AS blues,
																		json_extract(cyanite.genre, '$.classical') AS classical,
																		json_extract(cyanite.genre, '$.country') AS country,
																		json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
																		json_extract(cyanite.genre, '$.folk') AS folk,
																		json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
																		json_extract(cyanite.genre, '$.jazz') AS jazz,
																		json_extract(cyanite.genre, '$.latin') AS latin,
																		json_extract(cyanite.genre, '$.metal') AS metal,
																		json_extract(cyanite.genre, '$.pop') AS pop,
																		json_extract(cyanite.genre, '$.punk') AS punk,
																		json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
																		json_extract(cyanite.genre, '$.reggae') AS reggae,
																		json_extract(cyanite.genre, '$.rnb') AS rnb,
																		json_extract(cyanite.genre, '$.rock') AS rock,
																		json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$brand_id." and cyanite.process_type='".$brand_process_type."' and cyanite.is_active=0 and cyanite.LTrack_id IN (".$track_id_str.")";
																		
															$result = $conn->query($sql_paras);
															$track_id_array = array();
															$individual_cv_record_counter = 0;						
															if ($result->num_rows > 0) {
																$ins_ind_mood_temp_query = '';
																$ins_ind_mood_temp_query = "insert into tbl_industry_".$table_name."_mood_graph_temp_data (ind_id,ind_year,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
																$ins_ind_aggr_mood_temp_query = '';
																$ins_ind_aggr_mood_temp_query = "insert into tbl_industry_mood_aggr_graph_temp_data (ind_id,ind_year,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
																$ins_ind_genre_temp_query = '';
																$ins_ind_genre_temp_query = "insert into tbl_industry_".$table_name."_genre_graph_temp_data (ind_id,ind_year,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
																$ins_ind_aggr_genre_temp_query = '';
																$ins_ind_aggr_genre_temp_query = "insert into tbl_industry_genre_aggr_graph_temp_data (ind_id,ind_year,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
																while($row = $result->fetch_assoc()) {
																	$LTrack_id = $row["LTrack_id"];
																	//error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
																	//echo "track id extracting : ".$LTrack_id;
																	
																	$ins_ind_mood_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
																	
																	$ins_ind_aggr_mood_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting']."),";
																	
																	
																	$ins_ind_genre_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
																	
																	$ins_ind_aggr_genre_temp_query .= "(".$industry_id.",".$cv_year.",".$row['LTrack_id'].",".$row['ambient'].",".$row['blues'].",".$row['classical'].",".$row['country'].",".$row['electronicDance'].",".$row['folk'].",".$row['indieAlternative'].",".$row['jazz'].",".$row['latin'].",".$row['metal'].",".$row['pop'].",".$row['punk'].",".$row['rapHipHop'].",".$row['reggae'].",".$row['rnb'].",".$row['rock'].",".$row['singerSongwriter']."),";
																	
																	//array_push($track_id_array,$LTrack_id);
																	$individual_cv_record_counter = $individual_cv_record_counter+1;
																		
																}
																if($individual_cv_record_counter >0)
																{
																	$multi_ins_ind_mood_temp_query = rtrim($ins_ind_mood_temp_query,",");
																	error_log("multi_ins_ind_mood_temp_query is generated for".$table_name);
																	
																	$conn->query($multi_ins_ind_mood_temp_query);
																	
																	$multi_ins_ind_aggr_mood_temp_query = rtrim($ins_ind_aggr_mood_temp_query,",");
																	error_log("multi_ins_ind_aggr_mood_temp_query is generated for".$table_name);
																	$conn->query($multi_ins_ind_aggr_mood_temp_query);
																	
																	$multi_ins_ind_genre_temp_query = rtrim($ins_ind_genre_temp_query,",");
																	error_log("multi_ins_ind_genre_temp_query is generated for".$table_name);
																	$conn->query($multi_ins_ind_genre_temp_query);
																	
																	$multi_ins_ind_aggr_genre_temp_query = rtrim($ins_ind_aggr_genre_temp_query,",");
																	error_log("multi_ins_ind_aggr_genre_temp_query is generated for".$table_name);
																	$conn->query($multi_ins_ind_aggr_genre_temp_query);

																	if(!in_array($table_name, $data_inserted_type_arr))
																	{
																		array_push($data_inserted_type_arr,$table_name);
																	}
																}
																else
																{

																}							
																error_log("====================================================================================================================================================");
															}
														}
													}
													if(!empty($data_inserted_type_arr))
													{
														for($i=0; $i<count($data_inserted_type_arr); $i++)
														{
															$sql_ind_mood_avg_query = '';
															$sql_ind_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`= ".$cv_year;
															error_log("sql_ind_".$data_inserted_type_arr[$i]."_mood_avg_query:".$sql_ind_mood_avg_query);
															$result = $conn->query($sql_ind_mood_avg_query);
															if ($result->num_rows > 0) {
																while($row = $result->fetch_assoc()) {
																	$chk_mood_aggr_qry = "SELECT * FROM tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	$chk_mood_aggr_qry_result = $conn->query($chk_mood_aggr_qry);
																	if($chk_mood_aggr_qry_result->num_rows > 0)
																	{
																		$s1 = "UPDATE tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data SET `aggressive`=".$row['aggressive'].",`calm`=".$row['calm'].",`chilled`=".$row['chilled'].",`dark`=".$row['dark'].",`energetic`=".$row['energetic'].",`epic`=".$row['epic'].",`happy`=".$row['happy'].",`romantic`=".$row['romantic'].",`sad`=".$row['sad'].",`scary`=".$row['scary'].",`sexy`=".$row['sexy'].",`ethereal`=".$row['ethereal'].",`uplifting`=".$row['uplifting']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	}
																	else
																	{
																		$s1 = "insert into tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data (ind_id,ind_year,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$industry_id.",".$cv_year.",".$row['aggressive'].",".$row['calm'].",".$row['chilled'].",".$row['dark'].",".$row['energetic'].",".$row['epic'].",".$row['happy'].",".$row['romantic'].",".$row['sad'].",".$row['scary'].",".$row['sexy'].",".$row['ethereal'].",".$row['uplifting'].")";
																	}													

																	error_log("tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_data:".$s1);
																	$conn->query($s1);
																	$conn->query("DELETE FROM tbl_industry_".$data_inserted_type_arr[$i]."_mood_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
																}
															}
															$sql_ind_genre_avg_query = '';
															$sql_ind_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`= ".$cv_year;
															error_log("sql_ind_".$data_inserted_type_arr[$i]."_genre_avg_query:".$sql_ind_genre_avg_query);
															$result1 = $conn->query($sql_ind_genre_avg_query);
															if ($result1->num_rows > 0) {
																while($row1 = $result1->fetch_assoc()) {
																	$chk_ind_genre_aggr_qry = "SELECT * FROM tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	$chk_ind_genre_aggr_qry_result = $conn->query($chk_ind_genre_aggr_qry);
																	if($chk_ind_genre_aggr_qry_result->num_rows > 0)
																	{
																		$s11 = "UPDATE tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data SET `ambient`=".$row1['ambient'].",`blues`=".$row1['blues'].",`classical`=".$row1['classical'].",`country`=".$row1['country'].",`electronicDance`=".$row1['electronicDance'].",`folk`=".$row1['folk'].",`indieAlternative`=".$row1['indieAlternative'].",`jazz`=".$row1['jazz'].",`latin`=".$row1['latin'].",`metal`=".$row1['metal'].",`pop`=".$row1['pop'].",`punk`=".$row1['punk'].",`rapHipHop`=".$row1['rapHipHop'].",`reggae`=".$row1['reggae'].",`rnb`=".$row1['rnb'].",`rock`=".$row1['rock'].",`singerSongwriter`=".$row1['singerSongwriter']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	}
																	else
																	{
																		$s11 = "insert into tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data (ind_id,ind_year,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$industry_id.",".$cv_year.",".$row1['ambient'].",".$row1['blues'].",".$row1['classical'].",".$row1['country'].",".$row1['electronicDance'].",".$row1['folk'].",".$row1['indieAlternative'].",".$row1['jazz'].",".$row1['latin'].",".$row1['metal'].",".$row1['pop'].",".$row1['punk'].",".$row1['rapHipHop'].",".$row1['reggae'].",".$row1['rnb'].",".$row1['rock'].",".$row1['singerSongwriter'].")";
																	}

																	error_log("tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_data:".$s11);
																	$conn->query($s11);
																	$conn->query("DELETE FROM tbl_industry_".$data_inserted_type_arr[$i]."_genre_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
																}
															}
														}
														

														if(count($data_inserted_type_arr)>1)
														{
															$sql_ind_aggr_mood_avg_query = '';
															$sql_ind_aggr_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_industry_mood_aggr_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`=".$cv_year;
															error_log("sql_ind_aggr_mood_avg_query:".$sql_ind_aggr_mood_avg_query);
															$ind_result = $conn->query($sql_ind_aggr_mood_avg_query);
															if ($ind_result->num_rows > 0) {
																while($ind_result_row = $ind_result->fetch_assoc()) {
																	$chk_ind_mood_aggr_qry = "SELECT * FROM `tbl_industry_mood_aggr_graph_data` WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	$chk_ind_mood_aggr_qry_result = $conn->query($chk_ind_mood_aggr_qry);
																	if($chk_ind_mood_aggr_qry_result->num_rows > 0)
																	{
																		$ind_s1 = "UPDATE `tbl_industry_mood_aggr_graph_data` SET `aggressive`=".$ind_result_row['aggressive'].",`calm`=".$ind_result_row['calm'].",`chilled`=".$ind_result_row['chilled'].",`dark`=".$ind_result_row['dark'].",`energetic`=".$ind_result_row['energetic'].",`epic`=".$ind_result_row['epic'].",`happy`=".$ind_result_row['happy'].",`romantic`=".$ind_result_row['romantic'].",`sad`=".$ind_result_row['sad'].",`scary`=".$ind_result_row['scary'].",`sexy`=".$ind_result_row['sexy'].",`ethereal`=".$ind_result_row['ethereal'].",`uplifting`=".$ind_result_row['uplifting']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	}
																	else
																	{
																		$ind_s1 = "insert into tbl_industry_mood_aggr_graph_data (ind_id,ind_year,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$industry_id.",".$cv_year.",".$ind_result_row['aggressive'].",".$ind_result_row['calm'].",".$ind_result_row['chilled'].",".$ind_result_row['dark'].",".$ind_result_row['energetic'].",".$ind_result_row['epic'].",".$ind_result_row['happy'].",".$ind_result_row['romantic'].",".$ind_result_row['sad'].",".$ind_result_row['scary'].",".$ind_result_row['sexy'].",".$ind_result_row['ethereal'].",".$ind_result_row['uplifting'].")";
																	}
																	error_log("tbl_industry_mood_aggr_graph_data:".$ind_s1);
																	$conn->query($ind_s1);
																}
															}									
																			
																				
															$sql_ind_aggr_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_industry_genre_aggr_graph_temp_data` WHERE `ind_id` =".$industry_id." AND `ind_year`=".$cv_year;
															error_log("sql_ind_aggr_genre_avg_query:".$sql_ind_aggr_genre_avg_query);
															$ind_result1 = $conn->query($sql_ind_aggr_genre_avg_query);
															if ($ind_result1->num_rows > 0) {
																while($ind_result1_row = $ind_result1->fetch_assoc()) {
																	$chk_ind_genre_aggr_qry = "SELECT * FROM `tbl_industry_genre_aggr_graph_data` WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	$chk_ind_genre_aggr_qry_result = $conn->query($chk_ind_genre_aggr_qry);
																	if($chk_ind_genre_aggr_qry_result->num_rows > 0)
																	{
																		$ind_s11 = "UPDATE `tbl_industry_genre_aggr_graph_data` SET `ambient`=".$ind_result1_row['ambient'].",`blues`=".$ind_result1_row['blues'].",`classical`=".$ind_result1_row['classical'].",`country`=".$ind_result1_row['country'].",`electronicDance`=".$ind_result1_row['electronicDance'].",`folk`=".$ind_result1_row['folk'].",`indieAlternative`=".$ind_result1_row['indieAlternative'].",`jazz`=".$ind_result1_row['jazz'].",`latin`=".$ind_result1_row['latin'].",`metal`=".$ind_result1_row['metal'].",`pop`=".$ind_result1_row['pop'].",`punk`=".$ind_result1_row['punk'].",`rapHipHop`=".$ind_result1_row['rapHipHop'].",`reggae`=".$ind_result1_row['reggae'].",`rnb`=".$ind_result1_row['rnb'].",`rock`=".$ind_result1_row['rock'].",`singerSongwriter`=".$ind_result1_row['singerSongwriter']." WHERE `ind_id`=".$industry_id." and `ind_year`=".$cv_year;
																	}
																	else
																	{
																		$ind_s11 = "insert into tbl_industry_genre_aggr_graph_data (ind_id,ind_year,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$industry_id.",".$cv_year.",".$ind_result1_row['ambient'].",".$ind_result1_row['blues'].",".$ind_result1_row['classical'].",".$ind_result1_row['country'].",".$ind_result1_row['electronicDance'].",".$ind_result1_row['folk'].",".$ind_result1_row['indieAlternative'].",".$ind_result1_row['jazz'].",".$ind_result1_row['latin'].",".$ind_result1_row['metal'].",".$ind_result1_row['pop'].",".$ind_result1_row['punk'].",".$ind_result1_row['rapHipHop'].",".$ind_result1_row['reggae'].",".$ind_result1_row['rnb'].",".$ind_result1_row['rock'].",".$ind_result1_row['singerSongwriter'].")";
																	}							

																	error_log("tbl_industry_genre_aggr_graph_data:".$ind_s11);
																	$conn->query($ind_s11);
																}
															}
														}
														$conn->query("DELETE FROM tbl_industry_mood_aggr_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
														$conn->query("DELETE FROM tbl_industry_genre_aggr_graph_temp_data WHERE ind_id=".$industry_id." and ind_year=".$cv_year);
														error_log("Message : Both Graphs Average Data inserted");
														error_log("====================================================================================================================================================");
													}
												}
												else
												{
													error_log("There are some cvs of industry - ".$industry_id." ".$cv_year." pending in priority process tbl for download");
												}
												
											}
											else
											{
												error_log("There are some cvs of industry - ".$industry_id." ".$cv_year." pending for download content");
											}
										}
										error_log("Industry graph generation completed for Industry - ".$industry_id." ".$cv_year);
									}
									// Industry Graph generation end

									// Sub Industry Graph generation start
									if($sub_ind_id != '')
									{										
										error_log("Sub Industry graph generation started for Sub Industry - ".$sub_ind_id." ".$cv_year);
										$sind_cv_ids_arr = array();
										$sind_cv_ids_qry = "SELECT * FROM `tbl_cvs` WHERE industry_id=".$industry_id." and sub_industry_id = ".$sub_ind_id." and is_active=0 and status=1 and cv_year=".$cv_year;
										//echo $sind_cv_ids_qry;
										//echo "<br>";
										$sind_cv_ids_qry_res = $conn->query($sind_cv_ids_qry);
										if($sind_cv_ids_qry_res->num_rows > 0)
										{
											while($sind_cv_ids_qry_res_row = $sind_cv_ids_qry_res->fetch_assoc())
											{
												array_push($sind_cv_ids_arr, $sind_cv_ids_qry_res_row['cv_id']);
											}

											$sind_cv_ids_arr_str = implode(",",$sind_cv_ids_arr);
											error_log("sind_cv_ids_arr_str - ".$sind_cv_ids_arr_str);
											//echo "<br>";

											$chk_sind_pending_request_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` in (".$sind_cv_ids_arr_str.") and is_active=0 and status<2";
											$chk_sind_pending_request_qry_res = $conn->query($chk_sind_pending_request_qry);
											error_log($chk_sind_pending_request_qry_res->num_rows);
											//echo "<br>";
											if($chk_sind_pending_request_qry_res->num_rows == 0)
											{
												$chk_sind_process_priority_qry = "SELECT * FROM `tbl_social_media_sync_process_data` where cv_id IN (".$sind_cv_ids_arr_str.") and is_active=0 and (yt<2 or ig<2 or tt<2 or twt<2)";
												$chk_sind_process_priority_qry_res = $conn->query($chk_sind_process_priority_qry);
												error_log($chk_sind_process_priority_qry_res->num_rows);
												//echo "<br>";
												if($chk_sind_process_priority_qry_res->num_rows == 0)
												{
													$sind_data_inserted_type_arr = array();
													$sind_process_type_arr = array('youtube','instagram','tiktok','twitter');
													for($i=0; $i<count($sind_process_type_arr); $i++)
													{
														for($j=0; $j<count($sind_cv_ids_arr); $j++)
														{
															$get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$sind_cv_ids_arr[$j]." and process_type = '".$sind_process_type_arr[$i]."' and is_active = 0 and `uploaded_start_id` > 0 and `uploaded_end_id` > 0";
															$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
															if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 0)
															{
																$sql_pendings = "SELECT SUM(pending) as pending FROM (";
																$get_track_ids_qry = '';
																while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
																{
																	$sql_pendings .= "select count(id) as pending from tbl_social_spyder_graph_meta_data where cv_id=".$brand_Ids[$i]." and (status < 4) and is_active = 0 and `process_type`='".$PType[$i]."' and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
																	$sql_pendings .= " UNION ";

																	$get_track_ids_qry .= "select track_id from tbl_social_spyder_graph_meta_data where cv_id=".$sind_cv_ids_arr[$j]." and is_active = 0 and `process_type`='".$sind_process_type_arr[$i]."' and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
																	$get_track_ids_qry .= " UNION ";
																}

																$final_sql_pendings = rtrim($sql_pendings," UNION ")." ) sp";
																$final_get_track_ids_qry = rtrim($get_track_ids_qry," UNION ");

																$pendings_result = $conn->query($final_sql_pendings);
																if ($pendings_result->num_rows > 0) { 
																	while($pendings_result_row = $pendings_result->fetch_assoc()) {
																	  	
																	  	if($pendings_result_row["pending"]==0){
																			$get_track_ids_qry_result = $conn->query($final_get_track_ids_qry);
																	  		if ($get_track_ids_qry_result->num_rows > 0) {  
																	  			$track_id_arr = [];
																	  			while($get_track_ids_qry_result_row = $get_track_ids_qry_result->fetch_assoc()) {
																	  				array_push($track_id_arr,$get_track_ids_qry_result_row['track_id']);
																	  			}
																	  			$track_id_str = implode(",", $track_id_arr);
																	  		}
																	  	}
																	}
																}
															}
															$sind_brand_id = $sind_cv_ids_arr[$j];
															$sind_brand_process_type = $sind_process_type_arr[$i];
															switch($sind_brand_process_type){
																case 'youtube':
																	$sind_table_name = "youtube";
																	break;
																case 'instagram':
																	$sind_table_name = "instagram";								
																	break;
																case 'tiktok':
																	$sind_table_name = "tiktok";
																	break;
																case 'twitter':
																	$sind_table_name = "twitter";
																	break;
															}

															$sind_sql_paras = "SELECT 
																		cyanite.LTrack_id,
																		cyanite.brand_id,
																		json_extract(cyanite.mood, '$.aggressive') AS aggressive,
																		json_extract(cyanite.mood, '$.calm') AS calm,
																		json_extract(cyanite.mood, '$.chilled') AS chilled,
																		json_extract(cyanite.mood, '$.dark') AS dark,
																		json_extract(cyanite.mood, '$.energetic') AS energetic,
																		json_extract(cyanite.mood, '$.epic') AS epic,
																		json_extract(cyanite.mood, '$.happy') AS happy,
																		json_extract(cyanite.mood, '$.romantic') AS romantic,
																		json_extract(cyanite.mood, '$.sad') AS sad,
																		json_extract(cyanite.mood, '$.scary') AS scary,
																		json_extract(cyanite.mood, '$.sexy') AS sexy,
																		json_extract(cyanite.mood, '$.ethereal') AS ethereal,
																		json_extract(cyanite.mood, '$.uplifting') AS uplifting,
																		json_extract(cyanite.genre, '$.ambient') AS ambient,
																		json_extract(cyanite.genre, '$.blues') AS blues,
																		json_extract(cyanite.genre, '$.classical') AS classical,
																		json_extract(cyanite.genre, '$.country') AS country,
																		json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
																		json_extract(cyanite.genre, '$.folk') AS folk,
																		json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
																		json_extract(cyanite.genre, '$.jazz') AS jazz,
																		json_extract(cyanite.genre, '$.latin') AS latin,
																		json_extract(cyanite.genre, '$.metal') AS metal,
																		json_extract(cyanite.genre, '$.pop') AS pop,
																		json_extract(cyanite.genre, '$.punk') AS punk,
																		json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
																		json_extract(cyanite.genre, '$.reggae') AS reggae,
																		json_extract(cyanite.genre, '$.rnb') AS rnb,
																		json_extract(cyanite.genre, '$.rock') AS rock,
																		json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter FROM cyanite where cyanite.brand_id=".$sind_brand_id." and cyanite.process_type='".$sind_brand_process_type."' and cyanite.is_active=0 and cyanite.LTrack_id IN (".$track_id_str.")";
																		
															$sind_sql_paras_result = $conn->query($sind_sql_paras);
															$sind_track_id_array = array();
															$sind_individual_cv_record_counter = 0;						
															if ($sind_sql_paras_result->num_rows > 0) {
																$ins_sind_mood_temp_query = '';
																$ins_sind_mood_temp_query = "insert into tbl_sub_industry_".$sind_table_name."_mood_graph_temp_data (sind_id,sind_year,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
																$ins_sind_aggr_mood_temp_query = '';
																$ins_sind_aggr_mood_temp_query = "insert into tbl_sub_industry_mood_aggr_graph_temp_data (sind_id,sind_year,track_id,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";
																$ins_sind_genre_temp_query = '';
																$ins_sind_genre_temp_query = "insert into tbl_sub_industry_".$sind_table_name."_genre_graph_temp_data (sind_id,sind_year,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
																$ins_sind_aggr_genre_temp_query = '';
																$ins_sind_aggr_genre_temp_query = "insert into tbl_sub_industry_genre_aggr_graph_temp_data (sind_id,sind_year,track_id,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
																while($sind_sql_paras_result_row = $sind_sql_paras_result->fetch_assoc()) {
																	$sind_LTrack_id = $sind_sql_paras_result_row["LTrack_id"];
																	//error_log("----------------------------------------------------------------------------------------------------------------------------------------------------");
																	//echo "<br>";
																	//echo "track id extracting : ".$sind_LTrack_id;
																	
																	$ins_sind_mood_temp_query .= "(".$sub_ind_id.",".$cv_year.",".$sind_sql_paras_result_row['LTrack_id'].",".$sind_sql_paras_result_row['aggressive'].",".$sind_sql_paras_result_row['calm'].",".$sind_sql_paras_result_row['chilled'].",".$sind_sql_paras_result_row['dark'].",".$sind_sql_paras_result_row['energetic'].",".$sind_sql_paras_result_row['epic'].",".$sind_sql_paras_result_row['happy'].",".$sind_sql_paras_result_row['romantic'].",".$sind_sql_paras_result_row['sad'].",".$sind_sql_paras_result_row['scary'].",".$sind_sql_paras_result_row['sexy'].",".$sind_sql_paras_result_row['ethereal'].",".$sind_sql_paras_result_row['uplifting']."),";
																	
																	$ins_sind_aggr_mood_temp_query .= "(".$sub_ind_id.",".$cv_year.",".$sind_sql_paras_result_row['LTrack_id'].",".$sind_sql_paras_result_row['aggressive'].",".$sind_sql_paras_result_row['calm'].",".$sind_sql_paras_result_row['chilled'].",".$sind_sql_paras_result_row['dark'].",".$sind_sql_paras_result_row['energetic'].",".$sind_sql_paras_result_row['epic'].",".$sind_sql_paras_result_row['happy'].",".$sind_sql_paras_result_row['romantic'].",".$sind_sql_paras_result_row['sad'].",".$sind_sql_paras_result_row['scary'].",".$sind_sql_paras_result_row['sexy'].",".$sind_sql_paras_result_row['ethereal'].",".$sind_sql_paras_result_row['uplifting']."),";
																	
																	
																	$ins_sind_genre_temp_query .= "(".$sub_ind_id.",".$cv_year.",".$sind_sql_paras_result_row['LTrack_id'].",".$sind_sql_paras_result_row['ambient'].",".$sind_sql_paras_result_row['blues'].",".$sind_sql_paras_result_row['classical'].",".$sind_sql_paras_result_row['country'].",".$sind_sql_paras_result_row['electronicDance'].",".$sind_sql_paras_result_row['folk'].",".$sind_sql_paras_result_row['indieAlternative'].",".$sind_sql_paras_result_row['jazz'].",".$sind_sql_paras_result_row['latin'].",".$sind_sql_paras_result_row['metal'].",".$sind_sql_paras_result_row['pop'].",".$sind_sql_paras_result_row['punk'].",".$sind_sql_paras_result_row['rapHipHop'].",".$sind_sql_paras_result_row['reggae'].",".$sind_sql_paras_result_row['rnb'].",".$sind_sql_paras_result_row['rock'].",".$sind_sql_paras_result_row['singerSongwriter']."),";
																	
																	$ins_sind_aggr_genre_temp_query .= "(".$sub_ind_id.",".$cv_year.",".$sind_sql_paras_result_row['LTrack_id'].",".$sind_sql_paras_result_row['ambient'].",".$sind_sql_paras_result_row['blues'].",".$sind_sql_paras_result_row['classical'].",".$sind_sql_paras_result_row['country'].",".$sind_sql_paras_result_row['electronicDance'].",".$sind_sql_paras_result_row['folk'].",".$sind_sql_paras_result_row['indieAlternative'].",".$sind_sql_paras_result_row['jazz'].",".$sind_sql_paras_result_row['latin'].",".$sind_sql_paras_result_row['metal'].",".$sind_sql_paras_result_row['pop'].",".$sind_sql_paras_result_row['punk'].",".$sind_sql_paras_result_row['rapHipHop'].",".$sind_sql_paras_result_row['reggae'].",".$sind_sql_paras_result_row['rnb'].",".$sind_sql_paras_result_row['rock'].",".$sind_sql_paras_result_row['singerSongwriter']."),";
																	
																	//array_push($sind_track_id_array,$sind_LTrack_id);
																	$sind_individual_cv_record_counter = $sind_individual_cv_record_counter+1;
																		
																}
																if($sind_individual_cv_record_counter >0)
																{
																	$sind_multi_ins_ind_mood_temp_query = rtrim($ins_sind_mood_temp_query,",");
																	error_log("multi_ins_sind_mood_temp_query is generated for".$sind_table_name);
																	//echo "<br>";
																	
																	$conn->query($sind_multi_ins_ind_mood_temp_query);
																	
																	$sind_multi_ins_ind_aggr_mood_temp_query = rtrim($ins_sind_aggr_mood_temp_query,",");
																	error_log("multi_ins_sind_aggr_mood_temp_query is generated for".$sind_table_name);
																	//echo "<br>";
																	$conn->query($sind_multi_ins_ind_aggr_mood_temp_query);
																	
																	$sind_multi_ins_ind_genre_temp_query = rtrim($ins_sind_genre_temp_query,",");
																	error_log("multi_ins_sind_genre_temp_query is generated for".$sind_table_name);
																	//echo "<br>";
																	$conn->query($sind_multi_ins_ind_genre_temp_query);
																	
																	$sind_multi_ins_ind_aggr_genre_temp_query = rtrim($ins_sind_aggr_genre_temp_query,",");
																	error_log("multi_ins_sind_aggr_genre_temp_query is generated for".$sind_table_name);
																	//echo "<br>";
																	$conn->query($sind_multi_ins_ind_aggr_genre_temp_query);

																	if(!in_array($sind_table_name, $sind_data_inserted_type_arr))
																	{
																		array_push($sind_data_inserted_type_arr,$sind_table_name);
																	}
																}
																else
																{

																}							
																error_log("====================================================================================================================================================");
															}
														}
													}
													if(!empty($sind_data_inserted_type_arr))
													{
														for($i=0; $i<count($sind_data_inserted_type_arr); $i++)
														{
															$sql_sind_mood_avg_query = '';
															$sql_sind_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_mood_graph_temp_data` WHERE `sind_id` =".$sub_ind_id." AND `sind_year`= ".$cv_year;
															error_log("sql_sind_".$sind_data_inserted_type_arr[$i]."_mood_avg_query:".$sql_sind_mood_avg_query);
															//echo "<br>";
															$sql_sind_mood_avg_query_result = $conn->query($sql_sind_mood_avg_query);
															if ($sql_sind_mood_avg_query_result->num_rows > 0) {
																while($sql_sind_mood_avg_query_result_row = $sql_sind_mood_avg_query_result->fetch_assoc()) {
																	$chk_sind_mood_aggr_qry = "SELECT * FROM tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_mood_graph_data WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	$chk_sind_mood_aggr_qry_result = $conn->query($chk_sind_mood_aggr_qry);
																	if($chk_sind_mood_aggr_qry_result->num_rows > 0)
																	{
																		$sind_s1 = "UPDATE tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_mood_graph_data SET `aggressive`=".$sql_sind_mood_avg_query_result_row['aggressive'].",`calm`=".$sql_sind_mood_avg_query_result_row['calm'].",`chilled`=".$sql_sind_mood_avg_query_result_row['chilled'].",`dark`=".$sql_sind_mood_avg_query_result_row['dark'].",`energetic`=".$sql_sind_mood_avg_query_result_row['energetic'].",`epic`=".$sql_sind_mood_avg_query_result_row['epic'].",`happy`=".$sql_sind_mood_avg_query_result_row['happy'].",`romantic`=".$sql_sind_mood_avg_query_result_row['romantic'].",`sad`=".$sql_sind_mood_avg_query_result_row['sad'].",`scary`=".$sql_sind_mood_avg_query_result_row['scary'].",`sexy`=".$sql_sind_mood_avg_query_result_row['sexy'].",`ethereal`=".$sql_sind_mood_avg_query_result_row['ethereal'].",`uplifting`=".$sql_sind_mood_avg_query_result_row['uplifting']." WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	}
																	else
																	{
																		$sind_s1 = "insert into tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_mood_graph_data (sind_id,sind_year,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$sub_ind_id.",".$cv_year.",".$sql_sind_mood_avg_query_result_row['aggressive'].",".$sql_sind_mood_avg_query_result_row['calm'].",".$sql_sind_mood_avg_query_result_row['chilled'].",".$sql_sind_mood_avg_query_result_row['dark'].",".$sql_sind_mood_avg_query_result_row['energetic'].",".$sql_sind_mood_avg_query_result_row['epic'].",".$sql_sind_mood_avg_query_result_row['happy'].",".$sql_sind_mood_avg_query_result_row['romantic'].",".$sql_sind_mood_avg_query_result_row['sad'].",".$sql_sind_mood_avg_query_result_row['scary'].",".$sql_sind_mood_avg_query_result_row['sexy'].",".$sql_sind_mood_avg_query_result_row['ethereal'].",".$sql_sind_mood_avg_query_result_row['uplifting'].")";
																	}													

																	error_log("tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_mood_graph_data:".$sind_s1);
																	//echo "<br>";
																	$conn->query($sind_s1);
																	$conn->query("DELETE FROM tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_mood_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
																}
															}
															$sql_sind_genre_avg_query = '';
															$sql_sind_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_genre_graph_temp_data` WHERE `sind_id` =".$sub_ind_id." AND `sind_year`= ".$cv_year;
															error_log("sql_sind_".$sind_data_inserted_type_arr[$i]."_genre_avg_query:".$sql_sind_genre_avg_query);
															//echo "<br>";
															$sql_sind_genre_avg_query_result = $conn->query($sql_sind_genre_avg_query);
															if ($sql_sind_genre_avg_query_result->num_rows > 0) {
																while($sql_sind_genre_avg_query_result_row = $sql_sind_genre_avg_query_result->fetch_assoc()) {
																	$chk_sind_genre_aggr_qry = "SELECT * FROM tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_genre_graph_data WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	$chk_sind_genre_aggr_qry_result = $conn->query($chk_sind_genre_aggr_qry);
																	if($chk_sind_genre_aggr_qry_result->num_rows > 0)
																	{
																		$sind_s11 = "UPDATE tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_genre_graph_data SET `ambient`=".$sql_sind_genre_avg_query_result_row['ambient'].",`blues`=".$sql_sind_genre_avg_query_result_row['blues'].",`classical`=".$sql_sind_genre_avg_query_result_row['classical'].",`country`=".$sql_sind_genre_avg_query_result_row['country'].",`electronicDance`=".$sql_sind_genre_avg_query_result_row['electronicDance'].",`folk`=".$sql_sind_genre_avg_query_result_row['folk'].",`indieAlternative`=".$sql_sind_genre_avg_query_result_row['indieAlternative'].",`jazz`=".$sql_sind_genre_avg_query_result_row['jazz'].",`latin`=".$sql_sind_genre_avg_query_result_row['latin'].",`metal`=".$sql_sind_genre_avg_query_result_row['metal'].",`pop`=".$sql_sind_genre_avg_query_result_row['pop'].",`punk`=".$sql_sind_genre_avg_query_result_row['punk'].",`rapHipHop`=".$sql_sind_genre_avg_query_result_row['rapHipHop'].",`reggae`=".$sql_sind_genre_avg_query_result_row['reggae'].",`rnb`=".$sql_sind_genre_avg_query_result_row['rnb'].",`rock`=".$sql_sind_genre_avg_query_result_row['rock'].",`singerSongwriter`=".$sql_sind_genre_avg_query_result_row['singerSongwriter']." WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	}
																	else
																	{
																		$sind_s11 = "insert into tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_genre_graph_data (sind_id,sind_year,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$sub_ind_id.",".$cv_year.",".$sql_sind_genre_avg_query_result_row['ambient'].",".$sql_sind_genre_avg_query_result_row['blues'].",".$sql_sind_genre_avg_query_result_row['classical'].",".$sql_sind_genre_avg_query_result_row['country'].",".$sql_sind_genre_avg_query_result_row['electronicDance'].",".$sql_sind_genre_avg_query_result_row['folk'].",".$sql_sind_genre_avg_query_result_row['indieAlternative'].",".$sql_sind_genre_avg_query_result_row['jazz'].",".$sql_sind_genre_avg_query_result_row['latin'].",".$sql_sind_genre_avg_query_result_row['metal'].",".$sql_sind_genre_avg_query_result_row['pop'].",".$sql_sind_genre_avg_query_result_row['punk'].",".$sql_sind_genre_avg_query_result_row['rapHipHop'].",".$sql_sind_genre_avg_query_result_row['reggae'].",".$sql_sind_genre_avg_query_result_row['rnb'].",".$sql_sind_genre_avg_query_result_row['rock'].",".$sql_sind_genre_avg_query_result_row['singerSongwriter'].")";
																	}

																	error_log("tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_genre_graph_data:".$sind_s11);
																	//echo "<br>";
																	$conn->query($sind_s11);
																	$conn->query("DELETE FROM tbl_sub_industry_".$sind_data_inserted_type_arr[$i]."_genre_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
																}
															}
														}
														

														if(count($sind_data_inserted_type_arr)>1)
														{
															$sql_sind_aggr_mood_avg_query = '';
															$sql_sind_aggr_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_sub_industry_mood_aggr_graph_temp_data` WHERE `sind_id` =".$sub_ind_id." AND `sind_year`=".$cv_year;
															error_log("sql_sind_aggr_mood_avg_query:".$sql_sind_aggr_mood_avg_query);
															//echo "<br>";
															$sql_sind_aggr_mood_avg_query_result = $conn->query($sql_sind_aggr_mood_avg_query);
															if ($sql_sind_aggr_mood_avg_query_result->num_rows > 0) {
																while($sql_sind_aggr_mood_avg_query_result_row = $sql_sind_aggr_mood_avg_query_result->fetch_assoc()) {
																	$chk_ind_mood_aggr_qry = "SELECT * FROM `tbl_sub_industry_mood_aggr_graph_data` WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	$chk_ind_mood_aggr_qry_result = $conn->query($chk_ind_mood_aggr_qry);
																	if($chk_ind_mood_aggr_qry_result->num_rows > 0)
																	{
																		$sind_s1 = "UPDATE `tbl_sub_industry_mood_aggr_graph_data` SET `aggressive`=".$sql_sind_aggr_mood_avg_query_result_row['aggressive'].",`calm`=".$sql_sind_aggr_mood_avg_query_result_row['calm'].",`chilled`=".$sql_sind_aggr_mood_avg_query_result_row['chilled'].",`dark`=".$sql_sind_aggr_mood_avg_query_result_row['dark'].",`energetic`=".$sql_sind_aggr_mood_avg_query_result_row['energetic'].",`epic`=".$sql_sind_aggr_mood_avg_query_result_row['epic'].",`happy`=".$sql_sind_aggr_mood_avg_query_result_row['happy'].",`romantic`=".$sql_sind_aggr_mood_avg_query_result_row['romantic'].",`sad`=".$sql_sind_aggr_mood_avg_query_result_row['sad'].",`scary`=".$sql_sind_aggr_mood_avg_query_result_row['scary'].",`sexy`=".$sql_sind_aggr_mood_avg_query_result_row['sexy'].",`ethereal`=".$sql_sind_aggr_mood_avg_query_result_row['ethereal'].",`uplifting`=".$sql_sind_aggr_mood_avg_query_result_row['uplifting']." WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	}
																	else
																	{
																		$sind_s1 = "insert into tbl_sub_industry_mood_aggr_graph_data (sind_id,sind_year,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$sub_ind_id.",".$cv_year.",".$sql_sind_aggr_mood_avg_query_result_row['aggressive'].",".$sql_sind_aggr_mood_avg_query_result_row['calm'].",".$sql_sind_aggr_mood_avg_query_result_row['chilled'].",".$sql_sind_aggr_mood_avg_query_result_row['dark'].",".$sql_sind_aggr_mood_avg_query_result_row['energetic'].",".$sql_sind_aggr_mood_avg_query_result_row['epic'].",".$sql_sind_aggr_mood_avg_query_result_row['happy'].",".$sql_sind_aggr_mood_avg_query_result_row['romantic'].",".$sql_sind_aggr_mood_avg_query_result_row['sad'].",".$sql_sind_aggr_mood_avg_query_result_row['scary'].",".$sql_sind_aggr_mood_avg_query_result_row['sexy'].",".$sql_sind_aggr_mood_avg_query_result_row['ethereal'].",".$sql_sind_aggr_mood_avg_query_result_row['uplifting'].")";
																	}
																	error_log("tbl_sub_industry_mood_aggr_graph_data:".$sind_s1);
																	//echo "<br>";
																	$conn->query($sind_s1);
																}
															}									
																			
															$sql_sind_aggr_genre_avg_query = '';				
															$sql_sind_aggr_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_sub_industry_genre_aggr_graph_temp_data` WHERE `sind_id` =".$sub_ind_id." AND `sind_year`=".$cv_year;
															error_log("sql_ind_aggr_genre_avg_query:".$sql_sind_aggr_genre_avg_query);
															//echo "<br>";
															$sql_sind_aggr_genre_avg_query_result = $conn->query($sql_sind_aggr_genre_avg_query);
															if ($sql_sind_aggr_genre_avg_query_result->num_rows > 0) {
																while($sql_sind_aggr_genre_avg_query_result_row = $sql_sind_aggr_genre_avg_query_result->fetch_assoc()) {
																	$chk_sind_genre_aggr_qry = "SELECT * FROM `tbl_sub_industry_genre_aggr_graph_data` WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	$chk_sind_genre_aggr_qry_result = $conn->query($chk_sind_genre_aggr_qry);
																	if($chk_sind_genre_aggr_qry_result->num_rows > 0)
																	{
																		$sind_s11 = "UPDATE `tbl_sub_industry_genre_aggr_graph_data` SET `ambient`=".$sql_sind_aggr_genre_avg_query_result_row['ambient'].",`blues`=".$sql_sind_aggr_genre_avg_query_result_row['blues'].",`classical`=".$sql_sind_aggr_genre_avg_query_result_row['classical'].",`country`=".$sql_sind_aggr_genre_avg_query_result_row['country'].",`electronicDance`=".$sql_sind_aggr_genre_avg_query_result_row['electronicDance'].",`folk`=".$sql_sind_aggr_genre_avg_query_result_row['folk'].",`indieAlternative`=".$sql_sind_aggr_genre_avg_query_result_row['indieAlternative'].",`jazz`=".$sql_sind_aggr_genre_avg_query_result_row['jazz'].",`latin`=".$sql_sind_aggr_genre_avg_query_result_row['latin'].",`metal`=".$sql_sind_aggr_genre_avg_query_result_row['metal'].",`pop`=".$sql_sind_aggr_genre_avg_query_result_row['pop'].",`punk`=".$sql_sind_aggr_genre_avg_query_result_row['punk'].",`rapHipHop`=".$sql_sind_aggr_genre_avg_query_result_row['rapHipHop'].",`reggae`=".$sql_sind_aggr_genre_avg_query_result_row['reggae'].",`rnb`=".$sql_sind_aggr_genre_avg_query_result_row['rnb'].",`rock`=".$sql_sind_aggr_genre_avg_query_result_row['rock'].",`singerSongwriter`=".$sql_sind_aggr_genre_avg_query_result_row['singerSongwriter']." WHERE `sind_id`=".$sub_ind_id." and `sind_year`=".$cv_year;
																	}
																	else
																	{
																		$sind_s11 = "insert into tbl_sub_industry_genre_aggr_graph_data (sind_id,sind_year,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$sub_ind_id.",".$cv_year.",".$sql_sind_aggr_genre_avg_query_result_row['ambient'].",".$sql_sind_aggr_genre_avg_query_result_row['blues'].",".$sql_sind_aggr_genre_avg_query_result_row['classical'].",".$sql_sind_aggr_genre_avg_query_result_row['country'].",".$sql_sind_aggr_genre_avg_query_result_row['electronicDance'].",".$sql_sind_aggr_genre_avg_query_result_row['folk'].",".$sql_sind_aggr_genre_avg_query_result_row['indieAlternative'].",".$sql_sind_aggr_genre_avg_query_result_row['jazz'].",".$sql_sind_aggr_genre_avg_query_result_row['latin'].",".$sql_sind_aggr_genre_avg_query_result_row['metal'].",".$sql_sind_aggr_genre_avg_query_result_row['pop'].",".$sql_sind_aggr_genre_avg_query_result_row['punk'].",".$sql_sind_aggr_genre_avg_query_result_row['rapHipHop'].",".$sql_sind_aggr_genre_avg_query_result_row['reggae'].",".$sql_sind_aggr_genre_avg_query_result_row['rnb'].",".$sql_sind_aggr_genre_avg_query_result_row['rock'].",".$sql_sind_aggr_genre_avg_query_result_row['singerSongwriter'].")";
																	}							

																	error_log("tbl_sub_industry_genre_aggr_graph_data:".$sind_s11);
																	//echo "<br>";
																	$conn->query($sind_s11);
																}
															}
														}
														$conn->query("DELETE FROM tbl_sub_industry_mood_aggr_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
														$conn->query("DELETE FROM tbl_sub_industry_genre_aggr_graph_temp_data WHERE sind_id=".$sub_ind_id." and sind_year=".$cv_year);
														error_log("Message : Both Graphs Average Data inserted");
														error_log("====================================================================================================================================================");
													}
												}
												else
												{
													error_log("There are some cvs of sub industry - ".$sub_ind_id." ".$cv_year." pending in priority process tbl for download");
												}
											}
											else
											{
												error_log("There are some cvs of sub industry - ".$sub_ind_id." ".$cv_year." pending for download content");
											}
										}
										
									}
									// Sub Industry Graph generation end
									// Industry and Sub Industry Graph generation of current cv end
								}
							}
						}
						
					}					
				}
				
				error_log("====================================================================================================================================================");
			}			
		}
		catch(Exception $e)
		{
			error_log("page : [db_dump] : function [aggr_of_aggr] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("db_dump","aggr_of_aggr	",$e->getMessage());
		}
	}

	function ins_updt_monthly_graph_data($cv_id_array)
	{
		$dbcon = include('../config.php');
		$conn = new mysqli($dbcon['servername'], $dbcon['username'], $dbcon['password'], $dbcon['dbname']);

		foreach($cv_id_array as $cv_id)
		{
			error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] : Process to get monthwise data for CV: ".$cv_id." is Started");
			error_log("--------------------------------------------------------------------------------------------------------------------------");
			$cdate = date("Y-m-d h:i:s");
			$get_process_types_qry = "SELECT DISTINCT (process_type) as process_type FROM `tbl_social_spyder_graph_meta_data` WHERE cv_id=".$cv_id." and is_active=0";
    		$get_process_types_qry_result = $conn->query($get_process_types_qry);
    		if ($get_process_types_qry_result->num_rows > 0) 
    		{
    			while($get_process_types_qry_result_row = $get_process_types_qry_result->fetch_assoc())
        		{
        			//echo $current_process_type."<br>";
        			$current_process_type = $get_process_types_qry_result_row['process_type'];
        			$get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$cv_id." and `process_type` = '".$current_process_type."' and is_active = 0 and `uploaded_start_id` > 0 and `uploaded_end_id` > 0";
					$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
					if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 0)
					{
						$get_months_qry = "SELECT month as month FROM (";
						while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
						{
							$get_months_qry .= "SELECT DISTINCT(substring_index(substring_index(substring_index(video_published_at,'T',1),'-',2),'-',-1)) as month FROM `tbl_social_spyder_graph_meta_data` WHERE process_type='".$current_process_type."' and cv_id=".$cv_id." and is_active=0 and status >2 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
							$get_months_qry .= " UNION ";
						}
					}
        			$final_get_months_qry = rtrim($get_months_qry," UNION ")." ) gmq ORDER BY month ASC";		            
		            
		            $get_months_qry_result = $conn->query($final_get_months_qry);

		            if ($get_months_qry_result->num_rows > 0) 
		            {
		            	while($get_months_qry_result_row = $get_months_qry_result->fetch_assoc())
		                {
							$conn->query("DELETE FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id);
		            		$conn->query("DELETE FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id);
		                	if($current_process_type != 'twitter')
		                    {
		                    	$month = $get_months_qry_result_row['month'];
		                        //echo $cv_id."----".$process_type."----".$month."<br>";
		                        $get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$cv_id." and `process_type` = '".$current_process_type."' and is_active = 0 and `uploaded_start_id` > 0 and `uploaded_end_id` > 0";
								$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
								if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 0)
								{
									$get_month_data_qry = "SELECT cv_id,track_id FROM (";
									while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
									{
		                        		$get_month_data_qry .= "SELECT cv_id,track_id FROM `tbl_social_spyder_graph_meta_data` WHERE process_type='".$current_process_type."' and cv_id=".$cv_id." and `video_published_at` like '%-".$month."-%' and is_active=0 and status >2 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
		                        		$get_month_data_qry .= " UNION ";
									}
								}
			        			$final_get_month_data_qry = rtrim($get_month_data_qry," UNION ")." ) gmdq";	
		                        $get_month_data_qry_result = $conn->query($final_get_month_data_qry);
		                        if ($get_month_data_qry_result->num_rows > 0) 
                        		{
                        			$track_id_arr = array();
		                            while($get_month_data_qry_result_row = $get_month_data_qry_result->fetch_assoc())
		                            {
		                                array_push($track_id_arr,$get_month_data_qry_result_row['track_id']);
		                            }
		                            //print_r($track_id_arr);
		                            if(count($track_id_arr) !=0)
		                            {
		                                $track_id_arr_str = implode(",",$track_id_arr);
		                                $get_months_cyanite_data_qry = "SELECT 
		                                    cyanite.LTrack_id,
		                                    cyanite.brand_id,
		                                    json_extract(cyanite.mood, '$.aggressive') AS aggressive,
		                                    json_extract(cyanite.mood, '$.calm') AS calm,
		                                    json_extract(cyanite.mood, '$.chilled') AS chilled,
		                                    json_extract(cyanite.mood, '$.dark') AS dark,
		                                    json_extract(cyanite.mood, '$.energetic') AS energetic,
		                                    json_extract(cyanite.mood, '$.epic') AS epic,
		                                    json_extract(cyanite.mood, '$.happy') AS happy,
		                                    json_extract(cyanite.mood, '$.romantic') AS romantic,
		                                    json_extract(cyanite.mood, '$.sad') AS sad,
		                                    json_extract(cyanite.mood, '$.scary') AS scary,
		                                    json_extract(cyanite.mood, '$.sexy') AS sexy,
		                                    json_extract(cyanite.mood, '$.ethereal') AS ethereal,
		                                    json_extract(cyanite.mood, '$.uplifting') AS uplifting,
		                                    json_extract(cyanite.genre, '$.ambient') AS ambient,
		                                    json_extract(cyanite.genre, '$.blues') AS blues,
		                                    json_extract(cyanite.genre, '$.classical') AS classical,
		                                    json_extract(cyanite.genre, '$.country') AS country,
		                                    json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
		                                    json_extract(cyanite.genre, '$.folk') AS folk,
		                                    json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
		                                    json_extract(cyanite.genre, '$.jazz') AS jazz,
		                                    json_extract(cyanite.genre, '$.latin') AS latin,
		                                    json_extract(cyanite.genre, '$.metal') AS metal,
		                                    json_extract(cyanite.genre, '$.pop') AS pop,
		                                    json_extract(cyanite.genre, '$.punk') AS punk,
		                                    json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
		                                    json_extract(cyanite.genre, '$.reggae') AS reggae,
		                                    json_extract(cyanite.genre, '$.rnb') AS rnb,
		                                    json_extract(cyanite.genre, '$.rock') AS rock,
		                                    json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter  
		                                    FROM cyanite where cyanite.brand_id=".$cv_id." and cyanite.LTrack_id IN (".$track_id_arr_str.")  and cyanite.process_type='".$current_process_type."' and cyanite.is_active=0";
		                                $get_months_cyanite_data_qry_result = $conn->query($get_months_cyanite_data_qry);
		                                if ($get_months_cyanite_data_qry_result->num_rows > 0) 
		                                {
		                                    if($current_process_type == 'youtube')
		                                    {
		                                        $process_type = 1;
		                                    }
		                                    elseif($current_process_type == 'instagram')
		                                    {
		                                        $process_type = 2;
		                                    }
		                                    else
		                                    {
		                                        $process_type = 3;
		                                    }

		                                    $ins_month_mood_temp_query = "insert into tbl_month_mood_graph_temp_data (cv_id,track_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";

		                                    $ins_month_genre_temp_query = "insert into tbl_month_genre_graph_temp_data (cv_id,track_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
		                                    while($get_months_cyanite_data_qry_result_row = $get_months_cyanite_data_qry_result->fetch_assoc())
		                                    {
		                                        $ins_month_mood_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['aggressive'].",".$get_months_cyanite_data_qry_result_row['calm'].",".$get_months_cyanite_data_qry_result_row['chilled'].",".$get_months_cyanite_data_qry_result_row['dark'].",".$get_months_cyanite_data_qry_result_row['energetic'].",".$get_months_cyanite_data_qry_result_row['epic'].",".$get_months_cyanite_data_qry_result_row['happy'].",".$get_months_cyanite_data_qry_result_row['romantic'].",".$get_months_cyanite_data_qry_result_row['sad'].",".$get_months_cyanite_data_qry_result_row['scary'].",".$get_months_cyanite_data_qry_result_row['sexy'].",".$get_months_cyanite_data_qry_result_row['ethereal'].",".$get_months_cyanite_data_qry_result_row['uplifting']."),";

		                                        $ins_month_genre_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['ambient'].",".$get_months_cyanite_data_qry_result_row['blues'].",".$get_months_cyanite_data_qry_result_row['classical'].",".$get_months_cyanite_data_qry_result_row['country'].",".$get_months_cyanite_data_qry_result_row['electronicDance'].",".$get_months_cyanite_data_qry_result_row['folk'].",".$get_months_cyanite_data_qry_result_row['indieAlternative'].",".$get_months_cyanite_data_qry_result_row['jazz'].",".$get_months_cyanite_data_qry_result_row['latin'].",".$get_months_cyanite_data_qry_result_row['metal'].",".$get_months_cyanite_data_qry_result_row['pop'].",".$get_months_cyanite_data_qry_result_row['punk'].",".$get_months_cyanite_data_qry_result_row['rapHipHop'].",".$get_months_cyanite_data_qry_result_row['reggae'].",".$get_months_cyanite_data_qry_result_row['rnb'].",".$get_months_cyanite_data_qry_result_row['rock'].",".$get_months_cyanite_data_qry_result_row['singerSongwriter']."),";
		                                    }
		                                    $multi_ins_month_mood_temp_query = rtrim($ins_month_mood_temp_query,",");
		                                    //echo $multi_ins_month_mood_temp_query."<br><br>";

		                                    $multi_ins_month_genre_temp_query = rtrim($ins_month_genre_temp_query,",");
		                                    //echo $multi_ins_month_genre_temp_query."<br><br>";		                                    

		                                    if($conn->query($multi_ins_month_mood_temp_query) && $conn->query($multi_ins_month_genre_temp_query))
		                                    {
		                                        $month_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
		                                        //echo $month_mood_avg_query."<br><br>";
		                                        $month_mood_avg_query_result = $conn->query($month_mood_avg_query);
		                                        
		                                        if ($month_mood_avg_query_result->num_rows > 0)
		                                        {
		                                            $chk_month_mood_graph_data_query = "select * from tbl_month_mood_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
		                                            $chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
		                                            $q1_type = "";
		                                            
		                                            if($chk_month_mood_graph_data_query_res->num_rows > 0)
		                                            {
		                                            	$q1_type = 'updated';
		                                                while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
		                                                {
		                                                    $ms1 = "UPDATE `tbl_month_mood_graph_data` SET `aggressive`='".$month_mood_avg_query_result_row['aggressive']."',`calm`='".$month_mood_avg_query_result_row['calm']."',`chilled`='".$month_mood_avg_query_result_row['chilled']."',`dark`='".$month_mood_avg_query_result_row['dark']."',`energetic`='".$month_mood_avg_query_result_row['energetic']."',`epic`='".$month_mood_avg_query_result_row['epic']."',`happy`='".$month_mood_avg_query_result_row['happy']."',`romantic`='".$month_mood_avg_query_result_row['romantic']."',`sad`='".$month_mood_avg_query_result_row['sad']."',`scary`='".$month_mood_avg_query_result_row['scary']."',`sexy`='".$month_mood_avg_query_result_row['sexy']."',`ethereal`='".$month_mood_avg_query_result_row['ethereal']."',`uplifting`='".$month_mood_avg_query_result_row['uplifting']."', `updated_at`='".$cdate."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;		                                                    
		                                                }
		                                                
		                                            }
		                                            else
		                                            {
		                                            	$q1_type = 'inserted';
		                                                while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
		                                                {
		                                                    $ms1 = "insert into tbl_month_mood_graph_data(cv_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$process_type.",".$month.",".$month_mood_avg_query_result_row['aggressive'].",".$month_mood_avg_query_result_row['calm'].",".$month_mood_avg_query_result_row['chilled'].",".$month_mood_avg_query_result_row['dark'].",".$month_mood_avg_query_result_row['energetic'].",".$month_mood_avg_query_result_row['epic'].",".$month_mood_avg_query_result_row['happy'].",".$month_mood_avg_query_result_row['romantic'].",".$month_mood_avg_query_result_row['sad'].",".$month_mood_avg_query_result_row['scary'].",".$month_mood_avg_query_result_row['sexy'].",".$month_mood_avg_query_result_row['ethereal'].",".$month_mood_avg_query_result_row['uplifting'].")";
		                                                }
		                                            }
		                                            
		                                            if($conn->query($ms1))
		                                            {
		                                                //echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data<br>";
		                                                error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] : Aggr data ".$q1_type." for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data");
		                                            }
		                                        }

		                                        $month_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
		                                        //echo $month_genre_avg_query."<br><br>";
		                                        $month_genre_avg_query_result = $conn->query($month_genre_avg_query);
		                                        
		                                        if ($month_genre_avg_query_result->num_rows > 0)
		                                        {
		                                            $chk_month_genre_graph_data_query = "select * from tbl_month_genre_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
		                                            $chk_month_genre_graph_data_query_res = $conn->query($chk_month_genre_graph_data_query);
		                                            $q2_type = "";
		                                            if($chk_month_genre_graph_data_query_res->num_rows > 0)
		                                            {
		                                            	$q2_type = 'updated';
		                                                while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
		                                                {
		                                                    //$conn->query("DELETE FROM tbl_month_genre_graph_data WHERE cv_id=".$cv_id);
		                                                    $ms2 = "UPDATE `tbl_month_genre_graph_data` SET `ambient`='".$month_genre_avg_query_result_row['ambient']."',`blues`='".$month_genre_avg_query_result_row['blues']."',`classical`='".$month_genre_avg_query_result_row['classical']."',`country`='".$month_genre_avg_query_result_row['country']."',`electronicDance`='".$month_genre_avg_query_result_row['electronicDance']."',`folk`='".$month_genre_avg_query_result_row['folk']."',`indieAlternative`='".$month_genre_avg_query_result_row['indieAlternative']."',`jazz`='".$month_genre_avg_query_result_row['jazz']."',`latin`='".$month_genre_avg_query_result_row['latin']."',`metal`='".$month_genre_avg_query_result_row['metal']."',`pop`='".$month_genre_avg_query_result_row['pop']."',`punk`='".$month_genre_avg_query_result_row['punk']."',`rapHipHop`='".$month_genre_avg_query_result_row['rapHipHop']."',`reggae`='".$month_genre_avg_query_result_row['reggae']."',`rnb`='".$month_genre_avg_query_result_row['rnb']."',`rock`='".$month_genre_avg_query_result_row['rock']."',`singerSongwriter`='".$month_genre_avg_query_result_row['singerSongwriter']."', `updated_at`='".$cdate."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
		                                                }
		                                            }
		                                            else
		                                            {
		                                            	$q2_type = 'inserted';
		                                                while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
		                                                {
		                                                    $ms2 = "insert into tbl_month_genre_graph_data(cv_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$process_type.",".$month.",".$month_genre_avg_query_result_row['ambient'].",".$month_genre_avg_query_result_row['blues'].",".$month_genre_avg_query_result_row['classical'].",".$month_genre_avg_query_result_row['country'].",".$month_genre_avg_query_result_row['electronicDance'].",".$month_genre_avg_query_result_row['folk'].",".$month_genre_avg_query_result_row['indieAlternative'].",".$month_genre_avg_query_result_row['jazz'].",".$month_genre_avg_query_result_row['latin'].",".$month_genre_avg_query_result_row['metal'].",".$month_genre_avg_query_result_row['pop'].",".$month_genre_avg_query_result_row['punk'].",".$month_genre_avg_query_result_row['rapHipHop'].",".$month_genre_avg_query_result_row['reggae'].",".$month_genre_avg_query_result_row['rnb'].",".$month_genre_avg_query_result_row['rock'].",".$month_genre_avg_query_result_row['singerSongwriter'].")";
		                                                }
		                                            }

		                                            if($conn->query($ms2))
		                                            {
		                                                //echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data<br>";
		                                                error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data ".$q2_type." for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data");
		                                            }
		                                        }
		                                    }
		                                }
		                            }
                        		}
		                    }
		                    else
		                    {
		                    	$month = date_format(date_create($get_months_qry_result_row['month']),"m");
		                    	$get_chnl_start_n_end_cntnt_id_qry = "SELECT * FROM `tbl_social_spyder_graph_request_data` WHERE `cv_id` = ".$cv_id." and `process_type` = '".$current_process_type."' and is_active = 0 and `uploaded_start_id` > 0 and `uploaded_end_id` > 0";
								$get_chnl_start_n_end_cntnt_id_qry_res = $conn->query($get_chnl_start_n_end_cntnt_id_qry);
								if ($get_chnl_start_n_end_cntnt_id_qry_res->num_rows > 0)
								{
									$get_month_data_qry = "SELECT cv_id,track_id FROM (";
									while($get_chnl_start_n_end_cntnt_id_qry_res_row = $get_chnl_start_n_end_cntnt_id_qry_res->fetch_assoc())
									{
		                        		$get_month_data_qry .= "SELECT cv_id,track_id FROM `tbl_social_spyder_graph_meta_data` WHERE process_type='".$current_process_type."' and cv_id=".$cv_id." and `video_published_at` like '% ".date_format(date_create($get_months_qry_result_row['month']),"M")." %' and is_active=0 and status >2 and id between ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_start_id']." and ".$get_chnl_start_n_end_cntnt_id_qry_res_row['uploaded_end_id'];
		                        		$get_month_data_qry .= " UNION ";
									}
								}
			        			$final_get_month_data_qry = rtrim($get_month_data_qry," UNION ")." ) gmdq";	
		                        $get_month_data_qry_result = $conn->query($final_get_month_data_qry);

		                        if ($get_month_data_qry_result->num_rows > 0) 
		                        {
		                            $track_id_arr = array();
		                            while($get_month_data_qry_result_row = $get_month_data_qry_result->fetch_assoc())
		                            {
		                                array_push($track_id_arr,$get_month_data_qry_result_row['track_id']);
		                            }
		                            
		                            if(count($track_id_arr) !=0)
		                            {
		                                $track_id_arr_str = implode(",",$track_id_arr);
		                                $get_months_cyanite_data_qry = "SELECT 
		                                    cyanite.LTrack_id,
		                                    cyanite.brand_id,
		                                    json_extract(cyanite.mood, '$.aggressive') AS aggressive,
		                                    json_extract(cyanite.mood, '$.calm') AS calm,
		                                    json_extract(cyanite.mood, '$.chilled') AS chilled,
		                                    json_extract(cyanite.mood, '$.dark') AS dark,
		                                    json_extract(cyanite.mood, '$.energetic') AS energetic,
		                                    json_extract(cyanite.mood, '$.epic') AS epic,
		                                    json_extract(cyanite.mood, '$.happy') AS happy,
		                                    json_extract(cyanite.mood, '$.romantic') AS romantic,
		                                    json_extract(cyanite.mood, '$.sad') AS sad,
		                                    json_extract(cyanite.mood, '$.scary') AS scary,
		                                    json_extract(cyanite.mood, '$.sexy') AS sexy,
		                                    json_extract(cyanite.mood, '$.ethereal') AS ethereal,
		                                    json_extract(cyanite.mood, '$.uplifting') AS uplifting,
		                                    json_extract(cyanite.genre, '$.ambient') AS ambient,
		                                    json_extract(cyanite.genre, '$.blues') AS blues,
		                                    json_extract(cyanite.genre, '$.classical') AS classical,
		                                    json_extract(cyanite.genre, '$.country') AS country,
		                                    json_extract(cyanite.genre, '$.electronicDance') AS electronicDance,
		                                    json_extract(cyanite.genre, '$.folk') AS folk,
		                                    json_extract(cyanite.genre, '$.indieAlternative') AS indieAlternative,
		                                    json_extract(cyanite.genre, '$.jazz') AS jazz,
		                                    json_extract(cyanite.genre, '$.latin') AS latin,
		                                    json_extract(cyanite.genre, '$.metal') AS metal,
		                                    json_extract(cyanite.genre, '$.pop') AS pop,
		                                    json_extract(cyanite.genre, '$.punk') AS punk,
		                                    json_extract(cyanite.genre, '$.rapHipHop') AS rapHipHop,
		                                    json_extract(cyanite.genre, '$.reggae') AS reggae,
		                                    json_extract(cyanite.genre, '$.rnb') AS rnb,
		                                    json_extract(cyanite.genre, '$.rock') AS rock,
		                                    json_extract(cyanite.genre, '$.singerSongwriter') AS singerSongwriter  
		                                    FROM cyanite where cyanite.brand_id=".$cv_id." and cyanite.LTrack_id IN (".$track_id_arr_str.")  and cyanite.process_type='".$current_process_type."' and cyanite.is_active=0";
		                                $get_months_cyanite_data_qry_result = $conn->query($get_months_cyanite_data_qry);
		                                
		                                if ($get_months_cyanite_data_qry_result->num_rows > 0) 
		                                {
		                                    $process_type = 4;
		                                    $ins_month_mood_temp_query = "insert into tbl_month_mood_graph_temp_data (cv_id,track_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values ";

		                                    $ins_month_genre_temp_query = "insert into tbl_month_genre_graph_temp_data (cv_id,track_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values ";
		                                    while($get_months_cyanite_data_qry_result_row = $get_months_cyanite_data_qry_result->fetch_assoc())
		                                    {
		                                        $ins_month_mood_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['aggressive'].",".$get_months_cyanite_data_qry_result_row['calm'].",".$get_months_cyanite_data_qry_result_row['chilled'].",".$get_months_cyanite_data_qry_result_row['dark'].",".$get_months_cyanite_data_qry_result_row['energetic'].",".$get_months_cyanite_data_qry_result_row['epic'].",".$get_months_cyanite_data_qry_result_row['happy'].",".$get_months_cyanite_data_qry_result_row['romantic'].",".$get_months_cyanite_data_qry_result_row['sad'].",".$get_months_cyanite_data_qry_result_row['scary'].",".$get_months_cyanite_data_qry_result_row['sexy'].",".$get_months_cyanite_data_qry_result_row['ethereal'].",".$get_months_cyanite_data_qry_result_row['uplifting']."),";

		                                        $ins_month_genre_temp_query .= "(".$cv_id.",".$get_months_cyanite_data_qry_result_row['LTrack_id'].",".$process_type.",".$month.",".$get_months_cyanite_data_qry_result_row['ambient'].",".$get_months_cyanite_data_qry_result_row['blues'].",".$get_months_cyanite_data_qry_result_row['classical'].",".$get_months_cyanite_data_qry_result_row['country'].",".$get_months_cyanite_data_qry_result_row['electronicDance'].",".$get_months_cyanite_data_qry_result_row['folk'].",".$get_months_cyanite_data_qry_result_row['indieAlternative'].",".$get_months_cyanite_data_qry_result_row['jazz'].",".$get_months_cyanite_data_qry_result_row['latin'].",".$get_months_cyanite_data_qry_result_row['metal'].",".$get_months_cyanite_data_qry_result_row['pop'].",".$get_months_cyanite_data_qry_result_row['punk'].",".$get_months_cyanite_data_qry_result_row['rapHipHop'].",".$get_months_cyanite_data_qry_result_row['reggae'].",".$get_months_cyanite_data_qry_result_row['rnb'].",".$get_months_cyanite_data_qry_result_row['rock'].",".$get_months_cyanite_data_qry_result_row['singerSongwriter']."),";
		                                    }
		                                    $multi_ins_month_mood_temp_query = rtrim($ins_month_mood_temp_query,",");
		                                    //echo $multi_ins_month_mood_temp_query."<br><br>";

		                                    $multi_ins_month_genre_temp_query = rtrim($ins_month_genre_temp_query,",");
		                                    //echo $multi_ins_month_genre_temp_query."<br><br>";

		                                    if($conn->query($multi_ins_month_mood_temp_query) && $conn->query($multi_ins_month_genre_temp_query))
		                                    {
		                                        $month_mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
		                                        //echo $month_mood_avg_query."<br><br>";
		                                        $month_mood_avg_query_result = $conn->query($month_mood_avg_query);

		                                        if ($month_mood_avg_query_result->num_rows > 0)
		                                        {
		                                            $chk_month_mood_graph_data_query = "select * from tbl_month_mood_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
		                                            $chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
		                                            $q1_type ='';
		                                            if($chk_month_mood_graph_data_query_res->num_rows > 0)
		                                            {
		                                            	$q1_type = 'updated';
		                                                while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
		                                                {
		                                                    $ms1 = "UPDATE `tbl_month_mood_graph_data` SET `aggressive`='".$month_mood_avg_query_result_row['aggressive']."',`calm`='".$month_mood_avg_query_result_row['calm']."',`chilled`='".$month_mood_avg_query_result_row['chilled']."',`dark`='".$month_mood_avg_query_result_row['dark']."',`energetic`='".$month_mood_avg_query_result_row['energetic']."',`epic`='".$month_mood_avg_query_result_row['epic']."',`happy`='".$month_mood_avg_query_result_row['happy']."',`romantic`='".$month_mood_avg_query_result_row['romantic']."',`sad`='".$month_mood_avg_query_result_row['sad']."',`scary`='".$month_mood_avg_query_result_row['scary']."',`sexy`='".$month_mood_avg_query_result_row['sexy']."',`ethereal`='".$month_mood_avg_query_result_row['ethereal']."',`uplifting`='".$month_mood_avg_query_result_row['uplifting']."', `updated_at`='".$cdate."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
		                                                }
		                                            }
		                                            else
		                                            {
		                                            	$q1_type = 'inserted';
		                                                while($month_mood_avg_query_result_row = $month_mood_avg_query_result->fetch_assoc())
		                                                {
		                                                    $ms1 = "insert into tbl_month_mood_graph_data(cv_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$process_type.",".$month.",".$month_mood_avg_query_result_row['aggressive'].",".$month_mood_avg_query_result_row['calm'].",".$month_mood_avg_query_result_row['chilled'].",".$month_mood_avg_query_result_row['dark'].",".$month_mood_avg_query_result_row['energetic'].",".$month_mood_avg_query_result_row['epic'].",".$month_mood_avg_query_result_row['happy'].",".$month_mood_avg_query_result_row['romantic'].",".$month_mood_avg_query_result_row['sad'].",".$month_mood_avg_query_result_row['scary'].",".$month_mood_avg_query_result_row['sexy'].",".$month_mood_avg_query_result_row['ethereal'].",".$month_mood_avg_query_result_row['uplifting'].")";
		                                                }
		                                            }

		                                            if($conn->query($ms1))
		                                            {
		                                                //echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data<br>";
		                                                error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data ".$q1_type." for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_mood_graph_data");
		                                            }
		                                        }

		                                        $month_genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and process_type='".$process_type."' and month=".$month;
		                                        //echo $month_genre_avg_query."<br><br>";
		                                        $month_genre_avg_query_result = $conn->query($month_genre_avg_query);

		                                        if ($month_genre_avg_query_result->num_rows > 0)
		                                        {
		                                            $chk_month_genre_graph_data_query = "select * from tbl_month_genre_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
		                                            $chk_month_genre_graph_data_query_res = $conn->query($chk_month_genre_graph_data_query);
		                                            $q2_type = '';
		                                            if($chk_month_genre_graph_data_query_res->num_rows > 0)
		                                            {
		                                            	$q2_type = 'updated';
		                                                while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
		                                                {
		                                                    //$conn->query("DELETE FROM tbl_month_genre_graph_data WHERE cv_id=".$cv_id);
		                                                    $ms2 = "UPDATE `tbl_month_genre_graph_data` SET `ambient`='".$month_genre_avg_query_result_row['ambient']."',`blues`='".$month_genre_avg_query_result_row['blues']."',`classical`='".$month_genre_avg_query_result_row['classical']."',`country`='".$month_genre_avg_query_result_row['country']."',`electronicDance`='".$month_genre_avg_query_result_row['electronicDance']."',`folk`='".$month_genre_avg_query_result_row['folk']."',`indieAlternative`='".$month_genre_avg_query_result_row['indieAlternative']."',`jazz`='".$month_genre_avg_query_result_row['jazz']."',`latin`='".$month_genre_avg_query_result_row['latin']."',`metal`='".$month_genre_avg_query_result_row['metal']."',`pop`='".$month_genre_avg_query_result_row['pop']."',`punk`='".$month_genre_avg_query_result_row['punk']."',`rapHipHop`='".$month_genre_avg_query_result_row['rapHipHop']."',`reggae`='".$month_genre_avg_query_result_row['reggae']."',`rnb`='".$month_genre_avg_query_result_row['rnb']."',`rock`='".$month_genre_avg_query_result_row['rock']."',`singerSongwriter`='".$month_genre_avg_query_result_row['singerSongwriter']."', `updated_at`='".$cdate."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$month;
		                                                }
		                                            }
		                                            else
		                                            {
		                                            	$q2_type = 'inserted';
		                                                while($month_genre_avg_query_result_row = $month_genre_avg_query_result->fetch_assoc())
		                                                {
		                                                    $ms2 = "insert into tbl_month_genre_graph_data(cv_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$process_type.",".$month.",".$month_genre_avg_query_result_row['ambient'].",".$month_genre_avg_query_result_row['blues'].",".$month_genre_avg_query_result_row['classical'].",".$month_genre_avg_query_result_row['country'].",".$month_genre_avg_query_result_row['electronicDance'].",".$month_genre_avg_query_result_row['folk'].",".$month_genre_avg_query_result_row['indieAlternative'].",".$month_genre_avg_query_result_row['jazz'].",".$month_genre_avg_query_result_row['latin'].",".$month_genre_avg_query_result_row['metal'].",".$month_genre_avg_query_result_row['pop'].",".$month_genre_avg_query_result_row['punk'].",".$month_genre_avg_query_result_row['rapHipHop'].",".$month_genre_avg_query_result_row['reggae'].",".$month_genre_avg_query_result_row['rnb'].",".$month_genre_avg_query_result_row['rock'].",".$month_genre_avg_query_result_row['singerSongwriter'].")";
		                                                }
		                                            }

		                                            if($conn->query($ms2))
		                                            {
		                                                //echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data<br>";
		                                                error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data ".$q2_type." for cv".$cv_id." and process_type=".$process_type." and month:".$month." into tbl_month_genre_graph_data");
		                                            }
		                                        }
		                                    }
		                                }
		                            }
		                        }
		                    }
		                }
		            }
		            else
		            {
		            	error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] : no month found for process_type".$current_process_type." of CV ".$cv_id);
		            }

        		}

        		if($get_process_types_qry_result->num_rows > 1)
		        {
		            $process_type = 5;
		            $get_mnt_qry = "SELECT DISTINCT(`month`) as month FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id`=".$cv_id;
		            $get_mnt_qry_result = $conn->query($get_mnt_qry);
		            if($get_mnt_qry_result->num_rows>0)
		            {
		                while ($get_mnt_qry_result_row = $get_mnt_qry_result->fetch_assoc()) {
		                    $mood_avg_query = "SELECT round(AVG(`aggressive`),2) as aggressive, round(AVG(`calm`),2) as calm, round(AVG(`chilled`),2) as chilled, round(AVG(`dark`),2) as dark, round(AVG(`energetic`),2) as energetic, round(AVG(`epic`),2) as epic, round(AVG(`happy`),2) as happy, round(AVG(`romantic`),2) as romantic, round(AVG(`sad`),2) as sad, round(AVG(`scary`),2) as scary, round(AVG(`sexy`),2) as sexy, round(AVG(`ethereal`),2) as ethereal, round(AVG(`uplifting`),2) as uplifting FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month'];
		                    //echo $mood_avg_query."<br><br>";
		                    $mood_avg_query_result = $conn->query($mood_avg_query);
		                    if ($mood_avg_query_result->num_rows > 0)
		                    {
		                        $chk_month_mood_graph_data_query = "select * from tbl_month_mood_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
		                        $chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
		                        $q1_type='';
		                        if($chk_month_mood_graph_data_query_res->num_rows > 0)
		                        {
		                        	$q1_type='updated';
		                            while($mood_avg_query_result_row = $mood_avg_query_result->fetch_assoc())
		                            {
		                                $ms3 = "UPDATE `tbl_month_mood_graph_data` SET `aggressive`='".$mood_avg_query_result_row['aggressive']."',`calm`='".$mood_avg_query_result_row['calm']."',`chilled`='".$mood_avg_query_result_row['chilled']."',`dark`='".$mood_avg_query_result_row['dark']."',`energetic`='".$mood_avg_query_result_row['energetic']."',`epic`='".$mood_avg_query_result_row['epic']."',`happy`='".$mood_avg_query_result_row['happy']."',`romantic`='".$mood_avg_query_result_row['romantic']."',`sad`='".$mood_avg_query_result_row['sad']."',`scary`='".$mood_avg_query_result_row['scary']."',`sexy`='".$mood_avg_query_result_row['sexy']."',`ethereal`='".$mood_avg_query_result_row['ethereal']."',`uplifting`='".$mood_avg_query_result_row['uplifting']."', `updated_at`='".$cdate."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
		                            }
		                        }
		                        else
		                        {
		                        	$q1_type='inserted';
		                            while($mood_avg_query_result_row = $mood_avg_query_result->fetch_assoc())
		                            {
		                                $ms3 = "insert into tbl_month_mood_graph_data(cv_id,process_type,month,aggressive,calm,chilled,dark,energetic,epic,happy,romantic,sad,scary,sexy,ethereal,uplifting) values(".$cv_id.",".$process_type.",".$get_mnt_qry_result_row['month'].",".$mood_avg_query_result_row['aggressive'].",".$mood_avg_query_result_row['calm'].",".$mood_avg_query_result_row['chilled'].",".$mood_avg_query_result_row['dark'].",".$mood_avg_query_result_row['energetic'].",".$mood_avg_query_result_row['epic'].",".$mood_avg_query_result_row['happy'].",".$mood_avg_query_result_row['romantic'].",".$mood_avg_query_result_row['sad'].",".$mood_avg_query_result_row['scary'].",".$mood_avg_query_result_row['sexy'].",".$mood_avg_query_result_row['ethereal'].",".$mood_avg_query_result_row['uplifting'].")";
		                            }
		                        }

		                        if($conn->query($ms3))
		                        {
		                            //echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']."<br>";
		                            $conn->query("DELETE FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month']);
		                            error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data ".$q1_type." for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']);
		                        }
		                    }

		                    $genre_avg_query = "SELECT round(AVG(`ambient`),2) as ambient, round(AVG(`blues`),2) as blues, round(AVG(`classical`),2) as classical, round(AVG(`country`),2) as country, round(AVG(`electronicDance`),2) as electronicDance, round(AVG(`folk`),2) as folk, round(AVG(`indieAlternative`),2) as indieAlternative, round(AVG(`jazz`),2) as jazz, round(AVG(`latin`),2) as latin, round(AVG(`metal`),2) as metal, round(AVG(`pop`),2) as pop, round(AVG(`punk`),2) as punk, round(AVG(`rapHipHop`),2) as rapHipHop, round(AVG(`reggae`),2) as reggae, round(AVG(`rnb`),2) as rnb, round(AVG(`rock`),2) as rock, round(AVG(`singerSongwriter`),2) as singerSongwriter FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month'];
		                    //echo $genre_avg_query."<br><br>";
		                    $genre_avg_query_result = $conn->query($genre_avg_query);
		                    
		                    if ($genre_avg_query_result->num_rows > 0)
		                    {
		                        $chk_month_mood_graph_data_query = "select * from tbl_month_genre_graph_data where cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
		                        $chk_month_mood_graph_data_query_res = $conn->query($chk_month_mood_graph_data_query);
		                        $q2_type='';
		                        if($chk_month_mood_graph_data_query_res->num_rows > 0)
		                        {
		                        	$q2_type='updated';
		                            while($genre_avg_query_result_row = $genre_avg_query_result->fetch_assoc())
		                            {
		                                $ms4 = "UPDATE `tbl_month_genre_graph_data` SET `ambient`='".$genre_avg_query_result_row['ambient']."',`blues`='".$genre_avg_query_result_row['blues']."',`classical`='".$genre_avg_query_result_row['classical']."',`country`='".$genre_avg_query_result_row['country']."',`electronicDance`='".$genre_avg_query_result_row['electronicDance']."',`folk`='".$genre_avg_query_result_row['folk']."',`indieAlternative`='".$genre_avg_query_result_row['indieAlternative']."',`jazz`='".$genre_avg_query_result_row['jazz']."',`latin`='".$genre_avg_query_result_row['latin']."',`metal`='".$genre_avg_query_result_row['metal']."',`pop`='".$genre_avg_query_result_row['pop']."',`punk`='".$genre_avg_query_result_row['punk']."',`rapHipHop`='".$genre_avg_query_result_row['rapHipHop']."',`reggae`='".$genre_avg_query_result_row['reggae']."',`rnb`='".$genre_avg_query_result_row['rnb']."',`rock`='".$genre_avg_query_result_row['rock']."',`singerSongwriter`='".$genre_avg_query_result_row['singerSongwriter']."', `updated_at`='".$cdate."' WHERE cv_id =".$cv_id." and process_type=".$process_type." and month=".$get_mnt_qry_result_row['month'];
		                            }
		                        }
		                        else
		                        {
		                        	$q2_type='inserted';
		                            while($genre_avg_query_result_row = $genre_avg_query_result->fetch_assoc())
		                            {
		                                $ms4 = "insert into tbl_month_genre_graph_data(cv_id,process_type,month,ambient,blues,classical,country,electronicDance,folk,indieAlternative,jazz,latin,metal,pop,punk,rapHipHop,reggae,rnb,rock,singerSongwriter) values(".$cv_id.",".$process_type.",".$get_mnt_qry_result_row['month'].",".$genre_avg_query_result_row['ambient'].",".$genre_avg_query_result_row['blues'].",".$genre_avg_query_result_row['classical'].",".$genre_avg_query_result_row['country'].",".$genre_avg_query_result_row['electronicDance'].",".$genre_avg_query_result_row['folk'].",".$genre_avg_query_result_row['indieAlternative'].",".$genre_avg_query_result_row['jazz'].",".$genre_avg_query_result_row['latin'].",".$genre_avg_query_result_row['metal'].",".$genre_avg_query_result_row['pop'].",".$genre_avg_query_result_row['punk'].",".$genre_avg_query_result_row['rapHipHop'].",".$genre_avg_query_result_row['reggae'].",".$genre_avg_query_result_row['rnb'].",".$genre_avg_query_result_row['rock'].",".$genre_avg_query_result_row['singerSongwriter'].")";		                                
		                            }
		                        }

		                        if($ms4 != '')
		                        {
		                            if($conn->query($ms4))
		                            {
		                                //echo "data inserted for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']."<br>";
		                                $conn->query("DELETE FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id." and month=".$get_mnt_qry_result_row['month']);
		                                error_log("[db_dump] : function [ins_updt_monthly_graph_data] : Aggr data ".$q2_type." for cv".$cv_id." and process_type=".$process_type." and month:".$get_mnt_qry_result_row['month']);
		                            }
		                        }
		                    }
		                }
		            }
		        }
		        else
		        {
		            $conn->query("DELETE FROM `tbl_month_mood_graph_temp_data` WHERE `cv_id` =".$cv_id);
		            $conn->query("DELETE FROM `tbl_month_genre_graph_temp_data` WHERE `cv_id` =".$cv_id);
		        }
    		}
    		else
		    {
		        //echo "no process_type found for cv ".$value;
		        error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] : No process_type found for CV ".$cv_id);
		    }
		    error_log("page : [db_dump] : function [ins_updt_monthly_graph_data] : Process to get monthwise data for CV: ".$cv_id." is Ended");
			error_log("--------------------------------------------------------------------------------------------------------------------------");	    		
		}
	}
}

?>
