<?php

include 'userinfo.php';

if($_GET['listing'] || $_GET['listing_download']) {
	//============================================================ common filter
	$common = "
		FROM ".DB2.".student A
			INNER JOIN ".DB2.".stud_sponsor B ON (A.std_student_id = B.std_student_id)
			INNER JOIN ".DB2.".sponsor C ON ( B.spn_sponsor_code = C.spn_sponsor_code) 
		WHERE C.spn_sponsor_type = '05' -- AND A.std_status = '01'
			-- global filter
			AND CONCAT_WS('__',
				A.std_student_id,
				A.std_student_name,
				IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), std_passport),
				A.std_extended_field->>'$.std_status_desc',
				A.std_program_level,
				B.ssp_reference_no,
				B.ssp_warrant_no,
				B.ssp_warrant_amt,
				ssp_ptptn_deduction_amt,
                ssp_ptptn_balance_amt
			) LIKE CONCAT('%', ?, '%')
			-- smartFilter
			AND A.std_student_id LIKE CONCAT('%', ?, '%')
			AND A.std_student_name LIKE CONCAT('%', ?, '%')
			AND IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), std_passport) LIKE CONCAT('%', ?, '%')
			AND A.std_program_level LIKE CONCAT('%', ?, '%')
			AND A.std_status LIKE CONCAT('%', ?, '%')
			AND B.ssp_reference_no LIKE CONCAT('%', ?, '%')
			AND B.ssp_warrant_no LIKE CONCAT('%', ?, '%')
	";
	$commonParam = [
		$_POST['search']['value'],
		$_POST['smartFilter']['std_student_id'],
		$_POST['smartFilter']['std_student_name'],
		$_POST['smartFilter']['ic_passport'],
		$_POST['smartFilter']['std_program_level'],
		$_POST['smartFilter']['std_status'],
		$_POST['smartFilter']['ssp_reference_no'],
		$_POST['smartFilter']['ssp_warrant_no']
	];

	//============================================================ record count
	$sql = "SELECT COUNT(*) C $common";
	$recordsFiltered = executeQueryV2($sql, $commonParam)[0]['C'];

	//============================================================ main sql
	if($_GET['listing']) {
		$sql = "
			SELECT
				A.std_student_id,
				A.std_student_name,
				IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), std_passport) ic_passport,
				A.std_extended_field->>'$.std_status_desc' studStatus,
				A.std_program_level,
				B.ssp_reference_no,
				B.ssp_warrant_no,
				B.ssp_id,
				FORMAT(B.ssp_warrant_amt, 2) ssp_warrant_amt,
				FORMAT(ssp_ptptn_deduction_amt, 2) deduction,
				FORMAT(ssp_ptptn_balance_amt, 2) balance
			$common
			ORDER BY {$_POST['orderBy'][0]} {$_POST['orderBy'][1]}
			LIMIT ".$_POST['start'].", ".$_POST['length']."
		";
		$data = executeQueryV2($sql, $commonParam);
		foreach($data as &$d) $d['view'] = 'index.php?a='.flc_url_encode("page=page_wrapper&menuID=1479&mode=view&ssp_id=".$d['ssp_id']);
	
		return [
			'draw' => $_POST['draw'],
			'recordsFiltered' => $recordsFiltered,
			'data' => $data
		];
	}
	else {
		$data = executeQueryV2("
			SELECT
				null `Indexing`,
				A.std_student_id `Matric`,
				A.std_student_name `Name`,
				IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), std_passport) `NRIC/Passport`,
				A.std_extended_field->>'$.std_status_desc' `Status`,
				A.std_program_level `Program Level`,
				B.ssp_reference_no `No. Fail Pinjaman`,
				B.ssp_warrant_no `Warrant No.`,
				ssp_warrant_amt `FORMAT_CURRENCY::Warrant Amt`,
				ssp_ptptn_deduction_amt `FORMAT_CURRENCY::Deduction`,
				ssp_ptptn_balance_amt `FORMAT_CURRENCY::ssp_ptptn_deduction_amt`
				$common
			ORDER BY 1
		", $commonParam);

		generateExcel([
			"filename" => "List of PTPTN Students ".date("Ymd hiA"),
			"FILTERED CRITERIA" => [
				["Filter Type"=>"Global Search", "Filter Value"=>$_POST['search']['value']],
				["Filter Type"=>"Matric", "Filter Value"=>$_POST['smartFilter']['std_student_id']],
				["Filter Type"=>"Name", "Filter Value"=>$_POST['smartFilter']['std_student_name']],
				["Filter Type"=>"NRIC/Passport", "Filter Value"=>$_POST['smartFilter']['ic_passport']],
				["Filter Type"=>"Program Level", "Filter Value"=>$_POST['smartFilter']['std_program_level']],
				["Filter Type"=>"No. Fail Pinjaman", "Filter Value"=>$_POST['smartFilter']['ssp_reference_no']],
				["Filter Type"=>"Warrant No.", "Filter Value"=>$_POST['smartFilter']['ssp_warrant_no']],
				["Filter Type"=>"Status", "Filter Value"=>$_POST['smartFilter']['std_status']],
			],
			"DATA" => $data
		]);
	}
}