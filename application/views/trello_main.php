<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Trello Report to Excel</title>
	<link rel="stylesheet" href="<?=base_url();?>assets/css/lightpick.css">
	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
		padding-left: 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
</head>
<body>

<div id="container">
	<h1>Trello Report to Excel</h1>

	<div id="body">
		<form action="trello/download/" method="POST">
			Trello:
			<p>
				<input type="text" name="api_key" placeholder="API_KEY">
				<input type="password" name="token" placeholder="ACCESS_TOKEN">
				<input type="text" name="board_id" placeholder="BOARD_ID">
				<input type="text" name="list_name" placeholder="LIST_NAME">
			</p>
			Download Report:
			<p>
				<input type="text" class="TinyPicker form-control" id="startDate" name="startDate" value="10<?=date('/m/Y', strtotime("-1 month"))?>">
				<input type="text" class="TinyPicker form-control" id="endDate" name="endDate" value="10<?=date('/m/Y')?>">
			</p>
			<p><input type="submit" value="DOWNLOAD"></p>
		</form>
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>
<script src="<?=base_url();?>lightpick.js"></script>
<script type="text/javascript">
const myPicker = new lightPick({
	field: document.getElementById('startDate'),
	secondField: document.getElementById('endDate')
});
</script>
</body>
</html>
