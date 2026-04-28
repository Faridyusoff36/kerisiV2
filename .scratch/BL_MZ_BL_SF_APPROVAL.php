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
			mjm_extended_field->>'$.advance_amount' advance_amount,
			mjm_extended_field->>'$.semester' semester,
			IF(mjm_extended_field->>'$.advance_category'='1', 'Bayaran Melalui Penaja', 'Bayaran One Off') advance_category
		FROM
			".DB2.".manual_journal_master
		WHERE
			mjm_journal_id = ?
	", [$_POST['mjm_journal_id']])[0];

	// $option		= "";
	// $workflow = executeQueryV2("CALL ".DB2.".workflowGetDetails(?, ?, @OUT)", [
	// 	$_POST['TASK_ID'],
	// 	$_USER['USERNAME']
	// ]);
	// //$workflow = json_decode($workflow[0]['dataJson'], true);
	// foreach($workflow['statusListing'] as $row){
	// 	$option = $option.'<option value="'.$row['code'].'" data-wpd"'.$row['wpd_order'].'">'.$row['desc'].'</option>';
	// };


	return [
		"details" => $data,
	];
}

if($_GET['dt_listing']) {

	$DT_CR = $_GET['dt_credit_debit'];

	$common = "	FROM ".DB2.".manual_journal_details mjd
				WHERE	   
					mjd_trans_type  = '$DT_CR'
				AND mjd.mjm_journal_no = '".$_GET['mjm_journal_id']."'

			 ";

	if($_POST['search']['value']) {
		$common .= "
			 AND CONCAT_WS('__',
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
					mjd_document_no, 
					mjd_trans_amt,
					(SELECT MAX(mjm_total_amt) FROM ".DB2.".manual_journal_master mjm
						WHERE mjm.mjm_journal_id=mjd.mjm_journal_no),
				   CONCAT(mjd_payto_name, ' (', mjd_payto_id, ')')
			) LIKE CONCAT('%', ?, '%')

			";
		$param[] = $_POST['search']['value'];
	}
					
	$sql = "SELECT COUNT(*) C  $common ";
	$recordsFiltered = executeQueryV2($sql, $param)[0]['C'];

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
						(SELECT MAX(mjm_total_amt) FROM ".DB2.".manual_journal_master mjm
							WHERE mjm.mjm_journal_id=mjd.mjm_journal_no) mjm_total_amt,
						CONCAT(mjd_payto_name, ' (', mjd_payto_id, ')') cim_cust,
						(select saf_invoice_amt from ".DB2.".stud_advance_from_sponsor saf
							where saf.mjm_journal_no=mjd.mjm_journal_no
							and saf.std_student_id = mjd_payto_id) saf_invoice_amt
					$common
					ORDER BY {$_POST['orderBy'][0]} {$_POST['orderBy'][1]}
        		    LIMIT ".$_POST['start'].", ".$_POST['length']."
			",$param);
	

	$footerAmt =  executeQueryV2("SELECT SUM(mjd_trans_amt) S
								  FROM 
										".DB2.".manual_journal_details mjd
								   WHERE 
										mjd_trans_type  = '$DT_CR'
										AND mjd.mjm_journal_no = '".$_GET['mjm_journal_id']."'",  $param)[0]['S'];
    									
	return [
		 'draw' => $_POST['draw'],
		 'recordsFiltered' => $recordsFiltered,
		 'data'            => $data?:[],
		 'footer'          => ['mjd_trans_amt' => $footerAmt, 'mjm_total_amt' =>$footerAmt ]
	];
}

if($_GET['approve']) {

	//============================================== update cust_invoice_master
	//executeQueryV2("
	//	UPDATE ".DB2.".cust_invoice_master
	//	SET cim_pending_sponsor_amt = NULL
	//	WHERE cim_invoice_no IN (
	//		SELECT DISTINCT mjd_document_no
	//		FROM ".DB2.".manual_journal_details
	//		WHERE mjm_journal_no = ?
	//	)
	//", [$_POST['mjm_journal_id']]);
	
	$checking = executeQueryV2("SELECT
									distinct
									#mjm.mjm_journal_no,
									cim_invoice_no InvoiceNo
								FROM 
									".DB2.".manual_journal_master mjm,
									".DB2.".manual_journal_details mjd, 
									".DB2.".cust_invoice_details cid,
									".DB2.".cust_invoice_master cim 
								WHERE mjm.mjm_journal_id=mjd.mjm_journal_no 
								AND mjd.mjd_source_detail_id = cid.cid_cust_invoice_detl_id 
								and cid.cim_cust_invoice_id = cim.cim_cust_invoice_id
								AND mjd.mjd_source='Invoice' 
								AND mjm.mjm_journal_id= ? 
								AND mjd.mjd_trans_type='CR' 
								AND mjd.mjd_trans_amt > cid.cid_bal_amt",[$_POST['mjm_journal_id']]);


	// $checking2 = json_encode($checking);
	// $checking3 = preg_replace(array('/^\[/','/\]$/'), '',$checking2); //remove bracket dpn,blkg   
	
	// foreach($checking as $c){

	// 	$invoice = implode (',', $c);
	// 	echo $invoice;
	
	// }

	for($i=0; $i < count($checking); $i++){
		$invoice .= $checking[$i]['InvoiceNo'].'<br>';

	}

	if($checking){

		if($_POST['flow']['STATUS'] == 'REJECT'){

			//var_dump($_POST['flow']['STATUS']);die();

			//============================================== workflow
			$vendor =  executeQueryV2("SELECT concat_ws(' - ',mjd_payto_id,mjd_payto_name) S
									FROM 
											".DB2.".manual_journal_details mjd
									WHERE 
											mjd_trans_type  = 'DT'
											AND mjd.mjm_journal_no = ? limit 1",  [$_POST['mjm_journal_id']])[0]['S'];
			
			$param = [
					
					"sponsor"  			=> $vendor,
					"total"    			=> number_format($_POST['totalAmt'], 2),
					"journal"  			=> $_POST['journalNo'],
					"amount_to_process"	=> $_POST['totalAmt'],
					"unit"				=> "UNIT_STUD_FINANCE",
					"encodeURL"			=> "Y",
					"wtk_task_url" 		=> "page=page_wrapper&menuID=2390&wtk_application_id=".$_POST['mjm_journal_id']."&taskId="
			];

			$workflow = executeQueryV2("CALL ".DB2.".workflowUpdate(?,?,?,?,?, @OUT)", [
				$_POST['TASK_ID'],
				$_POST['flow']['STATUS'],
				$_POST['flow']['REMARK'],
				$_USER['USERNAME'],
				json_encode($param)
			]);

			$workflow = json_decode($workflow[0]['dataJson'], true);

			return [
				'workflow' => $workflow,
				'checking'	=> $checking,
				'status' => 'yes',
				'wfstatus' => $_POST['flow']['STATUS'],
				
			];
		}
		else{
			return [
				'status' => 'yes',
				'checking'	=> $checking,
				'wfstatus' => $_POST['flow']['STATUS'],
				'errormsg' => '<b>'.$_POST['journalNo'].'</b>.Tidak boleh diluluskan kerana terdapat invoice yang telah berubah baki.<br><b>'.$invoice.'</b></br>Mohon semak semula.'
    		];
			
		}
	}

		//============================================== workflow
		$vendor =  executeQueryV2("SELECT concat_ws(' - ',mjd_payto_id,mjd_payto_name) S
								   FROM 
										".DB2.".manual_journal_details mjd
								   WHERE 
										mjd_trans_type  = 'DT'
										AND mjd.mjm_journal_no = ? limit 1",  [$_POST['mjm_journal_id']])[0]['S'];
		$param = [
				
				"sponsor"  			=> $vendor,
				"total"    			=> number_format($_POST['totalAmt'], 2),
				"journal"  			=> $_POST['journalNo'],
				"amount_to_process"	=> $_POST['totalAmt'],
				"unit"				=> "UNIT_STUD_FINANCE",
				"encodeURL"			=> "Y",
				"wtk_task_url" 		=> "page=page_wrapper&menuID=2390&wtk_application_id=".$_POST['mjm_journal_id']."&taskId="
		];


		$workflow = executeQueryV2("CALL ".DB2.".workflowUpdate(?,?,?,?,?, @OUT)", [
			$_POST['TASK_ID'],
			$_POST['flow']['STATUS'],
			$_POST['flow']['REMARK'],
			$_USER['USERNAME'],
			json_encode($param)
		]);

		$workflow = json_decode($workflow[0]['dataJson'], true);

		return [
				"workflow" => $workflow,
				'status' => 'ok',
		];
}



if($_GET['getWFDetail']) {
	$option		= "";
    $workflow	= workflowGetDetails($_POST['taskId']);

	foreach($workflow['statusListing'] as $row){
		$option = $option.'<option value="'.$row['code'].'" data-wpd"'.$row['wpd_order'].'">'.$row['desc'].'</option>';
	};
	return [
        'Option'	=> ($option)?: $workflow,
        'status'	=> ($option)?1:0,
		'workflow' => $workflow,
    ];
}

	

