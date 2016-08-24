<html>

<style type="text/css">
body {
	background-color:rgb(150, 150, 150);
}

h1, h2 {
	margin-top: 0px;
	margin-bottom: -10px;
	text-align: center;
	font-size: 42px;
	font-family: 'Oswald', sans-serif;
}

h3 {
	margin-top: 0px;
	margin-bottom: 0px;
	font-size: 24px;
	font-family: 'Oswald', sans-serif;
}

p {
	word-wrap: break-word;
	text-align: center;
	font-size: 18px;
	font-family: 'Oswald', sans-serif;
}

p.tinytext {
	word-wrap: break-word;
	text-align: left;
	margin-top: 3px;
	font-size: 12px;
	font-family: 'Oswald', sans-serif;
}

span {
	font-size: 18px;
	font-family: 'Oswald', sans-serif;
}

input[type="text"], input[type="password"] {
	height: 24px;
	width: 100%;
}

input[type="submit"] {
	height: 36px;
	width: 100%;
	font-size: 18px;
	font-family: 'Oswald', sans-serif;
}

.flex_container {
	width: 100%;
	display: flex;
	justify-content: center;
}

.setup_centered {
	background-color:rgba(255, 255, 255, 0.9);
	width: 600px;
	padding: 10px;
	margin: 10px;
}

.settings_group {
	width: 400px;
	margin: 30px auto;
}
</style>

<!-- google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>

<body>

<div class="flex_container">
<div class="setup_centered">

<h2>WELCOME TO CARSINBIKELANES</h2>
<p>The open source web app for crowdsourced geo-located traffic violation reporting!<br>
Please fill in the following fields to configure your site.</p>

<form action="setup.php" method="post">

<div class="settings_group">
<h3>Administrator:</h3>
<span>username: </span><input type="text" name="username" placeholder="username"/><br>
<span>password: </span><input type="password" name="password1"/><br>
<span>type it again: </span><input type="password" name="password2"/><br>
<span>email (optional): </span><input type="text" name="email"/><br>
</div>

<div class="settings_group">
<h3>MySQL:</h3>
<span>hostname: </span><input type="text" name="sqlhost" placeholder="localhost"/><br>
<span>username: </span><input type="text" name="sqluser" placeholder="root"/><br>
<span>password: </span><input type="password" name="sqlpass" placeholder="root"/><br>
<span>database: </span><input type="text" name="database" placeholder="carsinbikelanes"/><br>
<p class="tinytext">Note: Database must not already exist.</p>
</div>

<div class="settings_group">
<input type="submit" value="SET UP"/>
</div>

</form>

</div>
</div>

</body>

</html>