<!DOCTYPE html>
<html>
<head>
    <title>API documentation</title>
    <link rel="stylesheet" href="/dist/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{{asset('dist/swagger-ui-bundle.js')}}"></script>
    <script src="{{asset('dist/swagger-ui-standalone-preset.js')}}"></script>
    <script>
        window.onload = function() {
          
            const ui = SwaggerUIBundle({
                url: "{{asset('dist/swagger.yaml')}}", 
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
