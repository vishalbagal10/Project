<?php

require_once('sonic/sonic_functions.php');

//$auth_token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiSW50ZWdyYXRpb25BY2Nlc3NUb2tlbiIsInZlcnNpb24iOiIxLjAiLCJpbnRlZ3JhdGlvbklkIjoxNDYsInVzZXJJZCI6MzkxOCwiYWNjZXNzVG9rZW5TZWNyZXQiOiI0ZDYxNjExZWE4N2IwNzhlOGY4NTY2NzAwYTlhNjZhOThlNjA3MjhiNjBlZTNjNTFmOTFmZDExNTJlMjM2MTIwIiwiaWF0IjoxNjI2NDI3ODUwfQ.47jfn5qJhc7XK73dHdw7_pgLNEdkAVKI-o6mzAPauRQ"; //client test account

//$auth_token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiSW50ZWdyYXRpb25BY2Nlc3NUb2tlbiIsInZlcnNpb24iOiIxLjAiLCJpbnRlZ3JhdGlvbklkIjoxMzksInVzZXJJZCI6MzE0NywiYWNjZXNzVG9rZW5TZWNyZXQiOiIyNzhmZTk5NmU2Y2YyNDkxMjg1Mzk2OTljMWU0MWUxY2Y0OTkxYjMzY2NlN2QzMzQ5YzRmZmM5NWE3MDZkNmU2IiwiaWF0IjoxNjI0ODYwMTc4fQ.E_7_p6pGsN3_rIWJBIrk3HdcFzwnNajMBtT28EAetMM"; // wits test account

$auth_token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiSW50ZWdyYXRpb25BY2Nlc3NUb2tlbiIsInZlcnNpb24iOiIxLjAiLCJpbnRlZ3JhdGlvbklkIjoxNTUsInVzZXJJZCI6MzkxNywiYWNjZXNzVG9rZW5TZWNyZXQiOiI3NWRlMDY4NjFmZjU0ZjM2YTM2M2U2ZDdjY2MyMDkyYTZkOTI1NmE4ZDE1Y2NhZjZhMTM5OGQ4N2M5OTZlN2E1IiwiaWF0IjoxNjI2Nzc3MzExfQ.rEJthSRYR2A8enFupsexOYGj0h8cXGuKjGF8YALhOm4"; // client live account

//set_time_limit ( 12500 );
class CyaniteAI{
	
	
	function RequestUpload(){
		
		error_log("RequestUpload called.");
		
		$sonic_functions = new sonic_functions();

		try{
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.cyanite.ai/graphql',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_SSL_VERIFYPEER => false,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{"query":"\\n  mutation fileUploadRequest {\\n    fileUploadRequest {\\n      id\\n      uploadUrl\\n    }\\n  }\\n"}',
			  CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.$GLOBALS['auth_token'],
				'Content-Type: application/json'
			  ),
			));
			
			$response = curl_exec($curl);

			if (curl_errno($curl)) {
			    $error_msg = curl_error($curl.$response);
			    error_log("page : [cyanite_apis] : function [RequestUpload] : error : ".$error_msg);
			    $response="0"; // error
			}

			curl_close($curl);
			
			return $response;
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_apis] : function [RequestUpload] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_apis","RequestUpload",$e->getMessage());
		}
	}

	function FileUpload($upload_url, $file_url){
		
		error_log("FileUpload called.");

		$sonic_functions = new sonic_functions();

		try{
			$localFile = $file_url;


			$fp = fopen($localFile, 'r');

			// Connecting to website.
			$ch = curl_init();

			//curl_setopt($ch, CURLOPT_USERPWD, "email@email.org:password");
			curl_setopt($ch, CURLOPT_URL, $upload_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_UPLOAD, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 86400); // 1 Day Timeout
			curl_setopt($ch, CURLOPT_INFILE, $fp);
			curl_setopt($ch, CURLOPT_NOPROGRESS, false);
			curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'CURL_callback');
			curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
			curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFile));
			curl_exec ($ch);

			//echo "file upload error : ".curl_errno($ch); exit;

			if (curl_errno($ch)) {

				//$msg = curl_error($ch);
				error_log("page : [cyanite_apis] : function [FileUpload] : error0 : ".curl_error($ch));
				$msg="0"; // error
			}
			else {

				$msg = "1";//$ch;//'File uploaded successfully.';
			}

			curl_close ($ch);
			
			$return = array('msg' => $msg);
			return $return;
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_apis] : function [FileUpload] : error1 : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_apis","FileUpload",$e->getMessage());
		}
		
		
	}
	
	function create_LibraryTrack($upload_id, $title){
		error_log("create_LibraryTrack called.");
			
		$sonic_functions = new sonic_functions();

		try{
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.cyanite.ai/graphql',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_SSL_VERIFYPEER => false,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{"query":"mutation LibraryTrackCreateMutation($input: LibraryTrackCreateInput!) {\\r\\n  libraryTrackCreate(input: $input) {\\r\\n    __typename\\r\\n    ... on LibraryTrackCreateSuccess {\\r\\n      createdLibraryTrack {\\r\\n        id\\r\\n      }\\r\\n    }\\r\\n    ... on LibraryTrackCreateError {\\r\\n      code\\r\\n      message\\r\\n    }\\r\\n  }\\r\\n}\\r\\n","variables":{"input":{"uploadId":"'.$upload_id.'","title":"'.$title.'"}}}',
			  CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer '.$GLOBALS['auth_token']
			  ),
			));

			$response = curl_exec($curl);

			if (curl_errno($curl)) {
			    $error_msg = curl_error($curl);
			    error_log("page : [cyanite_apis] : function [create_LibraryTrack] : error : ".$error_msg);
			    $response = "0"; // error
			}

			curl_close($curl);
			//echo $response;
			return $response;
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_apis] : function [create_LibraryTrack] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_apis","create_LibraryTrack",$e->getMessage());
		}
	
	}
		
	function Fetch_AnyalisedData($LibraryTrackid){
		
		error_log("Fetch_AnyalisedData called.");
		
		$sonic_functions = new sonic_functions();

		try{
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.cyanite.ai/graphql',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_SSL_VERIFYPEER => false,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  /*CURLOPT_POSTFIELDS =>'{"query":"query LibraryTrackQuery($libraryTrackId: ID!) {\\r\\n  libraryTrack(id: $libraryTrackId) {\\r\\n    __typename\\r\\n    ... on LibraryTrackNotFoundError {\\r\\n      message\\r\\n    }\\r\\n    ... on LibraryTrack {\\r\\n      id\\r\\n      audioAnalysisV6 {\\r\\n        __typename\\r\\n        ... on AudioAnalysisV6Finished {\\r\\n          result {\\r\\n            valence\\r\\n            arousal\\r\\n            energyLevel\\r\\n            energyDynamics\\r\\n            emotionalProfile\\r\\n            emotionalDynamics\\r\\n            voicePresenceProfile\\r\\n            predominantVoiceGender\\r\\n            voice {\\r\\n              female\\r\\n              male\\r\\n              instrumental\\r\\n            }\\r\\n            musicalEraTag\\r\\n            instrumentPresence {\\r\\n              percussion\\r\\n              synth\\r\\n              piano\\r\\n              acousticGuitar\\r\\n              electricGuitar\\r\\n              strings\\r\\n              bass\\r\\n              bassGuitar\\r\\n              brassWoodwinds\\r\\n            }\\r\\n            instrumentTags\\r\\n            mood {\\r\\n              aggressive\\r\\n              calm\\r\\n              chilled\\r\\n              dark\\r\\n              energetic\\r\\n              epic\\r\\n              happy\\r\\n              romantic\\r\\n              sad\\r\\n              scary\\r\\n              sexy\\r\\n              ethereal\\r\\n              uplifting\\r\\n            }\\r\\n            moodTags\\r\\n            moodMaxTimes {\\r\\n              mood\\r\\n              start\\r\\n              end\\r\\n            }\\r\\n            genre {\\r\\n              ambient\\r\\n              blues\\r\\n              classical\\r\\n              country\\r\\n              electronicDance\\r\\n              folk\\r\\n              indieAlternative\\r\\n              jazz\\r\\n              latin\\r\\n              metal\\r\\n              pop\\r\\n              punk\\r\\n              rapHipHop\\r\\n              reggae\\r\\n              rnb\\r\\n              rock\\r\\n              singerSongwriter\\r\\n            }\\r\\n            genreTags\\r\\n            subgenreEdm {\\r\\n              breakbeatDrumAndBass\\r\\n              deepHouse\\r\\n              electro\\r\\n              house\\r\\n              minimal\\r\\n              techHouse\\r\\n              techno\\r\\n              trance\\r\\n            }\\r\\n            subgenreEdmTags\\r\\n            segments {\\r\\n              timestamps\\r\\n              mood {\\r\\n                aggressive\\r\\n                calm\\r\\n                chilled\\r\\n                dark\\r\\n                energetic\\r\\n                epic\\r\\n                happy\\r\\n                romantic\\r\\n                sad\\r\\n                scary\\r\\n                sexy\\r\\n                ethereal\\r\\n                uplifting\\r\\n              }\\r\\n              voice {\\r\\n                female\\r\\n                instrumental\\r\\n                male\\r\\n              }\\r\\n              instruments {\\r\\n                percussion\\r\\n                synth\\r\\n                piano\\r\\n                acousticGuitar\\r\\n                electricGuitar\\r\\n                strings\\r\\n                bass\\r\\n                bassGuitar\\r\\n                brassWoodwinds\\r\\n              }\\r\\n              genre {\\r\\n                ambient\\r\\n                blues\\r\\n                classical\\r\\n                country\\r\\n                electronicDance\\r\\n                folk\\r\\n                indieAlternative\\r\\n                jazz\\r\\n                latin\\r\\n                metal\\r\\n                pop\\r\\n                punk\\r\\n                rapHipHop\\r\\n                reggae\\r\\n                rnb\\r\\n                rock\\r\\n                singerSongwriter\\r\\n              }\\r\\n              subgenreEdm {\\r\\n                breakbeatDrumAndBass\\r\\n                deepHouse\\r\\n                electro\\r\\n                house\\r\\n                minimal\\r\\n                techHouse\\r\\n                techno\\r\\n                trance\\r\\n              }\\r\\n              valence\\r\\n              arousal\\r\\n            }\\r\\n            experimental_keywords {\\r\\n              keyword\\r\\n              weight\\r\\n            }\\r\\n            bpm\\r\\n            key\\r\\n            timeSignature\\r\\n          }\\r\\n        }\\r\\n      }\\r\\n    }\\r\\n  }\\r\\n}","variables":{"libraryTrackId":"'.$LibraryTrackid.'"}}',*/
			  CURLOPT_POSTFIELDS =>'{"query":"query LibraryTrackQuery($libraryTrackId: ID!) {\\r\\n  libraryTrack(id: $libraryTrackId) {\\r\\n    __typename\\r\\n    ... on LibraryTrackNotFoundError {\\r\\n      message\\r\\n    }\\r\\n    ... on LibraryTrack {\\r\\n      id\\r\\n      audioAnalysisV6 {\\r\\n        __typename\\r\\n        ... on AudioAnalysisV6Finished {\\r\\n          result {\\r\\n            valence\\r\\n            arousal\\r\\n            energyLevel\\r\\n            energyDynamics\\r\\n            emotionalProfile\\r\\n            emotionalDynamics\\r\\n            voicePresenceProfile\\r\\n            predominantVoiceGender\\r\\n            voice {\\r\\n              female\\r\\n              male\\r\\n              instrumental\\r\\n            }\\r\\n            musicalEraTag\\r\\n            instrumentPresence {\\r\\n              percussion\\r\\n              synth\\r\\n              piano\\r\\n              acousticGuitar\\r\\n              electricGuitar\\r\\n              strings\\r\\n              bass\\r\\n              bassGuitar\\r\\n              brassWoodwinds\\r\\n            }\\r\\n            instrumentTags\\r\\n            mood {\\r\\n              aggressive\\r\\n              calm\\r\\n              chilled\\r\\n              dark\\r\\n              energetic\\r\\n              epic\\r\\n              happy\\r\\n              romantic\\r\\n              sad\\r\\n              scary\\r\\n              sexy\\r\\n              ethereal\\r\\n              uplifting\\r\\n            }\\r\\n            moodTags\\r\\n            moodMaxTimes {\\r\\n              mood\\r\\n              start\\r\\n              end\\r\\n            }\\r\\n            genre {\\r\\n              ambient\\r\\n              blues\\r\\n              classical\\r\\n              country\\r\\n              electronicDance\\r\\n              folk\\r\\n              indieAlternative\\r\\n              jazz\\r\\n              latin\\r\\n              metal\\r\\n              pop\\r\\n              punk\\r\\n              rapHipHop\\r\\n              reggae\\r\\n              rnb\\r\\n              rock\\r\\n              singerSongwriter\\r\\n            }\\r\\n            genreTags\\r\\n            subgenreEdm {\\r\\n              breakbeatDrumAndBass\\r\\n              deepHouse\\r\\n              electro\\r\\n              house\\r\\n              minimal\\r\\n              techHouse\\r\\n              techno\\r\\n              trance\\r\\n            }\\r\\n            subgenreEdmTags\\r\\n            segments {\\r\\n              timestamps\\r\\n              mood {\\r\\n                aggressive\\r\\n                calm\\r\\n                chilled\\r\\n                dark\\r\\n                energetic\\r\\n                epic\\r\\n                happy\\r\\n                romantic\\r\\n                sad\\r\\n                scary\\r\\n                sexy\\r\\n                ethereal\\r\\n                uplifting\\r\\n              }\\r\\n              voice {\\r\\n                female\\r\\n                instrumental\\r\\n                male\\r\\n              }\\r\\n              instruments {\\r\\n                percussion\\r\\n                synth\\r\\n                piano\\r\\n                acousticGuitar\\r\\n                electricGuitar\\r\\n                strings\\r\\n                bass\\r\\n                bassGuitar\\r\\n                brassWoodwinds\\r\\n              }\\r\\n              genre {\\r\\n                ambient\\r\\n                blues\\r\\n                classical\\r\\n                country\\r\\n                electronicDance\\r\\n                folk\\r\\n                indieAlternative\\r\\n                jazz\\r\\n                latin\\r\\n                metal\\r\\n                pop\\r\\n                punk\\r\\n                rapHipHop\\r\\n                reggae\\r\\n                rnb\\r\\n                rock\\r\\n                singerSongwriter\\r\\n              }\\r\\n              subgenreEdm {\\r\\n                breakbeatDrumAndBass\\r\\n                deepHouse\\r\\n                electro\\r\\n                house\\r\\n                minimal\\r\\n                techHouse\\r\\n                techno\\r\\n                trance\\r\\n              }\\r\\n              valence\\r\\n              arousal\\r\\n            }\\r\\n            \\r\\n            bpm\\r\\n            key\\r\\n            timeSignature\\r\\n          }\\r\\n        }\\r\\n      }\\r\\n    }\\r\\n  }\\r\\n}\\r\\n","variables":{"libraryTrackId":"'.$LibraryTrackid.'"}}',
			  CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.$GLOBALS['auth_token'],
				'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);

			if (curl_errno($curl)) {
			    $error_msg = curl_error($curl);
			    error_log("page : [cyanite_apis] : function [Fetch_AnyalisedData] : error : ".$error_msg);
			}

			curl_close($curl);
			
			return $response;
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_apis] : function [Fetch_AnyalisedData] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_apis","Fetch_AnyalisedData",$e->getMessage());
		}
		

	}

	function Creating_crate($crate_name){	
		
		error_log("Creating_crate called.");
		
		$sonic_functions = new sonic_functions();

		try{
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.cyanite.ai/graphql',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_SSL_VERIFYPEER => false,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{"query":"mutation crateCreate($input: CrateCreateInput!) {\\r\\n  crateCreate(input: $input) {\\r\\n    __typename\\r\\n    ... on CrateCreateSuccess {\\r\\n      id\\r\\n    }\\r\\n    ... on Error {\\r\\n      message\\r\\n    }\\r\\n  }\\r\\n}\\r\\n","variables":{"input":{"name":"'.$crate_name.'"}}}',
			  CURLOPT_HTTPHEADER => array(
			    'Authorization: Bearer '.$GLOBALS['auth_token'],
			    'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);

			if (curl_errno($curl)) {
			    $error_msg = curl_error($curl);
			    error_log("page : [cyanite_apis] : function [Creating_crate] : error : ".$e->getMessage());
			}

			curl_close($curl);		
			return $response;
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_apis] : function [Creating_crate] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_apis","Creating_crate",$e->getMessage());
		}

	}


	function add_libraryTrack_in_crate($libraryTrack_id, $crate_id){	
		
		error_log("add_libraryTrack_in_crate called.");
		
		$sonic_functions = new sonic_functions();

		try{
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.cyanite.ai/graphql',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_SSL_VERIFYPEER => false,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{"query":"mutation crateAddLibraryTracks($input: CrateAddLibraryTracksInput!) {\\r\\n  crateAddLibraryTracks(input: $input) {\\r\\n    ... on CrateAddLibraryTracksSuccess {\\r\\n      __typename\\r\\n    }\\r\\n    ... on CrateAddLibraryTracksError {\\r\\n      __typename\\r\\n      code\\r\\n      message\\r\\n    }\\r\\n  }\\r\\n}\\r\\n","variables":{"input":{"libraryTrackIds":["'.$libraryTrack_id.'"],"crateId":"'.$crate_id.'"}}}',
			  CURLOPT_HTTPHEADER => array(
			    'Authorization: Bearer '.$GLOBALS['auth_token'],
			    'Content-Type: application/json'
			  ),
			));

			
			$response = curl_exec($curl);

			if (curl_errno($curl)) {
			    $error_msg = curl_error($curl);
			    error_log("page : [cyanite_apis] : function [add_libraryTrack_in_crate] : error : ".$error_msg);
			    $response = "0";
			}
			
			curl_close($curl);		
			
			return $response;
		}
		catch(Exception $e)
		{			
			error_log("page : [cyanite_apis] : function [add_libraryTrack_in_crate] : error : ".$e->getMessage());
			$sonic_functions->trigger_log_email("cyanite_apis","add_libraryTrack_in_crate",$e->getMessage());
		} 
	}
}

// $CyaniteAI = new CyaniteAI();
// $CyaniteAI->add_libraryTrack_in_crate("1832125","46");

?>
