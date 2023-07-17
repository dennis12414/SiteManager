<!DOCTYPE html>
<html>
<head>
    <title>Swagger UI</title>
    <link rel="stylesheet" href="/dist/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{{asset('dist/swagger-ui-bundle.js')}}"></script>
    <script src="{{asset('dist/swagger-ui-standalone-preset.js')}}"></script>
    <script>
        window.onload = function() {
            // Begin Swagger UI call region
            const ui = SwaggerUIBundle({
                url: "{{asset('dist/swagger.yaml')}}", // Replace with your OpenAPI spec file
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "StandaloneLayout"
            })
            // End Swagger UI call region
        }
    </script>
</body>
</html>
