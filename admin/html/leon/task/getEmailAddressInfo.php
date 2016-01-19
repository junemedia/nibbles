<?php

require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/campaignController.php');

$r = new Campaign();
$res = $r->getCampaignEmailAddressInfo();
print_r($res);


