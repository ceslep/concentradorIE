$url = "https://app.iedeoccidente.com/consex2/generarMenu.php"
$body = @{ docente = "75081186" } | ConvertTo-Json
$response = Invoke-RestMethod -Uri $url -Method Post -Body $body -ContentType "application/json"
$response | ConvertTo-Json -Depth 10
