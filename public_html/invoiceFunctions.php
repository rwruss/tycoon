<?php

function writeInvoice($invoiceInfo, $taxRates, $contractFile) {
	$invoiceID = 0;
	if (flock($contractFile, LOCK_EX)) {
		// get a new invoice number
		fseek($contractFile, 0, SEEK_END);
		$invoiceID = max(100, ftell($contractFile));

		echo 'Create invoice #'.$invoiceID;
		$invoiceInfo[10] = $invoiceID;

		$invoiceDat = '';
		for ($i=1; $i<=20; $i++) {
			$invoiceDat .= pack('i', $invoiceInfo[$i]);
		}
		for ($i=1; $i<=30; $i++) {
			$invoiceDat .= pack('s', $taxRates[$i]);
		}

		echo 'Invoice size is '.strlen($invoiceDat);

		fseek($contractFile, $invoiceID);
		fwrite($contractFile, $invoiceDat);

		flock($contractFile, LOCK_UN);
	}
	
	return $invoiceID'
}

?>