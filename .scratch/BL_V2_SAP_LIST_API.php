<?php
include 'userinfo.php';

if($_GET['dt_listing'] || $_GET['download']) {

	$advance_amount = "
		SUM(
			CASE
				WHEN ddt.ddt_type = 'CR' THEN ddt.ddt_amt 
				WHEN ddt.ddt_type = 'DT' THEN -ddt.ddt_amt 
				ELSE 0
			END
		)
	";
	$invoice_balance = "
		(
			(SELECT IFNULL(SUM(ssa.ssa_sponsor_amt), 0)
			FROM ".DB2.".stud_sponsor_amount ssa
			WHERE ssa.spn_sponsor_code = dpm.vcs_vendor_code) +
			(SELECT IFNULL(SUM(cim.cim_bal_amt) ,0) FROM ".DB2.".cust_invoice_master cim WHERE cim.cim_cust_id=dpm.vcs_vendor_code AND cim_status='APPROVE' AND cim_bal_amt > 0 )
		)
	";

	//============================================================ common param
	if($_POST['search']['value']) {
		$filter .= "
			AND CONCAT_WS('__',
				dpm.vcs_vendor_code,
				dpm.dpm_vendor_name
			) LIKE CONCAT('%', ?, '%')
		";
		$param[] = $_POST['search']['value'];
	}
	if($_POST['smartFilter']['vcs_vendor_code']) {
		$filter .= " AND dpm.vcs_vendor_code = ?";
		$param[] = $_POST['smartFilter']['vcs_vendor_code'];
	}
	if($_POST['smartFilter']['dpm_vendor_name']) {
		$filter .= " AND dpm.dpm_vendor_name LIKE CONCAT('%', ?, '%')";
		$param[] = $_POST['smartFilter']['dpm_vendor_name'];
	}
	if($_POST['smartFilter']['advance_amount_from']!='') {
		$having .= " AND advance_amount >= ?";
		$havingParam[] = str_replace(',', '', $_POST['smartFilter']['advance_amount_from'])*1;
	}
	if($_POST['smartFilter']['advance_amount_to']!='') {
		$having .= " AND advance_amount <= ?";
		$havingParam[] = str_replace(',', '', $_POST['smartFilter']['advance_amount_from'])*1;
	}
	if($_POST['smartFilter']['invoice_balance_from']!='') {
		$having .= " AND invoice_balance >= ?";
		$havingParam[] = str_replace(',', '', $_POST['smartFilter']['invoice_balance_from'])*1;
	}
	if($_POST['smartFilter']['invoice_balance_to']!='') {
		$having .= " AND invoice_balance <= ?";
		$havingParam[] = str_replace(',', '', $_POST['smartFilter']['invoice_balance_from'])*1;
	}
	$param = [...($param?:[]), ...($havingParam?:[])];

	$sql = "
		SELECT
			null `No`,
			dpm.vcs_vendor_code /*`Sponsor No.`*/,
			dpm.dpm_vendor_name /*`Sponsor Name`*/,
			dpm.dpm_deposit_no,
			$advance_amount advance_amount /*`FORMAT_CURRENCY::Adv. Amt.`*/,
			$invoice_balance invoice_balance /*`FORMAT_CURRENCY::Inv. Bal.`*/
		FROM
			".DB2.".deposit_master dpm,
			".DB2.".deposit_details ddt,
			".DB2.".lookup_parameter_main lpm
		WHERE
			dpm.dpm_deposit_master_id = ddt.dpm_deposit_master_id
			AND ddt.acm_acct_code = lpm.lpm_value
			AND lpm_code = 'ACCT_CODE_DT_INV_SPON' AND dpm.dpm_payto_type='E'
			AND dpm.dpm_status IN ('APPROVE', '1')
			$filter
		GROUP BY 1, dpm.vcs_vendor_code, dpm.dpm_vendor_name, dpm.dpm_deposit_no
		HAVING
			$advance_amount > 0
			$having
	";

	//============================================================ datatable
	if($_GET['dt_listing']) {
		$data = executeQueryV2("
			$sql
			ORDER BY {$_POST['orderBy'][0]} {$_POST['orderBy'][1]}
			LIMIT {$_POST['start']}, {$_POST['length']}
		", $param);
		foreach($data as &$d) $d['process'] = 'index.php?a='.flc_url_encode("page=page_wrapper&menuID=2021&vcs_vendor_code=".$d['vcs_vendor_code']."&dpm_deposit_no=".$d['dpm_deposit_no']);

		//ni maz tambah utk pergi ke page transfer to student
		foreach($data as &$d) $d['transfer'] = 'index.php?a='.flc_url_encode("page=page_wrapper&menuID=2354&vcs_vendor_code=".$d['vcs_vendor_code']."&dpm_deposit_no=".$d['dpm_deposit_no']);

		return [
			'draw' => $_POST['draw'],
			'recordsFiltered' => count(executeQueryV2($sql, $param)),
			'data' => $data,
		];
	}

	//============================================================ download
	else {
		$sql = str_replace(['advance_amount /*', 'invoice_balance /*', '/*', '*/'], '', $sql);
		generateExcel([
			"filename" => "Advance Payment ".date("Ymd hiA"),
			"FILTERED CRITERIA" => [
				["Filter Type"=>"Global Search", "Filter Value"=>$_POST['search']['value']],
				["Filter Type"=>"Sponsor No.", "Filter Value"=>$_POST['smartFilter']['vcs_vendor_code']?:''],
				["Filter Type"=>"Sponsor Name", "Filter Value"=>$_POST['smartFilter']['dpm_vendor_name']?:''],
				["Filter Type"=>"Adv. Amt. From", "Filter Value"=>$_POST['smartFilter']['advance_amount_from']?:''],
				["Filter Type"=>"Adv. Amt. To", "Filter Value"=>$_POST['smartFilter']['advance_amount_to']?:''],
				["Filter Type"=>"Inv. Bal. From", "Filter Value"=>$_POST['smartFilter']['invoice_balance_from']?:''],
				["Filter Type"=>"Inv. Bal. To", "Filter Value"=>$_POST['smartFilter']['invoice_balance_to']?:''],
			],
			"DATA" => executeQueryV2($sql, $param)
		]);
	}
}