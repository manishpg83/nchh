@extends('layouts.app')

@section('content')
<section class="bg-grey padding pt-5 thankyou_container">
    <div class="container-fluid">
     <header class="site-header" id="header">
		<h1 class="site-header__title" data-lead-id="site-header-title">THANK YOU!</h1>
	</header>

	<div class="main-content">
     	<i class="fa fa-check main-content__checkmark" id="checkmark"></i>
        <p class="main-content__body" data-lead-id="main-content-body">Thanks a bunch for filling that out. It means a lot to us, just like you do! We really appreciate you giving us a moment of your time today. Thanks for being you.</p>
    </div>
    </div>
</section>
@endsection
@section('page_script')
<script type="text/javascript">
</script>
@endsection