<?
require_once('./lnk1');
require_once('./lnk0');
mysql_select_db($database_conn_db, $conn_db);
require_once('./functions.php.inc');
//print_r($_POST);
if (($_POST['from-number'] != '') or ($_POST['date-from'] != '') or ($_POST['date-to'] != '') or ($_POST['user'] != '') or ($_POST['project'] != '') or ($_POST['workgroup']) or ($_POST['calltype'])) {
    $fromnumber=$_POST['from-number'];
    $calltype=$_POST['calltype'];
    if ($_POST['date-from']) {
	$datefrom=$_POST['date-from'] . " 00:00:00";
	$datefrom = datetoepoch($datefrom);
    }
    if ($_POST['date-to']) {
        $dateto=$_POST['date-to'] . " 23:59:59";
        $dateto = datetoepoch($dateto);
    }
    $projectid=$_POST['project'];
    $workgroupid=$_POST['workgroup'];
    $userid=$_POST['user'];
    $query = "select distinct h.interactionid, u.firstname, u.lastname, p.name, w.name, q.actiondate, i.originator, i.destinator, i.duration, q.filename from historyroutinguser h, users u, projects p, workgroups w, interactions i, qualitycontrol q where h.companyid = 101 ";
    $query=$query . "and h.userid = u.userid ";
    $query=$query . "and h.workgroupid = w.workgroupid ";
    $query=$query . "and h.projectid = p.projectid ";
    $query=$query . "and h.interactionid = i.interactionid ";
    $query=$query . "and q.interactionid = h.interactionid ";
    if ($datefrom) {
	$query=$query . "and h.actiondate > $datefrom ";
    }
    if ($dateto) {
	$query=$query . "and h.actiondate < $dateto ";
    }
    if ($workgroupid) {
	$query=$query . "and h.workgroupid = $workgroupid ";
    }
    if ($userid) {
	$query=$query . "and h.userid = $userid ";
    }
    if ($fromnumber) {
	if ($calltype=='inbound') {
	    $query=$query . "and i.originator like '%".$fromnumber."%' ";
	} else if ($calltype=='outbound') {
	    $query=$query . "and i.destinator like '%".$fromnumber."%' ";
	}
    }
    $query = $query . "order by q.actiondate ";
//    $query = $query . "group by u.firstname, u.lastname, p.name, w.name, h.actiondate, i.originator, i.destinator, i.duration, q.filename";
//    echo "$query";

}

?>
<html>
<head> 
<link href="style.css" type="text/css" rel="stylesheet" />
<title> CCA Recordings </title>
<link rel="stylesheet" type="text/css" media="all" href="jscal/jsDatePick_ltr.min.css" />
<script type="text/javascript" src="jscal/jsDatePick.min.1.3.js"></script>
<script type="text/javascript">
/*    window.onload = function(){
    };
*/
	
    window.onload = function(){
	new JsDatePick({
	    useMode:2,
	    target:"date-to",
	    dateFormat:"%d-%M-%Y",
	    yearsRange:[1978,2020],
	    LimitToToday:false,
	    cellColorScheme:"beige",
	    dateFormat:"%d-%m-%Y",
	    imgPath:"jscal/img/",
	    weekStartDay:1
	});


	new JsDatePick({
	    useMode:2,
	    target:"date-from",
	    dateFormat:"%d-%M-%Y",
	    yearsRange:[1978,2020],
	    LimitToToday:false,
	    cellColorScheme:"beige",
	    dateFormat:"%d-%m-%Y",
	    imgPath:"jscal/img/",
	    weekStartDay:1
	});


    };
</script>
</head>
<body>
<div id="selectors">
<center>
<h1> .: CCA Recording :. </h1>
</center>
<form method="POST" id="form1" action="index.php">

<table>
<tr>
<td>
    <fieldset>
    <legend> Customer </legend>
    <table>
    <tr>
    <td> Phone Number: 
    <input type="text" name="from-number"> </input>
    </td>
    </tr>
    </table>
    </fieldset>
</td>
<td>
    <fieldset>
    <legend> Period </legend>
    <table>
    <tr>
    <td> 
    From: 
    <input type="text" id="date-from" name="date-from"> </input>
    </td>
    <td> 
    To: 
    <input type="text" id="date-to" name="date-to"> </input>
    </td>
    </tr>
    </table>
    </fieldset>
</td>
</tr>
</table>
<fieldset>
<legend> CCA </legend>
<table>
<tr>
<td> 
Project: 
<?
$querylistprojects="select projectid, name, description from projects where companyid='101' order by name";
$rs_querylistprojects = odbc_exec($conn_cc_db, $querylistprojects);
$row_rs_querylistprojects = odbc_fetch_row($rs_querylistprojects);
?>
<select name="project">
<option value=""> Choose Project </option>
<?
do {
    $projectid = odbc_result($rs_querylistprojects, 1);
    $projectname = odbc_result($rs_querylistprojects, 2);
    $projectdescription = odbc_result($rs_querylistprojects, 3);
    echo "<option value=\"$projectid\"> $projectname - $projectdescription </option>\n";
} while (odbc_fetch_row($rs_querylistprojects));

odbc_free_result($rs_querylistprojects);
?>
</select>
</td>
</tr>
<tr>
<td> 
Workgroup: 
<?
$querylistworkgroups="select workgroupid, name, description from workgroups where companyid='101' order by name";
$rs_querylistworkgroups = odbc_exec($conn_cc_db, $querylistworkgroups);
$row_rs_querylistworkgroups = odbc_fetch_row($rs_querylistworkgroups);
?>
<select name="workgroup">
<option value=""> Choose Workgroup </option>
<?
do {
    $workgroupid = odbc_result($rs_querylistworkgroups, 1);
    $workgroupname = odbc_result($rs_querylistworkgroups, 2);
    $workgroupdescription = odbc_result($rs_querylistworkgroups, 3);
    echo "<option value=\"$workgroupid\"> $workgroupname - $workgroupdescription </option>\n";
} while (odbc_fetch_row($rs_querylistworkgroups));

odbc_free_result($rs_querylistworkgroups);
?>
</select>
</td>
</tr>
<tr>
<td> 
User: 
<?
$querylistusers="select userid, firstname, lastname from users where companyid='101' order by firstname";
$rs_querylistusers = odbc_exec($conn_cc_db, $querylistusers);
$row_rs_querylistusers = odbc_fetch_row($rs_querylistusers);
?>
<select name="user">
<option value=""> Choose User </option>
<?
do {
    $userid = odbc_result($rs_querylistusers, 1);
    $firstname = odbc_result($rs_querylistusers, 2);
    $lastname = odbc_result($rs_querylistusers, 3);
    echo "<option value=\"$userid\"> $firstname $lastname </option>\n";
} while (odbc_fetch_row($rs_querylistusers));

odbc_free_result($rs_querylistusers);
?>
</select>
</td>
</tr>

<tr>
<td> 
Call type:
<input type="radio" name="calltype" value="inbound" checked>Inbound</input> <input type="radio" name="calltype" value="outbound">Outbound</input>
</td>
</tr>

</table>
</fieldset>
<input type="submit" name="submit" value="Submit">

</form>
</div>
<div id="result">
<?
if (($_POST['from-number']) or($_POST['date-from'] != '') or ($_POST['date-to'] != '') or ($_POST['user'] != '') or ($_POST['project'] != '') or ($_POST['workgroup']) or ($_POST['calltype'])) {
    $rs_querycalls=odbc_exec($conn_cc_db, $query);
    $row_rs_querycalls=odbc_fetch_row($rs_querycalls);
    $num_rows_querycalls=odbc_num_rows($rs_querycalls);
    $arraycalls = array();
    do {
	$thiscall = array();
	$thiscall[0]=odbc_result($rs_querycalls, 1);
	$thiscall[1]=odbc_result($rs_querycalls, 2);
	$thiscall[2]=odbc_result($rs_querycalls, 3);
	$thiscall[3]=odbc_result($rs_querycalls, 4);
	$thiscall[4]=odbc_result($rs_querycalls, 5);
	$thiscall[5]=odbc_result($rs_querycalls, 6);
	$thiscall[6]=odbc_result($rs_querycalls, 7);
	$thiscall[7]=odbc_result($rs_querycalls, 8);
	$thiscall[8]=odbc_result($rs_querycalls, 9);
	$thiscall[9]=odbc_result($rs_querycalls, 10);
	$arraycalls[] = $thiscall;
//	print_r($thiscall);
//	echo "<br>\n";
    } while (odbc_fetch_row($rs_querycalls));
//    echo "<pre>\n";
//    print_r($arraycalls);
//    echo "</pre>\n";
    echo "Your query returned " . count($arraycalls) . " rows.\n";
    echo "<table id=\"calls\">\n";
    echo "<tr> <th> Interactionid </th><th> Agent Name </th> <th> Project Name </th> <th> Workgroup Name</th> <th>Date Time </th> <th> Originator </th> <th> Destinator </th> <th> Duration </th> <th> Recording </th> <th> Path </th> <th> Backup </th> </tr>";
    for ($i=0; $i < count($arraycalls); $i++) {
	$thiscall=$arraycalls[$i];
	$interactionid=$thiscall[0];
	$firstname=$thiscall[1];
	$lastname=$thiscall[2];
	$projectname=$thiscall[3];
	$workgroupname=$thiscall[4];
	$datetime=$thiscall[5];
	$originator=$thiscall[6];
	$destinator=$thiscall[7];
	$duration=$thiscall[8];
	$filename=$thiscall[9];
	echo "<tr onMouseOver=\"this.className='highlight'\" onMouseOut=\"this.className='normal'\"\">\n";
	echo "<td class=\"callstd\"> " . $interactionid . "</td>\n";
	echo "<td class=\"callstd\"> " . $firstname . " " . $lastname . " </td>\n";
	echo "<td class=\"callstd\"> " . $projectname . "</td>\n";
	echo "<td class=\"callstd\"> " . $workgroupname . "</td>\n";
	echo "<td class=\"callstd\"> " . epochtodatetime($datetime) . "</td>\n";
	echo "<td class=\"callstd\"> " . $originator . "</td>\n";	
	echo "<td class=\"callstd\"> " . $destinator . "</td>\n";
	echo "<td class=\"callstd\"> " . $duration . "</td>\n";
	echo "<td class=\"callstd\"> " . $filename . " </td>\n";
	$query="select path from filelist where path like '%".$filename."'";
	$res=mysql_query($query);
        while ($row = mysql_fetch_assoc($res)) {
          $path=$row['path'];
          $newpath=str_replace("\\", "/", $path);
          $httplink1=str_replace("G:", "http://usw-vc-ccftp.uswitch.com/current", $newpath);
          $httplink=str_replace("X:", "http://usw-vc-ccftp.uswitch.com/backup", $httplink1);
          echo "<td class=\"callstd\"><a href=\"".$httplink."\" target=\"_blank\">Download</a></td>\n";
        }
	echo "</tr>\n";
    }
    odbc_free_result($rs_querycalls);
    echo "</table>\n";
}
?>

</div>

</body>
</html>