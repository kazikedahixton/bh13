<?php
require_once( "sparqllib.php" );
 
$db = sparql_connect( "http://rdf.farmbio.uu.se/chembl/sparql/" );
if( !$db ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }
$db->ns( "foaf","http://xmlns.com/foaf/0.1/" );
$db->ns( "chembl","http://rdf.farmbio.uu.se/chembl/onto/#" );
$db->ns( "rdfs","http://www.w3.org/2000/01/rdf-schema#" );
 

$color=array();
$fp = fopen("coloring.txt", 'r'); 
$i=0;
while (! feof ($fp)) {
   $load = fgets ($fp, 4096);
   $load = rtrim($load,"\n");
   $color[$i]=$load;
   ++$i;
}
fclose ($fp);

$query = $_POST['query'];
//print "<br>$query<br>";

$classL1="Top";
$classL2="$query";
//$classL2="7TM1";

$sparql = "SELECT COUNT(?a) AS ?count ?c WHERE { ?a chembl:classL2 \"$classL2\" . ?a chembl:classL3 ?c} group by ?c";
$result = $db->query( $sparql ); 
if( !$result ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }
 
$fields = $result->field_array( $result );

$json="wheel.json";
$fo = fopen($json, 'w'); 
chmod($json,0777);

$str='';
$str.="[\n";
$str.="{\n";
$str.=" \"name\": \"$classL1\", \n";
$str.=" \"children\": [ \n";

$str.="{\n";
$str.=" \"name\": \"$classL2\", \n";
$str.=" \"children\": [ \n";

$j=0;
while( $row = $result->fetch_array() )
{
	$obj="c"; 
	$obk="count"; 

        $classL3=$row[$obj];
	$str.="{\n";
	$str.=" \"name\": \"$classL3\", \n";
	$str.=" \"children\": [ \n";

	$sparql2= "SELECT COUNT(?a) AS ?count ?c WHERE { ?a chembl:classL3 \"$classL3\" . ?a chembl:classL4 ?c} group by ?c";
	$result2= $db->query( $sparql2 ); 
	if( !$result2 ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }
	while( $row2 = $result2->fetch_array() )
	{

        	$classL4=$row2[$obj];
		//$classL4=str_replace("receptor"," ",$classL4);
		//$classL4=str_replace("-"," ",$classL4);
		//$classL4=str_replace("/"," ",$classL4);
		$str.="{ \n";
		$str.=" \"name\": \"$classL4\", \n";
		$str.=" \"children\": [ \n";

		$sparql3= "SELECT COUNT(?a) AS ?count ?c WHERE { ?a chembl:classL4 \"$classL4\" . ?a chembl:classL5 ?c} group by ?c";
		$result3= $db->query( $sparql3 ); 
		if( !$result3 ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }

		while( $row3 = $result3->fetch_array() )
		{
        		$classL5=$row3[$obj];
			$classL5=str_replace("receptor"," ",$classL5);
			$classL5=str_replace("-"," ",$classL5);
			$classL5=str_replace("/"," ",$classL5);
        		$count=$row3[$obk];
			//print "$classL2 / $classL3 / $classL4 / $classL5 : $count <br>";

			$str.=" {\"name\": \"$classL5\", \"colour\": \"$color[$j]\"}\n";
			$str.=",";
			++$j;

		}
		$str=substr($str,0,-1); $str.=" ] \n";
		$str.="}\n,";
	}
	$str=substr($str,0,-1); $str.=" ] \n";
	$str.="}\n,";
}
$str=substr($str,0,-1); $str.=" ] \n";
$str.="}\n";
$str.="]\n";
$str.="}\n";
$str.="]\n";

fwrite($fo,"$str");
fclose($fo);

header("Location: http://localhost/hack/target.html");

?>
