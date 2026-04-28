<?php

include('userinfo.php');

if(isset($_GET['dt_listingDelete'])) {
	executeQueryV2("DELETE FROM ".DB2.".sponsor WHERE spn_sponsor_id = ?", [$_POST['spn_sponsor_id']]);
	return ["status" => "ok"];
}

if(isset($_GET['download'])) {
	$sql = "
		SELECT
			CONCAT_WS(' - ', spn_sponsor_code, spn_sponsor_name) `Sponsor`,
			spn_contact_person `Contact Name 1`,
			spn_contact_no `Contact Number 1`,
			spn_contact_person2  `Contact Name 2`,
			spn_contact_no2  `Contact Number 2`,
			spn_contact_person3  `Contact Name 3`,
			spn_contact_no3 `Contact Number 3`,
			spn_email `Email`,
			spn_extended_field->>'$.spn_status_desc' `Status`,
			spn_extended_field->>'$.spn_status_invoice_desc' `Status of Invoice`
		FROM ".DB2.".sponsor s
		ORDER BY 1
	";
}

//============================================================ common filter
$common = "
    FROM ".DB2.".sponsor s
	WHERE
		LOWER(CONCAT_WS('__',
			spn_sponsor_code,
			spn_sponsor_name,
			spn_contact_person, spn_contact_person2, spn_contact_person3,
			spn_extended_field->>'$.spn_status_desc'
		)) LIKE LOWER(CONCAT('%', ?, '%'))
		AND IFNULL(spn_email,'') LIKE CONCAT('%', ?, '%')
		AND CONCAT_WS(' - ', spn_sponsor_code, spn_sponsor_name) LIKE CONCAT('%', ?, '%')
		AND LOWER(spn_extended_field->>'$.spn_country_desc') LIKE LOWER(CONCAT('%', ?, '%'))
		AND spn_status_cd LIKE CONCAT('%', ?, '%')
";

$sql = "
	SELECT
		spn_sponsor_id,
		CONCAT_WS(' - ', spn_sponsor_code, spn_sponsor_name) sponsor,
		spn_contact_person,
		spn_contact_no,
		spn_contact_person2,
		spn_contact_no2,
		spn_contact_person3,
		spn_contact_no3,
		spn_email email,
		spn_extended_field->>'$.spn_status_desc' sponStatus,
		spn_extended_field->>'$.spn_status_invoice_desc' statusOfInvoice,
		(SELECT COUNT(*) FROM ".DB2.".stud_sponsor x WHERE x.spn_sponsor_code = s.spn_sponsor_name) hasChild
    $common
    ORDER BY {$_POST['orderBy'][0]} {$_POST['orderBy'][1]}
    LIMIT ".$_POST['start'].", ".$_POST['length']."
";
