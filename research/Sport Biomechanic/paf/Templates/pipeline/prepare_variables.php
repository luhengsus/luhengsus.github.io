<?php

if (file_exists($template_directory .'settings.php')) 	
	require($template_directory . 'settings.php');

require($v3dScriptDirectory . 'EMG_prepare_variables.php');

$c3d_files_count = 0; //is used to define number of EMG graphs for instrumented treadmill

foreach($measurements as $m) {
	if(($m["Used"] === "True") && ($m["Measurement_type"] === "Dynamic")) {
		$path_parts = pathinfo($m["Filename"]);
		$filenames_arr[] = $path_parts['filename'] . ".c3d";
		//$treadmill_speeds_arr[] = $m["Treadmill_speed"];
		$c3d_files_count++;
	}
}

if (!empty($filenames_arr))
	$filenames = implode('+',$filenames_arr);

//Copy theia c3ds to $working_directory
if (strpos($subsession['Model_used'],'Theia') !== false) {
	foreach($measurements as $m) {
		if($m["Used"] === "True") {
			$path_parts = pathinfo($m["Filename"]);
			$foldername = $path_parts['filename'];
			$files = glob($working_directory . 'TheiaFormatData\\' . $foldername . '\\pose_filt_*.c3d');
			rsort($files); //use one with lowest number
			
			$pose_filt_file = $working_directory . 'TheiaFormatData\\' . $foldername . '\\pose_filt_'.$m["Theia_c3d_file"].'.c3d';
			
			if ($m["Theia_c3d_file"] == 0)
				$file_to_copy = array_pop($files);
			else {
				$file_to_copy = $pose_filt_file;
				echo "! Theia c3d used: " . $file_to_copy . "\n";
			}
			
			$dest_name = $working_directory . $path_parts['filename'] . '_Theia.c3d';
			copy($file_to_copy, $dest_name);
		}
	}	
}

//EMG --------------
if ($includes_noraxon)
	$noraxon_exists = 'TRUE';
else
	$noraxon_exists = 'FALSE';

if ($includes_mega_me6000)
	$mega_me6000_exists = 'TRUE';
else
	$mega_me6000_exists = 'FALSE';

if ($includes_delsys_trigno)
	$delsys_trigno_exists = 'TRUE';
else
	$delsys_trigno_exists = 'FALSE';

if ($includes_myon)
	$myon_exists = 'TRUE';
else
	$myon_exists = 'FALSE';

if ($includes_analog_EMG)
	$analog_EMG_exists = 'TRUE';
else
	$analog_EMG_exists = 'FALSE';

if(file_exists($normativeDirectory .'EMG_normatives.vnd'))
	$normative_non_noraxon_file_exists = 'TRUE';
else
	$normative_non_noraxon_file_exists = 'FALSE';

if($includes_analog_EMG) 
	$EMG_unit_multiply = $EMG_unit_multiplicator; 
else 
	$EMG_unit_multiply = 1000000;

if ($EMG_units_pct)
	$EMG_y_axis_label = '%';
elseif ($EMG_unit_multiply == 1)
	$EMG_y_axis_label = 'volt';
elseif ($EMG_unit_multiply == 1000)
	$EMG_y_axis_label = 'milivolt';
elseif ($EMG_unit_multiply == 1000000)
	$EMG_y_axis_label = 'microvolt';
elseif ($EMG_unit_multiply == 1000000000)
	$EMG_y_axis_label = 'nanovolt';

if (!empty($EMG_names_array_left)) {
	$EMG_names_array_left_exists = "TRUE";
	$EMG_graph_signals_name_left = implode('+',$EMG_names_array_left);
}
else
	$EMG_names_array_left_exists = "FALSE";

if (!empty($EMG_names_array_right)) {
	$EMG_names_array_right_exists = "TRUE";
	$EMG_graph_signals_name_right = implode('+',$EMG_names_array_right);
}
else
	$EMG_names_array_right_exists = "FALSE";

$EMG_names_list = implode('+',$EMG_names_array);

if (!empty($EMG_names_array_left) || !empty($EMG_names_array_right)) {
	$EMG_valid_names = array_merge($EMG_names_array_right, $EMG_names_array_left); //make sure we only include EMG channels that are associated with a body side.
	$EMG_valid_names_list = implode('+',$EMG_valid_names);

	foreach ($EMG_valid_names as $EMG_signal_name) {
		$p2d_sig_name = preg_replace('/(^L |^Left |^L_|^Left_|^L-|^Left-|^R |^Right |^R_|^Right_|^R-|^Right-|^EMG_L_|^EMG L |^EMG_Left_|^EMG Left |^EMG_R_|^EMG R |^EMG_Right_|^EMG Right )/', '',$EMG_signal_name);
		$p2d_signal_names[] = str_replace('_',' ',$p2d_sig_name); //remove underscores, as EMG normative names should be without them
	}
	$p2d_signal_names_list = implode('+',$p2d_signal_names);
}

$selected_files = 'Dynamic';
$sd_line_style = 'Solid';
$sd_line_bold = 'TRUE';

$page_number = 4; //This is the first EMG page without UB and task specific page

if (strpos($report_template, 'report_base_extended') !== false)
	$page_number = $page_number + 3;
	
if (strpos($report_template_append, 'unspecified') === false)
	$page_number = $page_number + 1;

$first_EMG_page = $page_number;

if ($count_EMG_signals_left <= 4 || $count_EMG_signals_right <= 4)
	$last_EMG_page = $first_EMG_page + 2;
else 
	$last_EMG_page = $first_EMG_page + 5;

for ($i = $first_EMG_page; $i <= $last_EMG_page; $i++)
	$EMG_pages_nums_arr[] = $i;

$EMG_pages_nums_list = implode('+',$EMG_pages_nums_arr);


if ($includes_noraxon || $includes_delsys_trigno || $includes_mega_me6000 || $includes_myon || $includes_analog_EMG) {
	$EMG_names_array = array(); //for now we overwrite the list until we reached the last file and assume the same EMG channels were used in all files.
	$EMG_type_array = array();
	$EMG_folder_array = array();
	$EMG_names_array_web = array(); 
	$EMG_type_array_web = array();
	$EMG_folder_array_web = array();
	$EMG_exclude_seq_array = array();
	$EMG_event_seq_array = array();
	$EMG_points_array = array();
	$EMG_points_array_web = array();
	$EMG_names_norm_array = array();
	$EMG_type_norm_array = array();
	$EMG_folder_norm_array = array();
	$EMG_points_norm_array = array();

	foreach($measurements as $m) {
		if (strcmp('True', $m["Used"]) == 0 and isset($m["Channels"]) and strpos($m["Type"],"Static") === false) {
			foreach($m["Channels"] as $ch) {
				if (
						(
							(
								(
									strcmp('Noraxon', $ch["Board"]) == 0
									|| strcmp('MEGA/ME6000', $ch["Board"]) == 0
									|| (strpos($ch["Board"], 'Delsys Trigno') !== false && strpos($ch["Name"], "_ACC_") === false && strpos($ch["Name"], "_GYRO_") === false && strpos($ch["Name"], "_MAG_") === false)
									|| (strcmp('Cometa', $ch["Board"]) == 0 && strpos($ch["Name"], "_ACC_") === false && strpos($ch["Name"], "_GYRO_") === false && strpos($ch["Name"], "_MAG_") === false && strpos($ch["Name"], "_Q_") === false)
								)
								&& (strpos($ch["Name"], 'Sync') === false)
							)
							|| 
							(
								(
									strcmp('USB-2533', $ch["Board"]) == 0
									|| strcmp('PCI-DAS6402/16', $ch["Board"]) == 0
								) 
								&&
								(
									strpos($ch["Name"], "EMG_") !== false
									|| (
											substr($ch["Name"], 0, 1) == "R" 
											|| substr($ch["Name"], 0, 1) == "L"
										)
								)
							)
						)
						&& (!in_array(trim($ch["Name"]), $EMG_names_array))
					) {
						
					if (!in_array(trim($ch["Name"]), $EMG_names_array)) {
						if (stripos($ch["Name"], 'R ') === 0 || stripos($ch["Name"], 'R_') === 0 || stripos($ch["Name"], 'Right') === 0 || stripos($ch["Name"], 'EMG_R ') === 0 || stripos($ch["Name"], 'EMG_R_') === 0 || stripos($ch["Name"], 'EMG_Right') === 0) {
							$EMG_names_array[] = $ch["Name"] . "+" . $ch["Name"];
							$EMG_type_array[] = 'ANALOG+ANALOG';
							$EMG_folder_array[] = 'EMG_PROCESSED+EMG_RAW';
							$EMG_names_array_web[] = $ch["Name"];
							$EMG_type_array_web[] = 'ANALOG';
							$EMG_folder_array_web[] = 'EMG_RAW_web';
							$EMG_exclude_seq_array[] = ', ,';
						
							if(strpos($analysis_name, 'Word') !== false && (strpos($event_mode, 'Instrumented treadmill') !== false || !$auto_range))
								$EMG_event_seq_array[] = ('start+end, RHS+RHS');
							else 
								$EMG_event_seq_array[] = ('RHS+RHS, RHS+RHS');

							$EMG_points_array[] = '101+'.$points;
							
							$EMG_names_norm_array[] = $ch["Name"] . "+" . $ch["Name"] . '_raw+' . $ch["Name"] . "+" . $ch["Name"] . '_raw';
							$EMG_type_norm_array[] = 'DERIVED+DERIVED+DERIVED+DERIVED';
							$EMG_folder_norm_array[] = 'NORM_LOWER+NORM_LOWER+NORM_RANGE+NORM_RANGE';
							$EMG_points_norm_array[] = '101+'.$points.'+101+'.$points;
						}
						elseif  (stripos($ch["Name"], 'L ') === 0 || stripos($ch["Name"], 'L_') === 0 || stripos($ch["Name"], 'Left') === 0 || stripos($ch["Name"], 'EMG_L ') === 0 || stripos($ch["Name"], 'EMG_L_') === 0 || stripos($ch["Name"], 'EMG_Left') === 0) {
							if (!in_array(trim($ch["Name"]), $EMG_names_array))
								$EMG_names_array[] = $ch["Name"] . "+" . $ch["Name"];
							$EMG_type_array[] = 'ANALOG+ANALOG';
							$EMG_folder_array[] = 'EMG_PROCESSED+EMG_RAW';
							$EMG_names_array_web[] = $ch["Name"];
							$EMG_type_array_web[] = 'ANALOG';
							$EMG_folder_array_web[] = 'EMG_RAW_web';
							$EMG_exclude_seq_array[] = ', ,';
							
							if(strpos($analysis_name, 'Word') !== false && (strpos($event_mode, 'Instrumented treadmill') !== false || !$auto_range))
								$EMG_event_seq_array[] = ('start+end, LHS+LHS');
							else 
								$EMG_event_seq_array[] = ('LHS+LHS, LHS+LHS');

							$EMG_points_array[] = '101+'.$points;

							$EMG_names_norm_array[] = $ch["Name"] . "+" . $ch["Name"] . '_raw+' . $ch["Name"] . "+" . $ch["Name"] . '_raw';
							$EMG_type_norm_array[] = 'DERIVED+DERIVED+DERIVED+DERIVED';
							$EMG_folder_norm_array[] = 'NORM_LOWER+NORM_LOWER+NORM_RANGE+NORM_RANGE';
							$EMG_points_norm_array[] = '101+'.$points.'+101+'.$points;
						}
						else
							echo('!' . $ch["Name"] . ' is an invalid name for an EMG channel (must start with Left or Right to indicate side)\n');
					}
				}
			}
		}
	}

	$EMG_signal_names_exp = implode("+", $EMG_names_array);
	$EMG_type = implode("+", $EMG_type_array);
	$EMG_folder = implode("+", $EMG_folder_array);
	$EMG_points = implode('+', $EMG_points_array);

	$EMG_signal_names_web = implode("+", $EMG_names_array_web);
	$EMG_type_web = implode("+", $EMG_type_array_web);
	$EMG_folder_web = implode("+", $EMG_folder_array_web);

	$EMG_exclude_seq = implode(", ", $EMG_exclude_seq_array);
	$EMG_event_seq = implode(',', $EMG_event_seq_array);

	$EMG_names_norm = implode('+', $EMG_names_norm_array);
	$EMG_type_norm = implode('+', $EMG_type_norm_array);
	$EMG_folder_norm = implode('+', $EMG_folder_norm_array);
	$EMG_points_norm = implode('+', $EMG_points_norm_array);
}
?> 