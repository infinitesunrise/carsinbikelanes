<html>

<head>
</head>

<body>
</body>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type='text/javascript'>
$(document).ready(function(){
	$.getJSON( "localhost/api/search",{
		box: [45,42,-75,-71]
	})
	.fail(function(e){ console.log('failed!:'); console.log(JSON.stringify(e)); })
	.done(function( response, status, jqXHR ){
		console.log(JSON.stringify(response, null, 2));
		$('body').html(JSON.stringify(response, null, 2));
	});
});
</script>

</html>