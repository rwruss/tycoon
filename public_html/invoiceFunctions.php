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

	return $invoiceID;
}

function calcPrice($demandQty, $payDemos, $currentSupply) {
	//$demandQty = [];
	$usePrice = 0;
	for ($i=9; $i>0; $i--) {
		//$demandQty[$i] = $population*$populationDemo[$i]*$demandLevels[$i]/100;
	  echo $currentSupply.' vs '.$demandQty[$i].'<br>';
		if ($currentSupply < $demandQty[$i]) {
			echo 'Remaining Demand : '.($demandQty[$i] - $currentSupply).'<p>';
	    echo $payDemos[$i+1].'- ('.$payDemos[$i+1].' - '.$payDemos[$i].') * ('.$currentSupply.'/'.$demandQty[$i].')';
			$pctSupplied = $currentSupply/$demandQty[$i];
			$usePrice = round($payDemos[$i+1]-$pctSupplied*($payDemos[$i+1] - $payDemos[$i]), 2);
			break;
		}
		$currentSupply -= $demandQty[$i];
	}
	return $usePrice;
}

?>
