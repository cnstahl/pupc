<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
	<head>
{% block head %}
		<title>{% block title %}{{ title }}{% endblock %} - Princeton Society of Physics Students</title>
{% include "partials/meta.php" %}
{% include "partials/css.php" %}
{% include "partials/favicon.php" %}
{% endblock head %}
	</head>
	<body>
		{% include "partials/navbar.php" %}
		<div id="container" class="col-xs-12">
{% block body %}
			<header><h1>{% block heading %}{{ title }}{% endblock %}</h1></header>
			
			<!-- Beginning of content -->
{% endblock body %}
			{% include "partials/footer.php" %}
		</div>
	</body>
</html>
