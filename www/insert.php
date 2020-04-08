<?php
include "connect.php";
include "connections.php";

$fname=$_POST['fname'];
$email=$_POST['email'];
$productid=$_POST['product'];
$product = count($productid);

if($whichdb==1){
    // ******************************************
    // POSTGRES
    // ******************************************

    // postgres connection
    $db_connection = pg_connect("host=$pghost dbname=octank user=$username password=$password");


    // postgres check if email exists if not insert it
    $result = pg_query($db_connection, "SELECT CUSTOMER_ID FROM CUSTOMERS WHERE EMAIL_ADDRESS = '".$email."'");
    $row = pg_fetch_row($result);

    if ($row) {
        $customerid = $row[0];
    }
    else {
        $result = pg_query($db_connection, "INSERT INTO CUSTOMERS(EMAIL_ADDRESS, FULL_NAME) VALUES('".$email."', '".$fname."') RETURNING CUSTOMER_ID");
        $row = pg_fetch_row($result);
        $customerid = $row[0];
    }

    // postgres create an order record
    $now = date("d-M-Y")." ".date("H:i:s");
    $result = pg_query($db_connection, "INSERT INTO ORDERS(ORDER_DATETIME, CUSTOMER_ID, ORDER_STATUS, STORE_ID) VALUES('".$now."', $customerid, 'COMPLETE', 1) RETURNING ORDER_ID");
    $row = pg_fetch_row($result);
    $orderid = $row[0];


    // postgres if there is a product in the post use that, otherwise generate random products
    if($product!=0){
        $lineitemid=0;
        $quantity=1;
        foreach ($productid as $value){
        $lineitemid=$lineitemid+1;
        

        $result = pg_query($db_connection, "SELECT UNIT_PRICE FROM PRODUCTS WHERE PRODUCT_ID = $value");
        $row = pg_fetch_row($result);
        $unitprice = $row[0];

        $result = pg_query($db_connection, "INSERT INTO ORDER_ITEMS VALUES ($orderid, $lineitemid, $value, $unitprice, $quantity)");
        $row = pg_fetch_row($result);
        }
            
    }
    else {
        $howmany = rand(1,4);

    for ($x = 1; $x <= $howmany; $x++) {

        $lineitemid = $x;
        $productid = rand(1,46);
        $quantity = rand(1,4);

        $result = pg_query($db_connection, "SELECT UNIT_PRICE FROM PRODUCTS WHERE PRODUCT_ID = $productid");
        $row = pg_fetch_row($result);
        $unitprice = $row[0];

        
        $result = pg_query($db_connection, "INSERT INTO ORDER_ITEMS VALUES ($orderid, $lineitemid, $value, $unitprice, $quantity)");
        $row = pg_fetch_row($result);
    }
        
        
    }

}

else {

    // ******************************************
    // ORACLE
    // ******************************************


    // oracle connection
    $conn = oci_connect($username, $password, $orahost);
    if (!$conn) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    };


    // oracle check if email exists if not insert it
    $sql = "SELECT CUSTOMER_ID FROM CUSTOMERS WHERE EMAIL_ADDRESS = '".$email."'";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    oci_commit($conn);
    $row = oci_fetch_row($stmt);

    if($row){ 

        $customerid = $row[0];
    }
    else {
        $sql = "INSERT INTO CUSTOMERS(EMAIL_ADDRESS, FULL_NAME)
            VALUES('".$email."', '".$fname."') RETURNING CUSTOMER_ID INTO :NEW_ID";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':NEW_ID', $newid, -1, SQLT_INT);
    oci_execute($stmt);
    oci_commit($conn);
    $customerid = $newid;

    }

    // oracle create an order record
    $now = date("d-M-Y")." ".date("H.i.s");
    $sql = "INSERT INTO ORDERS(ORDER_DATETIME, CUSTOMER_ID, ORDER_STATUS, STORE_ID)
            VALUES(to_timestamp('".$now."','DD-MON-YYYY HH24.MI.SS.FF'), $customerid, 'COMPLETE', 1) RETURNING ORDER_ID INTO :NEW_ID";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':NEW_ID', $newid, -1, SQLT_INT);
    oci_execute($stmt);
    oci_commit($conn);
    $orderid = $newid;


    // oracle if there is a product in the post use that, otherwise generate random products
    if($product!=0){
        $lineitemid=0;
        $quantity=1;
        foreach ($productid as $value){
        $lineitemid=$lineitemid+1;
        
        $sql = "SELECT UNIT_PRICE FROM PRODUCTS WHERE PRODUCT_ID = $value";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt);
        $row = oci_fetch_row($stmt);
        $unitprice = $row[0];

        $sql = "INSERT INTO ORDER_ITEMS VALUES ($orderid, $lineitemid, $value, $unitprice, $quantity)";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt);
        oci_commit($conn);
        }
            
    }
    else {
        $howmany = rand(1,4);

    for ($x = 1; $x <= $howmany; $x++) {

        $lineitemid = $x;
        $productid = rand(1,46);
        $quantity = rand(1,4);

        $sql = "SELECT UNIT_PRICE FROM PRODUCTS WHERE PRODUCT_ID = $productid";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt);
        $row = oci_fetch_row($stmt);
        $unitprice = $row[0];

        $sql = "INSERT INTO ORDER_ITEMS VALUES ($orderid, $lineitemid, $productid, $unitprice, $quantity)";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt);
        oci_commit($conn);
    }
        
        
    }

}

?>

<html>
<head>
<script>
function goBack() {
                window.history.back()
            }
            </script>
<style>
    table {
  border-collapse: collapse;
  width:100%;
}

table, th, td {
  border: 1px solid black;
}

.form-style-2{
	max-width: 100%;
	padding: 20px 12px 10px 20px;
	font: 13px Arial, Helvetica, sans-serif;
}
.form-style-2-heading{
	font-weight: bold;
	font-style: italic;
	border-bottom: 2px solid #ddd;
	margin-bottom: 20px;
	font-size: 15px;
	padding-bottom: 3px;
}
.form-style-2 label{
	display: block;
	margin: 0px 0px 15px 0px;
}
.form-style-2 label > span{
	width: 100px;
	font-weight: bold;
	float: left;
	padding-top: 8px;
	padding-right: 5px;
}
.form-style-2 span.required{
	color:red;
}
.form-style-2 .tel-number-field{
	width: 40px;
	text-align: center;
}
.form-style-2 input.input-field, .form-style-2 .select-field{
	width: 48%;	
}
.form-style-2 input.input-field, 
.form-style-2 .tel-number-field, 
.form-style-2 .textarea-field, 
 .form-style-2 .select-field{
	box-sizing: border-box;
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	border: 1px solid #C2C2C2;
	box-shadow: 1px 1px 4px #EBEBEB;
	-moz-box-shadow: 1px 1px 4px #EBEBEB;
	-webkit-box-shadow: 1px 1px 4px #EBEBEB;
	border-radius: 3px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	padding: 7px;
	outline: none;
}
.form-style-2 .input-field:focus, 
.form-style-2 .tel-number-field:focus, 
.form-style-2 .textarea-field:focus,  
.form-style-2 .select-field:focus{
	border: 1px solid #0C0;
}
.form-style-2 .textarea-field{
	height:100px;
	width: 55%;
}
.form-style-2 input[type=submit],
.form-style-2 input[type=button]{
	border: none;
	padding: 8px 15px 8px 15px;
	background: #FF8500;
	color: #fff;
	box-shadow: 1px 1px 4px #DADADA;
	-moz-box-shadow: 1px 1px 4px #DADADA;
	-webkit-box-shadow: 1px 1px 4px #DADADA;
	border-radius: 3px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
}
.form-style-2 input[type=submit]:hover,
.form-style-2 input[type=button]:hover{
	background: #EA7B00;
	color: #fff;

    
}   
.center {
  text-align: center;
  border: 3px solid orange;
}

 </style>
</head>
<body>

<div class="center">
          <p><h2>Your Octank Fashion Order Details</h2></p>
        </div>
<div class="form-style-2">
<table border='1' width=100%>
<tr><td>ORDER_ID</td><td>ORDER_DATE</td><td>ORDER_STATUS</td><td>CUSTOMER_ID</td><td>EMAIL_ADDRESS</td><td>FULL_NAME</td><td>QTY</td><td>UNIT_PRICE</td><td>TOTAL_PRICE</td><td>ITEM_DETAIL</td></tr>

<!-- ******************************************
POSTGRES
****************************************** -->


<?
if ($whichdb==1){
    $result = pg_query($db_connection, "SELECT * FROM CUSTOMER_ORDER_PRODUCTS WHERE ORDER_ID = $orderid");
        
        while ($row = pg_fetch_row($result)) {
            print "<tr>\n";
            foreach ($row as $item) {
                print "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
            }
        }
        print "</tr>\n";
}

// <!-- ******************************************
// ORACLE
// ****************************************** -->

else {
    $sql = "SELECT * FROM CUSTOMER_ORDER_PRODUCTS WHERE ORDER_ID = $orderid";
    $stid = oci_parse($conn, $sql);
    $r = oci_execute($stid);

    while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
        print "<tr>\n";
        foreach ($row as $item) {
            print "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
        }
        print "</tr>\n";
    }
    // print "</table>\n";

    

    oci_free_statement($stid);
    oci_close($conn);

}
?>






</table>
<p>&nbsp</p>
<input type="button" value="Return To Octank Fashion" onclick="goBack()"/>
</div>

</body>
</html>
