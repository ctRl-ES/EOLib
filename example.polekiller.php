<?php

// PoleKiller - S4X8 EOLib example
// Marcos Vives Del Sol - 24/V/2012
// CC-BY 3.0 license (http://creativecommons.org/licenses/by/3.0/)

$nick = "USUARIO";
$pass = "PASSWORD";

require("eol.inc.php");

while (1) {
	$threads = EOLreadThreads(21, 0);

	foreach ($threads as $t) {
		if (($t["type"] == "normal") && ($t["locked"] == false) && ($t["answers"] == 0)) {
			echo "POLE KILLED: " . $t["title"] . " TEXT: $w\n";
			EOLreply($nick, $pass, 21, $t["id"], "POLEKILLER", "POLEKILLER");
			sleep(30);
		};
	};

	echo "Step done\n";
	sleep(5);
};

?>
