$('#dt_listing').closest('.card').append(`
	<div class="text-right mt-2">
		<a class="btn btn-primary ml-1" onclick="listing.download()"><i class="fas fa-file-spreadsheet align-middle"></i><span class="align-middle ml-1">Export</span></a>
	</div>
`)

window['listing'] = {
	reload: function() {
		var data = {
			search: {value: $('[type="search"]').val()},
			smartFilter: $('#dt_listingSmartFilter :input').serializeJson(),
		}
		$.post("api/V2_SAP_LIST_API?dt_listing", data, function(response) {
			dt_listing.clear().rows.add(response).draw(false)
		}, 'json')
	},
	download: function() {
		var data = {
			search: {value: $('[type="search"]').val()},
			smartFilter: $('#dt_listingSmartFilter :input').serializeJson(),
		}
		window.post("api/V2_SAP_LIST_API?download=1", data, '_BLANK')
	}
}

$(document).ready(function(){
	// listing.reload()
})