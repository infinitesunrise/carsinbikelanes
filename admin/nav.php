
<script type="text/javascript">
$(document).ready(function() {
	$("#queue").click( function() { window.location = "index.php"; } );
	$("#edit").click( function() { window.location = "edit.php"; } );
	$("#settings").click( function() { window.location = "settings.php"; } );
	$("#logout").click( function() { window.location = "index.php?logout=true"; } );
});
</script>

<div class="flex_container_nav  box_shadow2">
<button id="queue">QUEUE</button>
<?php
if (isset($_SESSION['admin'])){
	if ($_SESSION['admin'] == true){
		echo "<button id=\"edit\">EDIT</button> \n";
	}
}
?>
<button id="settings">SETTINGS</button>
<button id="logout">LOG OUT</button>
</div>