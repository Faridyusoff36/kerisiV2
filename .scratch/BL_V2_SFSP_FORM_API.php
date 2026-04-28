<?php

include 'system_prerequisite.php';

if($_GET['formDetails']) {
	$data = executeQueryV2("SELECT * FROM ".DB2.".sponsor WHERE spn_sponsor_id = ?", [$_POST['spn_sponsor_id']])[0];
	return ['data' => $data?:[]];
}

if($_GET['detailsSave']) {

	$exist = executeQueryV2("SELECT 1 FROM ".DB2.".sponsor WHERE spn_sponsor_name = ? AND spn_sponsor_id != ?", [$_POST['spn_sponsor_name'], $_POST['spn_sponsor_id']?:'xxx']);
	if($exist) return ["status"=>"ko", "message"=>"Similar sponsor name already exist"];

	if(!$_POST['spn_sponsor_id']) $_POST['spn_sponsor_id'] = getSeqNo("sponsor");
	if(!$_POST['spn_sponsor_code']) $_POST['spn_sponsor_code'] = getRefNo("SPONSOR_CODE");
	$_POST['spn_status_desc'] = $_POST['spn_status_cd']==1?'ACTIVE':'INACTIVE';
	expressDML("sponsor");

	return ["status" => "ok", "spn_sponsor_id" => $_POST['spn_sponsor_id'], "spn_sponsor_code" => $_POST['spn_sponsor_code']];
}

function feeCoverList() {
	$sql = "
		SELECT	
			sfc_id,
			sfc_fee_category_code,
			sfc_extended_field->>'$.sfc_fee_category_desc' sfc_fee_category_desc,
			sfc_fi_code,
			sfc_extended_field->>'$.sfc_fi_desc' sfc_fi_desc,
			IF(sfc_sponsor_amt, 'amt', 'pct') sfc_sponsor_by,
			FORMAT(sfc_sponsor_amt, 2) sfc_sponsor_amt,
			FORMAT(sfc_sponsor_pct, 2) sfc_sponsor_pct
		FROM
			".DB2.".sponsor_fee_cover
		WHERE
			spn_sponsor_id = ?
		ORDER BY sfc_fee_category_desc
	";
	return ['data' => executeQueryV2($sql, [$_POST['spn_sponsor_id']])];
}

if($_GET['feeCoverList']) {
	return feeCoverList();
}

if($_GET['feeCoverSave']) {
	$common = "
		sfc_status = 1,
		spn_sponsor_id = '".$_POST['spn_sponsor_id']."',
		sfc_fee_category_code = '".$_POST['sfc_fee_category_code']."',
		sfc_fi_code = '".$_POST['sfc_fi_code']."',
		sfc_sponsor_amt = ?,
		sfc_sponsor_pct = ?
	";
	$commonParam = [$_POST['sfc_sponsor_by']=='amt'?$_POST['sfc_sponsor_val']:null, $_POST['sfc_sponsor_by']=='pct'?$_POST['sfc_sponsor_val']:null, $_POST['sfc_fee_category_desc'], $_POST['sfc_fi_desc']];

	if($_POST['sfc_id']) {
		$commonParam[] = $_POST['sfc_id'];
		$rs = executeQueryV2("UPDATE ".DB2.".sponsor_fee_cover SET $common, sfc_extended_field = JSON_SET(sfc_extended_field, '$.sfc_fee_category_desc', ?, '$.sfc_fi_desc', ?), updatedby = '".$_USER['USERNAME']."' WHERE sfc_id = ?", $commonParam);
	}
	else {
		$exist = executeQueryV2("SELECT 1 FROM ".DB2.".sponsor_fee_cover WHERE spn_sponsor_id = ? AND sfc_fi_code = ?", [$_POST['spn_sponsor_id'], $_POST['sfc_fi_code']]);
		if(!$exist) {
			executeQueryV2("CALL ".DB2.".getTableSequenceNum('sponsor_fee_cover', @SEQ)");
			$seq = executeQueryV2("SELECT @SEQ")[0]['@SEQ'];
			$commonParam[] = $seq;
			$rs = executeQueryV2("INSERT INTO ".DB2.".sponsor_fee_cover SET $common, sfc_extended_field = JSON_OBJECT('sfc_fee_category_desc', ?, 'sfc_fi_desc', ?), createdby = '".$_USER['USERNAME']."', sfc_id = ?", $commonParam);
		}
	}

	return feeCoverList();
}

if($_GET['feeCoverDelete']) {
	executeQueryV2("DELETE FROM ".DB2.".sponsor_fee_cover WHERE sfc_id = ?", [$_POST['sfc_id']]);
	return feeCoverList();
}

if($_GET['download']) {
	foreach($_POST['data'] as &$c) {
		foreach($c as $key=>$value) {
			$c[preg_replace('/.*____/i', '', $key)] = $value;
			unset($c[$key]);
		}
	}
	generateExcel([
		"filename" => $_POST['filename']." ".date("Ymd hiA"),
		$_POST['filename'] => $_POST['data']
	]);
}

else if($_GET['account']){
	$result = [
        'status' => 'ok',
		'urlReport' => flc_url_encode("id=".$_POST['id']),
	];
}
return $result;