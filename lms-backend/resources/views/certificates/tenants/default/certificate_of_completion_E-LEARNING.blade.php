<!DOCTYPE html>
<html>

<head>
	<link href="https://fonts.googleapis.com/css?family=Times+New+Roman&display=swap" rel="stylesheet" />
	<link href="./css/main.css" rel="stylesheet" />
	<title>Document</title>
	<style>
		#main_div {
			display: flex;
			justify-items: center;
			align-items: center;
			flex-direction: column;
		}

		.font-size-large {
			font-size: 30px;
		}

		.font-size-medium {
			font-size: 27px;
		}

		.font-size-small {
			font-size: 24px;
		}
	</style>
</head>

<body>
	<div id="main_div" style="-webkit-text-size-adjust: none; text-size-adjust: none; text-align: center; display: flex; flex-direction: column; justify-content:space-between">
		<div class="v243_3"></div>
		<div class="v243_4">
			<img width="120px" height="80" contain 
				src="{{ $logo ?? asset('img/certificate/default-logo.png') }}" 
			/>
		</div>
		
		<div class="v243_6"></div>
		<div class="v243_7" style="margin-top: 80px;"><span class="v243_8">This</span></div>
		<div class="v243_9" style="margin-top: 20px;"><span style="font-size: 30px; font-weight: 700;" >CERTIFICATE OF COMPLETION</span></div>
		<div class="v243_11" style="margin-top: 20px;"><span class="v243_12">is awarded to</span></div>
		
		@php
			$fontSizeName = 'font-size-large';

			if (strlen($name) > 50) {
				$fontSizeName = 'font-size-small';
			} elseif (strlen($name) > 30) {
				$fontSizeName = 'font-size-medium';
			}
		@endphp
		
		<div class="v243_13" style="margin-top: 20px;"><span  class="{{ $fontSizeName }}" style="font-weight: 700;">{{ $name }}</span></div>
		<div class="v243_15" style="margin-top: 20px;"><span class="v243_16">for having successfully completed the training on</span></div>
		
		@php
			$fontSizeCourseTitle = 'font-size-large'; // Default

			if (strlen($course_title) > 50) {
				$fontSizeCourseTitle = 'font-size-small';
			} elseif (strlen($course_title) > 30) {
				$fontSizeCourseTitle = 'font-size-medium';
			}
		@endphp

		<div class="v243_17"  style="margin-top: 20px;"><span class="{{ $fontSizeCourseTitle }}" class="v243_18" style="margin-top: 20px; font-weight: 700;">{{ $course_title }}</span></div>

		<?php
			$formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
			$trainingHoursWords = $formatter->format($training_hours);
		?>

		<div class="v243_19"  style="margin-top: 20px;"><span class="v243_20" >
			
			@if(!empty($training_hours))
				( credit of {{$trainingHoursWords}} [{{$training_hours}}]
				@if($training_hours === 1)
				hour
				@else
				hours
				@endif
				{{$program}} )
			@endif
		</span></div>

		<div class="v243_21" style="margin-top: 20px;"><span class="v243_22">conducted by Aster Academy.</span></div>
		<div class="v243_23"></div>
		<div class="v243_24"  style="margin-top: 30px;"><span class="v243_25">Given this {{ date('jS \d\a\y \o\f F Y', strtotime($issue_date)) }}.</span></div>
		<div class="v243_26"></div>

		
		<div style="position: absolute; bottom: 0; margin-bottom: 80px; width: 665px;">
			<div class="v243_27">
				@if($signatory_esign)
					<img width="110px" height="40" src="{{ $signatory_esign }}" />
				@endif
				<div class="v244_242"> {{$signatory_name}} </div>
				<div class="v244_242" style="font-size: 12px"> {{$signatory_designation}} </div>
				<div class="v244_242" style="font-size: 12px"> {{$signatory_info}} </div>
			</div>

			@if($attestor_esign)
				<div class="v243_33" style="margin-top: 30px;">
					<div class="v243_88">Attested by:</div>
					@if($attestor_esign)
						<img width="110px" height="40" src="{{ $attestor_esign }}" />
					@endif
					<div class="v244_242">{{ $attestor_name }}</div>
					<div class="v244_242" style="font-size: 12px">{{ $attestor_designation }}</div>
					<div class="v244_242" style="font-size: 12px">{{ $attestor_info }}</div>
				</div>
			@endif

			
		</div>

		<div style="height:80px;line-height:100px;font-size:1px;margin-bottom: 10px; position: absolute; bottom: 0;">{!! $qrcodePDF !!}</div>
	</div>
</body>

</html>