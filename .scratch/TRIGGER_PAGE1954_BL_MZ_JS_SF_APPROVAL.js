$(document).ready(function(){
	$.post("api/MZ_BL_SF_APPROVAL?details=1", {
			mjm_journal_id: $_GET['wtk_application_id'],
			TASK_ID: $_GET['taskId'],
		}, function(response) {
		for(key in response.details) $('#'+key).val(response.details[key])
		$('#advance_amount').val(toCurrency(response.details.advance_amount))
		$('#mjm_total_amt').val(toCurrency(response.details.mjm_total_amt))
		
		// listing.getList('dt_debit')
		// listing.getList('dt_credit')
		//$('#STATUS').html(response.workflow.statusListing[0].map(i=>{ return `<option value="${i.code}">${i.desc}</option>` }).join(''))
		//$('#flowApproveDesc').val(response.workflow.currentProcessName)
	}, 'json')
})

taskId = $_GET['taskId'];

window['taskId'] = $_GET['taskId']
if (!$_GET['taskId'])
	$('#cm_flowApprove').hide();
else {
	$.post("api/MZ_BL_SF_APPROVAL?getWFDetail=1", {
		taskId: $_GET['taskId']
	}, function (data) {
		if (data.status == 1)
		$('#STATUS').html(data.Option).change();
		$('#flowApproveDesc').val(data.workflow.currentProcessName)

	}, "json");
	
}

formValidate('#cm_flowApprove')

$(document).on('change', '#STATUS', function () {
	$('#inputLabel_wf_remarks font').toggleClass('d-none', $(this).val() != 'REJECT')
	$('#REMARK').prop('required', $(this).val() == 'REJECT')
})

window['listing'] = {
	getList: function(type) {
		$.post("api/MZ_BL_SF_APPROVAL?dt_listing=1", {
			mjm_journal_id: $_GET['wtk_application_id'],
			//saf_batch_no: $_GET['saf_batch_no'],
			type: type
		}, function(response) {
			window[type].clear().rows.add(response).draw(false)
			//if(type=='dt_credit') listing.updateFooter()
		}, 'json')
	},
	// updateFooter: function() {
	// 	var totalOverall = 0, totalByInvoice = {}

	// 	dt_credit.data().toArray().forEach(i=>{
	// 		totalOverall += i.mjd_trans_amt

	// 		if(!totalByInvoice[i.mjd_document_no]) totalByInvoice[i.mjd_document_no] = 0
	// 		totalByInvoice[i.mjd_document_no] += i.mjd_trans_amt
	// 		$('#dt_debit [mjd_document_no="'+i.mjd_document_no+'"]').text(toCurrency(totalByInvoice[i.mjd_document_no]))
	// 		dt_debit.row($('#dt_debit [mjd_document_no="'+i.mjd_document_no+'"]').closest('tr')[0]).data().mjd_trans_amt = totalByInvoice[i.mjd_document_no]
	// 	})

	// 	$('#dt_credit').footer({ mjd_trans_amt: totalOverall })
	// 	$('#dt_debit').footer({ mjd_trans_amt: totalOverall })
	// },
	checkInvoice: function(){
		console.log('masuk');
		if(status == 'yes') {
			console.log('masuk2');
			showModal({
						content: errormsg,
						button: ['Cancel', 'Ok'],
						color: 'danger',  
					})
		}

	},
	approve: function() {

		var data = ([...dt_credit.data().toArray(), ...dt_debit.data().toArray()]).map(i=>{
			return {
				mjd_journal_detl_id: i.mjd_journal_detl_id,
				mjd_trans_amt: i.mjd_trans_amt
			}
		})
		var total = [$('#mjm_total_amt').val()].sum()
		var adv = [$('#advance_amount').val()].sum()

		if(total>adv) {
			showModal({
				content: 'Amount has exceed Advance Amount',
				size: 'sm',
			})
		}
		else if(total) {
			$('#btn_save').toggleSpinner()
			$.post('api/MZ_BL_SF_APPROVAL?popup_error=0&approve=1', {
				flow: $('#cm_flowApprove :input').serializeJson(),
				mjm_journal_id: $_GET['wtk_application_id'],
				TASK_ID: $_GET['taskId'],
				totalAmt:total,
				journalNo:$('#mjm_journal_no').val(),
				reject: $('#STATUS').val()=='REJECT',
				data: data,
			}).done(function(response){
				if(response.status == 'yes' &&  response.wfstatus != 'REJECT' ) {
					$('#btn_save').toggleSpinner()
					console.log('bknreject');
					showModal({
								content: response.errormsg+'<b>SILA PILIH REJECT.<b>',
								button: ['Ok'],
								//size: 'sm',
								// onclick: function(){
								// 		// if ($("#STATUS").val() == 'VERIFY'){
								// 		// 	//$('#btn_save').toggleSpinner()
								// 		// 	showModal({
								// 		// 		content: 'Sila pilih rejected kerana journal nama tidak boleh diluluskan',
								// 		// 		button: ['Ok'],
								// 		// 		size: 'sm',
								// 		// 	})
								// 		// }
								// 		// else{
								// 		// 	//$('#btn_save').toggleSpinner()
								// 		// 	showModal({
								// 		// 		content: 'Journal Successfully submitted',
								// 		// }
								// 		//$("#STATUS").val('REJECT')
								// 		$("#STATUS").val('REJECT').attr('disabled',true)
								// 		
								// } 
					})
					
				}
				else if(response.status == 'yes' && response.wfstatus == 'REJECT') {
					console.log('reject');
					showModal({
						content: 'Journal Successfully submitted',
						size: 'sm',
						onclick: function(){
							history.go(-1)
						}
					})
				}
				if(response.status == 'ok' && response.status != 'yes' ) {
					$('#btn_save').toggleSpinner()
					console.log('ygok');
					showModal({
						content: 'Journal Successfully submitted',
						size: 'sm',
						onclick: function(){
							history.go(-1)
						}
					})
				}
				else if(response.status != 'ok' && response.status != 'yes' ){
					$('#btn_save').toggleSpinner()
					console.log('ygxok');
					showModal({
						content: 'Fail to submit',
						size: 'sm',
						onclick: function(){
							history.go(-1)
						}
					})
				}
			})
	
		}

	},
}

// Shorthand for $( document ).ready()
// $(function() {
//     console.log( "ready!" );
// 	listing.checkInvoice()
	
// });

$(document).on('change', '[dt_credit_mjd_journal_detl_id]', function(){
	dt_credit.row($(this).closest('tr')[0]).data().mjd_trans_amt = [$(this).val()].sum()
	$('#mjm_total_amt').val(toCurrency([$(this).val()].sum()))
	listing.updateFooter()
})