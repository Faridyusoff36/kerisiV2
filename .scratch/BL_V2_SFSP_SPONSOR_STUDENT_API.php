<?php

include 'system_prerequisite.php';

//============================================================ common param
$commonParam = [
	$_GET['dt_sponsorStudent']
];

//============================================================ smart filter
if($_POST['search']['value']) {
	$smartFilter .= "
		AND CONCAT_WS('__',
			A.std_student_id,
			A.std_student_name,
			A.std_extended_field->>'$.std_status_desc',
			A.std_extended_field->>'$.std_program_desc',
			DATE_FORMAT((SELECT MIN(X.spc_date_from) FROM ".DB2.".stud_sponsor_period_cover X WHERE B.ssp_id = X.ssp_id), '%d/%m/%Y'),
			DATE_FORMAT((SELECT MAX(X.spc_date_to) FROM ".DB2.".stud_sponsor_period_cover X WHERE B.ssp_id = X.ssp_id), '%d/%m/%Y'),
			B.ssp_coverage
		) LIKE CONCAT('%', ?, '%')
	";
	$commonParam[] = $_POST['search']['value'];
	$csvHeader[] = ["Search :", $_POST['search']['value']];
}
if($_POST['smartFilter']['std_student_id']) {
	$smartFilter .= " AND A.std_student_id LIKE CONCAT('%', ?, '%') ";
	$commonParam[] = $_POST['smartFilter']['std_student_id'];
	$csvHeader[] = ["Matric :", $_POST['smartFilter']['std_student_id']];
}
if($_POST['smartFilter']['std_student_name']) {
	$smartFilter .= " AND A.std_student_name LIKE CONCAT('%', ?, '%') ";
	$commonParam[] = $_POST['smartFilter']['std_student_name'];
	$csvHeader[] = ["Name :", $_POST['smartFilter']['std_student_name']];
}
if($_POST['smartFilter']['std_status']) {
	$smartFilter .= " AND std_status = ? ";
	$commonParam[] = $_POST['smartFilter']['std_status'];
	$csvHeader[] = ["Status :", $_POST['smartFilter']['std_status_desc']];
}
if($_POST['smartFilter']['std_program_desc']) {
	$smartFilter .= " AND UPPER(IFNULL(A.std_extended_field->>'$.std_program_desc', '')) LIKE CONCAT('%', UPPER(?), '%') ";
	$commonParam[] = $_POST['smartFilter']['std_program_desc'];
	$csvHeader[] = ["Program :", $_POST['smartFilter']['std_program_desc']];
}
if($_POST['smartFilter']['ssp_coverage']) {
	$smartFilter .= " AND UPPER(IFNULL(B.ssp_coverage, '')) LIKE CONCAT('%', UPPER(?), '%') ";
	$commonParam[] = $_POST['smartFilter']['ssp_coverage'];
	$csvHeader[] = ["Coverage :", $_POST['smartFilter']['ssp_coverage']];
}

//============================================================ common filter
$common = "
	FROM
		".DB2.".student A,
		".DB2.".stud_sponsor B,
		".DB2.".sponsor D 
	WHERE
		A.std_student_id = B.std_student_id
		AND B.spn_sponsor_code = D.spn_sponsor_code
		AND D.spn_sponsor_id = ?
		$smartFilter
";

if($_GET['formDetails']) {
	$data = executeQueryV2("
		SELECT
			spn_sponsor_code,
			spn_sponsor_name,
			IF(spn_is_additional_spon_allow='Y', 'YES', 'NO') spn_is_additional_spon_allow,
			spn_amt_cover_stud,
			FORMAT(spn_limit_amt, 2) spn_limit_amt,
			FORMAT(spn_allowance_amt, 2) spn_allowance_amt,
			spn_extended_field->>'$.stf_staff_incharge_desc' stf_staff_incharge_desc,
			spn_extended_field->>'$.spn_sponsor_type_desc' spn_sponsor_type_desc,
			spn_extended_field->>'$.spn_prefered_currency_desc' spn_prefered_currency_desc,
			spn_extended_field->>'$.spn_payment_status_desc' spn_payment_status_desc,
			spn_extended_field->>'$.spn_sponsorship_category_desc' spn_sponsorship_category_desc,
			spn_extended_field->>'$.spn_status_invoice_desc' spn_status_invoice_desc,
			spn_extended_field->>'$.spn_allowance_type_desc' spn_allowance_type_desc,
			spn_extended_field->>'$.spn_status_desc' spn_status_desc
		FROM ".DB2.".sponsor
		WHERE spn_sponsor_id = ?
	", [$_POST['spn_sponsor_id']])[0];
	return ['data' => $data, "new" => "index.php?a=".flc_url_encode("page=page_wrapper&menuID=1479&mode=edit&is_new=1&spn_sponsor_code=".$data['spn_sponsor_code'])];
}

if($_GET['downloadCSV']) {
	$sql = "
		SELECT
			A.std_student_name `Name`,
			A.std_student_id `Matric`,
			IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), A.std_passport) `NRIC/Passport`,
			A.std_extended_field->>'$.std_status_desc' `Status`,
			A.std_extended_field->>'$.std_program_desc' `Program`,
			DATE_FORMAT((SELECT MIN(X.spc_date_from) FROM ".DB2.".stud_sponsor_period_cover X WHERE B.ssp_id = X.ssp_id), '%d/%m/%Y') `Start Date`,
			DATE_FORMAT((SELECT MAX(X.spc_date_to) FROM ".DB2.".stud_sponsor_period_cover X WHERE B.ssp_id = X.ssp_id), '%d/%m/%Y') `End Date`
		$common
		ORDER BY 1
	";
	$data = executeQueryV2($sql, $commonParam);

	generateCSV([
		"filename" => "Sponsor Student",
		"header" => $csvHeader,
		"index" => true,
		"content" => $data
	]);
	die;
}

if($_GET['dt_sponsorStudent']) {

	//============================================================ record count
	$recordsFiltered = executeQueryV2("SELECT COUNT(*) C $common", $commonParam)[0]['C'];

	//============================================================ main sql
	$data = executeQueryV2("
		SELECT
			A.std_student_id,
			IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), A.std_passport) std_ic_no,
			A.std_student_name,
			A.std_extended_field->>'$.std_status_desc' std_status_desc,
			A.std_extended_field->>'$.std_program_desc' std_program_desc,
			DATE_FORMAT((SELECT MIN(X.spc_date_from) FROM ".DB2.".stud_sponsor_period_cover X WHERE B.ssp_id = X.ssp_id), '%d/%m/%Y') dateFrom,
			DATE_FORMAT((SELECT MAX(X.spc_date_to) FROM ".DB2.".stud_sponsor_period_cover X WHERE B.ssp_id = X.ssp_id), '%d/%m/%Y') dateTo,
			B.ssp_id,
			B.ssp_coverage
		$common
		ORDER BY {$_POST['orderBy'][0]} {$_POST['orderBy'][1]}
		LIMIT {$_POST['start']}, {$_POST['length']}
	", $commonParam);
	
	foreach($data as &$d) {
		$d['edit'] = "index.php?a=".flc_url_encode("page=page_wrapper&menuID=1479&mode=edit&ssp_id=".$d['ssp_id']);
	}

	//============================================================ finally
	return [
		'draw' => $_POST['draw'],
		'recordsFiltered' => $recordsFiltered,
		'data' => $data?:[]
	];
}

if($_GET['delete']) {
	executeQueryV2("DELETE FROM ".DB2.".stud_sponsor WHERE ssp_id = ?", [$_POST['ssp_id']]);
	return ["status" => "ok"];
}

if($_GET['import']) {
	
	$csv = $_POST['csv'];
	$countUpdate = 0;
	$countInsert = 0;

	//================================================================================ checking student active
	$inActive = [];
	for($x=1; $x<count($csv); $x++) {
		$cols = $csv[$x];
		$std_student_id = $cols[0];
		$rs = executeQueryV2("SELECT 1 FROM ".DB2.".student WHERE std_status IS NOT NULL AND std_student_id = '$std_student_id'");
		if(!$rs) $inActive[] = $std_student_id;
	}
	if(count($inActive)) {
		return [
			'status' => 'inactive',
			'list' => $inActive
		];
	}

	//================================================================================ if all ok, crud
	for($x=1; $x<count($csv); $x++) {
		$cols = $csv[$x];
			
		$spn_sponsor_code = $_POST['spn_sponsor_code'];
		$std_student_id = $cols[0];
		$spc_date_from = $cols[1];
		$spc_date_to = $cols[2];
		$spc_sems_from = $cols[3];
		$spc_sems_to = $cols[4];
		$ssp_coverage = $cols[5];

		$rs = executeQueryV2("SELECT ssp_id FROM ".DB2.".stud_sponsor WHERE std_student_id = '$std_student_id' AND spn_sponsor_code = ?", [$spn_sponsor_code]);
		if($rs) {
			$countUpdate++;
			$ssp_id = $rs[0]['ssp_id'];

			executeQueryV2("
				UPDATE ".DB2.".stud_sponsor
				SET ssp_coverage = '$ssp_coverage', updatedby = '".$_USER['USERNAME']."'
				WHERE ssp_id = '$ssp_id'
			");
		}
		else {
			$countInsert++;
			executeQueryV2("CALL ".DB2.".getTableSequenceNum('stud_sponsor', @SEQ)");
			$ssp_id = executeQueryV2("SELECT @SEQ")[0]['@SEQ'];

			executeQueryV2("
				INSERT INTO ".DB2.".stud_sponsor
				SET std_student_id = '$std_student_id', spn_sponsor_code = ?, ssp_coverage = '$ssp_coverage', ssp_id = '$ssp_id', createdby = '".$_USER['USERNAME']."'
			", [$spn_sponsor_code]);
		}

		executeQueryV2("CALL ".DB2.".getTableSequenceNum('stud_sponsor_period_cover', @SEQ)");
		$spc_id = executeQueryV2("SELECT @SEQ")[0]['@SEQ'];

		$spc_date_from = executeQueryV2("SELECT COALESCE(
			STR_TO_DATE(?, '%e/%c/%Y'),
			STR_TO_DATE(?, '%d/%m/%Y'),
			STR_TO_DATE(?, '%Y%m%d')
		) A", [
			$spc_date_from?:null,
			$spc_date_from?:null,
			$spc_date_from?:null,
		])[0]['A'];

		$spc_date_to = executeQueryV2("SELECT COALESCE(
			STR_TO_DATE(?, '%e/%c/%Y'),
			STR_TO_DATE(?, '%d/%m/%Y'),
			STR_TO_DATE(?, '%Y%m%d')
		) A", [
			$spc_date_to?:null,
			$spc_date_to?:null,
			$spc_date_to?:null,
		])[0]['A'];

		executeQueryV2("
			INSERT INTO ".DB2.".stud_sponsor_period_cover
			SET
				spc_id = '$spc_id',
				ssp_id = '$ssp_id',
				spc_date_from ='$spc_date_from',
				spc_date_to = '$spc_date_to',
				spc_sems_from = ?,
				spc_sems_to = ?,
				createdby = '".$_USER['USERNAME']."'
			ON DUPLICATE KEY UPDATE updatedby = '".$_USER['USERNAME']."'
		", [
			$spc_sems_from?:null,
			$spc_sems_to?:null
		]);
	}

	return [
		'status' => 'ok',
		'countUpdate' => $countUpdate,
		'countInsert' => $countInsert
	];
}

if($_GET['downloadTemplate']) {
	$file = __DIR__ . "/upload/template/Template List of Sponsored Student.csv";

	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary");
	header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
	ob_clean();
	readfile($file);
	die;
}