<!DOCTYPE html>

<html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<title></title>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/><!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml><![endif]-->
<style>
		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			padding: 0;
		}

		a[x-apple-data-detectors] {
			color: inherit !important;
			text-decoration: inherit !important;
		}

		#MessageViewBody a {
			color: inherit;
			text-decoration: none;
		}

		p {
			line-height: inherit
		}

		.desktop_hide,
		.desktop_hide table {
			mso-hide: all;
			display: none;
			max-height: 0px;
			overflow: hidden;
		}

		.image_block img+div {
			display: none;
		}
		.background_block {
			background-image: url('{{ asset('img/certificate/certificate-bg.png') }}'); 
			background-repeat: no-repeat; 
			background-size: contain; 
			border-radius: 0; 
			color: #000000; 
			width: 900px; 
			margin: 0 auto; 
			background-position: center
		}

		@media (max-width:768px) {
			.background_block {
				background-size: cover; 
			}

			.mobile_hide {
				display: none;
			}

			.row-content {
				width: 100% !important;
			}

			.stack .column {
				width: 100%;
				display: block;
			}

			.mobile_hide {
				min-height: 0;
				max-height: 0;
				max-width: 0;
				overflow: hidden;
				font-size: 0px;
			}

			.desktop_hide,
			.desktop_hide table {
				display: table !important;
				max-height: none !important;
			}
		}

		
		@media (max-width:768px) {

			.row-2 .column-1 .block-5.heading_block h1,
			.row-3 .column-1 .block-1.heading_block h1 {
				font-size: 22px !important;
			}

			.row-2 .column-1 .block-2.heading_block h1 {
				font-size: 28px !important;
			}

			.row-3 .column-1 .block-5.heading_block h1 {
				font-size: 18px !important;
			}
		}
		
		.font-size-large { font-size: 30px; }
		.font-size-medium { font-size: 27px; }
		.font-size-small { font-size: 24px; }
	</style><!--[if true]><style>.forceBgColor{background-color: white !important}</style><![endif]-->
</head>
<body class="forceBgColor" style="background-color: transparent; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none;">
<table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: transparent;" width="100%">
<tbody>
<tr>
<td>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-1" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
<tbody>
<tr>
<td>
<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack background_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="900">
<tbody>
<tr>
<td class="column column-1" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;" width="100%">
<div class="spacer_block block-1" style="height:200px;line-height:115px;font-size:1px;"> </div>
<table border="0" cellpadding="5" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
<tr>
<td class="pad">
<div style="color:#101112;direction:ltr;font-family:TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif;font-size:17px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:20.4px;">
<p style="margin: 0;">This</p>
</div>
</td>
</tr>
</table>
<table border="0" cellpadding="10" cellspacing="0" class="heading_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
<tr>
<td class="pad">
<h1 style="margin: 0; color: #101112; direction: ltr; font-family: TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif; font-size: 30px; font-weight: 700; letter-spacing: normal; line-height: 100%; text-align: center; margin-top: 0; margin-bottom: 0; mso-line-height-alt: 20px;"><span class="tinyMce-placeholder">CERTIFICATE OF COMPLETION <br/></span></h1>
</td>
</tr>
</table>
<table border="0" cellpadding="5" cellspacing="0" class="paragraph_block block-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
<tr>
<td class="pad">
<div style="color:#101112;direction:ltr;font-family:TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif;font-size:17px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:20.4px;">
<p style="margin: 0;">is awarded to</p>
</div>
</td>
</tr>
</table>
<table border="0" cellpadding="20" cellspacing="0" class="heading_block block-6" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
<tr>
<td class="pad">
@php
    $fontSizeName = 'font-size-large'; // Default

    if (strlen($name) > 50) {
        $fontSizeName = 'font-size-small';
    } elseif (strlen($name) > 30) {
        $fontSizeName = 'font-size-medium';
    }
@endphp
<h1 class="{{ $fontSizeName }}"style="margin: 0; color: #101112; direction: ltr; font-family: TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif; font-weight: 700; letter-spacing: normal; line-height: 80%; text-align: center; margin-top: 0; margin-bottom: 0; mso-line-height-alt: 20px;"><span class="tinyMce-placeholder">{{ $name }}<br/></span></h1>
</td>
</tr>
</table>
<table border="0" cellpadding="5" cellspacing="0" class="paragraph_block block-7" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
<tr>
<td class="pad">
<div style="color:#101112;direction:ltr;font-family:TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif;font-size:17px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:20.4px;">
<p style="margin: 0;">for having successfully completed the conduct of the</p>
</div>
</td>
</tr>
</table>
<table border="0" cellpadding="10" cellspacing="0" class="heading_block block-8" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
<tr>
<td class="pad">
@php
    $fontSizeCourseTitle = 'font-size-large'; // Default

    if (strlen($course_title) > 50) {
        $fontSizeCourseTitle = 'font-size-small';
    } elseif (strlen($course_title) > 30) {
        $fontSizeCourseTitle = 'font-size-medium';
    }
@endphp
<h1 class="{{ $fontSizeCourseTitle }}" style="margin: 0; color: #101112; direction: ltr; font-family: TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif; font-weight: 700; letter-spacing: normal; line-height: 120%; text-align: center; margin-top: 0; margin-bottom: 0; mso-line-height-alt: 36px;"><span class="tinyMce-placeholder"> {{ $course_title }}<br/></span></h1>
</td>
</tr>
</table>
<table border="0" cellpadding="5" cellspacing="0" class="paragraph_block block-9" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
<tr>
<td class="pad">
<div style="color:#101112;direction:ltr;font-family:TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif;font-size:15px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:20.4px;">
<?php
	$formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
	$trainingHoursWords = $formatter->format($training_hours);
?>
<p style="margin: 0; text-transform: lowercase;"><em>
@if(!empty($training_hours))
( credit of {{$trainingHoursWords}} [{{$training_hours}}]
@if($training_hours === 1)
hour
@else
hours
@endif
 {{$program}} )
 @endif</em></p>
</div>
</td>
</tr>
</table>
<table border="0" cellpadding="5" cellspacing="0" class="paragraph_block block-11" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
<tr>
<td class="pad">
<div style="color:#101112;direction:ltr;font-family:TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif;font-size:17px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:20.4px;">
<p style="margin: 0; margin-bottom: 10px;">conducted by Aster Academy</p>
<p style="margin: 0;">
    @if(!empty($date_conducted) && isset($date_conducted['start_date'], $date_conducted['end_date']))
        @php
            $startDate = strtotime($date_conducted['start_date']);
            $endDate = strtotime($date_conducted['end_date']);
        @endphp
		on
        @if(date('Y', $startDate) == date('Y', $endDate))
            @if($date_conducted['start_date'] == $date_conducted['end_date'])
                {{ date('F d, Y', $endDate) }}
            @else
                {{ date('F d', $startDate) . ' - ' . date('F d, Y', $endDate) }}
            @endif
        @else
            {{ date('F d, Y', $startDate) . ' - ' . date('F d, Y', $endDate) }}
        @endif
    @else
        on {{ date('F d, Y', strtotime($created_at)) }}
    @endif
</p>
</div>
</td>
</tr>
</table>
<div class="spacer_block block-12" style="height:10px;line-height:20px;font-size:1px;"> </div>
<table border="0" cellpadding="5" cellspacing="0" class="paragraph_block block-13" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
<tr>
<td class="pad">
<div style="color:#101112;direction:ltr;font-family:TimesNewRoman, 'Times New Roman', Times, Beskerville, Georgia, serif;font-size:17px;font-weight:400;letter-spacing:0px;line-height:120%;text-align:center;mso-line-height-alt:20.4px;">
<p style="margin: 0;">Given this {{ date('jS \d\a\y \o\f F Y', strtotime($issue_date)) }}.</p>
</div>
</td>
</tr>
</table>
<div class="spacer_block block-15" style="height:315px;line-height:325px;font-size:1px;"> </div>
<div class="spacer_block block-16" style="height:80px;line-height:100px;font-size:1px;margin-bottom: 10px; position: absolute; bottom: 0;">{!! $qrcodePDF !!}</div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table><!-- End -->
</body>
</html>