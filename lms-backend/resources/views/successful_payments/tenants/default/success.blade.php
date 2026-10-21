<!DOCTYPE html>

<html>
<head>
<title></title>
<meta content="summary_large_image" name="twitter:card"/>
<meta content="website" property="og:type"/>
<meta content="" property="og:description"/>
<meta content="https://n7cdk45cik.preview-postedstuff.com/V2-RpkA-dfwJ-cx4f-dykv/" property="og:url"/>
<meta content="https://pro-bee-beepro-thumbnail.getbee.io/messages/945036/929509/1858571/8971877_large.jpg" property="og:image"/>
<meta content="" property="og:title"/>
<meta content="" name="description"/>
<meta charset="utf-8"/>
<meta content="width=device-width" name="viewport"/>
<style>
		.bee-row,
		.bee-row-content {
			position: relative
		}

		.bee-row-1,
		.bee-row-1 .bee-row-content {
			background-repeat: no-repeat
		}

		body {
			background-color: #fff;
			color: #000;
			font-family: Arial, Helvetica, sans-serif
		}

		.bee-row-1 .bee-col-1 .bee-block-6 a,
		a {
			color: #8a3c90
		}

		* {
			box-sizing: border-box
		}

		body,
		h1,
		p {
			margin: 0
		}

		.bee-row-content {
			max-width: 1280px;
			margin: 0 auto;
			display: flex
		}

		.bee-row-content .bee-col-w12 {
			flex-basis: 100%
		}

		.bee-button .content {
			text-align: center
		}

		.bee-button a {
			text-decoration: none
		}

		.bee-image {
			overflow: auto
		}

		.bee-image .bee-center {
			margin: 0 auto
		}

		.bee-row-1 .bee-col-1 .bee-block-2 {
			width: 100%
		}

		.bee-image img {
			display: block;
			width: 100%
		}

		.bee-paragraph {
			overflow-wrap: anywhere
		}

		.bee-row-1 .bee-row-content {
			color: #000
		}

		.bee-row-1 .bee-col-1 {
			padding-bottom: 5px;
			padding-top: 5px
		}

		.bee-row-1 .bee-col-1 .bee-block-5 {
			padding: 10px;
			text-align: center;
			width: 100%
		}

		.bee-row-1 .bee-col-1 .bee-block-6 {
			padding: 10px;
			color: #101112;
			direction: ltr;
			font-size: 22px;
			font-weight: 400;
			letter-spacing: 0;
			line-height: 150%;
			text-align: center
		}

		.bee-row-1 .bee-col-1 .bee-block-10,
		.bee-row-1 .bee-col-1 .bee-block-8 {
			padding: 10px;
			text-align: center
		}

		.bee-row-1 .bee-col-1 .bee-block-6 p:not(:last-child) {
			margin-bottom: 16px
		}

		@media (max-width:768px) {
			.bee-row-content:not(.no_stack) {
				display: block
			}

			.bee-row-1 .bee-col-1 .bee-block-5 h1 {
				font-size: 33px !important
			}

			.bee-row-1 .bee-col-1 .bee-block-6 {
				padding: 10px 30px !important;
				text-align: center !important;
				font-size: 16px !important
			}
		}
	</style>
</head>
<body>
<div class="bee-page-container">
<div class="bee-row bee-row-1">
<div class="bee-row-content">
<div class="bee-col bee-col-1 bee-col-w12">
<div class="bee-block bee-block-1 bee-spacer">
<div class="spacer" style="height:105px;"></div>
</div>
<div class="bee-block bee-block-2 bee-image"><img alt="" class="bee-center bee-autowidth" src="{{ asset('img/email/logo.png') }}" style="max-width:174px;"/></div>
<div class="bee-block bee-block-3 bee-spacer">
<div class="spacer" style="height:95px;"></div>
</div>
<div class="bee-block bee-block-4 bee-spacer">
<div class="spacer" style="height:15px;"></div>
</div>
<div class="bee-block bee-block-5 bee-heading">
<h1 style="color:#b52210;direction:ltr;font-family:Arial, Helvetica, sans-serif;font-size:47px;font-weight:700;letter-spacing:normal;line-height:120%;text-align:center;margin-top:0;margin-bottom:0;"><span class="tinyMce-placeholder">Congratulations! <br/>Payment Successful</span> </h1>
</div>
<div class="bee-block bee-block-6 bee-paragraph">
<p>This confirms your participation to the {{ $course_list }}. </p>
</div>
<div class="bee-block bee-block-7 bee-spacer">
<div class="spacer" style="height:45px;"></div>
</div>
<div class="bee-block bee-block-8 bee-button">
    <form id="request-or" action="{{ config('services.payment_gateway.url') }}/getReceipt" method="POST" target="_blank">
        <input type="hidden" name="gateway_ref_id" value="{{ $gateway_ref_id }}">
        <input type="hidden" name="ref_id" value="{{ $ref_id }}">
        <input type="hidden" name="hash" value="{{ $hash }}">
    </form>
    <a onclick="document.getElementById('request-or').submit(); return false;" class="bee-button-content" href="javascript:void(0)" style="font-size: 20px; background-color: #e05835; border-bottom: 0px solid transparent; border-left: 0px solid transparent; border-radius: 4px; border-right: 0px solid transparent; border-top: 0px solid transparent; color: #ffffff; direction: ltr; font-family: inherit; font-weight: 400; max-width: 100%; padding-bottom: 5px; padding-left: 20px; padding-right: 20px; padding-top: 5px; width: auto; display: inline-block;" target="_blank">
        <span dir="ltr" style="word-break: break-word; font-size: 20px; line-height: 200%;">Download Official Receipt</span>
    </a>
</div>
<div class="bee-block bee-block-9 bee-spacer">
<div class="spacer" style="height:10px;"></div>
</div>
<div class="bee-block bee-block-10 bee-button"><a class="bee-button-content" href="{{ $back_url }}" style="font-size: 20px; background-color: transparent; border-bottom: 0px solid transparent; border-left: 0px solid transparent; border-radius: 4px; border-right: 0px solid transparent; border-top: 0px solid transparent; color: #e05835; direction: ltr; font-family: inherit; font-weight: 400; max-width: 100%; padding-bottom: 5px; padding-left: 20px; padding-right: 20px; padding-top: 5px; width: auto; display: inline-block;" target="_blank"><span dir="ltr" style="word-break: break-word; font-size: 20px; line-height: 200%;">Back to home page</span></a></div>
<div class="bee-block bee-block-11 bee-spacer">
<div class="spacer" style="height:85px;"></div>
</div>
</div>
</div>
</div>
</div>
</body>
</html>