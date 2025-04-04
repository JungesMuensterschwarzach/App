<?php
	require_once("./assets/global_requirements.php");

	if (isset($_COOKIE[CookieModule::getFullName("accessLevel")]) === false) {
		header("Location: ./login.php");
		exit;
	} else if (!PERMISSIONS[intval($_COOKIE[CookieModule::getFullName("accessLevel")])][PERMISSION_ADMIN_LOGIN]) {
		CookieModule::set("alert", new Alert(
			"danger",
			$GLOBALS["dict"]["error_message_NEP"]
		));
		header("Location: ./logout.php");
		exit;
	}

	$cookieAlert = CookieModule::get("alert");
	CookieModule::remove("alert");
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php require(ROOT_LOCAL."/assets/head.php");?>
	</head>
	<body>
		<?php require(ROOT_LOCAL."/assets/navigation.php");?>
		
		<div class="container">
			<?php
				if ($cookieAlert !== null) {
					Alert::show($cookieAlert);
				}
			?>
			<div class="jumbotron jma-background-color">
				<div>
					<h1>Lucas Kontaktdaten</h1>
					<p class="ml-3">Bei Fragen, Problemen oder Vorschlägen:</p>
					<ul>
						<li>E-Mail: <a href="mailto:lucas.kinne@live.de?subject=%5BJMA%5D%20Supportanfrage">lucas.kinne@live.de</a> oder <a href="mailto:lucas@luckev.info?subject=%5BJMA%5D%20Supportanfrage">lucas@luckev.info</a></li>
						<li>WhatsApp/Telefonanruf: <a href="tel:+4917657843346">0176 57843346</a></li>
					</ul>
				</div>
				<div class="mt-4">
					<h1>Linksammlung</h1>
					<ul>
						<li>
							<strong>Live-System</strong>
							<ul>
								<li><a href="https://junges-muensterschwarzach.roth-familie.eu" target="_blank">App</a></li>
								<li><a href="https://junges-muensterschwarzach.roth-familie.eu/app-backend/index.php" target="_blank">Admin-Bereich</a></li>
							</ul>
						</li>
						<li>
							<strong>Test-System (für Zugriff bei Lucas melden)</strong>
							<ul>
								<li><a href="https://app.test.junges-muensterschwarzach.roth-familie.eu" target="_blank">App</a></li>
								<li><a href="https://app-backend.test.junges-muensterschwarzach.roth-familie.eu" target="_blank">Admin-Bereich</a></li>
							</ul>
						</li>
					</ul>
				</div>
				<div class="mt-4">
					<h1>Video-Tutorial</h1>
					<div class="embed-responsive embed-responsive-16by9">
						<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/BZlcKieP5io" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
				<div class="mt-4">
					<h1>Hinweise für formatierbare Inhalte</h1>
					<ul>
						<li><strong>für Bilder</strong>: Breite="100%" und Höhe="auto"</li>
						<li><strong>für Videos</strong>: Breite="100%" und Höhe="300" (oder andere fixe Höhe)</li>
					</ul>
				</div>
			</div>
		</div>		
	</body>
</html>