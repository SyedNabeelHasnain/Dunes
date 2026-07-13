# PowerShell script to test FTP connection locally
[CmdletBinding()]
param()

Write-Host "--- FTP Connection Diagnostic Tool ---" -ForegroundColor Cyan

$server = Read-Host "Enter FTP Server IP (e.g. 145.79.25.70)"
$username = Read-Host "Enter FTP Username (e.g. u410503041.deploy)"
Write-Host "Enter FTP Password (typing will be hidden):" -NoNewline
$password = [System.Security.SecureString]
while ($true) {
    $key = [System.Console]::ReadKey($true)
    if ($key.Key -eq [System.ConsoleKey]::Enter) {
        break
    }
    if ($key.Key -eq [System.ConsoleKey]::Backspace) {
        if ($password.Length -gt 0) {
            $password.RemoveAt($password.Length - 1)
            Write-Host -NoNewline "`b `b"
        }
    } else {
        $password.AppendChar($key.KeyChar)
        Write-Host -NoNewline "*"
    }
}
Write-Host ""

$bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($password)
$plainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)

Write-Host "Testing connection to ftp://$server ..." -ForegroundColor Yellow

try {
    $ftp = [System.Net.FtpWebRequest]::Create("ftp://$server/")
    $ftp.Credentials = New-Object System.Net.NetworkCredential($username, $plainPassword)
    $ftp.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftp.Timeout = 10000
    
    $response = $ftp.GetResponse()
    $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
    $directoryList = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    Write-Host "SUCCESS: Connection established successfully!" -ForegroundColor Green
    Write-Host "Directory listing:" -ForegroundColor Green
    Write-Host $directoryList
}
catch {
    Write-Host "ERROR: Connection failed." -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    if ($_.Exception.InnerException) {
        Write-Host $_.Exception.InnerException.Message -ForegroundColor Red
    }
}
