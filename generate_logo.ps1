Add-Type -AssemblyName System.Drawing
$bmp = New-Object System.Drawing.Bitmap(512, 512)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.Clear([System.Drawing.Color]::FromArgb(37, 99, 235))
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$pen = New-Object System.Drawing.Pen([System.Drawing.Color]::White, 20)
$pen.LineJoin = [System.Drawing.Drawing2D.LineJoin]::Round
$g.DrawRectangle($pen, 64, 100, 384, 220)
$g.DrawLine($pen, 256, 320, 256, 430)
$g.DrawLine($pen, 150, 430, 362, 430)
$font = New-Object System.Drawing.Font('Arial', 90, [System.Drawing.FontStyle]::Bold)
$brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
$g.DrawString('WE', $font, $brush, 150, 140)
$bmp.Save('C:\xampp\htdocs\webestoque\assets\logo.png', [System.Drawing.Imaging.ImageFormat]::Png)
