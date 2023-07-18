<!DOCTYPE html>
<html>
<head>
    <title>API documentation</title>
    <link rel="stylesheet" href="/swagger/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{{asset('swagger/swagger-ui-bundle.js')}}"></script>
    <script src="{{asset('swagger/swagger-ui-standalone-preset.js')}}"></script>
    <script>
        window.onload = function() {
          
            const ui = SwaggerUIBundle({
                url: "{{asset('swagger/swagger.yaml')}}", 
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "StandaloneLayout"
            })
            
        }
    </script>
</body>
</html>
