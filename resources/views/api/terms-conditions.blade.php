<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Terms & Conditions</title>
	<link href="<?php echo url('/'); ?>/assets/api/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo url('/'); ?>/assets/api/css/custom.css" rel="stylesheet">
</head>
<body>
	<div class="container" style="padding-top: 20px;">
	  <div class="term_section row">
      <div class="col-xl-1 col-lg-1 col-md-1 col-sm-12 col-12"></div>
      <div class="col-xl-10 col-lg-10 col-md-10 col-sm-12 col-12">
          <h1>Terms & Conditions</h1>
          <div class="term_contain">
            @if($get_data)
             {!! $get_data['description'] !!}
             @endif
          </div>
      </div>
      <div class="col-xl-1 col-lg-1 col-md-1 col-sm-12 col-12"></div>
    </div>
  </div>
</body>
</html>