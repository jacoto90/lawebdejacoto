param(
  [string]$ProjectPath = "C:\Users\josea\PhpstormProjects\untitled\lawebdejacoto",
  [string]$FtpHost = "41173422.servicio-online.net",
  [string]$FtpUser = "user-10840863",
  [string]$FtpPass = "",
  [string]$RemotePath = "lawebdejacoto.com"
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($FtpPass)) {
  $secure = Read-Host "FTP password" -AsSecureString
  $bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
  $FtpPass = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)
}

Set-Location -LiteralPath $ProjectPath
git checkout production
git pull

function New-Req($url, $method) {
  $r = [System.Net.FtpWebRequest]::Create($url)
  $r.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
  $r.Method = $method
  $r.UseBinary = $true
  $r.KeepAlive = $false
  return $r
}

function List-Entries($url) {
  $r = New-Req $url ([System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails)
  $resp = $r.GetResponse()
  $sr = New-Object System.IO.StreamReader($resp.GetResponseStream())
  $txt = $sr.ReadToEnd()
  $sr.Close()
  $resp.Close()
  $out = @()
  foreach ($line in ($txt -split "`r?`n")) {
    if ([string]::IsNullOrWhiteSpace($line)) { continue }
    $name = ($line -split '\s+')[-1]
    $isDir = $line.StartsWith('d')
    $out += [PSCustomObject]@{ Name = $name; IsDir = $isDir }
  }
  return $out
}

function Delete-Tree($url) {
  foreach ($e in (List-Entries $url)) {
    if ($e.Name -in @('.', '..')) { continue }
    $child = "$url/$($e.Name)"
    if ($e.IsDir) {
      Delete-Tree $child
      try {
        $r = New-Req $child ([System.Net.WebRequestMethods+Ftp]::RemoveDirectory)
        $resp = $r.GetResponse(); $resp.Close()
      } catch {}
    } else {
      try {
        $r = New-Req $child ([System.Net.WebRequestMethods+Ftp]::DeleteFile)
        $resp = $r.GetResponse(); $resp.Close()
      } catch {}
    }
  }
}

function Ensure-Dir($url) {
  try {
    $r = New-Req $url ([System.Net.WebRequestMethods+Ftp]::MakeDirectory)
    $resp = $r.GetResponse(); $resp.Close()
  } catch {}
}

function Upload-Dir($local, $remote) {
  Ensure-Dir $remote
  Get-ChildItem -LiteralPath $local -Force | ForEach-Object {
    $excludedDirs = @('.git', '.idea', '.vscode', 'storage')
    $excludedFiles = @('.env', '.env.local', 'database.php')
    $excludedExtensions = @('.sqlite', '.sqlite-shm', '.sqlite-wal', '.log')

    if ($_.PSIsContainer -and $_.Name -in $excludedDirs) { return }
    if (-not $_.PSIsContainer -and $_.Name -in $excludedFiles) { return }
    if (-not $_.PSIsContainer -and $_.Extension -in $excludedExtensions) { return }

    if ($_.PSIsContainer) {
      Upload-Dir $_.FullName "$remote/$($_.Name)"
    } else {
      $u = "$remote/$($_.Name)"
      $r = New-Req $u ([System.Net.WebRequestMethods+Ftp]::UploadFile)
      $bytes = [System.IO.File]::ReadAllBytes($_.FullName)
      $r.ContentLength = $bytes.Length
      $rs = $r.GetRequestStream()
      $rs.Write($bytes, 0, $bytes.Length)
      $rs.Close()
      $resp = $r.GetResponse(); $resp.Close()
    }
  }
}

$base = "ftp://$FtpHost/$RemotePath"
Delete-Tree $base
Upload-Dir (Get-Location).Path $base
Write-Host "OK: Deploy full completado desde production"
