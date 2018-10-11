<?php
function completeOrder($order_id) {
	//Charge credit card
	$transaction = new Transaction();
	$transaction->cardholder = $_POST['cardholder'];
	$transaction->number = $_POST['number'];
	$transaction->exp_month = $_POST['exp_month'];
	$transaction->exp_year = $_POST['exp_year'];
	$transaction->cvv = $_POST['cvv'];
	$transaction->type = $_POST['type'];

	//Check if transaction is success
	//Will be different based on transaction library
	if(!$transaction_id = $transaction->charge()) {
		echo "Error";
		return;
	} 

	// Set order_status as completed, set transaction_id
	$sql = "UPDATE order SET status_id = 2, transaction_id = $transaction_id WHERE order_id = $order_id";
	mysql_query($sql);
	
	$mail = new PHPMailer(true);
	//since we are setting true above, PHPMailer will throw exceptions we need to catch
	try {
		//Send email
		$mail->isSMTP();
		$mail->Host = 'smtp1.example.com;smtp2.example.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'user@example.com';
		$mail->Password = 'secret';
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;

		//Recipients
		$mail->setFrom('from@example.com', 'Mailer');

		$sql2 = "SELECT * FROM order WHERE order_id = " . $order_id;
		$order = mysql_query($sql3);
		$mail->addAddress($order['email'], $order['customer_name']);

		$mail->addReplyTo('info@example.com', 'Information');
		$mail->isHTML(true);
		$mail->Subject = 'Your order is complete!';
		$mail->Body    = 'Thank you for completing your order with us! Here\'s your transaction ID: '.$transaction_id;
		$mail->send();
	} catch (phpmailerException $e) {
		echo $e->errorMessage();
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
    echo "Okay!";
}
?>