<?php
include 'userinfo.php';

if($_GET['get_sponsorAmt']) {
	$spo_id = uniqid($_USER['USERNAME']);
	executeQueryV2("CALL ".DB2.".get_sponsorAmt(?, ?, ?, 'penaja', ?, '$spo_id', @p_flag, @p_msg, @errorMessage, @dataJson)", [$_GET['std_program'],$_GET['cim_semester_id'],$_GET['spn_sponsor_code'],$_USER['USERNAME']]);
	$seq = executeQueryV2("SELECT @SEQ")[0]['@SEQ'];
	executeQueryV2('reset');

	return ["status"=>"ok", "spo_id"=>$spo_id];
}

if($_GET['listing']) { //shared with datatable and download CSV ($_GET['listing']!='download')

	$p_current_sem = 'xxx';

	if($_GET['listing']==1) {
		return [
			'draw' => $_POST['draw'],
			'recordsFiltered' => 0,
			'data' => []
		];
	}

	//============================================================ common filter
	if($_GET['spn_sponsor_code']) {
		$filter .= " AND B.spn_sponsor_code = ? ";
		$param[] = $_GET['spn_sponsor_code'];
	}
	if($_GET['std_program']) {
		$filter .= " AND A.std_program_level = ? ";
		$param[] = $_GET['std_program'];
	}
	if($_GET['cim_semester_id']) {
		$filter .= " AND cim.cim_semester_id = ? ";
		$param[] =  $_GET['cim_semester_id'];
		$p_current_sem = explode(' ', $_GET['cim_semester_id'])[0];
	}
	if($_POST['search']['value']) {
		$filter .= "
			AND CONCAT_WS('__',
				A.std_student_id,
				A.std_student_name,
				CONCAT_WS(' - ', C.spn_sponsor_code, C.spn_sponsor_name)
			) LIKE CONCAT('%', ?, '%')
		";
		$param[] = $_POST['search']['value'];
	}
	if($_POST['smartFilter']['std_student_id']) {
		$filter .= " AND A.std_student_id  = ? ";
		$param[] = $_POST['smartFilter']['std_student_id'];
	}
	if($_POST['smartFilter']['std_student_name']) {
		$filter .= " AND A.std_student_name  LIKE CONCAT('%', ?, '%') ";
		$param[] = $_POST['smartFilter']['std_student_name'];
	}
	if($_POST['smartFilter']['std_status_desc']) {
		$filter .= " AND A.std_status = ? ";
		$param[] = $_POST['smartFilter']['std_status_desc'];
	}
	if($_POST['smartFilter']['outstanding_amt_from']) {
		$filter .= " AND SUM(IFNULL(cid_bal_amt, 0)) >= ? ";
		$param[] = str_replace(',', '', $_POST['smartFilter']['outstanding_amt_from'])*1;
	}
	if($_POST['smartFilter']['outstanding_amt_to']) {
		$filter .= " AND SUM(IFNULL(cid_bal_amt, 0)) <= ? ";
		$param[] = str_replace(',', '', $_POST['smartFilter']['outstanding_amt_to'])*1;
	}
	if($_POST['smartFilter']['cim_nett_amt_from']) {
		$filter .= " AND (SELECT ssa.ssa_sponsor_amt FROM ".DB2.".stud_sponsor_amount ssa WHERE ssa.spn_sponsor_code=C.spn_sponsor_code and ssa.cim_cust_id=A.std_student_id and ssa.cim_invoice_no=cim.cim_invoice_no ORDER BY ssa.createddate DESC LIMIT 1) >= ? ";
		$param[] = str_replace(',', '', $_POST['smartFilter']['cim_nett_amt_from'])*1;
	}
	if($_POST['smartFilter']['cim_nett_amt_to']) {
		$filter .= " AND (SELECT ssa.ssa_sponsor_amt FROM ".DB2.".stud_sponsor_amount ssa WHERE ssa.spn_sponsor_code=C.spn_sponsor_code and ssa.cim_cust_id=A.std_student_id and ssa.cim_invoice_no=cim.cim_invoice_no ORDER BY ssa.createddate DESC LIMIT 1) <= ? ";
		$param[] = str_replace(',', '', $_POST['smartFilter']['cim_nett_amt_to'])*1;
	}

	$common = "
		FROM
			".DB2.".student A,
			".DB2.".stud_sponsor B,
			".DB2.".sponsor C,
			".DB2.".cust_invoice_master cim,
			".DB2.".cust_invoice_details cid,
			".DB2.".cust_invoice_item cii,
			".DB2.".stud_sponsor_period_cover D
		WHERE
			A.std_student_id = B.std_student_id
			AND C.spn_sponsor_code = B.spn_sponsor_code
			AND cim.cim_cust_id = A.std_student_id
			AND cim.cim_status = 'APPROVE'
			AND cim.cim_cust_invoice_id = cid.cim_cust_invoice_id
			AND (IFNULL(cid_total_amt, 0) - IFNULL(cid_sponsor_amt, 0)) > 0
			AND cid_transaction_type = 'CR'
			AND cii.cii_item_code = cid.cii_item_code
			AND cii.cii_item_category = cid.cii_item_category
			AND D.ssp_id = B.ssp_id
			AND NOT EXISTS (SELECT 1 FROM ".DB2.".sponsor_invoice_details sid WHERE sid.cim_invoice_no = cim.cim_invoice_no)
			AND NOW() >= D.spc_date_from
			AND (NOW() <= IFNULL(D.spc_date_to, NOW()) OR D.spc_sems_to >= '$p_current_sem')
			$filter
	";

	$groupby = "GROUP BY A.std_student_id, A.std_student_name, A.std_status, C.spn_sponsor_code, D.spc_date_from, D.spc_date_to, cim.cim_cust_invoice_id";

	$outstanding_amt_grand = $cim_nett_amt_grand = 0;
	$sql = "
		SELECT
			cim.cim_cust_invoice_id,
			SUM(IFNULL(cid_bal_amt, 0)) outstanding_amt,
			(
				SELECT ssa.ssa_sponsor_amt
				FROM ".DB2.".stud_sponsor_amount ssa
				WHERE
					ssa.spn_sponsor_code = C.spn_sponsor_code
					AND ssa.cim_cust_id = A.std_student_id
					AND ssa.cim_invoice_no = cim.cim_invoice_no
				ORDER BY ssa.createddate DESC
				LIMIT 1
			) cim_nett_amt
		$common
		$groupby
	";
	$data = executeQueryV2($sql, $param);
	$recordsFiltered = count($data);
	foreach($data as $d) {
		$checkboxMonitoring[] = $d['cim_cust_invoice_id'];
		$checkboxMonitoring_outstanding_amt[$d['cim_cust_invoice_id']] = $d['cim_nett_amt']*1;
		$outstanding_amt_grand += $d['outstanding_amt'];
		$cim_nett_amt_grand += $d['cim_nett_amt'];
	}

	if($_GET['listing']!='download') {
		$limit = "LIMIT {$_POST['start']}, {$_POST['length']}";
	}

	//============================================================ main sql
	$sql = "
		SELECT
			A.std_student_id,
			A.std_student_name,
			B.ssp_limit_bal,

			cim.cim_invoice_no,

			CONCAT_WS(' - ', A.std_status, IFNULL(A.std_extended_field->>'$.std_status_desc', ' ')) std_status_desc,
			#IF(A.std_status=1, 'ACTIVE', 'INACTIVE') std_status_desc,

			(
				SELECT ssa.ssa_sponsor_amt
				FROM ".DB2.".stud_sponsor_amount ssa
				WHERE
					ssa.spn_sponsor_code=C.spn_sponsor_code
					and ssa.cim_cust_id=A.std_student_id
					and ssa.cim_invoice_no=cim.cim_invoice_no
				ORDER BY ssa.createddate DESC
				LIMIT 1
			) cim_nett_amt,
			#SUM(IFNULL(cid_sponsor_amt, 0)) cim_nett_amt,

			SUM(IFNULL(cid_bal_amt, 0)) outstanding_amt,
			CONCAT_WS(' - ', C.spn_sponsor_code, C.spn_sponsor_name) spn_sponsor_name,
			DATE_FORMAT(D.spc_date_from, '%d/%m/%Y') spc_date_from,
			DATE_FORMAT(D.spc_date_to, '%d/%m/%Y') spc_date_to,
			cim.cim_cust_invoice_id,

			IF(C.spn_status_invoice_cd = '2', 1, 0) is_journal
		$common
		$groupby
		ORDER BY {$_POST['orderBy'][0]} {$_POST['orderBy'][1]}, cim_cust_invoice_id
		$limit
	";
	$data = executeQueryV2($sql, $param);
	foreach($data as &$d) {
		$d['ssp_limit_bal'] *= 1;
		$d['cim_nett_amt'] *= 1;
		$d['outstanding_amt'] *= 1;
		$d['is_journal'] *= 1;
		
		if($_GET['listing']!='download') $d['view'] = 'index.php?a='.flc_url_encode("page=page_wrapper&menuID=1062&type=VIEW&id=".$d['cim_cust_invoice_id']);

		if(!$d['cim_nett_amt']) {
			$has_knockoff = executeQueryV2("SELECT 1 FROM ".DB2.".cust_invoice_details WHERE cim_cust_invoice_id = ? AND cid_sponsor_amt IS NOT NULL", [$d['cim_cust_invoice_id']]);
			$d['tell_me_why'] = $has_knockoff?"Invoice already knockoff":"Please check Fee Cover";
		}
	}

	//============================================================ finally
	if($_GET['listing']=='download') {
		generateCSV([
			"filename" => 'List of Sponsored Students with Outstanding Amount to Sponsor - '.$_GET['spn_sponsor_code'].', '.$_GET['std_program'].', '.$_GET['cim_semester_id'],
			"header" => ["Sponsor" => $_POST['spn_sponsor_desc'], "Program Level" => $_POST['std_program_desc'], "Semester" => $_POST['cim_semester_id_desc']],
			"index" => true,
			"content" => $data
		]);
	}
	else {
		return [
			'draw' => $_POST['draw'],
			'recordsFiltered' => $recordsFiltered,
			'totalStudent' => executeQueryV2("SELECT COUNT(DISTINCT A.std_student_id) C $common", $param)[0]['C'],
			'checkboxMonitoring' => $checkboxMonitoring,
			'checkboxMonitoring_outstanding_amt' => $checkboxMonitoring_outstanding_amt,
			'data' => $data,
			'footer' => [
				'outstanding_amt' => $outstanding_amt_grand,
				'cim_nett_amt' => $cim_nett_amt_grand
			]
		];
	}
}

if($_GET['submit_no_wait']) {
	$param = [
		$_POST['std_program'],
		$_POST['cim_semester_id'],
		$_POST['spn_sponsor_code'],
		'Sponsor Invoice',
		$_POST['username'],
		$_POST['spo_id'],
		json_encode(array_keys($_POST['checked']))
	];

	executeQueryV2("CALL ".DB2.".create_invoice_sponsor(?, ?, ?, ?, ?, ?, ?, @p_flag, @p_msg, @p_errorMessage, @p_dataJson)", $param);
}

if($_GET['generateInvoice']) {
	$spo_id = 'create_invoice_sponsor_'.uniqid($_USER['USERNAME']);

	expressPost("api/V2_SFSI_API?submit_no_wait=1", [
		"std_program"=> $_POST['std_program'],
		"cim_semester_id"=> $_POST['cim_semester_id'],
		"spn_sponsor_code"=> $_POST['spn_sponsor_code'],
		"checked"=> $_POST['checked'],
		"username" => $_USER['USERNAME'],
		"spo_id" => $spo_id,
	], $nowait = true);

	$limit = 21;
	while(!$dataJson && $limit>1) {
		$sp_out = executeQueryV2("SELECT spo_out1, spo_out2, spo_out4 FROM ".DB2.".sp_out WHERE spo_id = '$spo_id'")[0];
		$p_flag = $sp_out['spo_out1'];
		$p_msg = $sp_out['spo_out2'];
		$dataJson = json_decode($sp_out['spo_out4'], true);

		if(!$dataJson) {
			sleep(1);
			$limit--;
		}
	}

	// file_put_contents(__DIR__ . "/invoice_generation.log", "is_journal: ".(!!$_POST['is_journal']?'true':'false').PHP_EOL.json_encode($dataJson, JSON_PRETTY_PRINT));

	// if(!$_POST['is_journal'] && $dataJson) {
	// 	foreach($dataJson['taskListing'][0] as $task) {
	// 		$batch = executeQueryV2("SELECT wtk_application_id FROM ".DB2.".wf_task WHERE wtk_task_id='".$task['taskId']."'")[0]['wtk_application_id'];
	// 		executeQueryV2("UPDATE ".DB2.".wf_task SET wtk_task_url = ? WHERE wtk_task_id = ".$task['taskId']."", ["index.php?a=".flc_url_encode("page=page_wrapper&menuID=1536&taskId=".$task['taskId']."&batch=$batch")]);
	// 	}

	/* 20230125 Masri dah buat kat SP create_invoice_sponsor
	$rs = executeQueryV2("
		SELECT wtk_task_id, wtk_application_id
		FROM ".DB2.".wf_task
		WHERE
			wtk_workflow_code = 'INVOICE_SPON_BATCH'
			AND wtk_task_url = 'http://fims'
	");
	foreach($rs as $task) {
		executeQueryV2("UPDATE ".DB2.".wf_task SET wtk_task_url = ? WHERE wtk_task_id = ".$task['wtk_task_id'], ["index.php?a=".flc_url_encode("page=page_wrapper&menuID=1536&taskId=".$task['wtk_task_id']."&batch=".$task['wtk_application_id'])]);
	}
	*/

	if($p_flag!='1' && $limit) {
		file_put_contents(__DIR__ . "/log/aizal/V2_SFSI_API - generateInvoice.log", '================================================================== '.date("Y/m/d H:i:s"). PHP_EOL, FILE_APPEND);
		file_put_contents(__DIR__ . "/log/aizal/V2_SFSI_API - generateInvoice.log", '$spo_id  : '. $spo_id . PHP_EOL, FILE_APPEND);
		file_put_contents(__DIR__ . "/log/aizal/V2_SFSI_API - generateInvoice.log", '$p_flag  : '. $p_flag . PHP_EOL, FILE_APPEND);
		file_put_contents(__DIR__ . "/log/aizal/V2_SFSI_API - generateInvoice.log", '$p_msg   : '. $p_msg . PHP_EOL, FILE_APPEND);
		file_put_contents(__DIR__ . "/log/aizal/V2_SFSI_API - generateInvoice.log", '$dataJson: '. json_encode($dataJson, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
		file_put_contents(__DIR__ . "/log/aizal/V2_SFSI_API - generateInvoice.log", 'by       : '. $_USER['USERNAME'] . PHP_EOL, FILE_APPEND);
		file_put_contents(__DIR__ . "/log/aizal/V2_SFSI_API - generateInvoice.log", PHP_EOL, FILE_APPEND);
	}

	return [
		"status" => $p_flag=='1'?"ok":"ko",
		"p_flag" => $p_flag,
		"p_msg" => $p_msg,
		"sp_out" => $sp_out,
		"dataJson" => $dataJson
	];
}