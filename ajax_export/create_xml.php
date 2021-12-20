<?php
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Дата в прошлом
    header("Content-Length: 10000");

	require_once('../classes/DocpartCongif.php');
	require_once('../classes/DocpartNotification.php');
	require_once('../classes/DocpartSms.php');
	require_once('../classes/DocpartGeo.php');
	require_once('../classes/DocpartOffice.php');
	require_once('../classes/DocpartStorage.php');
	require_once('../classes/DocpartOrdersStatusesRef.php');
	require_once('../classes/DocpartOrdersItemsStatusesRef.php');
	require_once('../classes/DocpartOrder.php');
	require_once('../classes/DocpartCarts.php');
	require_once('../classes/DocpartUserFinance.php');
	require_once('../classes/DocpartFinanceCodes.php');
	require_once('../classes/DocpartPrices.php');
	require_once('../classes/DocpartPaymentSystem.php');
	require_once('../classes/DocpartPrintDocs.php');
	require_once('../classes/DocpartTextUrl.php');
	require_once('../classes/DocpartMenu.php');
	require_once('../classes/DocpartGroup.php');
	require_once('../classes/DocpartRegVariants.php');
	require_once('../classes/DocpartRegFields.php');
	require_once('../classes/DocpartAnalogList.php');
	require_once('../classes/DocpartManufacturers.php');
	require_once('../classes/DocpartUser.php');
	

	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();

	$fxml = fopen('db.xml','w');
	fwrite($fxml, '<?xml version="1.0" encoding="UTF-8"?>');
	fwrite($fxml, '<root>');
	fwrite($fxml, '<db_version>');
	fwrite($fxml, '1.0');
	fwrite($fxml, '</db_version>');
	$config = new DocpartConfig();
	$config->printTag('config', $fxml);
	$notify = new DocpartNotification(); 
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$notify->getDataBySql('*','`notifications_settings`', $fxml, 'notify', 'notifications');
	$sms = new DocpartSms();
	$sms->getDataBySql('*','`sms_api`', $fxml,'sms', null, '`active` = 1');
	$geo = new DocpartGeo();
	$geo->getDataBySql('*','`shop_geo`', $fxml, 'node','geo');
	$office = new DocpartOffice();
	$office->getDataBySql('*','`shop_offices`', $fxml, 'office','offices');
	$storage = new DocpartStorage();
	$storage->getDataBySql('*', '`shop_storages`', $fxml, 'storage', 'storages');
	$order_status_ref = new DocpartOrdersStatusesRef();
	$order_status_ref->getDataBySql('*', 'shop_orders_statuses_ref', $fxml, 'status', 'orders_statuses');
	$orders_items_statuses_ref = new DocpartOrdersItemsStatusesRef();
	$orders_items_statuses_ref->getDataBySql('*', 'shop_orders_items_statuses_ref', $fxml, 'status', 'orders_items_statuses');
	$orders = new DocpartOrder();
	$orders->getDataBySql('*', '`shop_orders`', $fxml, 'order', 'orders' );
	$carts = new DocpartCarts();
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$carts->getDataBySql('*', '`shop_carts`', $fxml, 'item', 'carts');
	$finance = new DocpartUserFinance();
	$finance->getDataBySql('*', '`shop_users_accounting`', $fxml, 'item', 'users_finance');
	$finance_codes = new DocpartFinanceCodes();
	$finance_codes->getDataBySql('*', '`shop_accounting_codes`', $fxml, 'item', 'finance_codes');
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$prices = new DocpartPrices();
	$prices->getDataBySql('*', '`shop_docpart_prices`', $fxml, 'price', 'prices');
	$payment_system = new DocpartPaymentSystem();
	$where_condition = '`active` = 1';
	$payment_system->getDataBySql('`handler`, `parameters_values`', '`shop_payment_systems`', $fxml, 'payment_system', null, $where_condition);
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$print_docs = new DocpartPrintDocs();
	$print_docs->getDataBySql('`name`,`parameters_values`', 'shop_print_docs', $fxml, 'doc', 'print_docs');
	//content
	//....
	$url = new DocpartTextUrl();
	$url->getDataBySql('*', '`text_for_url`', $fxml, 'item', 'text_for_url');
	$menu = new DocpartMenu();
	$menu->getDataBySql('*','`menu`',$fxml, 'item', 'menu');
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$groups = new DocpartGroup();
	$groups->getDataBySql('*', '`groups`', $fxml, 'group', 'users_groups');
	$reg_variants = new DocpartRegVariants();
	$reg_variants->getDataBySql('*', '`reg_variants`', $fxml, 'item', 'reg_variants');
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$reg_fields = new DocpartRegFields();
	$reg_fields->getDataBySql('*', '`reg_fields`', $fxml, 'item', 'reg_fields');
	$users = new DocpartUser();
	$users->getDataBySql('*','`users`', $fxml, 'user', 'users');
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$analogs = new DocpartAnalogList();
	$analogs->getDataBySql('*', '`shop_docpart_articles_analogs_list`', $fxml, 'item', 'parts_crosses');
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
	$manufacturers = new DocpartManufacturers();
	$manufacturers->getDataBySql('*', '`shop_docpart_manufacturers`', $fxml, 'manufacturer', 'manufacturers');
		
	fwrite($fxml, '</root>');
	$parcel = str_repeat('.', 1000);
	echo $parcel;
	flush();
	ob_flush();
    
	$awnser = [];
	$awnser["status"] = '200';
	$awnser["data"] = 'Successful complete.';
	exit(json_encode($awnser));
	
?>