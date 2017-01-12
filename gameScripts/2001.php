<?php

require_once('./objectClass.php');
$objFile = fopen($gamePath.'/objects.dat', 'r+b');

$thisBusiness = loadObject($pGameID, $objFile, 400);
$thisBusiness->save('money', $thisBusiness->get('money') + 1000000);

echo '
<script>
thisPlayer.money = '.$thisBusiness->get('money').'
</script>';

fclose($objFile);

?>
