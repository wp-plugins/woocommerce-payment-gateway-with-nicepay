function doRefund(msg, url, orderid, tid) {
	var a = confirm(msg);

	if (a) {
		var params = "TID="+tid+"&order_id="+orderid;

		jQuery.ajax({
			type: 		'POST',
			url: 		url,
			data: 		params,
			success: 	function( code ) {
					try {
						var result = jQuery.parseJSON( code );

						if (result.result == 'success') {
							alert(result.message);
							location.reload();
						} else if (result.result == 'failure') {
							alert(result.message);
							location.reload();
						} else {
							throw "Invalid response";
						}
					} catch(err) {
						jQuery(document).prepend(code);
					}
				},
			dataType: 	"html"
		});
	}
}

function doEscrow(msg, url, orderid, tid, ReqType) {
	var a = confirm(msg);

	var DeliveryCoNm = jQuery("#nicepay_company_name").val();
	var InvoiceNum = jQuery("#nicepay_tracking_no").val();

	if (a) {
		var params = "TID="+tid+"&order_id="+orderid+"&ReqType="+ReqType+"&DeliveryCoNm="+DeliveryCoNm+"&InvoiceNum="+InvoiceNum;

		jQuery.ajax({
			type: 		'POST',
			url: 		url,
			data: 		params,
			success: 	function( code ) {
					try {
						var result = jQuery.parseJSON( code );

						if (result.result == 'success') {
							alert(result.message);
							location.reload();
						} else if (result.result == 'failure') {
							alert(result.message);
							location.reload();
						} else {
							throw "Invalid response";
						}
					} catch(err) {
						jQuery(document).prepend(code);
					}
				},
			dataType: 	"html"
		});
	}
}