<?php

mysql_pconnect ("localhost", "root", "8tre938G");


/*

$get = "SELECT * FROM samir_test.from";
$get_result = mysql_query($get);
echo mysql_error();
$x = 0;
while ($get_row = mysql_fetch_object($get_result)) {
	$a = $get_row->a;
	$b = $get_row->b;
	$c = $get_row->c;
	
	$s = substr($b,0,3);
	$t = substr($b,3,3);
	$u = substr($b,6,4);
	
	
	//echo $b."\n\n\n";
	
	$update_query = "UPDATE samir_test.to
				SET xx=\"$a\",
				yy=\"$b\",
				zz=\"$c\"
				WHERE (s=\"$s\" and t=\"$t\" and u=\"$u\")";
	
	if ($x == 1) {
	//	echo $update_query;
	//	exit;
	}
	$update = mysql_query($update_query);
	echo mysql_error();
	
	
	$x++;
}

*/







/*
$get = "SELECT * FROM samir_test.data5";
$get_result = mysql_query($get);
echo mysql_error();
$x = 0;
while ($get_row = mysql_fetch_object($get_result)) {
	$id = $get_row->id;
	$v = $get_row->v;

	$pieces = explode("  ", $v);
	$partner = '';
	foreach ($pieces as $piece) {
		if (strstr($piece,'Partner')) {
			$partner = strtoupper(trim($piece));
			$partner = str_replace('PARTNER=','',$partner);
			break;
		}
	}
	$v = $partner;

	
	$update_query = "UPDATE samir_test.data5
				SET v=\"$v\"
				WHERE id=\"$id\"";
	if ($x == 0) {
		echo $update_query;
		exit;
	}
	
	$update = mysql_query($update_query);
	echo mysql_error();
	
	
	$x++;
}



*/





/*
$handle = @fopen("/home/spatel/instant_411.csv", "r");
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        echo $buffer;
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}






//city=Ashland  IONumber=18897  Address2=  State=MO  ADDRESS1=202 S Wood Ct  Partner=a7631cc6-7aa4-452a-97a3-a1a09893b086  MelissaAddStatus=TRUE  MelissaAddVerified=TRUE  STATE=MO  APIRowID=38af07e5-df47-4be4-81f9-b365ed622efa  IP=63.190.97.234  BIRTH_MONTH=05  Itemid=c86871c3-b177-48db-a8bd-e9e9581e1303  address1=202 S Wood Ct  state=MO  FNAME=Aaron  OPT3=4232  EMAIL=aaronschnurman@yahoo.com  MelissaComStatus=  MelissaStatus=WEBTRUE  subidvalue=1422  ADDRESS2=  Zip=65010  address=202 S Wood Ct  GENDER=MALE  ADDRESS=202 S Wood Ct  MelissaComVerified=  BIRTH_YEAR=1974  Address1=202 S Wood Ct  City=Ashland  address2=  CITY=Ashland  BIRTH_DAY=31  OPT2=657  OPT1=573  zip=65010  ZIP=65010  subidname=source_id  LNAME=Schnrman  Address=202 S Wood Ct  
$pieces = explode("  ", $w);
$partner = '';
foreach ($pieces as $piece) {
	if (strstr($piece,'Partner')) {
		$partner = strtoupper(trim($piece));
		$partner = str_replace('PARTNER=','',$partner);
		break;
	}
}
$w = $partner;

*/




/*
$get = "SELECT * FROM samir_test.BE WHERE email IN (SELECT DISTINCT c FROM samir_test.client)";
$get_result = mysql_query($get);
echo mysql_error();
$x = 0;
$count = mysql_num_rows($get_result);
while ($get_row = mysql_fetch_object($get_result)) {
	$dateTime = $get_row->dateTime;
	$email = $get_row->email;
	$api = $get_row->api;
	$subid = $get_row->subid;
	$home = $get_row->home;
	$afid1 = $get_row->afid1;
	$afid2 = $get_row->afid2;
	$io = $get_row->io;
	
	$brandid = '';
	
	if ($io == '19808') { $brandid = '17'; }
	if ($io == '19363') { $brandid = '10'; }
	if ($io == '20447') { $brandid = '26'; }
	if ($io == '19847') { $brandid = '16'; }
	
	
	$update = "UPDATE samir_test.client
				SET dateTime = \"$dateTime\",
				api = \"$api\",
				subid = \"$subid\",
				home = \"$home\",
				afid1 = \"$afid1\",
				afid2 = \"$afid2\"
			WHERE c = \"$email\" AND b = \"$brandid\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	if ($x % 25 == 0) {
		echo $x . " of ". $count . "\n\n";
	}
	$x++;
}

*/







/*

//"created";"brandid";"email";"phone";"firstname";"address1";"city";"state";"zip"

load data infile '/home/spatel/Samir_Resend_20110303.txt' 
into table samir_test.BE  
fields terminated by '|' 
enclosed by '"' 
lines terminated by '\n';

*/





/*
$get = "SELECT * FROM samir_test.BE";
$get_result = mysql_query($get);
echo mysql_error();
$x = 0;
while ($get_row = mysql_fetch_object($get_result)) {
	$a = $get_row->a;
	$b = $get_row->b;
	$c = $get_row->c;
	$d = $get_row->d;
	$e = $get_row->e;
	$f = $get_row->f;
	$g = $get_row->g;
	$h = $get_row->h;
	$i = $get_row->i;
	$j = $get_row->j;
	$k = $get_row->k;
	$l = $get_row->l;
	$m = $get_row->m;
	$n = $get_row->n;
	$o = $get_row->o;
	$p = $get_row->p;
	$q = $get_row->q;
	$r = $get_row->r;
	$s = $get_row->s;
	$t = $get_row->t;
	$u = $get_row->u;
	$v = $get_row->v;
	$w = $get_row->w;
	
	//city=Ashland  IONumber=18897  Address2=  State=MO  ADDRESS1=202 S Wood Ct  Partner=a7631cc6-7aa4-452a-97a3-a1a09893b086  MelissaAddStatus=TRUE  MelissaAddVerified=TRUE  STATE=MO  APIRowID=38af07e5-df47-4be4-81f9-b365ed622efa  IP=63.190.97.234  BIRTH_MONTH=05  Itemid=c86871c3-b177-48db-a8bd-e9e9581e1303  address1=202 S Wood Ct  state=MO  FNAME=Aaron  OPT3=4232  EMAIL=aaronschnurman@yahoo.com  MelissaComStatus=  MelissaStatus=WEBTRUE  subidvalue=1422  ADDRESS2=  Zip=65010  address=202 S Wood Ct  GENDER=MALE  ADDRESS=202 S Wood Ct  MelissaComVerified=  BIRTH_YEAR=1974  Address1=202 S Wood Ct  City=Ashland  address2=  CITY=Ashland  BIRTH_DAY=31  OPT2=657  OPT1=573  zip=65010  ZIP=65010  subidname=source_id  LNAME=Schnrman  Address=202 S Wood Ct  
	$pieces = explode("  ", $w);
	$partner = '';
	foreach ($pieces as $piece) {
		if (strstr($piece,'Partner')) {
			$partner = strtoupper(trim($piece));
			$partner = str_replace('PARTNER=','',$partner);
			break;
		}
	}
	$w = $partner;
	
	$update_query = "UPDATE samir_test.client
				SET a1=\"$a\",
				b1=\"$b\",
				c1=\"$c\",
				d1=\"$d\",
				e1=\"$e\",
				f1=\"$f\",
				g1=\"$g\",
				h1=\"$h\",
				i1=\"$i\",
				j1=\"$j\",
				k1=\"$k\",
				l1=\"$l\",
				m1=\"$m\",
				n1=\"$n\",
				o1=\"$o\",
				p1=\"$p\",
				q1=\"$q\",
				r1=\"$r\",
				s1=\"$s\",
				t1=\"$t\",
				u1=\"$u\",
				v1=\"$v\",
				w1=\"$w\" 
				WHERE s=\"$m\" AND t=\"$n\" AND u=\"$o\"";
	
	if ($x == 1) {
		echo $update_query;
		exit;
	}
	$update = mysql_query($update_query);
	echo mysql_error();
	
	
	$x++;
}
*/

?>

