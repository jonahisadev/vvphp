<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>404</title>

	<style>
		body {
			background-color: #EFEFEF;
			font-family: Arial, sans-serif;
		}

		footer {
			position: fixed;
			bottom: 0;
			width: 100%;
			left: 0;
		}

		.center {
			text-align: center;
		}

		.box {
			border: 1px solid black;
			background-color: white;
			width: 50%;
			margin-left: 25%;
		}
	</style>
</head>
<body>
	<div class="center">
		<div class="box">
			<h1>404 - Not Found</h1>
			<h2>No File or Directory: <?= $URL ?></h2>
			<p><i>Sorry :(</i></p>
		</div>

		<footer>vvPHP v0.1</footer>
	</div>
</body>
</html>