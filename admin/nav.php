
<script type="text/javascript">
$(document).ready(function() {
	$("#queue").click( function() { window.location = "index.php"; } );
	$("#settings").click( function() { window.location = "settings.php"; } );
	$("#logout").click( function() { window.location = "index.php?logout=true"; } );
});
</script>

<div class="flex_container_nav">
<button id="queue">QUEUE</button>
<button id="settings">SETTINGS</button>
<button id="logout">LOG OUT</button>
</div>