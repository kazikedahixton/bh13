<?php
require_once( "sparqllib.php" );
 
$db = sparql_connect( "http://rdf.farmbio.uu.se/chembl/sparql/" );
if( !$db ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }
$db->ns( "foaf","http://xmlns.com/foaf/0.1/" );
$db->ns( "chembl","http://rdf.farmbio.uu.se/chembl/onto/#" );
$db->ns( "rdfs","http://www.w3.org/2000/01/rdf-schema#" );

$query = $_POST['query'];
//print "<br>$query<br>";

$classL1="Top";
$classL2="$query";
//$classL2="7TM1";

$tar="target"; 
$obk="count"; 
$des="description"; 
$tart=array();


$sparql3  = "select count(distinct ?molecule) as ?count ?target where {";
$sparql3 .= " ?assay chembl:hasTarget ?target . ";
$sparql3 .= " ?target chembl:classL2 \"$classL2\" . ";
$sparql3 .= " ?target chembl:hasTaxonomy <http://bio2rdf.org/taxonomy:10116> . ";
//$sparql3 .= " ?target chembl:classL5 \"Serotonin receptor\" . ";
$sparql3 .= " ?activity chembl:onAssay ?assay ; ";
$sparql3 .= " chembl:forMolecule ?molecule ; ";
$sparql3 .= " chembl:type \"IC50\" ; ";
$sparql3 .= " chembl:standardUnits \"nM\"; ";
$sparql3 .= " chembl:standardValue ?value; ";
$sparql3 .= " chembl:relation \"=\"  ";
$sparql3 .= " FILTER ( ?value < 10000 ) . ";
$sparql3 .= " ?assay <http://purl.org/spar/cito/citesAsDataSource> ?cit . ";
$sparql3 .= " ?cit <http://purl.org/dc/elements/1.1/date> ?date . ";
$sparql3 .= " FILTER ( xsd:int(?date) > 2003 ) ";
$sparql3 .= " } group by ?target ";

$result3 = $db->query( $sparql3 );
if( !$result3 ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }
while( $row3 = $result3->fetch_array() )
{
	$bbb = split("/", $row3[$tar]);
	$cnt = $row3[$obk];
	$ttt = array_pop($bbb);
	$tart[$ttt]=$cnt;
}


$sparql2  = "select count(distinct ?molecule) as ?count ?target where {";
$sparql2 .= " ?assay chembl:hasTarget ?target . ";
$sparql2 .= " ?target chembl:classL2 \"$classL2\" . ";
$sparql2 .= " ?target chembl:hasTaxonomy <http://bio2rdf.org/taxonomy:10116> . ";
//$sparql2 .= " ?target chembl:classL5 \"Serotonin receptor\" . ";
$sparql2 .= " ?activity chembl:onAssay ?assay ; ";
$sparql2 .= " chembl:forMolecule ?molecule ; ";
$sparql2 .= " chembl:type \"IC50\" ; ";
$sparql2 .= " chembl:standardUnits \"nM\"; ";
$sparql2 .= " chembl:standardValue ?value; ";
$sparql2 .= " chembl:relation \"=\"  ";
$sparql2 .= " FILTER ( ?value < 10000 ) . ";
$sparql2 .= " ?assay <http://purl.org/spar/cito/citesAsDataSource> ?cit . ";
$sparql2 .= " ?cit <http://purl.org/dc/elements/1.1/date> ?date . ";
$sparql2 .= " FILTER ( xsd:int(?date) > 2008 ) ";
$sparql2 .= " } group by ?target ";

$result2 = $db->query( $sparql2 );
if( !$result2 ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }
while( $row2 = $result2->fetch_array() )
{
	$bbb = split("/", $row2[$tar]);
	$cnt = $row2[$obk];
	$ttt = array_pop($bbb);
	$tars[$ttt]=$cnt;
}


$sparql  = "select count(distinct ?molecule) as ?count ?target ?description where {";
$sparql .= " ?assay chembl:hasTarget ?target . ";
$sparql .= " ?target chembl:classL2 \"$classL2\" . ";
$sparql .= " ?target chembl:hasDescription ?description . ";
$sparql .= " ?target chembl:hasTaxonomy <http://bio2rdf.org/taxonomy:10116> . ";
//$sparql .= " ?target chembl:classL5 \"Serotonin receptor\" . ";
$sparql .= " ?activity chembl:onAssay ?assay ; ";
$sparql .= " chembl:forMolecule ?molecule ; ";
$sparql .= " chembl:type \"IC50\" ; ";
$sparql .= " chembl:standardUnits \"nM\"; ";
$sparql .= " chembl:standardValue ?value; ";
$sparql .= " chembl:relation \"=\"  ";
$sparql .= " FILTER ( ?value < 10000 ) ";
$sparql .= " } group by ?target ?description order by desc (?count) ";

$result = $db->query( $sparql ); 
if( !$result ) { print $db->errno() . ": " . $db->error(). "\n"; exit; }
 
$fields = $result->field_array( $result );

$json="data.csv";
$fo = fopen($json, 'w'); 
chmod($json,0777);

$str="Target,Last_5_years,5_to_10_years,Before_2003\n";

$j=0;
while( $row = $result->fetch_array() )
{
	$aaa = split("/", $row[$tar]);
	$tid = array_pop($aaa);
	$ddd = $row[$des];
	$ddd = str_replace(",","",$ddd);
	$ddd = str_replace("[","",$ddd);
	$ddd = str_replace("]","",$ddd);
	//if($j<5){
	if($j<50){
		$total=$row[$obk];
		$latest=0;
		$medium=0;

		if (array_key_exists($tid, $tars)) {
			$latest=$tars[$tid];
		}
		if (array_key_exists($tid, $tart)) {
			$medium=$tart[$tid];
		}

		$recent=$medium-$latest;
		$old=$total-$medium;
		$str .= "$ddd,$latest,$recent,$old\n";
	}
	++$j;
}

fwrite($fo,"$str");
fclose($fo);

header("Location: http://localhost/hack/molecule.html");

?>
