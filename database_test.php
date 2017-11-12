<?php

require "Database.php";

//CONNECTION
$db = new Database('localhost','root','','db_name');

//INSERT
$role = array('role_name' => 'test1', 'role_desc' => 'Rola test');
$insert = $db->insert('roles', $role);
if ($insert) echo nl2br("INSERT - OK (Affected rows: {$db->getResult()})" . PHP_EOL);

$role = array('role_name' => 'test2', 'role_desc' => 'Rola test');
$insert = $db->insert('roles', $role);
if ($insert) echo nl2br("INSERT - OK (Affected rows: {$db->getResult()})" . PHP_EOL);

$role = array('role_name' => 'test3', 'role_desc' => 'Rola test');
$insert = $db->insert('roles', $role);
if ($insert) echo nl2br("INSERT - OK (Affected rows: {$db->getResult()})" . PHP_EOL);

//UPDATE
$role = array('role_name' => 'test4', 'role_desc' => 'Rola test pozwala testować stronę.');
$update = $db->update('roles','role_name','test3', $role);
if ($update) echo nl2br("UPDATE - OK (Affected rows: {$db->getResult()})" . PHP_EOL);

//DELETE
$delete = $db->delete('roles','role_name','test4');
if ($delete) echo nl2br("DELETE - OK (Affected rows: {$db->getResult()})" . PHP_EOL);

//SELECT
$select = $db->select('roles', array('*'),'role_desc', 'Rola test', 'role_name DESC', 10);
if ($select){
    $fetched_select = $db->fetch_results();
    echo nl2br("SELECT - OK ({$db->get_num_rows()})" . PHP_EOL);
    echo '<pre>';
    print_r ($fetched_select);
    echo '</pre>';
}

//CUSTOM QUERY
$query = "DELETE FROM roles WHERE role_name REGEXP '^test'";
$delete = $db->custom_query($query);
if ($delete) echo nl2br("CUSTOM QUERY - OK (Affected rows: {$db->getResult()})" . PHP_EOL);
