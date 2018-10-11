<?php
include "const.php";
include "db.php";

// $_POST
if (array_key_exists('company_id', $_POST) && $_POST['company_id'] <> 0) {
	$result = updateCompanyLegacy($_POST);
} elseif (!empty($_POST)) {
	$result = addCompany($_POST);
}

// $_GET
if (array_key_exists('company_id', $_GET) && $_GET['company_id'] <> 0) {
	// protect against SQL injection
	$company_id = mysql_real_escape_string($_GET['company_id']);
	$sql = "SELECT * FROM company WHERE company_id = $company_id";
	$result = mysql_query($sql);
}

function updateCompanyPDO($data)
{
	// We need to use {} in order to use an array in a string i.e. "{$_POST['name']}"
	// But more importantly we need to protect against SQL injection
	// pdo will naturally protect against sql injection attacks by using prepared statements
	$stmt = $pdo->prepare("UPDATE company SET name = :name, address = :address WHERE company_id = :company_id");
	$stmt->execute([
		'name' => $data['name'],
		'address' => $data['address'],
		'company_id' => $data['company_id']
	]);
}

function updateCompanyLegacy($data)
{
	// for legacy code we can use the deprecated mysql_real_escape_string
	$name = mysql_real_escape_string($data['name']);
	$address = mysql_real_escape_string($data['address']);
	$company_id = mysql_real_escape_string($data['company_id']);
	$sql = "UPDATE company SET name = '$name', address = '$address' WHERE company_id = $company_id";
	return mysql_query($sql);
}

function addCompany($data)
{
	// protect against SQL injection
	$name = mysql_real_escape_string($data['name']);
	$address = mysql_real_escape_string($data['address']);
	$sql = "INSERT INTO company (name, address) VALUES ($name, $address)";
	return mysql_query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="description" content="This is an example of HTML5 header element. header element is the head of a new section." />
		<title>Company</title>
	</head>
<body>
<h1>
	<?php !empty($_GET['company_id']) ? "Edit" : "Add" ?> your company
</h1
<form action="messy-code.php" action="post">
	<label>Name</label>
	<input type="text" name="name" value="<?php echo isset($result) ? $result['name'] : ''?>" />
	<br />
	<label>Address</label>
	<input type="text" name="address" value="<?php echo isset($result) ? $result['address'] : ''?>" />
	<br />
	<input type="submit" name="submit" value="submit" />
</form>
</body>
</html>