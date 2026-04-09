<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Docs - Swagger</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #fafafa;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: #0f172a;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        .topbar a {
            color: #93c5fd;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="topbar">
    <div>API Documentation - Hệ thống Quản lý thư viện</div>
    <a href="/openapi.yaml" target="_blank" rel="noopener">Tải openapi.yaml</a>
</div>
<div id="swagger-ui"></div>

<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    window.ui = SwaggerUIBundle({
        url: '/openapi.yaml',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis],
    });
</script>
</body>
</html>
