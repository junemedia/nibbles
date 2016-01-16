<?php

// We will import the DB connections now
require_once(dirname(__FILE__) . '/../../../config.php');
mysql_select_db('arcamax');

$usersSql = "SELECT * FROM `LeonCampaignPush` WHERE `notes` LIKE 'User not found in campaigner'";
$uresult = mysql_query($usersSql);
?>

<div>Please note that the this record every trial of the user unsub actions so there may be duplicates emails</div>
<table>
    <tr><th>id</th><th>email</th><th>attrs</th><th>IsProcessed</th><th>date_add</th><th>notes</th></tr>
    <?php
        while($row = mysql_fetch_object($uresult)){
            echo "<tr>";
                echo "<td>" . $row->id . "</td>";
                echo "<td>" . $row->email . "</td>";
                echo "<td>" . $row->attrs . "</td>";
                echo "<td>" . $row->IsProcessed . "</td>";
                echo "<td>" . $row->date_add . "</td>";
                echo "<td>" . $row->notes . "</td>";
            echo "</tr>";
        }
    ?>
</table>

