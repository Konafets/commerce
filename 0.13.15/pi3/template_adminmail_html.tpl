<!--###MAILCONTENT### begin-->
<html>
<head>
	<title>SHOP ORDER: ###ORDERID###</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<body bgcolor="#ffffff" font-size="10px" leftmargin="0" topmargin="10" rightmargin="0" bottommargin="10" marginwidth="0" marginheight="0" link="#000000" alink="#000000" vlink="#000000">
<table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top" align="center">
<!-- center table  begin -->
<table align="center" width="600" cellspacing="5" cellpadding="5" border="0">
	<tr>	
		<td width="600" valign="top" align="left">
			<img src="typo3conf/ext/commerce/res/logo/mail_logo.gif" width="200" height="77" alt="TYPO3 Commerce" />
		</td>
	</tr>	
</table>
		
<table align="center" width="600" cellspacing="5" cellpadding="5" border="0">
	<tr>	
		<td width="600" valign="top">
		<h1>Ordernumber ###ORDERID###</h1>	
		<p><strong>Commentary:</strong><br/> ###COMMENT###</p>
		<!--###BASKET_VIEW### begin -->
		<table cellspacing="5" cellpadding="5" border="0">
			<thead>
				<tr>
					<th>###LANG_ARTICLE_NUMBER###</th>
					<th>###LANG_TITLE###</th>
					<th>###LANG_PRICE_GROSS###</th>
					<th>###LANG_COUNT###</th>
					<th>###LANG_PRICESUM_GROSS###</th>
				</tr>
			</thead>
			<tbody>
		<!--###LISTING_ARTICLE### begin-->
		<tr>
				<td>###ARTICLE_ORDERNUMBER###</td>
				<td>###PRODUCT_TITLE###</td>
				<td>###BASKET_ITEM_PRICEGROSS###</td>
				<td>###BASKET_ITEM_COUNT###</td>
				<td>###BASKET_ITEM_PRICESUM_GROSS###</td>
		</tr>
		<!--###LISTING_ARTICLE### end-->
		
		<!--###LISTING_BASKET_WEB### begin-->
			<tr>
				<td colspan="4">###SHIPPING_TITLE###</td>
				<td>###SUM_SHIPPING_NET###</td>
			</tr>
			<tr>
				<td colspan="4">###PAYMENT_TITLE###</td>
				<td>###SUM_PAYMENT_GROSS###</td>
			</tr>
			<tr>
				<td colspan="4">###LABEL_SUM_ARTICLE_GROSS###</td>
				<td>###SUM_ARTICLE_GROSS###</td>
			</tr>
			<tr>
				<td colspan="4">###LABEL_SUM_TAX###</td>
				<td>###SUM_TAX###</td>
			</tr>
			<!--###TAX_RATE_SUMS### begin -->
				<tr>
					<td colspan="4">###LABEL_SUM_TAX### ###TAX_RATE######LABEL_PERCENT###</td>
					<td>###TAX_RATE_SUM###</td>
				</tr>
			<!--###TAX_RATE_SUMS### end -->
			<tr class="com-chkout-sum">
				<td colspan="4">###LABEL_SUM_GROSS###</td>
				<td>###SUM_GROSS###</td>
			</tr>
		<!--###LISTING_BASKET_WEB### end-->
		</table>
		<!--###BASKET_VIEW### end -->
		
		<table align="left" cellspacing="5" cellpadding="5" border="0">
			<tr>
				<td width="300">
				<strong>###LANG_BILLING_TITLE###</strong><br/>
				<!--###BILLING_ADDRESS### begin-->
				###COMPANY###<br>
				###NAME### ###SURNAME###<br>
				###ADDRESS###<br>
				###ZIP### ###CITY###<br>
				###COUNTRY###<br>
				<!--###BILLING_ADDRESS### end-->		
				</td>
				<td width="300" >
				<strong>###LANG_DELIVERY_TITLE###</strong><br/>
				<!--###DELIVERY_ADDRESS### begin-->
				###COMPANY###<br/>
				###NAME### ###SURNAME###<br/>
				###ADDRESS###<br/>
				###ZIP### ###CITY###<br/>
				###COUNTRY###<br/>
				<!--###DELIVERY_ADDRESS### end--> 			
				</td>
			</tr>	
		</table>
<!-- center table  end -->	
		</td>	
	</tr>
</table>
</body>
</html>
<!--###MAILCONTENT### end-->