<?php

$conn = oci_connect('hr', 'welcome', 'localhost/XE');
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
};


$fname=$_POST['fname'];
$email=$_POST['email'];

$sql = "SELECT CUSTOMER_ID FROM OTF.CUSTOMERS WHERE EMAIL_ADDRESS = '".$email."'";
$stmt = oci_parse($conn, $sql); 
oci_execute($stmt);
oci_commit($conn);
$row = oci_fetch_row($stmt);

if($row){

    $customerid = $row[0];
}
else {
    $sql = "INSERT INTO OTF.CUSTOMERS(EMAIL_ADDRESS, FULL_NAME) 
        VALUES('".$email."', '".$fname."') RETURNING CUSTOMER_ID INTO :NEW_ID";

$stmt = oci_parse($conn, $sql); 
oci_bind_by_name($stmt, ':NEW_ID', $newid, -1, SQLT_INT);
oci_execute($stmt);
oci_commit($conn);
$customerid = $newid;

}

$now = date("d-M-Y")." ".date("H.i.s");

$sql = "INSERT INTO OTF.ORDERS(ORDER_DATETIME, CUSTOMER_ID, ORDER_STATUS, STORE_ID) 
        VALUES(to_timestamp('".$now."','DD-MON-YYYY HH24.MI.SS.FF'), $customerid, 'COMPLETE', 1) RETURNING ORDER_ID INTO :NEW_ID";


$stmt = oci_parse($conn, $sql); 
oci_bind_by_name($stmt, ':NEW_ID', $newid, -1, SQLT_INT);
oci_execute($stmt);
oci_commit($conn);
$orderid = $newid;


$howmany = rand(1,4);

for ($x = 1; $x <= $howmany; $x++) {

    $lineitemid = $x;
    $productid = rand(1,46);
    $quantity = rand(1,4);
        
    $sql = "SELECT UNIT_PRICE FROM OTF.PRODUCTS WHERE PRODUCT_ID = $productid";
    $stmt = oci_parse($conn, $sql); 
    oci_execute($stmt);
    $row = oci_fetch_row($stmt);
    $unitprice = $row[0];
    
    $sql = "INSERT INTO OTF.ORDER_ITEMS VALUES ($orderid, $lineitemid, $productid, $unitprice, $quantity)";
    $stmt = oci_parse($conn, $sql); 
    oci_execute($stmt);
    oci_commit($conn);
}

    $sql = "SELECT * FROM OTF.CUSTOMER_ORDER_PRODUCTS WHERE ORDER_ID = $orderid";
    $stid = oci_parse($conn, $sql);
    $r = oci_execute($stid);

    print "<table border='1'>\n";
    print "<tr><td>ORDER_ID</td><td>ORDER_DATE</td><td>ORDER_STATUS</td><td>CUSTOMER_ID</td><td>EMAIL_ADDRESS</td><td>FULL_NAME</td><td>ORDER_TOTAL</td><td>ITEM_DETAIL</td></tr>";
    while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
        print "<tr>\n";
        foreach ($row as $item) {
            print "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
        }
        print "</tr>\n";
    }
    print "</table>\n";


    oci_free_statement($stid);



oci_close($conn);
