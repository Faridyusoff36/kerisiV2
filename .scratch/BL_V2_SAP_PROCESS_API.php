<?php
include 'userinfo.php';

if($_GET['details']) {

	$data = executeQueryV2("
		SELECT
			CONCAT_WS(' - ', dpm.vcs_vendor_code, dpm.dpm_vendor_name) vcs_vendor,
			dpm.dpm_deposit_no,
			FORMAT(SUM(
				CASE
					WHEN ddt.ddt_type = 'CR' THEN ddt.ddt_amt 
					WHEN ddt.ddt_type = 'DT' THEN -ddt.ddt_amt 
					ELSE 0
				END
			), 2) advance_amount
		FROM
			".DB2.".deposit_master dpm,
			".DB2.".deposit_details ddt,
			".DB2.".lookup_parameter_main lpm
		WHERE
			dpm.dpm_deposit_master_id = ddt.dpm_deposit_master_id
			AND ddt.acm_acct_code = lpm.lpm_value
			AND lpm_code = 'ACCT_CODE_DT_INV_SPON'
			AND dpm.dpm_status IN ('APPROVE', '1')
			AND dpm.vcs_vendor_code = ?
			AND dpm.dpm_deposit_no = ?
		GROUP BY 1, 2, dpm.vcs_vendor_code, dpm.dpm_vendor_name
	", [$_POST['vcs_vendor_code'], $_POST['dpm_deposit_no']])[0];

	return $data;
}

else if($_GET['dt_listing']) {
	$rs = executeQueryV2("
		-- ========================================================================= student invoice
		(
			SELECT DISTINCT
				1 `union`,
				cim.cim_invoice_no,
				cim.cim_cust_invoice_id,
				cim.cim_cust_id,
				cim.cim_cust_name,
				cim.cim_invoice_date,
				cim.cim_semester_id,
				cim.cim_pending_sponsor_amt,
				cim.cim_bal_amt
			FROM
				".DB2.".student std 
				INNER JOIN ".DB2.".stud_sponsor spn ON (std.std_student_id = spn.std_student_id)
				INNER JOIN ".DB2.".sponsor sp ON (spn.spn_sponsor_code = sp.spn_sponsor_code)
				INNER JOIN ".DB2.".cust_invoice_master cim ON (cim.cim_cust_id = std.std_student_id)
			WHERE
				sp.spn_sponsor_code = ?
				AND cim.cim_bal_amt > 0
				AND cim.cim_status = 'APPROVE'
				AND (cim_system_id IS NULL OR cim_system_id = 'STUD_INV')
				-- LIMIT 1
		)

		-- ========================================================================= sponsor invoice
		UNION
		(
			SELECT
				2 `union`,
				cim.cim_invoice_no,
				cim.cim_cust_invoice_id,
				cim.cim_our_ref cim_cust_id,
				cim.cim_cust_name,
				cim.cim_invoice_date,
				cim.cim_semester_id,
				cim.cim_pending_sponsor_amt,
				cim.cim_bal_amt
			FROM ".DB2.".cust_invoice_master cim
			WHERE
				cim.cim_status = 'APPROVE'
				AND cim.cim_bal_amt > 0
				AND cim.cim_status = 'APPROVE'
				AND (cim_system_id IS NULL OR cim_system_id = 'SF_SPON_INV')
				AND cim.cim_cust_id = ?
				-- LIMIT 1
		)
	", [$_POST['vcs_vendor_code'], $_POST['vcs_vendor_code']]);

	foreach($rs as &$r) {
		$r['cim_pending_sponsor_amt'] *= 1;
		$r['cim_bal_amt'] *= 1;
		$r['view'] = 'index.php?a='.flc_url_encode("page=page_wrapper&menuID=1062&mode=VIEW&id=".$r['cim_cust_invoice_id']);
	}

	return $rs;
}

else if($_GET['process']) {

	//=============================================== manual_journal_master
	$master = [
		"mjm_journal_id" => getSeqNo("manual_journal_master"),
		"mjm_total_amt" => $_POST['total'],
		"mjm_journal_no" => getRefNo("MANUAL_JOURNAL_ADV"),
		"mjm_journal_desc" => "Sponsor ({$_POST['vcs_vendor_code']}) Advance Payment Distribution to Student",
		"mjm_typeofjournal" => "General",
		"mjm_system_id" => "MNL_ADVANCE",
		"mjm_enterdate" => "NOW()",
		"mjm_enterby" => $_USER['USERNAME'],
		"mjm_status" => "ENTRY",
		"org_code" => executeQueryV2("SELECT org_code FROM ".DB2.".organization WHERE org_status = 1 LIMIT 1")[0]['org_code'],
		"dpm_deposit_no" => $_POST['dpm_deposit_no'],
		"advance_amount" => $_POST['advance_amount']
	];
	expressDML([
		'table' => 'manual_journal_master',
		'data' => $master,
	]);

	$shared = "
		cim.cim_cust_id,
		cim.cim_cust_name,
		cid.cim_cust_invoice_id,
		cid.cii_item_category,
		cid.cii_item_code,
		cid.oun_code,
		REPLACE(cid.cid_extended_field->>'$.oun_desc', CONCAT(cid.oun_code, ' - '), '') oun_desc,
		cid.fty_fund_type,
		REPLACE(cid.cid_extended_field->>'$.fty_fund_type_desc', CONCAT(cid.fty_fund_type, ' - '), '') fty_fund_type_desc,
		cid.at_activity_code,
		cid.ccr_costcentre,
		REPLACE(cid.cid_extended_field->>'$.ccr_costcentre_charged_desc', CONCAT(cid.ccr_costcentre, ' - '), '') ccr_costcentre_charged_desc,
		cid.acm_acct_code,
		REPLACE(cid.cid_extended_field->>'$.acm_acct_desc', CONCAT(cid.acm_acct_code, ' - '), '') acm_acct_desc,
		cid.cpa_project_no,
		cid.cid_cust_invoice_detl_id
	";

	foreach($_POST['to_pay_amt'] as $key=>$value) {
		//=============================================== update cust_invoice_master
		executeQueryV2("UPDATE ".DB2.".cust_invoice_master SET cim_pending_sponsor_amt = ? WHERE cim_cust_invoice_id = ?", [$value, $key]);
		$cim_cust_type = executeQueryV2("SELECT cim_cust_type FROM ".DB2.".cust_invoice_master WHERE cim_cust_invoice_id = ?", [$key])[0]['cim_cust_type'];

		//=============================================== check priority
		if($cim_cust_type=='E') {
			$sql = "
				SELECT
					cim.cim_invoice_no,
					cid.cid_bal_amt sponsor_amt,
					$shared
				FROM
					".DB2.".cust_invoice_master cim,
					".DB2.".cust_invoice_details cid,
					".DB2.".lookup_details lov
				WHERE
					cim.cim_cust_invoice_id = cid.cim_cust_invoice_id
					AND cid.cii_item_category = lov.lde_value
					AND lov.lma_code_name = 'FCATEGORY'
					AND cid.cim_cust_invoice_id = ?
					AND cid.cid_transaction_type = 'DT'
				ORDER BY cid.cid_transaction_type, lov.lde_sorting, cid.cid_cust_invoice_detl_id
			";
		}
		else {
			$sql = "
				SELECT
					cim.cim_invoice_no,
					cid.cid_bal_amt sponsor_amt,
					$shared
				FROM
					".DB2.".cust_invoice_master cim,
					".DB2.".cust_invoice_details cid,
					".DB2.".lookup_details lov
				WHERE
					cim.cim_cust_invoice_id = cid.cim_cust_invoice_id
					AND cid.cii_item_category = lov.lde_value
					AND lov.lma_code_name = 'FCATEGORY'
					AND cid.cim_cust_invoice_id = ?
					AND cid.cid_transaction_type = 'DT'
				ORDER BY cid.cid_transaction_type, lov.lde_sorting, cid.cid_cust_invoice_detl_id
			";
		}
		$items_dt = executeQueryV2($sql, [$key]);
		$debit_mjd_trans_amt = 0;
		
		foreach($items_dt as &$dt) {

			//=============================================== get for CR based on DT data
			$cr = executeQueryV2("
				SELECT
					? cim_invoice_no,
					? sponsor_amt,
					$shared
				FROM
					".DB2.".cust_invoice_master cim,
					".DB2.".cust_invoice_details cid
				WHERE
					cim.cim_cust_invoice_id = cid.cim_cust_invoice_id
					AND cid.cid_transaction_type = 'CR'
					AND cid.cim_cust_invoice_id = ?
					AND cid.cii_item_category = ?
					AND cid.cii_item_code = ?
					AND cid.fty_fund_type = ?
					AND cid.at_activity_code = ?
					AND cid.oun_code = ?
					AND cid.ccr_costcentre = ?
			", [
				$dt['cim_invoice_no'],
				$dt['sponsor_amt'],
				$dt['cim_cust_invoice_id'],
				$dt['cii_item_category'],
				$dt['cii_item_code'],
				$dt['fty_fund_type'],
				$dt['at_activity_code'],
				$dt['oun_code'],
				$dt['ccr_costcentre']				
			])[0];

			//=============================================== manual_journal_details
			$amount = $cr['sponsor_amt']*1;
			if($value<$amount) $amount = $value;
			$value -= $amount;

			if($amount) {
				$debit_mjd_trans_amt += $amount;

				//=============================================== get invoice CR as journal DT
				// Means katakan kat bahagian credit ada 12 row, kat debit dlm database cuma ada 1 row? Bukan sekadar pada skrin jadi 1 row sedangkan kat DB remain sama byk mcm credit?

				// expressDML([
				// 	'table' => 'manual_journal_details',
				// 	'data' => [
				// 		"mjd_journal_detl_id" => getSeqNo("manual_journal_details"),
				// 		"mjm_journal_no" => $master['mjm_journal_id'],
				// 		"mjd_reference" => $cr['cid_cust_invoice_detl_id'],
				// 		"oun_code" => $cr['oun_code'],
				// 		"oun_desc" => $cr['oun_desc'],
				// 		"fty_fund_type" => $cr['fty_fund_type'],
				// 		"ft_fund_desc" => $cr['fty_fund_type_desc'],
				// 		"at_activity_code" => $cr['at_activity_code'],
				// 		"ccr_costcentre" => $cr['ccr_costcentre'],
				// 		"ccr_costcentre_desc" => $cr['ccr_costcentre_charged_desc'],
				// 		"acm_acct_code" => $cr['acm_acct_code'],
				// 		"acm_acct_desc" => $cr['acm_acct_desc'],
				// 		"cpa_project_no" => $cr['cpa_project_no'],
				// 		"mjd_document_no" => $cr['cim_invoice_no'],
				// 		"mjd_item_lineno" => $cr['cid_invoice_line_no'],
				// 		"mjd_trans_type" => 'DT',
				// 		"mjd_trans_amt" => $amount,
				// 		"mjd_trans_date" => "NOW()",
				// 		"mjd_status" => 'ENTRY',
				// 		"code_so" => $cr['cpa_project_no'],
				// 		"mjd_payto_type" => 'E',
				// 		"mjd_payto_id" => $_POST['vcs_vendor_code'],
				// 		"mjd_payto_name" => $_POST['vcs_vendor_name']
				// 	]
				// ]);

				//=============================================== get invoice DT as journal CR
				expressDML([
					'table' => 'manual_journal_details',
					'data' => [
						"mjd_journal_detl_id" => getSeqNo("manual_journal_details"),
						"mjm_journal_no" => $master['mjm_journal_id'],
						"mjd_reference" => $dt['cid_cust_invoice_detl_id'],
						"oun_code" => $dt['oun_code'],
						"oun_desc" => $dt['oun_desc'],
						"fty_fund_type" => $dt['fty_fund_type'],
						"at_activity_code" => $dt['at_activity_code'],
						"ft_fund_desc" => $dt['fty_fund_type_desc'],
						"ccr_costcentre" => $dt['ccr_costcentre'],
						"ccr_costcentre_desc" => $dt['ccr_costcentre_charged_desc'],
						"acm_acct_code" => $dt['acm_acct_code'],
						"cpa_project_no" => $dt['cpa_project_no'],
						"mjd_document_no" => $dt['cim_invoice_no'],
						"mjd_item_lineno" => $dt['cid_invoice_line_no'],
						"mjd_trans_type" => 'CR',
						"mjd_trans_amt" => $amount,
						"mjd_trans_date" => "NOW()",
						"acm_acct_desc" => $dt['acm_acct_desc'],
						"mjd_status" => 'ENTRY',
						"code_so" => $dt['cpa_project_no'],
						"mjd_payto_type" => $cim_cust_type,
						"mjd_payto_id" => $dt['cim_cust_id'],
						"mjd_payto_name" => $dt['cim_cust_name'],
					]
				]);
			}
		}

		//=============================================== journal DT is sum of CR
		$nsd = executeQueryV2("
			SELECT
				fty_fund_type,
				(SELECT fty_fund_desc FROM ".DB2.".fund_type fty WHERE fty.fty_fund_type = nsd.fty_fund_type LIMIT 1) fty_fund_type_desc,
				at_activity_code,
				oun_code_ptj oun_code,
				(SELECT oun_desc FROM ".DB2.".organization_unit oun WHERE oun.oun_code = nsd.oun_code_ptj LIMIT 1) oun_desc,
				ccr_costcentre_charged ccr_costcentre,
				(SELECT ccr_costcentre_desc FROM ".DB2.".costcentre ccr WHERE ccr.ccr_costcentre = nsd.ccr_costcentre_charged LIMIT 1) ccr_costcentre_desc,
				(SELECT CONCAT(lpm_value, ' - ', lpm_value_desc)  FROM ".DB2.".lookup_parameter_main WHERE lpm_code = 'ACCT_CODE_DT_INV_SPON') acm_acct_code,
				cpa_project_no,
				(SELECT mjd_document_no FROM ".DB2.".manual_journal_details mjd WHERE mjd.mjm_journal_no = mjm.mjm_journal_id LIMIT 1) mjd_document_no,
				(SELECT SUM(mjd_trans_amt) FROM ".DB2.".manual_journal_details mjd WHERE mjd_trans_type = 'CR' AND mjd.mjm_journal_no = mjm.mjm_journal_id) mjd_trans_amt
			FROM
				".DB2.".noninv_struct_details nsd,
				".DB2.".manual_journal_master mjm
			WHERE
				nsm_id = 0
				AND nsd_trans_type = 'DT'
				AND mjm.mjm_journal_id = ?
		", [$master['mjm_journal_id']])[0];

		expressDML([
			'table' => 'manual_journal_details',
			'data' => [
				"mjd_journal_detl_id" => getSeqNo("manual_journal_details"),
				"mjm_journal_no" => $master['mjm_journal_id'],
				// "mjd_reference" => $cr['cid_cust_invoice_detl_id'],
				"oun_code" => $nsd['oun_code'],
				"oun_desc" => $nsd['oun_desc'],
				"fty_fund_type" => $nsd['fty_fund_type'],
				"ft_fund_desc" => $nsd['fty_fund_type_desc'],
				"at_activity_code" => $nsd['at_activity_code'],
				"ccr_costcentre" => $nsd['ccr_costcentre'],
				"ccr_costcentre_desc" => $nsd['ccr_costcentre_desc'],
				"acm_acct_code" => explode(' - ', $nsd['acm_acct_code'])[0],
				"acm_acct_desc" => explode(' - ', $nsd['acm_acct_code'])[1],
				"cpa_project_no" => $nsd['cpa_project_no'],
				"mjd_document_no" => $cr['cim_invoice_no'],
				// "mjd_item_lineno" => $cr['cid_invoice_line_no'],
				"mjd_trans_type" => 'DT',
				"mjd_trans_amt" => $debit_mjd_trans_amt,
				"mjd_trans_date" => "NOW()",
				"mjd_status" => 'ENTRY',
				"code_so" => $nsd['cpa_project_no'],
				"mjd_payto_type" => 'E',
				"mjd_payto_id" => $_POST['vcs_vendor_code'],
				"mjd_payto_name" => $_POST['vcs_vendor_name']
			]
		]);

	}

	//=============================================== workflow. Name: ADVANCE_PAYMENT, Role: JOURNAL APPROVER (eg naimah)
	executeQueryV2("CALL ".DB2.".workflowSubmit(?,?,?,?, @OUT)", [
		'ADVANCE_PAYMENT',
		$master['mjm_journal_id'],
		$_USER['USERNAME'],
		json_encode([
			"sponsor" 			=> $_POST['vcs_vendor_code'].' - '.$_POST['vcs_vendor_name'],
			"total" 			=> number_format($_POST['total'], 2),
			"journal"  			=> $master['mjm_journal_no'],
			"amount_to_process"	=> $_POST['total'],
			"unit"				=> "UNIT_STUD_FINANCE",
			"wtk_task_url" 		=> "TMP"
		])
	]);
	$out = json_decode(executeQueryV2("SELECT @OUT")[0]['@OUT'], true);

	//=============================================== update wf_task
	$rs = executeQueryV2("
		SELECT wtk_task_id, wtk_application_id
		FROM ".DB2.".wf_task
		WHERE
			wtk_workflow_code = 'ADVANCE_PAYMENT'
			AND wtk_task_url = 'TMP'
	");
	foreach($rs as $task) {
		executeQueryV2("UPDATE ".DB2.".wf_task SET wtk_task_url = ? WHERE wtk_task_id = ".$task['wtk_task_id'], ["index.php?a=".flc_url_encode("page=page_wrapper&menuID=2049&taskId=".$task['wtk_task_id']."&wtk_application_id=".$task['wtk_application_id'])]);
	}

	return [
		'status' => 'ok',
		'mjm_journal_no' => $master['mjm_journal_no'],
		'out' => $out
	];
}

else if($_GET['goto_receipt']) {
	$rma_receipt_master_id = executeQueryV2("SELECT rma_receipt_master_id FROM ".DB2.".receipt_master WHERE rma_receipt_no = ?", [$_POST['rma_receipt_no']])[0]['rma_receipt_master_id'];
	return ["url" => 'index.php?a='.flc_url_encode("page=page_wrapper&menuID=1598&mode=view&rma_receipt_master_id=".$rma_receipt_master_id)];
}