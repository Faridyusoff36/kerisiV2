<?php
include 'userinfo.php';

if($_GET['details']) {
	$data = executeQueryV2("
		SELECT
			mjm_journal_no,
			mjm_total_amt,
			mjm_journal_desc,
			mjm_typeofjournal,
			mjm_status,
			DATE_FORMAT(mjm_enterdate, '%d/%m/%Y') mjm_enterdate,
			dpm_deposit_no,
			mjm_extended_field->>'$.advance_amount' advance_amount
		FROM
			".DB2.".manual_journal_master
		WHERE
			mjm_journal_id = ?
	", [$_POST['mjm_journal_id']])[0];

	$workflow = executeQueryV2("CALL ".DB2.".workflowGetDetails(?, ?, @OUT)", [
		$_POST['wtk_task_id'],
		$_USER['USERNAME']
	]);
	$workflow = json_decode($workflow[0]['dataJson'], true);

	return [
		"details" => $data,
		"workflow" => $workflow,
	];
}

else if($_GET['dt_listing']) {
	$data = executeQueryV2("
		SELECT
			mjd_journal_detl_id,
			oun_code,
			oun_desc,
			fty_fund_type,
			ft_fund_desc,
			at_activity_code,
			ccr_costcentre,
			ccr_costcentre_desc,
			acm_acct_code,
			acm_acct_desc,
			code_so,
			cpa_project_no,
			mjd_document_no, -- cim_invoice_no
			mjd_trans_amt,
			CONCAT(mjd_payto_name, ' (', mjd_payto_id, ')') cim_cust
			-- (SELECT CONCAT(cim_cust_name, ' (', cim_cust_id, ')') FROM ".DB2.".cust_invoice_master cim WHERE cim.cim_invoice_no = mjd_document_no) cim_cust
		FROM ".DB2.".manual_journal_details
		WHERE
			mjd_trans_type = '".($_POST['type']=='dt_credit'?'CR':'DT')."'
			AND mjm_journal_no = ?
	", [$_POST['mjm_journal_id']]);
	
	// else {
		// $data = executeQueryV2("
		// 	SELECT
		// 		fty_fund_type,
		// 		at_activity_code,
		// 		oun_code_ptj oun_code,
		// 		(SELECT oun_desc FROM ".DB2.".org_unit_costcentre WHERE oun_code = oun_code_ptj LIMIT 1) oun_desc,
		// 		ccr_costcentre_charged ccr_costcentre,
		// 		(SELECT lpm_value FROM ".DB2.".lookup_parameter_main WHERE lpm_code = 'ACCT_CODE_DT_INV_SPON') acm_acct_code,
		// 		cpa_project_no,
		// 		(SELECT mjd_document_no FROM ".DB2.".manual_journal_details mjd WHERE mjd.mjm_journal_no = mjm.mjm_journal_id LIMIT 1) mjd_document_no,
		// 		(SELECT SUM(mjd_trans_amt) FROM ".DB2.".manual_journal_details mjd WHERE mjd_trans_type = 'CR' AND mjd.mjm_journal_no = mjm.mjm_journal_id) mjd_trans_amt
		// 	FROM
		// 		".DB2.".noninv_struct_details nsd,
		// 		".DB2.".manual_journal_master mjm
		// 	WHERE
		// 		nsm_id = 0
		// 		AND nsd_trans_type = 'DT'
		// 		AND mjm.mjm_journal_id = ?
		// ", [$_POST['mjm_journal_id']]);

		// foreach($data as &$d) {
		// 	$d['acm_acct_desc'] = executeQueryV2("SELECT acm_acct_desc d FROM ".DB2.".account_main WHERE acm_acct_code = ?", [$d['acm_acct_code']])[0]['d'];
		// 	$d['ccr_costcentre_desc'] = executeQueryV2("SELECT ccr_costcentre_desc d FROM ".DB2.".org_unit_costcentre WHERE ccr_costcentre = ? LIMIT 1", [$d['ccr_costcentre']])[0]['d'];
		// 	$d['ft_fund_desc'] = executeQueryV2("SELECT ft_fund_desc d FROM ".DB2.".org_unit_costcentre WHERE fty_fund_type = ? LIMIT 1", [$d['fty_fund_type']])[0]['d'];
		// }
	// }

	return $data;
}

else if($_GET['approve']) {

	//============================================== update manual_journal_details
	foreach($_POST['data'] as $data) {
		expressDML([
			'table' => 'manual_journal_details',
			'data' => $data
		]);
	}

	//============================================== update cust_invoice_master
	executeQueryV2("
		UPDATE ".DB2.".cust_invoice_master
		SET cim_pending_sponsor_amt = NULL
		WHERE cim_invoice_no IN (
			SELECT DISTINCT mjd_document_no
			FROM ".DB2.".manual_journal_details
			WHERE mjm_journal_no = ?
		)
	", [$_POST['mjm_journal_id']]);

	//============================================== workflow
	$vendor =  executeQueryV2("
		SELECT concat_ws(' - ',mjd_payto_id,mjd_payto_name) S
		FROM ".DB2.".manual_journal_details mjd
		WHERE mjd_trans_type  = 'DT' AND mjd.mjm_journal_no = ?
		LIMIT 1
	",  [$_POST['mjm_journal_id']])[0]['S'];

	$param = [
			"sponsor"  			=> $vendor,
			"total"    			=> number_format($_POST['totalAmt'], 2),
			"journal"  			=> $_POST['journalNo'],
			"amount_to_process"	=> $_POST['totalAmt'],
			"unit"				=> "UNIT_STUD_FINANCE",
			"encodeURL"			=> "Y",
			"wtk_task_url" 		=> "page=page_wrapper&menuID=2049&wtk_application_id=".$_POST['mjm_journal_id']."&taskId="
	];

	$workflow = executeQueryV2("CALL ".DB2.".workflowUpdate(?,?,?,?,?, @OUT)", [
		$_POST['wtk_task_id'],
		$_POST['flow']['flowStatus'],
		$_POST['flow']['flowRemarks'],
		$_USER['USERNAME'],
		json_encode($param)
	]);
	
	$workflow = json_decode($workflow[0]['dataJson'], true);

	return [
		"workflow" => $workflow,
	];
}